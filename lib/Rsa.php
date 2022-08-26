<?php  
/**
 * 
 * @license read license.txt
 * @author sun <sunkangchina@163.com>
 * @copyright (c) 2021 
 */
namespace lib;
 
class Rsa
{
    public $rsa;
    public function __construct(){
        $this->rsa = new \phpseclib\Crypt\RSA;
    }
    public function create(){
        return $this->rsa->createKey();
    } 
    public function encode($data,$public_key){
        $this->rsa->loadKey($public_key);
        $r = $this->rsa->encrypt($data);
        return base64_encode($r);
    } 
    public function decode($data,$private_key){
        $data = base64_decode($data);
        $this->rsa->loadKey($private_key);
        return $this->rsa->decrypt($data); 
    }
}