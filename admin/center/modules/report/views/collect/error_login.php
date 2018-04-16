
<div class="panel panel-default">
	<div class="panel-body">
		<div style="height:40px;line-height:40px"><font class="collect_report_title"><?= Yii::t('app','login log report');?></font> <span style="color:#333;font-size:12px;">(<?= Yii::t('app','unit error');?>)</span></div>
		<div id="error_login" style="height:300px;"></div>
	</div>
</div>

<?php
use yii\helpers\Html;
use center\assets\ReportAsset;
use yii\bootstrap\ActiveForm;
use center\modules\report\models\SrunDetailDay;

ReportAsset::echartsJs($this);

$this->title = Yii::t('app', 'report/error/login');
?>

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
			var myChart = ec.init(document.getElementById('error_login'),{
				  noDataLoadingOption:{
				  text :"<?= Yii::t('app', 'user base help10') ?>",
				  effect : 'bubble',
				}
			});	

            option = {
                tooltip : {
                    trigger: 'item',
                    formatter: "{a} <br/>{b} : {c} ({d}%)"
                },
                legend: {
                    orient : 'vertical',
                    x : 'left',
                    data:<?=$errorData['legend']?>
                },
                calculable : true,
                series : [
                    {
                        name: '<?=Yii::t('app','err msg')?>',
                        type:'pie',
                        radius : '80%',
                        center: ['65%', '45%'],
                        data:<?=$errorData['data']?>
                    }
                ]
            };
            // 为echarts对象加载数据
            myChart.setOption(option);
        }
    );
</script>