<?php

/**
 * 日志
 * @license read license.txt
 * @author sun <sunkangchina@163.com>
 * @copyright (c) 2021 
 */
/**
 * https://logging.apache.org/log4php/index.html


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
 * 记录日志 
 * 日志的级别从低到高依次为： debug, info, notice, warning, error, critical, alert 
 */
if (!function_exists("write_log")) {
    function write_log($msg, $level = 'info')
    {
        $data = [
            'msg' => $msg,
            'level' => $level,
        ];
        $ret = do_action("log", $data);
        if ($ret) {
            return;
        }
        if (!is_array($msg)) {
            $arr['msg'] = $msg;
        } else {
            $arr = $msg;
        }
        $arr['REQUEST_URI'] = urldecode($_SERVER['REQUEST_URI']);
        Log::write($arr, $level);
    }
}

/**
 * 关闭当前日志写入
 */
function log_close()
{
    Log::close();
}
