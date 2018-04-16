<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model center\modules\product\models\MajorWorkRelation */
/* @var $form yii\widgets\ActiveForm */
?>

    <div class="major-work-relation-form">

        <?php $form = ActiveForm::begin(); ?>
        <div class="help-block help-block-error"
             style="color: #ffffff;"><?= $form->errorSummary($model); ?></div>
        <?= $form->field($model, 'work_name')->textInput(['maxlength' => true]) ?>
        <div class="form-group field-majorworkrelation-major_id has-success">
            <label class="control-label" for="majorworkrelation-major_id">专业名称</label>
            <select type="text" id="majorworkrelation-major_id" class="form-control" name="MajorWorkRelation[major_id]">
                <option value="">请选择</option>
            </select>
            <div class="help-block"></div>
        </div>
        <div class="form-group">
            <?= Html::submitButton($model->isNewRecord ? '增加' : '修改', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
            <?= Html::a('返回', 'index', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
<?php
$isNew = $model->isNewRecord;
$js = <<<js
$('document').ready(function() {
    $.get('major', {}, function(res) {
        res = eval('('+res+')');
        if (res.code != 1) {
            layer.msg(res.msg);
        } else {
           var _tar =  $('#majorworkrelation-major_id');
           $.each(res.data, function(k, v) {
               if (!'{$isNew}') {
                   if (k == '$model->major_id') {
                          $("<option/>", { "value": k, 'selected': true}).text(v.major_name).appendTo(_tar);
                   } else {
                          $("<option/>", { "value": k}).text(v.major_name).appendTo(_tar);
                   }
                   
               } else {
                      $("<option/>", { "value": k}).text(v.major_name).appendTo(_tar);
               }
              
          })
        }
    })
})
js;
$this->registerJs($js);
