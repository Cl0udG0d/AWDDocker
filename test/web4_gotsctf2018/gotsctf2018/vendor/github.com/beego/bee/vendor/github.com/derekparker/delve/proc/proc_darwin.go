package proc

// #include "proc_darwin.h"
// #include "threads_darwin.h"
// #include "exec_darwin.h"
// #include <stdlib.h>
import "C"
import (
	"debug/gosym"
	"errors"
	"fmt"
	"os"
	"os/exec"
	"path/filepath"
	"sync"
	"unsafe"

	"golang.org/x/debug/macho"

	"github.com/derekparker/delve/dwarf/frame"
	"github.com/derekparker/delve/dwarf/line"
	sys "golang.org/x/sys/unix"
)

// OSProcessDetails holds Darwin specific information.
type OSProcessDetails struct {
	task             C.task_t      // mach task for the debugged process.
	exceptionPort    C.mach_port_t // mach port for receiving mach exceptions.
	notificationPort C.mach_port_t // mach port for dead name notification (process exit).
	initialized      bool

	// the main port we use, will return messages from both the
	// exception and notification ports.
	portSet C.mach_port_t
}

// Launch creates and begins debugging a new process. Uses a
// custom fork/exec process in order to take advantage of
// PT_SIGEXC on Darwin which will turn Unix signals into
// Mach exceptions.
func Launch(cmd []string, wd string) (*Process, error) {
	// check that the argument to Launch is an executable file
	if fi, staterr := os.Stat(cmd[0]); staterr == nil && (fi.Mode()&0111) == 0 {
		return nil, NotExecutableErr
	}
	argv0Go, err := filepath.Abs(cmd[0])
	if err != nil {
		return nil, err
	}
	// Make sure the binary exists.
	if filepath.Base(cmd[0]) == cmd[0] {
		if _, err := exec.LookPath(cmd[0]); err != nil {
			return nil, err
		}
	}
	if _, err := os.Stat(argv0Go); err != nil {
		return nil, err
	}

	argv0 := C.CString(argv0Go)
	argvSlice := make([]*C.char, 0, len(cmd)+1)
	for _, arg := range cmd {
		argvSlice = append(argvSlice, C.CString(arg))
	}
	// argv array must be null terminated.
	argvSlice = append(argvSlice, nil)

	dbp := New(0)
	var pid int
	dbp.execPtraceFunc(func() {
		ret := C.fork_exec(argv0, &argvSlice[0], C.int(len(argvSlice)),
			C.CString(wd),
			&dbp.os.task, &dbp.os.portSet, &dbp.os.exceptionPort,
			&dbp.os.notificationPort)
		pid = int(ret)
	})
	if pid <= 0 {
		return nil, fmt.Errorf("could not fork/exec")
	}
	dbp.Pid = pid
	for i := range argvSlice {
		C.free(unsafe.Pointer(argvSlice[i]))
	}

	// Initialize enough of the Process state so that we can use resume and
	// trapWait to wait until the child process calls execve.

	for {
		err = dbp.updateThreadListForTask(C.get_task_for_pid(C.int(dbp.Pid)))
		if err == nil {
			break
		}
		if err != couldNotGetThreadCount && err != couldNotGetThreadList {
			return nil, err
		}
	}

	if err := dbp.resume(); err != nil {
		return nil, err
	}

	dbp.allGCache = nil
	for _, th := range dbp.Threads {
		th.clearBreakpointState()
	}

	trapthread, err := dbp.trapWait(-1)
	if err != nil {
		return nil, err
	}
	if err := dbp.Halt(); err != nil {
		return nil, dbp.exitGuard(err)
	}

	_, err = dbp.waitForStop()
	if err != nil {
		return nil, err
	}

	dbp.os.initialized = true
	dbp, err = initializeDebugProcess(dbp, argv0Go, false)
	if err != nil {
		return nil, err
	}

	if err := dbp.SwitchThread(trapthread.ID); err != nil {
		return nil, err
	}

	return dbp, err
}

// Attach to an existing process with the given PID.
func Attach(pid int) (*Process, error) {
	dbp := New(pid)

	kret := C.acquire_mach_task(C.int(pid),
		&dbp.os.task, &dbp.os.portSet, &dbp.os.exceptionPort,
		&dbp.os.notificationPort)

	if kret != C.KERN_SUCCESS {
		return nil, fmt.Errorf("could not attach to %d", pid)
	}

	dbp.os.initialized = true

	return initializeDebugProcess(dbp, "", true)
}

// Kill kills the process.
func (dbp *Process) Kill() (err error) {
	if dbp.exited {
		return nil
	}
	err = sys.Kill(-dbp.Pid, sys.SIGKILL)
	if err != nil {
		return errors.New("could not deliver signal: " + err.Error())
	}
	for port := range dbp.Threads {
		if C.thread_resume(C.thread_act_t(port)) != C.KERN_SUCCESS {
			return errors.New("could not resume task")
		}
	}
	for {
		var task C.task_t
		port := C.mach_port_wait(dbp.os.portSet, &task, C.int(0))
		if port == dbp.os.notificationPort {
			break
		}
	}
	dbp.postExit()
	return
}

