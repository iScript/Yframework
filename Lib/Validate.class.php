<?php
//验证类
class Validate {
	
	//email验证，成功返回该email,失败返回false
	static public function isEmail($data){
		return filter_var($data,FILTER_VALIDATE_EMAIL);
	}
	
	//IP验证，成功返回该IP,失败返回false
	static public function isIp($data){
		return filter_var($data,FILTER_VALIDATE_IP);
	}
	
	//URL验证，只判断http://之类的前缀？
	static public function isUrl($data){
		return filter_var($data,FILTER_VALIDATE_URL);
	}
	
	//是否为空
    static public function isEmpty($data){
        if(trim($data)=='') return true;
        return false;
    }
	
	//数据是否一致 
    static public function isEquals($data,$otherData){
        if(trim($data) == trim($otherData)) return true;
        return false;
    }
    
    //数据是否为数字
    static public function isNumber($data){
        if(is_numeric($data)) return true;
        return false;
    }
  
    //长度是否合法 $option为长度范围 
    static public function strLength($data,$option){
        //获取字符的长度，一个中文和一个英文一样长度都为1
		$length = mb_strlen(trim($data),'utf-8');
		if($length < $option['min']) return false;
		if($length > $option['max']) return false;
		return true;
    }
	
	//验证数字范围 $num1-$num2
	static public function intRange($data,$num1,$num2){
		return filter_var($data,FILTER_VALIDATE_INT,array('options'=>array('min_range'=>$num1,'max_range'=>$num2)));
	}
    
    /*
    static public function isEmail($data) {
        if (!preg_match('/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/',$data)) return true;
        return false;
    }
	*/
}
?>
