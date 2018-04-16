<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use center\widgets\Alert;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $model \common\models\LoginForm */

$this->title = Yii::t('app', 'Login Title');
?>

<style>
    #loginform-verifycode {
        height:46px;
    }
</style>

<div class="page-signin">
    <div class="signin-header">
        <div class="container text-center">
            <section class="logo">
                <a href="#/"><?= Yii::t('app', 'company') ?></a>
            </section>
        </div>
    </div>

    <div class="signin-body">
        <div class="container">
            <div class="form-container">
                <?= Alert::widget(); ?>
                <?php $form = ActiveForm::begin(
                    [
                        'id' => 'login-form',
                        'class' => 'form-horizontal',
                        'enableClientScript' => false,
                        'options' => ['onsubmit' => 'return checkps()']
                    ]); ?>

                <div class="form-group"><?= $form->errorSummary($model); ?></div>

                <?=
                $form->field($model, 'username', [
                    'inputOptions' => [
                        'placeHolder' => Yii::t('app', 'Manager Name'),
                    ],
                    'template' => '<div class="input-group input-group-lg"><span class="input-group-addon"><span class="glyphicon glyphicon-user"></span></span>{input}</div><p class="help-block help-block-error"></p>',
                ])->label(false) ?>

                <?=
                $form->field($model, 'password', [
                    'inputOptions' => [
                        'placeHolder' => Yii::t('app', 'Manager Password'),
                    ],
                    'template' => '<div class="input-group input-group-lg"><span class="input-group-addon"><span class="glyphicon glyphicon-lock"></span></span>{input}</div><p class="help-block help-block-error"></p>',
                ])->passwordInput()->label(false) ?>

                <?=
                $form->field($model, 'verifyCode')->widget(\yii\captcha\Captcha::className(), [
                    'template' => ' <div class="input-group input-group-lg">
                                                <span class="input-group-addon"><span class="glyphicon glyphicon-text-width"></span></span>
												<div style="width:60%">{input}</div>
												<div style="width:25%;float:right;">{image}</div>
											</div>',
                ])->label(false); ?>

                <div class="form-group">
                    <?= Html::submitButton(Yii::t('app', 'Login'), ['class' => 'btn btn-primary btn-lg btn-block']); ?>
                </div>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>