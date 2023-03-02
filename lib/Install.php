<?php

/*
    Copyright (c) 2021-2031, All rights reserved.
    This is NOT a freeware
    LICENSE: https://github.com/thefunpower/core/blob/main/LICENSE.md 
    Connect Email: sunkangchina@163.com 
*/

namespace lib;

class Install
{
	/*
	* 执行安装
	*/
	public static function init($db, $sql_files = [])
	{
		foreach ($sql_files as $file_name) {
			$fp =  fopen($file_name, "r");
			while ($sql = self::get_next_sql($fp)) {
				$sql = trim($sql);
				if ($sql) {
					$db->query($sql);
				}
			}
			fclose($fp);
		}
	}
	/**
	 * 从文件中逐条取sql
	 */
	public static function get_next_sql($fp)
	{
		$sql = "";
		while ($line = @fgets($fp, 40960)) {
			$line = trim($line);
			$line = str_replace("////", "//", $line);
			$line = str_replace("/", "'", $line);
			$line = str_replace("//r//n", "chr(13).chr(10)", $line);
			$line = stripcslashes($line);
			if (strlen($line) > 1) {
				if ($line[0] == '-' && $line[1] == "-") {
					continue;
				}
			}
			$sql .= $line . chr(13) . chr(10);
			if (strlen($line) > 0) {
				if ($line[strlen($line) - 1] == ";") {
					break;
				}
			}
		}
		return $sql;
	}
}
