<?php

/**
 * 
 * @license read license.txt
 * @author sun <sunkangchina@163.com>
 * @copyright (c) 2021 
 */

namespace lib;
/*  
* 多语言
*/

class Lang
{
    public static $lang_dir;
    public static $obj;

    public static function setLang($name = 'zh-cn')
    {
        cookie('lang', $name);
        self::$lang_dir = PATH . 'lang/' . $name . '/';
    }

    public static function trans($name, $val = [], $pre = 'app')
    {
        $arr = [];
        if (!self::$lang_dir) {
            self::setLang('zh-cn');
        }
        if (!self::$obj[$pre]) {
            $file = self::$lang_dir . $pre . '.php';
            if (file_exists($file)) {
                $arr = include $file;
            }
            self::$obj[$pre] = $arr;
        }
        $output =  self::$obj[$pre][$name];
        if ($val) {
            foreach ($val as $k => $v) {
                $output = str_replace("{" . $k . "}", $v, $output);
            }
        }
        return $output ?: $name;
    }
}
