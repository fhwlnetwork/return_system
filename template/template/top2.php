<!-- Fixed navbar -->
<div class="navfilter">
</div>
<nav class="navbar navbar-default">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse"
                    data-target="#b-navbar-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="http://www.ldstars.com"><img src="picture/logo.png" alt="Logo" style="width:160px;height:auto;"></a>
        </div>
        <div class="collapse navbar-collapse" id="b-navbar-collapse">
            <ul id="menu-bulletin%e8%8f%9c%e5%8d%95" class="nav navbar-nav">
                <li id="menu-item-332"
                    class="menu-item menu-item-type-custom menu-item-object-custom menu-item-home menu-item-332">
                    <a title="首页" href="../index.php"><i class="fa fa-home red"></i>&nbsp;首页</a>
                </li>
                <li id="menu-item-331"
                    class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-331 dropdown">
                    <a title="视点" href="#" data-toggle="dropdown" class="dropdown-toggle" aria-haspopup="true">
                        <i class="fa fa-globe blue"></i>&nbsp;视点 <span class="caret"></span>
                    </a>
                    <ul role="menu" class=" dropdown-menu">
                        <li id="menu-item-236"
                            class="menu-item menu-item-type-taxonomy menu-item-object-category menu-item-236"><a
                                    title="故事" href="../index.php?r=schoolnews">校内新闻</a></li>
                        <li id="menu-item-235"
                            class="menu-item menu-item-type-taxonomy menu-item-object-category menu-item-235"><a
                                    title="互联网" href="../index.php?r=tradenews">行业新闻</a></li>
                        <li id="menu-item-310"
                            class="menu-item menu-item-type-taxonomy menu-item-object-category menu-item-310"><a
                                    title="物联网" href="../index.php?r=megagamenews">大赛信息</a></li>
                    </ul>
                </li>
                <li id="menu-item-269"
                    class="menu-item menu-item-type-custom menu-item-object-custom current-menu-ancestor current-menu-parent menu-item-has-children menu-item-269 dropdown">
                    <a title="职场风云" href="#" data-toggle="dropdown" class="dropdown-toggle" aria-haspopup="true">
                        <i class="fa fa-code navy"></i>&nbsp;职场风云 <span class="caret"></span>
                    </a>

                    <ul role="menu" class=" dropdown-menu">
                        <li id="menu-item-237"
                            class="menu-item menu-item-type-taxonomy menu-item-object-category menu-item-237"><a
                                    title="PHP" href="../index.php?r=pub_center">PHP</a></li>
                        <!-- <li id="menu-item-238"
                             class="menu-item menu-item-type-taxonomy menu-item-object-category menu-item-238"><a
                                 title="MATLAB" href="index.php?r=matlab">MATLAB</a></li>
                         <li id="menu-item-271"
                             class="menu-item menu-item-type-taxonomy menu-item-object-category menu-item-271"><a
                                 title="Javascript" href="index.php?r=js">Javascript</a></li>
                         <li id="menu-item-308"
                             class="menu-item menu-item-type-taxonomy menu-item-object-category menu-item-308"><a
                                 title="C/C++" href="index.php?r=c">C/C++</a></li>
                         <li id="menu-item-270"
                             class="menu-item menu-item-type-taxonomy menu-item-object-category current-menu-item menu-item-270 active">
                             <a title="Android" href="index.php?r=android">Android</a></li>
                         <li id="menu-item-272"
                             class="menu-item menu-item-type-taxonomy menu-item-object-category menu-item-272"><a
                                 title="谷歌插件" href="index.php?r=chrome">谷歌插件</a></li>
                         <li id="menu-item-239"
                             class="menu-item menu-item-type-taxonomy menu-item-object-category menu-item-239"><a
                                 title="前端设计" href="index.php?r=php5">前端设计</a></li>
                                 -->
                    </ul>
                </li>
                <!--
                <li id="menu-item-266"
                    class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-266 dropdown">
                    <a title="#" href="#" data-toggle="dropdown" class="dropdown-toggle" aria-haspopup="true">
                        <i class="fa fa-paper-plane yellow"></i>&nbsp;精彩校园 <span class="caret"></span>
                    </a>
                    <ul role="menu" class=" dropdown-menu">
                        <li id="menu-item-267"
                            class="menu-item menu-item-type-post_type menu-item-object-page menu-item-267"><a
                                title="#" href="index.php?r=freevpn">温馨校园</a></li>
                        <li id="menu-item-268"
                            class="menu-item menu-item-type-post_type menu-item-object-page menu-item-268"><a
                                title="#" href="index.php?r=pan">校友话园</a></li>
                    </ul>
                </li>-->

                <li id="menu-item-345"
                    class="menu-item menu-item-type-taxonomy menu-item-object-category menu-item-345">
                    <a title="苹果体验馆" href="../index.php?r=recruit"><i class="fa fa-apple"></i>&nbsp;人才招聘</a>
                </li>
                <li id="menu-item-309" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-309">
                    <a title="留言板" href="../index.php?r=message"><i class="fa fa-commenting purple"></i>&nbsp;留言板</a>
                </li>


                <li style="margin-left:10px;">

                    <?php
                    if(!empty($_SESSION['username'])) {//你已经赋值的ID ?>
                <li id="menu-item-266"
                    class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-266 dropdown" style="margin-left:165px;">
                    <a title="<?= isset($_SESSION['username']) ? $_SESSION['username'] : "" ?>" href="#" data-toggle="dropdown"
                       class="dropdown-toggle" aria-haspopup="true">
                        <?= isset($_SESSION['username']) ? $_SESSION['username'] : "" ?> <span class="caret"></span>
                    </a>
                    <ul role="menu" class=" dropdown-menu">
                        <li id="menu-item-267"
                            class="menu-item menu-item-type-post_type menu-item-object-page menu-item-267"><a
                                    title="#" href="./admin/template/index.php">个人中心</a></li>
                        <li id="menu-item-268"
                            class="menu-item menu-item-type-post_type menu-item-object-page menu-item-268"><a
                                    title="#" href="./logout.php">退出</a></li>
                    </ul>
                </li>
                <?php }else{ ?>
                <li id="menu-item-309"
                    class="menu-item menu-item-type-post_type menu-item-object-page menu-item-309"
                    style="margin-left:165px;">
                    <a title="登录" href="login.php">登录</a>
                    <?php }?>

                </li>





            </ul>
        </div>
    </div>
</nav>


<div class="banner">
    <form class="search-form center" action="http://www.ldstars.com">
        <div class="form-group">
            <!--<input type="text" name="s" id="bdcsMain" class="form-control" placeholder="关键字...">
            <button type="submit" class="btn btn-default"><i class="fa fa-search navy"></i></button>-->
        </div>
    </form>
</div>
