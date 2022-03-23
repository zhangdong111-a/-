<?php
namespace app\admin\controller;
use think\Db;
class Student extends Base
{
    /**
     * [index 学员列表]
     * @return [type] [description]
     
     */
    public function index(){
        $key = input('key');
        $map = [];
        if($key&&$key!=="")
        {
            $map['name|account|phone'] = ['like',"%" . $key . "%"];          
        }       
        $Nowpage = input('get.page') ? input('get.page'):1;
        $limits = config('list_rows');// 获取总条数
        $count = Db::name('student')->where($map)->where("phone",'neq','')->count();//计算总页面
        $allpage = intval(ceil($count / $limits));
        $lists = Db::name('student')->where($map)->where("phone",'neq','')->page($Nowpage, $limits)->select();
        $this->assign('Nowpage', $Nowpage); //当前页
        $this->assign('allpage', $allpage); //总页数 
        $this->assign('val', $key);
        if(input('get.page'))
        {
            return json($lists);
        }
        return $this->fetch();
    }
    
    
    public function edit_student(){
        $id = input('id');
        if(request()->isAjax()){
            $param = input('post.');
            $user = Db::name('student')->where('phone',$param['phone'])->where('id','neq',$param['id'])->find();
            if($user){
                return json(['code' => 0, 'data' => '', 'msg' => '手机号已存在']);
            }
            $flag = Db::name('student')->where('id',$param['id'])->update($param);
            if($flag){
                return json(['code' => 1, 'data' => '', 'msg' => '修改成功']);
            }else{
                return json(['code' => 2, 'data' => '', 'msg' => '修改失败']);
            }
        }
        $rs = Db::name('student')->where('id',$id)->find();
        $this->assign('rs',$rs);
        return $this->fetch();
    }
    
    
    
    public function add_student(){
        if(request()->isAjax()){
            $param = input('post.');
            $user = Db::name('student')->where('phone',$param['phone'])->find();
            if($user){
                return json(['code' => 0, 'data' => '', 'msg' => '手机号已存在']);
            }
            $param['create_time'] = date('Y-m-d H:i:s');
            $add = Db::name('student')->insert($param);
            if($add){
                return json(['code' => 1, 'data' => '', 'msg' => '添加成功']);
            }else{
                return json(['code' => 0, 'data' => '', 'msg' => '添加失败']);
            }
        }
        return $this->fetch();
    }
    
    
    public function reset_password(){
        $id = input('id');
        if(!empty($id)){
            $arr = array("password"=>md5(123456));
            $update = Db::name('student')->where('id',$id)->update($arr);
            if($update){
                return ['code' => 1, 'data' => '', 'msg' => '重置成功'];
            }else {
                return ['code' => 2, 'data' => '', 'msg' => '重置失败'];
            }
        }else{
            return ['code' => 2, 'data' => '', 'msg' => '参数错误'];
        }
    }
    
    
    public function ck_kc()
    {
        $xyid = input('id');
        $Nowpage = input('get.page') ? input('get.page'):1;
        $limits = config('list_rows');// 获取总条数
        //$count = Db::name('student')->where($map)->count();//计算总页面
        $count = Db::name('order')->where('uid',$xyid)->where('pay_status',1)->count();
        $allpage = intval(ceil($count / $limits));
        //$lists = Db::name('student')->where($map)->page($Nowpage, $limits)->select();
        $lists = Db::name('order')->where('uid',$xyid)->where('pay_status',1)->page($Nowpage, $limits)->order('id desc')->select();
        foreach ($lists as $k => $v){
            $lists[$k]['cid'] = Db::name('curriculum')->where('id',$v['cid'])->value('kcname');
            $lists[$k]['place_id'] = Db::name('place')->where('id',$v['place_id'])->value('title');
            if($v['price_type'] == 1){
                $lists[$k]['price_type'] = "早鸟价";
            }elseif ($v['price_type'] == 2) {
                $lists[$k]['price_type'] = "原价";
            }elseif($v['price_type'] == 3){
                $lists[$k]['price_type'] = "团报价";
            }else{
                $lists[$k]['price_type'] = "学生价";
            }
            
            if($v['dabao'] == 1){
                $lists[$k]['dabao'] = "打包购买";
            }else{
                $lists[$k]['dabao'] = "单独购买";
            }
            
            if($v['lx'] == 1){
                $lists[$k]['lx'] = "一级课程";
            }else{
                $lists[$k]['lx'] = "二级课程";
            }
        }
        $this->assign('Nowpage', $Nowpage); //当前页
        $this->assign('allpage', $allpage); //总页数 
        $this->assign('xyid', $xyid); //总页数 
        if(input('get.page'))
        {
            return json($lists);
        }
        return $this->fetch();
    }
}