<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use center\modules\visitor\models\Setting;
use center\modules\report\models\OnlineReportPoint;

\center\assets\ReportAsset::echartsJs($this);
echo $this->render('/layouts/operate-menu');

$this->title = Yii::t('app', 'report/operate/online');
?>

<div class="panel panel-default">
    <div class="panel-body" style="padding: 10px">
        <?php
        $form = ActiveForm::begin([
            'layout' => 'horizontal',
            'fieldConfig' => [
                'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}"
            ],
        ]);
        ?>

            <div class="col-md-2">
                <?= $form->field($model, 'start_At', ['template' => '<div class="col-sm-12">{input}</div>'])
                    ->textInput(
                        [
                            'value' => isset($model->start_At) ? $model->start_At : date('Y-m-d H:00'),
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
                            'value' => isset($model->stop_At) ? $model->stop_At : date('Y-m-d H:00', strtotime('+1 hour')),
                            'class'=>'form-control inputDateHour',
                            'placeHolder'=>Yii::t('app', 'end time')
                        ]);
                ?>
            </div>

            <div class="col-md-2">
                <div class="input-group" style="border:1px solid #cbd5dd">
                    <?= Html::dropDownList('OnlineReportPoint[step]', isset($model->step) ? $model->step : '10', OnlineReportPoint::getAttributesList()['step'], ['class' => 'form-control', 'style'=>'border:0px;height:32px;']); ?>
                    <span class="input-group-addon" style="border:none;height:32px">
                        <?= Html::dropDownList('OnlineReportPoint[unit]', $model->unit, Setting::getAttributesList()['date']); ?>
                    </span>
                </div>
            </div>

            <?= Html::submitButton(Yii::t('app', 'search'), ['class' => 'btn btn-success']) ?>

            <div class="col-sm-12" style="text-align: left;color: #ffffff;">
                <?= $form->errorSummary($model); ?>
            </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

<!-- 为ECharts准备一个具备大小（宽高）的Dom -->
<div id="main" style="height:75%;padding:15px"></div>


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
            'echarts/chart/bar',
            'echarts/chart/line'
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
                title: {
                    text: <?= Yii::t('app', 'user online count') ?>,
                    subtext: <?= !empty($source['desc']) ? "'" . $source['desc'] . "'" : 0; ?>
                },
                tooltip: {
                    trigger: 'axis'
                },
                toolbox: {
                    show: true,
                    feature: {
                        mark: {show: false},
                        dataView: {show: false, readOnly: false},
                        magicType: {show: true, type: ['line', 'bar']},
                        restore: {show: true},
                        saveAsImage: {show: true}
                    }
                },
                calculable: true,
                xAxis: [
                    {
                        type: 'category',
                        boundaryGap: false,
                        data: [<?= !empty($source['xAxis']) ? $source['xAxis'] : '1'; ?>]
                    }
                ],
                yAxis: [
                    {
                        type: 'value'
                    }
                ],
                series: [
                    {
                        type: 'line',
                        smooth: true,
                        itemStyle: {
                            normal: {
                                areaStyle: {
                                    type: 'default'
                                },
                                label: {
                                    show: true, position: 'top',
                                    textStyle: {
                                        color: '#00a2ca'
                                    }
                                }
                            }
                        },
                        data: [<?= !empty($source['yAxis']) ? $source['yAxis'] : '0'; ?>],
                        markPoint: {
                            data: [
                                {type: 'max', name: <?= Yii::t('app', 'max') ?>},
                                {type: 'min', name: <?= Yii::t('app', 'min') ?>}
                            ]
                        },
                        markLine: {
                            data: [
                                {type: 'average', name: <?= Yii::t('app', 'average') ?>}
                            ]
                        }
                    }
                ]
            };

            // 为echarts对象加载数据
            myChart.setOption(option);
        }
    );
</script>