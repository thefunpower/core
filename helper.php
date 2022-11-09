<?php

/*
    Copyright (c) 2021-2050 FatPlug, All rights reserved.
    This file is part of the FatPlug Framework (http://fatplug.cn).
    This is not free software.
    you can redistribute it and/or modify it under the
    terms of the License after purchased commercial license. 
    mail: sunkangchina@163.com
*/

if (!defined('VERSION')) {
    die();
}
/**
 * 数组转tree 
 * 
 * 输入$list 
 * [
 *   {id:1,pid:0,其他字段},
 *   {id:2,pid:1,其他字段},
 *   {id:3,pid:1,其他字段},
 * ]
 * 输出 
 * [
 *   [
 *      id:1,
 *      pid:0,
 *      其他字段,
 *      children:[
 *           {id:2,pid:1,其他字段},
 *           {id:3,pid:1,其他字段},
 *      ]
 *   ]
 * ]
 * 
 */
function array_to_tree($list, $pk = 'id', $pid = 'pid', $child = 'children', $root = 0, $my_id = '')
{
    $tree = array();
    if (is_array($list)) {
        $refer = array();
        foreach ($list as $key => $data) {
            $refer[$data[$pk]] = &$list[$key];
        }
        foreach ($list as $key => $data) {
            $parentId = $data[$pid];
            if ($root == $parentId) {
                $tree[$data[$pk]] = &$list[$key];
            } else {
                if (isset($refer[$parentId])) {
                    $parent = &$refer[$parentId];
                    if ($my_id && $my_id == $list[$key]['id']) {
                    } else {
                        $parent[$child][] = &$list[$key];
                    }
                }
            }
        }
    }
    return $tree;
}
/**
 * 所有会员
 */
function get_user_all($where = [])
{
    $all  = db_get("user", "*", $where);
    $list = [];
    foreach ($all as $v) {
        $list[] = get_user($v['id']);
    }
    return $list;
}
/**
 * 根据用户id查用户信息
 */
function get_user($user_id)
{
    $where['id'] = $user_id;
    return get_user_where($where);
}
/**
 * 查用户信息，where条件
 */
function get_user_where($where = [])
{
    $user    = db_get_one('user', '*', $where);
    if (!$user) {
        return;
    }
    $user_id = $user['id'];
    $login_where = ['user_id' => $user_id];
    $from = g('from');
    if ($from) {
        $login_where['type'] = $from;
    }
    $login   = db_get_one('login', '*', $login_where) ?: [];
    $user    = array_merge($login, $user);
    //meta字段 
    $all = get_user_meta($user_id);
    foreach ($all as $k => $v) {
        $user[$k] = $v;
    }
    $user['group_name'] = user_group_get($user['group_id'])['name'];
    if ($login['avatar_url']) {
        //  $user['avatar_url'] = $login['avatar_url'];
    }
    return $user;
}
/**
 * 取用户扩展字段值
 */
function get_user_meta_where($where = [], $return_row = false)
{
    $user_id = $where['user_id'];
    foreach ($where as $k => $v) {
        if ($k != 'user_id') {
            unset($where[$k]);
            $where['AND'] = ['title' => $k, 'value[~]' => $v];
        }
    }
    if ($user_id) {
        $where['AND'] = ['user_id' => $user_id];
    }
    $new_where['AND'] = $where;
    $all  = db_select('user_meta', '*', $new_where);
    $meta = [];
    foreach ($all as $v) {
        $val = $v['value']; 
        if ($return_row) {
            $meta[$v['title']] = $val;
        } else {
            $meta[] = $v;
        }
    }
    return $meta;
}

/**
 * 取用户扩展字段值
 */
function get_user_meta($user_id)
{
    return get_user_meta_where(['user_id' => $user_id], true);
}
/**
 * 更新用户的meta信息
 * @param array $meta ['nickname'=>'']
 */
function set_user_meta($user_id, $meta)
{
    $user  = db_get_one('user', '*', ['id' => $user_id]);
    if ($user) {
        $all = db_select('user_meta', '*', ['user_id' => $user_id]);
        $insert  = $update = [];
        foreach ($meta as $k => $v) {
            $one = db_get_one('user_meta', '*', ['user_id' => $user_id, 'title' => $k]);
            $id = $one['id'];
            if (is_array($v)) {
                //无需要json_encode，在db中已处理 
            }
            if ($id) {
                db_update('user_meta', ['title' => $k, 'value' => $v], ['id' => $id]);
            } else {
                db_insert('user_meta', ['title' => $k, 'value' => $v, 'user_id' => $user_id]);
            }
        }
    }
}
/**
* 更新用户信息
*/
function set_user($user_id,$data = []){
    db_update("user",$data,['id'=>$user_id]);
    return $user_id;
}
/**
* 数组或字符输出，方便查看
*/
function pr($str)
{
    print_r("<pre>");
    print_r($str);
    print_r("</pre>");
}
/**
 * 添加动作
 * @param string $name 动作名
 * @param couser $call function
 * @version 1.0.0
 * @author sun <sunkangchina@163.com>
 * @return mixed
 */
