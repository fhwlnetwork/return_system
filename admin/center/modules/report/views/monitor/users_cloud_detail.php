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
?>
<div id="<?= $id; ?>" style="width:1000px;height:250px;"></div>


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
            'echarts/chart/bar',
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

            var option = {
                title: {
                    text: "<?= $title;?>",
                    x:'center'
                },
                tooltip: {
                    trigger: 'item',
                    formatter: "{a} <br/>{b} : {c} ({d}%)"
                },
                legend: {
                    orient : 'vertical',
                    x : 'left',
                    data:["<?=Yii::t('app', 'error count1')?>", "<?=Yii::t('app', 'error count2')?>", "<?=Yii::t('app', 'error count3')?>","<?=Yii::t('app', 'error count4')?>"]
                },
                toolbox: {
                    show : true,
                    feature : {
                        mark : {show: true},
                        dataView : {show: true, readOnly: false},
                        magicType : {
                            show: true,
                            type: ['pie', 'funnel'],
                            option: {
                                funnel: {
                                    x: '25%',
                                    width: '50%',
                                    funnelAlign: 'left',
                                    max: 1548
                                }
                            }
                        },
                        restore : {show: true},
                        saveAsImage : {show: true}
                    }
                },
                calculable : true,
                series : [
                    {
                        name:'<?=Yii::t('app', 'error count')?>',
                        type:'pie',
                        radius : '55%',
                        center: ['50%', '60%'],
                        data:[
                            {value:<?=$source['error_count1']?>, name:"<?=Yii::t('app', 'error count1')?>"},
                            {value:<?=$source['error_count2']?>, name:"<?=Yii::t('app', 'error count2')?>"},
                            {value:<?=$source['error_count3']?>, name:"<?=Yii::t('app', 'error count3')?>"},
                            {value:<?=$source['error_count4']?>, name:"<?=Yii::t('app', 'error count4')?>"},
                        ]
                    }
                ]
            };

            // 为echarts对象加载数据
            myChart.setOption(option);
        }
    );
</script>