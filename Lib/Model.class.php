<?php

/**
 * Model 基类
 * 还没写完，不写了。。。。
 */
class Model{

	protected $db               =   null;	// 当前数据库操作对象
    protected $pk               =   'id';	// 主键名称
    protected $tablePrefix      =   '';		// 数据表前缀
    protected $name             =   '';		// 模型名称
    protected $dbName           =   '';		// 数据库名称
    protected $tableName        =   '';		// 数据表名（不包含表前缀）
    protected $trueTableName    =   ''; 	// 实际数据表名（包含表前缀）
    protected $error            =   ''; 	// 最近错误信息
    protected $fields           =   array();// 字段信息
    protected $data             =   array();// 数据信息
	
	
	// 数据信息
	public function __construct($name='') {
        
        // 获取模型名称
        if(!empty($name)) {
            if(strpos($name,'.')) { // 支持 数据库名.模型名的 定义
                list($this->dbName,$this->name) = explode('.',$name);
            }else{
                $this->name   =  $name;
            }
        }elseif(empty($this->name)){
            $this->name =   $this->getModelName();
        }
        
		//表前缀
		$this->tablePrefix = $this->tablePrefix ? $this->tablePrefix:C('DB_PREFIX');
		
		$this->db(0);
	}
	
	
	    /**
     * 切换当前的数据库连接
     * @access public
     * @param integer $linkNum  连接序号
     * @return Model
     */
    public function db($linkNum=''){

    	if(''===$linkNum && $this->db) {
            return $this->db;
        }
        static $_linkNum    =   array();
        static $_db = array();
        //静态数组中是否有这个链接序号
		if(!isset($_db[$linkNum])) {
            $_db[$linkNum]            =    Db::getInstance();
        }

        // 切换数据库连接
        $this->db   =    $_db[$linkNum];
        // 字段检测
        //if(!empty($this->name) && $this->autoCheckFields)    $this->_checkTableInfo();
        return $this;
    }
	
	/**
     * 得到当前的数据对象名称
     * @access public
     * @return string
     */
    public function getModelName() {
        if(empty($this->name))
            $this->name =   substr(get_class($this),0,-5);
        return $this->name;
    }
	
	/**
     * 查询数据
     * @access public
     * @param mixed $options 表达式参数
     * @return mixed
     */
    public function find($options=array()) {
        if(is_numeric($options) || is_string($options)) {
            $where[$this->pk]  =   $options;
            $options                =   array();
            $options['where']       =   $where;
        }
        // 总是查找一条记录
        $options['limit']   =   1;
        // 分析表达式
        $options            =   $this->_parseOptions($options);
        $resultSet          =   $this->db->select($options);
        if(false === $resultSet) {
            return false;
        }
        if(empty($resultSet)) {// 查询结果为空
            return null;
        }
        $this->data         =   $resultSet[0];
        $this->_after_find($this->data,$options);
        return $this->data;
    }

}