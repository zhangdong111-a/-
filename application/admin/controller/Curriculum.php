<?php
namespace app\admin\controller;
use think\Db;
class Curriculum extends Base
{
    /**
     * [index 一级课程列表]
     * @return [type] [description]
     
     */
    public function oneindex(){
        $key = input('key');
        $map = [];
        if($key&&$key!=="")
        {
            $map['kcname'] = ['like',"%" . $key . "%"];  
        } 
        
        $Nowpage = input('get.page') ? input('get.page'):1;
        $limits = config('list_rows');// 获取总条数
        $count = Db::name('curriculum')->where('lx=1')->where($map)->count();//计算总页面
        $allpage = intval(ceil($count / $limits));
        $lists = Db::name('curriculum')->where('lx=1')->where($map)->page($Nowpage, $limits)->select();
        $this->assign('Nowpage', $Nowpage); //当前页
        $this->assign('allpage', $allpage); //总页数 
        $this->assign('val', $key);
        if(input('get.page'))
        {
            return json($lists);
        }
        return $this->fetch();
    }
    
    
    public function del_curriculum(){
        $id = input('id');
        if($id){
            $del = Db::name('curriculum')->where('id',$id)->delete();
            if($del){
                return json(['code' => '1', 'data' => '', 'msg' => '删除成功']);
            }else{
                return json(['code' => '2', 'data' => '', 'msg' => '删除失败']);
            }
        }
    }
    
    
    public function edit_curriculum(){
        if(request()->isPost()){           
            $param = input('post.');
            $up = Db::name('curriculum')->where('id',$param['id'])->update($param);
            if($up){
                $this->success('保存成功！');
            }else{
                $this->error('保存失败！');
            }
        }
        $id = input('param.id');
        $rs = Db::name('curriculum')->where('id',$id)->find();
        $this->assign('rs',$rs);
        return $this->fetch();
    }
    
    
    public function add_curriculum(){
        if(request()->isPost()){           
            $param = input('post.');
            $param['lx'] = 1;
            $add = Db::name('curriculum')->insert($param);
            if($add){
                return json(['code' => '1', 'data' => '', 'msg' => '添加成功']);
            }else{
                return json(['code' => '2', 'data' => '', 'msg' => '添加失败']);
            }
        }
        return $this->fetch();
    }
    
    
    public function fenyong()
    {
        $kcid = input('id');
        $Nowpage = input('get.page') ? input('get.page'):1;
        $limits = config('list_rows');// 获取总条数
        $count = Db::name('fenyong')->where('kc_id',$kcid)->count();//计算总页面
        $allpage = intval(ceil($count / $limits));
        $lists = Db::name('fenyong')->where('kc_id',$kcid)->page($Nowpage, $limits)->select();
        foreach ($lists as $k=>$v){
            $lists[$k]['kc_name'] = Db::name('curriculum')->where('id',$v['kc_id'])->value('kcname');
            $lists[$k]['jg_name'] = Db::name('place')->where('id',$v['jg_id'])->value('title');
        }
        $this->assign('Nowpage', $Nowpage); //当前页
        $this->assign('allpage', $allpage); //总页数 
        $this->assign('kcid',$kcid);
        if(input('get.page'))
        {
            return json($lists);
        }
        return $this->fetch();
    }
    
    
    public function del_fenyong(){
        $id = input('id');
        $del = Db::name('fenyong')->where('id',$id)->delete();
        if($del){
            return json(['code' => '1', 'data' => '', 'msg' => '删除成功']);
        }else{
            return json(['code' => '2', 'data' => '', 'msg' => '删除失败']);
        }
    }
    
    
    public function add_fenyong_jg(){
        $kcid = input('id');
        $fenyong = Db::name('fenyong')->where('kc_id',$kcid)->select();
        if($fenyong){
            $str = '';
            foreach ($fenyong as $k=>$v){
                $str = $str.$v['jg_id'].',';
            }
            $newstr = substr($str,0,strlen($str)-1);
            $jigou = Db::name('place')->where('status',1)->where('id','not in',$newstr)->select();
        }else{
            $jigou = Db::name('place')->where('status',1)->select();
        }
        $this->assign('kcid',$kcid);
        $this->assign('jigou',$jigou);
        return $this->fetch();
    }
    
    
    public function add_fenyong(){
        $jigou = input('ids');
        $kcid = input('kcid');
        $newstr = substr($jigou,0,strlen($jigou)-1);
        $jigou = explode(',', $newstr);
        foreach ($jigou as $kk=>$vv){
            $arr = array("kc_id"=>$kcid,"jg_id"=>$vv);
            $add = Db::name('fenyong')->insert($arr);
        }
        if($add){
            return json(['code' => '1', 'data' => '', 'msg' => '添加成功']);
        }else{
            return json(['code' => '2', 'data' => '', 'msg' => '添加失败']);
        }
    }
    
    
    public function upfy(){
        $id = input('id');
        $fenyong = input('fenyong');
        $arr = array('fenyong'=>$fenyong);
        $up = Db::name('fenyong')->where('id',$id)->update($arr);
        if($up){
            return json(['code' => '1', 'data' => '', 'msg' => '操作成功']);
        }else{
            return json(['code' => '2', 'data' => '', 'msg' => '操作失败']);
        }
    }
    
