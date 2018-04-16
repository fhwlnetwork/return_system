<?php
use center\widgets\SideNavWidget;

$canSetting = Yii::$app->user->can('message/default/index') ? [
    'label' => Yii::t('app', 'message/default/index'),
    'url' => ['/message/default/index'],
    'active' => Yii::$app->controller->id == 'default',
] : '';
$canGroup = Yii::$app->user->can('message/work/index') ? [
    'label' => Yii::t('app', 'message/work/index'),
    'url' => ['/message/work/index'],
    'active' => Yii::$app->controller->id == 'work',
] : '';
$canVisitorShortGroup = Yii::$app->user->can('message/news/index') ? [
    'label' => Yii::t('app', 'message/news/index'),
    'url' => ['/message/news/index', 'type' => 'short'],
    'active' => Yii::$app->controller->id == 'news',
] : '';
?>

<style>
    .col-lg-2 .list-group .list-group-item{padding:10px;}
</style>

<div class="col-lg-2  col-md-2 col-xs-2" style="padding-right: 0px;">
    <?=
    SideNavWidget::widget([
        'encodeLabels' => false,
        'items' => [
            $canSetting,
            $canGroup,
            $canVisitorShortGroup,
        ],
    ]);
    ?>
</div>