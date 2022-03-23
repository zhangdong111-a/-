<?php

namespace app\admin\model;

use think\Db;
use think\Model;

class PlaceModel extends Model
{
    protected $name = 'place';

    // 开启自动写入时间戳
    protected $autoWriteTimestamp = true;

    /**
     * 根据搜索条件获取分销机构列表
     */
    public function getPlaceByWhere($map, $Nowpage, $limits){
        return $this->where($map)->page($Nowpage, $limits)->order('id desc')->select();
    }

    /**
     * [insertPlace 添加分销机构]
     */
    public function insertPlace($param){
        try{
            $result = $this->validate('PlaceValidate')->save($param);
            if(false === $result){
                return ['code' => -1, 'data' => '', 'msg' => $this->getError()];
            }else{
                return ['code' => 1, 'data' => '', 'msg' => '分销机构添加成功'];
            }
        }catch( PDOException $e){
            return ['code' => -2, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

    /**
     * [editPlace 编辑分销机构]
     */
    public function editPlace($param){
        try{
            $result = $this->validate('PlaceValidate')->save($param, ['id' => $param['id']]);
            if(false === $result){
                return ['code' => 0, 'data' => '', 'msg' => $this->getError()];
            }else{
                return ['code' => 1, 'data' => '', 'msg' => '分销机构编辑成功'];
            }
        }catch( PDOException $e){
            return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
        }
    }


    /**
     * [getOnePlace 根据分销机构id获取一条信息]
     */
    public function getOnePlace($id){
        return $this->where('id', $id)->find();
    }


    /**
     * [delPlace 删除分销机构]
     * @return [type] [description]
     */

    public function delPlace($id){
        try{
            $this->where('id', $id)->delete();
            Db::name('place_user')->where('place_id',$id)->delete();
            return ['code' => 1, 'data' => '', 'msg' => '分销机构删除成功'];
        }catch( PDOException $e){
            return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

}