<?php
/**
 * Created by PhpStorm.
 * User: Ive
 * Date: 24/03/2018
 * Time: 2:54 PM
 */

namespace app\index\controller;


use think\Db;
use think\Exception;
use think\Request;

class Schedule extends MyController
{
    public function index()
    {
        // 用户表，返回 id-name
        $users = Db::table('users')->where('status', '1')->column('name', 'id');

        // 空课表
        $empSch = Db::table('empty_schedule')->field('mon,tue,wed,thu,fri')->select();
        for ($i = 0; $i < sizeof($empSch); $i++) {
            $empSch[$i] = array_values($empSch[$i]);
            for ($j = 0; $j < sizeof($empSch[$i]); $j++) {
                $tmpArr = explode(',', $empSch[$i][$j]);
                for ($k = 0; $k < sizeof($tmpArr); $k++) {
                    if (!empty($tmpArr[$k])) {
                        $tmpArr[$k] = $users[$tmpArr[$k]];
                    }
                }
                $empSch[$i][$j] = implode(',', $tmpArr);
            }
        }
        $this->assign(['empSch' => $empSch]);
        return $this->fetch('schedule');
    }

    public function update()
    {
        $post = Request::instance()->post();
        $idcard = $post['idcard'];
        $schedule = json_decode($post['schedule'], true);
        // 更新人员课表
        $data['mon'] = implode(',', $schedule[0]);
        $data['tue'] = implode(',', $schedule[1]);
        $data['wed'] = implode(',', $schedule[2]);
        $data['thu'] = implode(',', $schedule[3]);
        $data['fri'] = implode(',', $schedule[4]);
        $user = Db::table('users')->where("idcard", $idcard)->find();
        Db::table('schedule')->where('uid', $user['id'])->update($data);
        // 记录需要更新课表
        Db::table('sysconfig')->where('name', 'NEAD_UP_EMPTY_SCHEDULE')->update(['content' => 'true']);
        // 更新空课表
        try {
            //TODO: 添加事务锁
            $week = ['mon', 'tue', 'wed', 'thu', 'fri'];
            for ($i = 0; $i < 6; $i++) { // 节
                $section = Db::table('empty_schedule')->where('id', $i + 1)->find(); // id, mon - fri
                for ($j = 0; $j < 5; $j++) { // 星期
                    $box = explode(',', $section[$week[$j]]); // 课程格子
                    // 如果没课，定位空课表位置，添加人员ID
                    if ($data[$week[$j]][$i * 2] == 0) {
                        if (!in_array($user['id'], $box)) {
                            array_push($box, $user['id']);
                        }
                    } else {
                        // 有课且在里面要剔除
                        if (in_array($user['id'], $box)) {
                            unset($box[array_search($user['id'], $box)]);
                        }
                    }
                    $box = array_values($box); // 重置数组索引！
                    for ($k = 0; $k < sizeof($box); $k++) {
                        if ($box[$k] == '') {
                            unset($box[$k]);
                        }
                    }
                    $section[$week[$j]] = implode(',', $box);
                }
                Db::table('empty_schedule')->where('id', $i + 1)->update($section);
            }
        } catch (Exception $e) {
            return $e;
        }
        return true;
    }

    public function getByIdCard($idcard = '')
    {
        // 限制请求
        header("Access-Control-Allow-Origin: 127.0.0.1");
        if ($idcard) {
            $infoEntity = new Info();
            $info = $infoEntity->getInfo($idcard);
            if ($info) {
                $query = Db::table('schedule')->where('uid', $info['id'])->find();
                $schedule = [[], [], [], [], []];
                $query['mon'] = explode(',', $query['mon']);
                $query['tue'] = explode(',', $query['tue']);
                $query['wed'] = explode(',', $query['wed']);
                $query['thu'] = explode(',', $query['thu']);
                $query['fri'] = explode(',', $query['fri']);
                foreach ($query['mon'] as $e) {
                    switch ($e) {
                        case 0:
                            $e = '没课';
                            break;
                        case 1:
                            $e = '有课';
                            break;
                        case 2:
                            $e = '双周有课';
                            break;
                        case 3:
                            $e = '单周有课';
                            break;
                    }
                    array_push($schedule[0], $e);
                }
                foreach ($query['tue'] as $e) {
                    switch ($e) {
                        case 0:
                            $e = '没课';
                            break;
                        case 1:
                            $e = '有课';
                            break;
                        case 2:
                            $e = '双周有课';
                            break;
                        case 3:
                            $e = '单周有课';
                            break;
                    }
                    array_push($schedule[1], $e);
                }
                foreach ($query['wed'] as $e) {
                    switch ($e) {
                        case 0:
                            $e = '没课';
                            break;
                        case 1:
                            $e = '有课';
                            break;
                        case 2:
                            $e = '双周有课';
                            break;
                        case 3:
                            $e = '单周有课';
                            break;
                    }
                    array_push($schedule[2], $e);
                }
                foreach ($query['thu'] as $e) {
                    switch ($e) {
                        case 0:
                            $e = '没课';
                            break;
                        case 1:
                            $e = '有课';
                            break;
                        case 2:
                            $e = '双周有课';
                            break;
                        case 3:
                            $e = '单周有课';
                            break;
                    }
                    array_push($schedule[3], $e);
                }
                foreach ($query['fri'] as $e) {
                    switch ($e) {
                        case 0:
                            $e = '没课';
                            break;
                        case 1:
                            $e = '有课';
                            break;
                        case 2:
                            $e = '双周有课';
                            break;
                        case 3:
                            $e = '单周有课';
                            break;
                    }
                    array_push($schedule[4], $e);
                }
                return json($schedule);
            } else {
//                return json(['error'=>'idcarad']);
                return json([]);
            }
        } else {
            return json([]);
        }
    }
}