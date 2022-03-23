<?php

namespace app\api\controller;

use think\Controller;
use app\api\common\SmsServer;
use think\Db;

require_once VENDOR_PATH . '/weixin/lib/WxPay.Api.php';
require_once VENDOR_PATH . '/weixin/example/WxPay.JsApiPay.php';
require_once VENDOR_PATH . '/weixin/example/WxPay.Config.php';

class Curriculum extends Controller
{
    //课程详情
    public function info()
    {
        $id = input('id');
        $curriculum = Db::name('curriculum')
            ->field('id,kcname,lx,content,xs_number,bm_star_time,bm_end_time,px_star_time,xx_number,xs_status,xx_status,pid,teacher,img')
            ->where('id', $id)->find();
        //后5天
        $time = date("Y-m-d", strtotime("-14 days", strtotime($curriculum['bm_end_time'])));
        if ($time >= date('Y-m-d', time())) {
            $curriculum['price_type'] = '1';
            $curriculum['xs_price'] = Db::name('curriculum')->where('id', $id)->value('xs_znj');
            $curriculum['xx_price'] = Db::name('curriculum')->where('id', $id)->value('xx_znj');
        } else {
            $curriculum['price_type'] = '2';
            $curriculum['xs_price'] = Db::name('curriculum')->where('id', $id)->value('xs_yj');
            $curriculum['xx_price'] = Db::name('curriculum')->where('id', $id)->value('xx_yj');
        }
        if ($curriculum) {
            $data['code'] = 200;
            $data['msg'] = '获取课程介绍成功';
            $data['data'] = $curriculum;
            return json($data);
        } else {
            $data['code'] = 500;
            $data['msg'] = '获取课程介绍失败';
            return json($data);
        }
    }

    //课程顾问
    public function kefu()
    {
        //机构id
        $id = input('place_id');
        $kefu = Db::name('kefu')->where('status = 1 and place_id = ' . $id . '')->select();
        if ($kefu) {
            $data['code'] = 200;
            $data['msg'] = '课程顾问获取成功';
            $data['data'] = $kefu;
            return json($data);
        } else {
            $data['code'] = 500;
            $data['msg'] = '课程顾问获取失败';
            return json($data);
        }
    }

