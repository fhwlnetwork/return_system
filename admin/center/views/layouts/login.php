<?php
use yii\helpers\Html;
?>

<header class="clearfix">

    <!-- needs to be put after logo to make it working-->
    <div class="menu-button" toggle-off-canvas>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <ul class="nav-right pull-right list-unstyled" style="margin-right: 20px;">
            <?= $this->render('language'); ?>
        </ul>
    </div>

</header>
