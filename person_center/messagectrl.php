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
<form method="post" action="">
  <div class="panel admin-panel">
    <div class="panel-head"><strong class="icon-reorder"> 留言管理</strong></div>
    <div class="padding border-bottom">
      <ul class="search">
        <li>
          <button type="button"  class="button border-green" id="checkall"><span class="icon-check"></span> 全选</button>
          <button type="submit" class="button border-red"><span class="icon-trash-o"></span> 批量删除</button>
        </li>
      </ul>
    </div>

    <table class="table table-hover text-center">
      <tr>
        <th width="120">ID</th>

        <th>审核状态</th>
        <th>留言内容</th>
        <th>发布时间</th>
        <th>操作</th>       
      </tr>
        <?php
            require_once '../lib/init.php';
            $user = $_SESSION['person_name'];

            $sql = "select * from message where person_name='$user' ";
            $list = $dao->fetchAll($sql);
            foreach( $list as $v ):

        ?>

        <tr>
          <td><input type="checkbox" name="id[]" value="1" /><?php echo $v['id'];?></td>
          <td>
              <?php
                $a=$v['status'];
                if($a=1){
                    echo "已审核";
                }else if ($a=2){
                    echo "未通过";
                }else{echo "待审核";}
                ?>
          </td>
          <td>1<?php echo $v['message'];?></td>
          <td><?php echo date('Y年m月d日',$v['ctime']);?></td>

          <td><div class="button-group"> <a class="button border-red" href="messagedel.php?r=$v[id]" onclick="return del(1)"><span class="icon-trash-o"></span> 删除</a> </div></td>
        </tr>
        <?php endforeach;?>


      <tr>
        <td colspan="8"><div class="pagelist"> <a href="">上一页</a> <span class="current">1</span><a href="">2</a><a href="">3</a><a href="">下一页</a><a href="">尾页</a> </div></td>
      </tr>
    </table>
  </div>
</form>

<!--
<script type="text/javascript">

function del(id){
	if(confirm("您确定要删除吗?")){


	}
}

$("#checkall").click(function(){ 
  $("input[name='id[]']").each(function(){
	  if (this.checked) {
		  this.checked = false;
	  }
	  else {
		  this.checked = true;
	  }
  });
})

function DelSelect(){
	var Checkbox=false;
	 $("input[name='id[]']").each(function(){
	  if (this.checked==true) {		
		Checkbox=true;	
	  }
	});
	if (Checkbox){
		var t=confirm("您确认要删除选中的内容吗？");
		if (t==false){} return false;
	}
	else{
		alert("请选择您要删除的内容!");
		return false;
	}
}

</script>
-->
</body>
</html>