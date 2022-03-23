<?php
namespace app\admin\controller;
use think\Db;
class Subcommission extends Base
{
    /**
     * [index 分佣比例设置]
     * @return [type] [description]
     
     */
    public function index(){
        $Nowpage = input('get.page') ? input('get.page'):1;
        $limits = config('list_rows');// 获取总条数
        $count = Db::name('fenyongbili')->count();//计算总页面
        $allpage = intval(ceil($count / $limits));
        $lists = Db::name('fenyongbili')->page($Nowpage, $limits)->select();
        $this->assign('Nowpage', $Nowpage); //当前页
        $this->assign('allpage', $allpage); //总页数 
        if(input('get.page'))
        {
            return json($lists);
        }
        return $this->fetch();
    }
    
    
    public function add_subcommission(){
        if(request()->isAjax()){
            $param = input('post.');
            $add = Db::name('fenyongbili')->insert($param);
            if($add){
                return json(['code' => 1, 'data' => '', 'msg' => '添加成功']);
            }else{
                return json(['code' => 0, 'data' => '', 'msg' => '添加失败']);
            }
        }
        return $this->fetch();
    }
    
    
    public function del_subcommission()
    {
        $id = input('id');
        $del = Db::name('fenyongbili')->where('id',$id)->delete();
        if($del){
            return json(['code' => 1, 'data' => '', 'msg' => '删除成功']);
        }else{
            return json(['code' => 2, 'data' => '', 'msg' => '删除失败']);
        }
    }
    
    
    public function edit_subcommission(){
        $id = input('id');
        if(request()->isAjax()){
            $param = input('post.');
            $flag = Db::name('fenyongbili')->where('id',$param['id'])->update($param);
            if($flag){
                return json(['code' => 1, 'data' => '', 'msg' => '修改成功']);
            }else{
                return json(['code' => 2, 'data' => '', 'msg' => '修改失败']);
            }
        }
        $rs = Db::name('fenyongbili')->where('id',$id)->find();
        $this->assign('rs',$rs);
        return $this->fetch();
    }
}