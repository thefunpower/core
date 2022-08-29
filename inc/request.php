<?php

/**
 * 
 * @license read license.txt
 * @author sun <sunkangchina@163.com>
 * @copyright (c) 2021 
 */
if (!defined('VERSION')) {
    die();
}
/**
 * 有\'内容转成'显示，与addslashes相反
 */
function output_html($str)
{
    return stripslashes($str);
}
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
                    if ($v && !is_array($val)) {
                        //防止注入
                        $val = addslashes(trim($val));
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
            $val = $_GET[$key];
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

$res = $client->request('POST', '/form.php', [
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
