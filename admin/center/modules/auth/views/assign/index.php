<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel center\modules\auth\models\AuthAssignmentSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = \Yii::t('app', 'manager');

$canAdd = Yii::$app->user->can('auth/assign/signup');
$canBatch = Yii::$app->user->can('auth/assign/batch');
?>
<div class="padding-top-15px">
    <?= $this->render('/layouts/nav'); ?>

    <div class="col-lg-10">

        <h3 class="page-header">
            <i class="fa fa-male"></i>&nbsp;&nbsp;<?= Html::encode($this->title) ?>
            <div class="pull-right">
                <?= $canAdd ? Html::a('<i class="glyphicon glyphicon-flag"></i>&nbsp;' . Yii::t('app', 'User Manager'), ['signup'], ['class' => 'btn btn-success']) : ''; ?>
                <?= $canBatch ? Html::a('<i class="glyphicon glyphicon-flag"></i>&nbsp;' . Yii::t('app', '批量添加学生'), ['batch'], ['class' => 'btn btn-danger']) : ''; ?>
            </div>
        </h3>

        <div>
            <?= \center\widgets\Alert::widget(); ?>
            <?= $this->render('index_view', ['dataProvider' => $dataProvider, 'searchModel' => $searchModel]); ?>
        </div>

    </div>
</div>