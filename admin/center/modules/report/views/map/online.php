<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2017/5/3
 * Time: 12:42
 */

$legends = json_decode($data['legends']);
$count = count($legends);
$top = ceil($count / 3) * 30;
$step = ceil($count / 10) * 30;
$height = 380 - $top;
$width = 450 + $step;
//var_dump($top);exit;
//var_dump($data['xAxis']);exit;
?>

<!-- 为ECharts准备一个具备大小（宽高）的Dom -->
<div id="main" style="height:450px;margin:0 auto;padding:0;width:95%;"></div>


<!-- ECharts单文件引入 -->
<script type="text/javascript">
    // 基于准备好的dom，初始化echarts图表
    var myChart = echarts.init(document.getElementById('main'));
    option = {
        baseOption: {
            timeline: {
                // y: 0,
                axisType: 'category',
                // realtime: false,
                // loop: false,
                autoPlay: true,
                // currentIndex: 2,
                playInterval: 1000,
                // controlStyle: {
                //     position: 'left'
                // },
                data: <?=isset($data['xAxis'])?$data['xAxis']:json_encode([])?>,
            },
            title: {
                subtext: '在线人数统计'
            },
            tooltip: {
                trigger: 'axis'
            },
            legend: {
                data: ['<?= Yii::t('app', 'user online')?>'],
            },
            calculable: true,
            grid: {
                top: 80,
                bottom: 100
            },
            xAxis: [
                {
                    'type': 'category',
                    splitLine: {show: false}
                }
            ],
            toolbox: {
                show: true,
                feature: {
                    mark: {show: true},
                    dataView: {show: true, readOnly: false},
                    magicType: {show: true, type: ['line', 'bar']},
                    restore: {show: true},
                    saveAsImage: {show: true}
                }
            },
            yAxis: [
                {
                    type: 'value',
                }
            ],
            series: [
                {
                    name: '<?= Yii::t('app', 'user online')?>',
                    type: 'line',
                    areaStyle: {normal: {}},
                    markPoint: {
                        data: [
                            {type: 'max', name: '<?= Yii::t('app', 'max') ?>'},
                            {type: 'min', name: '<?= Yii::t('app', 'min') ?>'}
                        ]
                    },
                },
            ],
            color: ['#ff6666']
        },
        options: [
            <?php foreach ($data['series'] as $key => $v):?>
            {
                title: {text: '<?= $key?>'},
                xAxis : {
                    data: <?= json_encode($v['xAxis'], JSON_UNESCAPED_UNICODE)?>
                },
                series: [
                    {
                        data: <?= json_encode($v['data'], JSON_UNESCAPED_UNICODE)?>
                    }
                ]
            },
            <?php endforeach;?>
        ]
    };
    myChart.setOption(option);
</script>
