<?php 
/*
    Copyright (c) 2021-2031, All rights reserved.
    This is NOT a freeware
    LICENSE: https://github.com/thefunpower/core/blob/main/LICENSE.md 
    Connect Email: sunkangchina@163.com 
    Code Vesion: v1.0.x
*/

/**
 * 添加阿里云市场调用方法
 */
function get_aliyun_market($host,$path,$querys='',$method = "GET",$json_header=true,$append_headers = []){ 
    $cache_id = 'aliyun_market:'.md5($host.$path.$querys.$method);
    $body = cache($cache_id);
    if($body){
        return [
            'code'=>$code,
            'body'=>$body, 
            'cache_id'=>$cache_id,
        ];
    }
    $appcode = get_config("aliyun_market_AppCode");//开通服务后 买家中心-查看AppCode
    $headers = array();
    array_push($headers, "Authorization:APPCODE " . $appcode); 
    if($append_headers){
        foreach($append_headers as $v){
            array_push($headers,$v); 
        }
    }
    if($json_header){
        array_push($headers, "Content-Type" . ":" . "application/json; charset=UTF-8");    
    }    
    $bodys = "";
    $url = $host . $path . "?" . $querys; 
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_TIMEOUT, 60);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_FAILONERROR, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HEADER, true);
    if (1 == strpos("$" . $host, "https://")) {
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    }
    $out_put = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    list($header, $body) = explode("\r\n\r\n", $out_put, 2);
    if ($httpCode == 200) {
        //正常请求计费(其他均不计费)
        $code = 0;
    } else {
        $code = 250;
        if ($httpCode == 400 && strpos($header, "Invalid Param Location") !== false) {
            $error = '参数错误';
        } elseif ($httpCode == 400 && strpos($header, "Invalid AppCode") !== false) {
            $error = "AppCode错误";
        } elseif ($httpCode == 400 && strpos($header, "Invalid Url") !== false) {
            $error = "请求的 Method、Path 或者环境错误";
        } elseif ($httpCode == 403 && strpos($header, "Unauthorized") !== false) {
            $error = "服务未被授权（或URL和Path不正确）";
        } elseif ($httpCode == 403 && strpos($header, "Quota Exhausted") !== false) {
            $error = "套餐包次数用完";
        } elseif ($httpCode == 403 && strpos($header, "Api Market Subscription quota exhausted") !== false) {
            $error = "套餐包次数用完，请续购套餐";
        } elseif ($httpCode == 500) {
            $error = "API网关错误";
        } elseif ($httpCode == 0) {
            $error = "URL错误";
        } else {
            $error = "参数名错误 或 其他错误"; 
            $headers = explode("\r\n", $header);
            $headList = array();
            foreach ($headers as $head) {
                $value = explode(':', $head);
                $headList[$value[0]] = $value[1];
            }
            $error = $headList['x-ca-error-message'];
        }
    }
    if($body){
        $body = json_decode($body,true);
        cache($cache_id,$body,300);
    }
    return [
        'code'=>$code,
        'body'=>$body,
        'error'=>$error, 
        'cache_id'=>$cache_id,
    ];
 }

 