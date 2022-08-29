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
//加载配置
include PATH . '/config.ini.php';
include __DIR__ . '/inc/orm.php';
//加载共用函数
include __DIR__ . '/helper.php';
//加载css js配置
include PATH . '/misc.ini.php';
include __DIR__ . '/inc/misc.php';
//跨域
include __DIR__ . '/inc/cross.php';
//JWT接口验证
include __DIR__ . '/inc/jwt.php';
//缓存
include __DIR__ . '/inc/cache.php';
//日志
include __DIR__ . '/inc/log.php';
//数据库操作
include __DIR__ . '/inc/db.php';
//权限 
include __DIR__ . '/inc/auth.php';
$url = $_SERVER['REQUEST_URI'];
$plugin_name = substr($url, 1);
if (strpos($plugin_name, 'plugins') !== false) {
	$plugin_name = substr($plugin_name, strpos($plugin_name, 'plugins/'));
	$plugin_name = substr($plugin_name, strpos($plugin_name, '/') + 1);
	$plugin_name = substr($plugin_name, 0, strpos($plugin_name, '/'));
	if ($plugin_name) {
		if (!has_actived_plugin()[$plugin_name]) {
			if (cookie(ADMIN_COOKIE_NAME)) {
				//此插件未启用
				do_action("plugin.not_install", $plugin_name);
				write_log_error('插件' . $plugin_name . '未安装');
				die();
			} else {
				//访问插件，但用户没登录
				do_action("admin.not_login");
				//此插件未启用
				do_action("plugin.not_install", $plugin_name);
				jump(ADMIN_DIR_NAME . '/login.php');
			}
		}
	}
}
/**
 * 加载插件中的路径文件 router.php
 */
auto_include();
autoload_theme('admin');
