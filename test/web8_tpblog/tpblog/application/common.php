<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

//根据当前的控制器和操作给对应的菜单加样式
function nav_active($conf_controller,$conf_action,$current_controller,$current_action){	
    if ( $conf_controller==$current_controller && strpos($conf_action,$current_action)!==false ) {
            echo 'active_tab';
    }
}