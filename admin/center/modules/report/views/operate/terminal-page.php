<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use center\assets\ReportAsset;
use center\modules\visitor\models\Setting;

ReportAsset::echartsJs($this);
echo $this->render('/layouts/operate-menu');

$this->title = Yii::t('app', 'report/operate/index');

$unitVal = !empty($model->unit) ? $model->unit : 'days';
//单位长度下拉菜单
$unit = '<select name="TerminalTypeReport[unit]" id="TerminalTypeReport-unit">';
foreach(Setting::getAttributesList()['date'] as $key => $val) {
    if($key == $unitVal){
        $unit .= '<option value="' .$key. '" selected>' .$val. '</option>';
    } else {
        $unit .= '<option value="' .$key. '">' .$val. '</option>';
    }
}
$unit .= '</select>';
?>

<div class="panel panel-default" style="display: none;">
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
            <?= $form->field($model, 'start_At', ['template' => '<div class="col-sm-12">{input}</div>'])
                ->textInput(
                    [
                        'value' => isset($model->start_At) ? $model->start_At : date('Y-m-d 00:00', strtotime('-7 day')),
                        'class'=>'form-control inputDateHour',
                        'placeHolder'=>Yii::t('app', 'start time')
                    ]);
            ?>
        </div>

        <div class="col-md-2">
            <?= $form->field($model, 'stop_At', [
                'template' => '<div class="col-sm-12">{input}</div>'
            ])->textInput(
                    [
                        'value' => isset($model->stop_At) ? $model->stop_At : date('Y-m-d 00:00'),
                        'class'=>'form-control inputDateHour',
                        'placeHolder'=>Yii::t('app', 'end time')
                    ]);
            ?>
        </div>

        <div class="col-md-2">
            <div class="input-group" style="border:1px solid #cbd5dd">
                <?= Html::dropDownList('TerminalTypeReport[step]', isset($model->step) ? $model->step : '1', $model->getAttributesList()['step'], ['class' => 'form-control', 'style'=>'border:0px;height:32px;']); ?>
                <span class="input-group-addon" style="border:none;height:32px">
                    <?= $unit; ?>
                </span>
            </div>
        </div>

        <?= Html::submitButton('<i class="glyphicon glyphicon-search"></i>', ['class' => 'btn btn-line-info']) ?>

        <div class="col-sm-12" style="text-align: left;color: #ffffff;">
            <?= $form->errorSummary($model); ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

<!-- 为ECharts准备一个具备大小（宽高）的Dom -->
<div id="main" style="height:80%;"></div>


<!-- ECharts单文件引入 -->
<script type="text/javascript">
    // 路径配置
    require.config({
        paths: {
            echarts: '/lib/echarts/build/dist'
        }
    });

    // 使用
    require(
        [
            'echarts',
            'echarts/chart/pie',
            'echarts/chart/line',
            'echarts/chart/bar'
        ],
        function (ec) {
            // 基于准备好的dom，初始化echarts图表
			var myChart = ec.init(document.getElementById('main')	,{
				  noDataLoadingOption:{
				  text :"<?= Yii::t('app', 'user base help10') ?>",
				  effect : 'bubble',
				}
			});	

            var option = {
                tooltip : {
                    trigger: 'axis'
                },
                toolbox: {
                    show : true,
                    y: 'bottom',
                    feature : {
                        mark : {show: false},
                        dataView : {show: false, readOnly: false},
                        magicType : {show: true, type: ['line', 'bar', 'stack', 'tiled']},
                        restore : {show: true},
                        saveAsImage : {show: true}
                    }
                },
                calculable : true,
                legend: {
                    data:['pc','移动','其他','PC','移动端','Other']
                },
                xAxis : [
                    {
                        type : 'category',
                        splitLine : {show : true},
                        data : [<?= !empty($source['xAxis']) ? $source['xAxis'] : '1' ?>]
                    }
                ],
                yAxis : [
                    {
                        type : 'value',
                        position: 'right'
                    }
                ],
                series : ['']
            };

            // 为echarts对象加载数据
            myChart.setOption(option);
        }
    );
</script>