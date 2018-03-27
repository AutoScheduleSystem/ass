<?php
/**
 * Created by PhpStorm.
 * User: Ive
 * Date: 24/03/2018
 * Time: 3:02 PM
 */

namespace app\index\controller;


use think\Controller;
use think\Db;

class MyController extends Controller
{
    public $sysconfig = [];

    public function _initialize()
    {
        // CMS系统配置
        $query = Db::table('sysconfig')->select();
        foreach ($query as $e) {
            $this->sysconfig[$e['name']] = $e['content'];
        }
        $this->assign('sysconfig', $this->sysconfig);
        // 取得年级列表
        $grade = Db::table('users')->distinct(true)->field('grade')->order('grade')->select();
        $this->assign('grade', $grade);
        // 判断是否登录
        $isLogin = Auth::checkLogin();
        $this->assign('isLogin', $isLogin);
    }

    // 配置开关鉴权
    public function checkRedirect($config)
    {
        if ($this->sysconfig[$config] == 'false' && !Auth::checkLogin()) {
            $this->redirect('/login');
        }
    }

    // 未登录鉴权
    public function auth()
    {
        if (!Auth::checkLogin()) {
            $this->redirect('/login');
        }
    }
}