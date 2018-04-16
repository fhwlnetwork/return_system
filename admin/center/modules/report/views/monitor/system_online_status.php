<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2016/9/9
 * Time: 15:55
 */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use center\extend\Tool;

\center\assets\ReportAsset::newEchartsJs($this);

$sta = strtotime($params['start_time']);
$end = strtotime($params['end_time']);
$diff = $end - $sta;
if ($diff > 86400) {
    $unit = 'days';
    $unitStep = '1';
    $step = 86400;
}  else if ($diff > 3600){
    $unit = 'minutes';
    $unitStep = '60';
    $step = 3600;
} else {
    $unit = 'minutes';
    $unitStep = '5';
    $step = 300;
}

$yAxis = $xAxis = [];
$tool = new Tool();
$xAxis = $tool->substrTime($sta, $end, $unit, $unitStep);
array_pop($xAxis);
$timeLine = $tool->formatTime($unit, $xAxis);
$count = ceil(($end - $sta) / $step);
for ($i = 0; $i < $count; $i++) {
    $time = $step * $i + $sta;
    $yAxis[] = isset($data[$time]) ? $data[$time] : '0.00';
}
$yAxisLine = json_encode($yAxis, JSON_UNESCAPED_UNICODE);
?>
<div id="<?= $id; ?>" style="width:1000px;height:250px;"></div>
<script type="text/javascript">
    var myChart = echarts.init(document.getElementById("<?=$id?>"), {
        noDataLoadingOption: {
            text: "<?= Yii::t('app', 'user base help10') ?>",
            effect: 'bubble',
        }
    });
    var option = {
        title: {
            text: "<?= $title;?>"
        },
        legend: {
            data: ['<?=Yii::t('app', 'used percent')?>']
        },
        tooltip: {
            trigger: 'axis',
            axisPointer: {            // 坐标轴指示器，坐标轴触发有效
                type: 'line'        // 默认为直线，可选为：'line' | 'shadow'
            },
            formatter: function (params) {
                var str = '';
                for (var k in params) {
                    str += params[k].name + '<br/>'
                        + params[k].seriesName + ' : ' + params[k].value + '%' + '<br/>'
                }

                return str;
            }
        },
        grid: {
            left: '3%',
            right: '4%',
            bottom: '20%',
            containLabel: true
        },
        toolbox: {
            show: true,
            feature: {
                mark: {show: true},
                dataView: {show: true, readOnly: false, title: '数据视图'},
                magicType: {show: true, title: '关闭'},
                restore: {show: true, title: '刷新'},
                saveAsImage: {show: true, title: '保存'}
            }
        },
        calculable: true,
        xAxis: [
            {
                type: 'category',
                axisLabel: {
                    interval: 0,
                    rotate: 45,
                    margin: 2,
                    textStyle: {
                        color: "#222"
                    },
                },
                data: [<?=$timeLine?>]
            }
        ],
        yAxis: [
            {
                type: 'value',
                splitArea: {show: true},
                axisLabel: {
                    formatter: function (v) {
                        return v + '%';
                    }
                }

            }
        ],
        series: [
            {
                type: 'line',
                name: '<?= Yii::t('app', 'used percent') ?>',
                data: <?=$yAxisLine?>,
                areaStyle: {normal: {}},
            },
        ]
    };
    // 为echarts对象加载数据
    myChart.setOption(option);
</script>