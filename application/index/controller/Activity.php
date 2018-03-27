<?php
/**
 * Created by PhpStorm.
 * User: Ive
 * Date: 24/03/2018
 * Time: 2:55 PM
 */

namespace app\index\controller;


use think\Db;
use think\Request;

class Activity extends MyController
{
    // 活动页面
    public function index()
    {
        if (Request::instance()->post()) {
            $name = Request::instance()->post('name');
            $name = htmlspecialchars($name);
            Db::table('go')->insert(['name' => $name]);
        }
        return $this->fetch('activity');
    }

    public function goList()
    {
        $data = Db::table('go')->select();
        foreach ($data as $e) {
            echo $e['name'] . '<br>';
        }
    }

    // 重置活动
    public function reset()
    {

    }
}