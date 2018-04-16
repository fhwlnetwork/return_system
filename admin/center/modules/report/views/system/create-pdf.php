<?php
/**
 * 生成pdf预览
 * Created by PhpStorm.
 * User: wjh
 * Date: 2017/6/22
 * Time: 10:35
 */
use yii\bootstrap\Html;
use yii\bootstrap\ActiveForm;

\center\assets\ReportAsset::newEchartsJs($this);

$this->title = Yii::t('app', 'report/system/create-pdf');

?>

<?php if ($show): ?>
    <!DOCTYPE html>
    <html lang="zh-cn">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>eduroam self-register</title>
        <!-- Bootstrap -->
        <link href="../../../styles/bootstrap.min.css" rel="stylesheet">
        <style type="text/css">
            .mytable {
                border: 1px solid #333;
                font-size: 16px;
                line-height: 26px;
            }

            input {
                width: 100%;
                height: 30px;
                line-height: 30px;
                font-size: 18px;
                border-top-style: none;
                border-right-style: none;
                border-bottom-style: none;
                border-left-style: none;
                background-color: #e9e9e9;
                padding-left: 10px;
                padding-right: 5px;
            }

            .my_tr {
                height: 40px;
                line-height: 40px;
                vertical-align: middle;
            }

            .my_tr td {
                padding-top: 20px;
                text-align: center;
                vertical-align: middle;
            }

            .special {
                line-height: 30px;
                height: 30px;
            }
        </style>
    </head>
    <body>
    <div style="width:100%;margin-top:20px;background: white;" class="page">
        <?php if ($model->sql_type != 'efficiency'): ?>
            <img src="../../<?= $filename ?>" alt="">
        <?php endif; ?>
    </div>

<?php else : echo $this->render('/layouts/system-menu'); ?>
    <div class="panel panel-success">
        <div class="panel-heading"><span
                class="glyphicon glyphicon-th-large"></span>   <?= Yii::t('app', 'monitor help3')?>
        </div>
    </div>
    <?= \center\widgets\Alert::widget()?>
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
            <div class="col-md-12" style="padding-left:30px;padding-top: 20px">
                <?= Html::activeRadioList($model, 'sql_type', $model::getAllSqlType()) ?>
            </div>
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
            <?= Html::submitButton(Yii::t('app', 'search'), ['class' => 'btn btn-success']) ?>
            <?= Html::submitButton(Yii::t('app', '24小时报告'), ['class' => 'btn btn-primary', 'name' => 'BaseModel[timePoint]', 'value' => 5]) ?>
            <?= Html::submitButton(Yii::t('app', 'yesterday'), ['class' => 'btn btn-info', 'name' => 'BaseModel[timePoint]', 'value' => 6]) ?>
            <?= Html::submitButton(Yii::t('app', 'last seven days'), ['class' => 'btn btn-danger', 'name' => 'BaseModel[timePoint]', 'value' => 7]) ?>

            <?= Html::a(Html::button(Yii::t('app', '生成pdf'), ['class' => 'btn btn-danger', 'style' => 'float:right;']), 'create-pdf-inner') ?>
            <div class="col-sm-12" style="text-align: left;color: #ffffff;">
                <?= $form->errorSummary($model); ?>
            </div>
            <?php $form->end(); ?>
        </div>
    </div>
<?php endif; ?>
    <div class="page row" style="margin:0;padding:0;">
        <?php if ($model->sql_type != 'efficiency'): ?>
            <?php if (!empty($source) && $source['code'] == 200) : ?>
                <?= $this->render('system_content', [
                    'model' => $model,
                    'data' => $source,
                    'save' => true,
                    'show' => $show,
                    'unit' => $unit
                ]); ?>
            <?php else : ?>
                <div class="panel panel-default table-dynamic"><?= $source['msg'] ?></div>
            <?php endif; ?>
        <?php else : ?>
            <?php if (!empty($source) && $source['code'] == 200) : ?>
                <?php foreach ($source['base'] as $id): ?>
                    <?= $this->render('one-efficiency-detail', [
                        'id' => $id,
                        'series' => $source['series'][$id],
                        'xAxis' => $source['xAxis'],
                        'legends' => $source['legends'],
                        'model' => $model,
                        'header' => $source['table']['header'],
                        'table' => $source['table']['data'][$id],
                        'unit' => 'ms',
                        'text' => $model->device_ip . Yii::t('app', 'efficiency monitor'),
                        'subtext' => Yii::t('app', 'proc') . ':' . $id,
                        'show' => $show
                    ]) ?>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="panel panel-default table-dynamic"><?= $source['msg'] ?></div>
            <?php endif; ?>
        <?php endif; ?>

    </div>
<?php if ($show): ?>
    </body>
<?php endif; ?>