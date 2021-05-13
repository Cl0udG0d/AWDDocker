<?php
namespace app\home\controller;
use app\home\model;
class Index extends Base
{
    public function index()
    { 
        $server = array(
			'操作系统'=>PHP_OS,
			'运行环境'=>$_SERVER["SERVER_SOFTWARE"],
			'PHP运行方式'=>php_sapi_name(),
			'ThinkPHP版本'=>THINK_VERSION,
			'上传附件限制'=>ini_get('upload_max_filesize'),
			'执行时间限制'=>ini_get('max_execution_time').'秒',
			'北京时间'=>gmdate("Y年n月j日 H:i:s",time()+8*3600),
			'服务器域名/IP'=>$_SERVER['SERVER_NAME'].' [ '.gethostbyname($_SERVER['SERVER_NAME']).' ]',
			'剩余空间'=>round((disk_free_space(".")/(1024*1024)),2).'M',
		);

        $c_option	= model('Option')->where("o_status=1 ")->count();
		$c_articles = model('Article')->where("art_status=1 ")->count();
		$c_category = model('Category')->where("cate_status=1 ")->count();
		$c_link		= model('Link')->where("link_status=1 ")->count();
		$this->assign('c_option',$c_option);
		$this->assign('c_articles',$c_articles);
		$this->assign('c_category',$c_category);
		$this->assign('c_link',$c_link);
		$this->assign('server',$server);
		return $this->fetch('index/index');
    }
}
