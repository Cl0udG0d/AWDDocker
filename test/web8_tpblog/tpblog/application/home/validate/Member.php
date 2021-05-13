<?php
namespace app\common\validate;
use think\Validate;

class Member extends Validate{

	protected $rule = array(
		'm_email'   => 'require|max:40|min:9|email',
		'm_psw'     => 'require|max:30|min:6',
		'm_pswold'  => 'require|max:30|min:6',
		'm_pswre'   => 'require|max:30|min:6|confirm:m_psw',
		'm_gender'  => 'require',
		'm_card'    => ['regex' =>'/^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{4}$/'],
        'm_mobile'  => ['regex' =>'/^1(3[0-9]|4[57]|5[0-35-9]|7[01678]|8[0-9])\\d{8}$/'],
        'm_comapny' => 'length:6,120',
        'm_department'=> 'length:3,90',
        'm_title'   => 'length:3,30',
        'm_province'=> 'length:2,60',
        'm_city'    => 'length:2,60',
        'm_address' => 'length:2,120',
        'm_zipcode' => ['regex' =>'/^[1-9]{1}(\d+){5}$/'],
        'm_phone'   => ['regex' =>'/^((0\d{2,3}-\d{7,8})|(1[3584]\d{9}))$/'],
        'm_fax'     => ['regex' =>'/^((0\d{2,3}-\d{7,8})|(1[3584]\d{9}))$/'],
	);
	protected $message = array(
		'm_email.require'=> '登录名必须填写',
		'm_email.max'    => '登录名最大长度为40个字符',
		'm_email.min'    => '登录名最小长度为9个字符',
		'm_email.email'  => '登录名必须为邮件格式',
		'm_psw.require'  => '密码必须填写',
		'm_psw.max'      => '密码最大长度为30个字符',
		'm_psw.min'      => '密码最小长度为6个字符',
        'm_pswold.require'=> '原始密码必须填写',
		'm_pswold.max'   => '原始密码最大长度为30个字符',
		'm_pswold.min'   => '原始密码最小长度为6个字符',
        'm_pswre.require'=> '确认密码必须填写',
		'm_pswre.max'    => '确认密码最大长度为30个字符',
		'm_pswre.min'    => '确认密码最小长度为6个字符',
		'm_pswre.confirm'=> '输入的确认密码与密码不一致',
	);
	protected $scene = array(
		'login'     => 'm_email,m_psw',
		'saveinfo'  => 'm_gender,m_card,m_mobile,m_comapny,m_department,m_title,m_province,m_city,m_address,m_zipcode,m_phone,m_fax',
		'password'  => 'm_psw,m_pswold,m_pswre'
	);

}