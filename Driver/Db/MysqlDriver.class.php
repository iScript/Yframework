<?php

/**
 * 使用mysql_函数的Mysql Driver
 * 建议封装一层对此类的调用，不建议直接调用该接口
 */
class MysqlDriver{

	private static $_instance;		//单例句柄
	private static $_cfg;			//数据库配置
	private $dbLink = array();		//这个数组里有mysql链接标示
	private $link = null;			//mysql链接标示
	private $dbname = null;
	public static $query_num;

	/**
	 * 获取单例对象
	 * @return Core_Db_Driver_Mysql
	 */
	public static function getInstance($cfg)
	{	
		if (self::$_instance)
		{
			return self::$_instance;
		}
		self::$_cfg = $cfg;
		self::$_instance = new self();
		return self::$_instance;
	}

	/**
	 * 创建数据库链接
	 * 只有在需要创建时才创建，最小化调用
	 * @param bool $read 是否只读链接
	 * @return resource 返回link_source
	 * 
	 */
	private function getdbLink()
	{
		$dbname = self::$_cfg['dbName'];

		$config = array(
			'dbhost' => self::$_cfg['dbHost'],
			'dbuser' => self::$_cfg['dbUser'],
			'dbpwd' => self::$_cfg['dbPwd'],
			'dbpconnect' => self::$_cfg['dbPconnect'],
		);
		
		$key = implode('_', $config);
		if (!isset($this->dbLink[$key]) || !is_resource($this->dbLink[$key]))
		{
			if ($config['dbpconnect'])	//持久链接
			{
				$this->dbLink[$key] = mysql_pconnect($config['dbhost'], $config['dbuser'], $config['dbpwd'], true) or die('Connect Db Error');
			}
			else						//非持久链接
			{
				$this->dbLink[$key] = mysql_connect($config['dbhost'], $config['dbuser'], $config['dbpwd'], true) or die('Connect Db Error');
			}
			if(mysql_get_server_info($this->dbLink[$key]) >= '4.1')
			{
				mysql_query('SET character_set_connection=utf8,character_set_results=utf8,character_set_client=binary,sql_mode=\'\'', $this->dbLink[$key]);
			}
		}
		
		if ($this->dbname[$key] != $dbname)
		{
			mysql_select_db($dbname, $this->dbLink[$key]) or die('Can\'t use ' . $dbname);
			$this->dbname[$key] = $dbname;
		}
		return $this->link = $this->dbLink[$key];
	}

	/**
	 * 执行一条SQL 查询
	 * @param string $sql sql语句，需要做安全转义
	 * @param bool $master 是否查询主库
	 * @return resource 返回link_source
	 * 
	 */
	public function query($sql)
	{
		$link = $this->getdbLink();

		if (!$source = mysql_query($sql, $link))
			$this->halt($sql, $link);
		++self::$query_num;
		return $source;
	}


	/**
	 * 对查询的字符串进行安全转义
	 * @param string $str
	 * @return string
	 * 
	 */
	public function escape($str)
	{
		return addslashes($str);
	}

	/**
	 * 释放结果集
	 *
	 * @param resource $resource 结果集
	 * 
	 */
	public function free($resource)
	{
		if (is_resource($resource))
		{
			mysql_free_result($resource);
		}
	}


	/**
	 * 快捷的获得一个字段信息
	 *
	 * @param string $sql sql语句，需要做安全转义
	 * @return mix|null
	 */
	public function getOne($sql)
	{
		$resource = $this->query($sql);
		if ($resource)
		{
			$row = mysql_fetch_array($resource, MYSQL_NUM);
			$this->free($resource);
			return $row[0];
		}
		else
		{
			return null;
		}
	}



	/**
	 * 快捷的获得一条记录
	 *
	 * @param string $sql sql语句，需要做安全转义
	 * @return array|null
	 * 
	 */
	public function findOne($sql)
	{
		$resource = $this->query($sql);
		if ($resource)
		{
			$row = array();
			$row = mysql_fetch_array($resource, MYSQL_ASSOC);
			$this->free($resource);
			return $row;
		}
		else
		{
			return array();
		}
	}
	
	
	public function find($sql)
	{
		$resource = $this->query($sql);
		if ($resource)
		{
			$row = array();
			$row = mysql_fetch_array($resource, MYSQL_ASSOC);
			$this->free($resource);
			return $row;
		}
		else
		{
			return array();
		}
	}



	/**
	 * 获取自增的id
	 * @param bool $bigint 是否bigint 列
	 * @return number
	 * 
	 */
	public function insertId($bigint = false)
	{
		if ($bigint)
		{
			$r = mysql_query('Select LAST_INSERT_ID()', $this->link);
			$row = mysql_fetch_array($r, MYSQL_NUM);
			return $row[0]; //bigint 列
		}
		else
		{
			return mysql_insert_id($this->link);
		}
	}

	/**
	 * update 快捷操作
	 *
	 * @param string $table 操作的表名
	 * @param string $where where子句
	 * @param array $array 操作的数据关联数组 无需转移，使用原始数据
	 * @param array $safe 安全限制数组
	 * @param array $unset 不做单引号环绕的数组
	 * @return bool
	 * 
	 */
	public function update($table, $where, $array, $option = array() )
	{
		$_where = $this->filterWhere($where);
		$set = $this->createset($array);
		$sql = "Update $table Set $set Where $_where";
		return $this->query($sql, true);
	}



	/**
	 * insert 快捷操作
	 * @param string $table 操作的表名
	 * @param array $array 操作的数据关联数组 无需转移，使用原始数据
	 * @return number
	 * 
	 */
	public function insert($table, $array)
	{
		$set = $this->createset($array);
		//标准的SQL语句中Insert应是 insert into $table(列) value(值) ，mysql可以如下这样写
		$sql = "Insert Into $table Set $set";
		if ($resource = $this->query($sql))
		{
			return ($id = $this->insertId()) ? $id : true;
		}
		return false;
	}
	

	/**
	 * 创建安全的set子句
	 * @param array $array 需要创建set子句的关联数组
	 * @param array $safe 安全限制数组，字段列表
	 * @param array $unset 不用单引号环绕的字段列表
	 * @return string
	 * 
	 */
	public function createset($array)
	{
		$_res = array();
		foreach ((array) $array as $_key => $_val)
		{

			$_val = $this->escape($_val);
			$_res[$_key] = "`$_key`='$_val'";
		}
		return implode(',', $_res);
	}
	
	//
	private function filterWhere($array){
		
		if(!is_array($array)) exit("Error:where must be array~");
		$_temp = '1';
		foreach($array as $k=>$v){
			$_temp .= ' and '.$k. '= "'. $v .'"';
		}
		return $_temp;
	}

	/**
	 * 获取记录条数
	 *
	 * @param resource $query
	 * @return Integer
	 */
	function num_rows($query){
		return mysql_num_rows($query);
	}

	/**
	 *  取得前一次 MySQL 操作所影响的记录行数
	 *
	 * @return Integer
	 * 
	 */
	function affected_rows(){
		return mysql_affected_rows();
	}	


	/**
	 * 数据库报错抛出的异常
	 *
	 * @param string $sql 查询的sql语句
	 * @param resource $link 数据库链接资源
	 * 
	 */
	protected function halt($sql, $link)
	{
		exit('[MySQL Query Error] : ' . mysql_error($link) . '<br /> SQL:' . $sql.mysql_errno($link));
	}
	
	/**
	 * 获取服务器信息
	 * @return string
	 *
	 */
	public function server_info()
	{
		return mysql_get_server_info($this->getdbLink(true));
	}

}