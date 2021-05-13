<?php
namespace app\home\controller;
use app\home\model;
class Category extends Base
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('Category');
    }
    
    //信息列表
    public function index()
    { 
        $datalist = $this->model->tree();
        //dump($datalist);
        $this->assign('datalist', $datalist);
        return $this->fetch('category/index');
    }
    //新增/编辑
    public function edit(){    	
    	$cate_id = input('param.cate_id');
    	$info = $this->model->where("cate_id='{$cate_id}'")->find();
    	$cate_mod_arr = array(array('modname'=>'link'),array('modname'=>'article'),array('modname'=>'info'));
    	$catelist = $this->model->getcatetreearr('article');
    	$this->assign('cate_mod_arr',$cate_mod_arr);
    	$this->assign('info',$info);
    	$this->assign('catelist',$catelist);
    	return  $this->fetch('category/edit');
    }
    
    //信息保存
    public function saveinfo(){
    	$data = input('post.');
    	$data['cate_status'] = input('post.cate_status',0);
    	$validate = $this->validate($data,'Category.saveinfo');
    	if(true !== $validate){
    		$this->error($validate);
    	} else {
    		$cate_id = input('post.cate_id');
    		if($cate_id>0){
    			$result = $this->model->where('cate_id',$cate_id)->update($data);
    		} else {
    			$data['cate_ctime'] = time();
    			$result = $this->model->data($data)->save();
    		}
    		if(false !== $result){
    			$this->success('保存信息成功','home/category/index');
    		}
    	}
    }
    
    //信息删除
    public function removeinfo(){
    	$cate_id = input('param.cate_id');
    	$result = $this->model->where('cate_id',$cate_id)->delete();
    	if(false !== $result){
    		//$this->success('删除信息成功','home/category/index');
    		$data = [
    			'status' => 0,
    			'msg' => '分类删除成功！',
    		];
    	}else {
    		$data = [
    			'status' => 1,
    			'msg' => '分类删除失败！',
    		];
    	}    	
    	echo json_encode($data);
    }
    
    //修改顺序
    public function changeOrder(){
    	$cate_id = input('param.id');
    	$result = $this->model->where('cate_id',$cate_id)->update( [ 'cate_order'=>input('param.order') ] );
    	if(false !== $result){
    		$data = [
    			'status' => 0,
    			'msg' => '修改排序成功！',
    		];
    	}else {
    		$data = [
    			'status' => 1,
    			'msg' => '修改排序失败！',
    		];
    	}
    	echo json_encode($data);
    }
}
