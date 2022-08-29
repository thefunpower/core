<?php

//上线把dev去掉
if (!defined('ENV')) {
	define('ENV', 'dev');
}
//设置时区
date_default_timezone_set($config['timezone'] ?: "Asia/Shanghai");
//当前域名
if (!$config['host']) {
	die("请设置config['host']");
}
