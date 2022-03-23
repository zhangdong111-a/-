<?php


namespace app\admin\model;


use think\Model;

class TblistModel extends Model
{
    protected $name = 'tb_list';

    // 开启自动写入时间戳
//    protected $autoWriteTimestamp = true;

    /**
     * 根据搜索条件获取退补订单
     */
    public function getTbListByWhere($map, $Nowpage, $limits){
        return $this->where($map)->page($Nowpage, $limits)->order('id desc')->select();
    }

    /**
     * [inserttb 添加退补]
     */
    public function insertTb($param){
        try{
            $result = $this->allowField(true)->save($param);
            if(false === $result){
                return ['code' => -1, 'data' => '', 'msg' => $this->getError()];
            }else{
                return ['code' => 1, 'data' => '', 'msg' => '退款成功'];
            }
        }catch( PDOException $e){
            return ['code' => -2, 'data' => '', 'msg' => $e->getMessage()];
        }
    }


}