<?php
namespace app\home\controller;
use think\Controller;
define('INSTALL_APP_PATH', realpath('../') . '/');
class Install extends Controller
{  
    //信息列表
    public function index()
    { 
        header("Content-Type:text/html; charset=utf-8");
        //环境检测
        $env = $this->check_env();
        //函数检测
        $fun = $this->check_fun();
        //文件目录检测
        $dirfile = $this->check_dirfile();

        $this->assign('env',$env);
        $this->assign('fun',$fun);
        $this->assign('dirfile',$dirfile);
        return $this->fetch('install/index');
    }
    
	/**
	 * 环境监测函数
	 */
	function check_env()
	{
	    $items = array(
	        'os'      => array('操作系统', '不限制', PHP_OS, 'success'),
	        'php'     => array('PHP版本', '5.4', PHP_VERSION, 'success'),
	        'upload'  => array('附件上传', '不限制', '未知', 'success'),
	        'gd'      => array('GD库', '2.0', '未知', 'success'),
	        'disk'    => array('磁盘空间', '5M', '未知', 'success'),
	    );
	
	    //PHP环境检测
	    if($items['php'][2] < $items['php'][1]){
	        $items['php'][3] = 'error';
	        session('error', true);
	    }
	
	    //附件上传检测
	    if(@ini_get('file_uploads'))
	        $items['upload'][2] = ini_get('upload_max_filesize');
	
	    //GD库检测
	    $tmp = function_exists('gd_info') ? gd_info() : array();
	    if(empty($tmp['GD Version'])){
	        $items['gd'][2] = '未安装';
	        $items['gd'][3] = 'error';
	        session('error', true);
	    } else {
	        $items['gd'][2] = $tmp['GD Version'];
	    }
	    unset($tmp);
	
	    //磁盘空间检测
	    if(function_exists('disk_free_space')) {
	        $items['disk'][2] = floor(disk_free_space(INSTALL_APP_PATH) / (1024*1024)).'M';
	    }
	
	    return $items;
	}
	
	
	
	/**
	 * 目录，文件读写检测
	 * @return array 检测数据
	 */
	function check_dirfile()
	{
	    $items = array(	        
	        array('dir',  '可写', 'success', 'public/uploads'),
	        array('dir',  '可写', 'success', 'application/extra'),
	        array('file', '可写', 'success', 'application/config.php'),
	        array('file', '可写', 'success', 'application/database.php'),
	        array('dir',  '可写', 'success', 'runtime'),	        
	
	    );
	
	    foreach ($items as &$val) {
	        if('dir' == $val[0]){
	            if(!is_writable(INSTALL_APP_PATH . $val[3])) {
	                if(is_dir($val[3])) {
	                    $val[1] = '可读';
	                    $val[2] = 'error';
	                    session('error', true);
	                } else {
	                    $val[1] = '不存在';
	                    $val[2] = 'error';
	                    session('error', true);
	                }
	            }
	        } else {
	            if(file_exists(INSTALL_APP_PATH . $val[3])) {
	                if(!is_writable(INSTALL_APP_PATH . $val[3])) {
	                    $val[1] = '不可写';
	                    $val[2] = 'error';
	                    session('error', true);
	                }
	            } else {
	                if(!is_writable(dirname(INSTALL_APP_PATH . $val[3]))) {
	                    $val[1] = '不存在';
	                    $val[2] = 'error';
	                    session('error', true);
	                }
	            }
	        }
	    }
	
	    return $items;
	}
	
	
	/**
	 * 监测函数是否存在
	 */
	function check_fun()
	{
	    $items = array(
	        array('iconv',     '支持', 'success'),
	        array('file_get_contents', '支持', 'success'),
	        array('file_put_contents', '支持', 'success'),
	        array('var_export', '支持', 'success'),
	        array('mb_strlen',         '支持', 'success'),
	    );
	
	    foreach ($items as &$val) {
	        if(!function_exists($val[0])){
	            $val[1] = '不支持';
	            $val[2] = 'error';
	            $val[3] = '开启';
	            session('error', true);
	        }
	    }
	
	    return $items;
	}
}
