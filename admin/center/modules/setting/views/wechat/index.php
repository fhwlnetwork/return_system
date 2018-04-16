<?php
use center\widgets\Alert;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

$this->title = Yii::t('app', 'wechat008');
?>

<div class="page page-table">

    <h3 class="page-header">
        <i class="glyphicon glyphicon-wrench size-h4"></i>&nbsp;&nbsp;<?= Html::encode(Yii::t('app', 'wechat008')) ?>
        
    </h3>

    <?= Alert::widget() ?>

    <section class="panel panel-default padding-top-15px">
        <div class="panel-body">
            <?php $form = ActiveForm::begin([
                'layout' => 'horizontal',
                'fieldConfig' => [
                    'template' => "{label}\n{beginWrapper}\n{input}\n{endWrapper}\n{hint}\n{error}",
                    'horizontalCssClasses' => [
                        'label' => 'col-sm-2',
                        'offset' => 'col-sm-offset-2',
                        'wrapper' => 'col-sm-5',
                        //'error' => 'col-sm-6',
                        //'hint' => 'col-sm-4',
                    ],
                ],
                'options' => [
                    'enctype' => 'multipart/form-data'
                ]
            ]); ?>

            <?= $form->field($model, 'appid')->textInput(); ?>
            <?= $form->field($model, 'mchid')->textInput(); ?>
            <?= $form->field($model, 'key')->textInput(); ?>
            <?= $form->field($model, 'appsecret')->textInput(); ?>
            <?= $form->field($model, 'mode')->textInput(); ?>
            <?= $form->field($model, 'body')->inline()->radioList($model->getAttributeList()['subject_mode']); ?>
            <?= $form->field($model, 'appsecret')->textInput(); ?>
            <?= $form->field($model, 'notify_url')->textInput()->hint(Yii::t('app', 'wechat007')); ?>
            <div class="form-group">
                <div class="col-sm-10 col-sm-offset-4">
                    <?= Html::submitButton(Yii::t('app', 'save'), ['class' => 'btn btn-success']) ?>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </section>
</div>
