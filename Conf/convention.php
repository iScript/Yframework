<?php
return  array(
    /* Cookie设置 */
    'COOKIE_EXPIRE'         => 0,    	// Coodie有效期
    'COOKIE_DOMAIN'         => '',      // Cookie有效域名
    'COOKIE_PATH'           => '/',     // Cookie路径
    'COOKIE_PREFIX'         => '',      // Cookie前缀 避免冲突

    /* 默认设定 */
    'DEFAULT_LANG'          => 'zh-cn', // 默认语言
    'DEFAULT_CHARSET'       => 'utf-8', // 默认输出编码
    'DEFAULT_TIMEZONE'      => 'PRC',	// 默认时区
    'DEFAULT_AJAX_RETURN'   => 'JSON',  // 默认AJAX 数据返回格式,可选JSON XML ...
    
    /* 数据库设置 */
    'DB_TYPE'               => 'mysql',     // 数据库类型
    'DB_HOST'               => 'localhost', // 服务器地址
    'DB_NAME'               => 'ecshop',   	// 数据库名
    'DB_USER'               => 'root',      // 用户名
    'DB_PWD'                => '123456',   	// 密码
    'DB_PORT'               => '3306',      // 端口
    'DB_PREFIX'             => 'ecs_',    	// 数据库表前缀
    'DB_CHARSET'            => 'utf8',      // 数据库编码默认采用utf8
    'DB_PCONNECT' 			=> false,		// 是否持久链接
	//'DB_DEPLOY_TYPE'        => 0, 		// 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)

	/* front modules注册 */
	'FRONT_MODULES'			=> array('admin','api','plugin','wap','mobile'),
	
	
	/* 模板 */
	// 'THEME'					=> 'index',
	
	/*  */
	'CSS_PATH'				=> '/zts/APP/View/css',
	'JS_PATH'				=> '/zts/APP/View/js',
	'IMAGE_PATH'			=> '/zts/APP/View/images',
	'WEB_ROOT'				=> '/zts/',
	'UP_PATH'				=> '/zts/APP/upload/',
	
);