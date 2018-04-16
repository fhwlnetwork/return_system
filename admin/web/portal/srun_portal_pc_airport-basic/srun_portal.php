<?php
@include("../include/head.php");
@include("../include/auth_action.php");
header("Content-Type: text/html; charset=utf-8");
?>
<!DOCTYPE html>
<head>
    <meta charset="utf-8"/>
    <title>用户登录</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <link rel="stylesheet" href="css/airport.css"/>
    <script type="text/javascript" src="js/jquery-1.11.3.min.js"></script>
    <script type="text/javascript" src="js/srun_portal.js"></script>
    <script type="text/javascript" src="js/portal_data.js"></script>
    <script>
        $(document).ready(function () {
            $("#logo").html(ad_data.logo)
            $(".tel_font").html(ad_data.footer)
            $(".slideInner ul").html(ad_data.banner);
            $(".nav_content").html(ad_data.top_banner);
        });
    </script>
    <script type="text/javascript" src="js/airport.js"></script>

    <?php if ($double_stack_auth_pc) { //用于在AC上触发双栈接口 ?>
        <style type="text/css">
            .test {
                background-image: url(http://<?=$double_stack_auth_pc?>/images/fenjin.jpg)
            }
        </style>
    <? } ?>
</head>
<body class="clearfix home">

<!-- BANNER START -->
<div role="banner" class="clearfix">
    <div class="span12">

        <!-- logo start -->
        <div id="logo">
            <img src="img/airport_logo.png" width="96">
        </div>
        <!-- logo end -->

        <!--BANNER START-->
        <nav role="navigation" id="nav">
            <div class="banner">
                <div class="main_nav">
                    <div class="nav_content"></div>
                    <!--<li><a href="javascript:void(0);" class="menu__link landing">酒店预定</a></li>
                    <li>|</li>
                    <li><a href="javascript:void(0);" class="menu__link landing">优惠活动</a></li>
                    <li>|</li>
                    <li><a href="javascript:void(0);" class="menu__link landing">联系我们</a></li>-->
                </div>
            </div>
        </nav>
        <!--BANNER END-->

    </div>
</div>
<!-- BANNER END -->

<!--用登录框 START-->
<div id="main" role="main" class="homepage">
    <section class="region region-sidebar-first column sidebar">
        <div id="hm_icon_nav_cont" style="margin-top:50px;">
            <ul id="hm_icon_nav">
                <li class="status active">
                    <div class="icon"></div>
                    <a id="status" class="main" href="#" aria-haspopup="true">登录信息</a>

                    <div class="innerContent">

                        <?php if ($double_stack_auth_pc && $is_login) { //双栈认证表单 ?>
                            <form name="form1" method="post"
                                  action="http://<?= $double_stack_auth_pc ?>/<?= basename(__FILE__) ?>" id="login_form"
                                  class="form">
                                <input type="hidden" name="action" value="auto_login">
                                <input type="hidden" name="info" value="<?= $info ?>">
                            </form>
                        <?php } ?>

                        <?php if (!$is_login && !$is_logout) { //单栈认证表单 ?>

                            <form name="form2" action="<?= basename(__FILE__) ?>" method="post"
                                  onsubmit="return <?= ($auth_mode == 1) ? "check(this)" : "check1(this)" ?>">
                                <input type="hidden" name="action" value="login">
                                <input type="hidden" name="ac_id" value="<?= $ac_id ?>">
                                <input type="hidden" name="user_ip" value="<?= $auth_info["user_ip"] ?>">
                                <input type="hidden" name="nas_ip" value="<?= $auth_info["nas_ip"] ?>">
                                <input type="hidden" name="user_mac" value="<?= $auth_info["user_mac"] ?>">
                                <input type="hidden" name="url" value="<?= $auth_info["url"] ?>">
                                <input type="text" name="username" size="35" value="<?= $my_cookie["user_name"] ?>"
                                       placeholder="请填写用户名..." style="border:solid 1px #ccc;background:white;">
                                <br/>
                                <input type="password" name="password" size="35"
                                       value="<?= $my_cookie["user_password"] ?>" placeholder="请填写密码..."
                                       style="border:solid 1px #ccc;background:white;"><br/>
                                <input name="save_me" title="记忆密码" type="checkbox"
                                       value="1" <?= ($my_cookie["save_me"] == 1) ? "checked" : "" ?> /><span
                                    style="padding-top:2px;display:inline-block;">记住密码</span>
                                <table cellpadding="0" cellspacing="0" border="0" width="100%">
                                    <tr>
                                        <td height="50" align="center">
                                            <input type="submit" value="登录" class="a a_demo_one">
                                        </td>
                                        <td>
                                            <input type="button" value="注销" class="a a_demo_two" onclick="do_logout()">
                                        </td>
                                        <td>
                                            <input type="button" value="自服务" class="a a_demo_three"
                                                   onclick="window.open('<?= $service_url ?>')">
                                        </td>
                                    </tr>
                                </table>
                            </form>

                        <?php } else { //认证后显示的窗口 ?>
                            <form name="form3" action="<?= basename(__FILE__) ?>" method="post">
                                <input type="hidden" name="action" value="auto_logout">
                                <input type="hidden" name="info" value="<?= $info ?>">
                                <input type="hidden" name="user_ip" value="<?= $user_ip ?>">

                                <table>
                                    <tr>
                                        <td height="40"></td>
                                        <td height="40" style="font-weight:bold;color:orange;">
                                            <?= $res ?>
                                        </td>
                                    </tr>
                                    <?php if ($is_login) { ?>
                                        <tr>
                                            <td height="30" align="right" style="font-weight:bold;color:black;">用户名:
                                            </td>
                                            <td height="30">&nbsp;&nbsp;<span id="user_name"><?= $user_name ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td height="30" align="right" style="font-weight:bold;color:black;">已用流量:
                                            </td>
                                            <td height="30">&nbsp;&nbsp;<span id="sum_bytes"><font color="#aaaaaa">正在获取...</font></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td height="30" align="right" style="font-weight:bold;color:black;">已用时长:
                                            </td>
                                            <td height="30">&nbsp;&nbsp;<span id="sum_seconds"></span></td>
                                        </tr>
                                        <tr>
                                            <td height="30" align="right" style="font-weight:bold;color:black;">帐户余额:
                                            </td>
                                            <td height="30">&nbsp;&nbsp;<span id="user_balance"></span></td>
                                        </tr>
                                        <script language="javascript">
                                            setTimeout("get_online_info('<?=$user_ip?>')", 2000);
                                        </script>
                                    <?php } ?>
                                    <tr>
                                        <td colspan="2" height="10"></td>
                                    </tr>
                                    <tr>
                                        <td height="" colspan="2" style="font-weight:bold;color:black;">
                                            <?php if (!$is_logout) { //认证成功显示的按扭 ?>
                                                <input type="submit" value="注销" class="a a_demo_two">&nbsp;
                                                <?php if ($double_stack_auth_pc && !$double_stack_authed) { ?>
                                                    <input type="button" value="登录<?= $other_stack ?>"
                                                           class="a a_demo_one" onclick="auto_login()">&nbsp;
                                                <?php } ?>
                                            <?php } else { //注销成功显示的按扭 ?>
                                                猛击 <a href="http://www.baidu.com"><u>此处</u></a> 重新登录
                                            <?php } ?>
                                        </td>
                                    </tr>
                                </table>
                            </form>
                        <?php } ?>
                    </div>
                    <div style="height:10px;"></div>
                </li>
            </ul>
        </div>
    </section>
</div>
<!--用登录框 END-->

<!--滚动图片 START-->
<div class="slides">
    <div class="slideInner">
        <ul>
            <!--
            <a href="JavaScript:;" style="background: url(img/1.jpg) no-repeat center center;"></a>
            <a href="JavaScript:;" style="background: url(img/2.jpg) no-repeat center center"></a>
            <a href="JavaScript:;" style="background: url(img/3.jpg) no-repeat center center"></a>
            -->
        </ul>
    </div>
    <div class="Airportnav">
        <a class="prev" href="javascript:;"></a>
        <a class="next" href="javascript:;"></a>
    </div>
</div>
<!--滚动图片 END-->


<footer role="contentinfo" class="home">

    <!--最新消息 START-->
    <div class="span12 footer_info_text">
        <img src="img/news.gif" style="padding-top:5px;"> 机票1折起预订 早订更优惠!
    </div>
    <!--最新消息 END-->

    <!--底部电话 START-->
    <nav id="footer_navigation">
        <div class="span12" id="wrapper-footer-navigation">

            <div class="region region-footer-links">
                <ul class="social_nav flysfo">
                    <li class="google-plus">
                        <font class="tel_font">联系电话：400-123-0000</font></li>
                </ul>
            </div>

        </div>
    </nav>
    <!--底部电话 END-->
</footer>
</body>
</html>
