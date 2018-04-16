<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use center\modules\report\models\SrunDetailDay;
use center\modules\visitor\models\Setting;

\center\assets\ReportAsset::echartsJs($this);



?>

<div class="panel panel-default">
	<div class="panel-body">
		<div style="height:40px;line-height:40px"><font class="collect_report_title"><?= Yii::t('app','report online bilingfont3');?></font> <span style="color:#333;font-size:12px;"></span></div>		
		<div id="bytedata" style="height:350px;"></div>
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
            'echarts/chart/pie', // 使用柱状图就加载bar模块，按需加载
            'echarts/chart/funnel' // 使用柱状图就加载bar模块，按需加载
        ],
        function (ec) {
            // 基于准备好的dom，初始化echarts图表
			var myChart = ec.init(document.getElementById('bytedata')	,{
				  noDataLoadingOption:{
				  text :"<?= Yii::t('app', 'user base help10') ?>",
				  effect : 'bubble',
				}
			});			
            var option = {
                title: {
                    text: '<?= Yii::t('app','report online bilingfont3');?>',
                    x: 'center'
                },
                tooltip: {
                    trigger: 'item',
                    formatter: "{a} <br/>{b} : {c} ({d}%)"
                },
                legend: {
                    orient: 'vertical',
                    x: 'left',
                    data: [<?= isset($source['legend']) ? $source['legend'] : '0'; ?>]
                },
                calculable: true,
                series: [
                    {
                        name: '<?= Yii::t('app', 'report online bilingfont3') ?>',
                        type: 'pie',
                        radius: '70%',
                        center: ['50%', '50%'],
                        data: [<?= $source['xAxis'];?>]
                    }
                ]
            };

            // 为echarts对象加载数据
            myChart.setOption(option);
        }
    );
</script>