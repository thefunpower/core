### Core

代码不复杂，请仔细阅读源码

https://www.yuque.com/sunkangchina/juhe/phper


### 开发您自己的项目，也可以使用以下开源框架 

|  框架   | 网址  |
|  ----  | ----  |
| Yii  | https://www.yiiframework.com/ |
| ThinkPHP | https://www.thinkphp.cn/ |
| CodeignIter  | https://codeigniter.com/ |
| Laravel  | https://laravel.com/ |
| FuelPHP  | https://www.fuelphp.com/ |
| Nette  | https://www.fuelphp.com/ |
| CakePHP  | https://cakephp.org/ |
| Phalcon  | https://phalcon.io/zh-cn |
| Yar  |  https://github.com/laruence/yar |
 
--------------------------------------------------------------------
以下内容可以不看，直接阅读源码吧！阅读源码！阅读源码！阅读源码！

-------------------------------------------------------------------- 


### 如使用 mongodb 请使用

~~~
composer require mongodb/mongodb
~~~

https://www.mongodb.com/docs/php-library/current/tutorial/crud/

 


### 插件forum中使用视图文件

router.php代码演示，对应的目录有两个：
1. /theme/default/forum/tpl/index.php
2. /plugins/forum/tpl/index.php

~~~
global $router; 
$router->get("/forum",function(){
    view("forum@tpl/index"); 
});

~~~ 

#### JWT 
*****
**一般会员**
接口调用以下函数。默认如果用户未登录会返回JSON错误。
~~~
api($show_error = true)
~~~
有时我们需要不抛出错误，使用以下形式
~~~
$api = api(false);
$user_id = $api['user_id'];
~~~

*****
**管理员**
**接口是否是管理员**
~~~
$api = api_admin();
~~~
要求请求中含有`is_admin`参数，且有值。



> 至于跨域问题，接口默认全部跨域。参考代码。无需自行添加。
~~~
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Credentials:true'); 
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
header('Access-Control-Allow-Methods: GET, POST, PUT,DELETE,OPTIONS,PATCH');
header('X-Powered-By: WAF/2.0');
if(isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'OPTIONS'){ 
    exit;
}
~~~




#### 一些内置函数 
命令行中，防止重复执行
~~~
if(!is_cli()){
	exit('RUN IN CLI');
}
set_time_limit(-1); 
ini_set('memory_limit','1024M');
cli_prevent_duplication($argv, $cmd = 'php io.php');
~~~
判断平台管理员是否登录，未登录自动跳到登录页
~~~
check_admin_login();
~~~ 
写日志
~~~
write_log($msg,$is_error = false)
~~~
生成条形码
~~~
$bar = new lib\Barcode;
echo $bar->display(date("YmdHis"));exit;
~~~
发送邮件,配置在 `config.ini.php`中。
~~~
\lib\Mail::send([
    'from'   => 'ddd@163.com',
    'to'     => $data['email'],
    'subject'=> "标题, 
    'html'   => "<p>内容</p>"
   ]);
~~~
取位置lat,lng
~~~
https://lbs.qq.com/dev/console/key/setting
lib\Map::tx('地址')  腾讯地图
lib\Map::gaode($address, $show_full = false)高德地图
~~~

最近几天
~~~
lib\Time::neerDays($num = 5,$separate = "")
~~~
返回最近几月
~~~
lib\Time::neerMonths($num = 5,$separate = "")
~~~
返回最近几年
~~~
lib\Time::neerYears($num = 5,$separate = "")
~~~
取今日、本周、本月、本年、昨日、上周、上月、上年
~~~
lib\Time::get($key, $date_format = false)
~~~
返回多少岁
~~~
lib\Time::age($bornUnix)
~~~
计算时间剩余　
~~~
lib\Time::less_time($a, $b = null)
~~~
 
返回图片信息
~~~
$file = PATH.'../demos/doc/1.jpg';  
$pdf = new \lib\Img($file); 
//取长宽

print_r($pdf->getWh());
//2是横版，1是竖版
print_r($pdf->getDimensionsType()); 
~~~
多少时间之前
~~~
timeago($time)
~~~

