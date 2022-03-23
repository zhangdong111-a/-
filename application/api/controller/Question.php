<?php

namespace app\api\controller;

use think\Db;

class Question
{
    //问题列表
    public function question()
    {
        $qestion = Db::name('question')->where('status', 1)->select();
        if ($qestion) {
            $data['code'] = 200;
            $data['msg'] = "请求成功";
            $data['data'] = $qestion;
            return json($data);
        } else {
            $data['code'] = 500;
            $data['msg'] = "请求失败";
            $data['data'] = '';
            return json($data);
        }
    }

    //根据问题id查询答案
    public function info()
    {
        $id = input('id');
        $qestion = Db::name('question')->where('id', $id)->find();
        if ($qestion) {
            $data['code'] = 200;
            $data['msg'] = "请求成功";
            $data['data'] = $qestion;
            return json($data);
        } else {
            $data['code'] = 500;
            $data['msg'] = "请求失败";
            $data['data'] = '';
            return json($data);
        }
    }
}