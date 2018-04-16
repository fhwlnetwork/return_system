<?php

use yii\helpers\Html;
use center\extend\Tool;
use center\assets\ReportAsset;
use yii\bootstrap\ActiveForm;

ReportAsset::newEchartsJs($this);
echo $this->render('/layouts/online-menu');

if (Yii::$app->session->get('searchControlField')) {
    $searchField = array_keys(Yii::$app->session->get('searchControlField'));
} else {
    $searchField = [];
}

$this->title = Yii::t('app', Yii::$app->requestedRoute);
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
            <?=
            $form->field($model, 'start_At', ['template' => '<div class="col-sm-12">{input}</div>'])
                ->textInput(
                    [
                        'value' => isset($model->start_At) ? $model->start_At : date('Y-m-d H:00'),
                        'class' => 'form-control inputDate',
                        'placeHolder' => Yii::t('app', 'start time')
                    ]);
            ?>
        </div>

        <div class="col-md-2">
            <?=
            $form->field($model, 'stop_At', [
                'template' => '<div class="col-sm-12">{input}</div>'
            ])->textInput(
                [
                    'value' => isset($model->stop_At) ? $model->stop_At : date('Y-m-d H:00', strtotime('+1 hour')),
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
            <div class="col-md-2"><?= Yii::t('app', 'report online bilingfont2') ?>.</div>
            <div class="col-md-10">
                <?= Html::checkboxList('OnlineReportControl[showField][]', $searchField, $showField, ['class' => 'drag_inline']) ?>
            </div>
        </div>

        <label class="text-info"><input type="checkbox" ng-model="advanced"/>
            <small><?= Yii::t('app', 'advanced') ?></small>
        </label>

        <?= Html::submitButton(Yii::t('app', 'search'), ['class' => 'btn btn-success']) ?>

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
                        'title' => Yii::t('app', 'operate type Strategy Control') . ':'  .$showField[$searchField[0]],
                        'type' => $attributes[$model->sql_type]. Yii::t('app', 'monitor')
                    ]) ?>
                <?php else: ?>
                    <?= $this->render('/map/products', [
                        'data' => $source['data'],
                        'model' => $model,
                        'title' => Yii::t('app', 'operate type Strategy Control'),
                        'type' => $attributes[$model->sql_type]. Yii::t('app', 'monitor'),
                        'pro' => Yii::t('app', 'ploy_name')
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
