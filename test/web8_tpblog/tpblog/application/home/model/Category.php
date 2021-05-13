<?php
namespace app\home\model;
use think\Model;
class Category extends Model
{
    protected $pk = 'cate_id';
    
    //得到分类列表
    public function tree(){
    	$data = $this->field('cate_id,cate_mod,cate_name,cate_url,cate_status,cate_order,root_id')->order('cate_order asc')->select();
    	$treeArr = array();
    	$this->catetree($data, 0 ,0 , $treeArr);
    	return $treeArr;
    }
    
    public function catetree($data,$pid,$deep=0,&$treeArr){
    	foreach ($data as $k => $v) {
    		if($v['root_id']==$pid){
    			$v['cate_name'] = str_repeat('&nbsp;&nbsp;',$deep).'┝━&nbsp;'.$v['cate_name'];
    			$treeArr[] = $v;
    			$this->catetree($data, $v['cate_id'],$deep+1,$treeArr);
    		}
    	}
    }
    
    //返回栏目的树状数组
    public function getcatetreearr($cate_mod){    	
    	$data = $this->field('cate_id,cate_name,root_id')->where(" cate_mod='{$cate_mod}' and cate_status=1")->order('cate_order')->select();
    	$treeArr = array();
    	$this->catetree($data, 0 ,0 , $treeArr);
    	return $treeArr;
    }
    
    //获得所有分类名称的key-value值
    public function getcateinfo(){
    	$catelist = $this->field('cate_id,cate_name')->select();
    	$cateinfo = array();
    	if ($catelist){
    		foreach($catelist as $item){
    			$cateinfo[$item['cate_id']] = $item['cate_name'];
    		}
    	}
    	return $cateinfo;
    }
    
    //转化查询出来的数据记录中的分类名称
    public function convertCatename($datalist){
    	//$cateinfo = $this->getcateinfo();
    	$cateinfo = $this->column('cate_name','cate_id');
    	if ($datalist){
    		foreach($datalist as &$item){
    			if (array_key_exists($item['cate_id'], $cateinfo)){
    				$item['cate_name'] = $cateinfo[$item['cate_id']];
    			} else {
    				$item['cate_name'] = '';
    			}
    		}
    	}
    	return $datalist;
    }
}
