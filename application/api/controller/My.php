<?php

namespace app\api\controller;

use think\Controller;
use think\Db;

require_once VENDOR_PATH . '/weixin/lib/WxPay.Api.php';
require_once VENDOR_PATH . '/weixin/example/WxPay.JsApiPay.php';
require_once VENDOR_PATH . '/weixin/example/WxPay.Config.php';

/**
 * swagger: 我的
 */
class My extends Controller
{
    public function index()
    {
        $uid = input('uid');
        $rs = Db::name('student')->where('id', $uid)->find();
        if ($rs) {
            $data['code'] = 200;
            $data['msg'] = "请求成功";
            $data['data'] = $rs;
            return json($data);
        } else {
            $data['code'] = 500;
            $data['msg'] = "请求失败";
            $data['data'] = '';
            return json($data);
        }
    }

    public function orderlist()
    {
        // 2313
        $uid = input('uid');
        $orderlx = input('orderlx');
        if ($orderlx == 0) {
            $map['pay_status'] = array('neq', 2);
        } elseif ($orderlx == 1) {
            $map['pay_status'] = array('eq', 1);
        } else {
            $map['pay_status'] = array('eq', 4);
        }
        $order = Db::name('order')->where('uid', $uid)->where($map)->select();
        foreach ($order as $k => $v) {
            $order[$k]['kcname'] = Db::name('curriculum')->where('id', $v['cid'])->value('kcname');
            $order[$k]['img'] = Db::name('curriculum')->where('id', $v['cid'])->value('img');
            $order[$k]['teacher'] = Db::name('curriculum')->where('id', $v['cid'])->value('teacher');
            $order[$k]['px_star_time'] = Db::name('curriculum')->where('id', $v['cid'])->value('px_star_time');
        }
        $data['code'] = 200;
        $data['msg'] = "请求成功";
        $data['data'] = $order;
        return json($data);
    }

    //我的培训
    public function peixun()
    {
        $uid = input('uid');
        $where['uid'] = $uid;
        $where['pid'] = 0;
        $peixun = Db::name('gm_kecheng')->field('orderid,cid,uid,method,pid,lx,orderid,dabao')->where($where)->where('status', 1)->select();
        foreach ($peixun as $key => $value) {
            $kecheng = Db::name('curriculum')->where('id', $value['cid'])->find();
            $order = Db::name('order')->where("id", $value['orderid'])->find();
            $value['img'] = $kecheng['img'];
            $value['kcname'] = $kecheng['kcname'];
            $value['teacher'] = $kecheng['teacher'];
            $value['px_star_time'] = $kecheng['px_star_time'];
            $value['pay_channel'] = $order['pay_channel'];
            $shenqing = Db::name('shenqing')->where('orderid', $value['orderid'])->where("status", 0)->find();
            if ($shenqing) {
                $value['status'] = $shenqing['status'];
            } else {
                $value['status'] = 4;
            }
            $peixun[$key] = $value;
        }
        $data['code'] = 200;
        $data['msg'] = "请求成功";
        $data['data'] = $peixun;
        return json($data);
    }

    //培训课程子级
    public function child_curriculum()
    {
        $uid = input('uid');
        $pid = input('id');
        $where['uid'] = $uid;
        $where['pid'] = $pid;
        $peixun = Db::name('gm_kecheng')->field('orderid,dabao,cid,uid,method,pid,lx')->where($where)->where('status', 1)->select();
        foreach ($peixun as $key => $value) {
            $kecheng = Db::name('curriculum')->where('id', $value['cid'])->find();
            $order = Db::name('order')->where("id", $value['orderid'])->find();
            $value['img'] = $kecheng['img'];
            $value['kcname'] = $kecheng['kcname'];
            $value['teacher'] = $kecheng['teacher'];
            $value['px_star_time'] = $kecheng['px_star_time'];
            $value['pay_channel'] = $order['pay_channel'];
            $shenqing = Db::name('shenqing')->where('orderid', $value['orderid'])->where("status", 0)->find();
            if ($shenqing) {
                $value['status'] = $shenqing['status'];
            } else {
                $value['status'] = 4;
            }
            $peixun[$key] = $value;
        }
        $data['code'] = 200;
        $data['msg'] = "请求成功";
        $data['data'] = $peixun;
        return json($data);
    }

