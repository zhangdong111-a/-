<?php
namespace app\admin\controller;
use app\admin\model\BaomingModel;
use think\Db;

class Baoming extends Base
{
    /**
     * [index 报名列表]
     */
    public function index(){
        $Nowpage = input('get.page') ? input('get.page'):1;
        $limits = config('list_rows');// 获取总条数
        //$count = Db::name('gm_kecheng')->where('pid',0)->where('status',1)->field("count(*) as number,cid")->group("cid")->select();
        $count = Db::name('curriculum')->where('pid',0)->count();
        $allpage = intval(ceil($count / $limits));
        //$lists = Db::name('gm_kecheng')->where('pid',0)->where('status',1)->field("*,count(*) as number,cid")->group("cid")->page($Nowpage,$limits)->select();
        $lists = Db::name('curriculum')->where('pid',0)->select();
        foreach ($lists as $k => $v){
            if($v['lx'] == 1){
                $lists[$k]['ddsl'] = Db::name('gm_kecheng')->where('cid',$v['id'])->where('dabao',2)->where('status',1)->count();
                $lists[$k]['dbsl'] = Db::name('gm_kecheng')->where('cid',$v['id'])->where('dabao',1)->where('status',1)->count();
            }else{
                $lists[$k]['ddsl'] = Db::name('gm_kecheng')->where('pid',$v['id'])->where('dabao',2)->where('status',1)->count();
                $lists[$k]['dbsl'] = Db::name('gm_kecheng')->where('cid',$v['id'])->where('dabao',1)->where('status',1)->count();
            }
        }
        $this->assign('Nowpage', $Nowpage); //当前页
        $this->assign('allpage', $allpage); //总页数 
        $this->assign('count', $count); //总页数 
        if(input('get.page'))
        {
            return json($lists);
        }
        return $this->fetch();
    }
    
    public function info()
    {
        $cid = input('cid');//课程id
        $lx = input('lx');//课程类型
        $Nowpage = input('get.page') ? input('get.page'):1;
        $limits = config('list_rows');// 获取总条数
        if($lx == 1){
            $count = Db::name('gm_kecheng')->where('pid',0)->where('cid',$cid)->where('status', 1)->count();//计算总页面
            $allpage = intval(ceil($count / $limits));
            $lists = Db::name('gm_kecheng')->where('pid',0)->where('cid',$cid)->where('status', 1)->page($Nowpage, $limits)->select();
        }else{
            $count = Db::name('gm_kecheng')->where('pid',$cid)->where('status', 1)->count();//计算总页面
            $allpage = intval(ceil($count / $limits));
            $lists = Db::name('gm_kecheng')->where('pid',$cid)->where('status', 1)->page($Nowpage, $limits)->select();
        }
        
        foreach ($lists as $k => $v){
            $lists[$k]['kcname'] = Db::name('curriculum')->where('id',$v['cid'])->value('kcname');
            $lists[$k]['uid'] = Db::name('student')->where('id',$v['uid'])->value('name');
            $lists[$k]['jg_id'] = Db::name('place')->where('id',$v['jg_id'])->value('title');
            if($v['lx'] == 1){
                $lists[$k]['lx'] = '一级课程';
            }else {
                $lists[$k]['lx'] = '二级课程';
            }
            if($v['dabao'] == 1){
                $lists[$k]['dabao'] = '打包购买';
            }else {
                $lists[$k]['dabao'] = '单独购买';
            }
            if($v['price_type'] == 1){
                $lists[$k]['price_type'] = '早鸟价';
            }elseif($v['price_type'] == 2){
                $lists[$k]['price_type'] = '原价';
            }elseif($v['price_type'] == 3){
                $lists[$k]['price_type'] = '团报价';
            }else{
                $lists[$k]['price_type'] = '学生价';
            }
        }
        $this->assign('Nowpage', $Nowpage); //当前页
        $this->assign('allpage', $allpage); //总页数 
        $this->assign('cid', $cid);
        $this->assign('lx', $lx);
        $this->assign('count', $count); //总页数
        if(input('get.page'))
        {
            return json($lists);
        }
        return $this->fetch();
    }
}