    //添加订单
    public function add_order()
    {
        //课程id
        $cid = input('cid');
        //类型
        $lx = input('lx');
        //是否打包购买
        $dabao = input('dabao');
        //价格类型
        $price_type = input('price_type');
        //培训方式
        $method = input('method');
        //机构id
        $place_id = input('place_id');
        //姓名
        $user_data['name'] = input('name');
        //性别
        $user_data['sex'] = input('sex');
        //手机号
        $user_data['phone'] = input('phone');
        //邮箱
        $user_data['email'] = input('email');
        //地址
        $user_data['address'] = input('address');
        //微信号
        $user_data['wx'] = input('wx');
        //职业
        $user_data['job'] = input('job');
        //工作单位
        $user_data['gzdw'] = input('gzdw');
        //验证码
        $code = input('code');
        $uid = input('uid');
        $openid = input('openid');
        //课程价格
        $kc_map['id'] = $cid;
        if ($method == '线上' && $price_type == '1') {
            $value = 'xs_znj';
        } elseif ($method == '线上' && $price_type == '2') {
            $value = 'xs_yj';
        } elseif ($method == '线下' && $price_type == '2') {
            $value = 'xx_yj';
        } elseif ($method == '线下' && $price_type == '1') {
            $value = 'xx_znj';
        } else {
            return json(['code' => '500', 'data' => '', 'msg' => '添加失败，请重试']);
        }
        $user = Db::name('student')->where("id", $uid)->find();
        if (empty($user['phone'])) {
            $yzcode = Db::name('code')->where('phone', $user_data['phone'])->where('code', $code)->order('sj desc')->find();
            if ($yzcode) {
                Db::name('student')->where('id', $uid)->update($user_data);
            } else {
                return json(['code' => '500', 'data' => '', 'msg' => '验证码错误']);
            }
        }

        $kc_pirce = Db::name('curriculum')->where($kc_map)->value($value);
        //课程
        $kecheng = Db::name('curriculum')->where($kc_map)->find();
        //添加订单
        $orderId = dechex(date('m')) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99)) . sprintf('%02d', rand(0, 9999));
        $order_data['orderId'] = $orderId;
        $order_data['uid'] = $uid;
        $order_data['cid'] = $cid;
        $order_data['method'] = $method;
        $order_data['place_id'] = $place_id;
        $order_data['time'] = date('Y-m-d H:i:s');
        $order_data['price_type'] = $price_type;
        $order_data['dabao'] = $dabao;
        $order_data['lx'] = $lx;
        $order_data['price'] = $kc_pirce;
        $order_data['place_uid'] = input('place_uid');
        if ($lx == 1) {//一级课程
            $yigoumai = Db::name('gm_kecheng')->where('cid', $cid)->where('status', 1)->where('uid', $uid)->find();
            if ($yigoumai) {
                return json(['code' => '500', 'data' => '', 'msg' => '购买失败，此用户课程已存在!']);
            } else {
                if ($method == '线下') {
                    //培训人数限制
                    $gm_count = Db::name('gm_kecheng')->where('cid', $cid)->where('status = 1 and method = "线下"')->count();
                    if ($gm_count >= $kecheng['xx_number']) {
                        return json(['code' => '500', 'data' => '', 'msg' => '线下招生人数已满，请更改培训方式']);
                    }
                } else {
                    if ($kecheng['xs_status'] != 1) {
                        return json(['code' => '500', 'data' => '', 'msg' => '购买失败，此课程没有线上培训方式']);
                    }
                }
            }
            $add_order = Db::name('order')->insertGetId($order_data);
        } else {//二级课程
            if ($dabao == 1) {//打包购买
                $yigoumai = Db::name('gm_kecheng')->where('cid', $cid)->where('status', 1)->where('uid', $uid)->find();
                if ($yigoumai) {
                    return json(['code' => '500', 'data' => '', 'msg' => '购买失败，此用户课程已存在!']);
                } else {
                    $sub_curriculum = Db::name('curriculum')->where('pid', $cid)->select();
                    foreach ($sub_curriculum as $k => $v) {
                        $yigoumai = Db::name('gm_kecheng')->where('uid', $uid)->where('status', 1)->where('cid', $v['id'])->find();
                        if ($yigoumai) {
                            return json(['code' => '500', 'data' => '', 'msg' => '购买失败，此用户购买了子课程!']);
                        }
                        if ($method == '线下') {
                            //培训人数限制
                            $gm_count = Db::name('gm_kecheng')->where('status = 1 and method = "线下"')->where('cid', $v['id'])->count();
                            //判断该课程是否有线上
                            if ($gm_count >= $kecheng['xx_number']) {
                                return json(['code' => '500', 'data' => '', 'msg' => '线下招生人数已满，请更改培训方式']);
                            }
                        }
                    }
                    $add_order = Db::name('order')->insertGetId($order_data);
                }
            }
        }
        if ($add_order) {
            $tools = new \JsApiPay();
            $input = new \WxPayUnifiedOrder();
            $input->SetBody($kecheng['kcname']);
            $input->SetOut_trade_no($orderId);
            $input->SetTotal_fee(1);
            $input->SetTime_start(date("YmdHis"));
            $input->SetTime_expire(date("YmdHis", time() + 600));
            $notifyurl = "http://peixun.daru.xin/api/notify/index";//回调地址
            $input->SetGoods_tag('test1');
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


    public function two_curriculum()
    {
        $kcid = input('kcid');
        $method = input('method');//培训方式
        $kc = Db::name('curriculum')->where('id', $kcid)->find();
        if ($method) {
            if ($method == "线上") {
                $time = date("Y-m-d", strtotime("-14 days", strtotime($kc['bm_end_time'])));
                if ($time >= date('Y-m-d', time())) {
                    $kc['price_type'] = '1';
                    $kc['money'] = Db::name('curriculum')->where('id', $kcid)->value('xs_znj');
                } else {
                    $kc['price_type'] = '2';
                    $kc['money'] = Db::name('curriculum')->where('id', $kcid)->value('xs_yj');
                }
            } else {
                $time = date("Y-m-d", strtotime("-14 days", strtotime($kc['bm_end_time'])));
                if ($time >= date('Y-m-d', time())) {
                    $kc['price_type'] = '1';
                    $kc['money'] = Db::name('curriculum')->where('id', $kcid)->value('xx_znj');
                } else {
                    $kc['price_type'] = '2';
                    $kc['money'] = Db::name('curriculum')->where('id', $kcid)->value('xx_yj');
                }
            }
        }
        $subkc = Db::name('curriculum')->where('pid', $kcid)->select();
        foreach ($subkc as $k => $v) {
            $time = date("Y-m-d", strtotime("-14 days", strtotime($v['bm_end_time'])));
            if ($time >= date('Y-m-d', time())) {
                $subkc[$k]['price_type'] = '1';
                $subkc[$k]['xs_money'] = Db::name('curriculum')->where('id', $v['id'])->value('xs_znj');
                $subkc[$k]['xx_money'] = Db::name('curriculum')->where('id', $v['id'])->value('xx_znj');
            } else {
                $subkc[$k]['price_type'] = '2';
                $subkc[$k]['xs_money'] = Db::name('curriculum')->where('id', $v['id'])->value('xs_yj');
                $subkc[$k]['xx_money'] = Db::name('curriculum')->where('id', $v['id'])->value('xx_yj');
            }
        }
        $kc['subkc'] = $subkc;
        $data['code'] = 200;
        $data['msg'] = '请求成功';
        $data['data'] = $kc;
        return json($data);
    }


    public function two_curriculum_sub()
    {//二级课程单独购买
        //姓名
        $user_data['name'] = input('name');
        //性别
        $user_data['sex'] = input('sex');
        //手机号
        $user_data['phone'] = input('phone');
        //邮箱
        $user_data['email'] = input('email');
        //地址
        $user_data['address'] = input('address');
        //微信号
        $user_data['wx'] = input('wx');
        //职业
        $user_data['job'] = input('job');
        //工作单位
        $user_data['gzdw'] = input('gzdw');
        //验证码
        $code = input('code');
        $uid = input('uid');
        $place_uid = input('place_uid');
        $arr = file_get_contents("php://input");
        $aa = json_decode($arr, true);
        $aa = $aa['kcarr'];
        $openid = input('openid');
        $user = Db::name('student')->where("id", $uid)->find();
        if (empty($user['phone'])) {
            $yzcode = Db::name('code')->where('phone', $user_data['phone'])->where('code', $code)->order('sj desc')->find();
            if ($yzcode) {
                Db::name('student')->where('id', $uid)->update($user_data);
            } else {
                return json(['code' => '500', 'data' => '', 'msg' => '验证码错误']);
            }
        }
        $orderbh = dechex(date('m')) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99)) . sprintf('%02d', rand(0, 9999));
        $zfmoney = 0;
        foreach ($aa as $k => $v) {
            $kecheng = Db::name('curriculum')->where('id', $v['id'])->find();
            if ($v['price_type'] == 1) {
                if ($v['method'] == "线上") {
                    $money = Db::name("curriculum")->where("id", $v['id'])->value("xs_znj");
                } else {
                    $money = Db::name("curriculum")->where("id", $v['id'])->value("xx_znj");
                }
            } else {
                if ($v['method'] == "线上") {
                    $money = Db::name("curriculum")->where("id", $v['id'])->value("xs_yj");
                } else {
                    $money = Db::name("curriculum")->where("id", $v['id'])->value("xx_yj");
                }
            }

            $arr = array("place_uid" => $place_uid, "orderId" => $orderbh, "uid" => $uid, "cid" => $v['id'], "method" => $v['method'], "place_id" => $v['place_id'], "time" => date('Y-m-d H:i:s'), "price_type" => $v['price_type'], "price" => $money, "dabao" => 2, "lx" => 2);

            $yigoumai = Db::name('gm_kecheng')->where('cid', $v['id'])->where('status', 1)->where('uid', $uid)->find();
            if ($yigoumai) {
                return json(['code' => '500', 'data' => '', 'msg' => '购买失败，此用户课程已存在!']);
            } else {
                if ($v['method'] == '线下') {
                    //培训人数限制
                    $gm_count = Db::name('gm_kecheng')->where('cid', $v['id'])->where('status = 1 and method = "线下"')->count();
                    if ($gm_count >= $kecheng['xx_number']) {
                        return json(['code' => '500', 'data' => '', 'msg' => '线下招生人数已满，请更改培训方式']);
                    }
                } else {
                    if ($kecheng['xs_status'] != 1) {
                        return json(['code' => '500', 'data' => '', 'msg' => '购买失败，此课程没有线上培训方式']);
                    }
                }
            }
            $add_order = Db::name('order')->insertGetId($arr);
            $zfmoney += $money;
        }
        if ($zfmoney > 0) {
            $tools = new \JsApiPay();
            $input = new \WxPayUnifiedOrder();
            $input->SetBody("asd");
            $input->SetOut_trade_no($orderbh);
            $input->SetTotal_fee(1);
            $input->SetTime_start(date("YmdHis"));
            $input->SetTime_expire(date("YmdHis", time() + 600));
            $notifyurl = "http://peixun.daru.xin/api/notify/twoindex";//回调地址
            $input->SetGoods_tag('test1');
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

    public function sendcode()
    {
        $phone = input('phone');
        if ($phone) {
            $min = pow(10, (6 - 1));
            $max = pow(10, 6) - 1;
            $code = rand($min, $max);
            $rs = Db::name('code')->where('phone', $phone)->find();
            if ($rs) {
                $del = Db::name('code')->where('phone', $phone)->delete();
            }
            $arr = array('phone' => $phone, 'code' => $code, 'sj' => date('Y-m-d H:i:s'));
            $add = Db::name('code')->insert($arr);
            if ($add) {
                $sms = new SmsServer;
                $zt = $sms->smsSend($phone, $code);
                if ($zt == 1) {
                    $data['code'] = 200;
                    $data['msg'] = "发送成功";
                    return json($data);
                } else {
                    $data['code'] = 500;
                    $data['msg'] = "发送失败";
                    return json($data);
                }
            }
        }
    }

}