    //更改培训方式
    public function train_methods()
    {
        $uid = input('uid');
        $sq_data['uid'] = $uid;
        //培训方式
        $sq_data['method'] = input('method');
        //orderId
        $sq_data['orderid'] = input('orderid');
        //课程id
        $sq_data['cid'] = input('cid');
        //打包
        $sq_data['dabao'] = input('dabao');
        //类型
        $sq_data['lx'] = input('lx');
        $order = Db::name('order')->where("id", $sq_data['orderid'])->where("pay_status", "neq", 1)->find();
        if ($order) {
            return json(['code' => 500, 'msg' => '退款中无法更改培训方式']);
        }
        $orderbh = dechex(date('m')) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99)) . sprintf('%02d', rand(0, 9999));
        //未打包购买
        if ($sq_data['dabao'] == 2) {
            if ($sq_data['method'] == '线上') {
                $where['xs_status'] = 1;
                //课程招生数量
                $number = Db::name('curriculum')->where('id', $sq_data['cid'])->value('xs_number');
                //线下转线上退差价
                $sq_data['type'] = 2;
            } else {
                $where['xx_status'] = 1;
                //课程招生数量
                $number = Db::name('curriculum')->where('id', $sq_data['cid'])->value('xx_number');
                //线上转线下补差价
                $sq_data['type'] = 1;
            }
            $where['id'] = $sq_data['cid'];
            $kecheng = Db::name('curriculum')->where($where)->find();
            if (!$kecheng) {
                return json(['code' => 500, 'msg' => '当前课程没有此培训方式']);
            } else {
                $gm_where['cid'] = $sq_data['cid'];
                $gm_where['method'] = $sq_data['method'];
                $gm_count = Db::name('gm_kecheng')->where($gm_where)->where('status', 1)->count();
                //课程招生数量
                if ($gm_count >= $number) {
                    return json(['code' => 500, 'msg' => $sq_data['method'] . '招生人数已满，请更改培训方式']);
                }
            }
        } else {
            if ($sq_data['method'] == '线下') {
                return json(['code' => 500, 'msg' => '打包课程线上培训方式不能转为线下，请更改培训方式']);
            }
            $sq_data['type'] = 0;
        }
        $sq_data['create_time'] = date('Y-m-d H:i:s', time());
        $sq_data['order'] = $orderbh;
        $shenqing = Db::name('shenqing')->insert($sq_data);
        if ($shenqing) {
            $data['code'] = 200;
            $data['msg'] = "申请成功";
            $data['data'] = $shenqing;
            return json($data);
        } else {
            $data['code'] = 500;
            $data['msg'] = "请求失败";
            $data['data'] = '';
            return json($data);
        }
    }


    public function upuserinfo()
    {
        $parm = input('post.');
        $upodate = Db::name('student')->where('id', $parm['id'])->update($parm);
        if ($upodate) {
            $data['code'] = 200;
            $data['msg'] = "修改成功";
            return json($data);
        } else {
            $data['code'] = 500;
            $data['msg'] = "修改失败";
            return json($data);
        }
    }


    public function bjmoney()
    {
        $orderid = input('orderid');
        $openid = input('openid');
        $order = Db::name("shenqing")->where("orderid", $orderid)->find();
        if ($order) {
            $tools = new \JsApiPay();
            $input = new \WxPayUnifiedOrder();
            $input->SetBody("大儒课程补交");
            $input->SetOut_trade_no($order['order']);
            $input->SetTotal_fee(1);
            $input->SetTime_start(date("YmdHis"));
            $input->SetTime_expire(date("YmdHis", time() + 600));
            $notifyurl = "http://peixun.daru.xin/api/notify/bujiao";//回调地址
            $input->SetNotify_url($notifyurl);
            $input->SetTrade_type("JSAPI");
            $input->SetSub_openid($openid);
            $config = new \WxPayConfig();
            $order = \WxPayApi::unifiedOrder($config, $input);
            $jsApiParameters = $tools->GetJsApiParameters($order);
            $data['code'] = 200;
            $data['msg'] = "请求成功";
            $data['data'] = $jsApiParameters;
            return json($data);
        }
    }

    public function zhengshu()
    {
        $uid = input('uid');
        $zs = Db::name('zhengshu')->where("status", 1)->where('uid', $uid)->select();
        $data['code'] = 200;
        $data['msg'] = "请求成功";
        $data['data'] = $zs;
        return json($data);
    }

    public function occupation()
    {
        $occupation = Db::name('occupation')->where('is_del', 0)->select();
        $data['code'] = 200;
        $data['msg'] = "请求成功";
        $data['data'] = $occupation;
        return json($data);
    }
}