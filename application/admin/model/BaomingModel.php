<?php


namespace app\admin\model;


use think\Model;

class BaomingModel extends Model
{
    protected $name = 'baoming';

    // 开启自动写入时间戳
    protected $autoWriteTimestamp = true;

    /**
     * 根据搜索条件获取报名列表
     */
    public function getBaomingByWhere($map, $Nowpage, $limits){
        return $this->where($map)->page($Nowpage, $limits)->order('id desc')->select();
    }

//    /**
//     * [getOneBanner 根据轮播图id获取一条信息]
//     */
//    public function getOneBanner($id){
//        return $this->where('id', $id)->find();
//    }


//    /**
//     * [delBanner 删除轮播图]
//     * @return [type] [description]
//     */
//
//    public function delBanner($id){
//        try{
//            $this->where('id', $id)->delete();
//            return ['code' => 1, 'data' => '', 'msg' => '轮播图删除成功'];
//        }catch( PDOException $e){
//            return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
//        }
//    }

}