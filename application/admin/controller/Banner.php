<?php


namespace app\admin\controller;


use app\admin\model\BannerModel;
use think\Db;

class Banner extends Base
{
    /**
     * [index 轮播图列表]
     */
    public function index(){
        $map = [];
        $Nowpage = input('get.page') ? input('get.page'):1;
        $limits = config('list_rows');// 获取总条数
        $count = Db::name('banner')->where($map)->count();//计算总页面
        $allpage = intval(ceil($count / $limits));
        $banner = new BannerModel();
        $lists = $banner->getBannerByWhere($map, $Nowpage, $limits);
        $this->assign('Nowpage', $Nowpage); //当前页
        $this->assign('allpage', $allpage); //总页数
        $this->assign('count', $count);
        if(input('get.page')){
            return json($lists);
        }
        return $this->fetch();
    }

    /**
     * [add_banner 添加轮播图]
     */
    public function add_banner(){
        if(request()->isAjax()){
            $param = input('post.');
            $banner = new BannerModel();
            $flag = $banner->insertBanner($param);
            return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
        }
        return $this->fetch();
    }

    /**
     * [edit_banner 编辑轮播图]
     */
    public function edit_banner(){
        $banner = new BannerModel();
        if(request()->isAjax()){
            $param = input('post.');
            $flag = $banner->editBanner($param);
            return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
        }
        $id = input('param.id');
        $info = $banner->getOneBanner($id);
        $this->assign('info',$info);
        return $this->fetch();
    }

    /**
     * [del_banner 删除分销机构]
     */
    public function del_banner(){
        $id = input('param.id');
        $banner = new BannerModel();
        $flag = $banner->delBanner($id);
        return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
    }

    /**
     * [banner_state 轮播图状态]
     */
    public function banner_state(){
        $id=input('param.id');
        $status = Db::name('banner')->where(array('id'=>$id))->value('status');//判断当前状态情况
        if($status==1){
            $flag = Db::name('banner')->where(array('id'=>$id))->setField(['status'=>0]);
            return json(['code' => 1, 'data' => $flag['data'], 'msg' => '已禁止']);
        } else {
            $flag = Db::name('banner')->where(array('id'=>$id))->setField(['status'=>1]);
            return json(['code' => 0, 'data' => $flag['data'], 'msg' => '已开启']);
        }
    }

}