<?php


namespace app\admin\controller;

use app\admin\controller\Winxinrefund;
use app\admin\model\ShenqingModel;
use think\Db;

class Shenqing extends Base
{

    //申请列表
    public function index()
    {
        $key = input('key');
        $map = [];
        if ($key && $key !== "") {
            $map['title'] = ['like', "%" . $key . "%"];
        }
        $map['status'] = 0;
        $Nowpage = input('get.page') ? input('get.page') : 1;
        $limits = config('list_rows');// 获取总条数
        $count = Db::name('shenqing')->where($map)->count();//计算总页面
        $allpage = intval(ceil($count / $limits));
        $shenqing = new ShenqingModel();
        $lists = $shenqing->getShenqingByWhere($map, $Nowpage, $limits);
        foreach ($lists as $k => $v) {
            $v['type_name'] = $this->type($v['type']);
            //课程
            $curriculum = Db::name('curriculum')->where('id', $v['cid'])->find();
            $v['kc_name'] = $curriculum['kcname'];
            //学生
            $student = Db::name('student')->where('id', $v['uid'])->find();
            $v['student'] = $student['name'];
            //
            if ($v['method'] == '线上') {
                $v['method'] = '线下转线上';
            } else {
                $v['method'] = '线上转线下';
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

    public function type($type)
    {
        switch ($type) {
            case 0:
            {
                $value = '不退费不补缴';
                break;
            }
            case 1:
            {
                $value = '补缴';
                break;
            }
            case 2:
            {
                $value = '退费';
                break;
            }
            case 3:
            {
                $value = '取消报名';
                break;
            }
        }
        return $value;
    }

    //
    public function tb()
    {
        $id = input('id');
        $type = input('type');
        $shenqing = Db::name('shenqing')->where('id', $id)->find();
        $order = Db::name('order')->where('id', $shenqing['orderid'])->find();
        $this->assign('order', $order);
        $this->assign('shenqing', $shenqing);
        $this->assign('type', $type);
        if (request()->isAjax()) {
            $param = input('param.');
            //type == 1补缴 ==2退费
            if ($param['type'] == 1) {
                //查询课程
                $curriculum = Db::name('curriculum')->where('id', $param['cid'])->find();
                //查询课程线下报名人数
                $rs = Db::name('gm_kecheng')->where('cid = ' . $param['cid'] . ' and method = "线下"')->where('status', 1)->count();
                if ($rs >= $curriculum['xx_number']) {
                    return json(['code' => -1, 'msg' => '操作失败,线下招生人数已满']);
                }
                $data['value'] = $param['value'];
                //$data['content'] = $param['content'];
                $data['update_time'] = date('Y-m-d H:i:s', time());
                $data['status'] = 3;
                $flag = Db::name('shenqing')->where('id', $param['id'])->update($data);
            } else {
                $data['value'] = $param['value'];
                $data['content'] = $param['content'];
                $data['update_time'] = date('Y-m-d H:i:s', time());
                $data['status'] = 1;
                $flag = Db::name('shenqing')->where('id', $param['id'])->update($data);
            }
            if ($flag > 0) {
                if ($param['type'] == 2) {
                    //$tmoney = $param['value'] * 0.06;
                    $tmoney = '0.01';
                    $order1 = Db::name('order')->where('id', $param['orderId'])->find();
                    $sxf = $param['value'] * 0.06;
                    $tmoney = $param['value'] - $sxf;
                    $user = Db::name("student")->where("id", $order['uid'])->find();
                    $pay = new Winxinrefund($user['openid'], $order['orderId'], $tmoney, $shenqing['order'], $tmoney);
                    $result = $pay->refund();
                    if ($result['return_code'] == "SUCCESS" && $result['result_code'] == "SUCCESS") {
                        Db::name('order')->where('id', $param['orderId'])->setInc('tk_money', $tmoney);
                        Db::name('order')->where('id', $param['orderId'])->setInc('sxf', $sxf);
                        $tb_data['type'] = 2;
                        $tb_data['value'] = $tmoney;
                        $tb_data['time'] = date('Y-m-d H:i:s', time());
                        $tb_data['oid'] = $order1['id'];
                        $tb_data['sxf'] = $sxf;
                        $tb_data['status'] = 1;
                        Db::name('tb_list')->insert($tb_data);
                    }
                    // $sxf = $param['value'] * 0.06;
                    // //查询订单
                    // $order1 = Db::name('order')->where('id',$param['orderId'])->find();
                    // //退款金额
                    // $ord_data['tk_money'] = $order1['tk_money'] + $param['value'] - $sxf;
                    // //手续费
                    // $ord_data['sxf'] = $order1['sxf'] + $sxf;
                    // //更改订单
                    // Db::name('order')->where('id',$param['orderId'])->update($ord_data);
                    // //添加退补
                    // $tb_data['type'] = 2;
                    // $tb_data['value'] = $param['value'] - $sxf;
                    // $tb_data['time'] = date('Y-m-d H:i:s',time());
                    // $tb_data['oid'] = $order1['id'];
                    // $tb_data['sxf'] = $sxf;
                    // $tb_data['status'] = 1;
                    // Db::name('tb_list')->insert($tb_data);
                }
                return json(['code' => 1, 'msg' => '操作成功']);
            } else {
                return json(['code' => -1, 'msg' => '操作失败']);
            }
        }
        return $this->fetch();
    }

    public function tongyi()
    {
        $id = input('id');
        $shenqing = Db::name('shenqing')->where('id', $id)->find();
        $gm_kecheng = Db::name('gm_kecheng')->where('status', 1)->where('orderid', $shenqing['orderid'])->select();
        foreach ($gm_kecheng as $k => $value) {
            //课程
            $curriculum = Db::name('curriculum')->where('id', $value['cid'])->find();
            if ($curriculum['xs_status'] == 0) {
                $data['method'] = '线上';
            } else {
                $data['method'] = '线下';
            }
        }
        $flag = Db::name('gm_kecheng')->where('status', 1)->where('orderid', $shenqing['orderid'])->update($data);

        if ($flag > 0) {
            Db::name('order')->where('id', $shenqing['orderid'])->update(['method' => '线上']);
            $sq_data['update_time'] = date('Y-m-d H:i:s', time());
            $sq_data['status'] = 1;
            Db::name('shenqing')->where('id', $id)->update($sq_data);
            return json(['code' => 1, 'msg' => '操作成功']);
        } else {
            return json(['code' => -1, 'msg' => '操作失败']);
        }
    }

    public function jujue()
    {
        $id = input('id');
        $data['status'] = 2;
        $data['update_time'] = date('Y-m-d H:i:s', time());
        $flag = Db::name('shenqing')->where('id', $id)->update($data);
        if ($flag > 0) {
            return json(['code' => 1, 'msg' => '操作成功']);
        } else {
            return json(['code' => -1, 'msg' => '操作失败']);
        }
    }

}