<?php


namespace app\admin\model;


use think\Db;
use think\Model;

class ShenqingModel extends Model
{
    protected $name = 'Shenqing';

//    // 开启自动写入时间戳
//    protected $autoWriteTimestamp = true;

    /**
     * 根据搜索条件获取分销机构列表
     */
    public function getShenqingByWhere($map, $Nowpage, $limits){
        return $this->where($map)->page($Nowpage, $limits)->order('id desc')->select();
    }

//    /**
//     * [getOnePlace 根据分销机构id获取一条信息]
//     */
//    public function getOnePlace($id){
//        return $this->where('id', $id)->find();
//    }

}