function add_action($name, $call)
{
    global $_app;
    if (strpos($name, '|') !== false) {
        $arr = explode('|', $name);
        foreach ($arr as $v) {
            add_action($v, $call);
        }
        return;
    }
    $_app['actions'][$name][] = $call;
}
/**
 * 执行动作
 * @param  string $name 动作名
 * @param  array &$par  参数
 * @version 1.0.0
 * @author sun <sunkangchina@163.com>
 * @return  mixed
 */
function do_action($name, &$par = null)
{
    global $_app;
    if (!is_array($_app)) {
        return;
    }
    $calls  = $_app['actions'][$name];
    if ($calls) {
        foreach ($calls as $v) {
            $v($par);
        }
    }
}

/**
 * 取本地插件
 */
function local_plugin()
{
    $dir = PATH . '/plugins/';
    $all = glob($dir . '*/info.php');
    $list = [];
    foreach ($all as $v) {
        $info  = include $v;
        $name  = str_replace($dir, '', $v);
        $name  = substr($name, 0, strpos($name, '/'));
        $base_dir = substr($v, 0, strrpos($v, '/'));
        $info['name'] = $name;
        $list[$name] = $info;
    }
    return $list;
}
/**
 * 已安装插件
 */
function has_actived_plugin()
{
    global $config;
    if (!file_exists(PATH . '/config.ini.php')) {
        return;
    }
    if (!$config['db_host']) {
        return;
    }
    /**
    * //无插件
    * $config['no_plugin']  = true;
    */
    if($config['no_plugin']){
        return;
    }
    $all  = db_get("plugin", "*", ['status' => 1, "ORDER" => ['level' => "DESC"]]);
    $list = [];
    foreach ($all as $v) {
        $list[$v['name']] = $v;
    }
    return $list;
}



/**
 * 自动加载include.php
 */
function auto_include()
{
    $_autoinclude_dir = PATH . 'plugins/';
    $_actived = has_actived_plugin();
    foreach ($_actived as $name => $v) {
        $_autoinclude_file = $_autoinclude_dir . $name . '/include.php'; 
        if (file_exists($_autoinclude_file)) {
            include $_autoinclude_file;
        }
    }
}

/**
 * 自动加载router.php
 */
function auto_include_router()
{
    $_autoinclude_dir = PATH . 'plugins/';
    $_actived = has_actived_plugin();
    foreach ($_actived as $name => $v) {
        $_autoinclude_file = $_autoinclude_dir . $name . '/router.php';
        if (file_exists($_autoinclude_file)) {
            include $_autoinclude_file;
        }
    }
}
/**
 * 自动加载主题
 */
function autoload_theme($name = "front")
{
    global $config;
    $file = PATH . '/theme/' . $config['theme_' . $name] . '/include.php';
    if (file_exists($file)) {
        include $file;
    }
}
//部门tree
function user_group_tree($id = null)
{
    $where = [
        'status' => 1,
        'ORDER' => [
            'sort' => 'DESC'
        ]
    ];
    $title = get_post('name');
    if ($title) {
        $where['name[~]'] = $title;
    }
    $where['ORDER'] = ['sort' => 'DESC', 'pid' => "ASC"];
    $all = db_get("user_group", "*", $where);
    foreach ($all as $v) {
        $v['label'] = $v['name'];
        $v['_pid_name'] = user_group_get($v['pid'])['name'];
        $list[] = $v;
    }
    $list =  array_to_tree(
        $list,
        'id',
        $pid = 'pid',
        $child = 'children',
        $root = 0,
        $id
    );
    $list =  array_values($list);
    return $list;
}

/**
 * 取单个用户组信息
 */
function user_group_get($group_id)
{
    static $obj;
    if ($obj[$group_id]) {
        return $obj[$group_id];
    }
    $one = db_get_one("user_group", "*", ['id' => $group_id]);
    $one['_pid_name'] = db_get_one("user_group", "*", ['id' => $one['pid']]);
    ['name'];
    do_action("plugins.product.type", $one);
    $obj[$group_id] = $one;
    return $one;
}


/**
 * 跳转
 *
 * @param string $url
 * @return void
 */
function jump($url)
{
    if (substr($url, 0, 1) == '/') {
        $url = substr($url, 1);
    }
    if (strpos($url, '://') === false) {
        $url = host() . $url;
    } 
    header("Location: " . $url);
    exit;
}
/**
 * CDN地址
 */
if (!function_exists('static_url')) {
    function static_url()
    {
        global $config;
        $host = $config['host'];
        $arr  = $config['cdn_url'] ?: [];
        $n    = count($arr);
        if ($n > 0) {
            $i    = mt_rand(0, $n - 1);
            return $arr[$i] ?: '/';
        } else {
            return $host;
        }
    }
}
if (!function_exists('cdn')) {
    function cdn()
    {
        return static_url();
    }
}

/**
 * json输出 
 */
function json($data)
{
    global $config;
    $config['is_json'] = true;
    //JSON输出前
    do_action('end.data', $data);
    echo json_encode($data,JSON_UNESCAPED_UNICODE);
    //JSON输出后或页面渲染后
    do_action("end");
    exit;
}
/**
 * 域名
 */
