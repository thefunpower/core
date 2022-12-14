<?php 
/*
    Copyright (c) 2021-2031, All rights reserved.
    This is NOT a freeware
    LICENSE: https://github.com/thefunpower/core/blob/main/LICENSE.md 
    Connect Email: sunkangchina@163.com 
    Code Vesion: v1.0.x
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
