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
<div id="main" style="height:<?= $width ?>px;margin:0 auto;padding:0;width:95%;"></div>


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
                autoPlay: false,
                // currentIndex: 2,
                playInterval: 1000,
                controlStyle: {
                    position: 'left'
                },
                data: <?=isset($data['xAxis'])?$data['xAxis']:json_encode([])?>,
            },
            title: {
                subtext: '<?= $name?>'
            },
            tooltip: {
                trigger: 'item',
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
            legend: {
                x: 'right',
                top: 70,
                <?php if(count(json_decode($data['legends'],true)) > 10):?>
                x: 'left',
                orient: 'vertical',
                <?php endif;?>

                data:<?=isset($data['legends'])?$data['legends']:json_encode([])?>,
            },
            calculable: true,
            grid: {
                top: 80,
                bottom: 200
            },
            series: [
                {
                    name: '<?= $name?>',
                    type: 'pie',
                    radius : '55%',
                    center: ['50%', '60%'],
                    avoidLabelOverlap: false,
                    label: {
                        normal: {
                            show: false,
                            position: 'center'
                        },
                        emphasis: {
                            show: true,
                            textStyle: {
                                fontSize: '30',
                                fontWeight: 'bold'
                            }
                        }
                    },
                    itemStyle: {
                        emphasis: {
                            shadowBlur: 10,
                            shadowOffsetX: 0,
                            shadowColor: 'rgba(0, 0, 0, 0.5)'
                        }
                    }
                }
            ]
        },
        options: [
            <?php foreach ($data['series'] as $key => $v): ?>
            {
                title: {text: '<?= $type?>: <?= $key?>'},
                series: [{data: <?= json_encode($v, JSON_UNESCAPED_UNICODE)?>}]
            },
            <?php endforeach;?>
        ]

    };
     console.log(option);
    myChart.setOption(option);
</script>
