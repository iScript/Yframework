<?php
// 仿smarty模板引擎，实现smarty基本功能，缓存还没做，有空再搞！！~   -by ykq
class Template
{
    public $template_dir   = '';
    public $cache_dir      = '';
    public $compile_dir    = '';
    public $cache_lifetime = 3600; 		// 缓存更新时间, 默认 3600 秒
    public $caching        = false;

    public $_var           = array();	//存储变量

    public function __construct(){
      	//code ..  
    }

    /**
     * 注册变量
     * 如果实数组将数组的键值assign  assign(array('a'=>4,'b'=>45));
     * 如果不是数组 assign('a',$a);
     */
    public function assign($tpl_var, $value = '')
    {	
        if (is_array($tpl_var)){ 
        	foreach ($tpl_var AS $key => $val){    
        		if ($key != ''){
                    $this->_var[$key] = $val;
                }
            }
        }else{
            if ($tpl_var != ''){
                $this->_var[$tpl_var] = $value;
            }
        }
    }

    /**
     * 显示页面函数
     * @access  public
     * @param   string      $filename
     * @return  void
     */
    public function display($filename){
		
        $out = $this->fetch($filename);
        echo $out;
    }

    
    /**
     * 处理模板文件
     */
    public function fetch($filename){
    	
    	//echo $filename;exit;
    	//index.html
    	
		$tplFile = $this->template_dir . $filename;
		
		if(!file_exists($tplFile)) {
			echo 'ERROR:template ('.$tplFile.')file is not exist';
			exit;
        }
        $out = $this->make_compiled($tplFile);
        return $out; // 返回html数据
    }

    /**
     * 编译模板函数
     * @access  public
     * @param   string      $filename
     * @return  sring        编译后文件地址
     */
    private function make_compiled($tplFile){	
        
    	$parFile = $this->compile_dir . md5($tplFile) . '.php';
		
        //如果编译文件不存在 或 模板文件修改过就重新编译，否则直接include编译文件
        if (!file_exists($parFile) || filemtime($tplFile) > filemtime($parFile)){

        	$content  = file_get_contents($tplFile);
        	//将{}里的内容放到 $this->select()执行后的结果替换{xxx}
        	$content = preg_replace("/{([^\}\{\n]*)}/e", "\$this->select('\\1');", $content);
			
            //LOCK_EX 独占锁定   (防止高并发时文件无法写入？)
            if (file_put_contents($parFile, $content, LOCK_EX) === false){
            	trigger_error('can\'t write:' . $parFile);
            }
        }
        ob_start();
        include $parFile;
        $source = ob_get_contents();
        ob_end_clean(); 	  	
        return $source;
    }
	

    /**
     * 处理{}标签
     * 参数tag代表{}里面的内容
     * @access  public
     * @param   string      $tag
     * @return  sring
     */
    private function select($tag){
        $tag = stripslashes(trim($tag));	//删除由 addslashes() 函数添加的反斜杠

        if (empty($tag))
        {
            return '{}';
        }
        elseif ($tag{0} == '*' && substr($tag, -1) == '*') // 注释部分
        {
            return '';
        }
        elseif ($tag{0} == '$') // 变量
        {										//取$后面的
            return '<?php echo ' . $this->get_val(substr($tag, 1)) . '; ?>';
        }
        elseif ($tag{0} == '/') // 结束 tag
        {
            switch (substr($tag, 1))
            {
                case 'if':
                    return '<?php endif; ?>';
                    break;

                case 'foreach':	//前面有if(count($_array))
                    return '<?php endforeach; endif;  ?>';
                    break;

                case 'literal':
                    return '';
                    break;

                default:	
                    return '{'. $tag .'}';
                    break;
            }
        }
        else
        {
            $tag_arr = explode(' ', $tag);		//以空格分隔 如 {if xxx}
            $tag_sel = array_shift($tag_arr);	//将第一个关键字弹出赋值给$tag_sel
		
            switch ($tag_sel)
            {
                case 'if':
                    return $this->_compile_if_tag(substr($tag, 3));
                    break;

                case 'else':
                    return '<?php else: ?>';
                    break;

                case 'elseif':

                    return $this->_compile_if_tag(substr($tag, 7), true);
                    break;
				
                /* 暂时不处理
                case 'foreachelse':
                    return '<?php endforeach; else: ?>';
                    break;
				*/   
                case 'foreach':
                    return $this->_compile_foreach_start(substr($tag, 8));
                    break;
                case 'include':
                    $t = $this->get_para(substr($tag, 8));
                    return '<?php echo $this->fetch(' . "'$t[file]'" . '); ?>';
                    break;
                case 'literal':
                    return '';
                    break;
                default:
                    return '{' . $tag . '}';
                    break;
            }
        }
    }