function host()
{
    global $config;
    static $_host;
    if ($_host) {
        return $_host;
    }
    $_host = $config['host'];
    return $_host;
}
/**
 * 判断是命令行下
 */
function is_cli()
{
    return PHP_SAPI == 'cli' ? true : false;
}
/**
 * 是否是POST请求
 */
function is_post()
{
    if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
        return true;
    }
}
/**
 * 判断是否为json 
 */
function is_json($data, $assoc = false)
{ 
    $data = json_decode($data, $assoc);
    if ($data && (is_object($data)) || (is_array($data) && !empty(current($data)))) {
        return $data;
    }
    return false;
}



/**
 * 加载css
 */
function css($file, $is_output = true)
{
    global $_app;
    $key = md5($file);
    if (isset($_app['css'][$key])) {
        return;
    }
    if ($is_output) {
        echo '<link href="' . $file . '" rel="stylesheet">' . "\n";
        $_app['css'][$key] = true;
    } else {
        $_app['css'][$key] = $file;
    }
}
// 加载 JS文件 
function js($file, $is_output = true)
{
    global $_app;
    $key = md5($file);
    if (isset($_app['js'][$key])) {
        return;
    }
    if ($is_output) {
        echo '<script src="' . $file . '"></script>' . "\n";
        $_app['js'][$key] = true;
    } else {
        $_app['js'][md5($file)] = $file;
    }
}

/**
 * 数组转对象
 *
 * @param array $arr 数组
 * @return object
 */
function array_to_object($arr)
{
    if (gettype($arr) != 'array') {
        return;
    }
    foreach ($arr as $k => $v) {
        if (gettype($v) == 'array' || getType($v) == 'object') {
            $arr[$k] = (object) array_to_object($v);
        }
    }
    return (object) $arr;
}

/**
 * 对象转数组
 *
 * @param object $obj 对象
 * @return array
 */
function object_to_array($obj)
{
    $obj = (array) $obj;
    foreach ($obj as $k => $v) {
        if (gettype($v) == 'resource') {
            return;
        }
        if (gettype($v) == 'object' || gettype($v) == 'array') {
            $obj[$k] = (array) object_to_array($v);
        }
    }
    return $obj;
}
/**
 * 取目录名
 *
 * @param string $name
 * @version 1.0.0
 * @author sun <sunkangchina@163.com>
 * @return void
 */
function get_dir($name)
{
    return substr($name, 0, strrpos($name, '/'));
}
/**
 * 取后缀
 *
 * @param string $name
 * @version 1.0.0
 * @author sun <sunkangchina@163.com>
 * @return void
 */
function get_ext($name)
{
    if (strpos($name, '?') !== false) {
        $name = substr($name, 0, strpos($name, '?'));
    }
    $name =  substr($name, strrpos($name, '.'));
    return strtolower(substr($name, 1));
}
/**
 * 取文件名
 *
 * @param string $name
 * @version 1.0.0
 * @author sun <sunkangchina@163.com>
 * @return void
 */
function get_name($name)
{
    $name = substr($name, strrpos($name, '/'));
    $name = substr($name, 0, strrpos($name, '.'));
    $name = substr($name, 1);
    return $name;
}



/**
 * 创建目录 
 */
function create_dir_if_not_exists($arr)
{
    if(is_string($arr)){
        $v = $arr;
        if (!is_dir($v)) {
            mkdir($v, 0777, true);
        }
    }else if(is_array($arr)){
        foreach ($arr as $v) {
            if (!is_dir($v)) {
                mkdir($v, 0777, true);
            }
        }
    } 
}
 

/**
 * 是否是本地环境
 * @version 1.0.0
 * @author sun <sunkangchina@163.com>
 * @return boolean
 */
function is_local()
{
    return in_array(get_ip(), ['127.0.0.1', '::1']) ? true : false;
}

/**
 * 取IP 
 */
function get_ip($type = 0, $adv = false)
{
    $type      = $type ? 1 : 0;
    static $ip = null;
    if (null !== $ip) {
        return $ip[$type];
    }

    if ($adv) {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos = array_search('unknown', $arr);
            if (false !== $pos) {
                unset($arr[$pos]);
            }

            $ip = trim($arr[0]);
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $long = sprintf("%u", ip2long($ip));
    $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}
/**
 * 当前时间
 */
function now()
{
    return date('Y-m-d H:i:s', time());
}


/**
 * 计算两点地理坐标之间的距离
 * @param  Decimal $longitude1 起点经度
 * @param  Decimal $latitude1  起点纬度
 * @param  Decimal $longitude2 终点经度
 * @param  Decimal $latitude2  终点纬度
 * @param  Int     $unit       单位 1:米 2:公里
 * @param  Int     $decimal    精度 保留小数位数
 * @return Decimal
 */
function get_distance($longitude1, $latitude1, $longitude2, $latitude2, $unit = 2, $decimal = 2)
{

    $EARTH_RADIUS = 6370.996; // 地球半径系数
    $PI = 3.1415926;

    $radLat1 = $latitude1 * $PI / 180.0;
    $radLat2 = $latitude2 * $PI / 180.0;

    $radLng1 = $longitude1 * $PI / 180.0;
    $radLng2 = $longitude2 * $PI / 180.0;

    $a = $radLat1 - $radLat2;
    $b = $radLng1 - $radLng2;

    $distance = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2)));
    $distance = $distance * $EARTH_RADIUS * 1000;

    if ($unit == 2) {
        $distance = $distance / 1000;
    }

    return round($distance, $decimal);
}
/**
* 判断是否是ssl
*/
function is_ssl(){
    return isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 1 || strtolower($_SERVER['HTTPS'])=='on') 
        || isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT']==443
        || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO']=='https'
        || isset($_SERVER['HTTP_X_CLIENT_PROTO']) && $_SERVER['HTTP_X_CLIENT_PROTO']=='https';
}
/**
 * 设置、获取cookie
 *
 * @param string $name
 * @param string $value
 * @param integer $expire
 * @return void
 */