设置配置
~~~
set_config($title,$body)
~~~
优先取数据库，未找到后取配置文件
~~~
get_config($title)
~~~
XML 转成 数组
~~~
xml_to_array($xml)
~~~
数组转xml 
~~~
array_to_xml($arr)
~~~
AES加密
~~~
$config['aes_key'] = "123456";
$config['aes_iv']  = md5('app_sun');


$token = urlencode(aes_encode($d)); 
~~~
返回两个时间点间的日期数组
~~~
get_dates($start, $end)
~~~
当前时间是周几
~~~
get_current_week($date)
~~~
多语言翻译

~~~
lang('welcome',['name'=>'test'] )
~~~
配置文件在 `lang/zh-cn/app.php`中。
搜索替换\n , ，空格。 转成数组 
~~~
string_to_array($str)
~~~

AES解密
~~~
$token = $_GET['token']; 
$token = aes_decode($token);
pr($token);
~~~

根据用户id查用户信息
~~~
get_user($user_id)
~~~
查用户信息，where条件
~~~
get_user_where($where = [])
~~~

取用户扩展字段值
~~~
get_user_meta($user_id)
~~~
更新用户的meta信息
 ，参数$meta如  ['nickname'=>'']
~~~
set_user_meta($user_id,$meta)
~~~


获取HTTP_REFERER
~~~
referer($show_full = false)
~~~

当前域名 ，结尾带/
~~~
host()
~~~

取用户扩展字段值
~~~
get_user_meta_where($where = [],$return_row = false)
~~~


所有会员
~~~
get_user_all($where = [])
~~~


取单个用户组信息
~~~
user_group_get($group_id)
~~~

跳转
~~~
jump($url)
~~~

CDN地址,需要在`config.ini.php`配置` $config['cdn_url'] = [];`
~~~
static_url()
~~~

判断是命令行下
~~~
 is_cli()
~~~

判断是否为json 
~~~
is_json($str)
~~~

数组转对象
~~~
array_to_object($arr)
~~~

对象转数组
~~~
object_to_array($obj)
~~~

取目录名
~~~
get_dir($name)
~~~

取后缀，不包含`.`
~~~
get_ext($name)
~~~

取文件名
~~~
get_name($name)
~~~
创建目录 
~~~
create_dir($arr = [])
~~~
取IP
~~~
get_ip()
~~~
计算两点地理坐标之间的距离,返回公里数
~~~
get_distance($longitude1, $latitude1, $longitude2, $latitude2)
~~~
设置、获取cookie
~~~
cookie($name, $value = null, $expire = 0, $path = '/')
~~~
删除COOKIE 
~~~
remove_cookie($name)
~~~
返回  min="1900-01-01" max="2099-12-31"
~~~
date_limit()
~~~
路径列表，支持文件夹下的子所有文件夹
~~~
get_deep_dir($path)
~~~
返回成功信息，JSON格式 
~~~
json_success($arr = [])
~~~
返回错误信息，JSON格式 
~~~
json_error($arr = [])
~~~
yaml转数组，数组转yaml格式
~~~
yaml($str)
~~~
解析xlsx文件
~~~
$lists = lib\Xls::load($file, [
            '产品编号' => 'product_num',
            '产品规格' => 'name',
            '注册证号' => 'cert_num',
            '单位'     => 'unit',
        ],$column_use_date = []); 
~~~
`$column_use_date ` 指定哪几列是时间格式
xls生成
~~~
生成多个worksheet事例：
$where = [
    'status'    => 1,
    'date[>=]' => $start,
    'date[<=]' => $end,
];
$title  = [
      'invoice_number'   => '发票号码',
      'customer_name'    => '供应商',
      'product_num'      => '规格型号',
      'num'              => '数量',
      'price'            => '单价',
      'total_price'      => '金额',
]; 
//专票
$where['invoice_type'] = 1;
$values = invoice_set_xls_data($where,$title);
//普票
$where['invoice_type'] = 2;
$new_data = invoice_set_xls_data($where,$title);
//第一个worksheet
Xls::$label = '专票';
Xls::$sheet_width = [
    'A' => "15",
    'B' => "36",
    'C' => "30",
    'D' => "10",
    'E' => "10",
    'F' => "10",
];
//更多worksheet，如果只是一个，可不用下面代码，直接跳到Xls::create处
Xls::$works = [
    [
        'title' => $title,
        'label' => '普票',
        'data'  => $new_data,
        'width' => Xls::$sheet_width,
    ]
];
Xls::create($title, $values, $name, true);
~~~
验证数据
~~~
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
~~~
规则 
~~~
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
~~~

