<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2017/5/18
 * Time: 9:46
 */
//var_dump($source);exit;
$flag = boolval(count(json_decode($data['legends'], true)) > 30);
?>

<div id="main" class="col-md-11" style="height:600px;;padding:30px"></div>
<!-- ECharts单文件引入 -->
<script type="text/javascript">
    // 基于准备好的dom，初始化echarts图表
    var myChart = echarts.init(document.getElementById('main'));
    option = {
        title: {
            text: '<?=$this->title?>',
            show: '<?= !$flag?>',
            subtext: '<?= $subtext?>',
            x: 'center',
            subtextStyle: {
                fontSize: 14
            }
        },
        tooltip: {
            trigger: 'item',
            formatter: "{a} <br/>{b} : {c} ({d}%)"
        },
        <?php if(count(json_decode($data['legends'])) <= 15): ?>
        color: [
            '#3366CC',
            '#99CC33',
            '#009966',
            '#9933FF',
            '#FF6666',
            '#0066CC',
            '#336699',
            '#339966',
            '#FFCC33',
            '#FF99CC',
            '#CCFFCC',
            '#CCCC66',
            '#FFFF00',
            '#0099CC',
            '#99CC33',
            '#6666CC',
            '#00CC00',
            '#333366',
            '#CCCCFF',
            '#339966',
            '#FF6666',
            '#009966'
        ],
        <?php endif;?>
        legend: {
            <?php if ($bottom): ?>
            x: 'center',
            y: 'bottom',
            <?php else :?>
            x: 'left',
            orient: 'vertical',
            <?php endif;?>
            data: <?=isset($data['legends'])?$data['legends']:[]?>
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
        calculable: true,
        series: <?=isset($data['series'])?$data['series']:json_encode([])?>
    };
    // 为echarts对象加载数据
    console.log(option);
    myChart.setOption(option);
</script>
