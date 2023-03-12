<?php

/*
    Copyright (c) 2021-2031, All rights reserved.
    This is NOT a freeware
    LICENSE: https://github.com/thefunpower/core/blob/main/LICENSE.md 
    Connect Email: sunkangchina@163.com 
*/ 
/**
* 添加xss过滤
*/
function get_xss_clean_ins(){
    static $antiXss;
    if(!$antiXss){
        $antiXss = new voku\helper\AntiXSS();    
    }
    return $antiXss; 
}
/**
* 防止xss
*/
function xss_clean_str($str){ 
    $ins = get_xss_clean_ins();
    do_action("xss_clean",$ins);
    return $ins->xss_clean($str); 
}
/**
* 防止xss
*/
function xss_clean($input){ 
    if(is_array($input)){
        foreach($input as $k=>&$v){
            if($v && is_string($v)){
                $v = xss_clean_str($v);
            } 
        }
        return $input;
    }else if($input && is_string($input)){
        return xss_clean_str($input);
    }
    return $input;
}
/**
 * 有\'内容转成'显示，与addslashes相反 stripslashes
 */ 
/**
 * 对 GET POST COOKIE REQUEST请求字段串去除首尾空格
 */
if (!function_exists('global_trim')) {
    function global_trim()
    {
        $in = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
        foreach ($in as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $key => $val) {
                    if ($val && !is_array($val)) { 
                        $val = trim($val);
                    }
                    $in[$k][$key] = $val;
                    $in[] = &$in[$k][$key];
                }
            }
        }
    }
    global_trim();
}
/**
 * 取GET
 */
function get($key = "")
{
    if ($key) {
        return $_GET[$key];
    }
    return $_GET;
}
/**
 * 取POST
 */
function post($key = "")
{
    return get_post($key);
}
/**
 * 取POST值
 */
function get_post($key = "")
{
    $input = get_input();
    $data  = $_POST;
    if ($key) {
        $val = $data[$key];
        if ($input && is_array($input) && !$val) {
            $val = $input[$key];
        }
        return $val;
    } else {
        return $data ?: $input;
    }
}

if (!function_exists('g')) {
    function g($key = null)
    {
        $val = get_post($key);
        if (!$val) {
            $val = $key?$_GET[$key]:$_GET;
        }
        global $config;
        if($config['xss_clean'] == true){
            $val = xss_clean($val);
        }
        return $val;
    }
}

/**
 * 取php://input值
 */
function get_input()
{
    $data = file_get_contents("php://input");
    if (is_json($data)) {
        return json_decode($data, true);
    }
    return $data;
}
/**
 * 取当前URL完整地址 
 */
function get_full_url($with_http = false){
    $url = $_SERVER['REQUEST_URI'];
    if($with_http){
        $url = host().substr($url,1);
    } 
    return $url; 
}

/**
 * CURL请求
 * 
GET
$client = guzzle_http();
$res    = $client->request('GET', $url);
return (string)$res->getBody();  

PUT
$body = file_get_contents($local_file);  
$request = new \GuzzleHttp\Psr7\Request('PUT', $upload_url, $headers=[], $body);
$response = $client->send($request, ['timeout' => 30]);
if($response->getStatusCode() == 200){
    return true;
} 

POST
$res    = $client->request('POST', $url,['body'=>]);


return (string)$res->getBody();  

JSON

$res = $client->request('POST', '/json.php', [
    'json' => ['foo' => 'bar']
]);

发送application/x-www-form-urlencoded POST请求需要你传入form_params

$res = $client->request('POST', $url, [
    'form_params' => [
        'field_name' => 'abc',
        'other_field' => '123',
        'nested_field' => [
            'nested' => 'hello'
        ]
    ]
]);

 * 
 */
