<?php

namespace app\admin\controller;

use app\admin\model\KefuModel;
use app\admin\model\PlaceModel;
use app\admin\model\PlaceuserModel;
use org\QRcode;
use think\Db;
use think\Model;
use think\Request;

class Place extends Base
{
    /**
     * [index 分销机构列表]
     */

    public function index(){
        $key = input('key');
        $map = [];
        if($key&&$key!==""){
            $map['title'] = ['like',"%" . $key . "%"];
        }
        $Nowpage = input('get.page') ? input('get.page'):1;
        $limits = config('list_rows');// 获取总条数
        $count = Db::name('place')->where($map)->count();//计算总页面
        $allpage = intval(ceil($count / $limits));
        $place = new PlaceModel();
        $lists = $place->getPlaceByWhere($map, $Nowpage, $limits);
        foreach ($lists as $k=>$v){
            $v['create_time'] = date('Y-m-d H:i:s',$v['create_time']);
            $lists[$k] =$v;
        }
        $this->assign('Nowpage', $Nowpage); //当前页
        $this->assign('allpage', $allpage); //总页数
        $this->assign('count', $count);
        $this->assign('val', $key);
        $request = Request::instance();
        $domain = $request->domain();
        $this->assign('http',$domain);
        if(input('get.page')){
            return json($lists);
        }
        return $this->fetch();
    }

    /**
     * [add_article 添加分销机构]
     */
    public function add_place(){
        if(request()->isAjax()){
            $param = input('post.');
            $place = new PlaceModel();
            $flag = $place->insertPlace($param);
            return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
        }
        return $this->fetch();
    }

    /**
     * [edit_place 编辑分销机构]
     */
    public function edit_place(){
        $place = new PlaceModel();
        if(request()->isAjax()){
            $param = input('post.');
            $flag = $place->editPlace($param);
            return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
        }
        $id = input('param.id');
        $info = $place->getOnePlace($id);
        $this->assign('info',$info);
        return $this->fetch();
    }

    /**
     * [del_place 删除分销机构]
     */
    public function del_place(){
        $id = input('param.id');
        $place = new PlaceModel();
        $flag = $place->delPlace($id);
        return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
    }


    /**
     * [place_state 分销机构状态]
     */
    public function place_state(){
        $id=input('param.id');
        $status = Db::name('place')->where(array('id'=>$id))->value('status');//判断当前状态情况
        if($status==1){
            $flag = Db::name('place')->where(array('id'=>$id))->setField(['status'=>0]);
            return json(['code' => 1, 'data' => $flag['data'], 'msg' => '已禁止']);
        } else {
            $flag = Db::name('place')->where(array('id'=>$id))->setField(['status'=>1]);
            return json(['code' => 0, 'data' => $flag['data'], 'msg' => '已开启']);
        }
    }

    //生成二维码
    public function userImg(){
        $id = input('id');
        $place = Db::name('place')->where('id',$id)->find();
        if ($place['promo_code'] == ""){
            $qrcode = new QRcode();
            $request = Request::instance();
            $domain = $request->domain();
            $value = $domain.'/h5?pid='.$id.'&uid=0';         //二维码内容
            $errorCorrectionLevel = 'L';  //容错级别
            $matrixPointSize = 8;      //生成图片大小
            //生成二维码图片
            // 判断是否有这个文件夹  没有的话就创建一个
            if (!is_dir("qrcode")) {
                // 创建文件加
                mkdir("qrcode");
            }
            //设置二维码文件名
            $filename = 'qrcode/' . time() . rand(10000, 9999999) . '.png';
            //生成二维码
            $qrcode::png($value, $filename, $errorCorrectionLevel, $matrixPointSize, 2);
            Db::name('place')->where('id',$id)->update(['promo_code'=>$filename]);
        }else{
            $filename = $place['promo_code'];
        }
        return $filename;
    }


    /*****************************分销员********************************/
    public function user(){
        $key = input('key');
        $place_id = input('place_id');
        $map = [];
        if($key&&$key!==""){
            $map['name'] = ['like',"%" . $key . "%"];
        }
        if ($place_id != ""){
            $map['place_id'] = $place_id;
        }
        $Nowpage = input('get.page') ? input('get.page'):1;
        $limits = config('list_rows');// 获取总条数
        $count = Db::name('place_user')->where($map)->count();//计算总页面
        $allpage = intval(ceil($count / $limits));
        $place_user = new PlaceuserModel();
        $lists = $place_user->getPlaceUserByWhere($map, $Nowpage, $limits);
        $this->assign('Nowpage', $Nowpage); //当前页
        $this->assign('allpage', $allpage); //总页数
        $this->assign('count', $count);
        $this->assign('val', $key);
        $this->assign('place_id', $place_id);
        $request = Request::instance();
        $domain = $request->domain();
        $this->assign('http',$domain);
        $place = Db::name('place')->where('status',1)->select();
        $this->assign('place',$place);
        if(input('get.page')){
            return json($lists);
        }
        return $this->fetch();
    }

    /**
     * [add_place_user 添加分销人员]
     */
    public function add_place_user(){
        if(request()->isAjax()){
            $param = input('post.');
            $place_user = new PlaceuserModel();
            $flag = $place_user->insertPlaceUser($param);
            return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
        }
        $place = Db::name('place')->where('status',1)->select();
        $this->assign('place',$place);
        return $this->fetch();
    }

