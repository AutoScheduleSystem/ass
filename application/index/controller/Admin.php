<?php

namespace app\index\controller;

use think\Controller;
use think\Db;
use think\Request;
use think\Session;

class Admin extends Controller
{
    public $sysconfig = [];
    public $isLogin = false;

    public function _initialize()
    {
        // CMS系统配置
        $query = Db::table('sysconfig')->select();
        $this->sysconfig = [];
        foreach ($query as $e) {
            $this->sysconfig[$e['name']] = $e['content'];
        }
        $this->assign($this->sysconfig);

        // 取得年级列表
        $grade = Db::table('users')->distinct(true)->field('grade')->order('grade')->select();
        $this->assign('grade', $grade);


        // 判断是否登录

        $this->isLogin = $this->checkLogin();
        $this->assign('isLogin', $this->isLogin);
        if (!$this->isLogin) {
            $this->redirect('/login');
        }

    }

    // 检查是否登录
    public static function checkLogin()
    {
        $flag = Session::get('_lf');
        if ($flag) {
            $query = Db::table('admin')->where('flag', $flag)->find();
            if ($query) {
                return true;
            }
        }
        return false;
    }

    //登记人员信息
    public function record()
    {
        if ($this->sysconfig['RECORDING'] == "false") {
            if (!$this->isLogin) {
                $this->redirect('/login');
                return 0;
            }
        }

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
                'record_ip' => Tool::getIP(),
                'record_agent' => Tool::getAgent(),
            ];
            var_dump($data);
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

    // 系统配置页面
    public function config()
    {
        $this->assign('sysconfig', $this->sysconfig);
//        var_dump($this->sysconfig);
        return $this->fetch('config');
    }

    // 更新配置
    public function updateConfig($name = '', $content = '')
    {
        header("Access-Control-Allow-Origin: 127.0.0.1");
//        var_dump($this->sysconfig);
        if ($name) {
            $index = $this->sysconfig[$name];
            if ($index == 'true') {
                Db::table('sysconfig')->where('name', $name)->update(['content' => 'false']);
            } else if ($index == 'false') {
                Db::table('sysconfig')->where('name', $name)->update(['content' => 'true']);
            } else {
                Db::table('sysconfig')->where('name', $name)->update(['content' => $content]);
            }
        }
    }

    // 删除人员
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

    /**
     * 最重要的生成值班表功能，成就值：五颗星！٩( •̀㉨•́ )و
     *
     */
    public function generate()
    {
        // 取白天空课表数据
        $empSch = Db::table('empty_schedule')->where('id', '<=', '4')->select();

        // 数据模型
        // $empSch[0-3]['mon']
        //     0 1  2  3  4 5
        // 0 [ 1 一 二 三 四 五 ]
        // 1 [ 2 一 二 三 四 五 ]
        // 2 [ 3 一 二 三 四 五 ]
        // 3 [ 4 一 二 三 四 五 ]

        $rank = []; // 人员rank
        $psum = []; // 人员数
        for ($i = 0; $i < sizeof($empSch); $i++) { // 节
            $empSch[$i] = array_values($empSch[$i]); // 列 关联数组变为索引数组
            $rank[$i] = [];
            $psum[$i] = [];
            array_splice($empSch[$i], 0, 1);
            for ($j = 0; $j < sizeof($empSch[$i]); $j++) { // 周一到周五
                $this->strtoarr($empSch[$i][$j]); // string to array
                $rank[$i][$j] = [];
                $psum[$i][$j] = sizeof($empSch[$i][$j]); // 初始化人员数
                for ($e = 0; $e < $psum[$i][$j]; $e++) {
                    //格式：rank[节][周][第n个人]
                    $rank[$i][$j][$e] = 0; // 初始化人员rank为0
                }
            }
        }

        /**
         * 思路：
         *      获取数据
         *      处理数据：$empSch, $rank, $psum
         *      按人数排序数组
         *      按顺序处理每组
         */


//        echo "<pre>";
//        echo "<h1>empSch</h1>";
//        print_r($empSch);

//        echo "<h1>rank</h1>";
//        print_r($rank);

//        echo "<h1>psum</h1>";
//        print_r($psum);


        $sch = null;    // 结果集
        $this->solve3(4, 5, $empSch, $rank, $psum, $sch);
        $this->submitDuty($sch);
    }

    // 字符串转数组
    public function strtoarr(&$str)
    {
        $str = explode(',', $str);
        return $str;
    }

    // 数组转字符串
    public function arrtostr(&$arr)
    {
        $arr = implode(',', $arr);
        return $arr;
    }

