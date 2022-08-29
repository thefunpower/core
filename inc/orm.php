<?php
/*

$mongo_config = [
    'host' => '127.0.0.1',
    'dbname' => 'queue',
    'port' => '27017',
    'user' => '',
    'pwd' => '',
];

*/

use think\facade\Db;

class_alias('think\facade\Db', 'Db');
// 数据库配置信息设置（全局有效）
Db::setConfig([
    // 默认数据连接标识
    'default'     => 'mysql',
    // 数据库连接信息
    'connections' => [
        'mysql' => [
            'type'     => 'mysql',
            'dsn'      => $dsn,
            'username' => $user,
            'password' => $pwd,
            'hostport' => $port ?: 3306,
            // 数据库编码默认采用utf8
            'charset'  => 'utf8',
            // 数据库表前缀
            'prefix'   => '',
            // 数据库调试模式
            'debug'    => false,
        ],
        'mongo' => [
            'type'     => 'mongo',
            'hostname' => $mongo_config['host'] ?: '127.0.0.1',
            // 数据库名
            'database'    => $mongo_config['dbname'] ?: 'demo',
            'username' => $mongo_config['user'],
            'password' => $mongo_config['pwd'],
            'hostport' => $mongo_config['port'] ?: 27017,
            // 数据库编码默认采用utf8
            'charset'  => 'utf8',
            // 数据库表前缀
            'prefix'   => '',
            // 数据库调试模式
            'debug'    => false,
        ],
    ],
]);
function mongo_db($name = 'mongo')
{
    return Db::connect($name);
}

/*
mongo_db()->table('queue')->insert([
    'cmd'=>' ll /data ',
    'type'=>'html2pdf',
    'dely'=>0,
    'created_at'=>now()
]);

pr(mongo_db()->table('queue')->select());exit;
*/