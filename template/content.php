<!DOCTYPE html>
<html lang="zh-CN">
<? require_once 'head.php';?>

<body class="page-template-default page page-id-40">
<!-- Fixed navbar -->
<?php require 'top.php'; ?>
<div class="container content">
    <div class="row">
        <div class="col-md-9">
            <!-- Blog-Box -->
            <section class="article">
                <!--- Article-Head -->
                <div class="article-head">
                    <h1 class="article-title text-center">免费VPN账号获取</h1>
                    <div class="article-info">
                        <span class="article-author navy">King</span>
                        <span class="article-time">2015-06-10</span>
                        <span class="article-view pull-right">阅读:<span class="article-count red">7,777</span></span>
                        <span class="article-view pull-right">评论:<span class="article-count"><a href="#">12</a></span></span>						</div>
                </div>
                <!--- //Article-Head -->

                <!--- Article-content -->
                <div class="article-content">
                    <h1>i iam just a test page </h1>
                </div>
                <!--- //Article-content -->

                <div class="award">
                    <p><a href="javascript:void(0)" id="award" title="打赏，支持一下">打赏</a></p>
                </div>

                <div class="award_box">
                    <div class="award_payimg">
                        <img src="picture/alipayimg.jpg" alt="扫码支持" title="扫一扫" />
                    </div>
                    <div class="pay_explain">扫码打赏，你说多少就多少</div>
                    <div class="award_payselect">
                        <div class="pay_item checked" data-id="alipay">
                            <span class="radiobox"></span>
                            <span class="pay_logo"><img src="picture/alipay.jpg" alt="支付宝" /></span>
                        </div>
                        <div class="pay_item" data-id="weipay">
                            <span class="radiobox"></span>
                            <span class="pay_logo"><img src="picture/wechat.jpg" alt="微信" /></span>
                        </div>
                    </div>
                    <div class="award_info">
                        <p>打开<span id="award_pay_txt">支付宝</span>扫一扫，即可进行扫码打赏哦</p>
                    </div>
                </div>

                <div class="comments">
                    <h3>精彩评论</h3>
                    <div id="comments"><span style="font-weight:bold;font-size:14px;margin-right:30px;">全部回复</span><span style="color: #f60;font-size:18px;font-weight: bold;">12</span>人评论<span style="color: #f60;font-size:18px;margin-left:30px;font-weight: bold;">7,777</span>人参与</div>
                    <!-- 评论列表-->
                    <div id="comment-lists">
                        <h1> i am a recovery page!</h1>
                    </div>
                    <!-- //评论列表-->


                    <div id="respond">
                        <p>
                            <div id="respond" class="comment-respond">
                                <h3 id="reply-title" class="comment-reply-title"> <small><a rel="nofollow" id="cancel-comment-reply-link" href="/freevpn#respond" style="display:none;">【取消回复】</a></small></h3>			<form action="#wp-comments-post.php" method="post" id="commentform" class="comment-form">
                        <p class="comment-notes"><span id="email-notes">电子邮件地址不会被公开。</span> 必填项已用<span class="required">*</span>标注</p><p class="col-md-12 comments-form"><textarea id="comment" name="comment" tabindex="4" cols="40" rows="3" aria-required="true"></textarea></p><label for="comment_mail_notify" style="display:none"><input type="checkbox" name="comment_mail_notify" id="comment_mail_notify" value="comment_mail_notify" checked="checked"/>有人回复时邮件通知我</label><div id="comment-author-info"><p class="comment-form-author col-md-4"><input name="author" type="text" placeholder="昵称：*" value="" size="30" /></p>
                            <p class="comment-form-email col-md-4"><input name="email" type="text" placeholder="邮箱：*" value="" size="30" /></p>
                            <p class="comment-form-url col-md-4"><input name="url" type="text" placeholder="网址" value="" size="30" /></p></div>
                        <p class="form-submit"><input name="submit" type="submit" id="submit" class="submit" value="发表评论" /> <input type='hidden' name='comment_post_ID' value='40' id='comment_post_ID' />
                            <input type='hidden' name='comment_parent' id='comment_parent' value='0' />
                        </p>		<p class="antispam-group antispam-group-q" style="clear: both;">
                            <label>Current ye@r <span class="required">*</span></label>
                            <input type="hidden" name="antspm-a" class="antispam-control antispam-control-a" value="2017" />
                            <input type="text" name="antspm-q" class="antispam-control antispam-control-q" value="4.3" autocomplete="off" />
                        </p>
                        <p class="antispam-group antispam-group-e" style="display: none;">
                            <label>Leave this field empty</label>
                            <input type="text" name="antspm-e-email-url-website" class="antispam-control antispam-control-e" value="" autocomplete="off" />
                        </p>
                        </form>
                    </div>
                    <!-- #respond -->
                    <div id="loading-img"><img src="picture/loading_com.gif" alt="loading" /></div>
                    <div id="error-comments"></div>
                    <script type="text/javascript">	//快捷回复 Ctrl+Enter
                        //<![CDATA[
                        jQuery(document).keypress(function(e){
                            if(e.ctrlKey && e.which == 13 || e.which == 10) {
                                jQuery(".submit").click();
                                document.body.focus();
                            } else if (e.shiftKey && e.which==13 || e.which == 10) {
                                jQuery(".submit").click();
                            }
                        })
                        // ]]>
                        //<![CDATA[
                        var changeMsg = "[ 编辑信息 ]";
                        var closeMsg = "[ 隐藏信息 ]";
                        function toggleCommentAuthorInfo() {
                            jQuery('#comment-author-info').slideToggle('slow', function(){
                                if ( jQuery('#comment-author-info').css('display') == 'none' ) {
                                    jQuery('#toggle-comment-author-info').text(changeMsg);
                                } else {
                                    jQuery('#toggle-comment-author-info').text(closeMsg);
                                }
                            });
                        }
                        //]]>
                    </script>
                </div>
        </div>
        </section>
        <!-- //Blog-Box -->
    </div>
    <!-- Widget-Box -->
    <div class="col-md-3 widget">
        <div class="widget-box"><div class="widget-title"><h3>关注我<span class="sub-title orange">Follow Me</span></h3></div><div class="widget-item"><ul class="followme center"><li class="orange-bg jtooltip" title="点击关注我的新浪微博" ><a href="http://weibo.com/wwj448" class="follow-icon"><i class="fa fa-weibo"></i></a></li><li class="blue-bg jtooltip" title="点击与我QQ交谈"><a href="http://wpa.qq.com/msgrd?v=3&uin=2892391690&site=qq&menu=yes" class="follow-icon"><i class="fa fa-qq"></i></a></li><li class="green-bg" id="wechat" qrcode=" <img src='picture/6446d860dbbfe540e9e2.png' />"><a href="#" class="follow-icon"><i class="fa fa-weixin"></i></a></li><li class="red-bg jtooltip" title="订阅我们"><a href="#feed" class="follow-icon"><i class="fa fa-rss"></i></a></li></ul></div></div><div class="widget-box"><div class="widget-title"><h3>最新文章<span class="sub-title orange">New Articles</span></h3></div><div class="widget-item"><div class="widget-post"><a href="#ios/358.html" target="_blank" rel="bookmark" title="给iPhone的信息添加多彩炫酷的动画效果" ><img width="300" height="225" src="picture/945118c29cb261fc5453-300x225.jpg" class="attachment-thumbnail size-thumbnail wp-post-image" alt="" /><span>给iPhone的信息添加多彩炫酷的动画效果</span></a><span class="btn-cat"><a href="#ios" title="苹果体验馆"">苹果体验馆</a></span></div><div class="widget-post"><a href="#ios/355.html" target="_blank" rel="bookmark" title="苹果产品为什么选择锂离子电池？" ><img width="300" height="225" src="picture/25b2916b5c49db617f52-300x225.jpg" class="attachment-thumbnail size-thumbnail wp-post-image" alt="" srcset="#wp-content/uploads/2017/02/25b2916b5c49db617f52-300x225.jpg 300w, #wp-content/uploads/2017/02/25b2916b5c49db617f52-600x450.jpg 600w, #wp-content/uploads/2017/02/25b2916b5c49db617f52.jpg 640w" sizes="(max-width: 300px) 100vw, 300px" /><span>苹果产品为什么选择锂离子电池？</span></a><span class="btn-cat"><a href="#ios" title="苹果体验馆"">苹果体验馆</a></span></div><div class="widget-post"><a href="#program/arduino/353.html" target="_blank" rel="bookmark" title="arduino入门教程基础-模拟读取串口" ><img width="300" height="225" src="picture/75f5750f6dd6afbec57b-300x225.jpg" class="attachment-thumbnail size-thumbnail wp-post-image" alt="" /><span>arduino入门教程基础-模拟读取串口</span></a><span class="btn-cat"><a href="#program/arduino" title="arduino"">arduino</a></span></div><div class="widget-post"><a href="#ios/351.html" target="_blank" rel="bookmark" title="苹果官方 iPhone、iPad 和 iPod touch 电池保养小提示" ><img width="300" height="225" src="picture/25b2916b5c49db617f52-300x225.jpg" class="attachment-thumbnail size-thumbnail wp-post-image" alt="" srcset="#wp-content/uploads/2017/02/25b2916b5c49db617f52-300x225.jpg 300w, #wp-content/uploads/2017/02/25b2916b5c49db617f52-600x450.jpg 600w, #wp-content/uploads/2017/02/25b2916b5c49db617f52.jpg 640w" sizes="(max-width: 300px) 100vw, 300px" /><span>苹果官方 iPhone、iPad 和 iPod touch 电池保养小提示</span></a><span class="btn-cat"><a href="#ios" title="苹果体验馆"">苹果体验馆</a></span></div><div class="widget-post"><a href="#ios/348.html" target="_blank" rel="bookmark" title="苹果官方延长电池使用时间和寿命方法" ><img width="300" height="225" src="picture/25b2916b5c49db617f52-300x225.jpg" class="attachment-thumbnail size-thumbnail wp-post-image" alt="" srcset="#wp-content/uploads/2017/02/25b2916b5c49db617f52-300x225.jpg 300w, #wp-content/uploads/2017/02/25b2916b5c49db617f52-600x450.jpg 600w, #wp-content/uploads/2017/02/25b2916b5c49db617f52.jpg 640w" sizes="(max-width: 300px) 100vw, 300px" /><span>苹果官方延长电池使用时间和寿命方法</span></a><span class="btn-cat"><a href="#ios" title="苹果体验馆"">苹果体验馆</a></span></div></ul></div></div></div>
    <!-- //Widget-Box -->
