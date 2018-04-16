<!-- Widget-Box -->
<div class="col-md-3 widget">
	<div class="widget-box"><div class="widget-title">
		<h3>关注我<span class="sub-title orange">Follow Me</span></h3>
	</div>
	<div class="widget-item">
		<ul class="followme center">
			<li class="orange-bg jtooltip" title="点击关注我的新浪微博" >
				<a href="#" class="follow-icon">
					<i class="fa fa-weibo"></i>
				</a>
			</li>
			<li class="blue-bg jtooltip" title="点击与我QQ交谈">
				<a href="#" class="follow-icon">
					<i class="fa fa-qq"></i>
				</a>
			</li>
			<li class="green-bg" id="wechat" qrcode=" <img src='#' />">
				<a href="#" class="follow-icon">
					<i class="fa fa-weixin"></i>
				</a>
			</li>
			<li class="red-bg jtooltip" title="订阅我们">
				<a href="#feed" class="follow-icon">
					<i class="fa fa-rss"></i>
				</a>
			</li>
		</ul>
	</div>
</div>

<div class="widget-box">
		<div class="widget-title">
			<h3>最新留言
				<span class="sub-title orange">New Message</span>
			</h3>
		</div>
		<div class="widget-item">
			<ul class="comments_list">
                <?php

                $sql = "select * from message where status =1 ORDER BY ctime DESC LIMIT 10";
                $messages= $dao->fetchAll($sql);

                ?>
                <?php foreach ( $messages as $v ):?>
				<li>
					<div class='comments_author clearfix'>
						<img alt='' src='picture/f212f7c2b666433bbd7f5c3efee5d4b2.gif' d=monsterid&amp;r=g 2x' class='avatar avatar-32 photo' height='32' width='32' />
						<span><?php echo $v['person_name'];?></span>
					</div>
					<div class='comments_info'><?php echo $v['message'];?></div>
					<div class='comments_article'>
						<i class='fa fa-quote-left red'></i>
                        <?php echo date('y-m-d',$v['ctime']);?>发表在：<a href="index.php?r=message" class='jtooltip' title='点击查看-留言板'>留言板</a>
					</div>
				</li>
                <?php endforeach;?>



				</ul>
		</div>
	</div>
				</div>

</div>
</div>