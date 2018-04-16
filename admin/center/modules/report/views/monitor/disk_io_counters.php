<br />
<div id="disk_io<?= !empty($id) ? $id : ''; ?>" style="width:600px;height:250px;"></div>
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
			var myChart = ec.init(document.getElementById("disk_io<?= !empty($id) ? $id : ''; ?>")	,{
				  noDataLoadingOption:{
				  text :"<?= Yii::t('app', 'user base help10') ?>",
				  effect : 'bubble',
				}
			});	
			
			var option = {
				tooltip: {
					trigger: 'axis',
					axisPointer: {
						type: 'shadow'
					},
				},
				legend: {
					x: 'center',
					data:[<?= !empty($source['legend']) ? $source['legend'] : 0; ?>]
				},
				calculable : true,
				tooltip : {
					trigger: 'axis',
					formatter: function(params) {
						return params[0].name + '<br/>'
							   + params[0].seriesName + ' : ' + params[0].value + ' <br/>'
							   + params[1].seriesName + ' : ' + params[1].value + ' <br/>'
							   + params[2].seriesName + ' : ' + params[2].value + ' (KB)<br/>'
							   + params[3].seriesName + ' : ' + params[3].value + ' (KB)';
					}
				},				
				xAxis: [{
						type: 'category',
						boundaryGap : false,
						data: [<?= !empty($source['xAxis']) ? $source['xAxis'] : 0; ?>]}
				],
				yAxis: [{type: 'value'}],
				series: [<?= !empty($source['dataString']) ? $source['dataString'] : 0; ?>]
			};                    
			
            // 为echarts对象加载数据
            myChart.setOption(option);
        }
    );
</script>