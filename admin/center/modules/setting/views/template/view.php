<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model center\modules\setting\models\SmsTemplate */

$this->title = $model->name;
?>

<div class="page">

    <h3 class="page-header">
        <i class="fa fa-info-circle"></i>&nbsp;&nbsp;<?= Html::encode($this->title) ?>
        <?= Html::a(yii::t('app', 'goBack'), 'index', ['class' => 'btn btn-info btn-sm pull-right']) ?>
    </h3>

    <p style="display: none;">
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?=
        Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <div class="panel panel-body">
        <?=
        DetailView::widget([
            'model' => $model,
            'attributes' => [
                'id:html',
                'name:html',
                'join_ali:html',
                'created_at:datetime',
                [
                    'label' => Yii::t('app', 'Status'),
                    'value' => $model->status == 1 ? Yii::t('app', 'sms help11') : Yii::t('app', 'sms help10')
                ],
                'content:html',
                'instructions:html',
            ],
        ]) ?>
    </div>

</div>
