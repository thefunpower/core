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
* $where = Medoo::raw("WHERE $json like :like", [':like' => "%" . $arr[1] . "%"]); 
* 
* $data = db_pager("log", "*", $where); //db_pager_count(100);
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
global $db;
/**
* 数据参数用于分页后生成分页HTML代码
*/
global $db_par;
/**
* 错误信息
*/
global $db_error;
//连接数据库  
try {
    $pdo = new PDO($dsn, $user, $pwd);
    $db = new Medoo\Medoo([
        'pdo'     => $pdo,
        'type'    => 'mysql',
        'option'  => [
            PDO::ATTR_CASE => PDO::CASE_NATURAL
        ],
        'command' => [
            'SET SQL_MODE=ANSI_QUOTES'
        ],
        'error' => PDO::ERRMODE_WARNING
    ]);
} catch (Exception $e) {
    $err = $e->getMessage();
    write_log_error("连接数据库失败");
    add_action("db_connect_error", $err);
}

/**
多数据库时有用
$config['db_host'] = '127.0.0.1';
//数据库名
$config['db_name'] = 'saas_admin';
//数据库登录用户名
$config['db_user'] = 'root';
//数据库登录密码
$config['db_pwd']  = '111111'; 
//数据库端口号
$config['db_port'] = 3306;
 */
function new_db($config = [])
{
    $db = new \Medoo\Medoo([
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
    return $db;
}
/**
 * 数据库实例
 *
 * @return object
 */
function db()
{
    global $db;
    return $db;
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
    global $db_par;
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
    $p1 = $_REQUEST['per_page'] ?: g('per_page');
    $p2 = $_REQUEST['page_size'] ?: g('page_size');
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
    $db_par['size'] = $pre_page;
    $db_par['count'] = $count;
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
 * 显示分页
 * 调用db_pager后，再调用。
 * db_pager_html([
 *      'url'=>'',
 * ]);
 */
function db_pager_html($arr = [])
{
    global $db_par;
    if ($arr['count']) {
        $count  = $arr['count'];
    } else {
        $count = $db_par['count'];
    }
    $page_url = $arr['url'];
    if ($arr['size']) {
        $size  = $arr['size'];
    } else {
        $size = $db_par['size'] ?: 20;
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
    global $db_error;
    write_log($str,'error');
    $db_error[] = $str;
}
/**
* 获取错误信息
*/
function db_get_error()
{
    global $db_error;
    if ($db_error)
        return $db_error;
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
        //查寻数据
        do_action("db_get.$table", $all);
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
function db_insert($table, $data = [])
{
    foreach ($data as $k => $v) {
        if (substr($k, 0, 1) == "_") {
            unset($data[$k]);
        }
    }
    try {
        //写入数据前
        do_action("db_insert.$table.before", $data);
        foreach($data as $k=>$v){ 
            if(is_array($v)){
                $data[$k] = json_encode($v,JSON_UNESCAPED_UNICODE);   
            }else{
                $data[$k] = addslashes($v);  
            }          
        } 
        $db    = db()->insert($table, $data);
        $id = db()->id();
        //写入数据后
        $action_data = [];
        $action_data['id'] = $id;
        $action_data['data'] = $data;
        do_action("db_insert.$table.after", $action_data);
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
function db_update($table, $data = [], $where = [])
{
    global $db_where;
    $db_where = $where;

    foreach ($data as $k => $v) {
        if (substr($k, 0, 1) == "_") {
            unset($data[$k]);
        }
    }
    try {
        //更新数据前
        do_action("db_update.$table.before", $data);
        foreach($data as $k=>$v){
            if(is_array($v)){
                $data[$k] = json_encode($v,JSON_UNESCAPED_UNICODE);   
            }else{
                $data[$k] = addslashes($v);  
            }          
        }
        $db    = db()->update($table, $data, $where);
        $error = db()->error;
        if ($error) {
            write_log(['db_error' => $error, 'sql' => db()->last()]);
            throw new Exception($error);
        }
        $count =  $db->rowCount();
        //更新数据后
        $action_data = [];
        $action_data['where'] = $where;
        $action_data['data'] = $data;
        do_action("db_update.$table.after", $action_data);
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
    $db     = db();
    $db->action(function ($db) use (&$result, $call) {
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
    $db = db_get($table, $join, $columns, $where);
    if ($db) {
        $one =  $db[0];
        //查寻数据
        do_action("db_get_one.$table", $one);
        return $one;
    }
    return;
}  
/**
 * SQL查寻
 */
function db_query($sql, $raw = null)
{
    if ($raw === null) {
        return db()->query($sql);
    }
    $q = db()->query($sql, $raw);
    if ($q) {
        $all =  $q->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        //查寻数据
        do_action("db_query", $all);
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
    do_action("db_insert.$table.del", $where);
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
