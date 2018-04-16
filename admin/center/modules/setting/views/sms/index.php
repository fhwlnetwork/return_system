<?php

use center\widgets\Alert;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

$this->title = Yii::t('app', 'setting/sms/index');
?>

<div class="page page-table">

    <!-- title -->
    <h3 class="page-header">
        <i class="glyphicon glyphicon-comment size-h4"></i>&nbsp;&nbsp;<?= Html::encode($this->title) ?>
        <?= Html::a(yii::t('app', 'setting/template/index'), '/setting/template/index', ['class' => 'pull-right btn btn-info btn-sm']) ?>
    </h3>

    <!-- content start-->
    <div class="panel panel-default">

        <?= Alert::widget() ?>

        <div class="panel-body">

            <?php $form = ActiveForm::begin([
                'layout' => 'horizontal',
                'id' => 'extends-field-form',
                'fieldConfig' => [
                    'template' => "{label}\n{beginWrapper}\n{input}\n{error}\n{endWrapper}\n{hint}",
                    'horizontalCssClasses' => [
                        'label' => 'col-sm-2',
                        'wrapper' => 'col-sm-4',
                        'error' => '',
                        'hint' => 'col-xs-5',
                    ],
                ],
            ]); ?>

            <?=
            $form->field($model, 'sms_type', [
                'inputOptions' => [
                    'ng-model' => 'sms_type',
                    'ng-init' => 'sms_type=' . $model->sms_type,
                ]
            ])->dropDownList($model->getAttributeList()['sms_type']); ?>

            <!-- 深澜表单 -->
            <div data-ng-if="sms_type==0">
                <?= $form->field($model, 'name') ?>
                <?= $form->field($model, 'sign')->hint(Yii::t('app', 'sms help0')); ?>
                <?= $form->field($model, 'class')->textarea()->hint(Yii::t('app', 'sms help3')); ?>
                <?= $form->field($model, 'setting')->textarea(['rows' => 4])->hint(Yii::t('app', 'sms help4')) ?>
            </div>

            <!-- 第三方表单 -->
            <div data-ng-if="sms_type==1">
                <?= $form->field($model, 'name') ?>
                <?= $form->field($model, 'class')->textarea()->hint(Yii::t('app', 'sms help6')); ?>
                <?= $form->field($model, 'setting')->textarea(['rows' => 4])->hint(Yii::t('app', 'sms help7')) ?>
            </div>

            <div class="form-group">
                <div class="col-xs-10 col-xs-offset-2">
                    <?= Html::submitButton(Yii::t('app', 'save'), ['class' => 'btn btn-success']) ?>
                    <?= Html::a(Yii::t('app', 'test_sms'), ['msg'], ['class' => 'btn btn-primary', 'data-ng-hide' => 'sms_type==1']) ?>
                </div>
            </div>

            <div class="callout callout-info">
                <p><?= Yii::t('app', 'sms help5') ?></p>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
