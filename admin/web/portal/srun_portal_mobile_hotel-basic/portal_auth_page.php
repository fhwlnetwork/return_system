<?php
@include("../include/head.php");
@include("../include/auth_action.php");
header("Content-Type: text/html; charset=utf-8");
?>
<!DOCTYPE html>
<html class="ui-mobile">
    <head id="ctl00_Head">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <base href=".">
        <meta name="viewport" content="initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no">
        <meta name="format-detection" content="telephone=no">
        <link rel="stylesheet" href="css/jquery.mobile-1.1.0.min.css">
        <link rel="stylesheet" type="text/css" href="css/combined_1.css?33">
        <script type="text/javascript" src="js/jquery.js"></script>
        <script type="text/javascript" src="js/portal_data.js"></script>

        <?php if ($double_stack_auth_mobile) { //用于在AC上触发双栈接口 ?>
            <style type="text/css">
                .test {
                    background-image: url(http://<?=$double_stack_auth_mobile?>/images/fenjin.jpg)
                }
            </style>
        <? } ?>
        <title>认证计费系统</title>
        <script language="javascript" src="js/srun_portal.js"></script>
        <script>
            $(document).ready(function () {
                $(".row").html(ad_data.footer)

                var url = $("input[name='url']").val();

                if(url) {
                    setTimeout("redirect()", 2000);
                }
            });
        </script>
    </head>

    <body>
        <div class="" onkeydown="BindEnter(event)">
            <div class="header gray">认证计费系统，请登录</div>

            <div style="width:90%;margin:0 auto;">
                <?php if ($double_stack_auth_mobile && $is_login) { ?>
                    <form name="form1" method="post" action="http://<?= $double_stack_auth_mobile ?>/<?= basename(__FILE__) ?>"
                          id="login_form" class="form">
                        <input type="hidden" name="action" value="auto_login">
                        <input type="hidden" name="info" value="<?= $info ?>">
                    </form>
                <?php } ?>

                <?php if (!$is_login && !$is_logout) { ?>
                    <form name="form2" method="post" action="<?= basename(__FILE__) ?>" id="login_form" class="form"
                          onsubmit="return check(this)">
                        <input type="hidden" name="action" value="login">
                        <input type="hidden" name="ac_id" value="<?= $ac_id ?>">
                        <input type="hidden" name="user_ip" value="<?= $auth_info["user_ip"] ?>">
                        <input type="hidden" name="nas_ip" value="<?= $auth_info["nas_ip"] ?>">
                        <input type="hidden" name="user_mac" value="<?= $auth_info["user_mac"] ?>">
                        <input type="hidden" name="url" value="<?= $auth_info["url"] ?>">

                        <div style=" text-align: center; font-size: 14px;color:red;">
                            <p><?= ($msg) ? $msg : $message ?></p>
                        </div>
                        <div id="user_login">
                            <div data-role="fieldcontain">
                                <input id="txtuser" type="name" placeholder="用户名*" data-theme="c" name="username" tabindex="1"
                                       value="<?= $my_cookie["user_name"] ?>" class="pass">
                            </div>
                            <div data-role="fieldcontain">
                                <input id="txtPwd" type="password" placeholder="密码*" name="password" data-theme="c" tabindex="2"
                                       value="<?= $my_cookie["user_password"] ?>" class="pass" AUTOCOMPLETE="OFF">
                            </div>
                        </div>

                        <input type="submit" class="btn btn-warning tl-caption" value="登录">&nbsp;
                        <input type="button" class="btn btn-danger tl-caption" style="float: right;" layer="on" value="注销"
                               onclick="document.aspnetForm.action.value='logout';document.aspnetForm.submit();">

                        <div class="f14 cdgray" style="font-weight: normal"></div>
                    </form>

                <?php } else { ?>

                    <form name="form3" method="post" action="<?= basename(__FILE__) ?>?ac_id=<?= $ac_id ?>" id="login_form"
                          class="form" onSubmit="return do_login()">
                        <input type="hidden" name="action" value="auto_logout">
                        <input type="hidden" name="info" value="<?= $info ?>">
                        <input type="hidden" name="user_ip" value="<?= $user_ip ?>">
                        <input type="hidden" name="url" value="<?= $return_url ?>">

                        <div style=" text-align: center; padding:20px 0 20px;font-size: 24px; color:orange" id="login_ok_date">
                            <?= $res ?>
                        </div>
                        <?php if (!$is_logout) { ?>
                            <div style=" text-align: center; padding:20px 0 10px;" id="login_ok_button">
                                <input type="submit" class="btn btn-danger tl-caption" value="注销">&nbsp;&nbsp;
                                <?php if ($double_stack_auth_mobile && !$double_stack_authed) { ?>
                                    <input type="button"
                                           class="btnl bor-sdw-crv ui-btn ui-btn-up-e ui-btn-inline ui-shadow ui-btn-corner-all click_enter"
                                           value="登录到<?= $other_stack ?>" onclick="document.form1.submit();">&nbsp;
                                <?php } ?>
                            </div>
                        <?php } else { ?>
                            <div style=" text-align: center; padding:20px 0 10px;" id="login_ok_button">
                                点击 [<a href="srun_portal.php">此处</a>] 重新登录
                            </div>
                        <?php } ?>
                    </form>

                <?php } ?>
            </div>
        </div>

        <div class="row" style="padding-top: 30px;text-align: center;">
            <a href="" class="ui-link">万和城</a>
        </div>
        <!--FP-WEB33 -->
        <div class="ui-loader ui-corner-all ui-body-a ui-loader-default">
            <span class="ui-icon ui-icon-loading"></span>

            <h1>载入中...</h1>
        </div>
    </body>
</html>