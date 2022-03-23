<?php


namespace app\admin\validate;


use think\Validate;

class OrderValidate extends Validate
{
    protected $rule = [
        ['orderId', 'unique:order', '订单号已经存在']
    ];

}