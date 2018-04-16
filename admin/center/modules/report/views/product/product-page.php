<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use center\modules\report\models\OnlineReportProducts;
use center\modules\visitor\models\Setting;
use center\modules\report\models\OnlineReportPoint;

\center\assets\ReportAsset::echartsJs($this);
echo $this->render('/layouts/product-menu');

if(Yii::$app->session->get('searchProduct')) {
    $searchField = array_keys(Yii::$app->session->get('searchProduct'));
} else {
    $searchField = [];
}

$this->title = Yii::t('app', "report/product/online");
?>

<div class="panel panel-default">
    <div class="panel-body" style="padding: 10px">
        <?php
        $form = ActiveForm::begin([
            'layout' => 'horizontal',
            'fieldConfig' => [
                'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}"
            ],
        ]);
        ?>

        <div class="col-md-2">
            <?=
            $form->field($model, 'start_At', ['template' => '<div class="col-sm-12">{input}</div>'])
                ->textInput(
                    [
                        'value' => isset($model->start_At) ? $model->start_At : date('Y-m-d H:00'),
                        'class' => 'form-control inputDateHour',
                        'placeHolder' => Yii::t('app', 'start time')
                    ]);
            ?>
        </div>

        <div class="col-md-2">
            <?=
            $form->field($model, 'stop_At', [
                'template' => '<div class="col-sm-12">{input}</div>'
            ])->textInput(
                    [
                        'value' => isset($model->stop_At) ? $model->stop_At : date('Y-m-d H:00', strtotime('+1 hour')),
                        'class' => 'form-control inputDateHour',
                        'placeHolder' => Yii::t('app', 'end time')
                    ]);
            ?>
        </div>

		<div class="col-md-2">
                <div class="input-group" style="border:1px solid #cbd5dd">
                    <?= Html::dropDownList('OnlineReportProducts[step]', isset($model->step) ? $model->step : '15', OnlineReportProducts::getAttributesList()['step'], ['class' => 'form-control', 'style'=>'border:0px;height:32px;']); ?>
					<input type="hidden" name="OnlineReportProducts[unit]" value="minutes">
                    <span class="input-group-addon" style="border:none;height:32px"><?= Yii::t('app', 'minutes') ?></span>
                </div>			
        </div>
		
        <div class="col-md-12 form-group">
            <div class="col-md-2"><?= Yii::t('app', 'user products id select') ?>.</div>
            <div class="col-md-10">
                <?= Html::checkboxList('OnlineReportProducts[showField][]', $searchField, $showField, ['class' => 'drag_inline']) ?>
            </div>
        </div>

        <?= Html::submitButton(Yii::t('app', 'search'), ['class' => 'btn btn-success']) ?>

        <div class="col-sm-12" style="text-align: left;color: #ffffff;">
            <?= $form->errorSummary($model); ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

<div class="page">
    <section class="panel panel-default table-dynamic">
		<?php
		if(!empty($source)){
		?>
		<div style="float:right;margin-right:10px;margin-top:5px;">
				<button type="button" class="btn btn-default btn-sm">
                     <a href="product?action=excel"><span class="glyphicon glyphicon-log-out"></span>excel</a>
                </button>	
		</div>
		<?php
		}
		?>
		<div class="panel-heading">
			<strong>
                <span class="glyphicon glyphicon-th-large"></span> <?= $this->title; ?> 
			</strong>	
				<?= !empty($BeginDate)?'（':'' ?><?= $BeginDate;?><?= !empty($BeginDate)?'—':'' ?><?= $EndingDate?><?= !empty($BeginDate)?'）':'' ?>
            
        </div>
<?php

	// 重转数组
	if(!empty($source)){
	$c =array();
	foreach ($source as $key => $val) {
		if(in_array($val['product'], $c)){
 			foreach ($b as $i => $n) {
				$pid1 = $n[0]['product'];
				$pid2 = $val['product'];
				if($pid1 == $pid2){
					$b[$i][] = $val;
				}
			}
		}else{
 			$b[$key][] = $val;
			$c[] = $val['product']; 
		}		
	}
	$new_html = '<table class="table table-bordered table-striped table-responsive">';
	$new_html .= '<td width="15%">'.Yii::t('app', 'products name').'</td>';
	$new_html .= '<td width="15%">'.Yii::t('app', 'user time').'</td>';
	$new_html .= '<td width="12%">'.Yii::t('app', 'user online').'</td>';	
	$new_html .= '<tr>';
	foreach ($b as $k_1 => $v_1) {
		if(count($v_1) > 1){
			$rowspan = count($v_1);
			$new_html .= '<td rowspan='.$rowspan.'>'.$v_1[0]['product'].'</td>';
			foreach ($v_1 as $k_2 => $v_2) {
				$new_html .= '<td>'.$v_2['time'].'</td>';
				$new_html .= '<td>'.$v_2['countdata'].'</td>';
				$new_html .= '</tr><tr>';
			}
		}else{
			$background = ($v_1[0]['time'] == "/")?'style="background-color:#F6F6D7;"':'';
			$new_html .= '<td '.$background.'>'.$v_1[0]['product'].'</td>';
			$new_html .= '<td '.$background.'>'.$v_1[0]['time'].'</td>';
			$new_html .= '<td '.$background.'>'.$v_1[0]['countdata'].'</td>';
			$new_html .= '</tr><tr>';
		}
	}
	$new_html .= '</tr></table>';
	print_r($new_html);
	}else{
?>
<table class="table table-bordered table-striped table-responsive">
<tr><td><?= Yii::t('app', 'user base help10') ?></td></tr>
</table>

<?php	
	}
?>
    </section>
</div>		


<div id="main" style="height:80%;padding:15px"></div>
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
            var myChart = ec.init(document.getElementById('main'));
				
				var option = {
					title : {
						text: '<?= $this->title;?>',
						subtext: '<?= !empty($BeginDate) ? $BeginDate." — ".$EndingDate : ''; ?>'
					},
					tooltip : {
						trigger: 'axis'
					},
					legend: {
						data:[<?= !empty($tabletitle) ? $tabletitle : ''; ?>]
					},
					toolbox: {
						show : true,
						showTitle:false,
						feature : {
							mark : {show: false},
							dataView : {show: true, readOnly: false},
							magicType : {show: true, type: ['line', 'bar']},
							restore : {show: true},
							saveAsImage : {show: true}
						}
					},
					calculable : true,
					xAxis : [
						{
							type : 'category',
							data : [<?= !empty($xAxistime) ? $xAxistime : ''; ?>]
						}
					],
					yAxis : [
						{
							type : 'value'
						}
					],
					series : [<?= !empty($tableseries) ? $tableseries : ''; ?>]
				};

            // 为echarts对象加载数据
            myChart.setOption(option);
        }
    );
</script>