<?php

/**
 * 
 * @license read license.txt
 * @author sun <sunkangchina@163.com>
 * @copyright (c) 2021 
 */
if (!defined('VERSION')) {
	die();
}
/**
 * 加载资源文件
 * $base_path = '/node/';
 * misc('echarts'); 
 * misc('jquery,vue,element');   
 */
if (!function_exists('misc')) {
	function misc($name)
	{
		if (strpos($name, ',') !== false) {
			$arr = explode(',', $name);
			foreach ($arr as $v) {
				misc_one($v);
			}
		} else {
			misc_one($name);
		}
	}
}

if (!function_exists('misc_one')) {
	function misc_one($name)
	{
		$name = trim($name);
		static $_load;
		global $misc_config;
		$all = $misc_config[$name];
		if (isset($_load[$name])) {
			return;
		}
		if ($all && is_array($all)) {
			foreach ($all as $v) {
				$ext = get_ext($v);
				if (strpos($v, '//') === false) {
					$url = static_url() . $v;
				} else {
					$url = $v;
				}
				if ($ext == 'js') {
					js($url);
				} else if ($ext == 'css') {
					css($url);
				}
			}
			$_load[$name] = true;
		}
	}
}
