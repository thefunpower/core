<?php

/*
	Copyright (c) 2021-2050 FatPlug, All rights reserved.
	This file is part of the FatPlug Framework (http://fatplug.cn).
	This is not free software.
	you can redistribute it and/or modify it under the
	terms of the License after purchased commercial license. 
	mail: sunkangchina@163.com
	web: http://fatplug.cn
*/


namespace lib;

/**
 *   
$file = PATH.'../demos/doc/1.jpg';  
$pdf = new \lib\Img($file); 
print_r($pdf->getWh());
//2是横版，1是竖版
print_r($pdf->getDimensionsType()); 
 */
class Img
{
    public $file;
    public $info;
    public function __construct($file)
    {
        $this->file = $file;
        return $this;
    }
    //取长宽
    public function getWh()
    {
        $info = getimagesize($this->file);
        list($width, $height, $type) = $info;
        $this->info = [
            'width'  => $width,
            'height' => $height,
            'type'   => $type,
            'bits'   => $info['bits'],
            'mime'   => $info['mime'],
        ];
        return $this->info;
    }
    //2是横版，1是竖版
    public function getDimensionsType()
    {
        if (!$this->info) {
            $this->getWh();
        }
        $info = $this->info;
        if ($info['width'] > $info['height']) {
            return 2;
        } else {
            return 1;
        }
    }

    /**
     * 以下为static方法
     */
    public static function get_local_img_tag($content, $all = true)
    {
        $preg = '/<\s*img\s+[^>]*?src\s*=\s*(\'|\")(.*?)\\1[^>]*?\/?\s*>/i';
        preg_match_all($preg, $content, $out);
        $img = $out[2];
        if ($img) {
            $num = count($img);
            for ($j = 0; $j < $num; $j++) {
                $i = $img[$j];
                if ((strpos($i, "http://") !== false || strpos($i, "https://") !== false) && strpos($i, base_url()) === false) {
                    unset($img[$j]);
                }
            }
        }
        if ($all === true) {
            return array_unique($img);
        }
        return $img[0];
    }

    public static function preg_img_tag($content)
    {
        $preg = '/<\s*img\s+[^>]*?src\s*=\s*(\'|\")(.*?)\\1[^>]*?\/?\s*>/i';
        preg_match_all($preg, $content, $out);
        unset($out[1]);
        $out = array_merge($out, array());
        return $out;
    }

    public static function get_img_tag($content, $all = true)
    {
        $preg = '/<\s*img\s+[^>]*?src\s*=\s*(\'|\")(.*?)\\1[^>]*?\/?\s*>/i';
        preg_match_all($preg, $content, $out);
        $img = $out[2];
        if ($all === true) {
            return $img;
        }
        return $img[0];
    }

    public static function remove_img_tag($content, $all = false)
    {
        $preg = '/<\s*img\s+[^>]*?src\s*=\s*(\'|\")(.*?)\\1[^>]*?\/?\s*>/i';
        $out = preg_replace($preg, "", $content);
        return $out;
    }

    public static function img_wh($img)
    {
        $a = getimagesize($img);
        return array('w' => $a[0], 'h' => $a[1]);
    }
}
