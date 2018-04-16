
<?php
require_once './lib/init.php';
require_once './lib/page.class.php';
require_once 'head.php';
?>
<?php
//
$pageSize=6;        //每页显示的记录数
$sql="select * from stu_pub_center where status=1;";
$_mySQLi = $dao;
$totalRows=$_mySQLi->getResultNum($sql);   //总记录数
$totalPage=ceil($totalRows/$pageSize);  //总页数
$page=isset($_REQUEST['page'])?(int)$_REQUEST['page']:1;//当前页数
if($page<1||$page==null||!is_numeric($page)){
    $page=1;
}
if($page>=$totalPage)$page=$totalPage;
$offset=($page-1)*$pageSize;

$sql="select * from stu_pub_center where status=1 limit {$offset},{$pageSize}";
$result=$_mySQLi->fetchAll($sql);

?>
<body class="archive category category-gsh category-1">
<?php require 'top.php' ?>
<div class="container">
    <div class="row">
        <div class="section-title"><h3 class="center">学生做品</h3></div>
        <div class="col-md-9">
            <!-- Blog-Box -->
            <?php
            $sql = "select * from stu_pub_center where status=1 ORDER BY ctime DESC ";
            $pub_center=$dao->fetchAll($sql);
            ?>
            <?php foreach ($pub_center as $center):?>
                <section>
                    <div class="post-box fadeInUp animated">
                        <div class="col-sm-3 post-img">
                            <img itemprop="image" class="media-object" src="../admin/web/<?php echo $center['pic'];?>" />
                        </div>
                        <div class="col-sm-9 post-item">
                            <h3><a href="./template/pub_centerpage.php?r=<?php echo $center['id'];?>" target="_blank" rel="bookmark" title="<?php echo $center['title'];?>" ><?php echo $center['title'];?></a></h3>
                            <p class="post-item-text">
                                <?php echo $center['content'];?>
                            </p>
                            <div class="post-item-info">
                                <span class="post-item-author"><i class="fa fa-mortar-board"></i><?php echo $center['stu_name'];?></span>
                                <span class="post-label"><a href="#" title="故事"><i class="fa fa-list-ul"></i><?php echo $center['remark'];?></a></span>
                                <span class="tm"><i class="fa fa-clock-o"></i><?php echo date('y-m-d',$center['ctime']);?></span>

                            </div>
                        </div>

                    </div>
                </section>
            <?php endforeach;?>
            <div style="margin-left: 30%;"><?php echo showPage($page,$totalPage);  //输出页码链接?></div>

        </div>

        <!--右边导航-->

        <?php require 'right.php';?>

        <!---版权-->
        <?php require 'footer.php';?>
        <!--回到顶部-->
        <?php require 'back_to_top.php';?>
</body>
</html>
