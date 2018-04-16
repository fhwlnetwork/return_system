/**
 * Created by wjh on 2017/6/1.
 */

$(document).ready(function(){
    //周访客人数容器
    $half_year_bytes_chart = echarts.init(document.getElementById('half_year_bytes'));
    $half_year_times_chart = echarts.init(document.getElementById('half_year_times'));
    //用户组最近30天使用情况
    //最近30天消费
    $thirty_group_bytes_chart = echarts.init(document.getElementById('recently_thirty_group_bytes'));
    $thirty_group_times_chart = echarts.init(document.getElementById('recently_thirty_group_times'));
    $thirty_products_bytes_chart = echarts.init(document.getElementById('recently_thirty_products_bytes'));
    $thirty_products_times_chart = echarts.init(document.getElementById('recently_thirty_products_times'));

    $sixsty_group_bytes_chart = echarts.init(document.getElementById('recently_sixty_group_bytes'));
    $sixsty_group_times_chart = echarts.init(document.getElementById('recently_sixty_group_times'));
    $sixsty_products_bytes_chart = echarts.init(document.getElementById('recently_sixty_products_bytes'));
    $sixsty_products_times_chart = echarts.init(document.getElementById('recently_sixty_products_times'));

    $recently_bytes_chart = echarts.init(document.getElementById('recently-bytes'));
    $recently_times_chart = echarts.init(document.getElementById('recently-times'));

    if($half_year_bytes_chart){
        $half_year_bytes_chart.clear();
        $half_year_times_chart.clear();
        $half_year_bytes_chart.showLoading({
            text: "图表数据正在努力加载...",
            effect: 'whirling',//'spin' | 'bar' | 'ring' | 'whirling' | 'dynamicLine' | 'bubble'
            textStyle: {
                fontSize: 20
            }
        });
        $half_year_times_chart.showLoading({
            text: "图表数据正在努力加载...",
            effect: 'whirling',//'spin' | 'bar' | 'ring' | 'whirling' | 'dynamicLine' | 'bubble'
            textStyle: {
                fontSize: 20
            }
        });
        //获取昨日访客设备数量
        $.ajax({
                url:'ajax-get-half-year',
                success:function(data){
                    var data = eval('(' + data + ')');
                    option.series = data.series[0];
                    option.xAxis[0].data = data.xAxis;
                    option.xAxis.boundaryGap = false;
                    option.title.text = data.title[0];
                    option.title.subtext = 'unit: Tb';
                    $half_year_bytes_chart.hideLoading();
                    $half_year_bytes_chart.setOption(option);
                    option.series = data.series[1];
                    option.title.text = data.title[1];
                    option.title.subtext = 'unit: Hour';
                    $half_year_times_chart.hideLoading();
                    $half_year_times_chart.setOption(option);
                },
                error:function(){
                    $half_year_bytes_chart.hideLoading();
                    alert('获取数据失败');
                }

            }
        );
    }

    if($thirty_group_bytes_chart){
        $thirty_group_bytes_chart.clear();
        $thirty_group_times_chart.clear();
        $thirty_group_bytes_chart.showLoading({
            text: "图表数据正在努力加载...",
            effect: 'whirling',//'spin' | 'bar' | 'ring' | 'whirling' | 'dynamicLine' | 'bubble'
            textStyle: {
                fontSize: 20
            }
        });
        $thirty_group_times_chart.showLoading({
            text: "图表数据正在努力加载...",
            effect: 'whirling',//'spin' | 'bar' | 'ring' | 'whirling' | 'dynamicLine' | 'bubble'
            textStyle: {
                fontSize: 20
            }
        });
        //获取昨日访客设备数量
        $.ajax({
                url:'ajax-get-group-data',
                success:function(data){
                    var data = eval('(' + data + ')');
                    option.series = data.series[0];
                    option.xAxis[0].data = data.xAxis;
                    option.xAxis.boundaryGap = false;
                    option.grid.bottom = '20%';
                    option.grid.left = '10%';
                    option.xAxis[0].axisLabel.interval = 5;
                    option.xAxis[0].axisLabel.rotate = 25;
                    option.title.text = data.title[0];
                    option.title.subtext = 'unit: Tb';
                    $thirty_group_bytes_chart.hideLoading();
                    $thirty_group_bytes_chart.setOption(option);
                    option.series = data.series[1];
                    option.title.text = data.title[1];
                    option.title.subtext = 'unit: Hour';
                    $thirty_group_times_chart.hideLoading();
                    $thirty_group_times_chart.setOption(option);
                },
                error:function(){
                    alert('获取数据失败');
                }

            }
        );
    }

    if($sixsty_group_bytes_chart){
        $sixsty_group_bytes_chart.clear();
        $sixsty_group_times_chart.clear();
        $sixsty_group_bytes_chart.showLoading({
            text: "图表数据正在努力加载...",
            effect: 'whirling',//'spin' | 'bar' | 'ring' | 'whirling' | 'dynamicLine' | 'bubble'
            textStyle: {
                fontSize: 20
            }
        });
        $sixsty_group_times_chart.showLoading({
            text: "图表数据正在努力加载...",
            effect: 'whirling',//'spin' | 'bar' | 'ring' | 'whirling' | 'dynamicLine' | 'bubble'
            textStyle: {
                fontSize: 20
            }
        });
        //获取昨日访客设备数量
        $.ajax({
                url:'ajax-get-group-data?type=2',
                success:function(data){
                    var data = eval('(' + data + ')');
                    option.series = data.series[0];
                    option.xAxis[0].data = data.xAxis;
                    option.xAxis.boundaryGap = false;
                    option.grid.bottom = '20%';
                    option.grid.left = '10%';
                    option.xAxis[0].axisLabel.interval = 5;
                    option.xAxis[0].axisLabel.rotate = 25;
                    option.title.text = data.title[0];
                    option.title.subtext = 'unit: Tb';
                    $sixsty_group_bytes_chart.hideLoading();
                    $sixsty_group_bytes_chart.setOption(option);
                    option.series = data.series[1];
                    option.title.text = data.title[1];
                    option.title.subtext = 'unit: Hour';
                    $sixsty_group_times_chart.hideLoading();
                    $sixsty_group_times_chart.setOption(option);
                },
                error:function(){
                    alert('获取数据失败');
                }

            }
        );
    }


    if($thirty_products_bytes_chart){
        $thirty_products_bytes_chart.clear();
        $thirty_products_times_chart.clear();
        $thirty_products_bytes_chart.showLoading({
            text: "图表数据正在努力加载...",
            effect: 'whirling',//'spin' | 'bar' | 'ring' | 'whirling' | 'dynamicLine' | 'bubble'
            textStyle: {
                fontSize: 20
            }
        });
        $thirty_products_times_chart.showLoading({
            text: "图表数据正在努力加载...",
            effect: 'whirling',//'spin' | 'bar' | 'ring' | 'whirling' | 'dynamicLine' | 'bubble'
            textStyle: {
                fontSize: 20
            }
        });
        //获取昨日访客设备数量
        $.ajax({
                url:'ajax-get-product-data',
                success:function(data){
                    var data = eval('(' + data + ')');
                    if (data.count > 0) {
                        pie_option.series[0].data = data.series['total_bytes'];
                        pie_option.series[0].name = data.names[0];
                        pie_option.legend.data = data.legend;
                        pie_option.title.text = data.title[0];
                        pie_option.title.subtext = 'unit: Tb';
                        $thirty_products_bytes_chart.hideLoading();
                        $thirty_products_bytes_chart.setOption(pie_option);
                        pie_option.series[0].data = data.series['time_long'];
                        pie_option.title.text = data.title[1];
                        pie_option.title.subtext = 'unit: Hour';
                        $thirty_products_times_chart.hideLoading();
                        $thirty_products_times_chart.setOption(pie_option);
                    } else {
                        $thirty_products_bytes_chart.hideLoading();
                        $thirty_products_bytes_chart.showLoading({
                            text: "暂无产品上网情况",
                            effect: 'whirling',//'spin' | 'bar' | 'ring' | 'whirling' | 'dynamicLine' | 'bubble'
                            textStyle: {
                                fontSize: 20
                            }
                        });
                        $thirty_products_times_chart.hideLoading();
                        $thirty_products_times_chart.showLoading({
                            text: "暂无产品上网情况",
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

    if($sixsty_products_bytes_chart){
        $sixsty_products_bytes_chart.clear();
        $sixsty_products_times_chart.clear();
        $sixsty_products_bytes_chart.showLoading({
            text: "图表数据正在努力加载...",
            effect: 'whirling',//'spin' | 'bar' | 'ring' | 'whirling' | 'dynamicLine' | 'bubble'
            textStyle: {
                fontSize: 20
            }
        });
        $sixsty_products_times_chart.showLoading({
            text: "图表数据正在努力加载...",
            effect: 'whirling',//'spin' | 'bar' | 'ring' | 'whirling' | 'dynamicLine' | 'bubble'
            textStyle: {
                fontSize: 20
            }
        });
        //获取昨日访客设备数量
        $.ajax({
                url:'ajax-get-product-data?type=2',
                success:function(data){
                    var data = eval('(' + data + ')');
                    if (data.count > 0) {
                        pie_option.series[0].data = data.series['total_bytes'];
                        pie_option.series[0].name = data.names[0];
                        pie_option.legend.data = data.legend;
                        pie_option.title.text = data.title[0];
                        pie_option.title.subtext = 'unit: Tb';
                        $sixsty_products_bytes_chart.hideLoading();
                        $sixsty_products_bytes_chart.setOption(pie_option);
                        pie_option.series[0].data = data.series['time_long'];
                        pie_option.title.text = data.title[1];
                        pie_option.title.subtext = 'unit: Hour';
                        $sixsty_products_times_chart.hideLoading();
                        $sixsty_products_times_chart.setOption(pie_option);
                    } else {
                        $sixsty_products_bytes_chart.hideLoading();
                        $sixsty_products_bytes_chart.showLoading({
                            text: "暂无产品上网情况",
                            effect: 'whirling',//'spin' | 'bar' | 'ring' | 'whirling' | 'dynamicLine' | 'bubble'
                            textStyle: {
                                fontSize: 20
                            }
                        });
                        $sixsty_products_times_chart.hideLoading();
                        $sixsty_products_times_chart.showLoading({
                            text: "暂无产品上网情况",
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

    if($recently_bytes_chart){
        $recently_bytes_chart.clear();
        $recently_times_chart.clear();
        $recently_bytes_chart.showLoading({
            text: "图表数据正在努力加载...",
            effect: 'whirling',//'spin' | 'bar' | 'ring' | 'whirling' | 'dynamicLine' | 'bubble'
            textStyle: {
                fontSize: 20
            }
        });
        $recently_times_chart.showLoading({
            text: "图表数据正在努力加载...",
            effect: 'whirling',//'spin' | 'bar' | 'ring' | 'whirling' | 'dynamicLine' | 'bubble'
            textStyle: {
                fontSize: 20
            }
        });
        //获取昨日访客设备数量
        $.ajax({
                url:'ajax-get-recently-data',
                success:function(data){
                    var data = eval('(' + data + ')');
                    option.series = data.series[0];
                    option.xAxis[0].data = data.xAxis;
                    option.xAxis.boundaryGap = false;
                    option.title.text = data.title[0];
                    option.xAxis[0].axisLabel.interval = 0;
                    option.title.subtext = 'unit: Tb';
                    $recently_bytes_chart.hideLoading();
                    $recently_bytes_chart.setOption(option);
                    option.series = data.series[1];
                    option.title.text = data.title[1];
                    option.title.subtext = 'unit: Hour';
                    $recently_times_chart.hideLoading();
                    $recently_times_chart.setOption(option);
                },
                error:function(){
                    $recently_bytes_chart.hideLoading();
                    $recently_times_chart.hideLoading();
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
        text: '堆叠区域图',
        subtext: 'test',
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
pie_option = {
    backgroundColor: 'white',
    title: {
        text: '堆叠区域图',
        subtext: '堆叠区域图',
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