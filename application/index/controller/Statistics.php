<?php
/**
 * Created by PhpStorm.
 * User: Ive
 * Date: 24/03/2018
 * Time: 2:55 PM
 */

namespace app\index\controller;


class Statistics extends MyController
{
    public function index()
    {
        return $this->fetch('statistics');
    }
}