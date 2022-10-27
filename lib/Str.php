<?php

/*
	Copyright (c) 2021-2050 FatPlug, All rights reserved.
	This file is part of the FatPlug Framework (http://fatplug.cn).
	This is not free software.
	you can redistribute it and/or modify it under the
	terms of the License after purchased commercial license. 
	mail: sunkangchina@163.com
*/


namespace lib;

class Str
{
	/**
	* 使用Sonyflake生成唯一值，确保并发时生成唯一ID
	* config.ini.php
	* $config['redis'] = [
	* 	'host'=>'',
	* 	'port'=>'',
	* 	'auth'=>'',
	* 	'prefix'=>'',
	* ];
	* 
	* $config['sony_flake'] = [
	* 	'center_id'=>0,
	* 	'work_id'=>0,
	* 	'from_date'=>'2022-10-27',
	* ];
	* https://github.com/godruoyi/php-snowflake
	*/
	public static function sony_flake_id(){
		global $config;
		$redis_config = $config['redis'];
		$sony_flake  = $config['sony_flake'];
		$start_date  = $sony_flake['from_date']?:"2022-10-27";
		$SonyflakeCenterID  = $sony_flake['center_id']?:0;
		$SonyflakeWorkID  = $sony_flake['work_id']?:0; 
		$redis = new \Redis(); 
		$redis->connect($redis_config['host'], $redis_config['port']); 
		if($redis_config['auth']){
			$redis->auth($redis_config['auth']);	
		}		
		$snowflake = new \Godruoyi\Snowflake\Sonyflake($SonyflakeCenterID, $SonyflakeWorkID);
		$snowflake->setStartTimeStamp(strtotime(date($start_date))*1000)
		        ->setSequenceResolver(new \Godruoyi\Snowflake\RedisSequenceResolver($redis)); 
		$id = $snowflake->id(); 
		return $id;
	}	
	/**
	* 生成订单号
	*/
	public static function order_num($prefix = '')
	{
		return $prefix . date('Ymd') .self::sony_flake_id();
	}
	/**
	 * 500m 1km
	 * 1公里
	 * @param  mixed $dis [description]
	 * @return string     [description]
	 */
	public static function dis($dis)
	{
		$l['公里'] = 1000;
		$l['里']   = 1000;
		$l['m']    = 1;
		foreach ($l as $k => $num) {
			if (strpos($dis, $k) !== false) {
				$dis = str_replace($k, "", $dis);
				$dis = $dis * $num;
			}
		}
		return $dis;
	}
	/**
	 * 折扣 100 1 0.1折
	 * @param string $size 
	 * @return string　 
	 */
	public static function discount($price, $nowprice)
	{
		return round(10 / ($price / $nowprice), 1);
	}

	static $size = ['B', 'KB', 'MB', 'GB', 'TB'];

	/**
	 * 计算时间剩余　 
	 * 
	 * $timestamp - $small_timestamp 剩余的时间，相差几天几小时几分钟
	 * @param   $timestamp 当前时间戳
	 * @param   $small_timestamp 自定义时间戳，小于当前时间戳
	 * @return array ２天３小时２８分钟１０秒 
	 */
	public static function less_time($timestamp, $small_timestamp = null)
	{
		if (!$small_timestamp) $time = $timestamp;
		else $time = $timestamp - $small_timestamp;
		if ($time <= 0) return -1;
		$days = intval($time / 86400);
		$remain = $time % 86400;
		$hours = intval($remain / 3600);
		$remain = $remain % 3600;
		$mins = intval($remain / 60);
		$secs = $remain % 60;
		return ["d" => $days, "h" => $hours, "m" => $mins, "s" => $secs];
	}

	/**
	 * 字节单位自动转换 显示1GB MB等
	 * @param string $size 
	 * @return string　 
	 */
	public static function size($size)
	{
		$units = static::$size;
		for ($i = 0; $size >= 1024 && $i < 4; $i++) {
			$size /= 1024;
		}
		return round($size, 2) . ' ' . $units[$i];
	}
	/**
	 * 字节单位自动转换到指定的单位
	 * @param string $size 　 
	 * @param string $to 　
	 * @return string
	 */
	public static function size_to($size, $to = 'GB')
	{
		$size = strtoupper($size);
		$to = strtoupper($to);
		$arr = explode(' ', $size);
		$key = $arr[1];
		$size = $arr[0];
		$i = array_search($key, static::$size);
		$e = array_search($to, static::$size);
		$x = 1;
		if ($i < $e) {
			for ($i; $i < $e; $i++) {
				$x *= 1024;
			}
			return round($size / $x, 2);
		}
		for ($e; $e < $i; $e++) {
			$x *= 1024;
		}
		return $size * $x;
	}

	/**
	 * 随机数字
	 * @param string $j 位数 　 
	 * @return int
	 */
	public static function rand_number($j = 4)
	{
		$str = null;
		for ($i = 0; $i < $j; $i++) {
			$str .= mt_rand(0, 9);
		}
		return $str;
	}
	/**
	 * 随机字符
	 * @param string $j 位数 　 
	 * @return string
	 */
	public static function rand($j = 8)
	{
		$string = "";
		for ($i = 0; $i < $j; $i++) {
			srand((float)microtime() * 1234567);
			$x = mt_rand(0, 2);
			switch ($x) {
				case 0:
					$string .= chr(mt_rand(97, 122));
					break;
				case 1:
					$string .= chr(mt_rand(65, 90));
					break;
				case 2:
					$string .= chr(mt_rand(48, 57));
					break;
			}
		}
		return $string; //to uppercase
	}

	/**
	 * 截取后，用 ...代替被截取的部分
	 * @param  string $string 字符串
	 * @param  int $length 截取长度
	 * @return string
	 */
	public static function cut($string, $length)
	{
		$new_str = '';
		preg_match_all("/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/", $string, $info);
		for ($i = 0; $i < count($info[0]); $i++) {
			$new_str .= $info[0][$i];
			$j = ord($info[0][$i]) > 127 ? $j + 2 : $j + 1;
			if ($j > $length - 3) {
				return $new_str . " ...";
			}
		}
		return join('', $info[0]);
	}
}
