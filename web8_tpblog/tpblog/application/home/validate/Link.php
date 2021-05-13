<?php
namespace app\common\validate;
use think\Validate;

class Link extends Validate{

	protected $rule = array(
		'link_title' => 'require|max:50|min:6',
		'cate_id'    => 'require|number',		
		'link_order' => 'number',
	);
	protected $message = array(
		'link_title.require'=> '链接名称必须填写',
		'link_title.max'    => '链接名称最大长度为50个字符',
		'link_title.min'    => '链接名称最小长度为6个字符',		
		'cate_id.require'   => '链接分类必须填写',
		'cate_id.number'    => '链接分类必须为数字格式',		
		'link_order.number' => '链接排序必须为数字格式',
	);
	protected $scene = array(		
		'saveinfo'  => 'link_title,cate_id,link_order',
	);

}