<?php


namespace app\admin\validate;


use think\Validate;

class PlaceValidate extends Validate
{
    protected $rule = [
        ['mobile', 'unique:place', '联系电话已经存在']
    ];
}