<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
\center\assets\ReportAsset::echartsJs($this);
?>
<div class="panel panel-default">
	<div class="panel-body">
		<div style="height:40px;line-height:40px"><font class="collect_report_title"><?= Yii::t('app','report/financial/product');?></font> <span style="color:#333;font-size:12px;"></span></div>		
		<div id="financial" style="height:350px;"></div>
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
            'echarts/chart/line', // 使用柱状图就加载bar模块，按需加载
            'echarts/chart/bar' // 使用柱状图就加载bar模块，按需加载
        ],
        function (ec) {
            // 基于准备好的dom，初始化echarts图表
            //var myChart = ec.init(document.getElementById('main'), 'macarons');
			var myChart = ec.init(document.getElementById('financial'),{
				  noDataLoadingOption:{
				  text :"<?= Yii::t('app', 'user base help10') ?>",
				  effect : 'bubble',
				}
			});			
            option = <?=$option?>

            // 为echarts对象加载数据
            myChart.setOption(option);
        }
    );
</script>