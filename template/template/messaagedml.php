<?php
/**
 * Created by PhpStorm.
 * User: jh
 * Date: 2018/3/30
 * Time: 11:11
 */
header("Content-type: text/html; charset=utf-8");
require_once './lib/init.php';

$username=$_POST['user_name'];
$uid=$_POST['id'];
$content=$_POST['contents'];
$time=time();
$person=$_POST['personame'];


$sql="INSERT INTO `message` VALUES ('', $uid,$username,'$person','','$content','', $time,'','','')";
$message=$dao->query($sql);
header ('refresh:0;url=../index.php?r=message');