func (dbp *Process) requestManualStop() (err error) {
	var (
		task          = C.mach_port_t(dbp.os.task)
		thread        = C.mach_port_t(dbp.CurrentThread.os.threadAct)
		exceptionPort = C.mach_port_t(dbp.os.exceptionPort)
	)
	kret := C.raise_exception(task, thread, exceptionPort, C.EXC_BREAKPOINT)
	if kret != C.KERN_SUCCESS {
		return fmt.Errorf("could not raise mach exception")
	}
	return nil
}

var couldNotGetThreadCount = errors.New("could not get thread count")
var couldNotGetThreadList = errors.New("could not get thread list")

func (dbp *Process) updateThreadList() error {
	return dbp.updateThreadListForTask(dbp.os.task)
}

func (dbp *Process) updateThreadListForTask(task C.task_t) error {
	var (
		err   error
		kret  C.kern_return_t
		count C.int
		list  []uint32
	)

	for {
		count = C.thread_count(task)
		if count == -1 {
			return couldNotGetThreadCount
		}
		list = make([]uint32, count)

		// TODO(dp) might be better to malloc mem in C and then free it here
		// instead of getting count above and passing in a slice
		kret = C.get_threads(task, unsafe.Pointer(&list[0]), count)
		if kret != -2 {
			break
		}
	}
	if kret != C.KERN_SUCCESS {
		return couldNotGetThreadList
	}

	for _, thread := range dbp.Threads {
		thread.os.exists = false
	}

	for _, port := range list {
		thread, ok := dbp.Threads[int(port)]
		if !ok {
			thread, err = dbp.addThread(int(port), false)
			if err != nil {
				return err
			}
		}
		thread.os.exists = true
	}

	for threadID, thread := range dbp.Threads {
		if !thread.os.exists {
			delete(dbp.Threads, threadID)
		}
	}

	return nil
}

func (dbp *Process) addThread(port int, attach bool) (*Thread, error) {
	if thread, ok := dbp.Threads[port]; ok {
		return thread, nil
	}
	thread := &Thread{
		ID:  port,
		dbp: dbp,
		os:  new(OSSpecificDetails),
	}
	dbp.Threads[port] = thread
	thread.os.threadAct = C.thread_act_t(port)
	if dbp.CurrentThread == nil {
		dbp.SwitchThread(thread.ID)
	}
	return thread, nil
}

func (dbp *Process) parseDebugFrame(exe *macho.File, wg *sync.WaitGroup) {
	defer wg.Done()

	debugFrameSec := exe.Section("__debug_frame")
	debugInfoSec := exe.Section("__debug_info")

	if debugFrameSec != nil && debugInfoSec != nil {
		debugFrame, err := exe.Section("__debug_frame").Data()
		if err != nil {
			fmt.Println("could not get __debug_frame section", err)
			os.Exit(1)
		}
		dat, err := debugInfoSec.Data()
		if err != nil {
			fmt.Println("could not get .debug_info section", err)
			os.Exit(1)
		}
		dbp.frameEntries = frame.Parse(debugFrame, frame.DwarfEndian(dat))
	} else {
		fmt.Println("could not find __debug_frame section in binary")
		os.Exit(1)
	}
}

func (dbp *Process) obtainGoSymbols(exe *macho.File, wg *sync.WaitGroup) {
	defer wg.Done()

	var (
		symdat  []byte
		pclndat []byte
		err     error
	)

	if sec := exe.Section("__gosymtab"); sec != nil {
		symdat, err = sec.Data()
		if err != nil {
			fmt.Println("could not get .gosymtab section", err)
			os.Exit(1)
		}
	}

	if sec := exe.Section("__gopclntab"); sec != nil {
		pclndat, err = sec.Data()
		if err != nil {
			fmt.Println("could not get .gopclntab section", err)
			os.Exit(1)
		}
	}

	pcln := gosym.NewLineTable(pclndat, exe.Section("__text").Addr)
	tab, err := gosym.NewTable(symdat, pcln)
	if err != nil {
		fmt.Println("could not get initialize line table", err)
		os.Exit(1)
	}

	dbp.goSymTable = tab
}

func (dbp *Process) parseDebugLineInfo(exe *macho.File, wg *sync.WaitGroup) {
	defer wg.Done()

	if sec := exe.Section("__debug_line"); sec != nil {
		debugLine, err := exe.Section("__debug_line").Data()
		if err != nil {
			fmt.Println("could not get __debug_line section", err)
			os.Exit(1)
		}
		dbp.lineInfo = line.Parse(debugLine)
	} else {
		fmt.Println("could not find __debug_line section in binary")
		os.Exit(1)
	}
}

var UnsupportedArchErr = errors.New("unsupported architecture - only darwin/amd64 is supported")

