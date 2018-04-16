<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2017/6/12
 * Time: 14:41
 */


//var_dump(isset($data['series']));
?>

<div id="main" style="width:100%;height:500px;margin-top:20px;" class="page"></div>

<script>
    // 基于准备好的dom，初始化echarts图表
    var myChart = echarts.init(document.getElementById('main'));
    var sql_type = '<?= $model->sql_type?>';
    var preg = /byte/;
    var colors = ['#5793f3', '#d14a61', '#675bba'];
    option = {
        baseOption: {
            backgroundColor: 'white',
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
                data: <?=isset($data['base']['base'])?json_encode($data['base']['base']):json_encode([])?>,
            },
            title: {
                subtext: '<?= $this->title?>'
            },
            tooltip: {
                trigger: 'axis',
                formatter: function (params) {
                    str = params[0].name+'<br/>';
                    for (var param in params) {
                        var value = params[param].value;
                        value = value+'<?= $model->unit ?>';
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
                    data: <?=isset($data['base']['xAxis'])?json_encode($data['base']['xAxis']):json_encode([])?>,
                    splitLine: {show: false}
                }
            ],
            yAxis: [
                {
                    type: 'value',
                    position: 'left',
                    axisLine: {
                        lineStyle: {
                            color: colors[0]
                        }
                    },
                    axisLabel: {
                        formatter: '{value} ms'
                    }
                },
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
            series: <?=isset($data['base']['series'])? json_encode($data['base']['series']):json_encode([])?>,
        },
        options: <?=isset($data['options_data'])? json_encode($data['options_data']):json_encode([])?>,
    };
    console.log(option);
    myChart.setOption(option);
</script>