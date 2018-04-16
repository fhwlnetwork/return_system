<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <meta name="renderer" content="webkit">
    <title>网站信息</title>  
    <link rel="stylesheet" href="css/pintuer.css">
    <link rel="stylesheet" href="css/admin.css">
    <script src="js/jquery.js"></script>
    <script src="js/pintuer.js"></script>  
</head>
<body>
<?php require_once  '../lib/init.php';?>

<div class="panel admin-panel">

  <div class="panel-head"><strong><span class="icon-pencil-square-o"></span> 个人信息</strong></div>
  <div class="body-content">

    <form method="post" class="form-x" action="">

<?php
$user = $_SESSION['username'];

$sql="select * from manager where username='$user'";
$result =$dao->fetchAll($sql);

?>
        <?php
        foreach ($result as $list):?>
      <div class="form-group">
        <div class="label">
          <label>姓名：</label>
        </div>
        <div class="field" style="margin-top:8px;">
          <?php echo $list['person_name'];?>

          <div class="tips"></div>
        </div>
      </div>
      <div class="form-group" style="margin-top:8px;">
        <div class="label">
          <label>手机：</label>
        </div>
        <div class="field" style="margin-top:8px;">
           <?php echo $list['mobile_phone'];?>
        </div>
      </div>
      <div class="form-group" >
        <div class="label">
          <label>邮箱：</label>
        </div>
        <div class="field" style="margin-top:8px;">
            <?php echo $list['email'];?>
        </div>
      </div>
        <div class="form-group" >
                <div class="label">
                    <label>民族：</label>
                </div>
                <div class="field" style="margin-top:8px;">
                    <?php echo $list['nation'];?>
                </div>
            </div>

            <div class="form-group">
        <div class="label"   >
          <label>性别：</label>
        </div>
        <div class="field" style="margin-top:8px;">
            <?php echo $list['sex'];?>
        </div>
      </div>


      <div class="form-group" >
        <div class="label" >
          <label>身份证：</label>
        </div>
        <div class="field" style="margin-top:8px;">
            <?php echo $list['id_number'];?>
          <div class="tips"></div>
        </div>
      </div>
      <div class="form-group" style="margin-top:8px;">
        <div class="label">
          <label>专业：</label>
        </div>

        <div class="field" style="margin-top:8px;">
            <?php
            $id2=$list['major_id'];

            $sql="select * from major WHERE id='$id2'";
            $rows=$dao->fetchAll($sql);
            foreach($rows as $key=>$v){
                echo $v['major_name'];
            }
            ?>

          <div class="tips"></div>
        </div>

      </div>
      <div class="form-group">
        <div class="label">
          <label>入校时间：</label>
        </div>
        <div class="field" style="margin-top:8px;">
          <?php echo date('Y-M-D',$list['begin_time']) ;?>
          <div class="tips"></div>
        </div>
      </div>
      <div class="form-group">
        <div class="label">
          <label>毕业时间：</label>
        </div>
        <div class="field" style="margin-top:8px;">
            <?php echo date('Y-M-D',$list['stop_time']); ?>
          <div class="tips"></div>
        </div>
      </div>
        <?php endforeach;?>

    </form>
  </div>

</div>
</body></html>