<?php
require_once './hash.php';
	header("content-type:text/html;charset=utf-8");
	require  './lib/init.php';

	$username= isset($_POST['username']) ? $_POST['username'] : '';


//	需要添加hash加密验证
$password= isset($_POST['pwd'])  ? $_POST['pwd'] : '';
if (!empty($_POST)) {

    if (!is_string($password) || $password === '') {
        //密码不能为空;
    }
    //这一块数据查询，

    if($usernmae='' || $pwd='' ){
        header ('refresh:2;url=login.php');
        echo "please input your passwd nd username";
    }else  {
        $sql = "SELECT * FROM `manager` WHERE username= '$username'";

        $mysqli=$dao->fetchRow($sql);
        if ( $mysqli=$dao->fetchRow($sql) ){//验证超级管理员
            $hash = $mysqli['password_hash'];

            if (!preg_match('/^\$2[axy]\$(\d\d)\$[\.\/0-9A-Za-z]{22}/', $hash, $matches)
                || $matches[1] < 4
                || $matches[1] > 30
            ) {
                //不是有效的hash
                //throw new InvalidParamException('Hash is invalid.');
            }

            if (function_exists('password_verify')) {
                return password_verify($password, $hash);
            }

            $test = crypt($password, $hash);
            $n = strlen($test);
            if ($n !== 60) {
                return false;
            }
            $hashes = new hash();
            $flag = $hashes->compareString($test, $hash);
            header ('refresh:0;url=index.php');
            $_SESSION['username']=$mysqli['username'];
            $_SESSION['person_name']=$mysqli['person_name'];
            $_SESSION['id']=$mysqli['id'];


        } else{
            echo "用户名或密码不对";
        }

        $user_list=$dao->fetchRow($sql);


    }


}



	
	?>
