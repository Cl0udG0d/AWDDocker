<?php
namespace app\common\validate;
use think\Validate;

class Article extends Validate{

	protected $rule = array(
		'art_title' => 'require|max:50|min:6',
		'cate_id'  	=> 'require|number',		
		'art_order' => 'number',
	);
	protected $message = array(
		'art_title.require'=> '文章标题必须填写',
		'art_title.max'    => '文章标题最大长度为50个字符',
		'art_title.min'    => '文章标题最小长度为6个字符',		
		'cate_id.require'  => '文章分类必须填写',
		'cate_id.number'   => '文章分类必须为数字格式',		
		'art_order.number' => '文章排序必须为数字格式',
	);
	protected $scene = array(		
		'saveinfo'  => 'art_title,cate_id,art_order',
	);

}