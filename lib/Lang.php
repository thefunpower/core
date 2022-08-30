<?php

/*
	Copyright (c) 2021-2050 FatPlug, All rights reserved.
	This file is part of the FatPlug Framework (http://fatplug.cn).
	This is not free software.
	you can redistribute it and/or modify it under the
	terms of the License after purchased commercial license. 
	mail: sunkangchina@163.com
	web: http://fatplug.cn
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
