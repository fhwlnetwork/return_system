<?php
use yii\helpers\Html;
?>

<header class="clearfix">
    <a href="#/" data-toggle-min-nav class="toggle-min"><i class="fa fa-bars"></i></a>

    <!-- Logo -->
    <div class="logo"><a href="#/"><span><?= Yii::t('app', 'company') ?></span></a></div>

    <!-- needs to be put after logo to make it working-->
    <div class="menu-button" toggle-off-canvas>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
    </div>

    <div class="top-nav">
        <ul class="nav-right pull-right list-unstyled">
            <?= $this->render('version'); ?>
            <?= $this->render('language'); ?>
            <?= $this->render('userMessage'); ?>
        </ul>        
    </div>
</header>
