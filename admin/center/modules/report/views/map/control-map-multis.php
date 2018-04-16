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
$width = 500 + $step;
//var_dump($data);exit;
//var_dump($top);exit;
//var_dump($data['xAxis']);exit;
?>

<!-- 为ECharts准备一个具备大小（宽高）的Dom -->
<div id="main" style="height:<?= $width ?>px;margin:0 auto;padding:0;width:95%;"></div>


<!-- ECharts单文件引入 -->
<script type="text/javascript">
    // 基于准备好的dom，初始化echarts图表
    var myChart = echarts.init(document.getElementById('main'));
    var sql_type = '<?= $model->sql_type?>';
    var preg = /byte/;
    option = {
        baseOption: {
            timeline: {
                // y: 0,
                axisType: 'category',
                // realtime: false,
                // loop: false,
                autoPlay: true,
                // currentIndex: 2,
                playInterval: 2000,
                controlStyle: {
                    position: 'left'
                },
                bottom: 50,
                data: <?=isset($data['base'])?$data['base']:json_encode([])?>,
            },
            title: {
                subtext: '<?= $title?>'
            },
            tooltip: {
                trigger: 'axis',
                formatter: function (params) {
                    str = params[0].name+'<br/>';
                    for (var param in params) {
                        var value = params[param].value;
                        value = getBytesFormat(value);
                        str += params[param].seriesName + ':' + value + "<br/>";
                    }
                    return str;
                }
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
            xAxis: [
                {
                    'type':'category',
                    'axisLabel':{'interval':3},
                    splitLine: {show: false}
                }
            ],
            yAxis: [
                {
                    type: 'value',
                    splitArea: {show: true},
                    axisLabel: {
                        formatter: function (v) {
                            return  number_format(v / (1024 * 1024 * 1024), 2) + 'Gb';
                        }
                    }
                }
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
                top: 100,
                bottom: 170
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
            series: [
                {
                    name: '<?= '流量'?>',
                    type: 'line',
                    smooth: true,
                    symbol: 'none',
                    sampling: 'average',
                    itemStyle: {
                        normal: {}
                    },
                    areaStyle: {
                        normal: {}
                    },
                    markPoint: {
                        data: [
                            {type: 'max', name: '<?= Yii::t('app', 'max') ?>'},
                            {type: 'min', name: '<?= Yii::t('app', 'min') ?>'}
                        ]
                    },
                },
            ]
        },
        options: [
            <?php foreach ($data['series'] as $key => $v): ?>
            {
                title: {text: '<?= $name?>: <?= $key?>'},
                xAxis: [
                    {data:<?= json_encode($v['xAxis'], JSON_UNESCAPED_UNICODE)?>},
                ],
                series: [
                    {data: <?= json_encode($v['yAxis'], JSON_UNESCAPED_UNICODE)?>},

                ]
            },
            <?php endforeach;?>

        ]

    };
    console.log(option);
    myChart.setOption(option);


    function getTimeFormat(str) {
        var base = 60;
        if (str / (base * base) >= 1)
            return number_format(str / (base * base), 2) + "Hour";
        else if (str / base >= 1)
            return number_format(str / (base), 2) + "Minute";
        else
            return str + "s";
    }
    function getBytesFormat(str) {
        var base = 1024;
        if (str / (base * base*base) >= 1)
            return number_format(str / (base * base *base), 2) + "Gb";
        else if  (str / (base * base) >= 1)
            return number_format(str / (base * base), 2) + "Mb";
        else if (str / base >= 1)
            return number_format(str / (base), 2) + "Kb";
        else
            return str + "b";
    }
    function number_format(str) {
        return str.toFixed(2);
    }
</script>