    /**
     * [edit_place_user 编辑分销人员]
     */
    public function edit_place_user(){
        $place_user = new PlaceuserModel();
        if(request()->isAjax()){
            $param = input('post.');
            $flag = $place_user->editPlaceUser($param);
            return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
        }
        $id = input('param.id');
        $info = $place_user->getOnePlaceUser($id);
        $this->assign('info',$info);
        $place = Db::name('place')->where('status',1)->select();
        $this->assign('place',$place);
        return $this->fetch();
    }

    /**
     * [del_place_user 删除分销人员]
     */
    public function del_place_user(){
        $id = input('param.id');
        $place_user = new PlaceuserModel();
        $flag = $place_user->delPlaceUser($id);
        return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
    }

    /**
     * [place_state 分销机构状态]
     */
    public function place_user_state(){
        $id=input('param.id');
        $status = Db::name('place_user')->where(array('id'=>$id))->value('status');//判断当前状态情况
        if($status==1){
            $flag = Db::name('place_user')->where(array('id'=>$id))->setField(['status'=>0]);
            return json(['code' => 1, 'data' => $flag['data'], 'msg' => '已禁止']);
        } else {
            $flag = Db::name('place_user')->where(array('id'=>$id))->setField(['status'=>1]);
            return json(['code' => 0, 'data' => $flag['data'], 'msg' => '已开启']);
        }
    }

    //生成二维码分销人员
    public function qrcode_place_user(){
        $id = input('id');
        $place = Db::name('place_user')->where('id',$id)->find();
        if ($place['promo_code'] == ""){
            $qrcode = new QRcode();
            $request = Request::instance();
            $domain = $request->domain();
            $value = $domain.'/h5?pid='.$place['place_id'].'&uid='.$id;         //二维码内容
            $errorCorrectionLevel = 'L';  //容错级别
            $matrixPointSize = 8;      //生成图片大小
            //生成二维码图片
            // 判断是否有这个文件夹  没有的话就创建一个
            if (!is_dir("qrcode")) {
                // 创建文件加
                mkdir("qrcode");
            }
            //设置二维码文件名
            $filename = 'qrcode/' . time() . rand(10000, 9999999) . '.png';
            //生成二维码
            $qrcode::png($value, $filename, $errorCorrectionLevel, $matrixPointSize, 2);
            Db::name('place_user')->where('id',$id)->update(['promo_code'=>$filename]);
        }else{
            $filename = $place['promo_code'];
        }
        return $filename;
    }

    /*******************************客服********************************/

    //客服列表
    public function kefu(){
        $key = input('key');
        $place_id = input('id');
        $map = [];
        if($key&&$key!==""){
            $map['think_kefu.name'] = ['like',"%" . $key . "%"];
        }
        if ($place_id != ""){
            $map['place_id'] = $place_id;
        }
        $Nowpage = input('get.page') ? input('get.page'):1;
        $limits = config('list_rows');// 获取总条数
        $count = Db::name('kefu')->where($map)->count();//计算总页面
        $allpage = intval(ceil($count / $limits));
        $kefu = new KefuModel();
        $lists = $kefu->getKefuByWhere($map, $Nowpage, $limits);
        $this->assign('Nowpage', $Nowpage); //当前页
        $this->assign('allpage', $allpage); //总页数
        $this->assign('count', $count);
        $this->assign('val', $key);
        $this->assign('id', $place_id);
        if(input('get.page')){
            return json($lists);
        }
        return $this->fetch();
    }

    /**
     * [add_kefu 添加客服]
     */
    public function add_kefu(){
        $place_id = input('place_id');
        $this->assign('place_id',$place_id);
        if(request()->isAjax()){
            $param = input('post.');
            $kefu = new KefuModel();
            $param['create_time'] = time();
            $flag = $kefu->insertKefu($param);
            return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
        }
        return $this->fetch();
    }

    /**
     * [edit_kefu 编辑客服]
     */
    public function edit_kefu(){
        $kefu = new KefuModel();
        if(request()->isAjax()){
            $param = input('post.');
            $flag = $kefu->editKefu($param);
            return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
        }
        $id = input('param.id');
        $info = $kefu->getOneKefu($id);
        $this->assign('info',$info);
        return $this->fetch();
    }

    /**
     * [del_kefu 删除客服]
     */
    public function del_kefu(){
        $id = input('param.id');
        $kefu = new KefuModel();
        $flag = $kefu->delKefu($id);
        return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
    }

    /**
     * [kefu_state 客服状态]
     */
    public function kefu_state(){
        $id=input('param.id');
        $status = Db::name('kefu')->where(array('id'=>$id))->value('status');//判断当前状态情况
        if($status==1){
            $flag = Db::name('kefu')->where(array('id'=>$id))->setField(['status'=>0]);
            return json(['code' => 1, 'data' => $flag['data'], 'msg' => '已禁止']);
        } else {
            $flag = Db::name('kefu')->where(array('id'=>$id))->setField(['status'=>1]);
            return json(['code' => 0, 'data' => $flag['data'], 'msg' => '已开启']);
        }
    }



}