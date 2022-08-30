<?php

/*
	Copyright (c) 2021-2050 FatPlug, All rights reserved.
	This file is part of the FatPlug Framework (http://fatplug.cn).
	This is not free software.
	you can redistribute it and/or modify it under the
	terms of the License after purchased commercial license. 
	mail: sunkangchina@163.com
	web: http://fatplug.cn
*/


use think\facade\Cache;
//缓存 使用 https://www.kancloud.cn/manual/thinkphp6_0/1037634
// 缓存配置
/**

//缓存  file 或 redis
$config['cache_drive']  = 'file';
//文件缓存前缀
$config['cache_prefix'] = 'domain_'; 

//redis缓存配置，仅当cache_drive为redis时有效
$config['cache_redis']['host'] = "127.0.0.1";
$config['cache_redis']['port'] = 6379;
//redis缓存前缀
$config['cache_redis']['prefix'] = '';

 */
Cache::config([
    'default'   =>  $config['cache_drive'] ?: 'file',
    'stores'    =>  [
        'file'  =>  [
            'type'   => 'File',
            // 缓存保存目录
            'path'   => PATH . 'data/cache/',
            // 缓存前缀 $config['cache_prefix'] = 'domain_'; 
            'prefix' => $config['cache_prefix'] ?: '',
            // 缓存有效期 0表示永久缓存
            'expire' => 0,
        ],
        'redis' =>  [
            'type'   => 'redis',
            'host'   => $config['cache_redis']['host'],
            'port'   => $config['cache_redis']['port'] ?: 6379,
            'prefix' => $config['cache_redis']['prefix'],
            'expire' => 0,
        ],
    ],
]);

/**
 * 缓存删除
 */
function cache_delete($key)
{
    Cache::delete($key);
}
/**
 * 缓存设置|获取
 */
function cache($key, $data = null, $second = null)
{
    if ($key && $data) {
        if (is_object($data)) {
            $data = (array)$data;
        }
        if (is_array($data)) {
            $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        };
        Cache::set($key, $data, $second);
    } else {
        $data = Cache::get($key);
        $arr  = json_decode($data, true);
        if ($arr) {
            $data = $arr;
        }
        return $data;
    }
}
