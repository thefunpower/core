<?php
/*
    Copyright (c) 2021-2031, All rights reserved.  
    Connect Email: sunkangchina@163.com 
*/
/**
 * 写日志
 * log_info(string)
 * 依赖 https://github.com/Seldaek/monolog
*/
/**
* 写日志
*/
function log_info($str,$name='app',$type='info'){  
    if(is_array($str)){
        $str = json_encode($str,JSON_UNESCAPED_UNICODE);
    }
    $par = ['IP'=>get_ip(),'REQUEST'=>$_SERVER['REQUEST_URI']?:$_SERVER['SCRIPT_NAME']]; 
    $ts = debug_backtrace();
    $last = $ts[count($ts)-1]; 
    if(is_cli()){ 
        unset($par['IP']); 
    } 
    $par['LINE'] = $last['line'];
    log_init($name)->$type($str,$par);
}
/**
* 记录错误日志
*/
function log_error($str){  
    log_info($str,'error','error');
}

/**
* 初始化
*/
function log_init($channel_name='app')
{  
    static $_log;
    if($_log[$channel_name]){
        return $_log[$channel_name];
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
    $log = new Monolog\Logger($channel_name);
    $stream = new Monolog\Handler\StreamHandler($log_file); 
    $formatter = new Monolog\Formatter\LineFormatter($output, $dateFormat); 
    $stream->setFormatter($formatter);
    do_action("monolog",$log);
    do_action("monolog.stream",$stream);
    $log->pushHandler($stream);  
    $_log[$channel_name] = $log;
    return $log;
}
