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
/* 
生成条形码
$bar = new lib\Barcode;
echo $bar->display(date("YmdHis"));exit;

*/

class Barcode
{
    public $generator;
    public function __construct()
    {
        $this->generator = new \Picqer\Barcode\BarcodeGeneratorHTML();
    }

    public function display($txt = "demo")
    {
        return $this->generator->getBarcode($txt, $this->generator::TYPE_CODE_128);
    }
}
