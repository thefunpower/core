<?php

/*
    Copyright (c) 2021-2031, All rights reserved.
    This is NOT a freeware
    LICENSE: https://github.com/thefunpower/core/blob/main/LICENSE.md 
    Connect Email: sunkangchina@163.com 
*/


namespace lib;
 

class Time
{
	 
	/**
	 * 多少岁
	 * @return string　 
	 */
	public static function age($bornUnix)
	{
		if (strpos($bornUnix, ' ') !== false || strpos($bornUnix, '-') !== false) {
			$bornUnix = strtotime($bornUnix);
		}
		return ceil((time() - $bornUnix) / 86400 / 365);
	}
	/**
	 * 计算时间剩余　
	 * @return string ２天３小时２８分钟１０秒 
	 */
	public static function less_time($a, $b = null)
	{
		if (!$b) $time = $a;
		else $time = $a - $b;
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
	 * 最近30天
	 */
	public static function date($day = 30)
	{
		$arr = [];
		for ($i = 0; $i < $day; $i++) {
			$arr[] = date("Y-m-d", strtotime('-' . $i . ' days'));
		}
		$arr = array_reverse($arr);
		return $arr;
	}

	/**
	 * 最近几天
	 */
	public static function neer_days($num = 5, $separate = "")
	{
		$list  = [];
		for ($i = 0; $i < $num; $i++) {
			$list[] = date("Y" . $separate . "m" . $separate . "d", strtotime("-" . $i . " day", time()));
		}
		$list = array_reverse($list);
		return $list;
	}

	/**
	 * 返回最近几月
	 */
	public static function neer_months($num = 5, $separate = "")
	{
		$list  = [];
		for ($i = 0; $i < $num; $i++) {
			$list[] = date("Y" . $separate . "m", strtotime("-" . $i . " month", time()));
		}
		$list = array_reverse($list);
		return $list;
	}

	/**
	 * 返回最近几年
	 */
	public static function neer_years($num = 5)
	{
		$start = date("Y", strtotime("-" . ($num - 1) . " year", time()));
		$list  = [];
		for ($i = 1; $i <= $num; $i++) {
			$list[] = $start++;
		}
		return $list;
	}
	/**
	 * 取今日、本周、本月、本年、昨日、上周、上月、上年
	 */
	public static function get($key, $date_format = false)
	{
		$arr = [
			'today'      => ['today', 'tomorrow'],
			'yesterday'  => ['yesterday', 'today'],
			'week'       => ['this week 00:00:00', 'next week 00:00:00'],
			'lastweek'   => ['last week 00:00:00', 'this week 00:00:00'],
			'month'      => ['first Day of this month 00:00:00', 'first Day of next month 00:00:00'],
			'lastmonth'  => ['first Day of last month 00:00:00', 'first Day of this month 00:00:00'],
			'year'       => ['this year 1/1', 'next year 1/1'],
			'lastyear'   => ['last year 1/1', 'this year 1/1'],
		];
		$data = $arr[$key];
		if ($data) {
			$ret = [
				strtotime($data[0]),
				strtotime($data[1]),
			];
			if ($date_format) {
				return [
					date('Y-m-d H:i:s', $ret[0]),
					date('Y-m-d H:i:s', $ret[1]),
				];
			}
			return $ret;
		}
	}
}
