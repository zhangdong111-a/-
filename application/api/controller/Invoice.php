<?php

namespace app\api\controller;

use think\Controller;
use think\Db;

/**
 * swagger: 开票和红头文件
 */
class Invoice extends Controller
{
    public function index()
    {
        $orderid = input('orderid');
        $res = Db::name('order')->alias("a")->join('curriculum i', 'a.cid = i.id')->join('student u', 'a.uid = u.id')->where("a.id", $orderid)
            ->field('a.*,i.kcname,i.teacher,u.name,u.sex,u.phone')->find();
        if ($res) {
            $data['code'] = 200;
            $data['msg'] = "请求成功";
            $data['data'] = $res;
            return json($data);
        } else {
            $data['code'] = 500;
            $data['msg'] = "失败";
            return json($data);
        }
    }

    public function add_htwj()
    {
        $orderid = input('orderid');
        $account_type = input('account_type');
        $account = input('account');
        if (!empty($orderid) && !empty($account_type) && !empty($account)) {
            $arr = array("orderid" => $orderid, "account_type" => $account_type, "account" => $account);
            $htwj = Db::name('htwj')->where('orderid', $orderid)->find();
            if ($htwj) {
                $data['code'] = 500;
                $data['msg'] = "已经申请过了";
                return json($data);
            } else {
                $add_htwj = Db::name('htwj')->insert($arr);
            }
            if ($add_htwj) {
                $arr = array("htwj_status" => 1);
                $up = Db::name('order')->where('id', $orderid)->update($arr);
                if ($up) {
                    $data['code'] = 200;
                    $data['msg'] = "提交成功";
                    return json($data);
                }
            } else {
                $data['code'] = 500;
                $data['msg'] = "添加失败";
                return json($data);
            }
        } else {
            $data['code'] = 500;
            $data['msg'] = "参数错误";
            return json($data);
        }
    }


    public function fplx()
    {
        $rs = Db::name('fplx')->where('is_del', 0)->select();
        $data['code'] = 200;
        $data['msg'] = "请求成功";
        $data['data'] = $rs;
        return json($data);
    }


    public function kpindex()
    {
        $orderid = input('orderid');
        if ($orderid) {
            $order = Db::name('order')->where('id', $orderid)->field('id,orderId,price,cid,uid')->find();
            $data['code'] = 200;
            $data['msg'] = "请求成功";
            $data['data'] = $order;
            return json($data);
        } else {
            $data['code'] = 500;
            $data['msg'] = "参数错误";
            return json($data);
        }
    }

    public function add_kp()
    {
        $parm = input('post.');
        $order = Db::name('order')->where('id', $parm['orderid'])->find();
        $kc = Db::name('curriculum')->where('id', $order['cid'])->value('kcname');
        $user = Db::name('student')->where('id', $parm['uid'])->value('name');
        if (!empty($kc) && !empty($user)) {
            $parm['name'] = $user;
            $parm['kcname'] = $kc;
            $parm['orderbh'] = $order['orderId'];
            $parm['money'] = $order['price'];
            $parm['create_time'] = date('Y-m-d');
            $fp = Db::name('invoice')->where('orderid', $parm['orderid'])->find();
            if ($fp) {
                $data['code'] = 500;
                $data['msg'] = "已经申请过了";
                return json($data);
            }
            $add = Db::name('invoice')->insertGetId($parm);
            if ($add) {
                $arr = array('kp_status' => 1);
                $up = Db::name('order')->where('id', $parm['orderid'])->update($arr);
                if ($up) {
                    $data['code'] = 200;
                    $data['msg'] = "提交成功";
                    return json($data);
                } else {
                    Db::name('invoice')->where('id', $add)->delete();
                    $data['code'] = 500;
                    $data['msg'] = "添加失败";
                    return json($data);
                }
            } else {
                $data['code'] = 500;
                $data['msg'] = "添加失败";
                return json($data);
            }
        } else {
            $data['code'] = 500;
            $data['msg'] = "参数错误";
            return json($data);
        }
    }


    public function kp_info()
    {
        $orderid = input('orderid');
        $uid = input('uid');
        $fp = Db::name('invoice')
            ->field('a.*,b.title')
            ->alias('a')
            ->join('fplx b', 'a.fplx = b.id')
            ->where('a.orderid', $orderid)
            ->where('a.uid', $uid)
            ->find();
        //$fp = Db::name('invoice')->where('orderid',$orderid)->where('uid',$uid)->find();
        if ($fp) {
            $data['code'] = 200;
            $data['msg'] = "请求成功";
            $data['data'] = $fp;
            return json($data);
        } else {
            $data['code'] = 500;
            $data['msg'] = "参数错误";
            return json($data);
        }
    }


    public function del_kp()
    {
        $orderid = input('orderid');
        $fp = Db::name('invoice')->where('orderid', $orderid)->find();
        if ($fp['status'] == 0) {
            $del = Db::name('invoice')->where('orderid', $orderid)->delete();
            if ($del) {
                $arrs = array("kp_status" => 0);
                $up = Db::name('order')->where("id", $orderid)->update($arrs);
                if ($up) {
                    $data['code'] = 200;
                    $data['msg'] = "撤销成功";
                    return json($data);
                }
            } else {
                $data['code'] = 500;
                $data['msg'] = "撤销失败";
                return json($data);
            }
        } else {
            $data['code'] = 500;
            $data['msg'] = "当前状态不允许撤销";
            return json($data);
        }
    }

    public function fpnr()
    {
        $kpnr = Db::name('kpnr')->where('is_del', 1)->select();
        $data['code'] = 200;
        $data['msg'] = "请求成功";
        $data['data'] = $kpnr;
        return json($data);
    }
}