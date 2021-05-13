<?php
namespace app\common\validate;
use think\Validate;

class Option extends Validate{

	protected $rule = array(
		'o_name' => 'require|max:50|min:6',		
	);
	protected $message = array(
		'o_name.require'=> '配置项名称必须填写',
		'o_name.max'    => '配置项名称最大长度为50个字符',
		'o_name.min'    => '配置项名称最小长度为6个字符',		
	);
	protected $scene = array(		
		'saveinfo'  => 'o_name',
	);

}