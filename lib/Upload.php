<?php
/**
 * 上传
 */

namespace core\sys\controller;

use lib\Upload as UploadLib;
use lib\Str;

class upload extends \base\user_controller
{
    protected $upload;
    protected function init()
    {
        parent::init();
        $this->upload = new UploadLib();
        $this->upload->user_id = get_cookie_user_id();
    }
    /**
     * 上传成功后
     */
    protected function parse_uploaded($data)
    {
        $url_resize = '/' . $data['url'];
        if(in_array($data['ext'], ['jpg','jpeg','png','gif','webp'])) {
            $url_resize = cdn_image_resize($url_resize);
        }
        $list = [
            'url' => '/' . $data['url'],
            'http_url' => cdn() . '/' . $data['url'],
            'mime' => $data['mime'],
            'size' => $data['size'],
            'size_to' => Str::size((int)$data['size']),
            'ext' => $data['ext'],
            'url_resize' => $url_resize,
        ];
        //上传至OSS
        uploader_to_oss('/' . $data['url']);
        return $list;
    }
    /**
     * 单文件上传
     */
    public function action_one()
    {
        $ret = $this->upload->one();
        $ret = $this->parse_uploaded($ret);
        $ret['code'] = 0;
        return $ret;
    }
    /**
     * 多文件上传
     */
    public function action_muit()
    {
        $_POST['return_arr'] = 1;
        $list = [];
        foreach ($_FILES as $k => $v) {
            $_POST['file_key'] = $k;
            $ret = $this->upload->one();
            $ret = $this->parse_uploaded($ret);
            $list[] = $ret;
        }
        return ['data' => $list];
    }
    /**
     * 裁剪
     */
    public function action_crop()
    {
        $f  = $_FILES['file'];
        $type = $f['type'];
        if ($f['error'] != 0) {
            json(['code' => 250, 'msg' => '上传异常']);
        }
        $ext  = substr($type, strrpos($type, '/') + 1);
        $tmp  = $f['tmp_name'];
        $path = 'uploads/crop/' . date('Y-m') . "/";
        $dest = PATH . $path;
        if (!is_dir($dest)) {
            mkdir($dest, 0777, true);
        }
        $ext = $ext ?: 'jpg';
        $name = uniqid(true) . "." . $ext;
        $dest = $dest . $name;
        if (!move_uploaded_file($tmp, $dest)) {
            return ['code' => 0, 'msg' => 'move file failed'];
        }
        $data['code'] = 0;
        $data['status'] = 200;
        $data['url']  =  '/' . $path . $name;
        $data['http_url']  = cdn() . '/' . $path . $name;
        return $data;
    }
}