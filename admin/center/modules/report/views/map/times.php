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
$height = 380 - $top;
//var_dump($top);exit;
?>

<!-- 为ECharts准备一个具备大小（宽高）的Dom -->
<div id="main" style="height:450px;margin:0 auto;padding:0;width:95%;"></div>


<!-- ECharts单文件引入 -->
<script type="text/javascript">
    // 基于准备好的dom，初始化echarts图表
    var myChart = echarts.init(document.getElementById('main'));
    option = {
        title: {
            text: '<?=$this->title?>',
            x: 'center',
            subtextStyle: {
                top: 0,
                fontSize: 14
            }
        },
        tooltip: {
            trigger: 'item',
            formatter: "{a} <br/>{b}: {c} ({d}%)"
        },
        legend: {
            orient: 'vertical',
            x: 'left',
            data:<?=isset($data['legends'])?$data['legends']:json_encode([])?>
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
        color: [
            '#C33531','#EFE42A','#64BD3D','#EE9201','#29AAE3',
            '#B74AE5','#0AAF9F','#E89589',
            '#6699FF','#ff6666','#3cb371','#b8860b','#30e0e0'
        ],

        series: [
            {
                name:'<?= $name?>',
                type:'pie',
                radius: ['50%', '70%'],
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
                labelLine: {
                    normal: {
                        show: false
                    }
                },
                data:  <?=isset($data['series'])?$data['series']:json_encode([])?>
            }
        ]
    };
    // 为echarts对象加载数据
    myChart.setOption(option);
</script>
