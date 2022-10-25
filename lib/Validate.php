<?php
/*
	Copyright (c) 2021-2050 FatPlug, All rights reserved.
	This file is part of the FatPlug Framework (http://fatplug.cn).
	This is not free software.
	you can redistribute it and/or modify it under the
	terms of the License after purchased commercial license. 
	mail: sunkangchina@163.com
*/
namespace lib;

/**
 * https://github.com/vlucas/valitron

$v = new \lib\Validate(array('name' => 'Chester Tester'));
$v->rule('required', 'name');
if($v->validate()) {
    echo "Yay! We're all good!";
} else {
    // Errors
    print_r($v->errors());
}

 */

use Valitron\Validator as V;

//V::langDir(__DIR__.'/validator_lang'); // always set langDir before lang.
//此处固定使用中文，系统暂时没必要支持所有语言
V::lang('zh-cn');

class Validate extends \Valitron\Validator
{

    public function errors($field = null)
    {
        if ($field !== null) {
            return isset($this->_errors[$field]) ? $this->_errors[$field] : false;
        }

        return $this->_errors;
    }
}
