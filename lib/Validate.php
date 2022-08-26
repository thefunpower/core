<?php 
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
V::lang('zh-cn');

class Validate extends \Valitron\Validator{

    public function errors($field = null)
    {
        if ($field !== null) {
            return isset($this->_errors[$field]) ? $this->_errors[$field] : false;
        }

        return $this->_errors;
    }


}