<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

\center\assets\ReportAsset::echartsJs($this);
echo $this->render('/layouts/financial-menu');
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
            <?= $form->field($model, 'operator', [
                'template' => '<div class="col-sm-12">{input}</div>'
            ])->textInput(
                    [
                        'value' => isset($params['operator'])?$params['operator'] : '',
                        'placeHolder'=>Yii::t('app', 'toll taker')
                    ]);
            ?>
        </div>

        <div class="col-md-2">
            <?= $form->field($model, 'start_time', [
                'template' => '<div class="col-sm-12">{input}</div>'
            ])->textInput(
                    [
                        'value' => isset($params['start_time'])?$params['start_time'] : '',
                        'class'=>'form-control inputDate',
                        'placeHolder'=>Yii::t('app', 'start time')
                    ]);
            ?>
        </div>

        <div class="col-md-2">
            <?= $form->field($model, 'end_time', [
                'template' => '<div class="col-sm-12">{input}</div>'
            ])->textInput(
                    [
                        'value' => isset($params['end_time'])?$params['end_time'] : '',
                        'class'=>'form-control inputDate',
                        'placeHolder'=>Yii::t('app', 'end time')
                    ]);
            ?>
        </div>
        <?= Html::submitButton(Yii::t('app', 'search'), ['class' => 'btn btn-success']) ?>

        <?php $form->end(); ?>

    </div>
</div>
<!-- 为ECharts准备一个具备大小（宽高）的Dom -->

<div id="main" style="height:60%;padding:15px"></div>
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
            'echarts/chart/line', // 使用柱状图就加载bar模块，按需加载
            'echarts/chart/bar' // 使用柱状图就加载bar模块，按需加载
        ],
        function (ec) {
            // 基于准备好的dom，初始化echarts图表
            var myChart = ec.init(document.getElementById('main'), 'macarons');

            option = {
                title : {
                    text: '<?=Yii::t("app","reprot by paytype")?>',
                    subtext: '<?=Yii::t("app","statistics by pay methods")?>'
                },
                tooltip : {
                    trigger: 'axis'
                },

                toolbox: {
                    show : true,
					showTitle:false,
                    feature : {
                        saveAsImage : {show: true}
                    }
                },
                calculable : true,
                xAxis : [
                    {
                        type : 'category',
                        data : [<?=$methods?>]
                    }
                ],
                yAxis : [
                    {
                        type : 'value',
                        axisLabel:{formatter:'{value} <?=Yii::t("app","currency")?>'}
                    }
                ],
                series : [
                    {
                        name:'<?= Yii::t('app', 'report financial font1') ?>',
                        type:'bar',
                        data:[<?=$money?>],
                        barWidth:'50',
                        itemStyle: {
                            normal: {
                                barBorderWidth: 0,
                                barBorderRadius:0,
                                barWidth:'10px',
                                barBorderRadius:[5,5,0,0],
                                color: function(params) {
                                    // build a color map as your need.
                                    var colorList = [
                                        '#C1232B','#B5C334','#FCCE10','#E87C25','#27727B',
                                        '#FE8463','#9BCA63','#FAD860','#F3A43B','#60C0DD',
                                        '#D7504B','#C6E579','#F4E001','#F0805A','#26C0C0'
                                    ];
                                    return colorList[params.dataIndex]
                                },
                                label : {
                                    show: true, position: 'top'
                                }
                            }
                        }
                    }
                ]
            };

            // 为echarts对象加载数据
            myChart.setOption(option);
        }
    );
</script>