数组转tree 
~~~
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
array_to_tree($list, $pk = 'id', $pid = 'pid', $child = 'children', $root = 0, $my_id = '')
~~~

### 插件

在`plugins`目录创建`backrestoreapi`

info.php 代码

~~~
<?php 

return [
	'title'  => '为开发提供数据接口恢复',  
	'version'=> '1.0.1', 
	'level' => 100,
	'data'  => [
		'desc' => '',
		'price'=> '',
		'free' => 0, 
		'dependents'=> [
			 
		],
	]
];
~~~

include.php 代码

~~~
add_action("admin.menu",function(&$menu)
{
 	$menu['backrestoreapi'] = [
		'icon'=>'',
        'label'=>'网站开发接口恢复', 
 	];
 	$menu['backrestoreapi']['children'][] = [
 		'icon'=>'far fa-circle',
        'label'=>'应用',
        'url'=>'plugins/backrestoreapi/app.php', 
        'acl'=>[ 
        ]
 	];
});
~~~

### 自动退款、当使用pay插件时有用

refund_all.php

~~~
<?php 

include __DIR__.'/../app.php'; 

if(!is_cli()){
    echo "403";exit;
}
//防止重复执行 
cli_prevent_duplication($argv, $cmd = 'php refund_all.php');
echo "对所有支付进行退款\n";

$all = db_get("pay","*",['status[!]'=>-100]);
$pay = new plugins\pay\lib\WxPayBase(get_config('wx_appid')); 
if(!$all){
	echo "无需退款";
	exit;
}
foreach($all as $v){
	$out_trade_no = $v['out_trade_no'];
	$transaction_id = $v['transaction_id'];
	$total_fee = $v['total_fee'];
	$status = $v['status'];
	if($transaction_id && $total_fee){
		$r = $pay->refund($out_trade_no,$total_fee,$total_fee,$refund_desc = '退款');
		if($r['code'] == 0){
			echo "退款成功 ".$total_fee."\n";
			db_update("pay",['status'=>-100],['id'=>$v['id']]);
		}
	}
}
~~~

### 数据库

#### HOOk

~~~
//写入数据前
do_action("db_insert.$table.before",$data); 
//写入数据后
do_action("db_insert.$table.after",$id); 
//更新数据前
do_action("db_update.$table.before",$data); 
//更新数据后
do_action("db_update.$table.after",$data); 
//查寻数据
do_action("db_get_one.$table",$one);  
//删除数据前
do_action("db_insert.$table.del",$where); 
~~~


#### 数据库事务
~~~

db()->action(function() { 
    db_insert("aa",[  
        'amount'=>1,  
        'real_amount'=>1,  
        'order_num'=>1,  
        'invoicemoney_get_id'=>1,
        'type' => 'bank',
        'hexiao_status'=>1, 
        'created_at'=>now(), 
    ]);  
    db_insert("aa",[ 
        'real_amount'=>1,  
        'order_num'=>1,  
        'invoicemoney_get_id'=>1,
        'type' => 'bank',
        'hexiao_status'=>1, 
        'created_at'=>now(), 
    ]);  
    $err = db_get_error(); 
    if($err){ 
        return false;    
    } 
});
exit;
~~~
如果使用ThinkOrm，事务代码如下所示
~~~
use think\facade\Db;
Db::startTrans();
try { 
        Db::table("aa")->insert([  
            'amount'=>1,  
            'real_amount'=>1,  
            'order_num'=>1,  
            'invoicemoney_get_id'=>1,
            'type' => 'bank',
            'hexiao_status'=>1, 
            'created_at'=>now(), 
        ]);  
        Db::table("aa")->insert([ 
            'real_amount'=>1,  
            'order_num'=>1,  
            'invoicemoney_get_id'=>1,
            'type' => 'bank',
            'hexiao_status'=>1, 
            'created_at'=>now(), 
        ]);  
        // 提交事务
        Db::commit();  
} catch (\Exception $e) {
    pr($e->getMessage());
    Db::rollback();
} 
exit; 
~~~


