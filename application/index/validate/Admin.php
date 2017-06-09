<?php
namespace app\index\validate;

use think\Validate;

class Admin extends Validate
{
    protected $rule = [
        ['account', 'require|length:1,16|alpha', '必须填写账号|账号长度不能超过16个字符|账号只能由英文组成'],
        'password', 'require|length:6,18|alphaDash', '必须填写密码|密码长度为6-18个字符|密码只能由英语、数字或下划线组成',
    ];
}