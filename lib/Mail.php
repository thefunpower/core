<?php
/*
    Copyright (c) 2021-2031, All rights reserved.
    This is NOT a freeware
    LICENSE: https://github.com/thefunpower/core/blob/main/LICENSE.md 
    Connect Email: sunkangchina@163.com 
    Code Vesion: v1.0.x
*/

namespace lib;

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

/**
 * 发送邮件
 * composer require symfony/mailer
 * https://symfony.com/components/Mailer
 */
class Mail
{
	public static $mailer;
	public static $mail_from;
	//smtp://user:pass@smtp.example.com:25
	public static function init($dsn = '')
	{
		global $config;
		if (!$dsn) {
			$dsn = $config['mail_dsn'];
			$mail_from = get_config('mail_from');
			$pwd = get_config('mail_pwd');
			$mail_smtp = get_config('mail_smtp');
			$mail_port = get_config('mail_port');
			$dsn = "smtp://".$mail_from.":".$pwd."@".$mail_smtp.":".$mail_port; 
			self::$mail_from = $mail_from;
		}
		ini_set("default_socket_timeout", 3);
		$transport = Transport::fromDsn($dsn);
		self::$mailer = new Mailer($transport);
	}

	/**
	 *  发送邮件
   \lib\Mail::send([
    'from'   => 's7@163.com',
    'to'     => $data['email'],
    'subject'=> "开通成功提醒", 
    'html'   => "<p>登录网址为</p>"
   ]); 
   */
	public static function send($config)
	{  
		if (!self::$mailer) {
			self::init();
		}
		if(!$config['from']){
			$config['from'] = self::$mail_from;
		}
		$email = (new Email());
		foreach ($config as $k => $v) {
			$email = $email->$k($v);
		}
		return self::$mailer->send($email);
	}
}
