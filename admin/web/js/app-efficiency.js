/**
 * Created by Administrator on 2017/6/8.
 */


app.controller('efficiency', ['$scope', '$http',
    function($scope, $http) {
        $scope.$watch('$viewContentLoaded', function() {
            $scope.init = false;
            $http({
                method: "GET",
                url: "/report/dashboard/ajax-get-efficiency-status",
            }).
                success(function(data, status) {
                    $scope.single = '';
                    $scope.header = '';
                    $scope.showBody = '';
                    if(data.code == 200) {
                        $system_chart = echarts.init(document.getElementById('efficiency'));
                        if (data.single) {
                            $scope.single = data.single;
                            $scope.header = data.table.header;
                            $('#header').html($scope.header);
                            $scope.showBody = data.table.data;
                            option.series = data.series;
                            option.xAxis[0].data = data.xAxis;
                            option.legend.data = data.legends;
                            option.xAxis.boundaryGap = false;
                            option.title.text = data.text;
                            option.title.subtext = data.subtext;
                            $system_chart.setOption(option);
                        } else {
                            multi_option.baseOption.timeline.data = data.base.base;
                            multi_option.baseOption.legend.data = data.base.legends;
                            multi_option.baseOption.series = data.base.series;
                            multi_option.baseOption.xAxis[0].data = data.base.xAxis;
                            multi_option.options = data.options_data;
                            $system_chart.setOption(multi_option);
                        }
                    } else {
                       alert(data.msg)
                    }
                }).
                error(function(data, status) {
                    console.log(status)
                    //$scope.data = data || "Request failed";
                    //$scope.status = status;
                });
        });

        $scope.getSystemStatus = function (){
            if (typeof($scope.my_ip) == 'undefined') {
                $scope.my_ip = '';
            };
            var queryParams = '?proc_default='+$scope.proc+'&start_time='+$scope.start_time+'&stop_time='+$scope.stop_time+'&my_ip='+$scope.my_ip;
            $scope.init = false;
            $http({
                method: "GET",
                url: "/report/dashboard/ajax-get-efficiency-status"+queryParams
            }).
                success(function(data, status) {
                    $scope.single = '';
                    $scope.header = '';
                    $scope.showBody = '';
                    if(data.code == 200) {
                        $system_chart = echarts.init(document.getElementById('efficiency'));
                        if (data.single) {
                            $scope.single = data.single;
                            $scope.header = data.table.header;
                            //console.log($scope.header);
                            $scope.showBody = data.table.data;
                            console.log($scope.showBody);
                            option.series = data.series;
                            option.xAxis[0].data = data.xAxis;
                            option.legend.data = data.legends;
                            option.xAxis.boundaryGap = false;
                            option.title.text = data.text;
                            option.title.subtext = data.subtext;
                            $system_chart.setOption(option);
                        } else {
                            multi_option.baseOption.timeline.data = data.base.base;
                            multi_option.baseOption.legend.data = data.base.legends;
                            multi_option.baseOption.series = data.base.series;
                            multi_option.baseOption.xAxis[0].data = data.base.xAxis;
                            multi_option.options = data.options_data;
                            //console.log(multi_option);
                            $system_chart.setOption(multi_option);

                        }
                    } else {
                        alert(data.msg)
                    }
                }).
                error(function(data, status) {
                    console.log(status)
                    //$scope.data = data || "Request failed";
                    //$scope.status = status;
                });
        }


        $scope.$on('ngRepeatFinished', function (ngRepeatFinishedEvent) {
            //下面是在table render完成后执行的js
            var table = $("#leaderBoard").dataTable({
                bJQueryUI: true,
                "sScrollX": '100%',
            });
        });
    }]);


var colors = ['#5793f3', '#d14a61', '#675bba'];

option = {
    backgroundColor: 'white',
    color:  ['#ff7f50','#87cefa','#7b68ee','#00fa9a','#ffd700', '#3cb371','#b8860b','#30e0e0'],
    title: {},
    tooltip: {
        trigger: 'axis',
        axisPointer: {type: 'cross'}
    },
    grid: {
        bottom: '17%',
    },
    toolbox: {
        feature: {
            magicType: {show: true, type: ['line', 'bar']},
            dataView: {show: true, readOnly: false},
            restore: {show: true},
            saveAsImage: {show: true}
        }
    },
    legend: {
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
    xAxis: [
        {
            type: 'category',
            axisTick: {
                alignWithLabel: true
            },
            data: ['1月','2月','3月','4月','5月','6月','7月','8月','9月','10月','11月','12月']
        }
    ],
    yAxis: [
        {
            type: 'value',
            position: 'left',
            axisLine: {
                lineStyle: {
                    color: colors[0]
                }
            },
            axisLabel: {
                formatter: '{value} %'
            }
        },
        {
            type: 'value',
            position: 'right',
            axisLine: {
                lineStyle: {
                    color: colors[1]
                }
            },
            axisLabel: {
                formatter: '{value}'
            }
        },
    ],
    series: []
};

multi_option = {
    baseOption: {
        timeline: {
            // y: 0,
            axisType: 'category',
            // realtime: false,
            // loop: false,
            autoPlay: false,
            // currentIndex: 2,
            playInterval: 1000,
            // controlStyle: {
            //     position: 'left'
            // },
            bottom: 30,
            data: [],
        },
        title: {
            subtext: '数据来自国家统计局'
        },
        tooltip: {
            trigger: 'axis',
            axisPointer: {type: 'cross'}
        },
        color:  ['#ff7f50','#87cefa','#7b68ee','#00fa9a','#ffd700', '#3cb371','#b8860b','#30e0e0'],
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
        backgroundColor: 'white',
        legend: {
            x: 'right',
        },
        calculable : true,
        grid: {
            top: 80,
            bottom: 100
        },
        xAxis: [
            {
                'type':'category',
                'axisLabel':{'interval':3},
                splitLine: {show: false}
            }
        ],
        yAxis: [
            {
                type: 'value',
                position: 'left',
                axisLine: {
                    lineStyle: {
                        color: colors[0]
                    }
                },
                axisLabel: {
                    formatter: '{value} ms'
                }
            },
        ],
        series: []
    },
    options: [
    ]
};