    /**
     * [index 二级课程列表]
     * @return [type] [description]
     */
     
     public function twoindex(){
        $key = input('key');
        $map = [];
        if($key&&$key!=="")
        {
            $map['kcname'] = ['like',"%" . $key . "%"];  
        } 
        $Nowpage = input('get.page') ? input('get.page'):1;
        $limits = config('list_rows');// 获取总条数
        $count = Db::name('curriculum')->where('lx=2')->where('pid',0)->where($map)->count();//计算总页面
        $allpage = intval(ceil($count / $limits));
        $lists = Db::name('curriculum')->where('lx=2')->where('pid',0)->where($map)->page($Nowpage, $limits)->select();
        $this->assign('Nowpage', $Nowpage); //当前页
        $this->assign('allpage', $allpage); //总页数 
        $this->assign('val', $key);
        if(input('get.page'))
        {
            return json($lists);
        }
        return $this->fetch();
    }
    
    public function add_two_curriculum(){
        if(request()->isPost()){
            $param = input('post.');
            $param['lx'] = 2;
            $add = Db::name('curriculum')->insert($param);
            if($add){
                return json(['code' => '1', 'data' => '', 'msg' => '添加成功']);
            }else{
                return json(['code' => '2', 'data' => '', 'msg' => '添加失败']);
            }
        }
        return $this->fetch();
    }
    
    
    //子课程
    public function sub_curriculum(){
        $id = input('id');
        $Nowpage = input('get.page') ? input('get.page'):1;
        $limits = config('list_rows');// 获取总条数
        $count = Db::name('curriculum')->where('pid',$id)->count();//计算总页面
        $allpage = intval(ceil($count / $limits));
        $lists = Db::name('curriculum')->where('pid',$id)->page($Nowpage, $limits)->select();
        $this->assign('Nowpage', $Nowpage); //当前页
        $this->assign('allpage', $allpage); //总页数
        $this->assign('id', $id);
        if(input('get.page'))
        {
            return json($lists);
        }
        return $this->fetch();
    }
    
    
    //子课程添加
    public function add_sub_curriculum(){
        $id = input('id');
        if(request()->isPost()){
            $param = input('post.');
            $param['lx'] = 2;
            $add = Db::name('curriculum')->insert($param);
            if($add){
                return json(['code' => '1', 'data' => '', 'msg' => '添加成功']);
            }else{
                return json(['code' => '2', 'data' => '', 'msg' => '添加失败']);
            }
        }
        $this->assign('id',$id);
        return $this->fetch();
    }
    
    
    public function edit_two_curriculum()
    {
        if(request()->isPost()){
            $param = input('post.');
            $up = Db::name('curriculum')->where('id',$param['id'])->update($param);
            if($up){
                $this->success('保存成功！');
            }else{
                $this->error('保存失败！');
            }
        }
        $id = input('param.id');
        $rs = Db::name('curriculum')->where('id',$id)->find();
        $this->assign('rs',$rs);
        return $this->fetch();
    }
    
    
    public function edit_sub_curriculum()
    {
        if(request()->isPost()){
            $param = input('post.');
            $up = Db::name('curriculum')->where('id',$param['id'])->update($param);
            if($up){
                $this->success('保存成功！');
            }else{
                $this->error('保存失败！');
            }
        }
        $id = input('param.id');
        $rs = Db::name('curriculum')->where('id',$id)->find();
        $this->assign('rs',$rs);
        return $this->fetch();
    }

    public function fybl(){
        $id = input('id');
        $kc_id = input('kc_id');
        $jg_id = input('jg_id');
        $Nowpage = input('get.page') ? input('get.page'):1;
        $limits = config('list_rows');// 获取总条数
        $count = Db::name('fenyongbili')->where('fenyongid',$id)->count();//计算总页面
        $allpage = intval(ceil($count / $limits));
        $lists = Db::name('fenyongbili')->where('fenyongid',$id)->page($Nowpage, $limits)->select();
        $this->assign('Nowpage', $Nowpage); //当前页
        $this->assign('allpage', $allpage); //总页数
        $this->assign('id', $id); //总页数
        $this->assign('kc_id', $kc_id); //总页数
        $this->assign('jg_id', $jg_id); //总页数
        if(input('get.page'))
        {
            return json($lists);
        }
        return $this->fetch();
    }

    public function add_fybl(){
        $id = input('id');
        $kc_id = input('kc_id');
        $jg_id = input('jg_id');
        if(request()->isAjax()){
            $param = input('post.');
            $add = Db::name('fenyongbili')->insert($param);
            if($add){
                return json(['code' => 1, 'data' => '', 'msg' => '添加成功']);
            }else{
                return json(['code' => 0, 'data' => '', 'msg' => '添加失败']);
            }
        }
        $this->assign('id', $id);
        $this->assign('kc_id', $kc_id);
        $this->assign('jg_id', $jg_id);
        return $this->fetch();
    }
    
    public function edit_fybl(){
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
    
    
    public function del_fybl(){
        $id = input('id');
        $del = Db::name('fenyongbili')->where('id',$id)->delete();
        if($del){
            return json(['code' => 1, 'data' => '', 'msg' => '删除成功']);
        }else{
            return json(['code' => 2, 'data' => '', 'msg' => '删除失败']);
        }
    }
}