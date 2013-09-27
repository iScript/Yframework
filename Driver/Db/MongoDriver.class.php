<?php 
class MongoDriver{ 

    public static $_instance;	//单例模式实例化对象
    public $dbLink;				//数据库连接ID
    public $_mongo;				//mongo实例化对象
	public static $config;		//配置
	
	public function __construct() {
		try {
			$this->_mongo = new MongoClient('mongodb://root:123456@122.228.236.110/admin');
        }catch(MongoConnectionException $e) {
			exit("Failed to connect to database :".$e->getMessage());
		}
		$this->dbLink = $this->_mongo->selectDB("zts");
	}
	
    // Select Collection
    public function selectCollection($collection) {
        return $this->dbLink->selectCollection($collection);
    }
	
	 /**
     * 查询一条记录
     * @access public
     * @param string $collnections    集合名称(相当于关系数据库中的表)
     * @param array  $query            查询的条件array(key=>value) 相当于key=value
     * @param array  $filed            需要列表的字段信息array(filed1,filed2)
     * @return array
     */
    public function findOne($collnections, $query, $filed=array()) {
        return $this->selectCollection($collnections)->findOne($query, $filed);
    }
	
	/**
     * 查询多条记录
     * @access public
     * @param string $collnections    	集合名称(相当于关系数据库中的表)
     * @param array  $query          	查询的条件array(key=>value) 相当于key=value
     * @param array  $filed             需要列表的字段信息array(filed1,filed2)
     * @param array  $result_condition  mongodb中skip，limit等
	 * @return array
     */
    public function find($collnections, $query=array(), $filed=array() ,$result_condition=array() ) {
        $result = array();
		
		if(!count($query) && !count($filed)){
			$cursor = $this->selectCollection($collnections)->find();
		}else{
			$cursor = $this->selectCollection($collnections)->find($query, $filed);
        }
		
		if (!empty($result_condition['skip'])){  
            $cursor->skip($result_condition['skip']);  
        }  
        if (!empty($result_condition['limit']))  {  
            $cursor->limit($result_condition['limit']);  
        }  	
        if (!empty($result_condition['sort']))  {  
            $cursor->sort($result_condition['sort']);  
        }  
		
		while ($cursor->hasNext()) {
            $result[] = $cursor->getNext();
        }
        
		return $result;
    }
	
	/**
     * 插入数据
     * @access public
     * @param string    	$collnections    集合名称(相当于关系数据库中的表)
     * @param array        	$data_array
     * @return boolean		
     */
    public function insert($collnections, $data_array) {
	   return $this->selectCollection($collnections)->insert($data_array);
    }
	
	/**
     * 更改数据
     * @access public
     * @param string    	$collnections    集合名称(相当于关系数据库中的表)
     * @param array        	$query
     * @param array        	$update_data    $set=>array()
     * @param array     	$options	
     * @return boolean
     */
    public function update($collection, $query, $update_data, $options=array('safe'=>true,'multiple'=>true)) {
        return $this->selectCollection($collection)->update($query, $update_data, $options);
    }
	
	/**
     * 删除数据
     * @access public
     * @param string    $collnections    集合名称(相当于关系数据库中的表)
     * @param array     $query
     * @param array     $option			 
     * @return unknow
     */
    public function remove($collection, $query, $option=array("justOne"=>false)) {
        return $this->selectCollection($collection)->remove($query, $option);
    }
	
	/**
     * MongoId
     * @param string $id
     * @return MongoId
     */
    public static function MongoId($id = null)
    {
        return new MongoId($id);
    }
	
	/**
     * MongoTimestamp
     */
    public static function MongoTimestamp($sec = null, $inc = 0){
        if (!$sec) $sec = time();
        return new MongoTimestamp($sec, $inc);
    }
		
	/**
     * GridFS 
     */
    public function getGridFS($prefix = 'fs'){
        return $this->dbLink->getGridFS($prefix);
    }
	
	/**
     * count    
     * @return count
     */
	public function count($collection){
		return $this->selectCollection($collection)->count();
	}
		
	/**
     * 析构函数
     * @access public
     * @return void
     */
    public function __destruct() {
        if ($this->_mongo) {
            $this->_mongo->close();
        }
    }
	
	/**
     * 本类单例实例化函数
     * @access public
     * @param array $params 数据库连接参数,如数据库服务器名,用户名,密码等
     * @return object
     */
    /*
    public static function getInstance() {
		if(null === self::$config){
			self::$config = include ROOT . 'config/mongo_config.php';
		}
		
        if (!self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    */
}