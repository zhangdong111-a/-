<?php


namespace app\admin\validate;


use think\Validate;

class QuestionValidate extends Validate
{
    protected $rule = [
        ['question', 'unique:question', '问题已经存在']
    ];

}