**使用Medoo数据库操作类**

具体用法请查看。
https://medoo.in/

数据库配置在`config.ini.php`中。
在使用时可直接
~~~
db()->select(table,field,where)
~~~
如 https://medoo.in/api/select
不同的是这里`db()` 是已经初始化数据库对象了。
**读取表中的字段**
~~~ 
get_table_fields($table)
~~~
**返回数据库允许的数据，传入其他字段自动忽略**
此方法常用于过滤掉表不需要的字段。
~~~
$data = db_allow($table,$data);
~~~
显示数据库表结构，支持markdown格式
~~~
database_tables($name = null,$show_markdown = false)
~~~

**分页查寻**
~~~
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

~~~

**根据表名、字段 、条件 查寻多条记录** 
~~~
db_get($table, $join = "*", $columns=null, $where=null)
~~~

**写入记录**
~~~
db_insert($table, $data = [])
~~~

**更新记录**
~~~
db_update($table, $data = [], $where = [])
~~~
 

**根据表名、字段 、条件 查寻一条记录**
~~~
db_get_one($table, $join  = "*", $columns=null, $where=null)
~~~
**执行SQL**
~~~ 
db_query($sql,$raw=null)  //$raw有参数时为数组传值 
~~~
如
~~~
db_query("select * from user where user=:user",[":user"=>'admin'])
~~~ 

**取最小值**
~~~
db_get_min($table, $join  = "*", $column=null, $where=null)
~~~
**取最大值**
~~~
db_get_max($table, $join =  "*", $column = null, $where = null)
~~~
**总数**
~~~
db_get_count($table, $join =  "*", $column = null, $where = null)
~~~
是否有记录
~~~
db_get_has($table, $join = null, $where = null)
~~~
随机取多条记录
~~~
db_get_rand($table, $join= "*", $column=null, $where=null)
~~~
取总和
~~~
db_get_sum($table, $join="*", $column=null, $where=null)
~~~
取平均值
~~~
db_get_avg($table, $join="*", $column=null, $where=null)
~~~
删除
~~~
db_del($table, $where)
db_delete($table, $where)
~~~
显示所有表名
~~~
show_tables($table)
~~~
$where 条件：AND OR 查寻 https://medoo.in/api/where
~~~
 "AND" => [
    "OR" => [
        "user_name" => "foo",
        "email" => "foo@bar.com"
    ],
    "password" => "12345"
]
 ~~~
SQL：
~~~
WHERE (user_name = 'foo' OR email = 'foo@bar.com') AND password = '12345'
~~~
$where 条件：更复杂的AND OR 查寻
~~~
[
    "AND #Actually, this comment feature can be used on every AND and OR relativity condition" => [
        "OR #the first condition" => [
            "user_name" => "foo",
            "email" => "foo@bar.com"
        ],
        "OR #the second condition" => [
            "user_name" => "bar",
            "email" => "bar@foo.com"
        ]
    ]
]
~~~
SQL：
~~~
WHERE (
    ("user_name" = 'foo' OR "email" = 'foo@bar.com')
    AND
    ("user_name" = 'bar' OR "email" = 'bar@foo.com')
)
~~~
**连接新的数据库**
~~~
多数据库时有用
$config['db_host'] = '127.0.0.1';
//数据库名
$config['db_name'] = 'dbname';
//数据库登录用户名
$config['db_user'] = 'root';
//数据库登录密码
$config['db_pwd']  = '111111'; 
//数据库端口号
$config['db_port'] = 3306;
 
$new_db = new_db($config = []);
~~~
用返回的`$new_db `进行操作。
如
~~~
$new_db->insert(table,data);
~~~
用法与Medoo一样。

#### 缓存 

使用 `think\\Cache`

https://www.kancloud.cn/manual/thinkphp6_0/1037634

