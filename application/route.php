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

    // Test
    'idCard' => function () {
        return substr("220141222032", -11);
    },
    'test' => 'Test',

    // API
    '[api]' => [
        'schedule/[:idcard]' => 'index/Schedule/getByIdCard',
        'updateSchedule' => 'index/Schedule/update',
        'del/[:idcard]' => 'index/Info/delUser',
        'generate' => 'index/Settings/generate',
        'updateConfig/[:name]/[:content]' => 'index/Settings/update',
    ],

    // Pages
    'duty' => 'Duty/index',
    'schedule' => 'Schedule/index',
    'info' => 'Info/index',
    'activity' => 'Activity/index',
    'goList' => 'Activity/goList',
    'record' => 'Record/index',
    'statistics' => 'Statistics/index',
    'settings' => 'Settings/index',

    // Auth
    'login' => 'Auth/login',
    'logout' => 'Auth/logout',
];
