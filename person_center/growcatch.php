<?php  require_once '../lib/init.php';

$growid=$_GET['r'];
$user = $_SESSION['username'];
$sql="select * from stu_works where id=$growid";
$work=$dao->fetchRow($sql);



?>


<table width="675" height="480" border="0">
    <tr>
        <td width="199">公司名称</td>
        <td width="389"><?php echo $work['company_name'];?></td>
    </tr>
    <tr>
        <td>工作名称</td>
        <td>&nbsp;<?php echo $work['work_name'];?></td>
    </tr>
    <tr>
        <td>薪资范围</td>
        <td>&nbsp;<?php echo $work['salary'];?>元</td>
    </tr>
    <tr>
        <td>毕业去向代码</td>
        <td>&nbsp;<?php echo $work['Byqxdm'];?></td>
    </tr>
    <tr>
        <td>单位组织机构代码</td>
        <td>&nbsp;<?php echo $work['Dwzzjgdm'];?></td>
    </tr>
    <tr>
        <td>单位性质代码</td>
        <td>&nbsp;<?php echo $work['Dwxzdm'];?></td>
    </tr>
    <tr>
        <td>单位行业代码</td>
        <td>&nbsp;<?php echo $work['Dwhydm'];?></td>
    </tr>
    <tr>
        <td>单位所在地代码</td>
        <td>&nbsp;<?php echo $work['Dwszddm'];?></td>
    </tr>
    <tr>
        <td>工作职位类别代码</td>
        <td>&nbsp;<?php echo $work['Gzzwlbdm'];?></td>
    </tr>
    <tr>
        <td>开始时间</td>
        <td>&nbsp;<?php echo date('Y年m月d日',$work['ctime']);?></td>
    </tr>
    <tr>
        <td>结束时间</td>
        <td><?php echo date('Y年m月d日',$work['utime']);?></td>
    </tr>

</table>

