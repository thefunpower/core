<?php 

/*
    Copyright (c) 2021-2031, All rights reserved.
    This is NOT a freeware, use is subject to license terms  
*/

/**
* 获取站点ID
*/
function get_site_id(){
    return get_sub_domain();
} 
/**
* 生成站点登录tokne
*/
function create_sub_site_login_token($site_id,$arr = []){
    $d['site_id'] = $site_id;
    $d['times'] = time();
    $d = $d+$arr; 
    return urlencode(aes_encode(json_encode($d)));  
}
/**
* 获取站点token
*/
function get_sub_site_login_token($site_id,$token,$less_second=5){
    $flag = false; 
    $arr = json_decode(aes_decode(urldecode($token)),true); 
    if($arr['times'] && $arr['site_id'] == $site_id ){ 
        if($arr['times'] > time()-$less_second){
            $flag = true;
        }
    }
    if($flag){
        return $arr;
    } 
}
/**
* 获取登录信息
*/
function get_sub_site_logined_info($site_id = '',$cookie_pre_name=''){ 
    $name = get_sub_site_cookie_name($site_id,$cookie_pre_name);
    $user = cookie($name);
    if($user){
        return $user;
    }else{
        return ;
    }
}
/**
* 子站点登录COOKIE
*/
function get_sub_site_cookie_name($site_id = '',$pre_name = ''){
    $pre_name = $pre_name?:'subsite_admin_';
    $site_id = $site_id?:get_site_id();  
    $name = $pre_name.$site_id;
    return $name;
}
/**
* 使用token登录
*/
function sub_site_login_with_token(){
    $sign = $_GET['sign'];
    $site_id = get_site_id();
    $get_name = $_GET['name']?:$site_id;
    if($sign){ 
      $arr = get_sub_site_login_token($site_id,$sign,$less_second=5); 
      if($arr['site_id'] == $site_id){
        cookie(get_sub_site_cookie_name($site_id),$get_name,time()+86400*365*10);
        return true;
      } 
    } 
}