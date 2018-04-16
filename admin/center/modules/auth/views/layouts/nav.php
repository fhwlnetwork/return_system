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
                'label' => '&nbsp;&nbsp;<i class="fa fa-users"></i> &nbsp;' . Yii::t('app', 'orgnization'),
                'url' => ['/auth/structure/index'],
                'visible' => Yii::$app->user->can('auth/structure/index'),
                'active' => Yii::$app->controller->id == 'structure',
            ],
            [
                'label' => '&nbsp;&nbsp;<i class="fa fa-graduation-cap"></i> &nbsp;' . Yii::t('app', 'roles'),
                'url' => ['/auth/roles/index'],
                'visible' => Yii::$app->user->can('auth/roles/index'),
                'active' => Yii::$app->controller->id == 'roles',
            ],
            [
                'label' => '&nbsp;&nbsp;<i class="fa fa-male"></i> &nbsp;' . Yii::t('app', 'manager'),
                'url' => ['/auth/assign/index'],
                'visible' => Yii::$app->user->can('auth/assign/index'),
                'active' => Yii::$app->controller->id == 'assign',
            ],
            [
                'label' => '&nbsp;&nbsp;<i class="fa fa-male"></i> &nbsp;' . Yii::t('app', 'major'),
                'url' => ['/auth/major/index'],
                'visible' => Yii::$app->user->can('auth/major/index'),
                'active' => Yii::$app->controller->id == 'major',
            ],
            [
                'label' => '&nbsp;&nbsp;<i class="fa fa-male"></i> &nbsp;' . Yii::t('app', 'work'),
                'url' => ['/auth/work/index'],
                'visible' => Yii::$app->user->can('auth/work/index'),
                'active' => Yii::$app->controller->id == 'work',
            ],
            [
                'label' => '&nbsp;&nbsp;<i class="fa fa-comments-o"></i> &nbsp;' . Yii::t('app', 'instructions'),
                'url' => ['/auth/show/index'],
                'visible' => Yii::$app->user->can('auth/show/index'),
                'active' => Yii::$app->request->url == '/auth/show/index',
            ],

        ],
    ]);
    ?>
</div>