    /**
     * 递归回溯生成值班表
     *
     * @param $s section 节
     * @param $w week 周
     * @param $empSch 空课表
     * @param $rank 人员rank
     * @param $psum 每个格子的人员数 sizeof($empSch[$i][$j])
     * @param $sch 返回的值班表
     */
    public function solve1($s, $w, $empSch, &$rank, $psum, &$sch)
    {
        // s:0-3 w:s,1-5
        if ($s > 3 && $w > 5) {
            return;
        } else {
            for ($i = 0; $i < $psum[$s][$w]; $i++) {
                if (true) { // 约束条件
                    // 操作

                    // 继续探索
                    if ($w == 5) {
                        $this->solve($s + 1, 1);
                    } else {
                        $this->solve($s, $w + 1);
                    }

                    // 回溯

                }

            }
        }
    }


    /**
     * @param $s = 4 节
     * @param $w = 5 天
     * @param $empSch 空课表
     * @param $rank rank表
     * @param $psum
     * @param $sch 结果集
     */
    public function solve2($s, $w, $empSch, &$rank, $psum, &$sch)
    {
        $s--; // sum of section to section index
        for ($j = 1; $j <= $w; $j++) { // week array index 1-5
            trysolve($s, $j);  // section index 0-3, so $s-1. solve from bottom
            if (check()) {
                if ($s == 0 && $w == 5) return;
                $this->solve2($s - 1, $w);
            }
            backsolve($s, $j);
        }
    }

    /**
     * 优先级rank排班算法
     * @param $s = 4 节
     * @param $w = 5 天
     * @param $empSch 空课表
     * @param $rank rank表
     * @param $psum 人员数二维数组
     * @param $sch 结果集
     */
    public function solve3($s, $w, $empSch, &$rank, $psum, &$sch)
    {
        $sorti = [];  // index of every box 位置
        $newPsum = [];

        $k = 0;
        for ($i = 0; $i < $s; $i++) {
            for ($j = 0; $j < $w; $j++) {
                $sorti[$k] = $k;
                $newPsum[$k] = $psum[$i][$j];   // psum 二维数组转一维数组！
                $k++;
            }
        }

//        print_r($newPsum);
//        echo "<br>";
//        print_r($sorti);
//        echo "<br>";

        // 按人数把所有box排序，人数少的值班段优先安排
        array_multisort($newPsum, $sorti);

//        log("newPsum", $newPsum);
//        log("sorti", $sorti);


        // 初始化rank
        $rank = [];
        for ($i = 0; $i < 100; $i++) {
            $rank[$i] = 0;
        }

        for ($i = 0; $i < sizeof($sorti); $i++) {
            $p = $sorti[$i]; // 实际box位置
            $mans = $empSch[$p / $w][$p % $w];  // 该值班段所有人(星期，节)
            // step 1. 安排值班
            // step 2. 删除其它值班段值班人员 或 依据rank值排序再安排
            if (sizeof($mans) > 0) {
                // 冒泡排序：按rank值对该值班段人按rank小到大排序
                for ($s = 0; $s < sizeof($mans); $s++) {
                    for ($y = $s + 1; $y < sizeof($mans); $y++) {
                        if ($rank[$mans[$s]] > $rank[$mans[$y]]) {
                            $tmp = $mans[$s];
                            $mans[$s] = $mans[$y];
                            $mans[$y] = $tmp;
                        }
                    }
                }

                // shuffle($mans); // 随机排序
                $sch[$p] = array_slice($mans, 0, $this->sysconfig['SCH_NUM']);    // 取两个值班人员，安排值班

                @(sizeof($sch[$p]) > 0) && @$rank[$sch[$p][0]]++;
                @(sizeof($sch[$p]) > 1) && @$rank[$sch[$p][1]]++;
            }
        }
        ksort($sch);
//        log("sch", $sch);
//        log('rank', $rank);
    }


    /**
     * 提交值班表数据
     * @param $sch
     */
    public function submitDuty($sch)
    {
        $users = Db::table('users')->column('name', 'id');
//        print_r($users);
        /*
        echo "<pre>";
        for ($i = 0; $i < sizeof($sch); $i++) {
            for ($j = 0; $j < sizeof($sch[$i]); $j++) {
                echo $users[$sch[$i][$j]] . ' ';
            }
            echo '|';
            if ($i % 5 == 4) echo '<br>';
        }
        */

        $data = [];
        for ($i = 0; $i < sizeof($sch); $i++) {
            for ($j = 0;$j<sizeof($sch[$i]);$j++){
                @$sch[$i][$j] = $users[$sch[$i][$j]];
            }
            $data[$i] = $this->arrtostr($sch[$i]);
        }
        $data = array_chunk($data, 5);
        for ($i = 0; $i < sizeof($data); $i++) {
//            array_unshift($data[$i], $i + 1);
            Db::table('duty')->update([
                'id' => $i + 1,
                'mon' => $data[$i][0],
                'tue' => $data[$i][1],
                'wed' => $data[$i][2],
                'the' => $data[$i][3],
                'fri' => $data[$i][4],
            ]);
        }
        echo "<pre>";
//        print_r($sch);
        print_r($data);
    }
}

function log($name, $p)
{
    echo "<h1>" . $name . "</h1>";
    print_r($p);
}