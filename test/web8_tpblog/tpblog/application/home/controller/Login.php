<?php
namespace app\home\controller;
use think\Controller;
use think\Db;
class Login extends Controller
{
    public function login()
    { 
        if (session('m_id')>0){            
            $this->success('登录成功，进入系统……','home/index/index');            
        } else {
            return $this->fetch('login/login');
        }
    }
    
    //用户登录
    public function loginon()
    {         
        $data = array();
        $data['m_email'] = input('post.username');
        $data['m_psw'] = input('post.password');
        if (empty($data)) {
            $this->error('用户名或者密码不能为空！','home/login/login');
        }
        $result = $this->validate($data,'Member.login');
        if(true !== $result){
            // 验证失败 输出错误信息
            $this->error($result,'home/login/login');
        } else {
            $member = Db::name('member')->field('m_id,m_email,m_name,m_gender,m_icon,m_state,m_lastlogin,m_level')->where($data)->find();
            //echo Db::getlastsql();
            if ($member){
                foreach($member as $k=>$v){
                    session(strtolower($k),strtolower($v));                
                }             
                if(session("http_referer")){
                    $success_url = session("http_referer");
                } else {
                    $success_url = 'home/index/index';
                }
                unset($member);
                $this->success('登录成功',$success_url);
            } else {
                $this->error('请检查您输入的用户名和密码','home/login/login');
            }
        }        
    }
    
    //用户退出登录
    public function logout(){
        session(null);
        $this->success('退出登录成功，跳转中……','home/login/login');
    }
}
