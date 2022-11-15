<?php

/*
    Copyright (c) 2021-2050 FatPlug, All rights reserved.
    This file is part of the FatPlug Framework (http://fatplug.cn).
    This is not free software.
    you can redistribute it and/or modify it under the
    terms of the License after purchased commercial license. 
    mail: sunkangchina@163.com
*/
/**
*  对数据库操作的封装
*  https://medoo.in/api/where 
*/

if (!defined('VERSION')) {
    die();
} 
/**
复杂的查寻，(...  AND ...) OR (...  AND ...)
"OR #1" => [
    "AND #2" => $where,
    "AND #3" => $or_where
    ]
];  

 */
/**
* 数据库对象
* 建议使用 db()
*/
global $_db;
global $_db_active;
$_db_active  = 'default';
global $_db_connects;
/**
* 数据参数用于分页后生成分页HTML代码
*/
global $_db_par;
/**
* 错误信息
*/
global $_db_error;
/**
 * 连接数据库
 */
include __DIR__.'/../db_connect.php';
/**
 * 激活平台数据库连接，平台数据库不支持从库读
 */
function db_active_main()
{
    db_active('main');
}
/**
 * 激活读数据库连接
 */
function db_active_read()
{
    db_active('read');
}
/**
 * 激活默认数据库连接
 */
function db_active_default()
{
    db_active('default');
}

/**
 * 激活当前使用哪个数据库
 */
function db_active($name = 'default')
{
    global $_db_active;
    $_db_active  = $name;
}
/**
* 获取当前启用的数据库连接 
*/
function get_db_active_name()
{
    global $_db_active;
    return $_db_active;
}
/**
* 判断是否可运行action
*/
function db_can_run_action(){
    $name = get_db_active_name();
    //数据库连接平台时是不能使用action的
    if($name == 'main'){
        return false;
    } 
    return true;
}
/**
* 数据库是否可执行更新操作
*/
function db_can_run_update($sql = ''){
    $name = get_db_active_name();
    if($name == 'read'){
        if($sql){
            if(strpos(strtoupper($sql),'UPDATE') !== false){
                return false;
            }else{
                return true;
            }
        }
        return false;
    } 
    return true;
}

/** 
 * 连接数据库
 */
function new_db($config = [],$name = '')
{
    global $_db_connects;
    $_db = new \Medoo\Medoo([
        'type' => 'mysql',
        'host' => $config['db_host'],
        'database' => $config['db_name'],
        'username' => $config['db_user'],
        'password' => $config['db_pwd'],
        // [optional]
        'charset'   => 'utf8mb4',
        'collation' => 'utf8mb4_general_ci',
        'port'      => $config['db_port'],
        'prefix'    => '',
        'error'     => PDO::ERRMODE_SILENT,
        // Read more from http://www.php.net/manual/en/pdo.setattribute.php.
        'option'    => [
            PDO::ATTR_CASE => PDO::CASE_NATURAL
        ],
        'command' => [
            'SET SQL_MODE=ANSI_QUOTES'
        ]
    ]);
    if($name){
        $_db_connects[$name] = $_db;
    }
    return $_db;
}
/**
 * 数据库实例
 *
 * @return object
 */
function db()
{
    global $_db_connects,$_db_active; 
    return $_db_connects[$_db_active];
}

/***
 * 分页查寻 
 JOIN
 $where = [ 
    //"do_order.id"=>1,
    'ORDER'=>[
        'do_order.id'=>'DESC'
    ]
]; 

int date
$where['printer_refund_apply.created_at[<>]']  = [
 $dates[0] / 1000, $dates[1] / 1000
];
datetime
$where['printer_refund_apply.created_at[<>]']  = [
 date('Y-m-d H:i:s',$dates[0] / 1000), date('Y-m-d H:i:s',$dates[1] / 1000)
];

$data = db_pager("do_order",
    ["[><]do_mini_user" => ["uid" => "id"]],
    [
        "do_order.id",
        "do_order.uid",
        "user" => [
            "do_mini_user.nickName",
            "do_mini_user.avatarUrl",
            "do_mini_user.openid",
        ]
    ],
    $where);

 * @param string $table 表名
 * @param string $column 字段 
 * @param array $where  条件 [LIMIT=>1]  
 * @return array
 */ 
