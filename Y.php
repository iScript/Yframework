<?php
//项目开始时间
defined('BEGIN_TIME') or define('BEGIN_TIME',microtime(true));

define('Y_PATH',dirname(__FILE__).'/');

//!defined('ROOT') && exit('Forbidden');

//设置时区
date_default_timezone_set('Asia/Shanghai');

//设置错误等级
error_reporting(E_ALL);

//字符编码
header('Content-Type: text/html; charset=utf-8');

//加载常用的函数
require Y_PATH."common/common.func.php";

//加载常量
require Y_PATH."common/constant.php";

//定义目录常量
defined('LIB_PATH') 	or define('LIB_PATH',Y_PATH.'Lib/'); 								//系统核心类库目录
defined('APP_PATH') 	or define('APP_PATH',dirname($_SERVER['SCRIPT_FILENAME']).'/');		//项目路径
defined('APP_NAME') 	or define('APP_NAME',basename(dirname($_SERVER['SCRIPT_FILENAME'])));//项目名称
defined('MODEL_PATH') 	or define('MODEL_PATH',APP_PATH.'Model/');							//model
defined('VIEW_PATH') 	or define('VIEW_PATH',APP_PATH.'View/');							//view
defined('CONTROLLER_PATH') or define('CONTROLLER_PATH',APP_PATH.'Controller/');				//model
defined('RUNTIME_PATH') or define('RUNTIME_PATH',APP_PATH.'Runtime/');						//项目缓存目录
defined('CACHE_PATH')   or define('CACHE_PATH',RUNTIME_PATH.'_cache/'); 					//项目模板缓存目录
defined('COMPILE_PATH') or define('COMPILE_PATH',RUNTIME_PATH.'_compile/');					//项目模板编译文件夹
defined('SESSION_PATH') or define('SESSION_PATH',RUNTIME_PATH.'_session/');					//项目模板编译文件夹
defined('CONF_PATH')    or define('CONF_PATH',APP_PATH.'Conf/'); 							//项目配置目录
defined('LANG_PATH')    or define('LANG_PATH',APP_PATH.'Lang/'); 							//项目语言包目录
defined('DRIVER_PATH')  or define('DRIVER_PATH',Y_PATH.'Driver/'); 							//驱动目录

//ini_set( 'log_errors', 1 );
//ini_set( 'error_log', RUNTIME_PATH . '/error.log' );

//如果目录不存在则创建
if(!is_dir(CONTROLLER_PATH)) Fun::build_app_dir();

//加载惯例配置文件
C(include Y_PATH.'Conf/convention.php');

//加载框架语言文件
L(include Y_PATH.'Lang/'.strtolower(C('DEFAULT_LANG')).'.php');

//使用原始数据，入库需要转义！
if (!get_magic_quotes_gpc()) {
	//trips
	$_GET = Fun::addslashes_deep($_GET);
	$_POST = Fun::addslashes_deep($_POST);
	$_COOKIE = Fun::addslashes_deep($_COOKIE);
	$_REQUEST = Fun::addslashes_deep($_REQUEST);
}

//session
Fun::session_start();

//exit;

//路由
$front = Front::getInstance();
$front->setControllerPath(CONTROLLER_PATH);	//控制器目录

//注册运行的Module
$front->registerModules(C('FRONT_MODULES'));

try{
	$front->dispatch();
}catch (Exception $e){
    echo $e->getMessage();exit();
}

//自动加载类
function __autoload($class)
{	
	if(substr($class,-6) == 'Driver'){
		$file = $class. '.class.php';
		require_once(DRIVER_PATH . "Db/" . $file);
		return;
	}
	$file = $class. '.class.php';
	require_once(LIB_PATH . $file);
}