function cookie($name, $value = NULL, $expire = 0)
{
    global  $config;
    $name   = $config['cookie_prefix'] . $name;
    $path   = $config['cookie_path'] ?: '/';
    $domain = $config['cookie_domain'] ?: '';
    if ($value === NULL) {
        return $_COOKIE[$name];
    }
    $bool = is_ssl()?true:false; 
    $opt = [
        'expires' => $expire,
        'path' => $path,
        'domain' => $domain,
        'secure' => $bool,
        'httponly' => $bool,
        'samesite' => 'None',
    ];
    if(is_local()){
        unset($opt['samesite']);
    }
    setcookie($name, $value, $opt);  
    $_COOKIE[$name] = $value;
}
/**
 * 删除COOKIE 
 */
function remove_cookie($name)
{
    cookie($name,'',time()-100); 
} 

/**
 * 时间区间
 */
if (!function_exists('date_limit')) {
    function date_limit()
    {
        $max = date("Y",time()+86400*365*100);
        return ' min="1900-01-01" max="'.$max.'-12-31"';
    }
}

/**
 * 路径列表，支持文件夹下的子所有文件夹
 *
 * @param string $path
 * @version 1.0.0
 * @author sun <sunkangchina@163.com>
 * @return void
 */
function get_deep_dir($path)
{
    $arr = array();
    $arr[] = $path;
    if (is_file($path)) {
    } else {
        if (is_dir($path)) {
            $data = scandir($path);
            if (!empty($data)) {
                foreach ($data as $value) {
                    if ($value != '.' && $value != '..') {
                        $sub_path = $path . "/" . $value;
                        $temp = get_deep_dir($sub_path);
                        $arr  = array_merge($temp, $arr);
                    }
                }
            }
        }
    }
    return $arr;
}

if (!function_exists('el_size')) {
    function el_size()
    {
        return "medium";
    }
}

/**
 * 显示2位小数
 * @version 1.0.0
 * @author sun <sunkangchina@163.com>
 */
function price_format($yuan,$dot = 2)
{
    return bcmul($yuan, 1 ,$dot);
} 

/**
 * 返回错误信息，JSON格式 
 */
function json_error($arr = [])
{
    $arr['code'] = $arr['code'] ?: 250;
    $arr['type'] = $arr['type'] ?: 'error';
    json($arr);
}
/**
 * 返回成功信息，JSON格式 
 */
function json_success($arr = [])
{
    $arr['code'] = $arr['code'] ?: 0;
    $arr['type'] = $arr['type'] ?: 'success';
    json($arr);
} 

/**
 * yaml转数组
 *
 * @param string $str
 * @version 1.0.0
 * @author sun <sunkangchina@163.com>
 * @return array
 */
function yaml_load($str)
{
    return Symfony\Component\Yaml\Yaml::parse($str);
}
/**
 * 数组转yaml
 *
 * @param array $array
 * @param integer $line
 * @version 1.0.0
 * @author sun <sunkangchina@163.com>
 * @return string
 */
function yaml_dump($array, $line = 3)
{
    return Symfony\Component\Yaml\Yaml::dump($array, $line);
}
/**
 * yaml转数组，数组转yaml格式
 *
 * @param string $str
 * @version 1.0.0
 * @author sun <sunkangchina@163.com>
 * @return string|array
 */
function yaml($str)
{
    if (is_string($str)) {
        return yaml_load($str);
    } else {
        return yaml_dump($str);
    }
}

