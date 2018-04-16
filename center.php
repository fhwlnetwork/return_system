<?php
	header("content-type:text/html;charset=utf-8");

/**
 * File: center.php
 * User: Joye Chen
 * Date: 2017-11-27
 * Time: 21:30
 */
session_start();
echo '用户中心 -- 你好。。。 '.$_SESSION['username'];