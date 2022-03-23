<?php


namespace app\admin\controller;


use app\admin\model\OrderModel;
use app\admin\model\PlaceModel;
use app\admin\model\TblistModel;
use app\admin\controller\Winxinrefund;
use think\Db;
use think\Model;
use think\Request;

class Order extends Base
{
    /**
     * [index 报名列表]
     */
    public function index()
    {
        $key = input('key');
        $place_id = input('place_id');
        $startime = input('startime');
        $endtime = input('endtime');
        $map = [];
        if ($key && $key !== "") {
            $map['orderId'] = ['like', "%" . $key . "%"];
        }
        if ($place_id != "") {
            $map['place_id'] = $place_id;
        }

        if (!empty($startime) && !empty($endtime)) {
            if ($startime == $endtime) {
                $endtime = date("Y-m-d", strtotime("+1 day", strtotime($endtime)));
            }
            $map['s_time'] = array('between time', array($startime, $endtime));
        }

        $Nowpage = input('get.page') ? input('get.page') : 1;
        $limits = config('list_rows');// 获取总条数
        $count = Db::name('order')->where($map)->count();//计算总页面
        $allpage = intval(ceil($count / $limits));
        $order = new OrderModel();
        $lists = $order->getOrderByWhere($map, $Nowpage, $limits);
        foreach ($lists as $k => $v) {
            //查询课程
            $kecheng = Db::name('curriculum')->where('id', $v['cid'])->find();
            $v['kc_name'] = $kecheng['kcname'];
            $v['price_type'] = $this->getTypeById($v['price_type']);
            //学生信息
            $student = Db::name('student')->where('id', $v['uid'])->find();
            $v['student_name'] = $student['name'];
            //分销机构
            $place = Db::name('place')->where('id', $v['place_id'])->find();
            $v['place_name'] = $place['title'];
            //支付状态
            if ($v['pay_status'] == 1) {
                $v['pay_status'] = '已支付';
            } elseif ($v['pay_status'] == 2) {
                $v['pay_status'] = '未支付';
            } elseif ($v['pay_status'] == 3) {
                $v['pay_status'] = '退款中';
            } elseif ($v['pay_status'] == 4) {
                $v['pay_status'] = '取消报名';
            }
            if ($v['lx'] == 1) {
                $v['lx'] = '一级课程';
            } else {
                $v['lx'] = '二级课程';
            }
            if ($v['dabao'] == 1) {
                $v['dabao'] = '打包购买';
            } else {
                $v['dabao'] = '单独购买';
            }
            $lists[$k] = $v;
        }
        $place = Db::name('place')->where('status', 1)->select();
        $this->assign('place', $place);
        $this->assign('place_id', $place_id);
        $this->assign('Nowpage', $Nowpage); //当前页
        $this->assign('allpage', $allpage); //总页数
        $this->assign('count', $count);
        $this->assign('val', $key);
        $this->assign('startime', $startime);
        $this->assign('endtime', $endtime);
        if (input('get.page')) {
            return json($lists);
        }
        return $this->fetch();
    }

    //报名价格类型
    public function price_type()
    {
        $arr = array(
            ['id' => '1', 'title' => '早鸟价'],
            ['id' => '2', 'title' => '原价'],
            ['id' => '3', 'title' => '团报价'],
            ['id' => '4', 'title' => '学生价'],
        );
        return $arr;
    }

    //根据id查询课程类型
    public function getTypeById($id)
    {
        switch ($id) {
            case 1:
            {
                $type = '早鸟价';
                break;
            }
            case 2:
            {
                $type = '原价';
                break;
            }
            case 3:
            {
                $type = '团报价';
                break;
            }
            case 4:
            {
                $type = '学生价';
                break;
            }
        }
        return $type;
    }

    //订单详情
    public function info()
    {
        $id = input('id');
        $order = new OrderModel();
        $info = $order->getOneOrder($id);
        //查询课程
        $kecheng = Db::name('curriculum')->where('id', $info['cid'])->find();
        $info['kc_name'] = $kecheng['kcname'];
        //报名价格类型
        $info['price_type'] = $this->getTypeById($info['price_type']);
        //学生信息
        $student = Db::name('student')->where('id', $info['uid'])->find();
        $info['student_name'] = $student['name'];
        //分销机构
        $place = Db::name('place')->where('id', $info['place_id'])->find();
        $info['place_name'] = $place['title'];
        //支付状态
        if ($info['pay_status'] == 1) {
            $info['pay_status'] = '已支付';
        } elseif ($info['pay_status'] == 2) {
            $info['pay_status'] = '未支付';
        } elseif ($info['pay_status'] == 3) {
            $info['pay_status'] = '退款中';
        } elseif ($info['pay_status'] == 4) {
            $info['pay_status'] = '取消报名';
        }
        $this->assign('info', $info);
        return $this->fetch();
    }

