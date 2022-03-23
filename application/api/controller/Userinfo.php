<?php

namespace app\api\controller;

use think\Controller;
use think\Db;

class Userinfo extends Controller
{
    public function getUserInfo()
    {
        header("Content-type: text/html; charset=utf-8");
        $appid = "wxd786e2737e6edc01";  //填写你公众号的appid
        $secret = "33c553ec7d341f4785e804341add4cd0";  //填写你公众号的secret
        $code = input('code');
        $oauth2Url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appid&secret=$secret&code=$code&grant_type=authorization_code";
        $oauth2 = $this->getJson($oauth2Url);
        $access_token = $oauth2["access_token"];
        $openid = $oauth2['openid'];
        $get_user_info_url = "https://api.weixin.qq.com/sns/userinfo?access_token=$access_token&openid=$openid&lang=zh_CN";
        $userinfo = $this->getJson($get_user_info_url);
        if ($userinfo) {
            $rs = Db::name('student')->where('openid', $userinfo['openid'])->find();
            if ($rs) {
                $arr = array("openid" => $rs['openid'], "uid" => $rs['id']);
                $data['code'] = "200";
                $data['msg'] = "请求成功";
                $data['data'] = $arr;
                return json($data);
            } else {
                $arr = array('nickname' => $userinfo['nickname'], 'openid' => $userinfo['openid'], 'img' => $userinfo['headimgurl']);
                $add = Db::name('student')->insertGetId($arr);
                if ($add) {
                    $arrs = array("openid" => $userinfo['openid'], "uid" => $add);
                    $data['code'] = "200";
                    $data['msg'] = "请求成功";
                    $data['data'] = $arrs;
                    return json($data);
                }
            }
        }
    }

    public function getJson($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        return json_decode($output, true);
    }
}