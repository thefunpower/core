<?php

/*
	Copyright (c) 2021-2050 FatPlug, All rights reserved.
	This file is part of the FatPlug Framework (http://fatplug.cn).
	This is not free software.
	you can redistribute it and/or modify it under the
	terms of the License after purchased commercial license. 
	mail: sunkangchina@163.com
*/


namespace lib;

use Upload\Storage\FileSystem;
use Upload\File;
/*
use lib\Upload;
//是否总是上传
Upload::$db = false;
//是否把上传记录保存到数据库
Upload::$db = true;
//是否上传到 data/uploads目录下，而不是 uploads/ 下
Upload::$path = true;
*/

class Upload
{
    public $domain; 
    /**
     * 写入数据库,默认使用uploads表记录，
     * 也可以设置为false
     * add_action('upload.init',function(){
     *      \lib\Upload::$db = false;
     * })
     */
    static $db = true;

    public function __construct()
    {
        global $config;
        $host = $config['host'];
        if (!$host) {
            exit('请配置域名');
        }
        $host = str_replace("https://", "", $host);
        $host = str_replace("http://", "", $host);
        $host = str_replace(":", "_", $host);
        $host = str_replace(".", "_", $host);
        $this->domain  = $host;
        //上传初始化
        do_action("upload.init", $this);
    } 
    /**
     * 批量上传
     */
    public function all()
    {
        $list = [];
        foreach ($_FILES as $k => $v) {
            $_POST['file_key'] = $k;
            $ret = $this->one();
            if ($ret['url']) {
                $new_url = $ret['url'];
                $new_url = host() . $new_url;
                $list[] = [
                    'url' => $new_url
                ];
            }
        }
        return $list;
    }

    /**
     * 返回参数 
     */
    public function return_params(&$model)
    {
        unset($_POST['file']);
        do_action("upload", $model);
        $model['post'] = $_POST?:[];
        $model['get']  = $_GET?:[];
        if (!$model['data']) {
            $model['data'] = cdn() . $model['url'];
        }
    }

    /**
     *  单个文件上传
     *  do_action("upload.mime", $mime);
     *  如果不是管理员或小程序用户，比如PC商家需要上传图片可配置
     *  global $upload_always;
     *  $upload_always = true;
     */
    public function one($http_opt = [])
    {
        global $upload_always;
        $user_id = cookie(ADMIN_COOKIE_NAME) ?: api(false)['user_id'];
        $allow_upload = false;
        if($user_id){
            $allow_upload = true;
        }
        if($upload_always){
            $allow_upload = true;
        }
        if(!$allow_upload){
            json_error(['msg' => '上传文件被拦截，不支持当前用户上传文件']);
        } 
        global $config;
        //上传文件前
        do_action("upload.before", $this);
        $file_key =  g('file_key') ?: 'file';
        $sub_dir = g('sub_dir');
        if($sub_dir){
            $sub_dir = $sub_dir.'/';
        }
        $url      =  'uploads/' . $this->domain .$sub_dir. $user_id . '/' . date('Y-m-d'); 
        $path       = PATH . $url . '/';
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        $storage = new FileSystem($path);
        $file    = new File($file_key, $storage);
        $new_filename = uniqid();
        $ori_name = $file->getNameWithExtension();
        $file->setName($new_filename);
        $name = $file->getName();
        $md5  = $file->getMd5();
        $size = $file->getSize();
        $mime = $file->getMimetype();
        $file_ext = $file->getExtension();
        $upload_always = self::$db ?: false;
        do_action("upload.mime", $mime);
        do_action("upload.size", $size);
        do_action("upload.ext", $file_ext);
        if (self::$db) {
            $f  = db_get_one('upload', '*', ['hash' => $md5]);
            if ($f && !$upload_always) {
                $f['local_path'] = PATH . $f['url'];
                //上传成功后
                do_action("upload.after", $f);
                $this->return_params($f);
                return $f;
            }
        }
        try {
            $url = $url . '/' . $name . "." . $file_ext;
            $file->upload();
            $insert = [];
            $insert['url']      = $url;
            $insert['hash']     = $md5;
            $insert['user_id']  = $user_id;
            $insert['mime']     = $mime;
            $insert['size']     = bcdiv($size, bcmul(1024, 1024), 2);
            $insert['ext']      = $file_ext;
            $insert['name']     = $http_opt['name'] ?: $ori_name;
            $insert['created_at'] = date('Y-m-d H:i:s');
            if (self::$db) {
                $id = db_insert('upload', $insert);
                $f  = db_get_one('upload', '*', ['id' => $id]);
            } else {
                $f = $insert;
            }
            $this->return_params($f);
            $f['local_path'] = PATH . $url;
            //上传成功后
            do_action("upload.after", $f);
            return $f;
        } catch (\Exception $e) {
            $errors = $file->getErrors();
            return ['error' => $errors];
        }
    }
}
