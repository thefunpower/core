<?php 
/*
    Copyright (c) 2021-2031, All rights reserved.
    This is NOT a freeware
    LICENSE: https://github.com/thefunpower/core/blob/main/LICENSE.md 
    Connect Email: sunkangchina@163.com 
*/
/**
* 生成本地缩略图
*/
function image_resize($local_url,$w,$h = NULL,$bg = ''){
    if(substr($local_url,0,1) == '1'){
        $local_url = substr($local_url,1);
    }
    $new_dir = get_dir($local_url)."_resize/";
    if(!is_dir($new_dir)) mkdir($new_dir,0777,true);
    $unicode = "w_".$w."h_".$h;
    $new_file_url = $new_dir.get_name($local_url).$unicode.'.'.get_ext($local_url);
    $new_file = PATH.$new_file_url;
    if(file_exists($new_file)){
        return cdn().$new_file_url;
    } 
    $file = PATH.$local_url;
    if(!file_exists($file)){
        log_error("本地文件不存在，无法生成缩略图");
        return;
    }
    $driver = \Gregwar\Image\Image::open($file);     
    $driver->resize($w, $h,$bg); 
    $driver->save($new_file);
    return cdn().$new_file_url;
}
