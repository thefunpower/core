<?php

namespace lib;

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

/**
 * 需要在config.ini.php配置
 * 
//邮件发送 
$config['mail_from'] = "s19921272737@163.com";
$config['mail_dsn']  = "smtp://".$config['mail_from'].":YCEHKDTNJUZXXYNL@smtp.163.com:25";

 * composer require symfony/mailer
 * https://symfony.com/components/Mailer
 */
class Mail
{
	public static $mailer;
	//smtp://user:pass@smtp.example.com:25
	public static function init($dsn = '')
	{
		global $config;
		if (!$dsn) {
			$dsn = $config['mail_dsn'];
		}
		ini_set("default_socket_timeout", 3);
		$transport = Transport::fromDsn($dsn);
		self::$mailer = new Mailer($transport);
	}

	/**
	 *  发送邮件
   \lib\Mail::send([
    'from'   => 's19921272737@163.com',
    'to'     => $data['email'],
    'subject'=> "开通开票软件成功提醒", 
    'html'   => "<p>登录网址为</p>"
   ]);
   更多参数如下：
	from
	to 
	cc
	bcc
	replyTo
	 */
	public static function send($config)
	{
		if (!self::$mailer) {
			self::init();
		}
		$email = (new Email());
		foreach ($config as $k => $v) {
			$email = $email->$k($v);
		}
		return self::$mailer->send($email);
	}
}