function guzzle_http($click_option = [])
{
    $click_option['timeout'] = $click_option['timeout'] ?: 60;
    $client = new \GuzzleHttp\Client($click_option);
    return $client;
}
/*
$opt = guzzle_http_fake_option();
$opt['referer'] = '';
$opt['form_params'] = [
    'kw'  => $kw,
    'page'=> $page,
];
guzzle_http()->request('POST', $url, $opt);
$res = (string)$res->getBody();;

*/
function guzzle_http_fake_option(){
    $data = array(
      119.120.'.'.rand(1,255).'.'.rand(1,255),
      124.174.'.'.rand(1,255).'.'.rand(1,255),
      116.249.'.'.rand(1,255).'.'.rand(1,255),
      118.125.'.'.rand(1,255).'.'.rand(1,255),
      42.175.'.'.rand(1,255).'.'.rand(1,255),
      124.162.'.'.rand(1,255).'.'.rand(1,255),
      211.167.'.'.rand(1,255).'.'.rand(1,255),
      58.206.'.'.rand(1,255).'.'.rand(1,255),
      117.24.'.'.rand(1,255).'.'.rand(1,255),
      47.101.'.'.rand(1,255).'.'.rand(1,255),
    );
    $agentArray=[ 
      "safari 5.1 – MAC"=>"Mozilla/5.0 (Windows NT 6.1) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.57 Safari/536.11",
      "safari 5.1 – Windows"=>"Mozilla/5.0 (Windows; U; Windows NT 6.1; en-us) AppleWebKit/534.50 (KHTML, like Gecko) Version/5.1 Safari/534.50",
      "Firefox 38esr"=>"Mozilla/5.0 (Windows NT 10.0; WOW64; rv:38.0) Gecko/20100101 Firefox/38.0",
      "IE 11"=>"Mozilla/5.0 (Windows NT 10.0; WOW64; Trident/7.0; .NET4.0C; .NET4.0E; .NET CLR 2.0.50727; .NET CLR 3.0.30729; .NET CLR 3.5.30729; InfoPath.3; rv:11.0) like Gecko",
      "IE 9.0"=>"Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0",
      "IE 8.0"=>"Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0)",
      "IE 7.0"=>"Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0)",
      "IE 6.0"=>"Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)",
      "Firefox 4.0.1 – MAC"=>"Mozilla/5.0 (Macintosh; Intel Mac OS X 10.6; rv:2.0.1) Gecko/20100101 Firefox/4.0.1",
      "Firefox 4.0.1 – Windows"=>"Mozilla/5.0 (Windows NT 6.1; rv:2.0.1) Gecko/20100101 Firefox/4.0.1",
      "Opera 11.11 – MAC"=>"Opera/9.80 (Macintosh; Intel Mac OS X 10.6.8; U; en) Presto/2.8.131 Version/11.11",
      "Opera 11.11 – Windows"=>"Opera/9.80 (Windows NT 6.1; U; en) Presto/2.8.131 Version/11.11",
      "Chrome 17.0 – MAC"=>"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_0) AppleWebKit/535.11 (KHTML, like Gecko) Chrome/17.0.963.56 Safari/535.11",
      "傲游（Maxthon）"=>"Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; Maxthon 2.0)",
      "腾讯TT"=>"Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; TencentTraveler 4.0)",
      "世界之窗（The World） 2.x"=>"Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1)",
      "世界之窗（The World） 3.x"=>"Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; The World)",
      "360浏览器"=>"Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; 360SE)",
      "搜狗浏览器 1.x"=>"Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; Trident/4.0; SE 2.X MetaSr 1.0; SE 2.X MetaSr 1.0; .NET CLR 2.0.50727; SE 2.X MetaSr 1.0)",
      "Avant"=>"Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; Avant Browser)",
      "Green Browser"=>"Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1)",
    ]; 
    $ip = $data[array_rand($data)];
    $userAgent=$agentArray[array_rand($agentArray,1)];  //随机浏览器userAgent
    $headers = [
        'CLIENT-IP'=>$ip,
        'REMOTE-ADDR'=>$ip,
        'X-FORWARDED-FOR'=>$ip,
        //'referer'=> '',
        //'Cookie' => ,
        'CURLOPT-USERAGENT'=>$userAgent
    ];   
    $jar = new \GuzzleHttp\Cookie\CookieJar();
    return [
        'headers'=>$headers,
        'cookies'=>$cookies,
    ];
}   


