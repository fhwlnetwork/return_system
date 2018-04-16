<?php
header("content-type:text/html;charset=utf-8");

//这个文件是init.php，完成初始化工作。

//定义网站的根目录
define ('ROOT_PATH', dirname(__DIR__) . '/');
//定义lib工具类目录
define ('LIB_PATH',ROOT_PATH.'lib'.'/');
//echo LIB_PATH;
//echo ROOT_PATH;
//define("TEMP_PATH", ROOT_PATH."template".DIRECTORY_SEPARATOR);

/*---------开始定义前台信息-------------------------*/

//定义前台页面目录
define ("WEB", ROOT_PATH.DIRECTORY_SEPARATOR);
//定义前台模板template目录，模板目录
define ('TMP_PATH',ROOT_PATH .'template/');
//定义前台css目录
define ("CSS", TMP_PATH."css".'/');


/*------------前台目录定义结束------------------------------------*/


$php_script = isset($_GET['r'])?$_GET['r']:"index";

// $a="test";



//定于链接数据库属性
$option = array(
    'host'=>'localhost',
    'user'=>'root',
    'pwd'=>'root',
    'dbname'=>'ts_returnsystem',
    'port'=>3306,
    'charset'=>'utf8'
);

//引入DAOMYSQLi.class.php文件，数据库工具类
require LIB_PATH . 'DAOMySQLi.class.php';
$dao = DAOMySQLi::getSingleton($option);
//启动session
session_start();
?>