</div>
</div>
<footer>
    <div class="container">
        <div class="row">
            <div class="col-md-4 foot-item">
                <h3 class="foot-title">
                    关于我们
                </h3>
                <p>律动星光-网站开发 技术交流群：483749829 最有价值的网站技术群！！！</p>
            </div>
            <div class="col-md-4 foot-item">
                <h3 class="foot-title">
                    站点信息
                </h3>
                <ul id="menu-%e5%ba%95%e9%83%a8%e5%af%bc%e8%88%aa" class="sitelink"><li id="menu-item-38" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-38"><a href="#message">留言板</a></li>
                    <li id="menu-item-39" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-39"><a href="#about">关于我们</a></li>
                    <li id="menu-item-50" class="menu-item menu-item-type-post_type menu-item-object-page current-menu-item page_item page-item-40 current_page_item menu-item-50"><a href="#freevpn">免费VPN</a></li>
                    <li id="menu-item-56" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-56"><a href="#pan">网盘外链</a></li>
                    <li id="menu-item-96" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-96"><a href="#links">友情链接</a></li>
                </ul>				</div>
            <div class="col-md-4 foot-item">
                <h3 class="foot-title">
                    微信关注
                </h3>
                <p><img src="picture/6446d860dbbfe540e9e2.png" alt="" class="qrcode" /></p>
            </div>
        </div>
    </div>
    <div class="copyright text-center">
        申明：本站文字除标明出处外皆为作者原创，转载请注明原文链接。 <br/>Copyright © 2016-2017 律动星光 版权所有		</div>
    <div style="display: none">
        <script type="text/javascript">var cnzz_protocol = (("https:" == document.location.protocol) ? " https://" : " http://");document.write(unescape("%3Cspan id='cnzz_stat_icon_1000014177'%3E%3C/span%3E%3Cscript src='" + cnzz_protocol + "s22.cnzz.com/z_stat.php%3Fid%3D1000014177%26show%3Dpic' type='text/javascript'%3E%3C/script%3E"));</script>		</div>
</footer>

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
