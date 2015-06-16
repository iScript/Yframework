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
    
    // 验证身份证  http://baike.baidu.com/view/5112521.htm
    static public function isIDCard($IDCard) {
        if (strlen($IDCard) == 18) {
            return self::check18IDCard($IDCard);
        } elseif ((strlen($IDCard) == 15)) {
            $IDCard = self::convertIDCard15to18($IDCard);
            return self::check18IDCard($IDCard);
        } else {
            return false;
        }
    }

    //计算身份证的最后一位验证码,根据国家标准GB 11643-1999
    static public function calcIDCardCode($IDCardBody) {
        if (strlen($IDCardBody) != 17) {
            return false;
        }

        //加权因子 
        $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
        //校验码对应值 
        $code = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
        $checksum = 0;

        for ($i = 0; $i < strlen($IDCardBody); $i++) {
            $checksum += substr($IDCardBody, $i, 1) * $factor[$i];
        }

        return $code[$checksum % 11];
    }

    // 将15位身份证升级到18位 
    static public function convertIDCard15to18($IDCard) {
        if (strlen($IDCard) != 15) {
            return false;
        } else {
            // 如果身份证顺序码是996 997 998 999，这些是为百岁以上老人的特殊编码 
            if (array_search(substr($IDCard, 12, 3), array('996', '997', '998', '999')) !== false) {
                $IDCard = substr($IDCard, 0, 6) . '18' . substr($IDCard, 6, 9);
            } else {
                $IDCard = substr($IDCard, 0, 6) . '19' . substr($IDCard, 6, 9);
            }
        }
        $IDCard = $IDCard . self::calcIDCardCode($IDCard);
        return $IDCard;
    }

    // 18位身份证校验码有效性检查 
    static public function check18IDCard($IDCard) {
        if (strlen($IDCard) != 18) {
            return false;
        }

        $IDCardBody = substr($IDCard, 0, 17); //身份证主体
        $IDCardCode = strtoupper(substr($IDCard, 17, 1)); //身份证最后一位的验证码

        if (self::calcIDCardCode($IDCardBody) != $IDCardCode) {
            return false;
        } else {
            return true;
        }
    }

    //是否汉字
    static public function isChineseString($str){
        return preg_match("/^[\x7f-\xff]+$/",$str);
    }

    /*
    static public function isEmail($data) {
        if (!preg_match('/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/',$data)) return true;
        return false;
    }
	*/
    }
?>
