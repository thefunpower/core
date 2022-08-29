<?php

/**
 * 
 * @license read license.txt
 * @author sun <sunkangchina@163.com>
 * @copyright (c) 2021 
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