/**
 * 验证数据
 * https://github.com/vlucas/valitron
 * 
 * 事例代码
 * 
$data    = g();   
$vali    = validate([
    'company_title'   => '客户名',
    'email'   => '邮件地址',
    'active_plugins'  => '系统',
    'exp_time' => '过期时间',
],$data,[
    'required' => [
        ['company_title'],
        ['email'],
        ['active_plugins'],
        ['exp_time'],
    ],
    'email'=>[
        ['email']
    ]
]);
if($vali){
    json($vali);
} 

 * 更多规则 

required - Field is required
requiredWith - Field is required if any other fields are present
requiredWithout - Field is required if any other fields are NOT present
equals - Field must match another field (email/password confirmation)
different - Field must be different than another field
accepted - Checkbox or Radio must be accepted (yes, on, 1, true)
numeric - Must be numeric
integer - Must be integer number
boolean - Must be boolean
array - Must be array
length - String must be certain length
lengthBetween - String must be between given lengths
lengthMin - String must be greater than given length
lengthMax - String must be less than given length
min - Minimum
max - Maximum
listContains - Performs in_array check on given array values (the other way round than in)
in - Performs in_array check on given array values
notIn - Negation of in rule (not in array of values)
ip - Valid IP address
ipv4 - Valid IP v4 address
ipv6 - Valid IP v6 address
email - Valid email address
emailDNS - Valid email address with active DNS record
url - Valid URL
urlActive - Valid URL with active DNS record
alpha - Alphabetic characters only
alphaNum - Alphabetic and numeric characters only
ascii - ASCII characters only
slug - URL slug characters (a-z, 0-9, -, _)
regex - Field matches given regex pattern
date - Field is a valid date
dateFormat - Field is a valid date in the given format
dateBefore - Field is a valid date and is before the given date
dateAfter - Field is a valid date and is after the given date
contains - Field is a string and contains the given string
subset - Field is an array or a scalar and all elements are contained in the given array
containsUnique - Field is an array and contains unique values
creditCard - Field is a valid credit card number
instanceOf - Field contains an instance of the given class
optional - Value does not need to be included in data array. If it is however, it must pass validation.
arrayHasKeys - Field is an array and contains all specified keys.
 */
function validate($labels, $data, $rules, $show_array = false)
{
    $v = new \lib\Validate($data);
    $v->rules($rules);
    $v->labels($labels);
    $v->validate();
    $error = $v->errors();
    if ($error) {
        if (!$show_array) {
            foreach ($error as $k => $v) {
                $error = $v[0];
                break;
            }
        }
        return ['code' => 250, 'msg' => $error, 'type' => 'error'];
    } else {
        return;
    }
}
/**
 * 取文件信息
 */
function get_file($id)
{
    static $obj;
    $key = $id;
    if (is_array($id)) {
        $key = md5(json_encode($id));
    }
    $data = $obj[$key];
    if ($data) {
        return $data;
    }
    $f = db_get("upload", "*", ['id' => $id]);
    //上传成功后
    do_action("upload.after", $f);
    $obj[$key] = $f;
    return $f;
} 
/**
 * 获取主题 
 */
function get_theme()
{
    global $config;
    return $config['theme_front'] ?: 'default';
}
/**
 * 加载theme下文件 
 */
function view($name, $params = [])
{
    //访问文件被重复加载
    static $_view_loaded;
    $key = md5($name.json_encode($params));
    if($_view_loaded[$key]){return;}
    $_view_loaded[$key]  = true;
    $dir = PATH . 'theme/';
    $theme = get_theme();
    if ($theme != 'default') {
        $default_theme = 'default';
    }
    if (strpos($name, "@") !== false) {
        $arr  = explode("@", $name);
        $name = $arr[1];
        $file_3 = PATH . 'plugins/' . $arr[0] . '/' . $name . '.php';
        $name = $arr[0] . '/' . $name;
    }

    $file_1 = $dir . $theme . '/' . $name . '.php';
    $file_2 = $dir . $default_theme . '/' . $name . '.php';
    if ($params) {
        extract($params);
    }
    if (file_exists($file_1)) {
        include $file_1;
    } else if (file_exists($file_2)) {
        include $file_2;
    } else if (file_exists($file_3)) {
        include $file_3;
    }
}
/**
 * 创建或更新用户
 */
function admin_user($user, $pwd, $tag)
{
    $find = db_get_one('user', '*', ['user' => $user]);
    if (!$find) {
        if ($user && $pwd) {
            $id = db_insert('user', [
                'user'  => $user,
                'pwd'   => md5($pwd),
                'tag'   => $tag,
                'created_at' => now()
            ]);
        }
    } else {
        $id = $find['id'];
        if ($pwd) {
            db_update('user', ['pwd' => md5($pwd)], ['id' => $id]);
        }
    }
    return $id;
}

/**
 * 设置配置
 */
function set_config($title, $body)
{
    $one = db_get_one("config", "*", ['title' => $title]);
    if (is_array($body)) {
        
    }
    if (!$one) {
        db_insert("config", ['title' => $title, 'body' => $body]);
    } else {
        db_update("config", ['body' => $body], ['id' => $one['id']]);
    }
}
/**
 * 优先取数据库，未找到后取配置文件
 */
function get_config($title)
{
    global $config;
    if (is_array($title)) {
        $list = [];
        $all  = db_get("config", "*", ['title' => $title]);
        foreach ($all as $one) {
            $body = $one['body']; 
            $list[$one['title']] = $body ?: $config[$one['title']];
        }
        return $list;
    } else {
        $one  = db_get_one("config", "*", ['title' => $title]);
        $body = $one['body'];
        if (!$body) {
            return $config[$title];
        } 
        return $body; 
    }
}


/**
 * elementui table序号
 * methods中的方法，参考 /modules/user/user.php
 * <?= element_index_method() ?>,
 * <el-table-column type="index" label="序号" :index="indexMethod" width="50">
    </el-table-column>
 * @return string
 */
function element_index_method()
{
    return 'indexMethod(index) {
        let per_page = this.per_page||0;
        let cpage = this.where.page || 1;
        return (cpage - 1) * per_page + index + 1;
    }';
}
 
