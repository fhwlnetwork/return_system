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
    $('#main').html('');
    var myChart = echarts.init(document.getElementById('main'));
    var sql_type = '<?= $model->sql_type?>';
    var preg = /byte/;
    var colors = ['#5793f3', '#d14a61', '#675bba'];
    option = {
        backgroundColor: 'white',
        color:  ['#ff7f50','#87cefa','#7b68ee','#00fa9a','#ffd700', '#3cb371','#b8860b','#30e0e0'],
        title: {},
        tooltip: {
            trigger: 'axis',
            axisPointer: {type: 'cross'}
        },
        grid: {
            bottom: '17%',
        },
        toolbox: {
            feature: {
                magicType: {show: true, type: ['line', 'bar']},
                dataView: {show: true, readOnly: false},
                restore: {show: true},
                saveAsImage: {show: true}
            }
        },
        legend: {
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
        xAxis: [
            {
                type: 'category',
                axisTick: {
                    alignWithLabel: true
                },
                data: ['1月','2月','3月','4月','5月','6月','7月','8月','9月','10月','11月','12月']
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
                    formatter: '{value} %'
                }
            },
            {
                type: 'value',
                position: 'right',
                axisLine: {
                    lineStyle: {
                        color: colors[1]
                    }
                },
                axisLabel: {
                    formatter: '{value}'
                }
            },
        ],
        series: []
    };

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
                        formatter: '{value} %'
                    }
                },
                {
                    type: 'value',
                    position: 'right',
                    axisLine: {
                        lineStyle: {
                            color: colors[1]
                        }
                    },
                    axisLabel: {
                        formatter: '{value}'
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
            series: <?=isset($data['base']['series'])? json_encode($data['base']['series']):json_encode([])?>,
        },
        options: <?=isset($data['options_data'])? json_encode($data['options_data']):json_encode([])?>,
    };
    console.log(option);
    myChart.setOption(option);
</script>