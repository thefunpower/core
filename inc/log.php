<?php
/*
    Copyright (c) 2021-2031, All rights reserved.
    This is NOT a freeware
    LICENSE: https://github.com/thefunpower/core/blob/main/LICENSE.md 
    Connect Email: sunkangchina@163.com 
    Code Vesion: v1.0.x
*/
/**
 * 日志
 * @license read license.txt
 * @author sun <sunkangchina@163.com>
 * @copyright (c) 2021 
 */
/**  
write_log_info('一般');
write_log_warn('警告');
write_log_error('错误');
write_log_error(['data'=>['a'=>1]]); 
 */


/////////////////////////////////////////////////////////////
// 生成日志
/////////////////////////////////////////////////////////////

use think\facade\Log;

$log_from = "web";
if (is_cli()) {
    $log_from = 'cli';
}
$log_path = PATH . 'data/log/' . $log_from;
Log::init([
    'default'   =>  'file',
    'channels'  =>  [
        'file'  =>  [
            'type'  =>  'file',
            'path'  =>  $log_path,
            'time_format'   =>    'Y-m-d H:i:s',
        ],
    ],
]);

/**
 * 写一般日志
 */
function write_log_info($msg)
{
    write_log($msg, 'info');
}

/**
 * 写警告日志
 */
function write_log_warn($msg)
{
    write_log($msg, 'warning');
}
/**
 * 写错误日志
 */
function write_log_error($msg)
{
    write_log($msg, 'error');
}

/**
 * 
 * 记录日志 
 * 日志的级别从低到高依次为： debug, info, notice, warning, error, critical, alert 
 * 以下级别会记录到数据库 
 * write_log(['txt'=>"错误了!!!",'trace'=>'登录成功'], 'alert');
 * write_log("错误了!!!", 'error');
 * write_log("错误了!!!", 'critical');
 * write_log("错误了!!!", 'alert'); 
 * 
 *          
CREATE TABLE IF NOT EXISTS `log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `level` varchar(100) NOT NULL COMMENT '',
  `url` varchar(1000) NOT NULL COMMENT '',
  `file` varchar(1000) NOT NULL COMMENT '',
  `line` varchar(100) DEFAULT NULL COMMENT '',
  `msg` json NOT NULL,
  `trace` text NOT NULL COMMENT '', 
  `created_at` datetime NULL COMMENT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; 

 */ 
function write_log($msg, $level = 'info')
{ 
    global $config; 
    $level = strtolower($level); 
    $trace = debug_backtrace(false);  
    Log::write($trace, $level);  
    $last = count($trace)-1;  
    if ($config['log_db'] && in_array($level, ['warning', 'error', 'critical', 'alert'])) {
        $arr = [];
        $arr['url'] = urldecode($_SERVER['REQUEST_URI']);
        $arr['level'] = $level;
        $arr['file'] = str_replace("\\","/",$trace[$last]['file']);
        $arr['line'] = $trace[$last]['line']; 
        if(!is_array($msg)){
            $msg = ['log'=>$msg];
        }
        $arr['msg']  = $msg;
        $arr['trace'] = $trace;
        $arr['created_at'] = now();  
        $arr = db_allow("log",$arr);  
        db_insert('log', $arr);   
    } 
    do_action("log", $trace); 
} 

/**
 * 关闭当前日志写入
 */
function log_close()
{
    Log::close();
}
