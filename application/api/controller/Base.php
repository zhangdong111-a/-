<?php

namespace app\api\controller;
use think\Controller;

class Base extends Controller
{
    public function _initialize()
    {
        if(!session('openid') || !session('uid')){
            $this->redirect('Userinfo/index');
        }
    }
}