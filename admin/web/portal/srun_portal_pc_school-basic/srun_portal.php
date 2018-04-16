<?php
@include("../include/head.php");
@include("../include/auth_action.php");
header("Content-Type: text/html; charset=utf-8");
?>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>校园网认证系统</title>
    <link href="./css/style.css" rel="stylesheet" />
    <link href="./css/main.css" rel="stylesheet"/>
    <script language="javascript" src="js/jquery.js"></script>
    <script language="javascript" src="js/srun_portal.js"></script>
	<script type="text/javascript" src="js/portal_data.js"></script>    
	<script>
    $(document).ready(function(){
      $("#logo").html(ad_data.logo);
      $(".userlink").html(ad_data.footer);
	  $(".slideInner ul").html(ad_data.banner);  //背景图片
	  $(".top_banner").html(ad_data.top_banner);
	  var img = $(".imagebg img").attr("src");
	  $("body").css("background","url("+img+")repeat-x left bottom")
    });
    </script>	
</head>

<body class="" style="margin:0 auto;padding:0px;background-repeat:no-repeat;background-position:bottom;">

<div class="slideInner" style="display:none;">
	<ul class="imagebg"></ul>
</div>

<table cellpadding="0" cellspacing="0" width="960" border="0" align="center">
    <tr>
        <td>
            <!--  TOP_BANNER START -->
            <div style="height: 25px;line-height:25px">
                <marquee id="top_banner" direction="left" align="bottom" onmouseout="this.start()"
                         onmouseover="this.stop()" scrollamount="2" scrolldelay="1"></marquee>
            </div>
            <!--  TOP_BANNER STOP -->
            <div class="logo"
                 style="height:120px;padding-left:30px;border-bottom: 1px solid #DFDEDE;padding-bottom:0px;padding-top:0px;">
                <div id="logo" style="height:120px;float: left;"></div>
                <div id="banner"
                     style="width: 300px;height:80px;float: right;margin:20px 50px 0px 0px;text-align: right;"></div>
            </div>

            <table cellpadding="0" cellspacing="0" border="0" width="100%">
                <tr>
                    <td width="58%" align="left" valign="top" style="border-right:solid 1px #eee;padding:0px 25px;">
                        <div style="width:494px;margin:0 auto;margin-top:20px;">
                            <h3 style='font-family: "Microsoft YaHei",微软雅黑;color: rgb(89, 89, 89);text-shadow: 0px 0.0625em 0px rgba(255, 255, 255, 0.6);'>
                                通知公告</h3>

                            <div id="message" class="border bg_color"
                                 style='height:120px;font-family: "Microsoft YaHei",微软雅黑;color: rgb(89, 89, 89);'>
                                <p><font class="top_banner"></font></p></div>
                            <h3 style='font-family: "Microsoft YaHei",微软雅黑;color: rgb(89, 89, 89);text-shadow: 0px 0.0625em 0px rgba(255, 255, 255, 0.6);'>
                                客户端下载</h3>

                            <div class="downs bg_rgba" style="margin-top:0px;">
                                <ul class="clearfix">
                                    <li>
                                        <a href="files/SRun3K.exe"
                                           style="color:rgb(64, 68, 79);text-decoration:none;"><i class="icon_w"></i>Windows</a>
                                    </li>
                                    <li>
                                        <a href="files/auth.apk" style="color:rgb(64, 68, 79);text-decoration:none;"><i
                                                class="icon_a"></i>Linux</a>
                                    </li>
                                    <li>
                                        <a href="https://itunes.apple.com/cn/app/shen-lan/id849464884?mt=8"
                                           style="color:rgb(64, 68, 79);text-decoration:none;"><i
                                                class="icon_apple"></i>Mac</a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                    </td>
                    <td align="center" valign="top" style="padding:0px 25px;">
                        <div style="width:344px;margin:0 auto;margin-top:20px;background:#e2eef1;">
                            <table cellpadding="0" cellspacing="0" border="0" width="100%">
                                <tr>
                                    <td>
                                        <h2 style="margin:0px;">网络准入认证系统</h2>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <?php if ($double_stack_auth_pc && $is_login) { //双栈认证表单 ?>
                                            <form name="form1" method="post"
                                                  action="http://<?= $double_stack_auth_pc ?>/<?= basename(__FILE__) ?>"
                                                  id="login_form" class="form">
                                                <input type="hidden" name="action" value="auto_login">
                                                <input type="hidden" name="info" value="<?= $info ?>">
                                            </form>
                                        <?php } ?>
                                        <?php if (!$is_login && !$is_logout){ //单栈认证表单 ?>
                                            <table width="344px" height="">
                                                <form name="form2" action="<?= basename(__FILE__) ?>" method="post"
                                                      onsubmit="return <?= ($auth_mode == 1) ? "check(this)" : "check1(this)" ?>">
                                                    <input type="hidden" name="action" value="login">
                                                    <input type="hidden" name="ac_id" value="<?= $ac_id ?>">
                                                    <input type="hidden" name="user_ip"
                                                           value="<?= $auth_info["user_ip"] ?>">
                                                    <input type="hidden" name="nas_ip"
                                                           value="<?= $auth_info["nas_ip"] ?>">
                                                    <input type="hidden" name="user_mac"
                                                           value="<?= $auth_info["user_mac"] ?>">
                                                    <input type="hidden" name="url" value="<?= $auth_info["url"] ?>">
                                                    <tr>
                                                        <td width="20">
                                                        </td>
                                                        <td valign="top">
                                                            <table cellpadding="0" cellspacing="0" class="form_table"
                                                                   width="100%">
                                                                <tr>
                                                                    <td height="20"
                                                                        style="color:red;font-size:12px;"><?= $msg ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <td height="25"
                                                                        style='font-family: "Microsoft YaHei",微软雅黑;font-size: 1em;color: rgb(89, 89, 89);text-shadow: 0px 0.0625em 0px rgba(255, 255, 255, 0.6);'>
                                                                        用户名
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td align="left">
                                                                        <div class="input">
                                                                            <input type="text"
                                                                                   style="width:90%;height:28px;padding:5px;"
                                                                                   name="username" size="35"
                                                                                   value="<?= $my_cookie["user_name"] ?>">
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td height="25" align="left"
                                                                        style='font-family: "Microsoft YaHei",微软雅黑;font-size: 1em;color: rgb(89, 89, 89);text-shadow: 0px 0.0625em 0px rgba(255, 255, 255, 0.6);'>
                                                                        密码
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td align="left">
                                                                        <div class="input">
                                                                            <input type="password"
                                                                                   style="width:90%;height:28px;padding:5px;"
                                                                                   name="password" size="35"
                                                                                   value="<?= $my_cookie["user_password"] ?>">
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td height="30">
                                                                        <input name="save_me" title="记忆密码"
                                                                               type="checkbox"
                                                                               value="1" <?= ($my_cookie["save_me"] == 1) ? "checked" : "" ?> />
                                                                        <font
                                                                            style='font-family: "Microsoft YaHei",微软雅黑;font-size: 1em;color: rgb(89, 89, 89);text-shadow: 0px 0.0625em 0px rgba(255, 255, 255, 0.6);'>记住密码</font>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td align="center">
                                                                        <table cellpadding="0" cellspacing="0"
                                                                               border="0" width="100%">
                                                                            <tr>
                                                                                <td height="50" align="left">
                                                                                    <input type="submit" value="登录"
                                                                                           class="a a_demo_one">
                                                                                    <input type="button" value="注销"
                                                                                           class="a a_demo_two"
                                                                                           onclick="do_logout()">
                                                                                    <input type="button" value="自服务"
                                                                                           class="a a_demo_three"
                                                                                           onclick="window.open('<?= $service_url ?>')">
                                                                                </td>
                                                                            </tr>

                                                                            <tr>
                                                                                <td colspan="3" height="10"></td>
                                                                            </tr>
                                                                        </table>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                </form>
                                            </table>
                                        <?php }else{ //认证后显示的窗口 ?>
                                        <table width="350" height="260" class="login">
                                            <form name="form3" action="<?= basename(__FILE__) ?>" method="post">
                                                <input type="hidden" name="action" value="auto_logout">
                                                <input type="hidden" name="info" value="<?= $info ?>">
                                                <input type="hidden" name="user_ip" value="<?= $user_ip ?>">
                                                <tr>
                                                    <td width="70">
                                                    </td>
                                                    <td valign="top">
                                                        <table>
                                                            <tr>
                                                                <td height="40"></td>
                                                                <td height="40" style="font-weight:bold;color:orange;">
                                                                    <?= $res ?>
                                                                </td>
                                                            </tr>
                                                            <?php if ($is_login) { ?>
                                                                <tr>
                                                                    <td height="30" align="right"
                                                                        style="font-weight:bold;color:black;">用户名:
                                                                    </td>
                                                                    <td height="30">&nbsp;&nbsp;<span
                                                                            id="user_name"><?= $user_name ?></span></td>
                                                                </tr>
                                                                <tr>
                                                                    <td height="30" align="right"
                                                                        style="font-weight:bold;color:black;">已用流量:
                                                                    </td>
                                                                    <td height="30">&nbsp;&nbsp;<span
                                                                            id="sum_bytes"><font
                                                                                color="#aaaaaa">正在获取...</font></span>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td height="30" align="right"
                                                                        style="font-weight:bold;color:black;">已用时长:
                                                                    </td>
                                                                    <td height="30">&nbsp;&nbsp;<span
                                                                            id="sum_seconds"></span></td>
                                                                </tr>
                                                                <tr>
                                                                    <td height="30" align="right"
                                                                        style="font-weight:bold;color:black;">帐户余额:
                                                                    </td>
                                                                    <td height="30">&nbsp;&nbsp;<span
                                                                            id="user_balance"></span></td>
                                                                </tr>
                                                                <script language="javascript">
                                                                    setTimeout("get_online_info('<?=$user_ip?>')", 2000);
                                                                </script>
                                                            <?php } ?>
                                                            <tr>
                                                                <td colspan="2" height="30"></td>
                                                            </tr>
                                                            <tr>
                                                                <td height="" colspan="2"
                                                                    style="font-weight:bold;color:black;">
                                                                    <?php if (!$is_logout) { //认证成功显示的按扭 ?>
                                                                        <input type="submit" value="注销"
                                                                               class="a a_demo_one">&nbsp;
                                                                        <?php if ($double_stack_auth_pc && !$double_stack_authed) { ?>
                                                                            <input type="button"
                                                                                   value="登录<?= $other_stack ?>"
                                                                                   class="a a_demo_one"
                                                                                   onclick="auto_login()">&nbsp;
                                                                        <?php } ?>
                                                                    <?php } else { //注销成功显示的按扭 ?>
                                                                        猛击 <a
                                                                            href="http://www.baidu.com"><u>此处</u></a> 重新登录
                                                                    <?php } ?>
                                                                </td>
                                                            </tr>
                                                        </table>
                                        </table>
                                    </td>
                                </tr>
                                </form>
                            </table>
                            <?php } ?>
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
	<tr><td colspan="2" height="50"></td></tr>
	<tr><td colspan="2" align="center"><font class="userlink"></font></td></tr>
</table>

</body>
</html>
