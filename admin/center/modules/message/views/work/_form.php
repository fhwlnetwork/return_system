<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use center\widgets\Alert;

$canList = Yii::$app->user->can('user/interaction/index');
$canEdit = Yii::$app->user->can('user/interaction/edit');
$canListAll = Yii::$app->user->can('user/interaction/index-all');

if ($action != 'add') {
    $this->title = \Yii::t('app', 'user/interaction/' . $action);
}
/* @var $this yii\web\View */
/* @var $model center\modules\user\models\UserCloundComplaints */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="col-md-12">
    <?= Alert::widget() ?>
    <div class="panel-heading"><strong>
            <?php if ($action == 'edit') {
                echo '<span class="glyphicon glyphicon-edit"></span> ';
                echo Yii::t('app', 'edit');
            } else if ($action == 'view' || $action == 'view-all') {
                echo '<span class="glyphicon glyphicon-check"></span> ';
                echo Yii::t('app', 'view');
            } else if ($action == 'add') {
            } ?>
        </strong></div>

    <div class="panel panel-default">
        <div class="panel-body">
            <?php if ($action == 'view' || $action == 'edit'): ?>
                <?php $form = ActiveForm::begin([
                    'layout' => 'horizontal',
                    'fieldConfig' => [
                        'template' => "{label}\n{beginWrapper}\n{input}\n{error}\n{hint}\n{endWrapper}",
                        'horizontalCssClasses' => [
                            'label' => 'col-sm-2',
                            'offset' => 'col-sm-offset-4',
                            'wrapper' => 'col-sm-8',
                            'error' => '',
                            'hint' => '',
                        ],
                    ],
                ]); ?>
            <?php else: ?>
                <?php $form = ActiveForm::begin(['action' => yii\helpers\Url::to('add'), 'options' => ['enctype' => 'multipart/form-data'],
                        'layout' => 'horizontal',
                        'fieldConfig' => [
                            'template' => "{label}\n{beginWrapper}\n{input}\n{error}\n{hint}\n{endWrapper}",
                            'horizontalCssClasses' => [
                                'label' => 'col-sm-2',
                                'offset' => 'col-sm-offset-4',
                                'wrapper' => 'col-sm-8',
                                'error' => '',
                                'hint' => '',
                            ],
                        ],]
                ); ?>
            <?php endif; ?>
            <?= $form->field($model, 'company_name')?>
            <?= $form->field($model, 'salary')->hint('格式为100-500')?>
            <?= $form->field($model, 'major_id')->dropDownList($major)?>
            <div class="form-group field-workinformation-work_id required">
                <label class="control-label col-sm-2" for="workinformation-work_id">工作名称</label>
                <div class="col-sm-8">
                    <select id="workinformation-work_id" class="form-control" name="WorkInformation[work_id]">
                        <option value="">请选择</option>
                    </select>
                    <div class="help-block help-block-error "></div>

                </div>
            </div>
            <?= $form->field($model, 'desc')->textarea()?>
            <?= $form->field($model, 'require')->textarea()?>
            <div class="form-group" style="margin-left:220px;">
                <?php if ($action == 'add'): ?>
                    <?= Html::submitButton(Yii::t('app', 'save'), ['class' => 'btn btn-success']) ?>
                <?php endif; ?>
                <?php if ($canList && $action == 'view'): ?>
                    <?= Html::a(Html::button(Yii::t('app', '返回'), ['class' => 'btn btn-primary']),
                        ['index']
                    ) ?>
                <?php endif; ?>
                <?php if ($canEdit && $action == 'edit'): ?>
                    <?= Html::submitButton(Yii::t('app', 'save'), ['class' => 'btn btn-success']) ?>
                <?php endif; ?>
                <?php if ($canListAll && $action == 'edit' || $action == 'view-all'): ?>
                    <?= Html::a(Html::button(Yii::t('app', '返回'), ['class' => 'btn btn-primary']),
                        ['index']
                    ) ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
<?php
$isNew = $model->getIsNewRecord();
$js = <<<JS
$('#workinformation-major_id').change(function() {
    var major_id = $('#workinformation-major_id').val();
    $.get('works', {'major_id' : major_id}, function (res) {
        res = eval('('+res+')');
        console.log(res);
        if (res.code != 1) {
            layer.msg(res.msg);
        } else {
           var _tar =  $('#workinformation-work_id');
           $.each(res.data, function(k, v) {
               if (!'{$isNew}') {
                   if (k == '$model->work_id') {
                          $("<option/>", { "value": k, 'selected': true}).text(v.major_name).appendTo(_tar);
                   } else {
                          $("<option/>", { "value": k}).text(v.work_name).appendTo(_tar);
                   }
                   
               } else {
                      $("<option/>", { "value": k}).text(v.work_name).appendTo(_tar);
               }
              
          })
        }
    })
})
$(document).ready(function () {
    if (!'{$isNew}') {
        $('.form-control').css('border', 'none');
        $('#workinformation-desc').css('height', '100px');
        $('#workinformation-require').css('height', '100px');
        var major_id = $('#workinformation-major_id').val();
        $.get('works', {'major_id' : major_id}, function (res) {
        res = eval('('+res+')');
        console.log(res);
        if (res.code != 1) {
            layer.msg(res.msg);
        } else {
           var _tar =  $('#workinformation-work_id');
           $.each(res.data, function(k, v) {
               if (!'{$isNew}') {
                   if (k == '$model->work_id') {
                          $("<option/>", { "value": k, 'selected': true}).text(v.work_name).appendTo(_tar);
                   } else {
                          $("<option/>", { "value": k}).text(v.work_name).appendTo(_tar);
                   }
                   
               } else {
                      $("<option/>", { "value": k}).text(v.work_name).appendTo(_tar);
               }
              
          })
        }
       })
    }
   }
)
JS;
$this->registerJs($js);
