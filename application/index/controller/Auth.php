<?php
/**
 * Created by PhpStorm.
 * User: Ive
 * Date: 24/03/2018
 * Time: 4:06 PM
 */

namespace app\index\controller;


use think\Db;
use think\Loader;
use think\Request;
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

    /**
     * 登录
     * @return mixed
     */
    public function login()
    {
        if (Request::instance()->post()) {
            $account = Request::instance()->post('account');
            $password = Request::instance()->post('password');
            $data = [
                'account' => $account,
                'password' => $password,
            ];
            $validate = Loader::validate('Admin');
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }
            $admin = Db::table("admin")->where('account', $account)->find();
            if ($admin) {
                if ($admin['password'] == md5($password)) {
                    $flag = md5((string)time() . $admin['password']);
                    Session::set('_lf', $flag);
                    Db::table('admin')->where('account', $admin['account'])->update(['flag' => $flag]);
                    $this->success('登录成功！', '/');
                } else {
                    $this->error('账号密码不正确');
                }
            } else {
                $this->error('账号密码不正确');
            }
        }
        return $this->fetch('login');
    }

    /**
     * 退出
     */
    public function logout()
    {
        Session::delete('_lf');
        $this->redirect('/');
    }
}