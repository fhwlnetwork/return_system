<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

\center\assets\ReportAsset::echartsJs($this);
?>

<div class="panel panel-default">
	<div class="panel-body">
		<div style="height:40px;line-height:40px"><font class="collect_report_title"><?= Yii::t('app','report/operate/userproduct');?></font> <span style="color:#333;font-size:12px;"></span></div>		
		<div id="userProduct" style="height:350px;"></div>
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
			var myChart = ec.init(document.getElementById('userProduct')	,{
				  noDataLoadingOption:{
				  text :"<?= Yii::t('app', 'user base help10') ?>",
				  effect : 'bubble',
				}
			});	

            var option = {
                title: {
                    text: '<?= $this->title;?>',
                    x: 'center'
                },
                tooltip: {
                    trigger: 'item',
                    formatter: "{a} <br/>{b} : {c} ({d}%)"
                },
                legend: {
                    orient: 'vertical',
                    x: 'left',
                    data: [<?= isset($source['xAxis']) ? $source['xAxis'] : '0'; ?>]
                },
                calculable: true,
                series: [
                    {
                        name: "<?= Yii::t('app', 'area condition help remind5') ?>",
                        type: 'pie',
                        radius: '65%',
                        center: ['50%', '50%'],
                        data: [
                            <?= isset($source['xAxis']) ? $source['xAxis'] : '0'; ?>]
                    }
                ]
            };

            // 为echarts对象加载数据
            myChart.setOption(option);
        }
    );
</script>