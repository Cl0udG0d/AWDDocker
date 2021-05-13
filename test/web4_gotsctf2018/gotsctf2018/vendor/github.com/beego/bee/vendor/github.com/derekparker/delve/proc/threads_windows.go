package proc

import (
	"syscall"

	sys "golang.org/x/sys/windows"
)

// WaitStatus is a synonym for the platform-specific WaitStatus
type WaitStatus sys.WaitStatus

// OSSpecificDetails holds information specific to the Windows
// operating system / kernel.
type OSSpecificDetails struct {
	hThread syscall.Handle
}

func (t *Thread) halt() (err error) {
	// Ignore the request to halt. On Windows, all threads are halted
	// on return from WaitForDebugEvent.
	return nil

	// TODO - This may not be correct in all usages of dbp.Halt.  There
	// are some callers who use dbp.Halt() to stop the process when it is not
	// already broken on a debug event.
}

func (t *Thread) singleStep() error {
	context := newCONTEXT()
	context.ContextFlags = _CONTEXT_ALL

	// Set the processor TRAP flag
	err := _GetThreadContext(t.os.hThread, context)
	if err != nil {
		return err
	}

	context.EFlags |= 0x100

	err = _SetThreadContext(t.os.hThread, context)
	if err != nil {
		return err
	}

	_, err = _ResumeThread(t.os.hThread)
	if err != nil {
		return err
	}

	for {
		var tid, exitCode int
		t.dbp.execPtraceFunc(func() {
			tid, exitCode, err = t.dbp.waitForDebugEvent(waitBlocking | waitSuspendNewThreads)
		})
		if err != nil {
			return err
		}
		if tid == 0 {
			t.dbp.postExit()
			return ProcessExitedError{Pid: t.dbp.Pid, Status: exitCode}
		}

		if t.dbp.os.breakThread == t.ID {
			break
		}

		t.dbp.execPtraceFunc(func() {
			err = _ContinueDebugEvent(uint32(t.dbp.Pid), uint32(t.dbp.os.breakThread), _DBG_CONTINUE)
		})
	}

	_, err = _SuspendThread(t.os.hThread)
	if err != nil {
		return err
	}

	t.dbp.execPtraceFunc(func() {
		err = _ContinueDebugEvent(uint32(t.dbp.Pid), uint32(t.ID), _DBG_CONTINUE)
	})
	if err != nil {
		return err
	}

	// Unset the processor TRAP flag
	err = _GetThreadContext(t.os.hThread, context)
	if err != nil {
		return err
	}

	context.EFlags &= ^uint32(0x100)

	return _SetThreadContext(t.os.hThread, context)
}

func (t *Thread) resume() error {
	t.running = true
	var err error
	t.dbp.execPtraceFunc(func() {
		//TODO: Note that we are ignoring the thread we were asked to continue and are continuing the
		//thread that we last broke on.
		err = _ContinueDebugEvent(uint32(t.dbp.Pid), uint32(t.ID), _DBG_CONTINUE)
	})
	return err
}

func (t *Thread) blocked() bool {
	// TODO: Probably incorrect - what are the runtime functions that
	// indicate blocking on Windows?
	pc, err := t.PC()
	if err != nil {
		return false
	}
	fn := t.dbp.goSymTable.PCToFunc(pc)
	if fn == nil {
		return false
	}
	switch fn.Name {
	case "runtime.kevent", "runtime.usleep":
		return true
	default:
		return false
	}
}

func (t *Thread) stopped() bool {
	// TODO: We are assuming that threads are always stopped
	// during command execution.
	return true
}

func (t *Thread) writeMemory(addr uintptr, data []byte) (int, error) {
	var count uintptr
	err := _WriteProcessMemory(t.dbp.os.hProcess, addr, &data[0], uintptr(len(data)), &count)
	if err != nil {
		return 0, err
	}
	return int(count), nil
}

func (t *Thread) readMemory(addr uintptr, size int) ([]byte, error) {
	if size == 0 {
		return nil, nil
	}
	var count uintptr
	buf := make([]byte, size)
	err := _ReadProcessMemory(t.dbp.os.hProcess, addr, &buf[0], uintptr(size), &count)
	if err != nil {
		return nil, err
	}
	return buf[:count], nil
}
