<?php
use yii\helpers\Html;
use center\assets\ReportAsset;
use yii\bootstrap\ActiveForm;
use center\extend\Tool;

ReportAsset::newEchartsJs($this);
echo $this->render('/layouts/operate-menu');

$this->title = Yii::t('app', 'report/error/login');
?>
<div class="panel panel-default">
    <div class="panel-body" style="padding: 15px">
        <?php
        $form = ActiveForm::begin([
            'layout' => 'horizontal',
            'fieldConfig' => [
                'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}",
                'horizontalCssClasses' => [],
            ],
        ]);
        ?>
        <div class="col-md-12">
            <div class="col-md-2">
                <?= $form->field($model, 'start_time', [
                    'template' => '<div class="col-sm-12">{input}</div>'
                ])->textInput(
                    [
                        'value' => isset($model->start_time) ? $model->start_time : date('Y-m-1'),
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
                        'value' => isset($model->stop_time) ? $model->stop_time : date('Y-m-d'),
                        'class' => 'form-control inputDate',
                        'placeHolder' => Yii::t('app', 'end time')
                    ]);
                ?>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-default btn-sm">
                    <a href="/report/error/user-login?start_At=<?= isset($model->start_At) ? $model->start_At : date('Y-m-d') ?>&stop_At=<?= isset($model->stop_At) ? $model->stop_At : '' ?>"
                       target="_blank">
                        <span class="glyphicon glyphicon-list"></span> <?= Yii::t('app', 'report/error/user-login') ?>
                    </a>
                </button>
            </div>
            <div class="col-md-1" <?php $length = ($lang == 'en') ? 90 : 60; ?>style="width:<?= $length ?>px;">
                <?= Html::submitButton(Yii::t('app', 'search'), ['class' => 'btn btn-success']) ?>
            </div>
            <div class="col-md-1" <?php $length = ($lang == 'en') ? 90 : 60; ?>style="width:<?= $length ?>px;">
                <?= html::submitButton(Yii::t('app', 'today'), ['class' => 'btn btn-primary', 'name' => 'ErrorBase[timePoint]', 'value' => '1']) ?>
            </div>
            <div class="col-md-1" <?php $length = ($lang == 'en') ? 110 : 60; ?>style="width:<?= $length ?>px;">
                <?= html::submitButton(Yii::t('app', 'yesterday'), ['class' => 'btn btn-primary', 'name' => 'ErrorBase[timePoint]', 'value' => '2']) ?>
            </div>
            <div class="col-md-1" <?php $length = ($lang == 'en') ? 150 : 54 ; ?>style="width:<?= $length ?>px;">
                <?= html::submitButton(Yii::t('app', 'last seven days'), ['class' => 'btn btn-primary', 'name' => 'ErrorBase[timePoint]', 'value' => '3']) ?>
            </div>
            <div class="col-md-1" <?php $length = ($lang == 'en') ? 150 : 60; ?>style="width:<?= $length ?>px;">
                <?= html::submitButton(Yii::t('app', 'last thirty days'), ['class' => 'btn btn-primary', 'name' => 'ErrorBase[timePoint]', 'value' => '4']) ?>
            </div>
        </div>
        <div class="col-sm-12" style="text-align: left;color: #ffffff;">
            <?= $form->errorSummary($model); ?>
        </div>
        <?php $form->end(); ?>
    </div>
</div>

<div class="row" style="border:none;margin: 10px auto;padding:0;margin-bottom:50px;overflow-x: auto;">
    <section class="panel panel-default table-dynamic" style="margin:0;padding:0;">
            <?= $this->render('/map/error', [
                'data' => $source,
                'name' => Yii::t('app', 'report/error/login')
            ]) ?>

    </section>

</div>