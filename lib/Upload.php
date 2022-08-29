<?php

/**
 * 
 * @license read license.txt
 * @author sun <sunkangchina@163.com>
 * @copyright (c) 2021 
 */

namespace lib;

use Upload\Storage\FileSystem;
use Upload\File;
/*
use lib\Upload;
//是否总是上传
Upload::$_upload_always = false;
//是否把上传记录保存到数据库
Upload::$_upload_to_db = true;
//是否上传到 data/uploads目录下，而不是 uploads/ 下
Upload::$_upload_to_new_dir = true;
*/

class Upload
{
    public $domain;
    static $_upload_always  = false;
    static $_upload_to_new_dir = false;
    //写入数据库
    static $_upload_to_db = true;

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
     * 根据hash值取信息
     */
    public function getHash($md5)
    {
        $f = db_get_one('upload', '*', ['hash' => $md5]);
        $this->returnParams($f);
        return $f;
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
    public function returnParams(&$model)
    {
        unset($_POST['file']);
        do_action("upload", $model);
        $model['request'] = $_POST;
        if (!$model['data']) {
            $model['data'] = cdn() . $model['url'];
        }
    }

    /**
     *  单个文件上传
     */
    public function one($http_opt = [])
    {
        $user_id = cookie(ADMIN_COOKIE_NAME) ?: api(false)['user_id'];
        if (!$user_id) {
            write_log_error("未登录用户禁止上传文件");
            json_error(['msg' => '未登录用户禁止上传文件']);
        }
        global $config;
        //上传文件前
        do_action("upload.before", $this);
        $file_key =  g('file_key') ?: 'file';
        $url      =  'uploads/' . $this->domain . $user_id . '/' . date('Y-m-d');
        $_upload_to_new_dir  =  self::$_upload_to_new_dir ?: false;
        if ($_upload_to_new_dir) {
            $url = 'data/uploads/' . $this->domain . $user_id . '/' . date('Y-m-d');
        }
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
        $upload_always = self::$_upload_always ?: false;
        do_action("upload.mime", $mime);
        do_action("upload.size", $size);
        do_action("upload.ext", $file_ext);
        if (self::$_upload_to_db) {
            $f  = db_get_one('upload', '*', ['hash' => $md5]);
            if ($f && !$upload_always) {
                $f['local_path'] = PATH . $f['url'];
                //上传成功后
                do_action("upload.after", $f);
                $this->returnParams($f);
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
            if (self::$_upload_to_db) {
                $id = db_insert('upload', $insert);
                $f  = db_get_one('upload', '*', ['id' => $id]);
            } else {
                $f = $insert;
            }
            $this->returnParams($f);
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