    //退补列表
    public function tblist()
    {
        $id = input('id');
        $key = input('key');
        $map = [];
        if ($key && $key !== "") {
            $map['title'] = ['like', "%" . $key . "%"];
        }
        $map['oid'] = $id;
        $Nowpage = input('get.page') ? input('get.page') : 1;
        $limits = config('list_rows');// 获取总条数
        $count = Db::name('tb_list')->where($map)->count();//计算总页面
        $allpage = intval(ceil($count / $limits));
        $tbList = new TblistModel();
        $lists = $tbList->getTbListByWhere($map, $Nowpage, $limits);
        foreach ($lists as $k => $v) {
            if ($v['type'] == 1) {
                $v['type'] = '补交';
            } else {
                $v['type'] = '退款';
            }
            $lists[$k] = $v;
        }
        $this->assign('Nowpage', $Nowpage); //当前页
        $this->assign('allpage', $allpage); //总页数
        $this->assign('count', $count);
        $this->assign('val', $key);
        $this->assign('id', $id);
        if (input('get.page')) {
            return json($lists);
        }
        return $this->fetch();
    }

    //添加订单
    public function add_order()
    {
        //课程id
        $id = input('cid');
        $curriculum = Db::name('curriculum')->where('id', $id)->find();
        $this->assign('curriculum', $curriculum);
        //类型
        $lx = input('lx');
        $dabao = input('dabao');
        $this->assign('lx', $lx);
        $this->assign('dabao', $dabao);
        //学员列表
        $student = Db::name('student')->select();
        $this->assign('student', $student);
        //报名价格类型
        $this->assign('price_type', $this->price_type());
        //查询是否是子课程
        $zkc = Db::name('curriculum')->where('pid != 0 and id = ' . $id . '')->count();
        if ($zkc == 1) {
            $pid = Db::name('curriculum')->field('pid')->where('id', $id)->find();
            //$id = $pid['pid'];
            $fenyong = Db::name('fenyong')->field('jg_id')->where('kc_id', $pid['pid'])->select();
        } else {
            $fenyong = Db::name('fenyong')->field('jg_id')->where('kc_id', $id)->select();
        }
        //分佣

        $place_id = implode(',', array_column($fenyong, 'jg_id'));
        //分销机构
        $place = Db::name('place')->where('id in (' . $place_id . ')')->select();
        $this->assign('place', $place);
        //订单号
        $orderId = dechex(date('m')) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99)) . sprintf('%02d', rand(0, 9999));
        $this->assign('orderId', $orderId);
        if (request()->isAjax()) {
            $param = input('post.');
            if ($param['method'] == '线上') {
                switch ($param['price_type']) {
                    case 1:
                    {
                        $param['price'] = Db::name('curriculum')->where('id', $id)->value('xs_znj');
                        break;
                    }
                    case 2:
                    {
                        $param['price'] = Db::name('curriculum')->where('id', $id)->value('xs_yj');
                        break;
                    }
                    case 3:
                    {
                        $param['price'] = Db::name('curriculum')->where('id', $id)->value('xs_tbj');
                        break;
                    }
                    case 4:
                    {
                        $param['price'] = Db::name('curriculum')->where('id', $id)->value('xs_xsj');
                        break;
                    }
                }
            } else if ($param['method'] == '线下') {
                switch ($param['price_type']) {
                    case 1:
                    {
                        $param['price'] = Db::name('curriculum')->where('id', $id)->value('xx_znj');
                        break;
                    }
                    case 2:
                    {
                        $param['price'] = Db::name('curriculum')->where('id', $id)->value('xx_yj');
                        break;
                    }
                    case 3:
                    {
                        $param['price'] = Db::name('curriculum')->where('id', $id)->value('xx_tbj');
                        break;
                    }
                    case 4:
                    {
                        $param['price'] = Db::name('curriculum')->where('id', $id)->value('xx_xsj');
                        break;
                    }
                }
            } else {
                return json(['code' => '2', 'data' => '', 'msg' => '添加失败，请重试']);
            }
            $param['pay_channel'] = 2;
            $param['pay_status'] = 1;
            $param['time'] = date('Y-m-d H:i:s', time());
            $param['pay_time'] = date('Y-m-d H:i:s', time());
            $param['s_time'] = date('Y-m-d');
            $param['dabao'] = $dabao;
            $param['lx'] = $lx;
            if ($lx == 1) {//一级课程
                $yigoumai = Db::name('gm_kecheng')->where('cid', $id)->where('status', 1)->where('uid', $param['uid'])->find();
                if ($yigoumai) {
                    return json(['code' => '2', 'data' => '', 'msg' => '购买失败，此用户课程已存在!']);
                } else {
                    if ($param['method'] == '线下') {
                        //培训人数限制
                        $gm_count = Db::name('gm_kecheng')->where('cid', $id)->where('status = 1 and method = "线下"')->count();
                        if ($gm_count >= $curriculum['xx_number']) {
                            return json(['code' => '500', 'data' => '', 'msg' => '线下招生人数已满，请更改培训方式']);
                        }
                    } else {
                        if ($curriculum['xs_status'] != 1) {
                            return json(['code' => '500', 'data' => '', 'msg' => '购买失败，此课程没有线上培训方式']);
                        }
                    }
                    $add_order = Db::name('order')->insertGetId($param);
                    if ($param['kp_status'] == 2) {
                        $kp_arr = array("orderid" => $add_order, "uid" => $param['uid'], "status" => 1, "name" => Db::name('student')->where('id', $param['uid'])->value('name'), "kcname" => $curriculum['kcname']);
                        $add_kp = Db::name('invoice')->insert($kp_arr);
                    }
                }
            } else {//二级课程
                if ($dabao == 1) {//打包购买
                    $yigoumai = Db::name('gm_kecheng')->where('cid', $id)->where('status', 1)->where('uid', $param['uid'])->find();
                    if ($yigoumai) {
                        return json(['code' => '2', 'data' => '', 'msg' => '购买失败，此用户课程已存在!']);
                    } else {
                        $sub_curriculum = Db::name('curriculum')->where('pid', $id)->select();
                        foreach ($sub_curriculum as $k => $v) {
                            $yigoumai = Db::name('gm_kecheng')->where('uid', $param['uid'])->where('status', 1)->where('cid', $v['id'])->find();
                            if ($yigoumai) {
                                return json(['code' => '2', 'data' => '', 'msg' => '购买失败，此用户购买了子课程!']);
                            }
                            if ($param['method'] == '线下') {
                                //培训人数限制
                                $gm_count = Db::name('gm_kecheng')->where('status = 1 and method = "线下"')->where('cid', $v['id'])->count();
                                //判断该课程是否有线上
                                if ($gm_count >= $curriculum['xx_number']) {
                                    return json(['code' => '500', 'data' => '', 'msg' => '线下招生人数已满，请更改培训方式']);
                                }
                            }
                        }
                        // $order = new OrderModel();
                        // $flag = $order->insertOrder($param);
                        $add_order = Db::name('order')->insertGetId($param);
                        if ($param['kp_status'] == 2) {
                            $kp_arr = array("orderid" => $add_order, "uid" => $param['uid'], "status" => 1, "name" => Db::name('student')->where('id', $param['uid'])->value('name'), "kcname" => $curriculum['kcname']);
                            $add_kp = Db::name('invoice')->insert($kp_arr);
                        }
                    }
                } else {//单独购买
                    $yigoumai = Db::name('gm_kecheng')->where('cid', $id)->where('status', 1)->where('uid', $param['uid'])->find();
                    if ($yigoumai) {
                        return json(['code' => '2', 'data' => '', 'msg' => '购买失败，此用户课程已存在!']);
                    } else {
                        if ($param['method'] == '线下') {
                            //培训人数限制
                            $gm_count = Db::name('gm_kecheng')->where('cid', $id)->where('status = 1 and method = "线下"')->count();
                            if ($gm_count >= $curriculum['xx_number']) {
                                return json(['code' => '500', 'data' => '', 'msg' => '线下招生人数已满，请更改培训方式']);
                            }
                        } else {
                            if ($curriculum['xs_status'] != 1) {
                                return json(['code' => '500', 'data' => '', 'msg' => '购买失败，此课程没有线上培训方式']);
                            }
                        }
                        $add_order = Db::name('order')->insertGetId($param);
                        if ($param['kp_status'] == 2) {
                            $kp_arr = array("orderid" => $add_order, "uid" => $param['uid'], "status" => 1, "name" => Db::name('student')->where('id', $param['uid'])->value('name'), "kcname" => $curriculum['kcname']);
                            $add_kp = Db::name('invoice')->insert($kp_arr);
                        }
                    }
                }
            }
            //if ($flag['code'] == 1){
            if ($add_order) {
                $data['uid'] = $param['uid'];
                $data['price_type'] = $param['price_type'];
                $data['orderid'] = $add_order;
                $data['cid'] = $param['cid'];
                $data['method'] = $param['method'];
                if ($param['lx'] == 1) {
                    $data['lx'] = 1;
                    $data['dabao'] = 2;
                    $data['price'] = $param['price'];
                    $data['jg_id'] = $param['place_id'];
                    Db::name('gm_kecheng')->insert($data);
                } else {
                    if ($param['dabao'] == 1) {
                        $data['lx'] = 2;
                        $data['dabao'] = 1;
                        $data['price'] = $param['price'];
                        $data['jg_id'] = $param['place_id'];
                        $kecheng = Db::name('curriculum')->where('pid', $param['cid'])->select();
                        Db::name('gm_kecheng')->insert($data);
                        foreach ($kecheng as $key => $value) {
                            if ($param['method'] == '线上') {
                                if ($value['xs_status'] != 1) {
                                    $data['method'] = '线下';
                                }
                            } else {
                                if ($value['xx_status'] != 1) {
                                    $data['method'] = '线上';
                                }
                            }
                            $data['pid'] = $value['pid'];
                            $data['cid'] = $value['id'];
                            Db::name('gm_kecheng')->insert($data);
                        }
                    } else {
                        $zkc = Db::name('curriculum')->where('id', $param['cid'])->find();
                        $data['lx'] = 2;
                        $data['dabao'] = 2;
                        $sub_kc = Db::name('gm_kecheng')->where('cid', $zkc['pid'])->where('status', 1)->where('uid', $param['uid'])->find();
                        if ($sub_kc) {
                            $data['pid'] = $zkc['pid'];
                            $data['price'] = $param['price'];
                            $data['jg_id'] = $param['place_id'];
                            Db::name('gm_kecheng')->insert($data);
                        } else {
                            $data['cid'] = $zkc['pid'];
                            $data['orderid'] = 0;
                            $data['uid'] = $param['uid'];
                            $data['price_type'] = $param['price_type'];
                            $data['method'] = $param['method'];
                            $data['price'] = 0;
                            $data['jg_id'] = $param['place_id'];
                            $add_zkc = Db::name('gm_kecheng')->insert($data);
                            if ($add_zkc) {
                                $data['orderid'] = $add_order;
                                $data['cid'] = $param['cid'];
                                $data['pid'] = $zkc['pid'];
                                $data['price'] = $param['price'];
                                Db::name('gm_kecheng')->insert($data);
                            }
                        }
                    }
                }
            }
            //return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
            return json(['code' => '1', 'data' => '', 'msg' => '添加成功']);
        }
        return $this->fetch();
    }

    //退款
    public function tuikuan()
    {
        $id = input('id');
        $order = Db::name('order')->where('id', $id)->find();
        $this->assign('order', $order);
        if (request()->isAjax()) {
            $param = input('param.');
            $oid = input('oid');
            $order1 = Db::name('order')->where('id', $param['oid'])->find();
            //手续费
            $sxf = $param['sxf'];
            //退款金额+退款金额
            $price = $order1['tk_money'] + $param['value'];
            $param['time'] = date('Y-m-d H:i:s');
            $flag = Db::name('tb_list')->insert($param);
            if ($flag > 0) {
                Db::name('order')->where('id', $oid)->update(['tk_money' => $price, 'sxf' => $sxf + $order1['sxf']]);
                return ['code' => 1, 'data' => '', 'msg' => '退款成功'];

            } else {
                return ['code' => -1, 'data' => '', 'msg' => '退款失败'];
            }
        }
        return $this->fetch();
    }

    //取消报名
    public function qxbm()
    {
        $id = input('id');
        $order = Db::name('order')->where('id', $id)->find();
        $this->assign('order', $order);
        if (request()->isAjax()) {
            $param = input('param.');
            $oid = input('oid');
            $order1 = Db::name('order')->where('id', $param['oid'])->find();
            //手续费
            $sxf = $param['sxf'];
            //退款金额+退款金额
            $price = $order1['tk_money'] + $param['value'];
            $param['time'] = date('Y-m-d H:i:s');
            $tb = new TblistModel();
            $flag = $tb->insertTb($param);
            if ($flag['code'] == 1) {
                if ($order1['lx'] == 2) {
                    $pid = Db::name('curriculum')->where('id', $order1['cid'])->value('pid');
                    $w['pid'] = $pid;
                    $w['uid'] = $order1['uid'];
                    $gm = Db::name('gm_kecheng')->where($w)->where('status', 1)->count();
                    if ($gm == 1) {
                        Db::name('gm_kecheng')->where('orderId', $param['oid'])->update(['status' => 0]);
                        Db::name('gm_kecheng')->where('uid = ' . $order1['uid'] . ' and cid = ' . $pid . '')->update(['status' => 0]);
                    }
                }
                Db::name('order')->where('id', $oid)->update(['tk_money' => $price, 'pay_status' => 4, 'sxf' => $sxf]);
            }
            return $flag;
        }
        return $this->fetch();
    }

    //订单统计
    public function tongji()
    {
        $key = input('key');
        $map = [];
        if ($key && $key !== "") {
            $map['kcname'] = ['like', "%" . $key . "%"];
        }
        $Nowpage = input('get.page') ? input('get.page') : 1;
        $limits = config('list_rows');// 获取总条数
        $count = Db::name('order')->group('cid')->count();//计算总页面
        $allpage = intval(ceil($count / $limits));
        $lists = Db::name('order')->group('cid')->page($Nowpage, $limits)->select();
        foreach ($lists as $k => $v) {
            $kecheng = Db::name('curriculum')->where('id', $v['cid'])->find();
            $v['kcname'] = $kecheng['kcname'];
            //报名人数
            $v['bmrs'] = Db::name('order')->where('cid', $v['cid'])->count();
            //总金额
            $v['total'] = Db::name('order')->where('cid', $v['cid'])->sum('price');
            //退款金额
            $v['tui'] = Db::name('order')->where('cid', $v['cid'])->sum('tk_money');
            //手续费
            $v['sxf'] = Db::name('order')->where('cid', $v['cid'])->sum('sxf');
            $lists[$k] = $v;
        }
        $this->assign('Nowpage', $Nowpage); //当前页
        $this->assign('allpage', $allpage); //总页数
        $this->assign('val', $key);
        $this->assign('count', $count);
        if (input('get.page')) {
            return json($lists);
        }
        return $this->fetch();
    }

    //订单统计详情
    public function order_info()
    {
        $id = input('id');
        $map = [];
        $map['cid'] = $id;
        $Nowpage = input('get.page') ? input('get.page') : 1;
        $limits = config('list_rows');// 获取总条数
        $count = Db::name('order')->where($map)->count();//计算总页面
        $allpage = intval(ceil($count / $limits));
        $lists = Db::name('order')->where($map)->page($Nowpage, $limits)->select();
        foreach ($lists as $k => $v) {
            $kecheng = Db::name('curriculum')->where('id', $v['cid'])->find();
            $v['kcname'] = $kecheng['kcname'];
            //人员信息
            $student = Db::name('student')->where('id', $v['uid'])->find();
            $v['student_name'] = $student['name'];
            //价格类型
            $v['price_type'] = $this->getTypeById($v['price_type']);
            $lists[$k] = $v;
        }
        $this->assign('Nowpage', $Nowpage); //当前页
        $this->assign('allpage', $allpage); //总页数
        $this->assign('count', $count);
        $this->assign('id', $id);
        if (input('get.page')) {
            return json($lists);
        }
        return $this->fetch();
    }

    //分销统计
    public function place()
    {
        $key = input('key');
        $map = [];
        if ($key && $key !== "") {
            $map['title'] = ['like', "%" . $key . "%"];
        }
        $Nowpage = input('get.page') ? input('get.page') : 1;
        $limits = config('list_rows');// 获取总条数
        $count = Db::name('place')->where($map)->count();//计算总页面
        $allpage = intval(ceil($count / $limits));
        $place = new PlaceModel();
        $lists = $place->getPlaceByWhere($map, $Nowpage, $limits);
        foreach ($lists as $k => $v) {
            $dabao = Db::name('order')->where("pay_status", 1)->where('dabao = 1 and place_id = ' . $v['id'] . '')->count();
            $wdabao = Db::name('order')->where("pay_status", 1)->where('dabao = 2 and place_id = ' . $v['id'] . '')->count();
            $v['db'] = $dabao;
            $v['wdb'] = $wdabao;
            //根据课程查询分销金额
            //课程
            $kc = Db::name('order')->field('cid,place_id')->where("pay_status", 1)->where('place_id', $v['id'])->group('cid')->select();
            foreach ($kc as $k1 => $v1) {
                $w['cid'] = $v1['cid'];
                $w['place_id'] = $v1['place_id'];
                $v1['count'] = Db::name('order')->where("pay_status", 1)->where($w)->count();
                //分佣
                $fenyong = Db::name('fenyongbili')->where('cid = ' . $v1['cid'] . ' and jg_id = ' . $v1['place_id'] . ' and (min <= ' . $v1['count'] . ' or max >= ' . $v1['count'] . ')')->value('fenyong');
                $v1['fenyongbili'] = $fenyong / 100;
                //课程总金额
                $kc_total_money = Db::name('order')->where("pay_status", 1)->where($w)->sum('price');
                $v1['kc_total_money'] = $kc_total_money;
                //分佣金额
                $v1['money'] = $kc_total_money * $v1['fenyongbili'];
                $kc[$k1] = $v1;
            }
            $kc_money = array_sum(array_column($kc, 'money'));
            $v['money'] = $kc_money;
            $v['zrs'] = $dabao + $wdabao;
            $v['zmoney'] = Db::name('order')->where('place_id', $v['id'])->where("pay_status", 1)->sum("price");
            $lists[$k] = $v;
        }
        $this->assign('Nowpage', $Nowpage); //当前页
        $this->assign('allpage', $allpage); //总页数
        $this->assign('count', $count);
        $this->assign('val', $key);
        if (input('get.page')) {
            return json($lists);
        }
        return $this->fetch();
    }

    //分销统计详情
    public function place_info()
    {
        $id = input('id');
        $kcid = input('kcid');
        $map = [];
        $map['place_id'] = $id;
        $map['cid'] = $kcid;
        $Nowpage = input('get.page') ? input('get.page') : 1;
        $limits = config('list_rows');// 获取总条数
        $count = Db::name('order')->where($map)->count();//计算总页面
        $allpage = intval(ceil($count / $limits));
        $lists = Db::name('order')->where($map)->page($Nowpage, $limits)->select();
        foreach ($lists as $k => $v) {
            $kecheng = Db::name('curriculum')->where('id', $v['cid'])->find();
            $v['kcname'] = $kecheng['kcname'];
            //人员信息
            $student = Db::name('student')->where('id', $v['uid'])->find();
            $v['student_name'] = $student['name'];
            //价格类型
            $lists[$k] = $v;
        }
        $this->assign('Nowpage', $Nowpage); //当前页
        $this->assign('allpage', $allpage); //总页数
        $this->assign('count', $count);
        $this->assign('id', $id);
        $this->assign('kcid', $kcid);
        if (input('get.page')) {
            return json($lists);
        }
        return $this->fetch();
    }

    public function tklist()
    {
        $key = input('key');
        $map['pay_status'] = 3;
        if ($key && $key !== "") {
            $map['orderId'] = ['like', "%" . $key . "%"];
        }
        $Nowpage = input('get.page') ? input('get.page') : 1;
        $limits = config('list_rows');// 获取总条数
        $count = Db::name('order')->where($map)->count();//计算总页面
        $allpage = intval(ceil($count / $limits));
        $order = new OrderModel();
        $lists = $order->getOrderByWhere($map, $Nowpage, $limits);
        foreach ($lists as $k => $v) {
            //查询课程
            $kecheng = Db::name('curriculum')->where('id', $v['cid'])->find();
            $v['kc_name'] = $kecheng['kcname'];
            $v['price_type'] = $this->getTypeById($v['price_type']);
            //学生信息
            $student = Db::name('student')->where('id', $v['uid'])->find();
            $v['student_name'] = $student['name'];
            //分销机构
            $place = Db::name('place')->where('id', $v['place_id'])->find();
            $v['place_name'] = $place['title'];
            //支付状态
            if ($v['pay_status'] == 1) {
                $v['pay_status'] = '已支付';
            } elseif ($v['pay_status'] == 2) {
                $v['pay_status'] = '未支付';
            } elseif ($v['pay_status'] == 3) {
                $v['pay_status'] = '退款中';
            } elseif ($v['pay_status'] == 4) {
                $v['pay_status'] = '取消报名';
            }
            if ($v['lx'] == 1) {
                $v['lx'] = '一级课程';
            } else {
                $v['lx'] = '二级课程';
            }
            if ($v['dabao'] == 1) {
                $v['dabao'] = '打包购买';
            } else {
                $v['dabao'] = '单独购买';
            }
            $lists[$k] = $v;
        }
        $this->assign('Nowpage', $Nowpage); //当前页
        $this->assign('allpage', $allpage); //总页数
        $this->assign('count', $count);
        $this->assign('val', $key);
        if (input('get.page')) {
            return json($lists);
        }
        return $this->fetch();
    }

    public function jjtk()
    {
        $id = input('id');
        $arr = array('pay_status' => 1);
        $up = Db::name('order')->where('id', $id)->update($arr);
        if ($up) {
            return ['code' => 1, 'data' => '', 'msg' => '操作成功'];
        } else {
            return ['code' => 0, 'data' => '', 'msg' => '操作失败'];
        }
    }

    public function tytk()
    {
        $id = input('id');
        $order = Db::name('order')->where('id', $id)->find();
        $gmkc = Db::name('gm_kecheng')->where('status', 1)->where("orderid", $id)->find();
        if (request()->isAjax()) {
            $orderbh = dechex(date('m')) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99)) . sprintf('%02d', rand(0, 9999));
            $money = input('money');//退款金额
            //$tkmoney = $money * 0.06;//6%的手续费
            $tkmoney = '0.01';
            $sxf = $money - $tkmoney;//手续费
            $user = Db::name("student")->where("id", $order['uid'])->find();
            $pay = new Winxinrefund($user['openid'], $order['orderId'], $tkmoney, $orderbh, $tkmoney);
            $result = $pay->refund();
            if ($result['return_code'] == "SUCCESS") {
                $arr = array("pay_status" => 4, "tkorder" => $orderbh);
                Db::name('order')->where('id', $id)->update($arr);
                Db::name('order')->where('id', $id)->setInc('tk_money', $tkmoney);
                Db::name('order')->where('id', $id)->setInc('sxf', $sxf);
                if ($order['lx'] == 2 && $order['dabao'] == 2) {
                    $subkc = Db::name('gm_kecheng')->where('status', 1)->where('pid', $gmkc['pid'])->where('uid', $order['uid'])->where('id', 'neq', $id)->find();
                    if ($subkc) {
                        $ar = array("status" => 0);
                        Db::name('gm_kecheng')->where("orderid", $id)->where('uid', $order['uid'])->update($ar);
                    } else {
                        $ar = array("status" => 0);
                        Db::name('gm_kecheng')->where("orderid", $id)->where('uid', $order['uid'])->update($ar);
                        Db::name('gm_kecheng')->where("cid", $subkc['pid'])->where('uid', $order['uid'])->update($ar);
                    }
                } elseif ($order['lx'] == 2 && $order['dabao'] == 1) {
                    $ar = array("status" => 0);
                    Db::name('gm_kecheng')->where("orderid", $id)->where('uid', $order['uid'])->update($ar);
                    Db::name('gm_kecheng')->where("pid", $gmkc['cid'])->where('uid', $order['uid'])->update($ar);
                } else {
                    $ar = array("status" => 0);
                    Db::name('gm_kecheng')->where("orderid", $id)->where('uid', $order['uid'])->update($ar);
                }
                $shenqing = Db::name('shenqing')->where("orderid", $id)->where("type", 1)->where("status", 1)->where("dabao", 2)->where("uid", $order['uid'])->select();
                if ($shenqing) {
                    foreach ($shenqing as $k => $v) {
                        $orderbh = dechex(date('m')) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99)) . sprintf('%02d', rand(0, 9999));
                        $tkmoney = $v['value'] * 0.06;//6%的手续费
                        $sxf = $v['value'] - $tkmoney;//手续费
                        $pay = new Winxinrefund($user['openid'], $v['order'], $tkmoney, $orderbh, $tkmoney);
                        $result = $pay->refund();
                        if ($result['return_code'] == "SUCCESS") {
                            $arr = array("tkorder" => $orderbh);
                            Db::name('shenqing')->where('id', $v['id'])->update($arr);
                            Db::name('order')->where('id', $v['orderid'])->setInc('tk_money', $tkmoney);
                            Db::name('order')->where('id', $v['orderid'])->setInc('sxf', $sxf);
                        }
                    }
                }
                return ['code' => 1, 'data' => '', 'msg' => '退款成功'];
            } else {
                return ['code' => 0, 'data' => '', 'msg' => '退款失败'];
            }
        }
        $this->assign('order', $order);
        return $this->fetch();
    }

    public function kcfytj()
    {
        $key = input('key');
        $map = [];
        if ($key && $key !== "") {
            $map['kcname'] = ['like', "%" . $key . "%"];
        }
        $Nowpage = input('get.page') ? input('get.page') : 1;
        $limits = config('list_rows');// 获取总条数
        $count = Db::name('curriculum')->where($map)->where('pid', 0)->count();//计算总页面
        $allpage = intval(ceil($count / $limits));
        $lists = Db::name('curriculum')->where($map)->where('pid', 0)->page($Nowpage, $limits)->select();
        $this->assign('Nowpage', $Nowpage); //当前页
        $this->assign('allpage', $allpage); //总页数
        $this->assign('val', $key);
        $this->assign('count', $count);
        if (input('get.page')) {
            return json($lists);
        }
        return $this->fetch();
    }

    public function kcfytj_jg()
    {
        $id = input('kcid');
        $Nowpage = input('get.page') ? input('get.page') : 1;
        $limits = config('list_rows');// 获取总条数
        $count = Db::name('fenyong')->where('kc_id', $id)->count();//计算总页面
        $allpage = intval(ceil($count / $limits));
        $lists = Db::name('fenyong')->where('kc_id', $id)->page($Nowpage, $limits)->select();
        foreach ($lists as $k => $v) {
            $kc = Db::name('order')->field('cid,place_id')->where('cid', $id)->where("pay_status", 1)->where('place_id', $v['jg_id'])->group('cid')->select();
            foreach ($kc as $k1 => $v1) {
                $w['cid'] = $v1['cid'];
                $w['place_id'] = $v1['place_id'];
                $v1['count'] = Db::name('order')->where('cid', $id)->where("pay_status", 1)->where($w)->count();
                //分佣
                $fenyong = Db::name('fenyongbili')->where('cid = ' . $v1['cid'] . ' and jg_id = ' . $v1['place_id'] . ' and (min <= ' . $v1['count'] . ' or max >= ' . $v1['count'] . ')')->value('fenyong');
                $v1['fenyongbili'] = $fenyong / 100;
                //课程总金额
                $kc_total_money = Db::name('order')->where('cid', $id)->where("pay_status", 1)->where($w)->sum('price');
                $v1['kc_total_money'] = $kc_total_money;
                //分佣金额
                $v1['money'] = $kc_total_money * $v1['fenyongbili'];
                $kc[$k1] = $v1;
            }
            $kc_money = array_sum(array_column($kc, 'money'));
            $v['money'] = $kc_money;
            $v['title'] = Db::name('place')->where('id', $v['jg_id'])->value('title');
            $v['zrs'] = Db::name('order')->where('cid', $id)->where("pay_status", 1)->where('place_id = ' . $v['jg_id'] . '')->count();
            $v['zmoney'] = Db::name('order')->where('cid', $id)->where("pay_status", 1)->where('place_id', $v['jg_id'])->sum("price");
            $lists[$k] = $v;
        }
        $this->assign('Nowpage', $Nowpage); //当前页
        $this->assign('allpage', $allpage); //总页数
        $this->assign('count', $count);
        $this->assign('id', $id);
        if (input('get.page')) {
            return json($lists);
        }
        return $this->fetch();
    }

    public function zkctj()
    {
        $zid = input('pid');
        $key = input('key');
        $map['pid'] = $zid;
        if ($key && $key !== "") {
            $map['kcname'] = ['like', "%" . $key . "%"];
        }
        $Nowpage = input('get.page') ? input('get.page') : 1;
        $limits = config('list_rows');// 获取总条数
        $count = Db::name('curriculum')->where($map)->count();//计算总页面
        $allpage = intval(ceil($count / $limits));
        $lists = Db::name('curriculum')->where($map)->page($Nowpage, $limits)->select();
        $this->assign('Nowpage', $Nowpage); //当前页
        $this->assign('allpage', $allpage); //总页数
        $this->assign('val', $key);
        $this->assign('count', $count);
        $this->assign('pid', $zid);
        if (input('get.page')) {
            return json($lists);
        }
        return $this->fetch();
    }

    public function zkcjg()
    {
        $id = input('kcid');
        $pid = input('pid');
        $Nowpage = input('get.page') ? input('get.page') : 1;
        $limits = config('list_rows');// 获取总条数
        $zkc = Db::name('curriculum')->where('id', $pid)->find();
        $count = Db::name('fenyong')->where('kc_id', $zkc['id'])->count();//计算总页面
        $allpage = intval(ceil($count / $limits));
        $lists = Db::name('fenyong')->where('kc_id', $zkc['id'])->page($Nowpage, $limits)->select();
        foreach ($lists as $k => $v) {
            $kc = Db::name('order')->field('cid,place_id')->where('cid', $id)->where("pay_status", 1)->where('place_id', $v['jg_id'])->group('cid')->select();
            foreach ($kc as $k1 => $v1) {
                $w['cid'] = $v1['cid'];
                $w['place_id'] = $v1['place_id'];
                $v1['count'] = Db::name('order')->where('cid', $id)->where("pay_status", 1)->where($w)->count();
                //分佣
                $fenyong = Db::name('fenyongbili')->where('cid = ' . $v1['cid'] . ' and jg_id = ' . $v1['place_id'] . ' and (min <= ' . $v1['count'] . ' or max >= ' . $v1['count'] . ')')->value('fenyong');
                $v1['fenyongbili'] = $fenyong / 100;
                //课程总金额
                $kc_total_money = Db::name('order')->where('cid', $id)->where("pay_status", 1)->where($w)->sum('price');
                $v1['kc_total_money'] = $kc_total_money;
                //分佣金额
                $v1['money'] = $kc_total_money * $v1['fenyongbili'];
                $kc[$k1] = $v1;
            }
            $kc_money = array_sum(array_column($kc, 'money'));
            $v['money'] = $kc_money;
            $v['title'] = Db::name('place')->where('id', $v['jg_id'])->value('title');
            $v['zrs'] = Db::name('order')->where('cid', $id)->where("pay_status", 1)->where('place_id = ' . $v['jg_id'] . '')->count();
            $v['zmoney'] = Db::name('order')->where('cid', $id)->where("pay_status", 1)->where('place_id', $v['jg_id'])->sum("price");
            $lists[$k] = $v;
        }
        $this->assign('Nowpage', $Nowpage); //当前页
        $this->assign('allpage', $allpage); //总页数
        $this->assign('count', $count);
        $this->assign('id', $id);
        $this->assign('pid', $pid);
        if (input('get.page')) {
            return json($lists);
        }
        return $this->fetch();
    }

    public function kpsq(){
        $key = input('key');
        $map = [];
        if ($key && $key !== "") {
            $map['name|kcname'] = ['like', "%" . $key . "%"];
        }
        $Nowpage = input('get.page') ? input('get.page') : 1;
        $limits = config('list_rows');// 获取总条数
        $count = Db::name('invoice')->where('zt', 1)->where($map)->count();//计算总页面
        $allpage = intval(ceil($count / $limits));
        $lists = Db::name('invoice')->where('zt', 1)->where($map)->page($Nowpage, $limits)->select();
        foreach ($lists as $k => $v) {
            $lists[$k]['fplx'] = Db::name('fplx')->where('id', $v['fplx'])->value('title');
            if ($v['fpxz'] == 1) {
                $lists[$k]['fpxz'] = "个人或事业单位";
            } else {
                $lists[$k]['fpxz'] = "企业";
            }
            if ($v['status'] == 0) {
                $lists[$k]['status'] = "申请中";
            } else {
                $lists[$k]['status'] = "已开票";
            }

            if ($v['content'] != '') {
                $lists[$k]['pz_status'] = "已上传";
            } else {
                $lists[$k]['pz_status'] = "未上传";
            }

        }
        $this->assign('Nowpage', $Nowpage); //当前页
        $this->assign('allpage', $allpage); //总页数
        $this->assign('val', $key);
        if (input('get.page')) {
            return json($lists);
        }
        return $this->fetch();
    }
}