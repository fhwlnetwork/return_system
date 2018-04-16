
<?php
require_once './lib/init.php';
require_once './lib/page.class.php';
require_once 'head.php';
?>
<?php
//
$pageSize=6;        //每页显示的记录数
$sql="select * from work_information ;";
$_mySQLi = $dao;
$totalRows=$_mySQLi->getResultNum($sql);   //总记录数
$totalPage=ceil($totalRows/$pageSize);  //总页数
$page=isset($_REQUEST['page'])?(int)$_REQUEST['page']:1;//当前页数
if($page<1||$page==null||!is_numeric($page)){
    $page=1;
}
if($page>=$totalPage)$page=$totalPage;
$offset=($page-1)*$pageSize;

$sql="select * from work_information  limit {$offset},{$pageSize}";
$result=$_mySQLi->fetchAll($sql);

?>
<body class="archive category category-gsh category-1">
<?php require 'top.php' ?>
<div class="container">
    <div class="row">
        <div class="section-title"><h3 class="center">招聘信息</h3></div>
        <div class="col-md-9">
            <!-- Blog-Box -->
            <?php
            $sql = "select * from work_information ORDER BY ctime";
            $work=$dao->fetchAll($sql);
            ?>
            <?php foreach ($work as $works):?>
                <section>
                    <div class="post-box fadeInUp animated">
                        <div class="col-sm-3 post-img">
                            <img itemprop="image" class="media-object" src="picture/thumb-4.jpg" />
                        </div>
                        <div class="col-sm-9 post-item">
                            <h3><a href="#" target="_blank" rel="bookmark" title="带弹幕特效的清新博客主题bulletin免费发布" ><?php echo $works['work_name'];?></a></h3>
                            <p class="post-item-text">
                                <?php echo $works['require'];?>
                            </p>
                            <div class="post-item-info">
                                <span class="post-item-author"><i class="fa fa-mortar-board"></i><?php echo $works['salary'];?></span>
                                <span class="post-label"><a href="#gsh" title="故事"><i class="fa fa-list-ul"></i><?php echo $works['mid'];?></a></span>
                                <span class="tm"><i class="fa fa-clock-o"></i><?php echo $works['ctime'];?></span>
<!--                                <span class="count"><i class="fa fa-eye"></i>985</span>-->
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
