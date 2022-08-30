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


/**
 *  处理跨域
 */
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Credentials:true');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
header('Access-Control-Allow-Methods: GET, POST, PUT,DELETE,OPTIONS,PATCH');
header('X-Powered-By: WAF/2.0');
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit;
}
