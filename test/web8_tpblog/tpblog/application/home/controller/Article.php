<?php
namespace app\home\controller;
use app\home\model;
class Article extends Base
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('Article');
    }
    
    //信息列表
    public function index()
    { 
    	$where = '';
    	$art_title = input('param.art_title','');
    	$cate_id = input('param.cate_id','');
    	if ($art_title){
    		$where = [ 'art_title' => ['like',"%{$art_title}%"] ];
    	}
    	if ($cate_id){
    		$where = [ 'cate_id' => $cate_id ];
    	}
    	
    	$datalist = $this->model->field('art_id,art_title,cate_id,art_ctime')->where($where)->order('art_id desc')->paginate(10);
    	//把分类id转化成分类名称
    	$datalist = model('Category')->convertCatename($datalist);
        //dump($datalist);
    	$catelist = model('Category')->getcatetreearr('article');
        $this->assign('art_title', $art_title);
        $this->assign('cate_id', $cate_id);
        $this->assign('datalist', $datalist);
        $this->assign('catelist',$catelist);
        return $this->fetch('article/index');
    }
    
    //新增/编辑
    public function edit(){    	
    	$art_id = input('param.art_id');
    	$info = $this->model->where("art_id='{$art_id}'")->find();
    	$catelist = model('Category')->getcatetreearr('article');
    	$this->assign('info',$info);
    	$this->assign('catelist',$catelist);
    	return  $this->fetch('article/edit');
    }
    
    //信息保存
    public function saveinfo(){
    	$data = input('post.');
    	$data['art_status'] = input('post.art_status',0);
    	$validate = $this->validate($data,'Article.saveinfo');
    	if(true !== $validate){
    		$this->error($validate);
    	} else {
    		$art_id = input('post.art_id');
    		if($art_id>0){
    			$result = $this->model->where('art_id',$art_id)->update($data);
    		} else {
    			$data['art_ctime'] = time();
    			$result = $this->model->data($data)->save();
    		}
    		if(false !== $result){
    			$this->success('保存信息成功','home/article/index');
    		}
    	}
	}
	    
    //信息删除
    public function removeinfo(){
    	$art_id = input('param.art_id');
    	$result = $this->model->where('art_id',$art_id)->delete();
    	if(false !== $result){
    		$data = [
    			'status' => 0,
    			'msg' => '文章删除成功！',
    		];
    	}else {
    		$data = [
    			'status' => 1,
    			'msg' => '文章删除失败！',
    		];
    	}
    	echo json_encode($data);
	}
	
	public function getImage(){
		$url=input('param.url');
		if(preg_match('/^(ftp|zlib|data|glob|phar|ssh2|compress.bzip2|compress.zlib|rar|ogg|expect)(.|\\s)*|(.|\\s)*(file|data|\.\.)(.|\\s)*/i',$url)){
			echo json_encode([
				'status' => 0,
				'msg' => '解析失败'
			]);
		}
		else{
			$content=file_get_contents($url);
			// something todo
			echo json_encode([
				'status' => 0,
				'msg' => 'not finished yet'
			]);
		}
	}
}
