<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2017/6/14
 * Time: 14:32
 */

\center\assets\ReportAsset::newEchartsJs($this);

$this->registerJsFile('/js/app-monitor.js', ['depends' => [center\assets\ZTreeAsset::className()]]);
?>

<div style="border-radius:5px;border:solid 1px #ccc;" id="special">
    <div class="panel panel-default" data-ng-controller="sourceCtrl">
        <div class="row" id="tab">
            <?php $i = 0;
            foreach ($attributes as $key => $val): ?>
                <div class="col-lg-<?= $num ?> com-md-<?= $num ?> col-sm-6 col-xs-8"
                     data-ng-click="getSource('<?= $ip ?>','<?= $key?>')"
                     onclick="getSource('<?= $ip ?>','<?= $key?>')"
                    >
                    <div class="mini-box" style="margin:0 auto;">
                        <h5 style="text-align:center;"><?= $val ?></h5>

                        <div style="width:150px;display:inline-block;text-align:center;">
                            <div class="system_sta"
                                 style="margin:0 10px;display:inline-block;cursor:pointer;position:relative;">
                                     <span class="box-icon  <?= $rows[$key]['color'] ?> fa-shadow" style="margin:0px;">
                                         <i class="<?= $rows[$key]['icon']; ?>"></i>
                                     </span>
                            </div>
                            <div class="s_status">
                                <?= $rows[$key]['text'] ?><br/>
                                <?= !empty($status['error']) ? Yii::t('app', $status['error']) : '　'; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php $i++;endforeach; ?>
        </div>
    </div>
    <div class="page page-dashboard">
        <div id="main" style="height:400px;">
            <?php if ($source['code'] == 200): ?>
                <script>
                    var myChart = echarts.init(document.getElementById('main'));
                    var option;
                    <?php if ($source['type'] == 'pie'): ?>
                    <?php if (isset($source['single']) && !$source['single']): ?>
                    //饼图
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
                                bottom: 10,
                                data: <?=isset($source['base'])?json_encode($source['base']):json_encode([])?>,
                            },
                            title: {
                                text: '<?= $source['title']?>'
                            },
                            color: ['#ff7f50', '#87cefa', '#7b68ee', '#00fa9a', '#ffd700', '#3cb371', '#b8860b', '#30e0e0'],
                            tooltip: {
                                trigger: 'item',
                                formatter: "{a} <br/>{b}: {c} ({d}%)"
                            },
                            legend: {
                                data:<?=isset($source['legends'])?json_encode($source['legends']):json_encode([])?>,
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
                                top: 80,
                                bottom: 80,
                            },
                            series: [
                                {
                                    name: '<?= Yii::t('app', 'disk free total')?>',
                                    type: 'pie',
                                    radius: '55%',
                                    center: ['50%', '60%'],
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
                                    itemStyle: {
                                        emphasis: {
                                            shadowBlur: 10,
                                            shadowOffsetX: 0,
                                            shadowColor: 'rgba(0, 0, 0, 0.5)'
                                        }
                                    }
                                },
                                {
                                    name: '<?= Yii::t('app', 'other')?>',
                                    type: 'pie',
                                    radius: '55%',
                                    center: ['50%', '60%'],
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
                                    itemStyle: {
                                        emphasis: {
                                            shadowBlur: 10,
                                            shadowOffsetX: 0,
                                            shadowColor: 'rgba(0, 0, 0, 0.5)'
                                        }
                                    }
                                },
                            ]

                        },
                        options: <?=isset($source['series']['option'])?json_encode($source['series']['option']):json_encode([])?>,

                    };
                    console.log(option);
                    <?php else :?>
                    //一个分区
                    option = {
                        title: {
                            text: '<?= $source['title']?>'
                        },
                        tooltip: {
                            trigger: 'item',
                            formatter: "{a} <br/>{b} : {c} ({d}%)"
                        },
                        legend: {
                            orient: 'vertical',
                            left: 'left',
                            data:<?=isset($source['legends'])?json_encode($source['legends']):json_encode([])?>,
                        },
                        color: ['#ff7f50', '#87cefa', '#7b68ee', '#00fa9a', '#ffd700', '#3cb371', '#b8860b', '#30e0e0'],
                        series: <?=isset($source['series'])?json_encode($source['series']):json_encode([])?>
                    };
                    <?php endif;?>
                    <?php else: ?>
                    <?php if (isset($source['single']) && !$source['single']): ?>
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
                                bottom: 10,
                                data: <?=isset($source['base'])?json_encode($source['base']):json_encode([])?>,
                            },
                            title: {
                                text: '<?= $source['title']?>'
                            },

                            color: ['#ff7f50', '#87cefa', '#7b68ee', '#00fa9a', '#ffd700', '#3cb371', '#b8860b', '#30e0e0'],
                            tooltip: {
                                trigger: 'axis',
                                axisPointer: {
                                    type: 'line',
                                    label: {
                                        backgroundColor: '#6a7985'
                                    }
                                }
                            },
                            legend: {
                                data:<?=isset($source['legends'])?json_encode($source['legends']):json_encode([])?>,
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
                                top: 80,
                                bottom: 80,
                            },
                            xAxis: [
                                {
                                    type: 'category',
                                    boundaryGap: false,
                                    data:<?=isset($source['xAxis'])?json_encode($source['xAxis']):json_encode([])?>,
                                }
                            ],
                            yAxis: [
                                {
                                    type: 'value'
                                }
                            ],
                            series: <?=isset($source['series']['base'])?json_encode($source['series']['base']):json_encode([])?>,

                        },
                        options: <?=isset($source['series']['option'])?json_encode($source['series']['option']):json_encode([])?>,

                    };
                    <?php else :?>
                    option = {
                        tooltip: {
                            trigger: 'axis',
                            axisPointer: {
                                type: 'cross',
                                label: {
                                    backgroundColor: '#6a7985'
                                }
                            }
                        },
                        color: ['#ff7f50', '#87cefa', '#7b68ee', '#00fa9a', '#ffd700', '#3cb371', '#b8860b', '#30e0e0'],
                        legend: {
                            data:<?=isset($source['legends'])?json_encode($source['legends']):json_encode([])?>,
                        },
                        toolbox: {
                            feature: {
                                saveAsImage: {}
                            }
                        },
                        grid: {
                            left: '3%',
                            right: '4%',
                            bottom: '3%',
                            containLabel: true
                        },
                        xAxis: [
                            {
                                type: 'category',
                                boundaryGap: false,
                                data:<?=isset($source['xAxis'])?json_encode($source['xAxis']):json_encode([])?>,
                            }
                        ],
                        yAxis: [
                            {
                                type: 'value'
                            }
                        ],
                        series: <?=isset($source['series'])?json_encode($source['series']):json_encode([])?>,
                    };
                    <?php endif;?>
                    console.log(option);
                    <?php endif;?>
                    myChart.setOption(option);
                </script>
            <?php else : ?>
                <?= $source['msg'] ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<script>
    $(document).ready(function(){
        $('#chart_status').html('<?= Yii::t('app', 'monitor help2')?>');
    })
</script>
