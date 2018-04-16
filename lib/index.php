<?php
	header("content-type:text/html;charset=utg-8");
	//使用DAOMySQLi.calss.php
	require mysqli.php;

	$option = array(
		'host'=>'localhost',
		'user'=>'root',
		'pwd'=>'root',
		'dbname'=>'ts_returnsystem',
		'port'=>'3306',
		'charset'=>'utf8'
		);
	$dao_mysqli = DAOMySQLi::getSingleton($option);
	//使用select语句ok
	$sql ="select * from 'user_tea'";
	$user_list = $dao_mysqli->fetchaAll($sql);
	echo '<br>';
	//取出$user_list的记录
	foreach($user_list as $user){
		echo '<br> id='.$user['id'].'name='.$user['name'].'email='.$user['email'];
	}
	var_dump($user_list);
?>