<?php
/**
 * Created by PhpStorm.
 * User: Ive
 * Date: 24/03/2018
 * Time: 2:52 PM
 */

namespace app\index\controller;


use think\Db;

class Duty extends MyController
{
    public function index()
    {
        // TODO: data变量名不能为duty，与函数冲突但是没有提示？
        $data = Db::table('duty')->select();
        for ($i = 0; $i < sizeof($data); $i++) {
            $data[$i] = array_values($data[$i]);
        }
        $this->assign('duty', $data);
        return $this->fetch('duty');
    }
}