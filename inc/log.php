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
$log_from = "web";
if(is_cli()){
    $log_from = 'cli';
} 
$log_path = PATH.'data/log/'.$log_from.'/';
if(!is_dir($log_path)){
    mkdir($log_path,0777,true);
} 
$SERVER_NAME = $_SERVER['SERVER_NAME']; 
$SERVER_NAME = str_replace("/","",$SERVER_NAME); 
// $config['log_drive'] = 'db';
if($config['log_drive'] == 'db'){
    $log_appender = [
        'class' => 'LoggerAppenderPDO',
        'params' => array(
                'dsn' => $dsn,
                'user' => $config['db_user'],
                'password' => $config['db_pwd'],
                'table' => 'log4php_log',
        ),
    ]; 
}else{
   $log_appender = [
        'class'  => 'LoggerAppenderDailyFile',
        'layout' => array(
            'class' => 'LoggerLayoutHtml',
        ), 
        'params'=>[
            'datePattern' => 'Y-m-d', 
            'file' => $log_path.'log-'.$SERVER_NAME.'-%s.php', 
            'append' => true
        ]
    ]; 
}
 
$log_config = array(
    'rootLogger' => array(
        'appenders' => array('default'),
    ),
    'appenders' => array(
        'default' => $log_appender
    )
); 
if($config['host']){
    try {
        Logger::configure($log_config);        
    } catch (Exception $e) {
        
    }
    
}  
/**
* 写一般日志
*/
function write_log_info($msg,$category='app'){
    write_log($msg,$category,'info');
}  

/**
* 写警告日志
*/
function write_log_warn($msg,$category='app'){ 
    write_log($msg,$category,'warn');
}  
/**
* 写错误日志
*/
function write_log_error($msg,$category='app'){ 
    write_log($msg,$category,'error');
}   
 
/**
* 记录日志
*/
function write_log($msg,$category='app',$level = 'info'){ 
    if(!is_array($msg)){
        $arr['msg'] = $msg;
    }else{
        $arr = $msg;
    }
    $arr['REQUEST_URI'] = urldecode($_SERVER['REQUEST_URI']); 
    $logger = Logger::getLogger($category); 
    //如果是写数据库需要json_encode,如果是写文件不需要 
    $arr = json_encode($arr,JSON_UNESCAPED_UNICODE);
    $logger->$level($arr);
}
 