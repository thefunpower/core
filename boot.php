<?php
/*
    Copyright (c) 2021-2031, All rights reserved.
    This is NOT a freeware
    LICENSE: https://github.com/thefunpower/core/blob/main/LICENSE.md 
    Connect Email: sunkangchina@163.com 
    Code Vesion: v1.0.x
*/
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
//think-orm
include __DIR__ . '/inc/orm.php';
//加载共用函数
include __DIR__ . '/helper.php';
//加载css js配置
include    PATH . '/boot/misc.ini.php';
include __DIR__ . '/inc/misc.php';
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