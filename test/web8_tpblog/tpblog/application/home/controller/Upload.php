<?php
namespace app\home\controller;
use think\Controller;
use think\Db;
use think\File;
use think\Request;
class Upload extends Base
{
    public function _initialize()
    {
        $this->rootPath= '/public/uploads/';
		$this->savePath= './public/uploads/';
    }
    
    //信息列表
    public function multsaveupload()
    { 
    	/* 返回标准数据 */
		$return = array();
		$file = request()->file('file');
		$info = $file->move($this->savePath);		
		if ($info) {
			$data = array();
			$data['f_type']     = 'ueditor';
			$data['f_savepath'] = $this->rootPath.$info->getSaveName();
			$data['f_name']     = $info->getFilename();
			$data['f_savename'] = $info->getSaveName();
			$data['f_size']     = $info->getSize();
			$data['f_mime']     = $info->getExtension();
			$data['f_extension']= $info->getExtension();
			$data['f_hash']     = $info->hash('md5');
			$data['f_time']     = date("Y-m-d H:i:s");
			$data['u_id']       = '';//$_SESSION['uid'];
			$data['web_id']     = '';//$_SESSION['activewebid'];			
			//$fid = Db::name('file')->insert($data);
			//-----------------------
			$ret = array();
            $ret['filename'] = $this->rootPath.$info->getSaveName();
            $ret['imgurl']	 = $this->rootPath.$info->getSaveName();
            $ret['hash']	 = $info->hash('md5');
            $ret['fid']		 = '';//$fid;                                        
            array_push($return, $ret);
		}
		exit(json_encode($return));
    }
        
}
