<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2018/3/24
 * Time: 8:16
 */;
 \center\assets\ReportAsset::newEchartsJs($this);
?>
<div id="main" class="col-md-11" style="height:600px;;padding:30px"></div>
<!-- ECharts单文件引入 -->
<script type="text/javascript">
    // 基于准备好的dom，初始化echarts图表
    var myChart = echarts.init(document.getElementById('main'));
    option = {
        title: {
            text: '<?=$data['title']?>',
            x: 'center',
            subtextStyle: {
                fontSize: 14
            }
        },
        tooltip: {
            trigger: 'item',
            formatter: "{a} <br/>{b} : {c} ({d}%)"
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
        legend: {
            x: 'left',
            orient: 'vertical',
            data: <?=isset($data['legend'])?json_encode($data['legend']):json_encode([])?>
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
        series : [
            {
                name: '就业率统计',
                type: 'pie',
                radius : '55%',
                center: ['50%', '60%'],
                data: <?= json_encode($data['seriesData'])?>,
                itemStyle: {
                    emphasis: {
                        shadowBlur: 10,
                        shadowOffsetX: 0,
                        shadowColor: 'rgba(0, 0, 0, 0.5)'
                    }
                }
            }
        ]
    };
    // 为echarts对象加载数据
    console.log(option);
    myChart.setOption(option);
</script>
