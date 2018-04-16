<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model center\modules\product\models\Major */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="major-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'major_name')->textInput(['maxlength' => true]) ?>


    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : '提交', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
