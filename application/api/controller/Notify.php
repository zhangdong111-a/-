<?php

namespace app\api\controller;

use think\Controller;
use think\Db;

class Notify extends Controller
{
    public function index()
    {
        $xml = file_get_contents("php://input");
        $obj = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $out_trade_no = $obj->out_trade_no;
        $order = Db::name('order')->where("orderId", $out_trade_no)->find();
        if ($order) {
            $arr = array("pay_status" => 1, "pay_time" => date('Y-m-d H:i:s'), "s_time" => date('Y-m-d'));
            $up = Db::name('order')->where("orderId", $out_trade_no)->update($arr);
            if ($up) {
                $data['uid'] = $order['uid'];
                $data['price_type'] = $order['price_type'];
                $data['orderid'] = $order['id'];
                $data['cid'] = $order['cid'];
                $data['method'] = $order['method'];
                if ($order['lx'] == 1) {
                    $kcbh = Db::name('curriculum')->where('id', $order['cid'])->find();
                    $jigoubh = Db::name('place')->where('id', $order['place_id'])->find();
                    $cont = Db::name('gm_kecheng')->where('cid', $order['cid'])->where('jg_id', $order['place_id'])->count();
                    $aa = $cont + 1;
                    $bh = str_pad($aa, 6, '0', STR_PAD_LEFT);
                    $data['lx'] = 1;
                    $data['dabao'] = 2;
                    $data['price'] = $order['price'];
                    $data['jg_id'] = $order['place_id'];
                    $data['bh'] = $jigoubh['bh'] . $kcbh['bh'] . $bh;
                    $ygm = Db::name('gm_kecheng')->where("uid", $order['uid'])->where("cid", $order['cid'])->where("orderid", $order['id'])->where('status', 1)->find();
                    if (empty($ygm)) {
                        Db::name('gm_kecheng')->insert($data);
                    }
                } else {
                    $kcbh = Db::name('curriculum')->where('id', $order['cid'])->find();
                    $jigoubh = Db::name('place')->where('id', $order['place_id'])->find();
                    if ($order['dabao'] == 1) {
                        $data['lx'] = 2;
                        $data['dabao'] = 1;
                        $data['price'] = $order['price'];
                        $data['jg_id'] = $order['place_id'];
                        $kecheng = Db::name('curriculum')->where('pid', $order['cid'])->select();
                        $ygm = Db::name('gm_kecheng')->where("uid", $order['uid'])->where("cid", $order['cid'])->where('status', 1)->find();
                        if (empty($ygm)) {
                            Db::name('gm_kecheng')->insert($data);
                        }
                        foreach ($kecheng as $key => $value) {
                            if ($order['method'] == '线上') {
                                if ($value['xs_status'] != 1) {
                                    $data['method'] = '线下';
                                }
                            } else {
                                if ($value['xx_status'] != 1) {
                                    $data['method'] = '线上';
                                }
                            }
                            $cont = Db::name('gm_kecheng')->where('cid', $order['cid'])->where('jg_id', $order['place_id'])->count();
                            $aa = $cont + 1;
                            $bh = str_pad($aa, 6, '0', STR_PAD_LEFT);
                            $data['pid'] = $value['pid'];
                            $data['cid'] = $value['id'];
                            $data['bh'] = $jigoubh['bh'] . $kcbh['bh'] . $bh;
                            $ygm = Db::name('gm_kecheng')->where("uid", $order['uid'])->where("cid", $value['id'])->where('status', 1)->find();
                            if (empty($ygm)) {
                                Db::name('gm_kecheng')->insert($data);
                            }
                        }
                    }
                }
                echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
            }
        }
    }

