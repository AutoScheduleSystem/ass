<?php

namespace app\index\controller;

use think\config\driver\Json;
use think\Controller;
use think\Db;
use think\Loader;
use think\Request;
use think\Session;

class Index extends Controller
{
    public $sysconfig = [];

    /**
     * 初始操作
     */
    public function _initialize()
    {
        // CMS系统配置
        $query = Db::table('sysconfig')->select();
        foreach ($query as $e) {
            $this->sysconfig[$e['name']] = $e['content'];
        }
        $this->assign($this->sysconfig);
        // 取得年级列表
        $grade = Db::table('users')->distinct(true)->field('grade')->order('grade')->select();
        $this->assign('grade', $grade);
        // 判断是否登录
        $isLogin = Admin::checkLogin();
        $this->assign('isLogin', $isLogin);
    }

    /**
     * 首页是值班表
     * @return mixed
     */
    public function index()
    {
        // TODO: 为什么不能用 $this->duty();
        $data = Db::table('duty')->select();
        for ($i = 0; $i < sizeof($data); $i++) {
            $data[$i] = array_values($data[$i]);
        }
        $this->assign('duty', $data);
        return $this->fetch('duty');
    }

    /**
     * 值班表页面
     * @return mixed
     */
    public function duty()
    {
        // TODO: data变量名不能为duty，与函数冲突但是没有提示？
        $data = Db::table('duty')->select();
        for ($i = 0; $i < sizeof($data); $i++) {
            $data[$i] = array_values($data[$i]);
        }
        $this->assign('duty', $data);
        return $this->fetch('duty');
    }

    /**
     * 空课表页面
     * @return mixed
     */
    public function schedule()
    {
        // 用户表，返回 id-name
        $users = Db::table('users')->where('status', '1')->column('name', 'id');
        // 空课表
        $empSch = Db::table('empty_schedule')->select();
        for ($k = 0; $k < sizeof($empSch); $k++) {
            if ($empSch[$k]['mon'] != null) {
                $idArr = explode(',', $empSch[$k]['mon']);
                for ($i = 0; $i < sizeof($idArr); $i++) {
                    $idArr[$i] = $users[$idArr[$i]];
                }
                $empSch[$k]['mon'] = implode(',', $idArr);
            }
            if ($empSch[$k]['tue'] != null) {
                $idArr = explode(',', $empSch[$k]['tue']);
                for ($i = 0; $i < sizeof($idArr); $i++) {
                    $idArr[$i] = $users[$idArr[$i]];
                }
                $empSch[$k]['tue'] = implode(',', $idArr);
            }
            if ($empSch[$k]['wed'] != null) {
                $idArr = explode(',', $empSch[$k]['wed']);
                for ($i = 0; $i < sizeof($idArr); $i++) {
                    $idArr[$i] = $users[$idArr[$i]];
                }
                $empSch[$k]['wed'] = implode(',', $idArr);
            }
            if ($empSch[$k]['thu'] != null) {
                $idArr = explode(',', $empSch[$k]['thu']);
                for ($i = 0; $i < sizeof($idArr); $i++) {
                    $idArr[$i] = $users[$idArr[$i]];
                }
                $empSch[$k]['thu'] = implode(',', $idArr);
            }
            if ($empSch[$k]['fri'] != null) {
                $idArr = explode(',', $empSch[$k]['fri']);
                for ($i = 0; $i < sizeof($idArr); $i++) {
                    $idArr[$i] = $users[$idArr[$i]];
                }
                $empSch[$k]['fri'] = implode(',', $idArr);
            }
        }
        $this->assign(['empSch' => $empSch]);
        return $this->fetch('schedule');
    }

    /**
     * 取得课表信息API
     * @param string $idcard
     * @return Json
     */
    public function getSchedule($idcard = '')
    {
        // 限制请求
        header("Access-Control-Allow-Origin: 127.0.0.1");
        if ($idcard) {
            $info = $this->getInfo($idcard);
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

    /**
     * 更新课表API
     * @return bool
     */
    public function updateSchedule()
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

    /**
     * 人员信息页面
     * @return mixed
     */
    public function info()
    {
        $this->checkRedirect('SHOW_INFOS');
//        echo "<pre>";
//        var_dump($this->getInfo());
//        echo "</pre>";
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

    /**
     * 取得人员信息函数
     * @param string $idcard
     * @param string $grade
     * @return array|false|\PDOStatement|string|\think\Collection|\think\Model
     */
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

    /**
     * 人员统计
     * @return mixed
     */
    public function statistics()
    {
        return $this->fetch('statistics');
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

    //登记人员信息
    public function record()
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

    // 验证系统配置跳转到登录页面
    public function checkRedirect($config)
    {
        if ($this->sysconfig[$config] == 'false' && !Admin::checkLogin()) {
            $this->redirect('/login');
        }
    }

    // 活动页面
    public function go()
    {
        if (Request::instance()->post()) {
            $name = Request::instance()->post('name');
            $name = htmlspecialchars($name);
            Db::table('go')->insert(['name' => $name]);
        }
        return $this->fetch('go');
    }

    public function golist()
    {
        $data = Db::table('go')->select();
        foreach ($data as $e){
            echo $e['name'].'<br>';
        }
    }
}
