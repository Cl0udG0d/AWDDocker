<?php
namespace app\common\validate;
use think\Validate;

class Category extends Validate{

	protected $rule = array(
		'cate_mod'  => 'require',
		'cate_name' => 'require|max:30|min:2',
		'root_id'  	=> 'require|number',
		'cate_url'  => 'require|max:100|min:6',
		'cate_order'=> 'require|number',
	);
	protected $message = array(
		'cate_mod.require' => '分类类型必须填写',
		'cate_name.require'=> '分类名称必须填写',
		'cate_name.max'    => '分类名称最大长度为30个字符',
		'cate_name.min'    => '分类名称最小长度为6个字符',		
		'root_id.require'  => '上级分类必须填写',
		'root_id.number'   => '上级分类必须为数字格式',
        'cate_url.require' => '自定义URL必须填写',
		'cate_url.max'     => '自定义URL最大长度为100个字符',
		'cate_url.min'     => '自定义URL最小长度为6个字符',
		'cate_order.require'=> '分类排序必须填写',
		'cate_order.number'=> '分类排序必须为数字格式',
	);
	protected $scene = array(		
		'saveinfo'  => 'cate_mod,cate_name,root_id,cate_url,cate_order',
	);

}