<?php 
/**
* 生成本地缩略图
* 安装oss插件可使用阿里云oss图片处理
* //支持aliyun、local 等
* $config['image_drive'] = 'aliyun';
*/
if(!function_exists("image_resize")){
    function image_resize($local_url,$w,$h = null){
        global $config;
        $drive = $config['image_drive']?:'local';
        $fun = "image_resize_".$drive; 
        if(function_exists($fun)){
            return $fun($local_url,$w,$h);
        }else{
            log_error("方法".$fun."不存在");
        }
    }
} 
/**
* 使用本地PHP GD生成
* https://image.intervention.io/v2/usage/overview#basic-usage 
*/
function image_resize_local($local_url,$w,$h = NULL){
    $ext = get_ext($local_url);
    if(strpos($local_url,'://')!==false){
        $local_url = substr($local_url,strpos($local_url,'://')+3);
        $local_url = substr($local_url,strpos($local_url,'/')+1); 
    }
    if(substr($local_url,0,1) == '/'){
        $local_url = substr($local_url,1);
    }
    $new_relative_url = get_dir($local_url)."_resize/";
    $new_dir = PATH.$new_relative_url; 
    if(!is_dir($new_dir)) mkdir($new_dir,0777,true);
    $unicode = "w_".$w."h_".$h; 
    $new_file_url = $new_relative_url.get_name($local_url).$unicode.'.'.$ext;
    $new_file = PATH.$new_file_url;
    if(file_exists($new_file)){
        return cdn().$new_file_url;
    } 
    $file = PATH.$local_url;
    if(!file_exists($file)){
        log_error("本地文件不存在，无法生成缩略图".$file);
        return;
    } 
    $driver = image_init()->make($file);      
    $driver = $driver->resize($w,$h); 
    $driver->save($new_file);  
    return cdn().$new_file_url;
}

/**
* 配置 image_drive 值为imagick ,默认不用配置使用的gd
*/
function image_init(){
    static $_image;
    if($_image){
        return $_image;
    }
    global $config; 
    $image_drive = $config['image_drive']?:'gd';
    $_image = new \Intervention\Image\ImageManager(['driver' => $image_drive]);
    return $_image;
}