    public function twoindex()
    {
        $xml = file_get_contents("php://input");
        $obj = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $out_trade_no = $obj->out_trade_no;
        $order = Db::name('order')->where("orderId", $out_trade_no)->select();
        if ($order) {
            $arr = array("pay_status" => 1, "pay_time" => date('Y-m-d H:i:s'), "s_time" => date('Y-m-d'));
            $up = Db::name('order')->where("orderId", $out_trade_no)->update($arr);
            foreach ($order as $k => $v) {
                $data['uid'] = $v['uid'];
                $data['price_type'] = $v['price_type'];
                $data['orderid'] = $v['id'];
                $data['cid'] = $v['cid'];
                $data['method'] = $v['method'];
                $data['lx'] = 2;
                $data['dabao'] = 2;
                $zkc = Db::name('curriculum')->where('id', $v['cid'])->find();
                $sub_kc = Db::name('gm_kecheng')->where('cid', $zkc['pid'])->where('uid', $v['uid'])->where('status', 1)->find();
                if ($sub_kc) {
                    $kcbh = Db::name('curriculum')->where('id', $v['cid'])->find();
                    $jigoubh = Db::name('place')->where('id', $v['place_id'])->find();
                    $cont = Db::name('gm_kecheng')->where('cid', $v['cid'])->where('jg_id', $v['place_id'])->count();
                    $aa = $cont + 1;
                    $bh = str_pad($aa, 6, '0', STR_PAD_LEFT);
                    $data['pid'] = $zkc['pid'];
                    $data['price'] = $v['price'];
                    $data['jg_id'] = $v['place_id'];
                    $data['bh'] = $jigoubh['bh'] . $kcbh['bh'] . $bh;
                    $ygm = Db::name('gm_kecheng')->where("uid", $v['uid'])->where("cid", $v['cid'])->where('status', 1)->find();
                    if (empty($ygm)) {
                        Db::name('gm_kecheng')->insert($data);
                    }
                } else {
                    $data['cid'] = $zkc['pid'];
                    $data['orderid'] = 0;
                    $data['uid'] = $v['uid'];
                    $data['price_type'] = $v['price_type'];;
                    $data['method'] = $v['method'];
                    $data['price'] = 0;
                    $data['jg_id'] = $v['place_id'];
                    $ygm = Db::name('gm_kecheng')->where("uid", $v['uid'])->where("cid", $v['cid'])->where('status', 1)->find();
                    if (empty($ygm)) {
                        $add_zkc = Db::name('gm_kecheng')->insert($data);
                        if ($add_zkc) {
                            $kcbh = Db::name('curriculum')->where('id', $v['cid'])->find();
                            $jigoubh = Db::name('place')->where('id', $v['place_id'])->find();
                            $cont = Db::name('gm_kecheng')->where('cid', $v['cid'])->where('jg_id', $v['place_id'])->count();
                            $aa = $cont + 1;
                            $bh = str_pad($aa, 6, '0', STR_PAD_LEFT);
                            $data['orderid'] = $v['id'];
                            $data['cid'] = $v['cid'];
                            $data['pid'] = $zkc['pid'];
                            $data['price'] = $v['price'];
                            $data['bh'] = $jigoubh['bh'] . $kcbh['bh'] . $bh;
                            $ygm = Db::name('gm_kecheng')->where("uid", $v['uid'])->where("cid", $v['cid'])->where('status', 1)->find();
                            if (empty($ygm)) {
                                Db::name('gm_kecheng')->insert($data);
                            }
                        }
                    }
                }
            }
        }
        echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
    }


    public function bujiao()
    {
        $xml = file_get_contents("php://input");
        $obj = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $out_trade_no = $obj->out_trade_no;
        $bj = Db::name("shenqing")->where("order", $out_trade_no)->find();
        if ($bj) {
            $arr = array("status" => 1);
            $up = Db::name("shenqing")->where("order", $out_trade_no)->update($arr);
            $addarr = array("type" => 1, "value" => $bj['value'], "time" => date('Y-m-d H:i:s'), "oid" => $bj['orderid']);
            $addbj = Db::name("tb_list")->insert($addarr);
            //$bjmoney = array("bj_money"=>$bj['value']);
            $uporder = Db::name("order")->where("id", $bj['orderid'])->setInc('bj_money', $bj['value']);
            $upkcarr = array("method" => $bj['method']);
            $upkc = Db::name("gm_kecheng")->where("orderid", $bj['orderid'])->update($upkcarr);
        }
        echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
    }
}