/**
 * 每页显示多少条记录 
 */
function page_size($name)
{
    $key  = 'page_size_' . $name;
    $time = time() + 86400 * 365 * 10;
    $size = cookie($key);
    if (get_post('page_size')) {
        $size = (int)get_post('page_size');
        cookie($key, $size, $time);
        $size = cookie($key);
    }
    return $size ?: 20;
}

/**
 * 显示下拉选择分页每页显示多少条 
 */
function page_size_array()
{
    $defaults =  [
        20, 50, 100, 500, 1000
    ];
    return $defaults;
}
if (!function_exists('json')) {
    function json($data)
    {
        echo json_encode($data);
        exit;
    }
}

/**
 * 前台主题url
 */
function theme_url()
{
    return "/theme/" . cookie('front_theme') . '/';
}
/**
 * 设置前台主题
 */
function set_theme($name)
{
    cookie("front_theme", $name, time() + 86400 * 350 * 10);
}
/*
* 根据请求设置主题
*/
if ($_GET['_theme']) {
    set_theme($_GET['_theme']);
}
/**
 * 设置后台主题
 */
function set_admin_theme($name)
{
    cookie("admin_theme", $name);
}

/**
 * 后台主题url
 */
function admin_theme_url()
{
    return "/theme/" . cookie('admin_theme') . '/';
}
/**
 * AES加密
// aes 加密
$config['aes_key'] = "123456";
$config['aes_iv']  = md5('app_sun');


$token = urlencode(aes_encode($d)); 
 */
function aes_encode($data, $key = '', $iv = '', $type = 'AES-128-CBC', $options = '')
{
    global $config;
    if (!$key) {
        $key = $config['aes_key'];
    }
    if (!$iv) {
        $iv  = $config['aes_iv'];
    }
    $obj = new \lib\Aes($key, $iv, $type, $options);
    return base64_encode($obj->encrypt($data));
}
/**
 * AES解密 

$token = $_GET['token']; 
$token = aes_decode($token);
pr($token);

 */
function aes_decode($data, $key = '', $iv = '', $type = 'AES-128-CBC', $options = '')
{
    global $config;
    if (!$key) {
        $key = $config['aes_key'];
    }
    if (!$iv) {
        $iv  = $config['aes_iv'];
    }
    $data = base64_decode($data);
    $obj = new \lib\Aes($key, $iv, $type, $options);
    return $obj->decrypt($data);
}

function el_page_sizes()
{
    $arr = page_size_array();
    return json_encode($arr);
} 
/**
return [
    'welcome' => '你好{name}', 
];
<?= lang('welcome',['name'=>'test'])?>
 */
function lang($name, $val = [], $pre = 'app')
{
    return lib\Lang::trans($name, $val, $pre);
}
 
/**
 * 搜索替换\n , ，空格
 * @param string $name
 * @version 1.0.0
 * @author sun <sunkangchina@163.com>
 * @return array
 */
function string_to_array($name,$array = '')
{
    if (!$name) {
        return [];
    }
    $array = $array?:[
        "\n",
        "，",
        "、",
        "|",
        ",",
        chr(10),
    ];
    foreach ($array as $str) {
        if (strpos($name, $str) !== false) {
            $name = str_replace($str, ',', $name);
        }
    }
    if (strpos($name, ",") !== false) {
        $arr = explode(",", $name);
    }
    if ($arr) {
        $arr = array_filter($arr);
        foreach ($arr as $k => $v) {
            if (!is_array($v)) {
                $arr[$k] = trim($v);
            } else {
                $arr[$k] = $v;
            }
        }
    } else {
        $arr = [trim($name)];
    }
    return $arr;
}


/**
 * 返回两个时间点间的日期数组
 *
 * @param string $start 时间格式 Y-m-d
 * @param string $end   时间格式 Y-m-d
 * @return void
 */
function get_dates($start, $end)
{
    $dt_start = strtotime($start);
    $dt_end   = strtotime($end);
    while ($dt_start <= $dt_end) {
        $list[] = date('Y-m-d', $dt_start);
        $dt_start = strtotime('+1 day', $dt_start);
    }
    return $list;
}
/**
 * 当前时间是周几
 */
function get_date_china($date)
{
    $weekarray = array("日", "一", "二", "三", "四", "五", "六");
    return $weekarray[date("w", strtotime($date))];
}


/**
 * 多少时间之前
 */
function timeago($time)
{
    if (strpos($time, '-') !== false) {
        $time = strtotime($time);
    }
    $rtime = date("m-d H:i", $time);
    $top   = date("Y-m-d H:i", $time);
    $htime = date("H:i", $time);
    $time  = time() - $time;
    if ($time < 60) {
        $str = '刚刚';
    } elseif ($time < 60 * 60) {
        $min = floor($time / 60);
        $str = $min . '分钟前';
    } elseif ($time < 60 * 60 * 24) {
        $h   = floor($time / (60 * 60));
        $str = $h . '小时前 ' . $htime;
    } elseif ($time < 60 * 60 * 24 * 3) {
        $d = floor($time / (60 * 60 * 24));
        if ($d == 1) {
            $str = '昨天 ' . $rtime;
        } else {
            $str = '前天 ' . $rtime;
        }
    } else {
        $str = $top;
    }
    return $str;
}