    /**
     * 处理smarty标签中的变量标签 | 参数$val代表$后面的内容
     * @access  public
     * @param   string     $val
     * @return  bool
     */
    private function get_val($val)	
    {	
        // 处理 aa[bb] =>aa.bb  
    	if (strrpos($val, '[') !== false)
        {						
            $val = preg_replace("/\[([^\[\]]*)\]/eis", "'.'.'\\1'", $val);
        }
		
        //如果变量调节器
        if (strrpos($val, '|') !== false)	 // bb | strip
        {
            $moddb = explode('|', $val);
            $val = array_shift($moddb); 	//$moddb= (这个元素弹出赋值给$val)|($moddb只剩下|后面的)
        }

        if (empty($val))
        {
            return '';
        }
		// arr.$aa
        if (strpos($val, '.$') !== false)
        {
            $all = explode('.$', $val);
				
            foreach ($all AS $key => $val)
            {	// 第一个值直接 $this->_var['arr'] 后面接上[$this->var[$aa]]				
                $all[$key] = $key == 0 ? $this->make_var($val) : '['. $this->make_var($val) . ']';
            }
            $p = implode('', $all);
        }else{
        	
        	/* $val == aa 或者 aa.bb.cc 这样的值
             * $p = $this->_var['aa']
         	 * $p = $this->_var['aa']['bb']['cc']
         	 */
        	$p = $this->make_var($val);
        }

        if (!empty($moddb))	// Array([0] => strip_tags [1] => truncate:3) 
        {	
            foreach ($moddb AS $key => $mod)
            {	
            	
                $s = explode(':', $mod);
                // strip_tag   =>   array(0=>strip_tag)
                // truncate:3  =>   array(0=>truncate,1=>3)
                
                switch ($s[0]){
                    
                	case 'escape':
                        $s[1] = trim($s[1], '"');
                        if ($s[1] == 'html')
                        {
                            $p = 'htmlspecialchars(' . $p . ')';
                        }
                        elseif ($s[1] == 'url')
                        {
                            $p = 'urlencode(' . $p . ')';
                        }
                        elseif ($s[1] == 'decode_url')
                        {
                            $p = 'urldecode(' . $p . ')';
                        }
                        elseif ($s[1] == 'quotes')
                        {
                            $p = 'addslashes(' . $p . ')';
                        }
                        else
                        {
                            $p = 'htmlspecialchars(' . $p . ')';
                        }
                        break;

                    case 'nl2br':	
                        $p = 'nl2br(' . $p . ')';
                        break;

                    case 'truncate':
                        $p = 'mb_substr(' . $p . ",0,$s[1],'utf-8')";
                        break;

                    case 'strip_tags':
                        $p = 'strip_tags(' . $p . ')';
                        break;

                    default:
                        # code...
                        break;
                }
            }
        }

        return $p;
    }

    /**
     * 处理去掉$的字符串 ，经过处理只剩下2种情况 aa 或者aa.bb.cc这样的字符串
     * @access  public
     * @param   string     $val
     * @return  bool
     */
    private function make_var($val){	
    	
    	//不是数组，直接$this->_var[]
        if (strrpos($val, '.') === false){
            $p = '$this->_var[\'' . $val . '\']';
        
        //是数组的情况下
        }else{
        	
            $t = explode('.', $val);
            
            $_var_name = array_shift($t);	
	
            if ($_var_name == 'smarty'){	//$smarty.cookie ....
                 $p = $this->_compile_smarty_ref($t);
            }else{
                 $p = '$this->_var[\'' . $_var_name . '\']';
            }

            
            foreach ($t AS $val){
                $p.= '[\'' . $val . '\']';
            }
           
        }	   
         return $p;
    }

    /**
	 * 像key = key item = item from = $arr 这样的内容
	 * 先str_trim去掉等号旁边的空格，以空格分隔成数组$pa
	 * 再foreach $pa中的$value 以等号分隔变成参数
	 * $para[$a] = $b;
     * @return  array
     */
    private function get_para($val){
        $pa = $this->str_trim($val);	//key = key item = item from = $arr
        								//=> Array([0]=>key=key [1]=>item=item [2]=>from=$arr ) 
        foreach ($pa AS $value)
        {
            if (strrpos($value, '='))
            {
                list($a, $b) = explode('=', str_replace(array(' ', '"', "'", '&quot;'), '', $value));
                if ($b{0} == '$')	//array([from]=>this->_var[arr])
                {
     
                    $para[$a] = $this->get_val(substr($b, 1));
    
                }
                else				// array([key]=>key [item]=>item)
                {
                    $para[$a] = $b;
                }
            }
        }
		
        return $para;
    }