function db_pager($table, $join, $columns = null, $where = null)
{
    global $_db_par;
    $flag = true;
    if (!$where) {
        $where   = $columns;
        $columns = $join ?: "*";
        $join    = '';
        $count   = db_pager_count() ?: db_get_count($table, $where);
    } else if ($join && $where) {
        $flag    = false;
        $count   = db_pager_count() ?: db_get_count($table, $join, "$table.id", $where);
    }
    $current_page  = $_REQUEST['page'];
    if (!$current_page) {
        $current_page  = g('page');
    }
    if (!$current_page) {
        $current_page = 1;
    }
    $p1 = (int)($_REQUEST['per_page'] ?: g('per_page'));
    $p2 = (int)($_REQUEST['page_size'] ?: g('page_size'));
    if ($p1 >= 1) {
        $pre_page  = $p1;
    } else {
        $pre_page  = $p2;
    }
    if (!$pre_page) {
        $pre_page = 20;
    }
    $count = (int)$count;
    $last_page     = ceil($count / $pre_page);
    $has_next_page = $last_page > $current_page ? true : false;
    $start         = ($current_page - 1) * $pre_page;
    if (is_object($where)) {
        $where->value =  $where->value . " LIMIT $start, $pre_page";
    } else {
        $where['LIMIT'] = [$start, $pre_page];
    }

    if ($flag) {
        $data  =  db_get($table, $columns, $where);
    } else {
        $data  =  db_get($table, $join, $columns, $where);
    }
    $_db_par['size'] = $pre_page;
    $_db_par['count'] = $count;
    return [
        'current_page' => $current_page,
        'last_page'    => $last_page,
        'pre_page'     => $pre_page,
        'total'        => $count,
        'has_next_page' => $has_next_page,
        'data'         => $data,
        'data_count'   => count($data ?: []),
    ];
}
/**
 *  设置分页总记录数
 *  一般情况用不到
 */
function db_pager_count($nums = null)
{
    static $_page_count;
    if ($nums && $nums >= 0) {
        $_page_count = $nums;
    } else {
        return $_page_count;
    }
}
/**
 * 显示分页
 * 调用db_pager后，再调用。
 * db_pager_html([
 *      'url'=>'',
 * ]);
 */
function db_pager_html($arr = [])
{
    global $_db_par;
    if ($arr['count']) {
        $count  = $arr['count'];
    } else {
        $count = $_db_par['count'];
    }
    $page_url = $arr['url'];
    if ($arr['size']) {
        $size  = $arr['size'];
    } else {
        $size = $_db_par['size'] ?: 20;
    }
    $paginate = new \lib\Paginate($count, $size);
    if ($page_url) {
        $paginate->url = $page_url;
    }
    $limit  = $paginate->limit;
    $offset = $paginate->offset;
    return $paginate->show();
}
 

/**
* 添加错误信息
*/
function db_add_error($str)
{
    if(DEBUG){
        pr($str);exit;
    }
    global $_db_error;
    write_log($str,'error');
    $_db_error[] = $str;
}
/**
* 获取错误信息
*/
function db_get_error()
{
    global $_db_error;
    if ($_db_error)
        return $_db_error;
}

/***
 * 根据表名、字段 、条件 查寻多条记录
 * where https://medoo.in/api/where
 * select($table, $columns)
 * select($table, $columns, $where)
 * select($table, $join, $columns, $where)
 * @param string $table 表名
 * @param string $column 字段 
 * @param array $where  条件 [LIMIT=>1]  
 * @return array
 */
