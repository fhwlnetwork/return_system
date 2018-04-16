<?php  require_once '../lib/init.php';

$growid=$_GET['id'];
echo $growid;
$user = $_SESSION['username'];
$sql="select * from stu_works where stu_name='$user'";
$work=$dao->fetchAll($sql);


?>