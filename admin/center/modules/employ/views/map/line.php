<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2018/3/24
 * Time: 8:56
 */

$legends = json_decode($data['legends']);
$count = count($legends);
$height = 450 + (ceil($count/8)) * 45;
//var_dump($top);exit;
//var_dump($data);exit;
\center\assets\ReportAsset::newEchartsJs($this);
?>

<!-- 为ECharts准备一个具备大小（宽高）的Dom -->
<div id="main" style="height:<?=$height?>px;margin:0 auto;padding:0;width:95%;"></div>


<!-- ECharts单文件引入 -->
<script type="text/javascript">
    // 基于准备好的dom，初始化echarts图表
    var myChart = echarts.init(document.getElementById('main'));
    option = {
        title: {
            text: '<?=$data['title']?>',
            x: 'left',
            subtextStyle: {
                top: 0,
                fontSize: 14
            }
        },
        tooltip: {
            trigger: 'axis',
        },
        legend: {
            top: '10%',
            data:<?=isset($data['legends'])?$data['legends']:json_encode([])?>
        },
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
        color: [
            '#3366CC',
            '#99CC33',
            '#009966',
            '#9933FF',
            '#FF6666',
            '#0066CC',
            '#336699',
            '#339966',
            '#FFCC33',
            '#FF99CC',
            '#CCFFCC',
            '#CCCC66',
            '#FFFF00',
            '#0099CC',
            '#99CC33',
            '#6666CC',
            '#00CC00',
            '#333366',
            '#CCCCFF',
            '#339966',
            '#FF6666',
            '#009966'
        ],
        calculable: true,
        dataZoom: [{
            type: 'inside',
            start: 0,
            end: 100
        }, {
            start: 0,
            end: 100
        }],
        grid: {
            top: '15%',
            bottom: '20%',
            containLabel: true
        },
        xAxis: [
            {
                axisLabel: {
                    <?php if (count(json_decode($data['xAxis'])) > 31): ?>
                    interval: 3,
                    <?php else: ?>
                    interval: 0,
                    <?php endif;?>

                    rotate: 45,
                    showMaxLabel: true,
                },
                type: 'category',
                boundaryGap: false,
                data: <?=isset($data['xAxis'])?$data['xAxis']:json_encode([])?>
            }
        ],
        yAxis: [
            {
                type: 'value',
                splitArea: {show: true},
            }
        ],
        series: <?=isset($data['series'])?$data['series']:json_encode([])?>
    };
    console.log(option);
    // 为echarts对象加载数据
    myChart.setOption(option);
</script>
