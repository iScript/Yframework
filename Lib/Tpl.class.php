<?php
class Tpl
{

    private static $_VAR = array();
    private static $SMARTY = null;
    public static $_instance = null;

    /**
     * 注册一个模板变量
     *
     * @param string $key
     * @param mix $value
     */
    public static function assignvar($key, $value = null)
    {
        if(is_array($key))
        {
            foreach((array)$key as $_k => $_v)
            {
                self::assignvar($_k, $_v);
            }
        }
        else
        {
            self::$_VAR[(string)$key] = $value;
        }
    }

    /**
     * 获取Smarty对象并且初始化
     * 只会获取一次
     */
    protected static function getSmarty()
    {
        if(null === self::$SMARTY)
        {
            $smarty = new Template();
            //一些设置
            
            $smarty->compile_dir = COMPILE_PATH;
            $smarty->cache_dir = CACHE_PATH;
            $smarty->template_dir = VIEW_PATH; 
			self::$SMARTY = $smarty;
        }
        return self::$SMARTY;
    }


    /**
     * 显示/解析模板
     * @param string $resource_name 模板路径
     */
    public static function display($resource_name = null)
    {	
        if(!isset($resource_name)){
        	$Modeule = Front::getInstance()->getModuleName();
        	$Controller = Front::getInstance()->getControllerName();
        	$Action = Front::getInstance()->getActionName();
        	
        	$resource_name = $Modeule."/".$Controller."/".$Action.".html";
        }	

    	$smarty = self::getSmarty();
        foreach(self::$_VAR as $key => $value)
        {
            $smarty->assign($key, $value);
        }
        
        $_smarty_results = $smarty->fetch($resource_name);    
        if(false)
        {	
        	//模式修正符   i(不区分大小写)  e(替换字符串中对逆向引用作正常的替换，将其作为 PHP 代码求值) U( UTF-8)
        	
        	// seo change!
        	$_replace[] = "!action=\"(\/[^\"]*)\"!ieU";		//
        	$_replaceto[] = " 'action=\"' . WEB_PATH . '$1\"'";
        	

        	$_replace[] = "!href=\"(\/[^\"]*)\"!ieU";
        	$_replaceto[] = " 'href=\"' .WEB_PATH. '$1 \"'";
        	
        	/* 分页问题 request_uri
        	$_replace[] = "!href=\'(\/[^\']*)\'!ieU";
        	$_replaceto[] = " 'href=\"' . WEB_PATH. '$1 \"'";
        	*/
        	
        	//src自动替换
        	$_replace[] = "!src=\"(\/[^\"]*)\"!ieU";
        	$_replaceto[] = " 'src=\"' . WEB_PATH . '$1 \"'";
        	
        	//其他一些
        	$_replace[] = "!resultsUrl=\"(\/[^\"]*)\"!ieU";
        	$_replaceto[] = " 'resultsUrl=\"' . WEB_PATH . '$1\"'";
        
        	$_smarty_results = preg_replace($_replace, $_replaceto, $_smarty_results);
        }
        echo $_smarty_results;
    }


  
    /**
     * 获取单例对象
     * @return Core_Template
     */
    public static function getInstance()
    {
        if(null === self::$_instance)
        {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * 私有的构造函数
     * 禁止new
     */
    private function __construct(){}


}
