/**
 * Created by Administrator on 2017/5/27.
 */

$(document).ready(function(){
    //周访客人数容器
    $half_year_chart = echarts.init(document.getElementById('half_year'));
    //用户组最近30天使用情况
    //最近30天消费
    $thirty_group_chart = echarts.init(document.getElementById('recently_thirty_group'));
    $thirty_product_chart = echarts.init(document.getElementById('recently-thirty-product'));
    //最近30-60天消费
    $sixty_group_chart = echarts.init(document.getElementById('recently_sixty_group'));
    $sixty_product_chart = echarts.init(document.getElementById('recently-sixty-product'));

    if($half_year_chart){
        $half_year_chart.clear();
        $half_year_chart.showLoading({
            text: "图表数据正在努力加载...",
            effect: 'whirling',//'spin' | 'bar' | 'ring' | 'whirling' | 'dynamicLine' | 'bubble'
            textStyle: {
                fontSize: 20
            }
        });
        //获取昨日访客设备数量
        $.ajax({
                url:'ajax-get-half-year?type=checkout',
                success:function(data){
                    var data = eval('(' + data + ')');
                    option.series = data.series;
                    option.xAxis[0].data = data.xAxis;
                    option.xAxis.boundaryGap = false;
                    option.title.text = data.title;
                    $half_year_chart.hideLoading();
                    $half_year_chart.setOption(option);
                },
                error:function(){
                    $half_year_chart.hideLoading();
                    alert('获取数据失败');
                }

            }
        );
    }
    if($thirty_group_chart){
        $thirty_group_chart.showLoading({
            text: "图表数据正在努力加载...",
            effect: 'whirling',//'spin' | 'bar' | 'ring' | 'whirling' | 'dynamicLine' | 'bubble'
            textStyle: {
                fontSize: 20
            }
        });
        //获取昨日访客设备数量
        $.ajax({

                url:'ajax-get-group-data?type=1&pay=checkout',
                success:function(data){
                    var data = eval('(' + data + ')');
                    option.series = data.series;
                    option.grid.bottom = '20%';
                    option.grid.left = '10%';
                    option.xAxis[0].data = data.xAxis;
                    option.xAxis[0].axisLabel.interval = 5;
                    option.xAxis[0].axisLabel.rotate = 25;
                    option.xAxis.boundaryGap = false;
                    option.title.text = data.title;
                    $thirty_group_chart.hideLoading();
                    $thirty_group_chart.setOption(option);
                },
                error:function(){
                    alert('获取数据失败');
                }

            }
        );
    }
    if($sixty_group_chart){
        $sixty_group_chart.showLoading({
            text: "图表数据正在努力加载...",
            effect: 'whirling',//'spin' | 'bar' | 'ring' | 'whirling' | 'dynamicLine' | 'bubble'
            textStyle: {
                fontSize: 20
            }
        });
        //获取昨日访客设备数量
        $.ajax({
                url:'ajax-get-group-data?type=2&pay=checkout',
                success:function(data){
                    var data = eval('(' + data + ')');
                    option.series = data.series;
                    option.grid.bottom = '20%';
                    option.grid.left = '10%';
                    option.xAxis[0].data = data.xAxis;
                    option.xAxis[0].axisLabel.interval = 5;
                    option.xAxis[0].axisLabel.rotate = 35;
                    option.xAxis.boundaryGap = false;
                    option.title.text = data.title;
                    $sixty_group_chart.hideLoading();
                    $sixty_group_chart.setOption(option);
                },
                error:function(){
                    alert('获取数据失败');
                }

            }
        );
    }
    if($thirty_product_chart){
        $thirty_product_chart.showLoading({
            text: "图表数据正在努力加载...",
            effect: 'whirling',//'spin' | 'bar' | 'ring' | 'whirling' | 'dynamicLine' | 'bubble'
            textStyle: {
                fontSize: 20
            }
        });
        //获取昨日访客设备数量
        $.ajax({
                url:'ajax-get-product-data?type=1&pay=checkout',
                success:function(data){
                    var data = eval('(' + data + ')');
                    if (data.count > 0) {
                        pie_option.series[0].data = data.series;
                        pie_option.series[0].name = data.name;
                        pie_option.legend.data = data.legend;
                        pie_option.title.text = data.title;
                        $thirty_product_chart.hideLoading();
                        $thirty_product_chart.setOption(pie_option);
                    } else {
                        $thirty_product_chart.hideLoading();
                        $thirty_product_chart.showLoading({
                            text: "暂无产品结算情况",
                            effect: 'whirling',//'spin' | 'bar' | 'ring' | 'whirling' | 'dynamicLine' | 'bubble'
                            textStyle: {
                                fontSize: 20
                            }
                        });
                    }

                },
                error:function(){
                    alert('获取数据失败');
                }

            }
        );
    }

    if($sixty_product_chart){
        $sixty_product_chart.showLoading({
            text: "图表数据正在努力加载...",
            effect: 'whirling',//'spin' | 'bar' | 'ring' | 'whirling' | 'dynamicLine' | 'bubble'
            textStyle: {
                fontSize: 20
            }
        });
        //获取昨日访客设备数量
        $.ajax({
                url:'ajax-get-product-data?type=2&pay=checkout',
                success:function(data){
                    var data = eval('(' + data + ')');
                    if (data.count > 0) {
                        pie_option.series[0].data = data.series;
                        pie_option.series[0].name = data.name;
                        pie_option.legend.data = data.legend;
                        pie_option.title.text = data.title;
                        $sixty_product_chart.hideLoading();
                        $sixty_product_chart.setOption(pie_option);
                    } else {
                        $sixty_product_chart.hideLoading();
                        $sixty_product_chart.showLoading({
                            text: "暂无产品结算情况",
                            effect: 'whirling',//'spin' | 'bar' | 'ring' | 'whirling' | 'dynamicLine' | 'bubble'
                            textStyle: {
                                fontSize: 20
                            }
                        });
                    }

                },
                error:function(){
                    alert('获取数据失败');
                }

            }
        );
    }
});
//折线图 柱状图数据模板
option = {
    backgroundColor: 'white',
    title: {
        text: '堆叠区域图'
    },
    tooltip : {
        trigger: 'axis'
    },
    legend: {
        data:['邮件营销','联盟广告','视频广告','直接访问','搜索引擎']
    },
    toolbox: {
        feature: {
            magicType: {show: true, type: ['line', 'bar']},
            restore: {show: true},
            saveAsImage: {},
        }

    },
    dataZoom: [{
        type: 'inside',
        start: 0,
        end: 100
    }, {
        start: 0,
        end: 100,
        handleIcon: 'M10.7,11.9v-1.3H9.3v1.3c-4.9,0.3-8.8,4.4-8.8,9.4c0,5,3.9,9.1,8.8,9.4v1.3h1.3v-1.3c4.9-0.3,8.8-4.4,8.8-9.4C19.5,16.3,15.6,12.2,10.7,11.9z M13.3,24.4H6.7V23h6.6V24.4z M13.3,19.6H6.7v-1.4h6.6V19.6z',
        handleSize: '80%',
        handleStyle: {
            color: '#fff',
            shadowBlur: 3,
            shadowColor: 'rgba(0, 0, 0, 0.6)',
            shadowOffsetX: 2,
            shadowOffsetY: 2
        }
    }],
    grid: {
        left: '3%',
        right: '4%',
        bottom: '7%',
        containLabel: true
    },
    xAxis : [
        {
            axisLabel: {
                interval:0,
                rotate: 0
            },
            type : 'category',
            data : ['周一','周二','周三','周四','周五','周六','周日']
        }
    ],
    yAxis : [
        {
            type : 'value'
        }
    ],
    series : [

    ]
};
pie_option = {
    backgroundColor: 'white',
    title: {
        text: '堆叠区域图'
    },
    grid: {
        left: '3%',
        right: '4%',
        bottom: '7%',
        containLabel: true
    },
    tooltip: {
        trigger: 'item',
        formatter: "{a} <br/>{b}: {c} ({d}%)"
    },
    legend: {
        orient: 'vertical',
        x: 'left',
        top: '15%',
        data:['直接访问','邮件营销','联盟广告','视频广告','搜索引擎']
    },
    series: [
        {
            name:'访问来源',
            type:'pie',
            radius : '55%',
            center: ['60%', '70%'],
            avoidLabelOverlap: false,
            label: {
                normal: {
                    show: false,
                    position: 'center'
                },
                emphasis: {
                    show: true,
                    textStyle: {
                        fontSize: '30',
                        fontWeight: 'bold'
                    }
                }
            },
            labelLine: {
                normal: {
                    show: false
                }
            },
            data:[]
        }
    ]
};