<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use center\assets\ReportAsset;
use center\extend\Tool;

ReportAsset::newEchartsJs($this);
echo $this->render('/layouts/online-menu');

$this->title = Yii::t('app', 'report/online/terminal');

if (Yii::$app->session->get('searchOsField')) {
    $searchField = array_keys(Yii::$app->session->get('searchOsField'));
} else {
    $searchField = [];
}
?>

<div class="panel panel-default">
    <div class="panel-body" style="padding: 10px">
        <?php
        $form = ActiveForm::begin([
            'layout' => 'horizontal',
            'fieldConfig' => [
                'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}"
            ],
        ]);
        ?>

        <div class="col-md-2">
            <?= $form->field($model, 'start_At', ['template' => '<div class="col-sm-12">{input}</div>'])
                ->textInput(
                    [
                        'value' => isset($model->start_At) ? $model->start_At : date('Y-m-d 08:00'),
                        'class' => 'form-control inputDate',
                        'placeHolder' => Yii::t('app', 'start time')
                    ]);
            ?>
        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'stop_At', [
                'template' => '<div class="col-sm-12">{input}</div>'
            ])->textInput(
                [
                    'value' => isset($model->stop_At) ? $model->stop_At : date('Y-m-d 22:00'),
                    'class' => 'form-control inputDate',
                    'placeHolder' => Yii::t('app', 'end time')
                ]);
            ?>
        </div>
        <div class="col-md-2">
            <?=
            $form->field($model, 'type', [
                'template' => '<div class="col-sm-12">{input}</div>'
            ])->dropDownList($attributes);
            ?>
        </div>
        <div class="col-md-12 form-group" ng-cloak ng-show="advanced==1">
            <div class="col-md-2"><?= Yii::t('app', 'Please Select') ?>.</div>
            <div class="col-md-10">
                <?= Html::checkboxList('OnlineReportClassName[showField][]', $searchField, $showField, ['class' => 'drag_inline']) ?>
            </div>
        </div>

        <label class="text-info"><input type="checkbox" ng-model="advanced"/>
            <small><?= Yii::t('app', 'advanced') ?></small>
        </label>


        <?= Html::submitButton(Yii::t('app', 'search'), ['class' => 'btn btn-success']) ?>
        <?= Html::submitButton(yii::t('app', 'this day'), ['class' => 'btn btn-warning', 'name' => 'timePoint', 'value' => 'Today']) ?>
        <?= Html::submitButton(yii::t('app', 'this week'), ['class' => 'btn btn-primary', 'name' => 'timePoint', 'value' => 'week']) ?>
        <?= Html::submitButton(yii::t('app', 'last week'), ['class' => 'btn btn-info', 'name' => 'timePoint', 'value' => 'last']) ?>
        <?= Html::submitButton(yii::t('app', 'this month'), ['class' => 'btn btn-warning', 'name' => 'timePoint', 'value' => 'month']) ?>


        <div class="col-sm-12" style="text-align: left;color: #ffffff;">
            <?= $form->errorSummary($model); ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
<div class="row" style="border:none;margin: 0;padding:0;margin-top:10px;overflow-x: auto;">
    <section class="panel panel-default table-dynamic" style="margin:0;padding:0;">
        <div class="panel-heading"><strong><span
                    class="glyphicon glyphicon-th-large"></span> <?= Yii::t('app', 'search result') ?></strong>

        </div>
        <div style="clear:both;"></div>
        <?php if (!empty($source['table'])) : ?>
            <div class="panel panel-default">
                <?php if ($model->flag): ?>
                    <?= $this->render('/map/report', [
                        'data' => $source['data'],
                        'model' => $model,
                        'title' => Yii::t('app', 'Terminal Name'). ':' .$showField[$searchField[0]],
                        'type' => $attributes[$model->sql_type].Yii::t('app', 'monitor'),
                    ]) ?>
                <?php else: ?>
                    <?= $this->render('/map/products', [
                        'data' => $source['data'],
                        'model' => $model,
                        'title' =>  Yii::t('app', 'Terminal Name'),
                        'type' => $attributes[$model->sql_type].Yii::t('app', 'monitor'),
                        'pro' => Yii::t('app', 'Terminal Name'),
                    ]) ?>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="panel-body">
                <?= Yii::t('app', 'no record') ?>
            </div>
        <?php endif ?>
    </section>
</div>

