<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

\center\assets\ReportAsset::newEchartsJs($this);
echo $this->render('/layouts/financial-menu');
//默认session选择的产品
if (Yii::$app->session->get('searchFinProject')) {
    $searchField = array_keys(Yii::$app->session->get('searchFinProject'));
} else {
    $searchField = [];
}
$this->title = Yii::t('app', 'report/financial/paytype')
?>
<div class="panel panel-default">
    <div class="panel-body" style="padding: 10px">

        <?php
        $form = ActiveForm::begin([
            'layout' => 'horizontal',
            'fieldConfig' => [
                'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}",
                'horizontalCssClasses' => [
                ],
            ],
        ]);
        ?>

        <div class="col-md-2">
            <?= $form->field($model, 'start_time', [
                'template' => '<div class="col-sm-12">{input}</div>'
            ])->textInput(
                [
                    'value' => isset($model->start_time) ? $model->start_time : date('Y-m-d 08:00'),
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
                    'value' => isset($model->stop_time) ? $model->stop_time : date('Y-m-d 08:00'),
                    'class' => 'form-control inputDate',
                    'placeHolder' => Yii::t('app', 'end time')
                ]);
            ?>
        </div>

        <?= Html::submitButton(Yii::t('app', 'search'), ['class' => 'btn btn-success']) ?>

        <div class="col-sm-12" style="text-align: left;color: #ffffff;">
            <?= $form->errorSummary($model); ?>
        </div>
        <?php $form->end(); ?>
    </div>
</div>
<div class="row" style="border:none;margin: 0;padding:0;margin-top:10px;overflow-x: auto;">
    <section class="panel panel-default table-dynamic" style="margin:0;padding:0;">
        <div class="panel-heading"><strong><span
                    class="glyphicon glyphicon-th-large"></span> <?= Yii::t('app', 'search result') ?></strong>

        </div>
        <div style="clear:both;"></div>
        <?php if (!empty($source['table'])) : ?>
            <?= $this->render('/map/report', [
                'data' => $source['data'],
                'title' => Yii::t('app', 'report/financial/paytype'),
                'subtext' => Yii::t("app", "statistics by pay type"),
                'type' => Yii::t('app', 'amount')
            ]) ?>

        <?php else: ?>
            <div class="panel-body">
                <?= Yii::t('app', 'no record') ?>
            </div>
        <?php endif ?>
    </section>
</div>