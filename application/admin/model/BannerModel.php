<?php


namespace app\admin\model;


use think\Db;
use think\Model;

class BannerModel extends Model
{
    protected $name = 'banner';

    // 开启自动写入时间戳
    protected $autoWriteTimestamp = true;

    /**
     * 根据搜索条件获取轮播图列表
     */
    public function getBannerByWhere($map, $Nowpage, $limits){
        return $this->where($map)->page($Nowpage, $limits)->order('id desc')->select();
    }

    /**
     * [insertbanner 添加轮播图]
     */
    public function insertBanner($param){
        try{
            $result = $this->allowField(true)->save($param);
            if(false === $result){
                return ['code' => -1, 'data' => '', 'msg' => $this->getError()];
            }else{
                return ['code' => 1, 'data' => '', 'msg' => '轮播图添加成功'];
            }
        }catch( PDOException $e){
            return ['code' => -2, 'data' => '', 'msg' => $e->getMessage()];
        }
    }
    /**
     * [editBanner 编辑轮播图]
     */
    public function editBanner($param){
        try{
            $result = $this->allowField(true)->save($param, ['id' => $param['id']]);
            if(false === $result){
                return ['code' => 0, 'data' => '', 'msg' => $this->getError()];
            }else{
                return ['code' => 1, 'data' => '', 'msg' => '轮播图编辑成功'];
            }
        }catch( PDOException $e){
            return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
        }
    }


    /**
     * [getOneBanner 根据轮播图id获取一条信息]
     */
    public function getOneBanner($id){
        return $this->where('id', $id)->find();
    }


    /**
     * [delBanner 删除轮播图]
     * @return [type] [description]
     */

    public function delBanner($id){
        try{
            $this->where('id', $id)->delete();
            return ['code' => 1, 'data' => '', 'msg' => '轮播图删除成功'];
        }catch( PDOException $e){
            return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

}