function db_get($table, $join = null, $columns = null, $where = null)
{
    if (is_array($join)) {
        foreach ($join as $k => $v) {
            if (is_string($v) && strpos($v, '(') !== false) {
                $join[$k] = db_raw($v);
            }
        }
    }
    if (is_string($columns) && strpos($columns, 'WHERE') !== FALSE) {
        $columns = db_raw($columns);
    }
    try {
        $all =  db()->select($table, $join, $columns, $where);
        if($all){
            foreach($all as &$v){
                db_row_json_to_array($table,$v);
            }
        } 
        //查寻数据 
        if(db_can_run_action()){
            do_action("db_get.$table", $all);    
        }
        return $all;
    } catch (Exception $e) {
        db_add_error($e->getMessage());
    }
}
/** 
$lists = db_select('do_order', [ 
            'count' => 'COUNT(`id`)',
            'total' => 'SUM(`total_fee`)',
            'date'  => "FROM_UNIXTIME(`inserttime`, '%Y-%m-%d')"
        ], 
        'WHERE `status` = 1 GROUP BY `date` LIMIT 30'
);  
 */
function db_select($table, $join = "*", $columns = null, $where = null)
{
    return db_get($table, $join, $columns, $where);
}
/**
 * 写入记录
 *
 * @param string $table 表名 
 * @param array  $data  数据 
 * @return array
 */
function db_insert($table, $data = [],$don_run_action = false)
{
    foreach ($data as $k => $v) {
        if (substr($k, 0, 1) == "_") {
            unset($data[$k]);
        }
    }
    try {
        //写入数据前 
        foreach($data as $k=>$v){ 
            if(get_table_field_is_json($table,$k)){
                if($v && !is_array($v)){
                    $arr = json_decode($v,true);
                    if($arr){
                        $v = $arr;
                    }else{
                        $v = yaml($v);
                    } 
                }
                if(!$v){
                    $v = [];
                }
            }
            if(is_array($v)){
                $data[$k] = json_encode($v,JSON_UNESCAPED_UNICODE);   
            }else{
                $data[$k] = addslashes($v);  
            }          
        } 
        if(db_can_run_action() && !$don_run_action){
            do_action("db_insert.$table.before", $data);
        }
        $_db    = db()->insert($table, $data);
        $id = db()->id();
        //写入数据后
        $action_data = [];
        $action_data['id'] = $id;
        $action_data['data'] = $data;
        if(db_can_run_action() && !$don_run_action){
            do_action("db_insert.$table.after", $action_data);
        }
        return $id;
    } catch (Exception $e) {
        db_add_error($e->getMessage());
    }
}

/**
 * 写入记录
 *
 * @param string $table 表名 
 * @param array  $data  数据 
 * @return array
 */
function db_update($table, $data = [], $where = [],$don_run_action = false)
{
    if(!db_can_run_update()){
        exit('从库禁止运行update操作');
    }
    global $_db_where;
    $_db_where = $where;

    foreach ($data as $k => $v) {
        if (substr($k, 0, 1) == "_") {
            unset($data[$k]);
        }
    }
    try {
        //更新数据前 
        foreach($data as $k=>$v){
            if(get_table_field_is_json($table,$k)){
                if($v && !is_array($v)){
                    $arr = json_decode($v,true);
                    if($arr){
                        $v = $arr;
                    }else{
                        $v = yaml($v);
                    } 
                }
                if(!$v){
                    $v = [];
                }
            }
            if(is_array($v)){
                $data[$k] = json_encode($v,JSON_UNESCAPED_UNICODE);   
            }else{
                $data[$k] = addslashes($v);  
            }          
        }
        if(db_can_run_action() && !$don_run_action){
            do_action("db_update.$table.before", $data);
        }
        $_db    = db()->update($table, $data, $where);
        $error = db()->error;
        if ($error) {
            write_log(['db_error' => $error, 'sql' => db()->last()]);
            throw new Exception($error);
        }
        $count =  $_db->rowCount();
        //更新数据后
        $action_data = [];
        $action_data['where'] = $where;
        $action_data['data'] = $data;
        if(db_can_run_action() && !$don_run_action ){
            do_action("db_update.$table.after", $action_data);
        }
        return $count;
    } catch (Exception $e) {
        db_add_error($e->getMessage());
    }
}

