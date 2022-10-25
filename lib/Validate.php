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
 */

use Valitron\Validator;
 
/**
* 
* $lang = 'zh-cn';
* lib\Validate::lang($lang);
* lib\Validate::langDir(__DIR__.'/validator_lang');
*/  
class Validate extends Validator
{

    public function errors($field = null)
    {
        if ($field !== null) {
            return isset($this->_errors[$field]) ? $this->_errors[$field] : false;
        }
        return $this->_errors;
    }
}
