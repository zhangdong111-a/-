<?php
namespace app\api\controller;
use think\Db;

class Curriculum
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
    public function add_order(){
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
        //职业
        $user_data['job'] = input('job');
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
        $kc_pirce = Db::name('curriculum')->where($kc_map)->value($value);
        //课程
        $kecheng = Db::name('curriculum')->where($kc_map)->find();
        //根据openID查询该用户是否存在
        Db::name('student')->where('id', $uid)->update($user_data);
        //添加订单
        $orderId = dechex(date('m')) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99)) . sprintf('%02d', rand(0, 9999));
        $order_data['orderId'] = $orderId;
        $order_data['uid'] = $uid;
        $order_data['cid'] = $cid;
        $order_data['method'] = $method;
        $order_data['place_id'] = $place_id;
        $order_data['pay_channel'] = '微信支付';
        $order_data['time'] = date('Y-m-d H:i:s');
        $order_data['price_type'] = $price_type;
        $order_data['dabao'] = $dabao;
        $order_data['lx'] = $lx;
        $order_data['price'] = $kc_pirce;
        if ($lx == 1) {//一级课程
            $yigoumai = Db::name('gm_kecheng')->where('cid', $cid)->where('status', 1)->where('uid', $uid)->find();
            if ($yigoumai) {
                return json(['code' => '500', 'data' => '', 'msg' => '购买失败，此用户课程已存在!']);
            } else {
                if ($method == '线下'){
                    //培训人数限制
                    $gm_count = Db::name('gm_kecheng')->where('cid', $cid)->where('status = 1 and method = "线下"')->count();
                    if ($gm_count >= $kecheng['xx_number']){
                        return json(['code' => '500', 'data' => '', 'msg' => '线下招生人数已满，请更改培训方式']);
                    }
                }else{
                    if ($kecheng['xs_status'] != 1){
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
                        $zkc = Db::name('curriculum')->where('id',$v['id'])->find();
                        $yigoumai = Db::name('gm_kecheng')->where('uid', $uid)->where('status', 1)->where('cid', $v['id'])->find();
                        if ($yigoumai) {
                            return json(['code' => '500', 'data' => '', 'msg' => '购买失败，此用户购买了子课程!']);
                        }
                        if ($method == '线下'){
                            //培训人数限制
                            $gm_count = Db::name('gm_kecheng')->where('status = 1 and method = "线下"')->where('cid', $v['id'])->count();
                            //判断该课程是否有线上
                            if ($gm_count >= $kecheng['xx_number']){
                                return json(['code' => '500', 'data' => '', 'msg' => '线下招生人数已满，请更改培训方式']);
                            }
                        }
                    }
                    $add_order = Db::name('order')->insertGetId($order_data);
                }
            }
        }
        if ($add_order) {
            // require VENDOR_PATH.'/weixin/lib/WxPay.Api.php'; //引入微信支付
            // $input = new \WxPayUnifiedOrder();//统一下单
            // $config = new \WxPayConfig();//配置参数
            // $goods_name = '扫码支付'.$kc_pirce.'元'; //商品名称(自定义)
            // $input->SetBody($kecheng['kcname']);
            // $input->SetAttach($kecheng['kcname']);
            // $input->SetOut_trade_no($orderId);
            // $input->SetTotal_fee($kc_pirce*100);//金额乘以100$paymoney*100
            // $input->SetTime_start(date("YmdHis"));
            // $input->SetTime_expire(date("YmdHis", time() + 600));
            // $input->SetGoods_tag("test");
            // $input->SetNotify_url("http://peixun.daru.xin/api/notify/index"); //回调地址
            // $input->SetTrade_type("JSAPI");
            // $input->SetSub_openid($openid);
            // $result = \WxPayApi::unifiedOrder($config, $input);
            // $data['code'] = 200;
            // $data['url'] = $result;
            // $data['data'] = '请求成功';
            // return json($data);
            $a=require VENDOR_PATH . '/weixin/lib/WxPay.Api.php';
            print_r($a);exit;
            require VENDOR_PATH . '/weixin/example/WxPay.JsApiPay.php';
            require VENDOR_PATH . '/weixin/example/WxPay.Config.php';
            $tools = new \JsApiPay();
            $notifyurl = "http://peixun.daru.xin/api/notify/index";
            $input = new \WxPayUnifiedOrder();
            $input->SetBody($kecheng['kcname']);
            $input->SetAttach($kecheng['kcname']);
            $input->SetOut_trade_no($orderId);
            $input->SetTotal_fee($kc_pirce * 100);
            $input->SetTime_start(date("YmdHis"));
            $input->SetTime_expire(date("YmdHis", time() + 600));
            $input->SetGoods_tag("test");
            $input->SetNotify_url($notifyurl);
            $input->SetTrade_type("JSAPI");
            $input->SetSub_openid($openId);
            $config = new \WxPayConfig();
            $order = \WxPayApi::unifiedOrder($config, $input);
            $jsApiParameters = $tools->GetJsApiParameters($order);
            $data['code'] = 200;
            $data['url'] = $jsApiParameters;
            $data['data'] = '请求成功';
            return json($data);
        }
    }
    
    
    public function two_curriculum(){
        $kcid = input('kcid');
        $method = input('method');//培训方式
        $kc = Db::name('curriculum')->where('id',$kcid)->find();
        if($method){
            if($method == "线上"){
                $time = date("Y-m-d", strtotime("-14 days", strtotime($kc['bm_end_time'])));
                if ($time >= date('Y-m-d', time())){
                    $kc['price_type'] = '1';
                    $kc['money'] = Db::name('curriculum')->where('id', $kcid)->value('xs_znj');
                }else{
                    $kc['price_type'] = '2';
                    $kc['money'] = Db::name('curriculum')->where('id', $kcid)->value('xs_yj');
                }
            }else{
                $time = date("Y-m-d", strtotime("-14 days", strtotime($kc['bm_end_time'])));
                if ($time >= date('Y-m-d', time())){
                    $kc['price_type'] = '1';
                    $kc['money'] = Db::name('curriculum')->where('id', $kcid)->value('xx_znj');
                }else{
                    $kc['price_type'] = '2';
                    $kc['money'] = Db::name('curriculum')->where('id', $kcid)->value('xx_yj');
                }
            }
        }
        $subkc = Db::name('curriculum')->where('pid',$kcid)->select();
        foreach ($subkc as $k => $v){
            $time = date("Y-m-d", strtotime("-14 days", strtotime($v['bm_end_time'])));
            if ($time >= date('Y-m-d', time())){
                $subkc[$k]['price_type'] = '1';
                $subkc[$k]['xs_money'] = Db::name('curriculum')->where('id', $v['id'])->value('xs_znj');
                $subkc[$k]['xx_money'] = Db::name('curriculum')->where('id', $v['id'])->value('xx_znj');
            }else{
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
    
    
    public function two_curriculum_sub(){//二级课程单独购买
        $uid = input('uid');
        $arr = file_get_contents("php://input");
        $aa = json_decode($arr,true);
        $aa = $aa['kcarr'];
        $openid = input('openid');
        $orderbh = dechex(date('m')) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99)) . sprintf('%02d', rand(0, 9999));
        $zfmoney = 0;
        foreach ($aa as $k=>$v){
            $kecheng = Db::name('curriculum')->where('id',$v['id'])->find();
            if($v['price_type'] == 1){
                if($v['method'] == "线上"){
                    $money = Db::name("curriculum")->where("id",$v['id'])->value("xs_znj");
                }else{
                    $money = Db::name("curriculum")->where("id",$v['id'])->value("xs_znj");
                }
            }else{
                if($v['method'] == "线上"){
                    $money = Db::name("curriculum")->where("id",$v['id'])->value("xs_yj");
                }else{
                    $money = Db::name("curriculum")->where("id",$v['id'])->value("xs_yj");
                }
            }
            
            $arr = array("orderId"=>$orderbh,"uid"=>$uid,"cid"=>$v['id'],"method"=>$v['method'],"place_id"=>$v['place_id'],"time"=>date('Y-m-d H:i:s'),"price_type"=>$v['price_type'],"price"=>$money,"dabao"=>1,"lx"=>2);
            
            $yigoumai = Db::name('gm_kecheng')->where('cid', $v['id'])->where('status', 1)->where('uid', $uid)->find();
            if ($yigoumai) {
                return json(['code' => '500', 'data' => '', 'msg' => '购买失败，此用户课程已存在!']);
            } else {
                if ($v['method'] == '线下'){
                        //培训人数限制
                    $gm_count = Db::name('gm_kecheng')->where('cid', $v['id'])->where('status = 1 and method = "线下"')->count();
                    if ($gm_count >= $kecheng['xx_number']){
                        return json(['code' => '500', 'data' => '', 'msg' => '线下招生人数已满，请更改培训方式']);
                    }
                }else{
                    if ($kecheng['xs_status'] !=1){
                        return json(['code' => '500', 'data' => '', 'msg' => '购买失败，此课程没有线上培训方式']);
                    }
                }
            }
            $add_order = Db::name('order')->insertGetId($arr);
            $zfmoney += $money;
        }
        if($zfmoney > 0){
            require VENDOR_PATH.'/wxpay/WxPay.Api.php'; //引入微信支付
            $input = new \WxPayUnifiedOrder();//统一下单
            $config = new \WxPayConfig();//配置参数
            $goods_name = '扫码支付'.$zfmoney.'元'; //商品名称(自定义)
            $input->SetBody("大儒打包购买课程");
            $input->SetAttach("大儒打包购买课程");
            $input->SetOut_trade_no($orderbh);
            $input->SetTotal_fee($zfmoney*100);//金额乘以100$paymoney*100
            $input->SetTime_start(date("YmdHis"));
            $input->SetTime_expire(date("YmdHis", time() + 600));
            $input->SetGoods_tag("test");
            $input->SetNotify_url("http://peixun.daru.xin/api/notify/twoindex"); //回调地址
            $input->SetTrade_type("JSAPI");
            $input->SetSub_openid($openid);
            $result = \WxPayApi::unifiedOrder($config, $input);
            $data['code'] = 200;
            $data['url'] = $result;
            $data['data'] = '请求成功';
            return json($data);
        }
    }

}