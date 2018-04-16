<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2016/9/9
 * Time: 15:55
 */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
\center\assets\ReportAsset::echartsJs($this);
?>
<div id="<?= $id;?>" style="width:1000px;height:250px;"></div>


<script src="/lib/echarts/build/dist/echarts-all.js"></script>
<script src="/js/lib/jquery.js"></script>
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
            var myChart = ec.init(document.getElementById("<?= $id;?>")	,{
                noDataLoadingOption:{
                    text :"<?= Yii::t('app', 'user base help10') ?>",
                    effect : 'bubble',
                }
            });
            var labelFormatter = {
                normal : {
                    label : {
                        formatter : function (params){
                            return params.value+'%';
                        },
                        textStyle: {
                            baseline : 'top'
                        }
                    }
                },
            }

            var option = {
                title: {
                    text: "<?= $title;?>"
                },
                animation: false,
                tooltip : {
                    trigger: 'axis',
                    axisPointer : {            // 坐标轴指示器，坐标轴触发有效
                        type : 'line'        // 默认为直线，可选为：'line' | 'shadow'
                    },
                    formatter: function (params){
                        var str = params[0].name + '<br/>';
                        for (var param in params) {
                            str += params[param].seriesName + ' : ' + parseFloat(params[param].value).toFixed(2)+ '%' + '<br/>'
                        }
                        return str;
                    }
                },
                legend: {
                    data:['<?= Yii::t('app', 'cpu max') ?>','<?= Yii::t('app', 'mem max') ?>','<?= Yii::t('app', 'mem-cached max') ?>']
                },
                toolbox: {
                    show : true,
                    feature : {
                        mark : {show: true},
                        dataView : {show: true, readOnly: false},
                        magicType : {show: true, type: ['line', 'bar']},
                        restore : {show: true},
                        saveAsImage : {show: true}
                    }
                },

                calculable: true,
                xAxis: [
                    {
                        type: 'category',
                        boundaryGap : false,
                        data: [<?= !empty($source['xAxis']) ? $source['xAxis'] : 0; ?>]
                    }
                ],
                yAxis : [
                    {
                        type : 'value',
                        axisLabel : {
                            formatter: '{value} %'
                        }
                    }
                ],
                series: [
                    {
                        type: 'line',
                        name:'<?= Yii::t('app', 'cpu max') ?>',
                        data: [<?= !empty($source['yAxis']) ? implode(',', $source['yAxis']['cpu']) : 0; ?>],
                        markPoint: {
                            data: [
                                {type: 'max', name: '<?= Yii::t('app', 'max') ?>',itemStyle : labelFormatter},
                                {type : 'min', name: '<?= Yii::t('app', 'min') ?>',itemStyle : labelFormatter}
                            ]
                        }
                    },
                    {
                        type: 'line',
                        name:'<?= Yii::t('app', 'mem max') ?>',
                        data: [<?= !empty($source['yAxis']) ? implode(',', $source['yAxis']['mem']) : 0; ?>],
                        markPoint: {
                            data: [
                                {type: 'max', name: '<?= Yii::t('app', 'max') ?>',itemStyle : labelFormatter},
                                {type : 'min', name: '<?= Yii::t('app', 'min') ?>',itemStyle : labelFormatter}
                            ]
                        },
                    },
                    {
                        type: 'line',
                        name:'<?= Yii::t('app', 'mem-cached max') ?>',
                        data: [<?= !empty($source['yAxis']) ? implode(',', $source['yAxis']['mem_cached']) : 0; ?>],
                        markPoint: {
                            data: [
                                {type: 'max', name: '<?= Yii::t('app', 'max') ?>',itemStyle : labelFormatter},
                                {type : 'min', name: '<?= Yii::t('app', 'min') ?>', itemStyle : labelFormatter}
                            ]
                        },
                    }

                ]
            };


            // 为echarts对象加载数据
            myChart.setOption(option);
        }
    );
</script>