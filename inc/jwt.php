<?php 
/**
 * 
 * @license read license.txt
 * @author sun <sunkangchina@163.com>
 * @copyright (c) 2021 
 */
use Firebase\JWT\JWT as Firebase_JWT;
 
/**
* 返回接口AUTHORIZATION解密后数组
* 返回{user_id:'',time:int}
*/
function api($show_error = true){
	static $_data;
	if(!$_data){
		$_data = get_author(null,false,$show_error);	
		if(is_json($_data)){
			exit;
		}
	}
	return $_data;
}
/**
* 接口是否是管理员
*/
function api_admin(){
	$arr = api();
	if(!$arr['is_admin']){
		json_error(['msg'=>'403']);
	}
}

// 解析 HTTP_AUTHORIZATION
function get_author($sign = null,$ignore_time_check = false,$show_error = true){
	global $config; 
	if(!$sign){
		$sign  = $_SERVER['HTTP_AUTHORIZATION'];   	
	}	
	if(!$sign){
		$error = 'Sign Error'; 
	} 
	$key = $config['jwt_key'];
	if(!$key){ 
		$error = 'jwt_key参数有问题'; 
	}   
	if(get_post('sign')){
	    $sign = get_post('sign');
	}
	$jwt  = Jwt::decode($sign);    
	if(!$jwt->time){
		 $error = 'time参数有问题';
	} 
	$exp = (int)$c['exp'];
	if($exp <= 0){
		$exp = 3600;
	}
	if(!$ignore_time_check && $jwt->time + $exp < time() ){
		$error = '请求已过期';
	}
	if($jwt->user_id){
		
	}else{
		$error = '用户未登录';
	}
	if($error){
		if($show_error){
			json_error(['code'=>205,'msg'=>$error]);		
		}		
	}	
	return (array)$jwt;
}

/**
 * $s = Jwt::encode(['user_id'=>100,'t'=>['s'=>2]]);
 * pr(Jwt::decode($s));
 */
class Jwt{ 
    public static function encode($data,$key = null){
    	global $config;
        if(!$key){
            $key   = $config['jwt_key'];    
        }        
        $time     = time();
        $exp_time = time()+10;
        $payload  = array(  
            "iat" => $time,
            "nbf" => $time,
            "exp" => $exp_time,
        )+$data; 
        $jwt = Firebase_JWT::encode($payload, $key);
        return base64_encode($jwt);
    }

    public static function decode($value,$key = null)
    {
    	global $config;
        $value   = base64_decode($value);
        if(!$key){
            $key     = $config['jwt_key'];     
        }        
        try {
            $arr     = Firebase_JWT::decode($value, $key, array('HS256'));
            return $arr;
        } catch (\Exception $e) { 
        } 
    }

}