<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model center\modules\setting\models\SmsTemplate */

$this->title = yii::t('app', 'setting/template/update') .': ' . $model->name;
?>
<div class="page">

    <h3 class="page-header">
        <i class="fa fa-edit"></i>&nbsp;&nbsp;<?= Html::encode($this->title) ?>
        <?= Html::a(yii::t('app', 'goBack'), 'index', ['class' => 'btn btn-info btn-sm pull-right']) ?>
    </h3>

    <div class="panel panel-body">
        <?=
        $this->render('_form', [
            'model' => $model,
        ]) ?>
    </div>

</div>
