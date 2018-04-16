/**
 * Created by Administrator on 2017/6/8.
 */


app.controller('system', ['$scope', '$http',
    function ($scope, $http) {
        $scope.$watch('$viewContentLoaded', function () {
            $http({
                method: "GET",
                url: "/report/system/ajax-get-all-data"
            }).
                success(function (data, status) {
                    if (data.code == 200) {
                        $scope.times = data.rows.length;
                        for (var id_pre in data.data) {
                            content =  data.data[id_pre].text+'<br/>'+data.data[id_pre].ip;
                            if (data.data[id_pre].error) {
                                content += '<br/>' + data.data[id_pre].error;
                            }
                            $('#' + id_pre + '_status').attr('class', data.data[id_pre].icon);
                            $('#' + id_pre + '_box').attr('class', 'box-icon ' + data.data[id_pre].color + ' fa-shadow');
                            $('#' + id_pre + '_msg').html(content);
                        }
                        $('#status').html('数据加载完成, 请点击查看详细');
                    } else {
                        alert(data.msg)
                    }
                }).
                error(function (data, status) {
                    console.log(status)
                    //$scope.data = data || "Request failed";
                    //$scope.status = status;
                });
        });
    }]);

app.controller("sourceCtrl", ["$scope", "$http", function ($scope, $http) {
    $scope.getSource = function (ip, type) {
        $scope.type = type;
        $scope.searchField = {'ip': ip, 'type': type};
        $http({
            type: 'GET',
            url: '/report/system/ajax-get-one-type-status',
            params: $scope.searchField,
            async: false
        }).success(function (data) {
            if (data.code == 200) {
                $system_chart = echarts.init(document.getElementById('main'));
                var option;
                if (data.type == 'pie') {
                    if (typeof (data.single) != 'undefined' && !data.signle) {
                        pie_arr_option.baseOption.timeline.data = data.base;
                        pie_arr_option.baseOption.title.text = data.title;
                        pie_arr_option.baseOption.legend.data = data.legends;
                        pie_arr_option.options = data.series.option;
                        option = pie_arr_option;
                    } else {
                        pie_option.legend.data = data.legends;
                        pie_option.series = data.series;
                        option = pie_option;
                    }
                } else {
                    if (typeof (data.single) != 'undefined' && !data.signle) {
                        bar_arr_option.baseOption.timeline.data = data.base;
                        bar_arr_option.baseOption.title.text = data.title;
                        bar_arr_option.baseOption.legend.data = data.legends;
                        bar_arr_option.baseOption.xAxis[0].data = data.xAxis;
                        bar_arr_option.baseOption.series = data.series.base;
                        bar_arr_option.options = data.series.option;
                        option = bar_arr_option;
                    } else {
                        bar_option.xAxis[0].data = data.xAxis;
                        bar_option.legend.data = data.legends;
                        bar_option.series = data.series;
                        option = bar_option;
                    }
                }
                $system_chart.setOption(option);
            } else {
                $('#main').html(data.msg);
            }

        }).error(function (data, status) {
            console.log('err:' + status);
        });
    }
}]);

