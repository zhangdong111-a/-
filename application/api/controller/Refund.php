<?php

namespace app\api\controller;

use think\Controller;
use think\Db;

/**
 * swagger: 退款
 */
class Refund
{
    public function tkts()
    {
        $ts = Db::name('reminder')->select();
        $data['code'] = 200;
        $data['msg'] = "请求成功";
        $data['data'] = $ts;
        return json($data);
    }

    public function sqtk()
    {
        $orderid = input('orderid');
        $order = Db::name('order')->where('id', $orderid)->find();
        if ($order) {
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


    public function tj()
    {
        $orderid = input('orderid');
        $content = input('content');
        $order = Db::name('order')->where('id', $orderid)->find();
        $sq = Db::name('shenqing')->where("orderid", $orderid)->where("status", 0)->find();
        if ($sq) {
            $data['code'] = 500;
            $data['msg'] = "申请失败，此订单修改培训方式中无法退款";
            return json($data);
        }
        if ($order['pay_status'] != 1) {
            $data['code'] = 500;
            $data['msg'] = "申请失败，此订单状态无法退款！";
            return json($data);
        }
        if ($order['pay_channel'] != 1) {
            $data['code'] = 500;
            $data['msg'] = "申请失败，此订单无法退款！";
            return json($data);
        }
        $ar = array("pay_status" => 3, "tkyy" => $content);
        $up = Db::name('order')->where("id", $orderid)->update($ar);
        if ($up) {
            $data['code'] = 200;
            $data['msg'] = "提交成功";
            return json($data);
        }
    }
}