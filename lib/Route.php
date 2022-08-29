<?php

namespace lib;

class Route
{

	public static function init()
	{
		global $router;
		$plugin_req_url = "/index.php/plugins/";
		$router->before('GET|POST', $plugin_req_url . '.*', function () use ($plugin_req_url) {
			$uri = $_SERVER['REQUEST_URI'];
			$uri = substr($uri, strpos($uri, $plugin_req_url) + strlen($plugin_req_url));
			if (strpos($uri, '?') !== false) {
				$uri = substr($uri, 0, strrpos($uri, '?'));
			}
			$a = substr($uri, strrpos($uri, '/') + 1);
			$c = substr($uri, 0, strrpos($uri, '/' . $a));
			$c = "\\app\\" . str_replace('/', '\\', $c);
			include __DIR__ . '/admin/base.php';
			if (class_exists($c) && method_exists($c, $a)) {
				(new $c)->$a();
			}
		});
	}
}
