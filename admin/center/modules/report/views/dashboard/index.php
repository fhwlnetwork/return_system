<?php

use center\extend\Tool;

\center\assets\ReportAsset::newEchartsJs($this);

/*** 权限控制*/
$canViewPay = Yii::$app->user->can('financial/pay/list');
$canViewRefund = Yii::$app->user->can('financial/refund/list');
$canViewCheck = Yii::$app->user->can('financial/checkout/list');

$this->title = Yii::t('app', 'Dashboard');
?>

    <div class="page page-dashboard">

        <!--用户详情-->
        <div class="row">
            <div class="col-lg-3 col-xsm-6">
                <div class="panel mini-box">
                <span class="box-icon bg-success">
                    <i class="fa fa-user-plus"></i>
                </span>
                    <div class="box-info">
                        <p class="size-h2">0</p>
                        <p class="text-muted"><?= Yii::t('app', 'report add user today') ?></p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-xsm-6">
                <div class="panel mini-box">
                <span class="box-icon bg-info">
                    <i class="fa fa-users"></i>
                </span>
                    <div class="box-info">
                        <p class="size-h2">0</p>
                        <p class="text-muted"><?= Yii::t('app', 'report all user') ?></p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-xsm-6">
                <div class="panel mini-box">
                <span class="box-icon bg-warning">
                    <i class="fa fa-user"></i>
                </span>
                    <div class="box-info">
                        <p class="size-h2">0</p>
                        <p class="text-muted"><?= Yii::t('app', 'report expire user') ?></p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-xsm-6">
                <div class="panel mini-box">
                <span class="box-icon bg-danger">
                    <i class="fa fa-user"></i>
                </span>
                    <div class="box-info">
                        <p class="size-h2">0</p>
                        <p class="text-muted"><?= Yii::t('app', 'report delete user') ?></p>
                    </div>
                </div>
            </div>

        </div>

        <!--系统报表-->
        <div class="panel panel-default" data-ng-controller="userProfileCtrl">
            <div class="panel-body">
                <div class="row">
                    <ul class="nav nav-tabs">
                        <li class="active"><a id="today" href="#" ng-model='today'
                                              ng-click="chg('today')"><?= Yii::t('app', 'Today') ?></a></li>
                        <li><a id="yesterday" href="#" ng-model='yesterday'
                               ng-click="chg('yesterday')"><?= Yii::t('app', 'Yesterday') ?></a></li>
                    </ul>

                </div>
                <!--服务器状态-->

                <br/>
                <?php
                if (!empty($systemStatus)) {
                    ?>

                    <?= $this->render('report-system', ['source' => $systemStatus]) ?>

                    <?php
                }
                ?>
            </div>
        </div>
    </div>
    </div>

<?php
$this->registerJs("
    $('#tab a').click(function (e) {
          e.preventDefault();//阻止a链接的跳转行为
          $(this).tab('show');//显示当前选中的链接及关联的content
    })
 ");
?>