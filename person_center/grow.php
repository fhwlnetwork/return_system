<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <meta name="renderer" content="webkit">
    <title></title>
    <link rel="stylesheet" href="css/pintuer.css">
    <link rel="stylesheet" href="css/admin.css">
    <script src="js/jquery.js"></script>
    <script src="js/pintuer.js"></script>



</head>
<body>
<div class="panel admin-panel">
    <div class="panel-head"><strong><span class="icon-key"></span> 成长路线</strong></div>
    <div class="body-content">
        <form method="post" class="form-x" action="">
            <div class="form-group">
                <table width="1015" border="0" slid="0">
                    <tr style="font-size:20px;">
                        <td width="213" height="50px">公司名称</td>
                        <td width="189">工作名称</td>
                        <td width="156">薪资范围</td>
                        <td width="157">开始时间</td>
                        <td width="107">结束时间</td>
                        <td width="64">操作</td>
                        <td width="83">&nbsp;</td>

                    </tr>

                    <?php  require_once '../lib/init.php';
                    $user = $_SESSION['username'];
                    $sql="select * from stu_works where stu_name='$user'";
                    $work=$dao->fetchAll($sql);


                    ?>
                    <?php foreach ($work as $v):?>

                    <tr>
                        <td height="30px;"><?php echo $v['company_name'];?></td>
                        <td><?php echo $v['work_name'];?></td>
                        <td><?php echo $v['salary'];?></td>
                        <td><?php echo date('y-m-d',$v['ctime']) ;?></td>
                        <td><?php echo $v['stop_time'];?></td>
                        <td>

                            <a href="./growcatch.php?r=<?php echo $v['id'] ?>">
                                <input type="button" name="modify"  value="修改" />
                            </a>
                        </td>
                        <td>&nbsp;</td>

                    </tr>
                    <?php endforeach;?>
                </table>
            </div>






        </form>
    </div>
</div>
<?php  require_once '../lib/init.php';
$user = $_SESSION['username'];
$sql="select * from stu_works where stu_name='$user'";
$work=$dao->fetchAll($sql);


?>
</body></html>