<?php


namespace app\admin\model;


use think\Db;
use think\Model;

class QuestionModel extends Model
{
    protected $name = 'question';

    // 开启自动写入时间戳
    protected $autoWriteTimestamp = true;

    /**
     * 根据搜索条件获取问答列表列表
     */
    public function getQuestionByWhere($map, $Nowpage, $limits){
        return $this->where($map)->page($Nowpage, $limits)->order('id desc')->select();
    }

    /**
     * [insertQuestion 添加问答]
     */
    public function insertQuestion($param){
        try{
            $result = $this->validate('QuestionValidate')->save($param);
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
     * [editQuestion 编辑问答]
     */
    public function editQuestion($param){
        try{
            $result = $this->validate('QuestionValidate')->save($param, ['id' => $param['id']]);
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
     * [getOneQuestion 根据问答id获取一条信息]
     */
    public function getOneQuestion($id){
        return $this->where('id', $id)->find();
    }


    /**
     * [delQuestion 删除问答]
     * @return [type] [description]
     */

    public function delQuestion($id){
        try{
            $this->where('id', $id)->delete();
            return ['code' => 1, 'data' => '', 'msg' => '删除成功'];
        }catch( PDOException $e){
            return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
        }
    }


}