<!DOCTYPE html>
<html>
<head>
	<title>登陆</title>
	<meta charset="utf-8">
	<style type="text/css">
	body{
		 /*background: #4d5e70;*/
        background: url('./image/homepage.jpg');
	}


	 h1{
            text-align: center;
            color: #777;
            margin-top: -20px;
            margin-left: -20px;
        }

     a{
     	color: #449FE5;
        text-align: right;
     }

	.content{
		
		width: 510px;
		height: 340px;
		border: 1px solid black;
		margin: 100px auto;
		display: table;
		background: url('bg.jpg');
        opacity: 0.75;

	}

	.login{
		background: url('./image/bg.jpg');
		text-align: center;
		/*相当于把login盒子当作一个<td>或者<th>*/
		display: table-cell;
		vertical-align: middle;
		margin-left:50px; 

	}
	.login input{
		height: 25px;
		border-radius:10px;

	}
	button{
		background: #449FE5;
        text-align: right;
	}


	.btn26
	{
		width:85px;
		height:45px;
		line-height:18px;
		font-size:18px;
		background:url("./image/bg26.jpg") no-repeat left top;
		color:#FFF;
		padding-bottom:4px;
		border-radius:10px;
	}
	</style>
	
</head>
<body>
	<div class="content">

		<div class="login">
			<h1>管理系统登陆</h1> 
		<form name="LoginForm" action="login_check.php" method="post" accept-charset="utf-8" style="margin-left:200px; " onSubmit="return InputCheck(this)">
			<a>用户名：</a><input type="text" name="username"><br><br>
			<a>密&nbsp;码：</a><input type="password" name="pwd"><br>
			<br>

	
			 &nbsp;<input type="submit" class="btn26" value="登陆" name="submit" onmouseover="this.style.backgroundPosition='left -36px'" onmouseout="this.style.backgroundPosition='left top'" style="margin-right: 10px" />

			  <input type="reset" class="btn26" value="重置" onmouseover="this.style.backgroundPosition='left -36px'" onmouseout="this.style.backgroundPosition='left top'" />
		</form>

	
		</div>
		</div>
	</div>



</body>
</html>