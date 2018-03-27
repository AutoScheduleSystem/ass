<?php
/**
 * Created by PhpStorm.
 * User: Ive
 * Date: 24/03/2018
 * Time: 11:37 AM
 */

namespace app\index\controller;

use think\Controller;

class Test extends MyController
{
    public function index()
    {
        var_dump($this->sysconfig);
        echo "hello";
    }
}