<?php 
\center\assets\ReportAsset::echartsJs($this);
use center\modules\report\models\DashboardReports;
use center\modules\report\models\Efficiency;
 ?>
<?php
$report = new DashboardReports();	
$Efficiency = new Efficiency();
switch($key){
	case 'httpd_process_data':
	$source = $report->SystemStatus('proccess',$ip,true);	
	$chart = 'AreaChart';	
	break;
	case 'system_load':
	$source = $report->SystemStatus('loads',$ip,true);	
	$chart = 'AreaChart';
	break; 
	case 'portal_server':
	$source = $Efficiency->DeviceData($ip);	
	$source = $source['maxData'];
	$source = !empty($source['auth_response_time']) ? $source['auth_response_time'] :0;
	$message = Yii::t('app', 'auth');
	$chart = 'Dashboard';	
	break;
	case 'disk_io_status':
	$source = $report->DiskIoCounters($ip);	
	$chart = '';	
	break;	
	case 'system_status':
	$source = $report->SystemStatus('mem',$ip,true);	
	$chart = 'AreaChart';
	break; 	
	case 'radisud':
	$source = $Efficiency->DeviceData($ip);
	$source = $source['maxData'];
	$source = $source['radiusd_auth'];
	$message = 'Radiusd';	
	$chart = 'Dashboard';	
	break;
	case 'mysqld':
	$source = $report->ProcessIoCounters('mysqld',$ip);	
	$source = $source ['proArray'];
	$chart = 'AreaChart';
	break;
	case 'system_data':
	//$source = $report->SystemStatus('mem',$ip,true);
	//$chart = '';
	$source = $report->SystemStatus('cpu',$ip,true);
	$chart = 'AreaChart';
	break;	
	case 'data_acquisition':
	$source = $Efficiency->DeviceData($ip);	
	$source = $source['maxData'];
	$chart = 'Dashboard2';	
	break;		
	case 'hard_disk_data':
	$source = $report->PartitionStatus($ip);
	$chart = 'circular';
	break;	
	case 'redis_status':
	$source = $report->ProcessIoCounters('redis_server',$ip);	
	$source = $source ['sendArray'];
	$chart = 'AreaChart';	
	break;	
	default:
	$source = array();
}
?>
<?php
if($key == 'disk_io_status'){
?>	
                <div class="tab-content">
                    <div class="row">
                        <div class="col-md-12">						
							<!---------------------->
							<?php
							if(!empty($source)){
							?>
							<ul class="nav nav-tabs" id="myTab"> 
							<?php
							$i = 1;
							foreach($source as $key=>$value){
								$class = ($i == 1)?"active":'';
							?>
							<li class="<?= $class;?>"><a href="#<?= $key;?><?= $id;?>"><?= $key;?></a></li> 
							<?php
							$i++;							
							}
							?>
							</ul> 
							   
							<div class="tab-content"> 
							<?php
							$i = 1;
							foreach($source as $key=>$value){
								$class = ($i == 1)?"active":'';
							?>							
							  <div class="tab-pane <?= $class;?>" id="<?= $key;?><?= $id;?>">
							  		<div class="col-lg-6 col-xsm-6">		
										<?= $this->render('disk_io_counters',['source'=>$value,'id'=>$id.$i])?>
									</div>
							  </div>
							<?php
							$i++;							
							}
							?>							  
							</div> 							   
							<script> 
							  $(function () { 
								$('#myTab a:last').tab('show');
								$('#myTab a').click(function (e) { 
								  e.preventDefault();
								  $(this).tab('show');
								}) 
							  }) 
							</script>	
							<?php
							}else{
							echo Yii::t('app', 'user base help10');
							}
							?>
							<!---------------------->
                        </div>
                    </div>
                </div>					
<?php
}elseif($key == 'data_acquisition'){
?>
<div class="col-lg-4 col-xsm-6" style="text-align:center;">
	<?= Yii::t('app', 'online');?><div id="start<?= $id;?>" style="width:200px;height:200px;"></div>
</div>		

<div class="col-lg-4 col-xsm-6" style="text-align:center;">
	<?= Yii::t('app', 'collect');?><div id="update<?= $id;?>" style="width:200px;height:200px;"></div>
</div>		

<div class="col-lg-4 col-xsm-6" style="text-align:center;">
	<?= Yii::t('app', 'offline');?><div id="stop<?= $id;?>" style="width:200px;height:200px;"></div>
</div>
<?php
}else if ($key == 'hard_disk_data'){
if(!empty($source)){
?>
							<ul class="nav nav-tabs" id="myTab3"> 
							<?php
							$i = 10;
							foreach($source as $key=>$value){
								$class = ($i == 10)?"active":'';
							?>
							<li class="<?= $class;?>"><a href="#<?= $key;?><?= $id;?>"><?= $key;?></a></li> 
							<?php
							$i++;							
							}
							?>
							</ul> 							   
							<div class="tab-content"> 
							<?php
							$i = 10;
							foreach($source as $key=>$value){
								$class = ($i == 10)?"active":'';
							?>							
							  <div class="tab-pane <?= $class;?>" id="<?= $key;?><?= $id;?>">
							  		<div class="col-lg-6 col-xsm-6">		
										<?= $this->render('partitions_status',['source'=>$value,'id'=>$id.$i])?>
									</div>
							  </div>
							<?php
							$i++;							
							}
							?>							  
							</div> 							   
<script> 
	$(function () { 
		$('#myTab3 a:last').tab('show');
		$('#myTab3 a').click(function (e) { 
			e.preventDefault();
				$(this).tab('show');
		}) 
	}) 
</script>
<?php
}else{
   echo Yii::t('app', 'user base help10');
}
?>
<?php
}else{
?>
<div id="main<?= !empty($id) ? $id : ''; ?>" style="width:620px;height:270px;text-align:center;"></div>
<?php
}
?>
<script type="text/javascript">
    require.config({
        paths: {
            echarts: '/lib/echarts/build/dist'
        }
    });
    require(
        [
            'echarts',
            'echarts/chart/line',
            'echarts/chart/bar',
			'echarts/chart/gauge'
        ],
        function (ec) {		
			
				<?php if($chart == 'AreaChart'){?>
					var divObj = document.getElementById("main" + <?= $id;?>+"");
					AreaChart(divObj);		
				<?php }else if($chart == 'Dashboard'){?>
					var divObj = document.getElementById("main" + <?= $id;?>+"");
					Dashboard(divObj,"<?= $source;?>","<?= $message;?>");			
				<?php }else if($chart == 'Dashboard2'){
				?>
					var start = document.getElementById("start" + <?= $id;?>+"");
					Dashboard(start,"<?= !empty($source['start_response_time'])?$source['start_response_time']:0;?>",'');				
					var update = document.getElementById("update" + <?= $id;?>+"");
					Dashboard(update,"<?= !empty($source['update_response_time'])?$source['update_response_time']:0;?>",'');				
					var stop = document.getElementById("stop" + <?= $id;?>+"");
					Dashboard(stop,"<?= !empty($source['stop_response_time'])?$source['stop_response_time']:0;?>",'');					
				<?php
				} ?>
			//面积图
			function AreaChart(divObj){
				var obj = {};	
				
				var myChart = ec.init(divObj,{
					  noDataLoadingOption:{
					  text :"<?= Yii::t('app', 'user base help10') ?>",
					  effect : 'bubble',
					}
				});	
				var option = {
					tooltip: {trigger: 'axis',axisPointer: {type: 'shadow'},},
					legend: {
						x: 'center',
						data:[<?= !empty($source['legend']) ? $source['legend'] :0; ?>]
					},
					calculable : true,
					tooltip : {
						trigger: 'axis',
						formatter: function(params) {
							var dataAry = params[0].name+"：<br/>";
							for(var i=0;i < params.length;i++){
								var string = '';
								string = params[i].seriesName + ' : ' + params[i].value + "<br/>";
								dataAry += string;
							}
							return dataAry;
						}					
					},					
					xAxis: [{
							type: 'category',
							boundaryGap : false,
							data: [<?= !empty($source['xAxis']) ? $source['xAxis'] :0; ?>]}
					],
					yAxis: [{type: 'value'}],
					series: [<?= !empty($source['dataString']) ? $source['dataString'] :0; ?>]
				};                    
				myChart.setOption(option);
			}//面积图
			//仪表盘	
			function Dashboard(ele,data,des){
					var obj = {};
					obj.myChart = ec.init(ele	,{
						  noDataLoadingOption:{
						  text :"<?= Yii::t('app', 'user base help10') ?>",
						  effect : 'bubble',
						}
					});
				   obj.option = {
					tooltip : {
						formatter: "{a} : {c}ms"
					},
					series : [
						{
							name:"<?= Yii::t('app', 'average consumption') ?>",
							type:'gauge',
							title : {  
								show : true,  
								offsetCenter : [ 0, -40 ], // x, y，单位px  
								textStyle : {
									fontSize : 12  
								}  
							},  						
							detail: {textStyle: {color: 'auto',fontSize: 20}},	
							data:[{value: ""+data+"", name: ''+des+''}]
						}
					]
				};			
				obj.myChart.setOption(obj.option);
			}//仪表盘			
			
        }
    );
</script>