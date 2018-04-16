<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2016/7/27
 * Time: 10:15
 */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

\center\assets\ReportAsset::echartsJs($this);

$legend = "'".implode("','", $legend)."'";
//var_dump($serieses);exit;
?>
<div id="<?= $id; ?>" style="width:1000px;height:<?=$height?>px;"></div>


<script src="/lib/echarts/build/dist/echarts-all.js"></script>
<script src="/js/lib/jquery.js"></script>
<script type="text/javascript">
    // 路径配置
    require.config({
        paths: {
            echarts: '/lib/echarts/build/dist'
        }
    });

    // 使用
    require(
        [
            'echarts',
            'echarts/chart/pie',
            'echarts/chart/funnel',
        ],
        function (ec) {
            // 基于准备好的dom，初始化echarts图表
            var myChart = ec.init(document.getElementById("<?= $id;?>"), {
                noDataLoadingOption: {
                    text: "<?= Yii::t('app', 'user base help10') ?>",
                    effect: 'bubble',
                }
            });
            var labelTop = {
                normal : {
                    label : {
                        show : false,
                    },
                    labelLine : {
                        show : false
                    }
                }
            };
            var labelFromatter = {
                normal : {
                    label : {
                        formatter : function (params){
                            return params.value;
                        },
                        textStyle: {
                            baseline : 'top'
                        }
                    }
                },
            }
            var labelBottom = {
                normal : {
                    color: '#ccc',
                    label : {
                        show : true,
                        formatter : function (params){
                            return '空闲空间:'+params.value;
                        },
                        position : 'center'
                    },
                    labelLine : {
                        show : false
                    }
                },
                emphasis: {
                    color: 'rgba(0,0,0,0)'
                }
            };
            var radius = [40, 55];
            var option = {
                title: {
                    text: "<?= $title;?>",
                    x:'center'
                },
                toolbox: {
                    show : true,
                    feature : {
                        dataView : {show: true, readOnly: false},
                        magicType : {
                            show: true,
                            type: ['pie', 'funnel'],
                            option: {
                                funnel: {
                                    width: '20%',
                                    height: '30%',
                                    itemStyle : {
                                        normal : {
                                            label : {
                                                formatter : function (params){
                                                    return 'other\n' + params.value + '%\n'
                                                },
                                                textStyle: {
                                                    baseline : 'middle'
                                                }
                                            }
                                        },
                                    }
                                }
                            }
                        },
                        restore : {show: true},
                        saveAsImage : {show: true}
                    }
                },
                tooltip: {
                    trigger: 'item',
                    formatter: "{a} <br/>{b} : {c} ({d}%)"
                },
                legend: {
					orient : 'vertical',
                    x : 'left',
                    data:[<?=$legend?>]
                },

                series : [
                    <?php foreach ($serieses as $series):?>
                    {
                        type:'pie',
						center: ["<?=$series['center'][0]?>", "<?=$series['center'][1]?>"],
                        radius : radius,
                        x: "<?=$series['x']?>", // for funnel
                        itemStyle : labelFromatter,
                        data:[
						
                               {name:"<?=$series['data'][0]['name'];?>", value:<?=$series['data'][0]['value']?>, itemStyle : labelBottom},
                               {name:"<?=$series['data'][1]['name'];?>", value:<?=$series['data'][1]['value']?>,itemStyle : labelTop}
                        ]
                    },
                    <?php endforeach;?>
                ]
            };

            // 为echarts对象加载数据
            myChart.setOption(option);
        }
    );
</script>