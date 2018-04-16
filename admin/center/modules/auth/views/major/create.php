<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>
<div class="major-create">
    <div class="col-lg-10 col-md-10">
        <?php $form = ActiveForm::begin(); ?>
        <?= $form->field($model, 'major_name')->textInput(['maxlength' => true, 'id' => 'major_name']) ?>
        <div class="form-group">
            <?= Html::button($model->isNewRecord ? '创建' : '修改', ['class' => $model->isNewRecord ? 'btn btn-success add' : 'btn btn-primary edit']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>
</div>
<?php
$js = <<<JS
$('.add').click(function() {
    var name = $('#major_name').val();
    if (name) {
        $.post('create', {'major_name' : name}, function (res) {
            
        })
    } else {
        layer.msg("专业名称不能为空");
    }
  
})
JS;
$this->registerJs($js);