缓存配置,在`config.ini.php`中。
~~~
/缓存  file 或 redis
$config['cache_drive']  = 'file';
//文件缓存前缀
$config['cache_prefix'] = 'domain_'; 

//redis缓存配置，仅当cache_drive为redis时有效
$config['cache_redis']['host'] = "127.0.0.1";
$config['cache_redis']['port'] = 6379;
//redis缓存前缀
$config['cache_redis']['prefix'] = '';
~~~
**缓存设置|获取**
~~~
cache($key, $data = null, $second = null)
~~~
注册 `$data` 可以为数组或者对象，将自动转存为JSON，取值时JSON自动转数组。
简单说，`$data` 不要传JSON数据。
**缓存删除**
~~~
cache_delete($key)
~~~




### AES 加密解密，可用于接口数据提供给第三方使用。 

使用 `AES-128-ECB`方式。
~~~
aes_encode($data,$type='AES-128-ECB',$key='',$iv='')
aes_decode($data,$type='AES-128-ECB',$key='',$iv='')
~~~
**事例代码**

1.加密
~~~
$key = "516610f18f";
$iv    = "6fc5328948e9edd17d";
$title = "demo15";
$data  = [
     'title'=> $title,
     'time' => time(),
];
$data = json_encode($data);
echo urlencode(aes_encode($data,$type='AES-128-ECB',$key,$iv));
~~~

2.url参数形式解密
~~~
$sign  = $_GET['sign'];
$title = $_GET['title'];
if(!$sign){
    exit('Params error');
}   
//$res怎么来的，自己看着办
$secret_key = $res['secret_key'];
$secret_id  = $res['secret_id'];
$sign = urldecode($sign);
$arr  = aes_decode($sign,'AES-128-ECB',$secret_id,$secret_key);
$arr  = json_decode($arr,true);
if($arr['title']){
    if($arr['time'] < time()-300){
        exit('Access Deny');
    } 
   //这里就解密成功了。可以添加自己的逻辑。
  
}
exit('Access Deny');
~~~
 


#### action  

系统提供两个核心函数。
~~~
add_action  
do_action
~~~ 

添加HOOK
~~~
add_action("product",function(&$v){
	//购物车相关数据 start
    $cart_num     = plugins\cart\Core::get_num_by_proudct_id($v['id']); 
    $v['cart_num'] = (int)$cart_num?:0;     
});
~~~

执行HOOK

~~~
do_action("product",$v);
~~~


#### 取所有的action

~~~
<?php
include __DIR__.'/../app.php'; 

if(!is_cli()){
    echo "403";exit;
}
 
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
set_time_limit(0);
ignore_user_abort(true);
$f  = PATH.'/plugins';

$li = get_deep_dir($f);
$list = [];
foreach ($li as $key => $v) {
    if (get_ext($v) == 'php' 
    //    && strpos($v,'/api/') !== false
    ) {
        $list[] = $v;
    }
}  
$arr = cli_action_parse($list, 'do_action'); 
if ($arr) {
    $file = PATH . '/data/action.php';
    file_put_contents($file, "<?php return " . var_export($arr, true) . ";");
}
echo "生成action完成";


function cli_action_parse($li, $preg = 'do_action')
{
    $out = [];
    foreach ($li as $v) {
        $d = file_get_contents($v);
        if(!$d){
            continue;
        }
        preg_match_all('/\/\/(.*)\s*' . $preg . '(\(.*\))/', $d, $m);
        if ($m[1] && $m[2]) {
            foreach ($m[1] as $k => $desc) {
                $val = "";
                $val  = $m[2][$k];
                $val  = substr($val, 1, -1);
                $val  = str_replace("'", "", $val);
                if (strpos($val, ',') !== false) {
                    $arr = explode(',', $val);
                    $a   = $arr[0];
                    $b   = $arr[1];
                    $a = str_replace("\"", "", $a);
                    $array[] = [
                        'name'   => trim($desc),
                        'action' => trim($a),
                        'pars'   => $b,
                    ];
                } else {
                    $val = str_replace("\"", "", $val);
                    $array[] = [
                        'name'   => trim($desc),
                        'action' => trim($val),
                    ];
                }
                $total_lines++;
            }
        }
    }
    return $array;
}
~~~


 