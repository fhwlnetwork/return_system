﻿<!DOCTYPE html>
<html lang="zh-CN">
<?php
require_once 'head.php';
require_once './lib/init.php';
?>

<body class="page-template-default page page-id-40">
<!-- Fixed navbar -->
<?php require 'top2.php'; ?>
<div class="container content">
    <div class="row">
        <div class="col-md-9">
            <!-- Blog-Box -->
            <?php
            $newsid=$_GET['r'];


            $sql = "select * from worK_information where id =$newsid";
            $pub_center=$dao->fetchAll($sql);

            foreach ($pub_center as $v):
            ?>
            <section class="article">
                <!--- Article-Head -->
                <div class="article-head">

                    <h1 class="article-title text-center"><?php echo $v['work_name'];?></h1>
                    <div class="article-info">


                        <span class="article-time"><?php echo date('y-m-d',$v['ctime']);?></span>


                    </div>
                </div>





                <div class="comments">
                    <h3>精彩内容</h3>
                    <div id="comments">
                       <div style="margin-left: 25%">
                           <img src="../admin/web/<?php echo $v['pic'];?>" style="height: 300px;width: 400px ">
                       </div>


                    <div id="respond"style="word-break : break-all;" >
                        <h3>工作描述</h3>

                        <!--从数据库中读取文字并自动换行-->
                        <?php
                        $str=$v['desc'];
                        //echo $str.'<br />';
                        $arr=explode("\n",$str);
                        //print_r($arr);
                       // echo count($arr).'<br />';//回车数
                        $str1=nl2br($str);//回车换成换行默认函数
                        echo $str1;
                        ?>



                    </div>


                    <div id="loading-img"><img src="picture/loading_com.gif" alt="loading" /></div>
                    <div id="error-comments"></div>

                </div>

                    <div class="comments">
                        <div id="comments">

                            <div id="respond"style="word-break : break-all;" >
                                <h3>岗位要求</h3>

                                <!--从数据库中读取文字并自动换行-->
                                <?php
                                $str=$v['require'];
                                //echo $str.'<br />';
                                $arr=explode("\n",$str);
                                //print_r($arr);
                                // echo count($arr).'<br />';//回车数
                                $str1=nl2br($str);//回车换成换行默认函数
                                echo $str1;
                                ?>



                            </div>


                            <div id="loading-img"><img src="picture/loading_com.gif" alt="loading" /></div>
                            <div id="error-comments"></div>

                        </div>
        </div>
        </section>
            <?php endforeach;?>


    </div>

    <?php require_once './right.php';?>


    <!-- //Widget-Box -->
</div>
</div>
<?php require_once './footer.php';?>

<!-- Back-To-Top -->
<div class="top">
    <a href="javascript:void(0);" id="back-to-top"><i class="fa fa-arrow-up fa-3x"></i></a>
</div>
<!-- //Back-To-Top -->
<script type='text/javascript'>
    /* <![CDATA[ */
    var ajaxcomment = {"ajax_url":"http:\/\/www.ldstars.com\/wp-admin\/admin-ajax.php","order":"desc","formpostion":"bottom"};
    /* ]]> */
</script>
<script type='text/javascript' src='js/430ac79e976649c688d41df608208b2e.js'></script>
<script type='text/javascript' src='js/jbox.min.js'></script>
<script type='text/javascript' src='js/function.js'></script>
<script type='text/javascript' src='js/jquery.barrager.min.js'></script>

</body>
</html>
