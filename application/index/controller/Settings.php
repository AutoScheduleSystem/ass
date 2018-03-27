<?php
/**
 * Created by PhpStorm.
 * User: Ive
 * Date: 24/03/2018
 * Time: 3:53 PM
 */

namespace app\index\controller;


use think\Db;

class Settings extends MyController
{
    private $utils = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->utils = new Utils();
    }

    public function index()
    {
        return $this->fetch('settings');
    }

    // 更新配置
    public function update($name = '', $content = '')
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

    /**
     * 最重要的生成值班表功能，成就值：五颗星！٩( •̀㉨•́ )و
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
                $this->utils->strToArr($empSch[$i][$j]); // string to array
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
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function submitDuty($sch)
    {
        $users = Db::table('users')->column('name', 'id');
        $data = [];
        for ($i = 0; $i < sizeof($sch); $i++) {
            for ($j = 0; $j < sizeof($sch[$i]); $j++) {
                @$sch[$i][$j] = $users[$sch[$i][$j]];
            }
            $data[$i] = $this->utils->arrToStr($sch[$i]);
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