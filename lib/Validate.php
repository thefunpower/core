<?php
/*
    Copyright (c) 2021-2031, All rights reserved.
    This is NOT a freeware, use is subject to license terms 
    Connect Email: sunkangchina@163.com 
    Code Vesion: v1.0
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
