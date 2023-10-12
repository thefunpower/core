<?php
/*
    Copyright (c) 2021-2031, All rights reserved.  
    Connect Email: sunkangchina@163.com 
*/
include __DIR__.'/boot.php';
/**
 在项目boot/helper.php添加函数plugin_not_active处理插件未启用显示错误内容
*/
_app_check_plugin($plugin_name,'plugins');
_app_check_plugin($plugin_name,'modules');
/**
 * 加载插件中的路径文件 router.php
 */
auto_include();
autoload_theme('admin'); 
function _app_check_plugin($plugin_name,$plugin_dir='plugins'){
	if (strpos($plugin_name, $plugin_dir) !== false) {
		$plugin_name = substr($plugin_name, strpos($plugin_name, $plugin_dir.'/'));
		$plugin_name = substr($plugin_name, strpos($plugin_name, '/') + 1);
		$plugin_name = substr($plugin_name, 0, strpos($plugin_name, '/'));
		if ($plugin_name) {
			if (!has_actived_plugin()[$plugin_name]) {
				 if(function_exists('plugin_not_active')){
				 	plugin_not_active();
				 }
			}
		}
	}
}