func (dbp *Process) findExecutable(path string) (*macho.File, string, error) {
	if path == "" {
		path = C.GoString(C.find_executable(C.int(dbp.Pid)))
	}
	exe, err := macho.Open(path)
	if err != nil {
		return nil, path, err
	}
	if exe.Cpu != macho.CpuAmd64 {
		return nil, path, UnsupportedArchErr
	}
	dbp.dwarf, err = exe.DWARF()
	if err != nil {
		return nil, path, err
	}
	return exe, path, nil
}

func (dbp *Process) trapWait(pid int) (*Thread, error) {
	for {
		task := dbp.os.task
		port := C.mach_port_wait(dbp.os.portSet, &task, C.int(0))

		switch port {
		case dbp.os.notificationPort:
			// on macOS >= 10.12.1 the task_t changes after an execve, we could
			// receive the notification for the death of the pre-execve task_t,
			// this could also happen *before* we are notified that our task_t has
			// changed.
			if dbp.os.task != task {
				continue
			}
			if !dbp.os.initialized {
				if pidtask := C.get_task_for_pid(C.int(dbp.Pid)); pidtask != 0 && dbp.os.task != pidtask {
					continue
				}
			}
			_, status, err := dbp.wait(dbp.Pid, 0)
			if err != nil {
				return nil, err
			}
			dbp.postExit()
			return nil, ProcessExitedError{Pid: dbp.Pid, Status: status.ExitStatus()}

		case C.MACH_RCV_INTERRUPTED:
			if !dbp.halt {
				// Call trapWait again, it seems
				// MACH_RCV_INTERRUPTED is emitted before
				// process natural death _sometimes_.
				continue
			}
			return nil, nil

		case 0:
			return nil, fmt.Errorf("error while waiting for task")
		}

		// In macOS 10.12.1 if we received a notification for a task other than
		// the inferior's task and the inferior's task is no longer valid, this
		// means inferior called execve and its task_t changed.
		if dbp.os.task != task && C.task_is_valid(dbp.os.task) == 0 {
			dbp.os.task = task
			kret := C.reset_exception_ports(dbp.os.task, &dbp.os.exceptionPort, &dbp.os.notificationPort)
			if kret != C.KERN_SUCCESS {
				return nil, fmt.Errorf("could not follow task across exec: %d\n", kret)
			}
		}

		// Since we cannot be notified of new threads on OS X
		// this is as good a time as any to check for them.
		dbp.updateThreadList()
		th, ok := dbp.Threads[int(port)]
		if !ok {
			if dbp.halt {
				dbp.halt = false
				return th, nil
			}
			if dbp.firstStart || th.singleStepping {
				dbp.firstStart = false
				return th, nil
			}
			if err := th.Continue(); err != nil {
				return nil, err
			}
			continue
		}
		return th, nil
	}
}

func (dbp *Process) waitForStop() ([]int, error) {
	ports := make([]int, 0, len(dbp.Threads))
	count := 0
	for {
		var task C.task_t
		port := C.mach_port_wait(dbp.os.portSet, &task, C.int(1))
		if port != 0 && port != dbp.os.notificationPort && port != C.MACH_RCV_INTERRUPTED {
			count = 0
			ports = append(ports, int(port))
		} else {
			n := C.num_running_threads(dbp.os.task)
			if n == 0 {
				return ports, nil
			} else if n < 0 {
				return nil, fmt.Errorf("error waiting for thread stop %d", n)
			} else if count > 16 {
				return nil, fmt.Errorf("could not stop process %d", n)
			}
		}
	}
}

func (dbp *Process) setCurrentBreakpoints(trapthread *Thread) error {
	ports, err := dbp.waitForStop()
	if err != nil {
		return err
	}
	trapthread.SetCurrentBreakpoint()
	for _, port := range ports {
		if th, ok := dbp.Threads[port]; ok {
			err := th.SetCurrentBreakpoint()
			if err != nil {
				return err
			}
		}
	}
	return nil
}

func (dbp *Process) loadProcessInformation(wg *sync.WaitGroup) {
	wg.Done()
}

func (dbp *Process) wait(pid, options int) (int, *sys.WaitStatus, error) {
	var status sys.WaitStatus
	wpid, err := sys.Wait4(pid, &status, options, nil)
	return wpid, &status, err
}

func killProcess(pid int) error {
	return sys.Kill(pid, sys.SIGINT)
}

func (dbp *Process) exitGuard(err error) error {
	if err != ErrContinueThread {
		return err
	}
	_, status, werr := dbp.wait(dbp.Pid, sys.WNOHANG)
	if werr == nil && status.Exited() {
		dbp.postExit()
		return ProcessExitedError{Pid: dbp.Pid, Status: status.ExitStatus()}
	}
	return err
}

func (dbp *Process) resume() error {
	// all threads stopped over a breakpoint are made to step over it
	for _, thread := range dbp.Threads {
		if thread.CurrentBreakpoint != nil {
			if err := thread.StepInstruction(); err != nil {
				return err
			}
			thread.CurrentBreakpoint = nil
		}
	}
	// everything is resumed
	for _, thread := range dbp.Threads {
		if err := thread.resume(); err != nil {
			return dbp.exitGuard(err)
		}
	}
	return nil
}
