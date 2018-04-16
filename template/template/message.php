<?php require_once 'head.php';?>
<body class="page-template page-template-page-messages page-template-page-messages-php page page-id-11">
	
	<?php require 'top.php';?>
<div class="container">
		<div class="row">
			<div class="col-md-12">
				<!-- Blog-Box -->
				<section class="article">
					<!--- Article-Head -->
					<div class="article-head">
						<h1 class="article-title text-center">留言板</h1>
						<div class="article-info">
							<span class="article-author navy">King</span>
							<span class="article-time">2015-05-18</span>
							<span class="article-view pull-right">阅读:<span class="article-count red">8,550</span></span>
							<span class="article-view pull-right">评论:<span class="article-count"><a href="#message#comments">26</a></span></span>						</div>
					</div>
					<!--- //Article-Head -->

					<!--- Article-content -->
					<div class="article-content">
						<div id="cShow">
                            <center><font color="#FFF100"><wbr><font color="#00AEEF"><wbr>●</font>
                                <wbr>
                                </font>
                                <wbr>
                                <br> <font size="6" style="line-height:1.5em"><wbr><font color="#FFF100"><wbr>●</font>
                                <wbr>
                                </font>
                                <wbr> <font color="#8FC63D"><wbr>●</font>
                                <wbr>
                                <br> <font color="#EE1000"><wbr>●</font>
                                <wbr>
                                <br> <font color="#39B778"><wbr>●</font>
                                <wbr>
                                <br> <font size="4" style="line-height:1.5em"><wbr><font color="#8FC63D"><wbr>●</font>
                                <wbr>
                                </font>
                                <wbr>
                                <br> <font size="5" style="line-height:1.5em"><wbr><font color="#F16C4D"><wbr>●</font>
                                <wbr>
                                </font>
                                <wbr>
                            </center>
                            <br>
                            <center><font color="#8FC63D"><wbr><font size="6" style="line-height:1.5em"><wbr>＊</font>
                                <wbr><font color="#00AEEF"><wbr>有什么想说的~在这里告诉我吧</font>
                                <wbr><font color="#8FC63D"><wbr><font size="6" style="line-height:1.5em"><wbr>＊</font>
                                <wbr>
                                <center>
                                    <br>  <font color="#F49BC1"><wbr> ━━━━━━━━━━━━</font>
                                    <wbr><font color="#EF6EA8"><wbr><font face="Webdings"><wbr>=</font>
                                    <wbr>
                                    </font>
                                    <wbr><font color="#EF6EA8"><wbr><font face="Webdings"><wbr>=</font>
                                    <wbr>
                                    </font>
                                    <wbr><font color="#F49BC1"><wbr>━━━━</font>
                                    <wbr>
                                    <br>
                                    <center><font color="00AEEF"><wbr>朋友留言</font>
                                        <wbr>
                                        </font>
                                        <wbr>
                                        </font>
                                        <wbr>
                                    </center>
                                </center>
                                <br>
                                </font>
                                <wbr>
                                </font>
                                <wbr>
                            </center>
                        </div>
					</div>
					<!--- //Article-content -->

					<div class="award">
						<p><a href="" id="award" title="打赏，支持一下">点赞</a></p>
					</div>





                    <div class="comments">
						<h3>精彩评论</h3>



                        <!-- 评论列表-->
                        <?php
                            require_once './lib/init.php';
                            $sql="select * from message  where status=1";
                            $message=$dao->fetchAll($sql);
                        ?>
     <?php foreach ($message as $v):?>
	<div id="comment-lists">
		<ol class="commentlist">
			<li class="comment even thread-even depth-1" id="comment-268">
	   <div id="div-comment-268" class="comment-body">
		  		  <div class="gravatar">
			<div class="comment-author vcard">
				<img alt='' src='picture/5e567e3377274f53bd333b93dbf10d22.gif' srcset='http://2.gravatar.com/avatar/eb2e41b1f0921ba81faef40e681baadb?s=100&amp;d=monsterid&amp;r=g 2x' class='avatar avatar-50 photo' height='50' width='50' />			</div>
		  </div>
<!--		<div class="floor">1楼</div>-->
		 <div class="commenttext">
			 <span class="commentid"><span class="comment_author"><?php echo $v['person_name']; ?></php></span><a data-hint="潜水" class="hint hint--top"><i class="fa fa-star yellow"></i></a></span>
					<div class="comment_text">
										<p><?php echo $v['message']; ?></p>
										</div>
					<div class="comment-info">
						<span class="datetime"><i class="fa fa-clock-o"></i><?php echo date('y-m-d',$v['ctime']); ?></span>
                    </div>
		</div>
	  </div>
    </div>
     <?php endforeach;?>

	<!-- //评论列表--< //评论列表-->


                        <form name="form1" method="post" action="./template/messaagedml.php">
<!--                            隐藏域传值-->
                            <input type="hidden" name="user_name" value="<?php echo $_SESSION['username']?>">

                            <input type="hidden" name="id" value="<?php echo $_SESSION['id']?>">
                            <input type="hidden" name="personame" value="<?php echo $_SESSION['person_name']?>">

                            <table width="1000" border="0">
                                <tr>
                                    <td width="300px";>留言信息</td>
                                </tr>
                                <tr width="300px"; height="200px">
                                    <td ><textarea style="width: 1000px;height: 200px;"  name="contents"></textarea></td>
                                </tr>
                                <tr >
                                    <td ><button  type="submit" >提交</button></td>
                                </tr>
                            </table>
                        </form>




<!-- 	评论去结束-->


			<!---版权-->
		<?php require 'footer.php';?>
			

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
<script type="text/javascript">(function(){document.write(unescape('%3Cdiv id="bdcs"%3E%3C/div%3E'));var bdcs = document.createElement('script');bdcs.type = 'text/javascript';bdcs.async = true;bdcs.src = 'http://znsv.baidu.com/customer_search/api/js?sid=12844460128595975264' + '&plate_url=' + encodeURIComponent(window.location.href) + '&t=' + Math.ceil(new Date()/3600000);var s = document.getElementsByTagName('script')[0];s.parentNode.insertBefore(bdcs, s);})();</script>	<script type="text/javascript">
		$('#award').jBox('Modal', {
			title: '感谢您的支持，我会继续努力的!',
			content: $('.award_box'),
			animation: 'pulse',
		});

		$(".pay_item").click(function(){
			$(this).addClass('checked').siblings('.pay_item').removeClass('checked');
			var dataid=$(this).attr('data-id');
			$(".award_payimg img").attr("src","#wp-content/themes/bulletin/img/"+dataid+"img.jpg");
			$("#award_pay_txt").text(dataid=="alipay"?"支付宝":"微信");
		});
		new jBox('Image');
			</script>
  </body>
</html>
