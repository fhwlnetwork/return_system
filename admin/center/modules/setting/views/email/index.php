<?php

use center\widgets\Alert;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

$this->title = Yii::t('app', 'T40000');
?>

<div class="page page-table">

    <h3 class="page-header">
        <i class="fa fa-envelope-o fa-fw"></i>&nbsp;&nbsp;<?= Html::encode(Yii::t('app', 'T40000')) ?>
    </h3>

    <?= Alert::widget() ?>

    <section class="panel panel-default">
        <div class="panel-body">
            <?php $form = ActiveForm::begin([
                'layout' => 'horizontal',
                'fieldConfig' => [
                    'template' => "{label}\n{beginWrapper}\n{input}\n{error}\n{endWrapper}\n{hint}",
                    'horizontalCssClasses' => [
                        'label' => 'col-sm-2',
                        'offset' => 'col-sm-offset-2',
                        'wrapper' => 'col-sm-4',
                        //'error' => 'col-sm-6',
                        //'hint' => 'col-sm-4',
                    ],
                ],
            ]); ?>

            <?= $form->field($model, 'host')->textInput(); ?>
            <?= $form->field($model, 'port')->textInput(); ?>
            <?= $form->field($model, 'nickname')->textInput(); ?>
            <?= $form->field($model, 'username')->textInput(); ?>
            <?= $form->field($model, 'password')->passwordInput(); ?>
            <?=
            $form->field($model, 'encryption', [
                'template' => "<div class=\"col-md-offset-3 col-lg-3\">{input}</div>\n<div class=\"col-lg-5\">{error}</div>",
            ])->checkbox(); ?>

            <div class="form-group">
                <div class="col-sm-10 col-sm-offset-2">
                    <?= Html::submitButton(Yii::t('app', 'save'), ['class' => 'btn btn-success']) ?>
                    <?=
                    Html::a(Yii::t('app', 'T40006'), ['test'], [
                        'class' => 'btn btn-primary',
                        'data' => [
                            'method' => 'post',
                        ],
                    ]) ?>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </section>
</div>
