<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

\center\assets\ReportAsset::newEchartsJs($this);
$this->title = Yii::t('app','report/financial-m/product');
echo $this->render('/layouts/financial-menu');
?>

<div class="panel panel-default" data-ng-controller="report-financial">
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
            <?= $form->field($model, 'data_source', [
                'template' => '<div class="col-sm-12">{input}</div>'
            ])->dropDownList($model::getAttributesList()['data_source']);
            ?>
        </div>

        <div class="col-md-2">
            <?= $form->field($model, 'statistical_cycle', [
                'template' => '<div class="col-sm-12">{input}</div>'
            ])->dropDownList($model::getAttributesList()['statistical_cycle'], [
                'id' => 'statistical_cycle',
                'ng-model' => 'cycle_value',
                'ng-change' => 'getInputDate()'
            ]);
            ?>
        </div>

        <div class="col-md-2" data-ng-show="cycle_value=='day'">
            <?= $form->field($model, 'start_time_day', [
                'template' => '<div class="col-sm-12">{input}</div>'
            ])->textInput(
                [
                    'value' => isset($params['start_time_day']) ? $params['start_time_day'] : '',
                    'class' => 'form-control inputDate',
                    'placeHolder' => Yii::t('app', 'select statistical cycle', ['type' => Yii::t('app', 'date')])
                ]);
            ?>
        </div>

        <div class="col-md-2" data-ng-show="cycle_value=='week'">
            <?= $form->field($model, 'start_time', [
                'template' => '<div class="col-sm-12">{input}</div>'
            ])->textInput(
                [
                    'value' => isset($params['start_time']) ? $params['start_time'] : '',
                    'class' => 'form-control inputWeek',
                    'placeHolder' => Yii::t('app', 'start date')
                ]);
            ?>
        </div>

        <div class="col-md-2" data-ng-show="cycle_value=='week'">
            <?= $form->field($model, 'end_time', [
                'template' => '<div class="col-sm-12">{input}</div>'
            ])->textInput(
                [
                    'value' => isset($params['end_time']) ? $params['end_time'] : '',
                    'class' => 'form-control inputWeek',
                    'placeHolder' => Yii::t('app', 'end date')
                ]);
            ?>
        </div>

        <div class="col-md-2" data-ng-show="cycle_value=='year'">
            <?= $form->field($model, 'start_time_year', [
                'template' => '<div class="col-sm-12">{input}</div>'
            ])->textInput(
                [
                    'value' => isset($params['start_time_year']) ? $params['start_time_year'] : date('Y'),
                    'class' => 'form-control inputYear',
                    'placeHolder' => Yii::t('app', 'select statistical cycle', ['type' => Yii::t('app', 'years')])
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

        <?php $form->end(); ?>

    </div>
</div>

<div id="main_pie" style="height:600px;"></div>
<script type="text/javascript">
    var myChart = echarts.init(document.getElementById('main_pie'));
    myChart.setOption(<?=$echarts?>);
</script>