<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

\center\assets\ReportAsset::newEchartsJs($this);
echo $this->render('/layouts/financial-menu');
//默认session选择的产品
if (Yii::$app->session->get('searchProductField')) {
    $searchField = array_keys(Yii::$app->session->get('searchProductField'));
} else {
    $searchField = [];
}
$this->title = Yii::t('app', 'report/financial/product')
?>
<div class="panel panel-default" data-ng-controller="report-financial">
    <?= \center\widgets\Alert::widget();?>
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
                    'class' => 'form-control inputDate',
                    'placeHolder' => Yii::t('app', 'start date')
                ]);
            ?>
        </div>

        <div class="col-md-2">
            <?= $form->field($model, 'stop_time', [
                'template' => '<div class="col-sm-12">{input}</div>'
            ])->textInput(
                [
                    'class' => 'form-control inputDate',
                    'placeHolder' => Yii::t('app', 'end date')
                ]);
            ?>
        </div>

        <div class="col-md-12 form-group" ng-cloak ng-show="advanced==1">
            <div class="col-md-2"><?= Yii::t('app', 'select product') ?></div>
            <div class="col-md-10">
                <?php if ($showField) {
                    echo Html::checkboxList('Financial[show_products][]', $searchField, $showField, ['class' => 'drag_inline']);
                } ?>
            </div>
        </div>

        <label class="text-info"><input type="checkbox" ng-model="advanced"/>
            <small><?= Yii::t('app', 'select product') ?></small>
        </label>

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

            <?= $this->render('/map/pie', [
                'data' => $source['data'],
                'bottom' => true,
                'subtext' => empty($model->data_source) ? $attribute['all'] : $attribute[$model->data_source]
            ]) ?>

            <table class="table table-bordered table-striped table-responsive table-hover">
                <thead>
                <tr>
                    <th nowrap="nowrap">
                        <div class="th"><?= Yii::t('app', 'products id') ?></div>
                    </th>
                    <th nowrap="nowrap">
                        <div class="th"><?= Yii::t('app', 'products name') ?></div>
                    </th>
                    <th nowrap="nowrap">
                        <div class="th"><?= Yii::t('app', 'transfer from fee') ?></div>
                    </th>
                    <th nowrap="nowrap">
                        <div class="th"><?= Yii::t('app', 'transfer to fee') ?></div>
                    </th>
                    <th nowrap="nowrap">
                        <div class="th"><?= Yii::t('app', 'Export expense breakdown') ?></div>
                    </th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($source['table'] as $id => $v): ?>
                    <tr>
                        <td><?= $id ?></td>
                        <td><?= $showField[$id] ?></td>
                        <td><?= (isset($v['from']) ? $v['from']: '0.00') .Yii::t('app', '$') ?></td>
                        <td><?= (isset($v['to']) ? $v['to']: '0.00') .Yii::t('app', '$') ?></td>
                        <td>
                            <a href="detail?start_time=<?= $model->start_time?>&stop_time=<?=$model->stop_time?>&product_id=<?php echo $id; ?>"><?= Html::button(Yii::t('app', '下载费用明细'), ['class' => 'btn btn-warning btn-xs']); ?></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="panel-body">
                <?= Yii::t('app', 'no record') ?>
            </div>
        <?php endif ?>
    </section>
</div>
