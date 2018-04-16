<?php
	header("content-type:text/html;charset=utf-8");

	//使用DAOMySQLi.calss.php
	//
	require './init.php';

	$dao_mysqli = DAOMySQLi::getSingleton($option);
	
	/*$sql= "insert into super_user_table values('wjh','123456','123','123','123',0)";
	if($dao_mysqli->query($sql)){
		echo 'success';
	}*/
	
	//使用select语句ok
	$sql ="SELECT * FROM super_user_table";
	$user_list = $dao_mysqli->fetchAll($sql);
	echo '<pre>';
	//var_dump($user_list);
	//取出$user_list的记录
	foreach($user_list as $user){
		echo '<br> id='.$user['super_user'].'name='.$user['super_passwd'].'email='.$user['super_email'];
	}
	
?>