<?php 
if (!defined('ADMIN_DIR_NAME')) {
	define("ADMIN_DIR_NAME", 'admin');
}
if (!defined('ADMIN_COOKIE_NAME')) {
	define("ADMIN_COOKIE_NAME", 'user_id');
}

global $_app, $db, $pdo, $log,  $config;

if (!$base_path) {
	$base_path = "/";
}
$write_dirs = [
	'data/log',
	'data/cache',
];
foreach ($write_dirs as $v) {
	$d = PATH . '/' . $v;
	if (!is_dir($d)) {
		mkdir($d, 0777, true);
	}
}
//启用session 
if (php_sapi_name() !== 'cli' && session_status() !== PHP_SESSION_ACTIVE) {
	session_start();
}
//请求
include __DIR__ . '/inc/request.php'; 
//配置时区
ini_set('date.timezone', $config['timezone'] ?: 'Asia/Shanghai');
//加载共用函数
include __DIR__ . '/helper.php'; 
//跨域
include __DIR__ . '/inc/cross.php';
//JWT接口验证
include __DIR__ . '/inc/jwt.php';
//缓存
include __DIR__ . '/inc/cache.php';
//日志
include __DIR__ . '/inc/log.php';  
$url = $_SERVER['REQUEST_URI'];
$plugin_name = substr($url, 1);  
/**
 只加载这个文件的时候，如果需要开启插件建议直接加载app.php
 但如果是SAAS版本的需要加载些文件，而不是加载app.php 
 SAAS版在boot/app.php中加载此文件后，需要执行 
 auto_include();
 autoload_theme('admin'); 
*/
/**
* 处理异常
*/
set_exception_handler(function ($e) {
    $err = $e->getMessage(); 
    if(function_exists('exception')){
        exception($e);
    }  
    $err = ['msg'=>$e->getMessage(),'line'=>$e->getLine(),'file'=>$e->getFile()];
    if(is_cli()){
        pr($err);
        return;
    }
    log_error($err,'exception'); 
    do_action("exception",$err);
    if(!DEBUG){
        return;
    }    
    if(is_json_request()){ 
        json_error($err);
    }else{
        if(DEBUG){ 
            $html = html_error([
                '错误信息：'=>$err['msg'],
                '文件：'=>$err['file'],
                '行号：'=>$err['line'],
            ]);
            echo $html;exit;
        } 
    }
});