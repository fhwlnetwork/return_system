<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="viewport" content="initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no">
        <title>认证计费系统</title>
        <link rel="stylesheet" type="text/css" href="css/combined_1.css?33">

        <script type="text/javascript" src="js/jquery.js"></script>
        <script type="text/javascript" src="js/portal_data.js"></script>

        <script>
            $(document).ready(function () {
                $(".logo").html(ad_data.logo)
            });
        </script>
    </head>
    <body>
        <div class="body">
            <!-- 引到 div -->
            <div id="guide_div">
                <div class="logo">
                    <img src="img/logo.png" class="logo"/>
                </div>
                <div class="go_on_line_btn">
                    <?php
                    $url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'];
                    //$url  = 'http://202.112.134.185/mobile/srun_portal.php?userip=19.268.0.1&wlanid=131113131&url=1.1.1.1&ac_id=2';
                    $url = str_replace('srun_portal', 'portal_auth_page', $url);
                    $arr = pathinfo($url);
                    $url = $arr['dirname'] . '/' . $arr['basename'];
                    ?>
                    <a href="<?php echo $url; ?>" class="btn btn-warning tl-caption" id="on_line_btn_a">我要上网</a>
                </div>
            </div>
        </div>
    </body>
</html>

