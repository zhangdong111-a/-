<?php

namespace app\api\common;

use smsserver\lib\Ucpaas;
use think\Db;
use alisms\SmsDemo;

class SmsServer
{
    public function smsSend($tel, $code)
    {
        $response = SmsDemo::duanxin($tel, $code);
        $array = json_decode(json_encode($response), TRUE);
        if ($array['Message'] == 'OK') {
            return 1;
        }
    }
}