<?php

/*
    Copyright (c) 2021-2031, All rights reserved.
    This is NOT a freeware
    LICENSE: https://github.com/thefunpower/core/blob/main/LICENSE.md 
    Connect Email: sunkangchina@163.com 
    Code Vesion: v1.0.x
*/


namespace lib;

class Rsa
{
    public $rsa;
    public function __construct()
    {
        $this->rsa = new \phpseclib\Crypt\RSA;
    }
    public function create()
    {
        return $this->rsa->createKey();
    }
    public function encode($data, $public_key)
    {
        $this->rsa->loadKey($public_key);
        $r = $this->rsa->encrypt($data);
        return base64_encode($r);
    }
    public function decode($data, $private_key)
    {
        $data = base64_decode($data);
        $this->rsa->loadKey($private_key);
        return $this->rsa->decrypt($data);
    }
}
