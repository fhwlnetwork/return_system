<?php
use center\widgets\SideNavWidget;
?>

<style>
    .col-lg-2 .list-group .list-group-item {
        padding: 10px;
    }
</style>

<div class="col-lg-2" style="padding-right: 0px;">
    <?=
    SideNavWidget::widget([
        'encodeLabels' => false,
        'items' => [
            [
                'label' => '&nbsp;&nbsp;<i class="fa fa-comments-o"></i> &nbsp;' . Yii::t('app', 'pwd_strong'),
                'url' => ['/user/user-setting/pwd'],
                'visible' => Yii::$app->user->can('user/user-setting/pwd'),
                'active' => Yii::$app->request->url == '/user/user-setting/pwd',
            ],
            [
                'label' => '&nbsp;&nbsp;<i class="fa fa-comments-o"></i> &nbsp;' . Yii::t('app', 'pwd_change'),
                'url' => ['/user/user-setting/pwd-change'],
                'visible' => Yii::$app->user->can('user/user-setting/pwd-change'),
                'active' => Yii::$app->request->url == '/user/user-setting/pwd-change',
            ],

        ],
    ]);
    ?>
</div>