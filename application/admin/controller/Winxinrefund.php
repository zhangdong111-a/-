<?php

namespace app\admin\controller;

class Winxinrefund
{
    protected $SSLCERT_PATH = VENDOR_PATH.'cert/apiclient_cert.pem';//证书路径
    protected $SSLKEY_PATH = VENDOR_PATH.'cert/apiclient_key.pem';//证书路径
    protected $opUserId = '1619881281';//商户号
    protected $KEY = 'djhzhclfo0vqfb1v4ichkgthvwj40pog';


    function __construct($openid, $outTradeNo, $totalFee, $outRefundNo, $refundFee){
        //初始化退款类需要的变量
        $this->openid = $openid;
        $this->outTradeNo = $outTradeNo;
        $this->totalFee = $totalFee * 100;
        $this->outRefundNo = $outRefundNo;
        $this->refundFee = $refundFee * 100;
    }


    public function refund(){
        //对外暴露的退款接口
        $result = $this->wxrefundapi();
        return $result;
    }

    private function wxrefundapi(){
        //通过微信api进行退款流程
        $parma = array(
            'appid' => 'wxd786e2737e6edc01',
            'mch_id' => '1431311702',
            'sub_mch_id' => '1619881281',
            'nonce_str' => $this->createNoncestr(),
            'out_refund_no' => $this->outRefundNo,
            'out_trade_no' => $this->outTradeNo,
            'total_fee' => $this->totalFee,
            'refund_fee' => $this->refundFee
        );
        $parma['sign'] = $this->getSign($parma);
        $xmldata = '<xml>
                    <appid>'.$parma['appid'].'</appid>
                    <mch_id>'.$parma['mch_id'].'</mch_id>
                    <sub_mch_id>'.$parma['sub_mch_id'].'</sub_mch_id>
                    <nonce_str>'.$parma['nonce_str'].'</nonce_str>
                    <out_refund_no>'.$parma['out_refund_no'].'</out_refund_no>
                    <out_trade_no>'.$parma['out_trade_no'].'</out_trade_no>
                    <refund_fee>'.$parma['refund_fee'].'</refund_fee>
                    <total_fee>'.$parma['total_fee'].'</total_fee>
                    <sign>'.$parma['sign'].'</sign>
                    </xml>';
        $xmlresult = $this->postXmlSSLCurl($xmldata, 'https://api.mch.weixin.qq.com/secapi/pay/refund');
        $result = $this->xmlToArray($xmlresult);
        return $result;

    }

    private function sign($data){
        $stringA = '';
        foreach ($data as $key=>$value){
            if(!$value) continue;
            if($stringA) $stringA .= '&'.$key."=".$value;
            else $stringA = $key."=".$value;
        }
        $wx_key = 'djhzhclfo0vqfb1v4ichkgthvwj40pog';//服务商key
        $stringSignTemp = $stringA.'&key='.$wx_key;//申请支付后有给予一个商户账号和密码，登陆后自己设置key
        //return $stringSignTemp;
        return strtoupper(md5($stringSignTemp));
    }

    protected function getSign($Obj)
    {
        foreach ($Obj as $k => $v) {
            $Parameters[$k] = $v;
        }
        //签名步骤一：按字典序排序参数
        ksort($Parameters);
        $String = $this->formatBizQueryParaMap($Parameters, false);
        //签名步骤二：在string后加入KEY
        $String = $String . "&key=" . $this->KEY;
        //签名步骤三：MD5加密
        $String = md5($String);
        //签名步骤四：所有字符转为大写
        $result_ = strtoupper($String);
        return $result_;
    }

    /*
     *排序并格式化参数方法，签名时需要使用
     */
    protected function formatBizQueryParaMap($paraMap, $urlencode)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if ($urlencode) {
                $v = urlencode($v);
            }
            $buff .= $k . "=" . $v . "&";
        }
        $reqPar = "";
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }
        return $reqPar;
    }

    /*
     * 生成随机字符串方法
     */
    protected function createNoncestr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }



    protected static function xmlToArray($xml)
    {
        // dump($xml);die;
        $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $array_data;
    }

//需要使用证书的请求
    private function postXmlSSLCurl($xml, $url, $second = 30)
    {
        $ch = curl_init();

        //超时时间
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        //这里设置代理，如果有的话
        //curl_setopt($ch,CURLOPT_PROXY, '8.8.8.8');
        //curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //设置证书
        //使用证书：cert 与 key 分别属于两个.pem文件
        //默认格式为PEM，可以注释
        curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
        curl_setopt($ch, CURLOPT_SSLCERT, $this->SSLCERT_PATH);
        //默认格式为PEM，可以注释
        curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
        curl_setopt($ch, CURLOPT_SSLKEY, $this->SSLKEY_PATH);
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        $data = curl_exec($ch);
        print_r($data);
        //返回结果
        if ($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            echo "curl出错，错误码:$error" . "";
            curl_close($ch);
            return false;
        }
    }
}