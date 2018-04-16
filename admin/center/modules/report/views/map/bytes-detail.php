<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2017/5/3
 * Time: 12:42
 */


//var_dump($top);exit;
?>

<!-- 为ECharts准备一个具备大小（宽高）的Dom -->
<div id="main" style="height:450px;"></div>


<!-- ECharts单文件引入 -->
<script type="text/javascript">
    // 基于准备好的dom，初始化echarts图表
    var myChart = echarts.init(document.getElementById('main'));
    option = {
        title: {
            text: '<?=$name?>',
            subtext: '<?= Yii::t('app', 'OnlineSummaryReport_model-unit').':'.$model->unit?>',
            x: 'left',
            subtextStyle: {
                top: 0,
                fontSize: 14
            }
        },
        tooltip: {
            trigger: 'axis',
            axisPointer: {            // 坐标轴指示器，坐标轴触发有效
                type: 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
            },
            formatter: function (params) {
                str = '';
                str += params[0].name + '<br/>';
                for (var param in params) {
                    var value = params[param].value;
                    if (params[param].seriesName == '<?= Yii::t('app', 'user_number')?>') {
                        str += params[param].seriesName + ':' + value + '<?= Yii::t('app', '个')?>' + "<br/>";
                    } else {
                        str += params[param].seriesName + ':' + value + '<?= $model->unit?>' + "<br/>";
                    }


                }
                return str;
            }
        },
        legend: {
            data:<?=isset($data['legends'])?$data['legends']:json_encode([])?>
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
        toolbox: {
            show: true,
            feature: {
                mark: {show: true},
                dataView: {show: true, readOnly: false},
                magicType: {show: true},
                restore: {show: true},
                saveAsImage: {show: true}
            }
        },
        calculable: true,
        <?php if (count(json_decode($data['xAxis'])) > 31): ?>
        dataZoom: [{
            type: 'inside',
            start: 0,
            end: 100
        }, {
            start: 0,
            end: 100
        }],
        <?php endif;?>
        grid: {
            top: '17%',
            left: '3%',
            right: '4%',
            bottom: '20%',
            containLabel: true
        },
        xAxis: [
            {
                type: 'category',
                axisLabel: {
                    <?php if (count(json_decode($data['xAxis'])) > 31): ?>
                    interval: 3,
                    <?php else: ?>
                    interval: 0,
                    <?php endif;?>

                    rotate: 45,
                    showMaxLabel: true,
                },

                data: <?=isset($data['xAxis'])?$data['xAxis']:json_encode([])?>
            }
        ],
        yAxis: [
            {
                type: 'value',
                splitArea: {show: true},
                axisLabel: {
                    formatter: function (v) {
                        return v + '<?= $model->unit?>';
                    }
                }
            }
        ],
        series: <?=isset($data['series'])?$data['series']:json_encode([])?>
    };
    // 为echarts对象加载数据
    myChart.setOption(option);
</script>
