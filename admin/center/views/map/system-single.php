<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2017/6/12
 * Time: 14:41
 */

//var_dump(isset($data['series']));
?>

<div id="main" style="width:100%;height:500px;margin-top:20px;background: white;" class="page"></div>

<script>
    // 基于准备好的dom，初始化echarts图表
    var myChart = echarts.init(document.getElementById('main'));
    var colors = ['#5793f3', '#d14a61', '#675bba'];
    option = {
        backgroundColor: '#fff',
        color: ['#ff7f50', '#87cefa', '#7b68ee', '#00fa9a', '#ffd700', '#3cb371', '#b8860b', '#30e0e0'],
        title: {
            text: '<?= $data['text']?>',
            subtext: '<?= $data['subtext']?>'
        },
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
            data:  <?=isset($data['legends'])? json_encode($data['legends']):json_encode([])?>,
        },
        animation: false,
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
                data:  <?=isset($data['xAxis'])? json_encode($data['xAxis']):json_encode([])?>,
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
                }
            },
            <?php if($unit == '%'): ?>
            {
                type: 'value',
                position: 'right',
                axisLine: {
                    lineStyle: {
                        color: colors[1]
                    }
                },
                axisLabel: {
                    formatter: '{value} ' + '<?=$unit?>'
                }
            }
            <?php endif;?>
        ],
        series: <?=isset($data['series'])? json_encode($data['series']):json_encode([])?>,
    };

    myChart.setOption(option);
    <?php if($save): ?>
    var picInfo = myChart.getDataURL();
    console.log(picInfo);
    if (picInfo) {
        if (picInfo) {
            $.ajax({
                type: "post",
                data: {
                    baseimg: picInfo,
                    sql_type: '<?= $model->sql_type?>'
                },
                url: '/report/system/image-save',
                async: true,
                success: function (data) {

                },
                error: function (err) {
                    console.log('图片保存失败');
                    alert('图片保存失败');
                }
            });
        }
    }
    <?php endif;?>
</script>