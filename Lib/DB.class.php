<?php

/**
 * 数据库对外接口方法
 */
class DB
{
	
	protected static $config = null;
	
	/**
	 * 快速从数据表中读取一行
	 * @param string $sql 需要转义！
	 * @param bool $master 是否读取主库
	 * @return array 
	 */
	public static function findOne($sql)
	{
		return self::D()->findOne($sql);
	}

	public static function find($sql)
	{
		return self::D()->find($sql);
	}

	public static function getOne($sql)
	{
		return self::D()->getOne($sql);
	}
	



	/**
	 * 直接执行一个SQL
	 * 
	 * @param string $sql 主义需要转移
	 * @param bool $master 是否读取主库
	 * @return resource
	 */
	public static function query($sql)
	{
		return self::D()->query($sql);
	}

	/**
	 * 指定一个关联数组 比如
	 * array('field1'=>1,'field2'=>2)
	 * key是字段名称，val是字段值
	 * 向一个Table中插入一行
	 * 只有在$safe中允许的key，才会被插入。
	 * @param string $table 数据表名称
	 * @param array $array	插入的数据，看例子	不要转义数据，系统会自动转义，否则会被转义两次
	 * @param array $safe 安全限制数组，留空不限制
	 * @return insertid|bool
	 */
	public static function insert($table, $array)
	{
		return self::D()->insert($table, $array);
	}



	/**
	 * Update操作的快捷封装
	 *
	 * @param string $table	数据表名称
	 * @param string $where	where条件
	 * @param array $array	更新的关联数组	不要转义数据，系统会自动转义，否则会被转义两次
	 * @param array $option 额外选项
	 * @return bool
	 */
	public static function update($table, $where, $array,$options = array())
	{
		return self::D()->update($table, $where, $array,$options = array());
	}
	


	/**
	 * 获取 insert 时产生的 auto_incret ID
	 * @return number
	 */
	public static function insertId()
	{
		return self::D()->insertId();
	}

	/**
	 * 对字符串进行转移，保证入库安全
	 * @param string $str
	 * @return mix
	 */
	public static function sqlescape($str)
	{
		if (is_array($str))
		{
			foreach ($str as $_key => $_var)
			{
				$str[$_key] = self::sqlescape($_var);
			}
			return $str;
		}
		else
		{
			return self::D()->escape($str);
		}
	}

	/**
	 * 获取MySQL Driver对象
	 * @return Db_Driver_Mysql
	 */
	public static function D()
	{	
		$cfg = self::getConfig();
		$class = ucfirst($cfg['dbtype'])."Driver";
		$file = DRIVER_PATH.'Db/' .$class.'.class.php';
		require_once $file;
		//php5.3 才支持 $class::getInstance()
		$return = MysqlDriver::getInstance($cfg);
		return $return;
	}
	
	/**
	 * 取得前一次 MySQL 操作所影响的记录行数
	 * @return integer
	 */
	public static function affected_rows()
	{
		return self::D()->affected_rows();
	}


	/**
	 * 获取记录数
	 *
	 * @param resource $query
	 * @return Integer
	 */
	public static function num_rows($query)
	{
		return self::D()->num_rows($query);
	}
	
	/**
	 * 获取数据库服务器信息
	 *
	 * @return mixed 
	 */
	public static function server_info()
	{
		return self::D()->server_info();	
	}
	

	

	/**
	 * 初始化数据库配置
	 * @return array
	 */
	protected static function getConfig()
	{
		if(null === self::$config)
		{
			self::$config = array (
				'dbtype'    =>  C('DB_TYPE'),
				'dbUser'  	=>  C('DB_USER'),
				'dbPwd'  	=>  C('DB_PWD'),
				'dbHost'  	=>  C('DB_HOST'),
				'dbPort'  	=>  C('DB_PORT'),
				'dbName'  	=>  C('DB_NAME'),
				'dbPrefix'	=>	C('DB_PREFIX'),
				'dbCharset'	=>	C('DB_CHARSET'),
				'dbPconnect'=>	C('DB_PCONNECT'),
			);
		}
		return self::$config;
	}

}