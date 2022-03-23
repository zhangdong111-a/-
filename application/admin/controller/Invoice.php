<?php

namespace app\admin\controller;

use think\Db;

class Invoice extends Base
{
    /**
     * [index 开票管理]
     * @return [type] [description]
     */
    public function index()
    {
        $key = input('key');
        $map = [];
        if ($key && $key !== "") {
            $map['name|kcname'] = ['like', "%" . $key . "%"];
        }
        $Nowpage = input('get.page') ? input('get.page') : 1;
        $limits = config('list_rows');// 获取总条数
        $count = Db::name('invoice')->where('zt', 0)->where($map)->count();//计算总页面
        $allpage = intval(ceil($count / $limits));
        $lists = Db::name('invoice')->where('zt', 0)->where($map)->page($Nowpage, $limits)->select();
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

    public function do_upload()
    {
        $id = input('id');
        $order = Db::name('invoice')->where('id', $id)->find();
        $file = request()->file('file');
        $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads/kaipiao');
        if ($info) {
            $arr = array('content' => $info->getSaveName());
            $up = Db::name('invoice')->where('id', $id)->update($arr);
            if ($up) {
                $arr = array("status" => 1);
                $as = array("kp_status" => 2);
                Db::name('invoice')->where('id', $id)->update($arr);
                Db::name('order')->where('id', $order['orderid'])->update($as);
                $data['code'] = 1;
                $data['msg'] = "上传成功";
                return json($data);
            }
        } else {
            echo $file->getError();
        }
    }

    public function fplx()
    {
        $key = input('key');
        $map = [];
        if ($key && $key !== "") {
            $map['title'] = ['like', "%" . $key . "%"];
        }
        $Nowpage = input('get.page') ? input('get.page') : 1;
        $limits = config('list_rows');// 获取总条数
        $count = Db::name('fplx')->where('is_del', 0)->where($map)->count();//计算总页面
        $allpage = intval(ceil($count / $limits));
        $lists = Db::name('fplx')->where('is_del', 0)->where($map)->page($Nowpage, $limits)->select();
        $this->assign('Nowpage', $Nowpage); //当前页
        $this->assign('allpage', $allpage); //总页数 
        $this->assign('val', $key);
        if (input('get.page')) {
            return json($lists);
        }
        return $this->fetch();
    }

    public function add_fplx()
    {
        if (request()->isAjax()) {
            $param = input('post.');
            $user = Db::name('fplx')->where('title', $param['title'])->find();
            if ($user) {
                return json(['code' => 0, 'data' => '', 'msg' => '类型标题已存在']);
            }
            $add = Db::name('fplx')->insert($param);
            if ($add) {
                return json(['code' => 1, 'data' => '', 'msg' => '添加成功']);
            } else {
                return json(['code' => 0, 'data' => '', 'msg' => '添加失败']);
            }
        }
        return $this->fetch();
    }


    public function del_fplx()
    {
        $id = input('id');
        if ($id) {
            $arr = array("is_del" => 1);
            $del = Db::name('fplx')->where('id', $id)->update($arr);
            if ($del) {
                return json(['code' => 1, 'data' => '', 'msg' => '删除成功']);
            } else {
                return json(['code' => 2, 'data' => '', 'msg' => '删除失败']);
            }
        } else {
            return json(['code' => 2, 'data' => '', 'msg' => '参数错误']);
        }
    }


    public function edit_fplx()
    {
        $id = input('id');
        if (request()->isAjax()) {
            $param = input('post.');
            $user = Db::name('fplx')->where('title', $param['title'])->where('id', 'neq', $param['id'])->find();
            if ($user) {
                return json(['code' => 0, 'data' => '', 'msg' => '类型标题已存在']);
            }
            $flag = Db::name('fplx')->where('id', $param['id'])->update($param);
            if ($flag) {
                return json(['code' => 1, 'data' => '', 'msg' => '修改成功']);
            } else {
                return json(['code' => 2, 'data' => '', 'msg' => '修改失败']);
            }
        }
        $rs = Db::name('fplx')->where('id', $id)->find();
        $this->assign('rs', $rs);
        return $this->fetch();
    }

    public function kpnr()
    {
        $key = input('key');
        $map = [];
        if ($key && $key !== "") {
            $map['nr'] = ['like', "%" . $key . "%"];
        }
        $Nowpage = input('get.page') ? input('get.page') : 1;
        $limits = config('list_rows');// 获取总条数
        $count = Db::name('kpnr')->where('is_del', 1)->where($map)->count();//计算总页面
        $allpage = intval(ceil($count / $limits));
        $lists = Db::name('kpnr')->where('is_del', 1)->where($map)->page($Nowpage, $limits)->select();
        $this->assign('Nowpage', $Nowpage); //当前页
        $this->assign('allpage', $allpage); //总页数
        $this->assign('val', $key);
        if (input('get.page')) {
            return json($lists);
        }
        return $this->fetch();
    }

    public function add_kpnr()
    {
        if (request()->isAjax()) {
            $param = input('post.');
            $add = Db::name('kpnr')->insert($param);
            if ($add) {
                return json(['code' => 1, 'data' => '', 'msg' => '添加成功']);
            } else {
                return json(['code' => 0, 'data' => '', 'msg' => '添加失败']);
            }
        }
        return $this->fetch();
    }

    public function edit_kpnr()
    {
        $id = input('id');
        if (request()->isAjax()) {
            $param = input('post.');
            $flag = Db::name('kpnr')->where('id', $param['id'])->update($param);
            if ($flag) {
                return json(['code' => 1, 'data' => '', 'msg' => '修改成功']);
            } else {
                return json(['code' => 2, 'data' => '', 'msg' => '修改失败']);
            }
        }
        $rs = Db::name('kpnr')->where('id', $id)->find();
        $this->assign('rs', $rs);
        return $this->fetch();
    }

    public function del_kpnr()
    {
        $id = input('id');
        if ($id) {
            $arr = array("is_del" => 2);
            $del = Db::name('kpnr')->where('id', $id)->update($arr);
            if ($del) {
                return json(['code' => 1, 'data' => '', 'msg' => '删除成功']);
            } else {
                return json(['code' => 2, 'data' => '', 'msg' => '删除失败']);
            }
        } else {
            return json(['code' => 2, 'data' => '', 'msg' => '参数错误']);
        }
    }

    public function kptj()
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
        foreach ($lists as $k => $v) {
            $lists[$k]['zmoney'] = Db::name('invoice')->where('status', 1)->where('cid', $v['id'])->sum('money');
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

    public function tjxq()
    {
        $kcid = input('kcid');
        $key = input('key');
        $map = [];
        if ($key && $key !== "") {
            $map['name'] = ['like', "%" . $key . "%"];
        }
        $Nowpage = input('get.page') ? input('get.page') : 1;
        $limits = config('list_rows');// 获取总条数
        $count = Db::name('invoice')->where($map)->where('cid', $kcid)->where('status', 1)->count();//计算总页面
        $allpage = intval(ceil($count / $limits));
        $lists = Db::name('invoice')->where($map)->where('cid', $kcid)->where('status', 1)->page($Nowpage, $limits)->select();
        $this->assign('Nowpage', $Nowpage); //当前页
        $this->assign('allpage', $allpage); //总页数
        $this->assign('val', $key);
        $this->assign('count', $count);
        $this->assign('kcid', $kcid);
        if (input('get.page')) {
            return json($lists);
        }
        return $this->fetch();
    }

    public function subkc()
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
        foreach ($lists as $k => $v) {
            $lists[$k]['zmoney'] = Db::name('invoice')->where('status', 1)->where('cid', $v['id'])->sum('money');
        }
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

    public function tysq()
    {
        $id = input('id');
        $arr = array('zt' => 1);
        $up = Db::name('invoice')->where('id', $id)->update($arr);
        if ($up) {
            return json(['code' => 1, 'data' => '', 'msg' => '操作成功']);
        } else {
            return json(['code' => 1, 'data' => '', 'msg' => '操作失败']);
        }
    }

    public function jjsq()
    {
        $id = input('id');
        //$order = Db::name('order')->where('id', $id)->find();
        $invoice = Db::name('invoice')->where('id', $id)->find();
        $del = Db::name('invoice')->where('id', $id)->delete();
        if ($del) {
            $arr = array("kp_status" => 0);
            $up = Db::name('order')->where('id', $invoice['orderid'])->update($arr);
            if ($up) {
                return json(['code' => 1, 'data' => '', 'msg' => '操作成功']);
            }
        } else {
            return json(['code' => 1, 'data' => '', 'msg' => '操作失败']);
        }
    }
}