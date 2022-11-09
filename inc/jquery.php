<?php 
/*
    Copyright (c) 2021-2050 FatPlug, All rights reserved.
    This file is part of the FatPlug Framework (http://fatplug.cn).
    This is not free software.
    you can redistribute it and/or modify it under the
    terms of the License after purchased commercial license. 
    mail: sunkangchina@163.com
*/
/**
* 获取checkbox选中数组 
* jquery_checkbox_get_active('search','search')
* <input type="checkbox" name="search" :value="k">
*/
function jquery_checkbox_get_active($var){
    return "let ".$var." = [];
      \$('input[name=".$var."]').each(function(){
        if(\$(this).prop('checked')){
          ".$var.".push(\$(this).val());
        }
      });\n";
}
/**
* checkbox全选|全不选
*/
function jquery_checkbox_select_all($name){
    $var = "var_jquery_checkbox_select_all".mt_rand(0,99999);
    return " 
      let ".$var." = false;
      \$('input[name=".$name."]').each(function(){
        if(\$(this).prop('checked') === true){
            ".$var." = true;
        }
        if(".$var." === true){
            \$(this).prop('checked',false);
        }else{
            \$(this).prop('checked',true);
        } 
      });\n";
}
