<?php

use yii\helpers\Html;
use yii\grid\GridView;
use center\modules\setting\models\SmsTemplate;

/* @var $this yii\web\View */
/* @var $searchModel center\modules\setting\models\SmsTemplateSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = yii::t('app', 'setting/template/index');

$create = yii::$app->user->can('setting/template/create');
$update = yii::$app->user->can('setting/template/update');
$delete = yii::$app->user->can('setting/template/delete');

$attributes = SmsTemplate::getAttributesLabel();
?>

<div class="page">

    <h3 class="page-header">
        <i class="glyphicon glyphicon-list-alt size-h4"></i>&nbsp;&nbsp;<?= Html::encode($this->title) ?>
        <?= Html::a(yii::t('app', 'setting/template/create'), ['create'], ['class' => 'pull-right btn btn-success btn-sm']) ?>
        <?= Html::a(yii::t('app', 'goBack'), '/setting/sms/index', ['class' => 'pull-right btn btn-info btn-sm', 'style' => 'margin-right:10px']) ?>
    </h3>

    <?= center\widgets\Alert::widget(); ?>

    <div class="panel panel-body">
        <?=
        GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => [
                //['class' => 'yii\grid\SerialColumn'],

                [
                    'attribute' => 'id',
                    //'headerOptions' => ['width' => '30'],
                    //'options' => ['class' => 'col-xs-1']
                ],
                [
                    'attribute' => 'name',
                    //'options' => ['class' => 'col-xs-2'],
                    //'headerOptions' => ['width' => '170'],
                    'format' => 'text'
                ],
                [
                    'attribute' => 'join_ali',
                    //'headerOptions' => ['width' => '130'],
                    //'options' => ['class' => 'col-xs-1']
                ],
                [
                    'attribute' => 'status',
                    //'headerOptions' => ['width' => '120'],
                    //'options' => ['class' => 'col-xs-1'],
                    'content' => function ($dataProvider) {
                            return SmsTemplate::isAudit($dataProvider['status']);
                        },
                    'filter' => $attributes['status'],
                ],
                /*[
                    'attribute' => 'created_at',
                    'headerOptions' => ['width' => '150'],
                    'format' => 'datetime',
                ],*/
                [
                    'attribute' => 'content',
                    'class' => '\yii\grid\DataColumn',
                    'content' => function ($dataProvider) {
                            return yii\helpers\StringHelper::truncate($dataProvider->content, 30);
                        }
                ],
                // 'instructions',
                [
                    'attribute' => 'is_delete',
                    //'headerOptions' => ['width' => '80'],
                    //'options' => ['class' => 'col-xs-1'],
                    'content' => function ($dataProvider) {
                            return SmsTemplate::isDelete($dataProvider['is_delete']);
                        },
                    'filter' => $attributes['is_delete'],
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'header' => Yii::t('app', 'operate'),
                    //'headerOptions' => ['width' => '70'],
                    'options' => ['class' => 'col-xs-1'],
                    'template' => '{view}&nbsp;' . ($update ? '{update}&nbsp;' : ''),
                    //'template' => '{view}&nbsp;' . ($update ? '{update}&nbsp;' : '') . ($delete ? ' {delete}' : ''),
                ],
            ],
        ]); ?>
    </div>
</div>
