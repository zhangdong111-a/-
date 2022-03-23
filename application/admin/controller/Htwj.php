<?php

namespace app\admin\controller;

use think\Db;

class Htwj extends Base
{
    /**
     * [index 红头文件申请]
     */
    public function index()
    {
        $Nowpage = input('get.page') ? input('get.page') : 1;
        $limits = config('list_rows');// 获取总条数
        $count = Db::name('htwj')->count();//计算总页面
        $allpage = intval(ceil($count / $limits));
        //$lists = $question->getQuestionByWhere($map, $Nowpage, $limits);
        $lists = Db::name('htwj')->order('zt asc')->select();
        foreach ($lists as $k => $v) {
            $order = Db::name('order')->where('id', $v['orderid'])->find();
            $lists[$k]['kcname'] = Db::name('curriculum')->where('id', $order['cid'])->value('kcname');
            if ($v['account_type'] == 1) {
                $lists[$k]['account_type'] = '微信';
            } else {
                $lists[$k]['account_type'] = '邮箱';
            }
        }
        $this->assign('Nowpage', $Nowpage); //当前页
        $this->assign('allpage', $allpage); //总页数
        $this->assign('count', $count);
        if (input('get.page')) {
            return json($lists);
        }
        return $this->fetch();
    }

    public function chuli()
    {
        $id = input('id');
        $arr = array('zt' => 1);
        $up = Db::name('htwj')->where('id', $id)->update($arr);
        if ($up) {
            return json(['code' => 1, 'msg' => '操作成功']);
        }else{
            return json(['code' => 2, 'msg' => '操作失败']);
        }
    }
}