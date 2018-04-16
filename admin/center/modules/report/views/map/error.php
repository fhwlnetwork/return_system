<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2017/5/8
 * Time: 9:59
 */
//var_dump($data, json_encode($data, JSON_UNESCAPED_UNICODE));
//exit;
?>
<div id="main" style="height:680px;margin:0 auto;padding:0;width:95%;"></div>

<script>
    var json = eval(<?= '('.json_encode($data, JSON_UNESCAPED_UNICODE).')'?>);
    var myChart = echarts.init(document.getElementById('main'));
    var timeJson = json.timeJson;
    var userJson = json.userJson;
    var errJson = json.errJson;

    option = {
        tooltip: {

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
        title: [{
            text: '<?= $name?>',
            x: '25%',
            textAlign: 'center'
        }, {
            text: '<?= Yii::t('app', 'Authentication error statistics')?>',
            x: '75%',
            textAlign: 'center'
        }, {
            text: '<?= Yii::t('app', 'User authentication error statistics').' Top'?>',
            x: '75%',
            y: '50%',
            textAlign: 'center'
        }],
        grid: [{
            top: 50,
            width: '50%',
            bottom: '60%',
            left: 10,
            containLabel: true
        }, {
            top: '55%',
            width: '50%',
            bottom: '15%',
            left: 10,
            containLabel: true
        }],
        xAxis: [{
            type: 'category',
            data: Object.keys(timeJson),
            axisLabel: {
                interval: 3,
                rotate: 45
            },
            splitLine: {
                show: false
            }
        }, {
            gridIndex: 1,
            bottom: 500,
            type: 'category',
            data: Object.keys(errJson),
            axisLabel: {
                interval: 0,
                rotate: 30
            },
            splitLine: {
                show: false
            }
        }
        ],
        yAxis: [{
            type: 'value',
            splitLine: {
                show: false
            }
        },
            {
                type: 'value',
                gridIndex: 1,
                splitLine: {
                    show: false
                }
            }],
        series: [
            {
                type: 'bar',
                stack: 'chart',
                z: 3,
                label: {
                    normal: {
                        position: 'top',
                        <?php if (count($data['timeJson']) < 25): ?>
                        show: true,
                        <?php endif;?>

                    }
                },
                data: Object.keys(timeJson).map(function (key) {
                    return timeJson[key];
                })
            },
            {
                type: 'bar',
                stack: 'component',
                xAxisIndex: 1,
                yAxisIndex: 1,
                z: 3,
                label: {
                    normal: {
                        position: 'top',
                        show: true
                    }
                },
                data: Object.keys(errJson).map(function (key) {
                    return errJson[key];
                })
            },
            {
                type: 'pie',
                radius: ['20%', '40%'],
                center: ['75%', '25%'],
                label: {
                    normal: {
                        show: false,
                        position: 'center',
                        formatter: '{b} : {c} ({d}%)'
                    },
                    emphasis: {
                        show: true,
                        textStyle: {
                            fontSize: '18',
                            fontWeight: 'bold'
                        }
                    }
                },
                data: Object.keys(errJson).map(function (key) {
                    return {
                        name: key,
                        value: errJson[key]
                    }
                })
            },
            {
                type: 'pie',
                radius: ['20%', '40%'],
                center: ['75%', '75%'],
                label: {
                    normal: {
                        show: false,
                        position: 'center',
                        formatter: '{b} : {c} ({d}%)'
                    },
                    emphasis: {
                        show: true,
                        textStyle: {
                            fontSize: '18',
                            fontWeight: 'bold'
                        }
                    }
                },
                data: Object.keys(userJson).map(function (key) {
                    return {
                        name: key,
                        value: userJson[key]
                    }
                })
            }]
    }
    myChart.setOption(option);
</script>
