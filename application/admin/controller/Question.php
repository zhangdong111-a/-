<?php


namespace app\admin\controller;


use app\admin\model\QuestionModel;
use think\Db;

class Question extends Base
{
    /**
     * [index 问答列表]
     */
    public function index(){
        $key = input('key');
        $map = [];
        if($key&&$key!==""){
            $map['question'] = ['like',"%" . $key . "%"];
        }
        $Nowpage = input('get.page') ? input('get.page'):1;
        $limits = config('list_rows');// 获取总条数
        $count = Db::name('question')->where($map)->count();//计算总页面
        $allpage = intval(ceil($count / $limits));
        $question = new QuestionModel();
        $lists = $question->getQuestionByWhere($map, $Nowpage, $limits);
        foreach ($lists as $k=>$v){
            $v['create_time'] = date('Y-m-d H:i:s',$v['create_time']);
            $lists[$k] =$v;
        }
        $this->assign('Nowpage', $Nowpage); //当前页
        $this->assign('allpage', $allpage); //总页数
        $this->assign('count', $count);
        $this->assign('val', $key);
        if(input('get.page')){
            return json($lists);
        }
        return $this->fetch();
    }

    /**
     * [add_question 添加问答]
     */
    public function add_question(){
        if(request()->isAjax()){
            $param = input('post.');
            $question = new QuestionModel();
            $flag = $question->insertQuestion($param);
            return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
        }
        return $this->fetch();
    }

    /**
     * [edit_question 编辑问答]
     */
    public function edit_question(){
        $question = new QuestionModel();
        if(request()->isAjax()){
            $param = input('post.');
            $flag = $question->editQuestion($param);
            return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
        }
        $id = input('param.id');
        $info = $question->getOneQuestion($id);
        $this->assign('info',$info);
        return $this->fetch();
    }

    /**
     * [del_question 删除问答]
     */
    public function del_question(){
        $id = input('param.id');
        $question = new QuestionModel();
        $flag = $question->delQuestion($id);
        return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
    }


    /**
     * [question_state 问答状态]
     */
    public function question_state(){
        $id=input('param.id');
        $status = Db::name('question')->where(array('id'=>$id))->value('status');//判断当前状态情况
        if($status==1){
            $flag = Db::name('question')->where(array('id'=>$id))->setField(['status'=>0]);
            return json(['code' => 1, 'data' => $flag['data'], 'msg' => '已禁止']);
        } else {
            $flag = Db::name('question')->where(array('id'=>$id))->setField(['status'=>1]);
            return json(['code' => 0, 'data' => $flag['data'], 'msg' => '已开启']);
        }
    }

}