<?php


namespace app\admin\model;


use think\Model;

class KefuModel extends Model
{

    protected $name = 'kefu';

    // 开启自动写入时间戳字段

//    protected $autoWriteTimestamp = true;

    /**
     * 根据搜索条件客服列表信息
     */
    public function getKefuByWhere($map, $Nowpage, $limits){

        return $this->field('think_kefu.*')->join('think_place', 'think_kefu.place_id = think_place.id')->where($map)->page($Nowpage, $limits)->order('id desc')->select();

    }

    /**
     * [insertKefu 添加客服]
     */
    public function insertKefu($param){
        try{
            $result = $this->allowField(true)->save($param);
            if(false === $result){
                return ['code' => -1, 'data' => '', 'msg' => $this->getError()];
            }else{
                return ['code' => 1, 'data' => '', 'msg' => '添加成功'];
            }
        }catch( PDOException $e){
            return ['code' => -2, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

    /**
     * [editKefu 编辑客服]
     */
    public function editKefu($param){
        try{
            $result = $this->allowField(true)->save($param, ['id' => $param['id']]);
            if(false === $result){
                return ['code' => 0, 'data' => '', 'msg' => $this->getError()];
            }else{
                return ['code' => 1, 'data' => '', 'msg' => '编辑成功'];
            }
        }catch( PDOException $e){
            return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
        }
    }


    /**
     * [getOneKefu 根据客服id获取一条信息]
     */
    public function getOneKefu($id){
        return $this->where('id', $id)->find();
    }


    /**
     * [delKefu 删除客服]
     * @return [type] [description]
     */

    public function delKefu($id){
        try{
            $this->where('id', $id)->delete();
            return ['code' => 1, 'data' => '', 'msg' => '删除成功'];
        }catch( PDOException $e){
            return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
        }
    }
}