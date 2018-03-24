<?php
/**
 * Created by PhpStorm.
 * User: Ive
 * Date: 24/03/2018
 * Time: 4:06 PM
 */

namespace app\index\controller;


use think\Session;

class Auth extends MyController
{
    // 未登录跳转
    public function auth()
    {
        if (!$this->checkLogin()) {
            $this->redirect('/login');
        }
    }

    // 检查是否登录
    public static function checkLogin()
    {
        $flag = Session::get('_lf');
        if ($flag) {
            $query = Db::table('admin')->where('flag', $flag)->find();
            if ($query) {
                return true;
            }
        }
        return false;
    }
}