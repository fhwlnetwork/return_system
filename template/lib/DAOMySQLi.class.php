<?php

	//开发一个dao数据库操作类【单侧模式】【oop,mysqli编程思想】
	class DAOMySQLi{
		//dao应该有哪些属性
		private $_host;
		private $_user;
		private $_pwd;
		private $_dbname;
		private $_port;
		//字符集设置
		private $_charset;
		//有一个mysqli对象实例
		public $_mySQLi;
		//有第一个唯一实例的属性
		private static $_instance;

		//该函数完成一个初始化成员属性的任务
		private function _initOption (array $option =array()){

			//将传入的数组$option的值赋给成员属性并简单核验
			$this->_host = isset($option['host']) ? $option['host'] : '';
			$this->_user = isset($option['user']) ? $option['user'] : '';
			$this->_pwd = isset($option['pwd']) ? $option['pwd'] : '';
			$this->_port = isset($option['port']) ? $option['port'] : '';
			$this->_dbname = isset($option['dbname']) ? $option['dbname'] : '';
			$this->_charset = isset($option['charset']) ? $option['charset'] : '';
			
			if ($this->_host == '' || $this->_user == '' || $this->_pwd == '' || $this->_dbname == '' || $this->_port== '' || $this->_charset == '' ) {
				die('你传入的参数有问题，请重新输入');
			}
		}

		//写一个函数，完成——mySQLi对象初始化
		private function _initMySQLi() {
			
			//创建mysqli对象实例
			$this->_mySQLi = new MySQLi($this->_host,$this->_user,$this->_pwd,$this->_dbname,$this->_port);
			
			if($this->_mySQLi->connect_errno){
				die('你的链接失败，错误信息'.$this->_mySQLi->connect_error);
			}
			
			//设置字符集
			$this->_mySQLi->set_charset($this->_charset);
		}

		//构造函数，完成初始化
		private function __construct(array $option = array()){
			
			
			
			//该函数完成一个初始化成员属性的任务
			$this->_initOption($option);
			$this->_initMySQLi();


		}

		//对外提供一个静态的public方法，可以返回一个唯一的对象实例
		public static function getSingleton(array $option = array()){
			//如果还没有创建这个实例对象
			if(!(self::$_instance instanceof self)){
				//创建对象实例，这里就是构造函数
				self::$_instance = new self($option);
			}
			//返回对象实例
			return self::$_instance;
		}
		//防止克隆
		private function _clone(){

		}
//		查询的时候直接用这个
		//对外提供一个方法（接口），返回一条记录
		public function fetchRow($sql = ' ' ){
			//因为只有一条记录，直接给一个数组即可
			$arr = array ();
			$res =$this->_mySQLi->query($sql);
			
			if ($row = $res->fetch_assoc()) {
					$arr =$row;
				}
				//立即释放$res；
				$res->free();
				return $arr;
		}

		//对外提供一个方法（接口），完成查询任务（select）
		public function fetchAll($sql=''){
			//为了达到这样的目录
			/*
				1.立即释放$res
				2.需要把这个结果返回给调用文件使用
				思路是将$res 记录封到一个数组中，将数组返回
				定义一个空数组，准备装记录
			 */
			$arr = array();
			$res =$this->_mySQLi->query($sql);
			//var_dump($res);exit;
			while ($row = $res->fetch_assoc()) {
				$arr[]=$row;
			}
			//立即释放$res;
			$res->free();
			return $arr;
		}
		//对外提供一个dml操作的方法（接口），完成（dml操作）
		public function query($sql = ''){
			$res = $this->_mySQLi->query($sql);
			if(!$res){
				echo '<br>执行sql语句失败';
				echo '错误信息',$this->_mySQLi->error;
				exit;
			}
			return $res;
		}

    //返回受影响数据行数
    function getResultNum($sql){
        $res=$this->_mySQLi->query($sql);
        return mysqli_num_rows($res);
    }




	}
?>