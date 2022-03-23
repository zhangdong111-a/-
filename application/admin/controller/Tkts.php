<?php
namespace app\admin\controller;
use think\Db;
class Tkts extends Base
{
    /**
     * [index 退款温馨提示]
     * @return [type] [description]
     
     */
    public function index(){
        $key = input('key');
        $map = [];
        if($key&&$key!=="")
        {
            $map['title'] = ['like',"%" . $key . "%"];          
        }       
        $Nowpage = input('get.page') ? input('get.page'):1;
        $limits = config('list_rows');// 获取总条数
        $count = Db::name('reminder')->where($map)->count();//计算总页面
        $allpage = intval(ceil($count / $limits));
        $lists = Db::name('reminder')->where($map)->page($Nowpage, $limits)->select();
        $this->assign('Nowpage', $Nowpage); //当前页
        $this->assign('allpage', $allpage); //总页数 
        $this->assign('val', $key);
        if(input('get.page'))
        {
            return json($lists);
        }
        return $this->fetch();
    }
    
    
    public function add_tkts(){
        if(request()->isAjax()){
            $param = input('post.');
            $user = Db::name('reminder')->where('title',$param['title'])->find();
            if($user){
                return json(['code' => 0, 'data' => '', 'msg' => '标题已存在']);
            }
            $add = Db::name('reminder')->insert($param);
            if($add){
                return json(['code' => 1, 'data' => '', 'msg' => '添加成功']);
            }else{
                return json(['code' => 0, 'data' => '', 'msg' => '添加失败']);
            }
        }
        return $this->fetch();
    }
    
    
    public function del_tkts(){
        $id = input('id');
        if($id){
            $del = Db::name('reminder')->where('id',$id)->delete();
            if($del){
                return json(['code' => 1, 'data' => '', 'msg' => '删除成功']);
            }else{
                return json(['code' => 2, 'data' => '', 'msg' => '删除失败']);
            }
        }else{
            return json(['code' => 2, 'data' => '', 'msg' => '参数错误']);
        }
    }
    
    
    public function edit_tkts(){
        $id = input('id');
        if(request()->isAjax()){
            $param = input('post.');
            $user = Db::name('reminder')->where('title',$param['title'])->where('id','neq',$param['id'])->find();
            if($user){
                return json(['code' => 0, 'data' => '', 'msg' => '标题已存在']);
            }
            $flag = Db::name('reminder')->where('id',$param['id'])->update($param);
            if($flag){
                return json(['code' => 1, 'data' => '', 'msg' => '修改成功']);
            }else{
                return json(['code' => 2, 'data' => '', 'msg' => '修改失败']);
            }
        }
        $rs = Db::name('reminder')->where('id',$id)->find();
        $this->assign('rs',$rs);
        return $this->fetch();
    }
}