/**
 * 数据库事务
 *
 */
function db_action($call)
{
    $result = "";
    $_db     = db();
    $_db->action(function ($_db) use (&$result, $call) {
        $call();
    });
}
/**
 * 根据表名、字段 、条件 查寻一条记录
 *
 * @param string $table 表名
 * @param string $column 字段 
 * @param array  $where 条件 
 * @return array
 */
function db_get_one($table, $join  = "*", $columns = null, $where = null)
{
    if (!$where) {
        $columns['LIMIT'] = 1;
    } else {
        $where['LIMIT']   = 1;
    }
    $_db = db_get($table, $join, $columns, $where);
    if ($_db) {
        $one =  $_db[0];
        if($one){
            db_row_json_to_array($table,$one);    
        }        
        //查寻数据
        if(db_can_run_action()){
            do_action("db_get_one.$table", $one);
        }
        return $one;
    }
    return;
}  
/**
 * SQL查寻
 */
function db_query($sql, $raw = null)
{
    if(!db_can_run_update($sql)){
        exit('从库禁止运行update操作');
    }
    if ($raw === null) {
        return db()->query($sql);
    }
    $q = db()->query($sql, $raw);
    if ($q) {
        $all =  $q->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        //查寻数据
        if(db_can_run_action()){
            do_action("db_query", $all);
        }
        return $all;
    } else {
        return [];
    }
}
/**
 * 取最小值 
 * https://medoo.in/api/min
 * min($table, $column, $where)
 * min($table, $join, $column, $where)
 * @param string $table  表名
 * @param string $column 字段 
 * @param array $where   条件
 * @return void
 */
function db_get_min($table, $join  = "*", $column = null, $where = null)
{
    return db()->min($table, $join, $column, $where);
}

/**
 * 取最大值  
 * max($table, $column, $where)
 * max($table, $join, $column, $where)
 * @param string $table  表名
 * @param string $column 字段 
 * @param array $where   条件
 * @return void
 */
function db_get_max($table, $join =  "*", $column = null, $where = null)
{
    return db()->max($table, $join, $column, $where);
}

/**
 * 总数  
 * count($table, $where)
 * count($table, $join, $column, $where)
 * @param string $table  表名 
 * @param array $where   条件
 * @return void
 */
function db_get_count($table, $join =  "*", $column = null, $where = null)
{
    return db()->count($table, $join, $column, $where);
}

/**
 * 是否有记录
 * has($table, $where)
 * has($table, $join, $where)
 * @param string $table  表名 
 * @param array $where   条件
 * @return void
 */
function db_get_has($table, $join = null, $where = null)
{
    return db()->has($table, $join, $where);
}

/**
 * 随机取多条记录  
 * rand($table, $column, $where)
 * rand($table, $join, $column, $where)
 * @param string $table  表名
 * @param string $column 字段 
 * @param array $where   条件
 * @return void
 */
function db_get_rand($table, $join = "*", $column = null, $where = null)
{
    return db()->rand($table, $join, $column, $where);
}

/**
 * 取总和
 * sum($table, $column, $where)
 * sum($table, $join, $column, $where)
 * @param string $table  表名
 * @param string $column 字段 
 * @param array $where   条件
 * @return void
 */
function db_get_sum($table, $join = "*", $column = null, $where = null)
{
    return db()->sum($table, $join, $column, $where);
}

/**
 * 取平均值 
 * avg($table, $column, $where)
 * avg($table, $join, $column, $where)
 * @param string $table  表名
 * @param string $column 字段 
 * @param array $where   条件
 * @return void
 */
function db_get_avg($table, $join = "*", $column = null, $where = null)
{
    return db()->avg($table, $join, $column, $where);
}

