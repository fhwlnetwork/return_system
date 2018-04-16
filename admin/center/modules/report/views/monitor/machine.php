<?php

use yii\bootstrap\ActiveForm;
use center\widgets\Alert;

\center\assets\ReportAsset::echartsJs($this);


$this->title = Yii::t('app', 'report/monitor/machine-state');
?>
<?= Alert::widget() ?>
<?php if (!empty($productKeys)): ?>
    <?php

    $form = ActiveForm::begin([
        'layout' => 'horizontal',
        'fieldConfig' => [
            'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}",
            'horizontalCssClasses' => [],
        ],
    ]);
    ?>
    <div style="padding: 20px;font-family: inherit;">
        <div class="row">
            <div class="col-md-2">
                <?= $form->field($model, 'time_point', [
                    'template' => '<span class="col-sm-12" style="display: inline;">{input}</span>'
                ])->textInput(
                    [
                        'value' => date('Y-m-d H:i:s', time() - 300),
                        'class' => 'form-control inputDateTime',
                        'placeHolder' => Yii::t('app', 'start time')
                    ]);
                ?>
            </div>
            <div class="col-md-2">
                <?= $form->field($model, 'end_time', [
                    'template' => '<span class="col-sm-12" style="display: inline;">{input}</span>'
                ])->textInput(
                    [
                        'value' => date('Y-m-d H:i:s'),
                        'class' => 'form-control inputDateTime',
                        'placeHolder' => Yii::t('app', 'end time')
                    ]);
                ?>
            </div>
            <div class="col-md-1" style="margin: 0;padding: 0;width:150px;">
                <select name="products_key" id="products_key" style="width:150px;display: inline;" class="form-control">
                    <option value=""><?= Yii::t('app', 'Select User') ?></option>
                    <?php foreach ($productKeys as $productKey): ?>
                        <option value="<?= $productKey['products_key'] ?>"><?= $productKey['products_key'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <input type="button" id="btn" style="margin-left:20px;" class="btn btn-success"
                   value="<?= Yii::t('app', 'confirm') ?>" onclick="send()">
        </div>


    </div>
<?php endif; ?>


<!-- 为ECharts准备一个具备大小（宽高）的Dom -->
<div id="main"></div>
<!-- ECharts单文件引入 -->
<script src="/lib/echarts/build/dist/echarts-all.js"></script>
<script src="/js/lib/jquery.js"></script>
<script type="text/javascript">
    // 基于准备好的dom，初始化echarts图表
    var myChart;
    var option = {
        title: {
            text: '用户监控图表'
        },
        animation: false,
        tooltip: {
            trigger: 'axis',
            axisPointer: {            // 坐标轴指示器，坐标轴触发有效
                type: 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
            },
            formatter: function (params) {
                var str = params[0].name + '<br/>';
                for (var param in params) {
                    str += params[param].seriesName + ' : ' + params[param].value + 'ms' + '<br/>'
                }
                return str;
            }
        },
        legend: {
            data: [],
        },
        grid: {
            left: '3%',
            right: '4%',
            bottom: '3%',
            containLabel: true
        },
        toolbox: {
            show: true,
            feature: {
                mark: {show: true},
                dataView: {show: true, readOnly: false},
                restore: {show: true},
                saveAsImage: {show: true}
            }
        },
        calculable: true,

        xAxis: [
            {
                type: 'category'
            }
        ],
        yAxis: [
            {
                type: 'value',
                splitArea: {show: true}
            }
        ],
        series: [
            {
                name: "<?php echo Yii::t('app', 'startRes')?>",
                type: 'bar',
                itemStyle: {
                    normal: {
                        color: 'tomato',
                        barBorderColor: 'tomato',
                        barBorderWidth: 6,
                        barBorderRadius: 0,
                        label: {
                            show: true,
                        }
                    }
                },
            },
            {
                name: "<?php echo Yii::t('app', 'authRes')?>",
                type: 'bar',
                itemStyle: {
                    normal: {
                        barBorderColor: 'tomato',
                        barBorderWidth: 0,
                        barBorderRadius: 0,
                        label: {
                            show: true,
                        }
                    }
                },
            },
            {
                name: "<?php echo Yii::t('app', 'dmRes')?>",
                type: 'bar',
                itemStyle: {
                    normal: {
                        barBorderColor: 'tomato',
                        barBorderWidth: 0,
                        barBorderRadius: 0,
                        label: {
                            show: true,
                        }
                    }
                },
            },
            {
                name: "<?php echo Yii::t('app', 'coaRes')?>",
                type: 'bar',
                itemStyle: {
                    normal: {
                        barBorderColor: 'tomato',
                        barBorderWidth: 0,
                        barBorderRadius: 0,
                        label: {
                            show: true,
                        }
                    }
                },
            },
            {
                name: "<?php echo Yii::t('app', 'updateRes')?>",
                type: 'bar',
                itemStyle: {
                    normal: {
                        barBorderColor: 'tomato',
                        barBorderWidth: 0,
                        barBorderRadius: 0,
                        label: {
                            show: true,
                        }
                    }
                },
            },
            {
                name: "<?php echo Yii::t('app', 'stopRes')?>",
                type: 'bar',
                itemStyle: {
                    normal: {
                        barBorderColor: 'tomato',
                        barBorderWidth: 0,
                        barBorderRadius: 0,
                        label: {
                            show: true,
                        }
                    }
                },
            },

        ]
    };


    // 为echarts对象加载数据
    $(document).ready(function () {
        var heigth = $(document).height();
        $('#main').height(heigth - 500);
        myChart = echarts.init(document.getElementById('main'));
        //console.log(myChart);
    });
    function send() {
        myChart.clear();
        /*  option.legend.data = {};*/
        myChart.showLoading({
            text: "图表数据正在努力加载...",
            effect: 'whirling',//'spin' | 'bar' | 'ring' | 'whirling' | 'dynamicLine' | 'bubble'
            textStyle: {
                fontSize: 20
            }
        });
        var start_time = $.trim($('#cloundmonitor-time_point').val());
        var end_time = $.trim($('#cloundmonitor-end_time').val());
        var products_key = $('#products_key').val();
        var url = '';
        if (products_key) {
            url = '/report/monitor/machine-list?start_time=' + start_time + '&products_key=' + products_key + '&end_time=' + end_time;
        } else {
            alert("<?=Yii::t('app', 'user base help34')?>");
            return false;
        }
        $.ajax({
            url: url,
            type: 'get',
            async: false,
            success: function (result) {
                if (result.code == 200) {
                    option.xAxis[0].data = result.proc;
                    option.series[0].data = result.startRes;
                    option.series[1].data = result.authRes;
                    option.series[2].data = result.dmRes;
                    option.series[3].data = result.coaRes;
                    option.series[4].data = result.updateRes;
                    option.series[5].data = result.stopRes;
                    option.legend.data = result.legend;
                    myChart.setOption(option);
                    myChart.hideLoading();
                    window.onresize = myChart.resize;
                    //  'rad_auth', 'rad_dm ', 'srun_portal_server', 'third_auth', 'proxy_3p'

                } else {
                    alert(result.error);
                    myChart.setOption({"series": [{"name": "", "type": "pie"}]});
                }
            }
        })
    }
</script>
</body>