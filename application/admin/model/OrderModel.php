<?php


namespace app\admin\model;


use think\Model;

class OrderModel extends Model
{
    protected $name = 'order';

    /**
     * 根据搜索条件获取订单列表
     */
    public function getOrderByWhere($map, $Nowpage, $limits){
        return $this->where($map)->page($Nowpage, $limits)->order('id desc')->select();
    }

    /**
     * [getOneOrder 根据订单id获取一条信息]
     */
    public function getOneOrder($id){
        return $this->where('id', $id)->find();
    }

    /**
     * [insertOrder 添加订单]
     */
    public function insertOrder($param){
        try{
            $result = $this->validate('OrderValidate')->allowField(true)->save($param);
            if(false === $result){
                return ['code' => -1, 'data' => '', 'msg' => $this->getError()];
            }else{
                return ['code' => 1, 'data' => '', 'msg' => '订单添加成功'];
            }
        }catch( PDOException $e){
            return ['code' => -2, 'data' => '', 'msg' => $e->getMessage()];
        }
    }


}