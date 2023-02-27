<?php
/*
    Copyright (c) 2021-2031, All rights reserved.
    This is NOT a freeware
    LICENSE: https://github.com/thefunpower/core/blob/main/LICENSE.md 
    Connect Email: sunkangchina@163.com 
    Code Vesion: v1.0.x
*/
/**
 * 写日志
 * log_info(string)
 * 依赖 https://github.com/Seldaek/monolog
*/
/**
* 写日志
*/
function log_info($str,$name='app',$type='warning'){  
    if(is_array($str)){
        $str = json_encode($str,JSON_UNESCAPED_UNICODE);
    }
    log_init($type,$name)->$type($str,['ip'=>get_ip()]);
}
/**
* 记录错误日志
*/
function log_error($str,$name='app'){  
    log_info($str,$name,'error');
}

/**
* 初始化
*/
function log_init($WARNING='WARNING',$channel_name='app')
{  
    static $_log;
    if($_log){
        return $_log;
    }
    $log_from = "web";
    if (is_cli()) {
        $log_from = 'cli';
    }
    $log_path = PATH . 'data/log/' . $log_from; 
    if(!is_dir($log_path)) mkdir($log_path,0777,true);
    $log_file = $log_path.'/'.$channel_name.'.log'; 
    $dateFormat = "Y-m-d H:i:s"; 
    $output = "%datetime% > %level_name% > %message% %context% %extra%\n";
    do_action("monolog.output",$output);
    $_log = new Monolog\Logger($channel_name);
    $stream = new Monolog\Handler\StreamHandler($log_file); 
    $formatter = new Monolog\Formatter\LineFormatter($output, $dateFormat); 
    $stream->setFormatter($formatter);
    do_action("monolog",$_log);
    do_action("monolog.stream",$stream);
    $_log->pushHandler($stream);  
    return $_log;
}
