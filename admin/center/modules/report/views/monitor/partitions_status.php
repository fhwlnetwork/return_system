<div class="panel-body">
<div id="partition<?= !empty($id) ? $id : ''; ?>" style="width:600px;height:200px;"></div>
</div>
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
			var myChart = ec.init(document.getElementById("partition<?= !empty($id) ? $id : ''; ?>")	,{
				  noDataLoadingOption:{
				  text :"<?= Yii::t('app', 'user base help10') ?>",
				  effect : 'bubble',
				}
			});	
			var labelTop = {
				normal : {
					label : {
						show : true,
						position : 'center',
						formatter : '{b}',
						textStyle: {
							baseline : 'bottom'
						}
					},
					labelLine : {
						show : false
					}
				}
			};
			var labelFromatter = {
				normal : {
					label : {
						formatter : function (params){
							var data = 100 - params.value;
							return data.toFixed(2) + '%'
						},
						textStyle: {
							baseline : 'top',
							color:'#608CF5',
							fontSize : 14  
						}
					}
				},
			}
			var labelBottom = {
				normal : {
					color: '#ccc',
					label : {
						show : true,
						position : 'center'
					},
					labelLine : {
						show : false
					}
				},
				emphasis: {
					color: 'rgba(0,0,0,0)'
				}
			};
			var radius = [50,80];
			var option = {
				legend: {
					x : 'center',
					y : 'bottom',
					data:[<?= !empty($source['legend']) ? $source['legend'] : 0; ?>]
				},					
				series : [<?= !empty($source['series']) ? $source['series'] : 0; ?>]
			};
                    
								

            // 为echarts对象加载数据
            myChart.setOption(option);
        }
    );
</script>