    /**
     * 处理if标签
     * @access  public
     * @param   string     $tag_args
     * @param   bool       $elseif
     * @return  string
     */
    private function _compile_if_tag($tag_args, $elseif = false)
    {
        preg_match_all('/\-?\d+[\.\d]+|\'[^\'|\s]*\'|"[^"|\s]*"|[\$\w\.]+|!==|===|==|!=|<>|<<|>>|<=|>=|&&|\|\||\(|\)|,|\!|\^|=|&|<|>|~|\||\%|\+|\-|\/|\*|\@|\S/', $tag_args, $match);
        
        //$match =>Array ( [0] => Array ( [0] => $a [1] => == [2] => 11111 ) )

        $tokens = $match[0];
        
        for ($i = 0, $count = count($tokens); $i < $count; $i++)
        {
            $token = &$tokens[$i];
            switch (strtolower($token)){
                case 'eq':
                    $token = '==';
                    break;

                case 'ne':
                case 'neq':
                    $token = '!=';
                    break;

                case 'lt':
                    $token = '<';
                    break;

                case 'le':
                case 'lte':
                    $token = '<=';
                    break;

                case 'gt':
                    $token = '>';
                    break;

                case 'ge':
                case 'gte':
                    $token = '>=';
                    break;

                case 'and':
                    $token = '&&';
                    break;

                case 'or':
                    $token = '||';
                    break;

                case 'not':
                    $token = '!';
                    break;

                case 'mod':
                    $token = '%';
                    break;

                default:
                    if ($token[0] == '$')
                    {
                        $token = $this->get_val(substr($token, 1));
                    }
                    break;
            }
        }

        if ($elseif){
            return '<?php elseif (' . implode(' ', $tokens) . '): ?>';
        }
        else{
            return '<?php if (' . implode(' ', $tokens) . '): ?>';
        }
    }

	//处理foreach
    private function _compile_foreach_start($tag_args){

    	$attrs = $this->get_para($tag_args);
    	//Array ( [key] => key [item] => item [from] => $this->_var['arr'] )
        
    	$arg_list = array();
        $_array = $attrs['from'];
		//
        $item = $this->get_val($attrs['item']);		//$this->_var['item'] 
		
        if (!empty($attrs['key'])){
            $key = $attrs['key'];
            $key_part = $this->get_val($key).' => ';
        }else{
            $key = null;
            $key_part = '';
        }

        $output = '<?php ';
        $output .= "\$_array = $_array;";

        $output .= "if (count(\$_array)):\n";
       	$output .= "foreach (\$_array AS $key_part$item):\n";

        return $output . '?>';
    }


    /**
     * 处理smarty开头的预定义变量
     * @access  public
     * @param   array   $indexes
     * @return  string
     */
    private function _compile_smarty_ref(&$indexes){	
    	// print_r($indexes);exit;
    	// smarty.get.id  =>
    	// Array ( [0] => get [1] => id )
        
    	$_ref = $indexes[0];

        switch ($_ref)
        {
            case 'now':
                $compiled_ref = 'time()';
                break;

            case 'get':
                $compiled_ref = '$_GET';
                break;

            case 'post':
                $compiled_ref = '$_POST';
                break;

            case 'cookies':
                $compiled_ref = '$_COOKIE';
                break;

            case 'env':
                $compiled_ref = '$_ENV';
                break;

            case 'server':
                $compiled_ref = '$_SERVER';
                break;

            case 'request':
                $compiled_ref = '$_REQUEST';
                break;

            case 'session':
                $compiled_ref = '$_SESSION';
                break;

            default:
                // echo ;
                break;
        }
        array_shift($indexes);
		
        return $compiled_ref; //$_GET ....
    }


    /* 处理'a=b c=d k = f '类字符串，返回数组 */
    private function str_trim($str){
        while (strpos($str, '= ') != 0)
        {
            $str = str_replace('= ', '=', $str);
        }
        while (strpos($str, ' =') != 0)
        {
            $str = str_replace(' =', '=', $str);
        }
        return explode(' ', trim($str));
    }
    
}

?>