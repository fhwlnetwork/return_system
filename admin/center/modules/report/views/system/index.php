<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;


\center\assets\ReportAsset::newEchartsJs($this);

$this->title = Yii::t('app', 'report/system/index');
echo $this->render('/layouts/system-menu');
?>

<style>
    .system-basic ol li {
        height: 35px;
        line-height: 35px;
        border-bottom: 1px solid #eeeeee;
        padding-left: 10px;
    }

    .panel {
        margin-bottom: 10px;
        background-color: white;
        border: 1px solid transparent;
        border-radius: 2px;
        -webkit-box-shadow: 0 1px 1px rgba(0, 0, 0, 0.05);
        box-shadow: 0 1px 1px rgba(0, 0, 0, 0.05);
    }
</style>
<div class="panel panel-default">
    <div class="panel-body" style="padding: 10px">

        <?php
        $form = ActiveForm::begin([
            'layout' => 'horizontal',
            'fieldConfig' => [
                'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}",
                'horizontalCssClasses' => [],
            ],
        ]);
        ?>

        <div class="col-md-2">
            <?= $form->field($model, 'device_ip', [
                'template' => '<div class="col-sm-12">{input}</div>'
            ])->textInput(
                [
                    'class' => 'form-control',
                    'placeHolder' => Yii::t('app', 'device ip')
                ]);
            ?>
        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'start_time', [
                'template' => '<div class="col-sm-12">{input}</div>'
            ])->textInput(
                [
                    'class' => 'form-control inputDate',
                    'placeHolder' => Yii::t('app', 'start time')
                ]);
            ?>
        </div>

        <div class="col-md-2">
            <?= $form->field($model, 'stop_time', [
                'template' => '<div class="col-sm-12">{input}</div>'
            ])->textInput(
                [
                    'class' => 'form-control inputDate',
                    'placeHolder' => Yii::t('app', 'end time')
                ]);
            ?>
        </div>

        <!-- 选择用户组 end -->

        <?= Html::submitButton(Yii::t('app', 'search'), ['class' => 'btn btn-success']) ?>
        <?= Html::submitButton(Yii::t('app', 'this month'), ['class' => 'btn btn-primary', 'name' => 'BaseModel[timePoint]', 'value' => 1]) ?>
        <?= Html::submitButton(Yii::t('app', 'last month'), ['class' => 'btn btn-primary', 'name' => 'BaseModel[timePoint]', 'value' => 2]) ?>
        <?= Html::submitButton(Yii::t('app', 'this quarter'), ['class' => 'btn btn-success', 'name' => 'BaseModel[timePoint]', 'value' => 3]) ?>
        <?= Html::submitButton(Yii::t('app', 'last quarter'), ['class' => 'btn btn-danger', 'name' => 'BaseModel[timePoint]', 'value' => 4]) ?>
        <div class="col-sm-12" style="text-align: left;color: #ffffff;">
            <?= $form->errorSummary($model); ?>
        </div>
        <?php $form->end(); ?>
    </div>
</div>
<?=
   $this->render('system_content', [
       'data' => $data,
       'model' => $model,
       'unit' => '%'
   ])
?>
