<?php
use yii\helpers\Html;

$this->title = Yii::t('app', 'roles');
$roles = Yii::$app->user->can('auth/roles/create'); //添加角色按钮
?>

<div class="padding-top-15px">
    <?= $this->render('/layouts/nav'); ?>

    <div class="col-lg-10">

        <h3 class="page-header">
            <i class="fa fa-graduation-cap"></i>&nbsp;&nbsp;<?= Html::encode($this->title) ?>
            <?= $roles ? Html::a('<i class="glyphicon glyphicon-flag"></i>&nbsp;' . Yii::t('app', 'add roles'),['create'],['class' => 'btn btn-success pull-right']) : ''; ?>
        </h3>

        <div>
            <?= \center\widgets\Alert::widget(); ?>
            <?= $this->render('index_view', ['dataProvider' => $dataProvider, 'searchModel' => $searchModel]); ?>
        </div>

    </div>
</div>