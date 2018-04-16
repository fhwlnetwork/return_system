<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2017/5/17
 * Time: 15:43
 */

$count = count(json_decode($data['legends'], true));
$top = 100 + floor($count / 8) * 50;

//var_dump($data['series']);exit;
?>
<div id="main" style="height:500px;margin:0 auto;padding:0;width:95%;"></div>

<script>
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
                autoPlay: false,
                // currentIndex: 2,
                playInterval: 2000,
                controlStyle: {
                    position: 'left'
                },
                bottom: 50,
                data: <?=isset($data['xAxis'])?$data['xAxis']:json_encode([])?>,
            },
            title: {
                subtext: '<?= $this->title?>'
            },
            tooltip: {
                trigger: 'axis',
                axisPointer: {            // 坐标轴指示器，坐标轴触发有效
                    type: 'line'        // 默认为直线，可选为：'line' | 'shadow'
                },
                formatter: function (params) {
                    str = params[0].name + '<br/>';
                    for (var param in params) {
                        var value = params[param].value;
                        str += params[param].seriesName + ':' + value + '<?= Yii::t('app', 'currency')?>' + "<br/>";
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
                    'type': 'category',
                    data: <?=isset($data['legends'])?$data['legends']:json_encode([])?>,
                    splitLine: {show: false}
                }
            ],
            yAxis: [
                {
                    type: 'value',
                    splitArea: {show: true},
                    axisLabel: {
                        formatter: function (v) {
                            return v + '<?= Yii::t('app', 'currency')?>'
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
                    name: '<?= $pro?>',
                    type: 'bar',
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
                series: [
                    {data: <?= json_encode($v, JSON_UNESCAPED_UNICODE)?>},
                ]
            },
            <?php endforeach;?>

        ]

    };

    console.log(option);
    myChart.setOption(option);
</script>

