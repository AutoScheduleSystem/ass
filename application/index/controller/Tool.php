<?php

namespace app\index\controller;


use think\Controller;

class Tool extends Controller
{
    public static function getIP()
    {
        if (getenv("HTTP_CLIENT_IP"))
            $ip = getenv("HTTP_CLIENT_IP");
        else if (getenv("HTTP_X_FORWARDED_FOR"))
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        else if (getenv("REMOTE_ADDR"))
            $ip = getenv("REMOTE_ADDR");
        else
            $ip = "Unknow";
        return $ip;
    }

    public static function getAgent()
    {
        return $_SERVER['HTTP_USER_AGENT'];
    }

    function log($name, $p)
    {
        echo "<h1>" . $name . "</h1>";
        print_r($p);
    }
}