<?php
use yii\helpers\Html;

$this->title = Yii::t('app', 'orgnization');
?>

<div class="padding-top-15px" xmlns="http://www.w3.org/1999/html">
    <?= $this->render('/layouts/nav'); ?>

    <div class="col-lg-10">
        <h3 class="page-header">
            <i class="fa fa-users size-h4"></i>&nbsp;&nbsp;<?= Html::encode($this->title) ?>
        </h3>

        <?= \center\widgets\Alert::widget(); ?>

        <div>
            <!-- 组织结构图页面START -->
            <div class="panel panel-default">
                <div class="panel-heading">
                    <strong><span class="glyphicon glyphicon-th-large"></span> <?= Yii::t('app', 'Organization Chart') ?> </strong>
                </div>

                <div class="panel-body">
                    <?= $this->render('index_view'); ?>
                </div>
            </div>
            <!-- 组织结构图页面STOP -->

        </div>
    </div>
</div>