/**
 * 请求是否是AJAX
 */
function is_ajax()
{
    if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") {
        return true;
    } else {
        return false;
    }
}


/**
 * 防止重复执行
 */
function cli_prevent_duplication($argv, $cmd = 'php cmd.php')
{
    $cmd_line = "php";
    $str = '';
    foreach ($argv as $v) {
        $str .= " " . $v;
    }
    $cmd_line = $cmd_line . $str;
    exec("ps aux|grep '" . $cmd_line . "'", $arr);
    $list = [];
    foreach ($arr as $v) {
        if ($v) {
            $v = str_replace('  ', '', $v);
            preg_match('(' . $cmd . '.*)', $v, $output);
            $new = $output[0];
            if ($new) {
                $list[] = trim($new);
            }
        }
    }
    $new_list = [];
    foreach ($list as $v => $k) {
        if (!$new_list[$k]) {
            $new_list[$k] = 1;
        } else {
            $new_list[$k]++;
        }
    }
    if ($new_list && $new_list[$cmd_line] > 2) {
        echo "程序已在运行，不能重复执行！\n";
        exit();
    }
}
 
/**
 * 包含文件 
 */
function import($file, $vars = [], $check_vars = false)
{
    static $obj;
    $key = md5(str_replace('\\', '/', $file));
    if ($vars && $check_vars) {
        $md5 = md5(json_encode($vars));
        $key = $key . $md5;
    }
    if ($vars) {
        extract($vars);
    }
    if (!isset($obj[$key])) {
        if (file_exists($file)) {
            include $file;
            $obj[$key] = true;
            return true;
        } else {
            return false;
        }
    } else {
        return true;
    }
}

/**
 * 生成表单TOKEN，防止重复提交
<input type="hidden" name="form_token" value="<?=create_form_token()?>">
 */
function create_form_token()
{
    $salt = "ken.2022";
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    $form_token = $_SESSION['form_token'];
    if ($form_token) {
        return $form_token;
    }
    return $_SESSION['form_token'] = md5(mt_rand(1, 1000000) . $salt);
}
/**
 * 检测表单TOKEN

//检测form_token
check_form_token(g('form_token'));
 */
function check_form_token($token = '')
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    $token = $token ?: g('form_token');
    $session_token = $_SESSION['form_token'];
    if ($session_token && $session_token == $token) {
        //unset($_SESSION['form_token']);
    } else {
        json_error(['msg' => '请求已过期，请刷新当前页面！']);
    }
}
/**
 * 检测reffer是否正常，如异常返回JSON
 */
function check_reffer_with_json($allow_domain = [], $is_root = true)
{
    $flag = check_reffer($allow_domain, $is_root);
    if (!$flag) {
        json_error(['msg' => '请求异常']);
    }
}
/**
 * 检测reffer
 */
function check_reffer($allow_domain = [], $is_root = true)
{
    if (!$allow_domain) {
        $allow_domain[] = $_SERVER['HTTP_REFERER'];
    }
    $root = [];
    foreach ($allow_domain as $v) {
        $root[] = get_root_domain($v);
    }
    $refer = get_reffer($refer);
    if ($is_root) {
        $refer = get_root_domain($refer);
    }
    if (in_array($refer, $root)) {
        return true;
    }
    return false;
}
/**
 * 取reffer
 */
function get_reffer($refer = '')
{
    $refer = $refer ?: $_SERVER['HTTP_REFERER'];
    $refer = str_replace("http://", '', $refer);
    $refer = str_replace("https://", '', $refer);
    $refer = str_replace("/", '', $refer);
    return $refer;
}
/**
 * 取主域名，如 admin.baidu.com返回baidu.com
 */
function get_root_domain($host = '')
{
    $host = $host ?: host();
    preg_match("#\.(.*)#i", $host, $match);
    $host = $match[1];
    return str_replace("/", '', $host);
}
/**
 * 取子域名，如admin.baidu.com返回admin
 */
function get_sub_domain($host = '')
{
    $host = $host ?: host();
    preg_match("#(http://|https://)(.*?)\.#i", $host, $match);
    $host = $match[2];
    return str_replace("/", '', $host);
}

function admin_header()
{
    include PATH . ADMIN_DIR_NAME . '/header.php';
}

function admin_footer()
{
    include PATH . ADMIN_DIR_NAME . '/footer.php';
} 

/**
 * 格式化金额
 */
function format_money($money, $len = 2, $sign = '￥')
{
  $negative = $money >= 0 ? '' : '-';
  $int_money = intval(abs($money));
  $len = intval(abs($len));
  $decimal = ''; //小数
  if ($len > 0) {
    $decimal = '.' . substr(sprintf('%01.' . $len . 'f', $money), -$len);
  }
  $tmp_money = strrev($int_money);
  $strlen = strlen($tmp_money);
  $format_money = '';
  for ($i = 3; $i < $strlen; $i += 3) {
    $format_money .= substr($tmp_money, 0, 3) . ',';
    $tmp_money = substr($tmp_money, 3);
  }
  $format_money .= $tmp_money;
  $format_money = strrev($format_money);
  return $sign . $negative . $format_money . $decimal;
}

