<?php
namespace app\home\controller;
use app\home\model;
class System extends Base
{
    public function _initialize()
    {
        parent::_initialize();
        //$this->model = model('Link');
    }
    
    //信息列表
	public function unzip()
	{
		//清除系统缓存

	}
}
