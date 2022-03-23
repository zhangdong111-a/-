<?php

namespace app\api\controller;

use think\Controller;
use think\Db;

/**
 * swagger: 首页
 */
class Index extends Controller
{
    public function banner()
    {
        $banner = Db::name('banner')->where('status', 1)->select();
        if ($banner) {
            $data['code'] = 200;
            $data['msg'] = '请求成功';
            $data['data'] = $banner;
            return json($data);
        } else {
            $data['code'] = 500;
            $data['msg'] = '请求失败';
            return json($data);
        }
    }

    public function one_curriculum()
    {
        //机构id
        $place_id = input('place_id');
        if ($place_id == "") {
            $data['code'] = 500;
            $data['msg'] = '机构id不能为空';
            return json($data);
        }
        //分佣
        $fenyong = Db::name('fenyong')->where('jg_id', $place_id)->select();
        if ($fenyong) {
            $kc_id = implode(',', array_column($fenyong, 'kc_id'));
            $curriculum = Db::name('curriculum')->where('lx = 1 and id in (' . $kc_id . ')')->order('id desc')->limit(2)->select();
        } else {
            $curriculum = array();
        }
        if ($curriculum) {
            $data['code'] = 200;
            $data['msg'] = '请求成功';
            $data['data'] = $curriculum;
            return json($data);
        } else {
            $data['code'] = 500;
            $data['msg'] = '请求失败';
            return json($data);
        }
    }


    public function two_curriculum()
    {
        //机构id
        $place_id = input('place_id');
        if ($place_id == "") {
            $data['code'] = 500;
            $data['msg'] = '机构id不能为空';
            return json($data);
        }
        //分佣
        $fenyong = Db::name('fenyong')->where('jg_id', $place_id)->select();
        if ($fenyong) {
            $kc_id = implode(',', array_column($fenyong, 'kc_id'));
            $curriculum = Db::name('curriculum')->where('lx = 2 and id in (' . $kc_id . ')')->order('id desc')->limit(2)->select();
        } else {
            $curriculum = array();
        }
        if ($curriculum) {
            $data['code'] = 200;
            $data['msg'] = '请求成功';
            $data['data'] = $curriculum;
            return json($data);
        } else {
            $data['code'] = 500;
            $data['msg'] = '请求失败';
            return json($data);
        }
    }

    public function all_one_curriculum()
    {
        //机构id
        $place_id = input('place_id');
        if ($place_id == "") {
            $data['code'] = 500;
            $data['msg'] = '机构id不能为空';
            return json($data);
        }
        //分佣
        $fenyong = Db::name('fenyong')->where('jg_id', $place_id)->select();
        if (!$fenyong) {
            $curriculum = array();
        } else {
            $kc_id = implode(',', array_column($fenyong, 'kc_id'));
            $curriculum = Db::name('curriculum')->where('lx = 1 and id in (' . $kc_id . ')')->select();
        }
        if ($curriculum) {
            $data['code'] = 200;
            $data['msg'] = '请求成功';
            $data['data'] = $curriculum;
            return json($data);
        } else {
            $data['code'] = 500;
            $data['msg'] = '请求失败';
            return json($data);
        }
    }


    public function all_two_curriculum()
    {
        //机构id
        $place_id = input('place_id');
        if ($place_id == "") {
            $data['code'] = 500;
            $data['msg'] = '机构id不能为空';
            return json($data);
        }
        //分佣
        $fenyong = Db::name('fenyong')->where('jg_id', $place_id)->select();
        if (!$fenyong) {
            $curriculum = array();
        } else {
            $kc_id = implode(',', array_column($fenyong, 'kc_id'));
            $curriculum = Db::name('curriculum')->where('lx = 2 and id in (' . $kc_id . ')')->select();
        }
        if ($curriculum) {
            $data['code'] = 200;
            $data['msg'] = '请求成功';
            $data['data'] = $curriculum;
            return json($data);
        } else {
            $data['code'] = 500;
            $data['msg'] = '请求失败';
            return json($data);
        }
    }
}