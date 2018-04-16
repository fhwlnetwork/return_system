<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model center\modules\setting\models\SmsTemplate */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="sms-template-form">

    <?php $form = ActiveForm::begin([
        'layout' => 'horizontal',
        'fieldConfig' => [
            'template' => "{label}\n{beginWrapper}\n{input}\n{error}{endWrapper}\n{hint}",
            'horizontalCssClasses' => [
                'label' => 'col-sm-2',
                'offset' => 'col-sm-offset-2',
                'wrapper' => 'col-sm-5',
                'error' => '',
                'hint' => '',
            ],
        ],
    ]); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'join_ali')->hint(yii::t('app', 'sms help13'))->textInput(['maxlength' => true]); ?>

    <?= $form->field($model, 'status')->dropDownList($model->getAttributesLabel('status'), ['prompt' => '']) ?>

    <?= $form->field($model, 'content')->textarea(['maxlength' => true, 'rows' => 3]) ?>

    <?= $form->field($model, 'instructions')->textarea(['maxength' => true, 'rows' => 3]) ?>

    <?= $form->field($model, 'is_delete')->dropDownList($model->getAttributesLabel('is_delete'), ['prompt' => '']) ?>

    <div class="form-group">
        <label class="col-xs-2"></label>

        <div class="col-xs-5">
            <?= Html::submitButton($model->isNewRecord ? yii::t('app', 'add') : yii::t('app', 'edit'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

    <div class="callout callout-info">
        <p><?= Yii::t('app', 'sms template help') ?></p>
    </div>
</div>
