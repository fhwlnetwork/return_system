<?php require_once 'head.php';?>



<body class="home blog">

<?php
require_once './lib/init.php';
	require 'top.php';
?>

<div class="maintop">
	<div class="container">
		<div class="row">
			<!-- Slider -->
			<div class="col-md-6 slider">
									<div id="slide-box" class="carousel slide" data-ride="carousel">
					<!-- Wrapper for slides -->
					<div class="carousel-inner" role="listbox">
                        <div class="item active">
                            <div id="decoroll2" class="imgfocus">

                               <div id="decoroll2" class="imgfocus" style="margin-top:-20%; ">

                                   <?php  require 'lunbo.html'; ?>

                                </div>

                            </div>

                        </div>

					</div>
				</div>
							</div>
			<!-- //Slider -->

<!--            轮播旁内容板块-->
            <div class="col-md-3">
                <div class="hometop">
                    <h3 class="hometitle navy-bg">最新文章</h3>
                    <!--            轮播旁内容板块-->

                    <?php

                    $sql = "select * from news where ctime ORDER BY ctime DESC LIMIT 5";
                    $news_list=$dao->fetchAll($sql);
                    ?>

                            <?php foreach ($news_list as $news) :?>
                                <ul>
                                    <li>
                                        <a href="./template/newspage.php?r=<?php echo $news['id'];?>" target="_blank" class="jtooltip" rel="bookmark" title="<?php echo $news['content'];?>" >
                                            <span class="hometime"><?php echo date('Y-m-d',$news['ctime']);?></span><?php echo $news['title'];?></a></li>
                                </ul>
                            <?endforeach;?>


                    </div>
            </div>

            <!--            轮播旁内容板块结束-->
<!--            二维码模块-->
			<div class="col-md-3">
				<div class="hometop">
					<div class="homeimg">
						<img  src="picture/1486442852924.png">					</div>
					<div class="homenote red-bg">
						扫描二维码，加入TS众智云端交流群。<a target="_blank" href="https://jq.qq.com/?_wv=1027&k=5pq3UFV"><img border="0" src="picture/group.png" alt="律动星光" title="律动星光"></a>					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<!--            二维码模块结束-->



<div class="container" id="main">
    <div class="row">
        <div class="col-md-9">

            <div class="cmsbox clearfix"><div class="cmstitle">

                    <!--                    展示部分学生作品-->
                    <?php

                    $sql = "select * from stu_pub_center where status=1 ORDER BY ctime DESC LIMIT 6";
                    $stu_pub_list=$dao->fetchAll($sql);

                    ?>

                    <h3><i class="fa fa-code"></i>编程设计</h3>
                    <a href="./index.php?r=pub_center" class="pull-right">全部
                        <i class="fa fa-chevron-right"></i>
                    </a></div>
                <?php foreach ($stu_pub_list as $pub_center) :?>
                    <ul class="cmsul">
                        <li class="col-md-4 cmslist-2">
                            <a href="./template/pub_centerpage.php?r=<?php echo $pub_center['id'];?>" class="jtooltip" title="arduino入门教程基础-模拟读取串口" target="_blank">
                                <h4><?php echo $pub_center['title'];?></h4>
                                <div class="cmsmask-2">