/**
 * RAW
 * https://medoo.in/api/raw
 * raw('NOW()')
 * raw('RAND()')
 * raw('AVG(<age>)') 
 * @param string $raw
 * @return  
 */
function db_raw($raw)
{
    return \Medoo\Medoo::raw($raw);
}

//删除
function db_del($table, $where)
{
    //删除数据前
    if(db_can_run_action()){
        do_action("db_insert.$table.del", $where);
    }
    return db()->delete($table, $where);
}

function db_delete($table, $where)
{
    return db_del($table, $where);
}

/**
 * 显示所有表名
 *
 * @param string $table 表名
 * @version 1.0.0
 * @author sun <sunkangchina@163.com>
 * @return void
 */
function show_tables($table)
{
    $sql = "SHOW TABLES LIKE '%$table%';";
    $all = db()->query($sql);
    foreach ($all as $v) {
        foreach ($v as $v1) {
            $list[] = $v1;
        }
    }
    return $list;
}
/**
 * 取表中字段
 */
function get_table_fields($table, $has_key  = true)
{
    $sql   = "SHOW FULL FIELDS FROM `".$table."`";
    $lists = db()->query($sql);
    $arr   = [];
    foreach ($lists as $vo) {
        if ($has_key) {
            $arr[$vo['Field']] = $vo;
        } else {
            $arr[] = $vo['Field'];
        }
    }
    return $arr;
}
/**
 *返回数据库允许的数据，传入其他字段自动忽略
 */ 
function db_allow($table, $data)
{
    $fields = get_table_fields($table);
    foreach ($data as $k => $v) {
        if (!$fields[$k]) {
            unset($data[$k]);
        }
    }
    return $data;
}

/**
 * 显示数据库表结构，支持markdown格式
 * @param string $name 数据库名
 * @version 1.0.0
 * @author sun <sunkangchina@163.com>
 */
function database_tables($name = null, $show_markdown = false)
{
    global $config;
    if (!$name) {
        $name = $config['db_name'];
    }
    $sql  = "SHOW TABLE STATUS FROM `{$name}`";
    $all  = db_query($sql, []);
    foreach ($all as $k => $v) {
        $sql   = "SHOW FULL FIELDS FROM `" . $v['Name'] . "`";
        $lists = db()->query($sql, []);
        $all[$k]['FIELDS'] = $lists;
    }
    if (!$show_markdown) {
        return $all;
    }
    $str = "";
    foreach ($all as $v) {
        $str .= "###### " . $v['Name'] . " " . $v['Comment'] . "\n";
        $str .= "| 字段  |  类型 | 备注|\n";
        $str .= "| ------------ | ------------ |------------ |\n";
        foreach ($v['FIELDS'] as $vo) {
            $str .= "|  " . $vo['Field'] . " |  " . $vo['Type'] . " |" . $vo['Comment'] . "|\n";
        }
        $str .= "\n\n";
    }
    return $str;
}
/**
 * 取表中json字段
 */
function get_table_field_json($table){
    static $table_fields;
    if(!$table_fields[$table]){
      $all = get_table_fields($table); 
      $table_fields_row = [];
      foreach($all as $k=>$v){
        if($v['Type'] == 'json'){
            $table_fields_row[$k] = true; 
        }
      } 
      $table_fields[$table] = $table_fields_row;
    }
    return $table_fields[$table];
}
/**
 * 判断表中的字段是不是json
 */
function get_table_field_is_json($table,$field){
    $table_fields = get_table_field_json($table); 
    if($table_fields[$field]){
      return true;
    }else{
      return false;
    }    
}

/**
* 把数据库中json字段转成array
* @param $table_name 表名
* @param $row_data 一行记录
*/
function db_row_json_to_array($table_name,&$row_data = []){
    foreach ($row_data as $key=>$val) {
        if(is_string($val) && get_table_field_is_json($table_name,$key)){  
          $row_data[$key] = json_decode($val,true)?:[];   
        }
        if(is_string($val) && is_json($val)){
          $row_data[$key] = json_decode($val,true);
        }
    } 
}

