<?php


namespace app\admin\model;


use think\Model;

class PlaceuserModel extends Model
{
    protected $name = 'place_user';

    // 开启自动写入时间戳字段

    protected $autoWriteTimestamp = true;

    /**
     * 根据搜索条件获取分销人员列表信息
     */
    public function getPlaceUserByWhere($map, $Nowpage, $limits){

        return $this->field('think_place_user.*,title')->join('think_place', 'think_place_user.place_id = think_place.id')->where($map)->page($Nowpage, $limits)->order('id desc')->select();

    }

    /**
     * [insertPlaceUser 添加分销人员]
     */
    public function insertPlaceUser($param){
        try{
            $result = $this->save($param);
            if(false === $result){
                return ['code' => -1, 'data' => '', 'msg' => $this->getError()];
            }else{
                return ['code' => 1, 'data' => '', 'msg' => '分销人员添加成功'];
            }
        }catch( PDOException $e){
            return ['code' => -2, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

    /**
     * [editPlaceUser 编辑分销人员]
     */
    public function editPlaceUser($param){
        try{
            $result = $this->save($param, ['id' => $param['id']]);
            if(false === $result){
                return ['code' => 0, 'data' => '', 'msg' => $this->getError()];
            }else{
                return ['code' => 1, 'data' => '', 'msg' => '分销人员编辑成功'];
            }
        }catch( PDOException $e){
            return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
        }
    }


    /**
     * [getOnePlaceUser 根据分销人员id获取一条信息]
     */
    public function getOnePlaceUser($id){
        return $this->where('id', $id)->find();
    }


    /**
     * [delPlaceUser 删除分销人员]
     * @return [type] [description]
     */

    public function delPlaceUser($id){
        try{
            $this->where('id', $id)->delete();
            return ['code' => 1, 'data' => '', 'msg' => '分销人员删除成功'];
        }catch( PDOException $e){
            return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

}