bar_option = {
    color: ['#ff7f50', '#87cefa', '#7b68ee', '#00fa9a', '#ffd700', '#3cb371', '#b8860b', '#30e0e0'],
    tooltip: {
        trigger: 'axis',
        axisPointer: {
            type: 'cross',
            label: {
                backgroundColor: '#6a7985'
            }
        }
    },
    legend: {
        data: ['邮件营销', '联盟广告', '视频广告', '直接访问', '搜索引擎']
    },
    toolbox: {
        feature: {
            saveAsImage: {}
        }
    },
    grid: {
        left: '3%',
        right: '4%',
        bottom: '3%',
        containLabel: true
    },
    xAxis: [
        {
            type: 'category',
            boundaryGap: false,
            data: ['周一', '周二', '周三', '周四', '周五', '周六', '周日']
        }
    ],
    yAxis: [
        {
            type: 'value'
        }
    ],
    series: []
};
bar_arr_option = {
    baseOption: {
        timeline: {
            // y: 0,
            axisType: 'category',
            // realtime: false,
            // loop: false,
            autoPlay: false,
            // currentIndex: 2,
            playInterval: 2000,
            controlStyle: {
                position: 'left'
            },
            bottom: 10,
            data: [],
        },
        title: {
            text: 'test'
        },

        color: ['#ff7f50', '#87cefa', '#7b68ee', '#00fa9a', '#ffd700', '#3cb371', '#b8860b', '#30e0e0'],
        tooltip: {
            trigger: 'axis',
            axisPointer: {
                type: 'line',
                label: {
                    backgroundColor: '#6a7985'
                }
            },
        },
        legend: {
            data: [],
        },
        toolbox: {
            show: true,
            feature: {
                mark: {show: true},
                dataView: {show: true, readOnly: false},
                magicType: {show: true, type: ['line', 'bar']},
                restore: {show: true},
                saveAsImage: {show: true}
            }
        },
        grid: {
            top: 80,
            bottom: 80,
        },
        xAxis: [
            {
                type: 'category',
                boundaryGap: false,
                data: ['周一', '周二', '周三', '周四', '周五', '周六', '周日']
            }
        ],
        yAxis: [
            {
                type: 'value'
            }
        ],
        series: [],

    },
    options: [],

};
pie_option = {
    title: {
        text: '某站点用户访问来源',
        x: 'center'
    },
    tooltip: {
        trigger: 'item',
        formatter: "{a} <br/>{b} : {c} ({d}%)"
    },
    legend: {
        orient: 'vertical',
        left: 'left',
        data: ['直接访问', '邮件营销', '联盟广告', '视频广告', '搜索引擎']
    },
    series: []
};
pie_arr_option = {
    baseOption: {
        timeline: {
            // y: 0,
            axisType: 'category',
            // realtime: false,
            // loop: false,
            autoPlay: false,
            // currentIndex: 2,
            playInterval: 2000,
            controlStyle: {
                position: 'left'
            },
            bottom: 10,
            data: [],
        },
        title: {
            text: 'test'
        },
        color: ['#ff7f50', '#87cefa', '#7b68ee', '#00fa9a', '#ffd700', '#3cb371', '#b8860b', '#30e0e0'],
        tooltip: {
            trigger: 'item',
            formatter: "{a} <br/>{b}: {c} ({d}%)"
        },
        legend: {
            data: [],
        },
        toolbox: {
            show: true,
            feature: {
                mark: {show: true},
                dataView: {show: true, readOnly: false},
                restore: {show: true},
                saveAsImage: {show: true}
            }
        },
        grid: {
            top: 80,
            bottom: 80,
        },
        series: [
            {
                name: 'test',
                type: 'pie',
                radius: '55%',
                center: ['50%', '60%'],
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
                itemStyle: {
                    emphasis: {
                        shadowBlur: 10,
                        shadowOffsetX: 0,
                        shadowColor: 'rgba(0, 0, 0, 0.5)'
                    }
                }
            },
            {
                name: 'test',
                type: 'pie',
                radius: '55%',
                center: ['50%', '60%'],
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
                itemStyle: {
                    emphasis: {
                        shadowBlur: 10,
                        shadowOffsetX: 0,
                        shadowColor: 'rgba(0, 0, 0, 0.5)'
                    }
                }
            },
        ]

    },
    options: []
};

function getSource(ip, type) {
    var url = '/report/system/ajax-get-one-type-status?ip='+ip+'&type='+type;
    $.ajax({
        type:"GET",
        url: url,
        dataType:'json',
        success:function (data){
            if (data.code == 200) {
                $system_chart = echarts.init(document.getElementById('main'));
                var option;
                if (data.type == 'pie') {
                    if (typeof (data.single) != 'undefined' && !data.signle) {
                        pie_arr_option.baseOption.timeline.data = data.base;
                        pie_arr_option.baseOption.title.text = data.title;
                        pie_arr_option.baseOption.legend.data = data.legends;
                        pie_arr_option.options = data.series.option;
                        option = pie_arr_option;
                    } else {
                        pie_option.legend.data = data.legends;
                        pie_option.series = data.series;
                        option = pie_option;
                    }
                } else {
                    if (typeof (data.single) != 'undefined' && !data.signle) {
                        bar_arr_option.baseOption.timeline.data = data.base;
                        bar_arr_option.baseOption.title.text = data.title;
                        bar_arr_option.baseOption.legend.data = data.legends;
                        bar_arr_option.baseOption.xAxis[0].data = data.xAxis;
                        bar_arr_option.baseOption.series = data.series.base;
                        bar_arr_option.options = data.series.option;
                        option = bar_arr_option;
                    } else {
                        bar_option.xAxis[0].data = data.xAxis;
                        bar_option.legend.data = data.legends;
                        bar_option.series = data.series;
                        option = bar_option;
                    }
                }
                $system_chart.setOption(option);
            } else {
                $('#main').html(data.msg);
            }
        }
    });
}

