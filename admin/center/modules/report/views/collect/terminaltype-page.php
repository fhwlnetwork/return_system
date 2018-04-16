<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use center\modules\visitor\models\Setting;
use center\modules\report\models\OnlineReportPoint;

\center\assets\ReportAsset::echartsJs($this);
?>



<div class="panel panel-default">
	<div class="panel-body">
		<div style="height:40px;line-height:40px"><font class="collect_report_title"><?= Yii::t('app','online operating system');?></font> <span style="color:#333;font-size:12px;"></span></div>		
		<div id="terminalType" style="height:330px;"></div>
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
            'echarts/chart/pie',
			'echarts/chart/funnel'			
        ],
        function (ec) {
            // 基于准备好的dom，初始化echarts图表
			var myChart = ec.init(document.getElementById('terminalType')	,{
				  noDataLoadingOption:{
				  text :"<?= Yii::t('app', 'user base help10') ?>",
				  effect : 'bubble',
				}
			});	
			
			var option = {
				timeline : {
				type :'number',
				checkpointStyle :{
						label: {
							show: true,
							textStyle: {
								color: 'auto'
							}
						}
					}  ,
				data : [<?= isset($source['xAxis']) ? $source['xAxis'] : '0'; ?>],
					label : {
						formatter : function(s) {
							return s.slice(0,5);
						}
					}
				},
				options : [
					{
						tooltip : {
							trigger: 'item',
							formatter: "{a} <br/>{b} : {c} ({d}%)"
						},
						legend: {
						    orient : 'vertical',
							x : 'left',
							data:[<?= isset($source['legend']) ? $source['legend'] : '0'; ?>]
						},
						calculable : true,
						series : [<?= isset($source['default']) ? $source['default'] : '0'; ?>]
					},
					<?= isset($source['yAxis']) ? $source['yAxis'] : '0'; ?>
					
				]
			};			
			// option end

            // 为echarts对象加载数据
            myChart.setOption(option);
        }
    );
</script>