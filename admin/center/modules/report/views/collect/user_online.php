<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use center\modules\visitor\models\Setting;
use center\modules\report\models\OnlineReportPoint;
\center\assets\ReportAsset::echartsJs($this);
?>
<div class="panel panel-default">
	<div class="panel-body">
		<div style="height:40px;line-height:40px"><font class="collect_report_title"><?= Yii::t('app','user online count');?></font> </div>		
		<div id="user_online" style="height:330px;"></div>
	</div>
</div>

<!-- ECharts单文件引入 -->
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
            'echarts/chart/line'
        ],
        function (ec) {
            // 基于准备好的dom，初始化echarts图表
			var myChart = ec.init(document.getElementById('user_online')	,{
				  noDataLoadingOption:{
				  text :"<?= Yii::t('app', 'user base help10') ?>",
				  effect : 'bubble',
				}
			});	

            var option = {
	
                tooltip: {
                    trigger: 'axis'
                },
                calculable: true,
				grid : {'y':40},
                xAxis: [
                    {
                        type: 'category',
                        boundaryGap: false,
						position:'left',
                        data: [<?= !empty($onlineData['xAxis']) ? $onlineData['xAxis'] : 0; ?>]
                    }
                ],
                yAxis: [
                    {
                        type: 'value'
                    }
                ],
                series: [
                    {
                        type: 'line',
						name:'<?= Yii::t('app', 'user online') ?>',
                        smooth: true,
                        itemStyle: {
                            normal: {
                                areaStyle: {
                                    type: 'default'
                                },							
                                label : {
                                    show: false, position: 'top',
									textStyle: {
                                        color: '#00a2ca'
                                    }
                                }
                            }
                        },						
                        data: [<?= !empty($onlineData['yAxis']) ? $onlineData['yAxis'] : 0; ?>],
                        markPoint: {
                            data: [
                                {type: 'max', name: '<?= Yii::t('app', 'max') ?>'},
								{type : 'min', name: '<?= Yii::t('app', 'min') ?>'}
                            ]
                        },
                    }
                ]
            };

            // 为echarts对象加载数据
            myChart.setOption(option);
        }
    );
</script>