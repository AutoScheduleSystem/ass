<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
    // 全局变量规则定义
    '__pattern__' => [
        'name' => '\w+',
        'id' => '\d+',
        'year' => '\d{4}',
        'month' => '\d{2}',
        'password' => '[a-zA-Z0-9_-]{6-18}',
    ],
    'admin' => function () {
        return substr("220141222032", -11);
    },
    '[api]' => [
        'schedule/[:idcard]' => 'index/Index/getSchedule',
        'updateSchedule' => 'index/Index/updateSchedule',
        'del/[:idcard]' => 'index/Admin/delUser',
        'generate' => 'index/Admin/generate',
        'updateConfig/[:name]/[:content]' => 'index/Admin/updateConfig',
    ],
    'duty' => 'index/Index/duty',
    'schedule' => 'index/Index/schedule',
    'info' => 'index/Index/info',
    'statistics' => 'index/Index/statistics',
    'login' => 'index/Index/login',
    'logout' => 'index/Index/logout',
    'record' => 'index/Index/record',
    'go' => 'index/Index/go',
    'golist' => 'index/Index/golist',

    'config' => 'index/Admin/config',
];
