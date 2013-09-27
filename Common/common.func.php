<?php
/**
 * ��ȡ���������ò��� ֧����������
 * @param string|array $name ���ñ���
 * @param mixed $value ����ֵ
 * @return mixed
 */
function C($name=null, $value=null) {
    static $_config = array();
    
	// �޲���ʱ��ȡ����
    if (empty($name)) {
        return $_config;
    }
    
    //�ַ��� ����ִ�����û�ȡ��ֵ
    if (is_string($name)) {
        if (!strpos($name, '.')) {	//û��������
            $name = strtolower($name);
            if (is_null($value))
                return isset($_config[$name]) ? $_config[$name] : null;
            $_config[$name] = $value;
            return;
        }
        // ��ά�������úͻ�ȡ֧��   ��������.�����
        $name = explode('.', $name);   //tag.route_check
        $name[0]   =  strtolower($name[0]);
        if (is_null($value))
            return isset($_config[$name[0]][$name[1]]) ? $_config[$name[0]][$name[1]] : null;
        $_config[$name[0]][$name[1]] = $value;
        return;
    }
    // ����
    if (is_array($name)){
        $_config = array_merge($_config, array_change_key_case($name));
        return;
    }
    return null; // ����Ƿ�����
}

/**
 * ��ȡ���������Զ���(�����ִ�Сд)
 * @param string|array $name ���Ա���
 * @param string $value ����ֵ
 * @return mixed
 */
function L($name=null, $value=null) {
    static $_lang = array();
    // �ղ����������ж���
    if (empty($name))
        return $_lang;
    
	// �ж����Ի�ȡ(������)
    if (is_string($name)) {
        $name = strtoupper($name);
        if (is_null($value))
            return isset($_lang[$name]) ? $_lang[$name] : $name;
        $_lang[$name] = $value; // ���Զ���
        return;
    }
    // �������壬תΪ��д�ϲ�
    if (is_array($name))
        $_lang = array_merge($_lang, array_change_key_case($name, CASE_UPPER));
    return;
}
