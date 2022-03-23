<?php

namespace app\admin\controller;

use think\Db;

class Occupation extends Base
{
    /**
     * [index 职业]
     * @return [type] [description]
     */
    public function index()
    {
        $key = input('key');
        $map = [];
        if ($key && $key !== "") {
            $map['title'] = ['like', "%" . $key . "%"];
        }
        $Nowpage = input('get.page') ? input('get.page') : 1;
        $limits = config('list_rows');// 获取总条数
        $count = Db::name('occupation')->where('is_del',0)->where($map)->count();//计算总页面
        $allpage = intval(ceil($count / $limits));
        $lists = Db::name('occupation')->where('is_del',0)->where($map)->page($Nowpage, $limits)->select();
        $this->assign('Nowpage', $Nowpage); //当前页
        $this->assign('allpage', $allpage); //总页数 
        $this->assign('val', $key);
        if (input('get.page')) {
            return json($lists);
        }
        return $this->fetch();
    }


    public function add()
    {
        if (request()->isAjax()) {
            $param = input('post.');
            $user = Db::name('occupation')->where('title', $param['title'])->find();
            if ($user) {
                return json(['code' => 0, 'data' => '', 'msg' => '标题已存在']);
            }
            $add = Db::name('occupation')->insert($param);
            if ($add) {
                return json(['code' => 1, 'data' => '', 'msg' => '添加成功']);
            } else {
                return json(['code' => 0, 'data' => '', 'msg' => '添加失败']);
            }
        }
        return $this->fetch();
    }


    public function del()
    {
        $id = input('id');
        if ($id) {
            $arr = array("is_del" => 1);
            $del = Db::name('occupation')->where('id', $id)->update($arr);
            if ($del) {
                return json(['code' => 1, 'data' => '', 'msg' => '删除成功']);
            } else {
                return json(['code' => 2, 'data' => '', 'msg' => '删除失败']);
            }
        } else {
            return json(['code' => 2, 'data' => '', 'msg' => '参数错误']);
        }
    }


    public function edit()
    {
        $id = input('id');
        if (request()->isAjax()) {
            $param = input('post.');
            $user = Db::name('occupation')->where('title', $param['title'])->where('id', 'neq', $param['id'])->find();
            if ($user) {
                return json(['code' => 0, 'data' => '', 'msg' => '标题已存在']);
            }
            $flag = Db::name('occupation')->where('id', $param['id'])->update($param);
            if ($flag) {
                return json(['code' => 1, 'data' => '', 'msg' => '修改成功']);
            } else {
                return json(['code' => 2, 'data' => '', 'msg' => '修改失败']);
            }
        }
        $rs = Db::name('occupation')->where('id', $id)->find();
        $this->assign('rs', $rs);
        return $this->fetch();
    }
}