<!--                                    E:/phpStudy/WWW/bysj/web/<?php echo $pub_center['pic'];?>-->
                                    <img width="300" height="225" src="../admin/web/<?php echo $pub_center['pic'];?>" class="attachment-thumbnail size-thumbnail wp-post-image" alt="" />
                                    <div class="cmslist-2-desc"><?php echo $pub_center['desc'];?></div>
                                </div>
                                <div class="cmslist-2-info">
                                <span>
                                    <i class="fa fa-eye"></i>
                                    <?php echo $pub_center['stu_name'];?>
                                </span>
                                    <span>
                                    <i class="fa fa-clock-o"></i>
                                        <?php echo date('y-m-d',$pub_center['ctime']) ;?>

                                </span>
                                </div>
                            </a>
                        </li>

                    </ul>
                <?php endforeach;?>
            </div>
            <!--            展示部分学生作品结束-->

            <!--            展示校内新闻模块-->

            <div class="cmsbox clearfix">
                <div class="cmstitle">

                    <h3><i class="fa fa-google"></i>校内新闻</h3>
                    <a href="./index.php?r=schoolnews" class="pull-right">全部
                        <i class="fa fa-chevron-right"></i>
                    </a>
                </div>
                <ul class="cmsul">
                    <?php
                    $sql = "select * from news where type=1 ORDER BY ctime DESC LIMIT 4";
                    $news_list3=$dao->fetchAll($sql);
                    ?>
                    <?php foreach ($news_list3 as $news3) :?>
                        <li class="col-md-3 cmslist">
                            <a href="./template/newspage.php?r=<?php echo $news3['id'];?>" target="_blank">
                                <div class="cmsdiv">
                                    <img itemprop="image" class="media-object" src="../admin/web/<?php echo $news3['pic'];?>" />
                                    <div class="cmsmask">
                                        <h3><?php echo $news3['title'];?></h3>
                                        <p>
                                        <span>
                                            <i class="fa fa-eye"></i>
                                            <?php echo $news3['mid'];?>
                                        </span>
                                            <span>
                                            <i class="fa fa-clock-o"></i>
                                                <?php echo date('Y-m-d',$news3['ctime']);?>
                                        </span>
                                            <?php echo $news3['desc'];?>
                                        </p>
                                    </div>
                                </div>
                            </a>
                        </li>
                    <?php endforeach; ?>

                </ul>
            </div>
            <!--            展示校内新闻模块结束-->



            <!--            大赛信息展示部分模块-->

            <div class="cmsbox clearfix">
                <div class="cmstitle">

                    <h3>
                        <i class="fa fa-internet-explorer"></i>大赛信息
                    </h3>
                    <a href="./index.php?r=megagamenews" class="pull-right">全部
                        <i class="fa fa-chevron-right"></i>
                    </a>
                </div>
                <ul class="cmsul">

                    <?php
                    $sql = "select * from news where type=2 ORDER BY ctime DESC LIMIT 5";
                    $news_list3=$dao->fetchAll($sql);
                    ?>
                    <?php foreach ($news_list3 as $news3) :?>

                        <li class="col-md-3 cmslist">
                            <a href="./template/newspage.php?r=<?php echo $news3['id'];?>" target="_blank">
                                <div class="cmsdiv">
                                    <img itemprop="image" class="media-object" src="../admin/web/<?php echo $news3['pic'];?>" />
                                    <div class="cmsmask">
                                        <h3><?php echo $news3['title'];?></h3>
                                        <p>
                                        <span>
                                            <i class="fa fa-eye"></i>
                                            <?php echo $news3['mid'];?>
                                        </span>
                                            <span>
                                            <i class="fa fa-clock-o"></i>
                                                <?php echo date('Y-m-d',$news3['ctime']);?>
                                        </span>
                                            <?php echo $news3['desc'];?>
                                        </p>
                                    </div>
                                </div>
                            </a>
                        </li>
                    <?php endforeach; ?>


                </ul>
            </div>
            <!--          大赛信息 展示部分模块结束-->






            <div class="cmsbox-3 clearfix col-md-6">
                <div class="cmsbox clearfix">
                    <div class="cmstitle">
                        <h3>
                            <i class="fa fa-apple"></i>
                            行业新闻馆
                        </h3>
                        <a href="./index.php?r=tradenews" class="pull-right">全部
                            <i class="fa fa-chevron-right"></i>
                        </a>
                    </div>
                    <ul class="cmsul">
                        <?php
                        $sql = "select * from news where type =3 ORDER BY utime DESC LIMIT 4";
                        $news_list1=$dao->fetchAll($sql);

                        ?>
                        <?php foreach ($news_list1 as $list1):?>
                        <li class="cmslist-3">
                            <a href="./template/newspage.php?r=<?php echo $news3['id'];?>" target="_blank">
                                <span class="cmslist-3-time"><?php echo date('Y-m-d',$list1['ctime']);?></span>
                                <h4><?php echo $list1['title'];?></h4>
                                <span class="cmslist-3-more pull-right">查看
                                    <i class="fa fa-chevron-right"></i>
                                </span>
                            </a>
                        </li>
                        <?php endforeach;?>
                        </ul>
                </div>
            </div>

<!--            展示招聘系模块-->

            <div class="cmsbox-3 clearfix col-md-6">
                <div class="cmsbox clearfix">
                    <div class="cmstitle">
                        <h3>
                            <i class="fa fa-windows"></i>招聘馆
                        </h3>
                        <a href="./index.php?r=recruit" class="pull-right">全部
                            <i class="fa fa-chevron-right"></i>
                        </a>
                    </div>
                    <ul class="cmsul">
                        <?php
                        $sql = "select * from worK_information ORDER BY utime DESC LIMIT 5";
                        $worK_information=$dao->fetchAll($sql);
                        ?>
                        <?php foreach ($worK_information as $information):?>
                        <li class="cmslist-3">
                            <a href="./template/recruitpage.php?r=<?php echo $news3['id'];?>" target="_blank">
                                <span class="cmslist-3-time"><?php echo date('Y-m-d',$information['ctime']);?></span>
                                <h4><?php echo $information['work_name'];?></h4>
                                <span class="cmslist-3-more pull-right">查看
                                    <i class="fa fa-chevron-right"></i>
                                </span>
                            </a>
                        </li>
                        <?php endforeach;?>
                    </ul>
                </div>
            </div>
            <!--            展示招聘系模块结束-->


        </div>









<?php require_once 'right.php';?>
    </div>
</div>
				<?php require 'footer.php';?>

	<!-- Back-To-Top -->
	<div class="top">
		<a href="javascript:void(0);" id="back-to-top"><i class="fa fa-arrow-up fa-3x"></i></a>
	</div>
	<!-- //Back-To-Top -->
	<script type='text/javascript' src='js/93175b39f89f4c459a07199fa532e44d.js'></script>
<script type='text/javascript' src='js/jbox.min.js'></script>
<script type='text/javascript' src='js/function.js'></script>
<script type="text/javascript">(function(){document.write(unescape('%3Cdiv id="bdcs"%3E%3C/div%3E'));var bdcs = document.createElement('script');bdcs.type = 'text/javascript';bdcs.async = true;bdcs.src = 'http://znsv.baidu.com/customer_search/api/js?sid=12844460128595975264' + '&plate_url=' + encodeURIComponent(window.location.href) + '&t=' + Math.ceil(new Date()/3600000);var s = document.getElementsByTagName('script')[0];s.parentNode.insertBefore(bdcs, s);})();</script>  </body>
</html>
