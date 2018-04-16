<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
\center\assets\ReportAsset::echartsJs($this);
?>
<div id="<?= $id;?>" style="width:100%;height:310px;margin-top:20px;"></div>

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

            var option = {
                title: {
                    text: "<?= $title;?>"
                },
                tooltip: {
                    trigger: 'axis'
                },
                calculable: true,
                grid : {'y':60},
                xAxis: [
                    {
                        type: 'category',
                        boundaryGap: false,
                        position:'left',
                        data: [<?= !empty($source['xAxis']) ? $source['xAxis'] : 0; ?>]
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
                        name:'<?= Yii::t('app', 'user online') ?>',
                        smooth: true,
                        itemStyle: {
                            normal: {
                                areaStyle: {
                                    type: 'default'
                                },
                                label : {
                                    show: false, position: 'top',
                                    textStyle: {
                                        color: '#00a2ca'
                                    }
                                }
                            }
                        },
                        symbol:'none',
                        data: [<?= !empty($source['yAxis']) ? $source['yAxis'] : 0; ?>],
                        markPoint: {
                            data: [
                                {type: 'max', name: '<?= Yii::t('app', 'max') ?>'},
                                {type : 'min', name: '<?= Yii::t('app', 'min') ?>'}
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