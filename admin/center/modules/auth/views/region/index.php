<?php
use yii\helpers\Html;

$this->title = Yii::t('app', 'region structure');
$attributes = Yii::$app->user->can('auth/region/attributes');
?>

<div class="padding-top-15px" xmlns="http://www.w3.org/1999/html">
    <?= $this->render('/layouts/nav'); ?>

    <div class="col-lg-10">
        <h3 class="page-header">
            <i class="glyphicon glyphicon-user"></i>&nbsp;&nbsp;<?= Html::encode($this->title) ?>
            <?= $attributes ? Html::a('<i class="glyphicon glyphicon-map-marker"></i>&nbsp;' . Yii::t('app', 'attributes'),['attributes'],['class' => 'btn btn-success pull-right']) : ''; ?>
        </h3>

        <?= \center\widgets\Alert::widget(); ?>

        <div>
            <!-- 组织结构图页面START -->
            <div class="panel panel-default">
                <div class="panel-heading">
                    <strong><span class="glyphicon glyphicon-th-large"></span> <?= Yii::t('app', 'region structure chart') ?> </strong>
                </div>

                <div class="panel-body">
                    <?= $this->render('index_view'); ?>
                </div>
            </div>
            <!-- 组织结构图页面STOP -->

        </div>
    </div>
</div>