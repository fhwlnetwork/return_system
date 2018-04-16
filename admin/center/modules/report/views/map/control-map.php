<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2017/5/10
 * Time: 13:27
 */

$legends = json_decode($data['legends']);
$count = count($legends);
$top = ceil($count / 3) * 30;
$step = ceil($count / 10) * 30;
$height = 380 - $top;
$width = 450 + $step;
?>
<div id="main" style="height:<?= $width ?>px;margin:0 auto;padding:0;width:95%;"></div>

<script>
    var myChart = echarts.init(document.getElementById('main'));
    var sql_type = '<?= $model->sql_type?>';
    var preg = /byte/;
    option = {
        tooltip: {
            trigger: 'axis',
            position: function (pt) {
                return [pt[0], '10%'];
            },
            formatter: function (params) {
                str = params[0].name + '<br/>';
                for (var param in params) {
                    var value = params[param].value;
                    value = getBytesFormat(value);
                    str += params[param].seriesName + ':' + value + "<br/>";
                }
                return str;
            }

        },
        title: {
            left: 'center',
            text: '<?= $title?>',
            subtext: '<?=$type?>'
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
        grid: {
            top: 100,
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
        xAxis: {
            type: 'category',
            boundaryGap: false,
            data:<?=isset($data['xAxis'])?$data['xAxis']:json_encode([])?>,
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
        yAxis: {
            type: 'value',
            boundaryGap: [0, '100%'],
            splitArea: {show: true},
            axisLabel: {
                formatter: function (v) {
                    return number_format(v / (1024 * 1024 * 1024), 2) + 'Gb';
                }
            }
        },
        dataZoom: [{
            type: 'inside',
            start: 0,
            end: 100
        }, {
            start: 0,
            end: 100,
            handleIcon: 'M10.7,11.9v-1.3H9.3v1.3c-4.9,0.3-8.8,4.4-8.8,9.4c0,5,3.9,9.1,8.8,9.4v1.3h1.3v-1.3c4.9-0.3,8.8-4.4,8.8-9.4C19.5,16.3,15.6,12.2,10.7,11.9z M13.3,24.4H6.7V23h6.6V24.4z M13.3,19.6H6.7v-1.4h6.6V19.6z',
            handleSize: '80%',
            handleStyle: {
                color: '#fff',
                shadowBlur: 3,
                shadowColor: 'rgba(0, 0, 0, 0.6)',
                shadowOffsetX: 2,
                shadowOffsetY: 2
            }
        }],
        series: [
            {
                name: '<?= '总流量'?>',
                type: 'line',
                areaStyle: {normal: {}},
                markPoint: {
                    data: [
                        {type: 'max', name: '<?= Yii::t('app', 'max') ?>'},
                        {type: 'min', name: '<?= Yii::t('app', 'min') ?>'}
                    ]
                },
                data:<?=isset($data['series'])?$data['series']:json_encode([])?>,
            },
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
        if (str / (base * base * base) >= 1)
            return number_format(str / (base * base * base), 2) + "Gb";
        else if (str / (base * base) >= 1)
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
