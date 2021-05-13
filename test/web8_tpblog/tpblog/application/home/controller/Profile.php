<?php
namespace app\home\controller;
use think\Controller;
use think\Db;
class Profile extends Base
{
    //个人信息编辑页面
    public function index()
    { 
        $info = Db::name('member')->where(['m_id' => session('m_id')])->find();
        //dump($info);
        $province = $this->getProvinceList();
        $this->assign('info',$info);
        $this->assign('province',$province);
        return $this->fetch('profile/index');
    }
    
    //信息保存
    public function saveinfo(){
        $data = input('post.');
        $validate = $this->validate($data,'Member.saveinfo');
        if(true !== $validate){
            $this->error($validate);
        } else {
            $result = Db::name('member')->where('m_id',session('m_id'))->update($data);
            if(false !== $result){
                $this->success('保存信息成功','home/profile/index');
            }
        }
    }
    
    //修改密码页面
    public function editpsw(){
        return $this->fetch('profile/editpsw');
    }
    
    //保存密码
    public function savepsw(){
        $data = input('post.');
        $validate = $this->validate($data,'Member.password');
        if(true !== $validate){
            $this->error($validate);
        } else {
            $m_pswold = Db::name('member')->where('m_id',session('m_id'))->value('m_psw');
            if ( $m_pswold != $data['m_pswold'] ){
                $this->error('输入的原始密码与系统中存储的密码不一致');
            } else {                
                $result = Db::name('member')->where('m_id',session('m_id'))->update(['m_psw' => $data['m_psw']]);
                if(false !== $result){
                    $this->success('保存信息成功','home/profile/editpsw');
                }
            }
        }
    }
}
