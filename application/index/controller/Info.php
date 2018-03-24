<?php
/**
 * Created by PhpStorm.
 * User: Ive
 * Date: 24/03/2018
 * Time: 2:54 PM
 */

namespace app\index\controller;


use think\Db;
use think\Request;

class Info extends MyController
{
    public function index()
    {
        $this->checkRedirect('SHOW_INFOS');
        if (Request::instance()->get('grade')) {
            $this->assign('info', $this->getInfo('', Request::instance()->get('grade')));
        } else {
            $this->assign('info', $this->getInfo());
        }
        // 获取学院-编号信息
        $query = Db::table('college')->select();
        $college = [];
        foreach ($query as $e) {
            $college[$e['college_code']] = $e['college_name'];
        }
        $this->assign('college', $college);
        // 获取部门-编号信息
        $query = Db::table('department')->select();
        $department = [];
        foreach ($query as $e) {
            $department[$e['depart_code']] = $e['depart_name'];
        }
        $this->assign('department', $department);
        return $this->fetch('info');
    }

    public function delUser($idcard = '')
    {
        header("Access-Control-Allow-Origin: 127.0.0.1");
        if ($idcard) {
            Db::table('users')->where('idcard', $idcard)->update(['status' => '0']);
            // 删除空课表
            $uid = Db::table('users')->where('idcard', $idcard)->value('id');
            $empSch = Db::table('empty_schedule')->select();
            for ($k = 0; $k < sizeof($empSch); $k++) {
                $this->empReplace($empSch[$k]['mon'], $uid);
                $this->empReplace($empSch[$k]['tue'], $uid);
                $this->empReplace($empSch[$k]['wed'], $uid);
                $this->empReplace($empSch[$k]['thu'], $uid);
                $this->empReplace($empSch[$k]['fri'], $uid);
                Db::table('empty_schedule')->where('id', $empSch[$k]['id'])->update($empSch[$k]);
            }
        }
    }

    // 删除人员时，处理空课表的函数
    public function empReplace(&$box, $uid)
    {
        if (!!strstr($box, $uid . ',')) {
            $box = str_replace($uid . ',', '', $box);
        } elseif (!!strstr($box, ',' . $uid)) {
            $box = str_replace(',' . $uid, '', $box);
        } else {
            $box = str_replace($uid, '', $box);
        }
    }

    //
    public function getInfo($idcard = '', $grade = '')
    {
        if ($grade) {
            $query = Db::table('users')->where('status', '1')->where('grade', $grade)->select();
            return $query ? $query : [];
        }
        if ($idcard) {
            $query = Db::table('users')->where('status', '1')->where('idcard', $idcard)->find();
        } else {
            $query = Db::table('users')->where('status', '1')->order('grade,idcard')->select();
        }
        return $query == null ? [] : $query;
    }
}