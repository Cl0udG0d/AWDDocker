<?php
namespace app\home\controller;
use think\Controller;
use think\Db;
use think\Request;

class Base extends Controller
{
    public function _initialize()
    {
        $current_request = Request::instance();
        $this->assign('current_controller', $current_request->controller());
        $this->assign('current_action', $current_request->action());
    }
    
    //获得省份信息列表
    public function getProvinceList(){
        return $provincelist = Db::name('ybqh')->distinct(true)->field('charProvince')->where("charArea<>'国外'")->order('charProvinceen asc')->select();
    }
    //获得某省份的城市信息列表
    public function getCityList(){
        header("Content-Type:text/html; charset=utf-8");
        $provinces = input("post.provinces");        
        $citylist = Db::name('ybqh')->distinct(true)->field('charCity,charPostCode,charPhoneCode')->where(" intActorid=0 and charProvince='{$provinces}'")->order('charCityen asc')->select();
        $data = array('data'=>$citylist);
        return $data;
    }
}