/**
* 生成签名链接
*/
function create_sample_sign_url($arr = []){
    $arr['time'] = time();
    $arr['sign'] = urlencode(aes_encode(json_encode($arr)));
    unset($arr['time']); 
    return http_build_query($arr);
}
/**
* 验证签名链接是否可用
*/
function verify_sample_sign_url($exp_time = 60){
    $sign = g('sign');
    if(!$sign){
        return false;
    }
    $arr = json_decode(aes_decode($sign),true); 
    $flag = false; 
    if($arr && $arr['time'] > time()-$exp_time){
        $flag = true;
    } 
    if($flag){
       return true;
    }else{
       return false;
    }
}


/**
 * 检查签名防止篡改
 */
function signature_checker(){
    $_signature = $_POST['_signature'];
    unset($_POST['_signature']); 
    $sign = sign_by_secret($_POST,'',true,true);
    if($_signature != $sign){
        json_error(['msg'=>'签名错误'.$sign]);
    } 
}
/**
* 生成签名
签名生成的通用步骤如下：
第一步：将参与签名的参数按照键值(key)进行字典排序
第二步：将排序过后的参数，进行key和value字符串拼接
第三步：将拼接后的字符串首尾加上app_secret秘钥，合成签名字符串
第四步：对签名字符串进行MD5加密，生成32位的字符串
第五步：将签名生成的32位字符串转换为大写 
*/
function sign_by_secret($params,$secret='',$array_encode = false,$encodeURIComponent = false){
    if(!$secret){
        $secret = get_config('sign_secret')?:'TheCoreFun2022';
    }
    $str = ''; 
    //将参与签名的参数按照键值(key)进行字典排序
    ksort($params); 
    foreach ($params as $k => $v) { 
        //将排序过后的参数，进行key和value字符串拼接
        if(is_array($v) && $array_encode){
            $v = json_encode($v,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
        }
        if($encodeURIComponent){
            $v = encodeURIComponent($v);    
        }        
        $str .= "$k=$v";
    } 
    //将拼接后的字符串首尾加上app_secret秘钥，合成签名字符串
    $str .= $secret;  
    //对签名字符串进行MD5加密，生成32位的字符串
    $str = md5($str);
    //将签名生成的32位字符串转换为大写
    return strtoupper($str);
}

/**
 * 与JS的encodeURIComponent功能相同
 */ 
function encodeURIComponent($str)
{
    $revert = array('%21' => '!', '%2A' => '*', '%27' => "'", '%28' => '(', '%29' => ')');
    return strtr(rawurlencode($str), $revert);
}


/**
 * 多语言
 */
function set_lang($lang = 'zh-cn')
{
    lib\Lang::set($lang);
    lib\Validate::lang($lang);
}

/**
 * 生成URL
 */
function create_url($url){
    $host = host();
    if(substr($url,0,1) == '/'){
        $url = substr($url,1);
    }
    return $host.$url;
}

/***
 * 页面BLOCK实现
 */
global $_core_block;
global $_core_block_name;
/**
 * 清空BLOCK
 */
function block_clean(){
    global $_core_block;
    $_core_block = [];
}
/**
 * BLOCK开始
 */
function block_start($name){
    global $_core_block;
    global $_core_block_name;
    ob_start();
    $_core_block_name = $name;
}
/**
 * BLOCK结束
 */
function block_end(){
    global $_core_block;
    global $_core_block_name;
    $content = ob_get_contents();
    ob_end_clean();
    return $_core_block[$_core_block_name] = trim($content);
}
/**
 * 获取BLOCK
 */
function get_block($name = ''){
    global $_core_block; 
    if($name){
        return $_core_block[$name];
    }else{
        return $_core_block;
    } 
}

/**
* 处理ZIP
*/
/**
* 所本地文件解压到指定目录
*/
function zip_extract($local_file,$extract_local_dir){ 
    if(strpos($local_file,'/uploads/') !== false && strpos($local_file,'://') !== false){
        $local_file = PATH.substr($local_file,strpos($local_file,'/uploads/')+1); 
    }
    if(!file_exists($local_file)){return false;}
    $zippy = Alchemy\Zippy\Zippy::load();
    $archive = $zippy->open($local_file);
    if(!is_dir($extract_local_dir)){
        create_dir_if_not_exists([$extract_local_dir]);
    }
    $archive->extract($extract_local_dir);
}
/**
* 生成ZIP
* @param $local_zip_file 本地zip文件
* @param $files 包含的文件
*/
function zip_create($local_zip_file,$files = []){ 
    $dir = get_dir($local_zip_file);
    if(!is_dir($dir)){
        create_dir_if_not_exists([$dir]);
    } 
    $zippy = Alchemy\Zippy\Zippy::load();
    $archive = $zippy->create($local_zip_file, $files, true);
    return str_replace(PATH,'',$local_zip_file);
}



include __DIR__ . '/yii1-javascript-encode.php';
include __DIR__ . '/inc/jquery.php'; 
include __DIR__ . '/inc/vue.php'; 
