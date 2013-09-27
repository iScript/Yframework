<?php 
define('MAGIC_QUOTES_GPC', function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc());	//是否开启魔术转义 [php5.3以后官方不建议使用]
define('ICONV_ENABLE', function_exists('iconv'));			//iconv字符集转义
define('MB_ENABLE', function_exists('mb_convert_encoding'));//mb_convert_encoding字符集转义
define('EXT_OBGZIP', function_exists('ob_gzhandler'));		//ob_start的gzip压缩