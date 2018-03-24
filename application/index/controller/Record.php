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

class Record extends MyController
{
    public function index()
    {
        $this->checkRedirect('RECORDING');
        if (Request::instance()->post()) {
            $idcard = substr(Request::instance()->post('idcard'), -11);
            $photo = "http://iplat.ujn.edu.cn/PHOTO/" . substr($idcard, 0, 4) . "/" . $idcard . ".jpg";
            $data = [
                'idcard' => $idcard,
                'photo' => $photo,
                'name' => Request::instance()->post('name'),
                'gender' => Request::instance()->post('gender'),
                'grade' => substr($idcard, 0, 4),
                'college' => Request::instance()->post('college'),
                'tel' => Request::instance()->post('tel'),
                'depart' => Request::instance()->post('depart'),
                'record_ip' => Utils::getIP(),
                'record_agent' => Utils::getAgent(),
            ];
            // 开启事务
            Db::startTrans();
            try {
                // 插入用户
                $query = Db::table('users')->insert($data);
                // 生成默认课表
                $userId = Db::name('users')->getLastInsID();
                Db::table('schedule')->insert(['uid' => $userId]);
                if ($query) {
                    Db::commit();
                    return $this->success('登记成功！');
                } else {
                    Db::rollback();
                    return $this->error('系统错误！');
                }
            } catch (Exception $e) {
                Db::rollback();
                return $this->error('系统错误');
            }
        }
        $college = Db::table('college')->order('college_code')->select();
        $this->assign('college', $college);
        return $this->fetch('record');
    }
}