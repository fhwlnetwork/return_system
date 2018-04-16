<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2017/5/16
 * Time: 10:26
 */
//var_dump($data);exit;
?>
<div id="main" style="height:450px;margin:0 auto;padding:0;width:95%;"></div>
<script>
    var sql_type = '<?= $model->sql_type?>';
    var preg = /byte/;
    var myChart = echarts.init(document.getElementById('main'));
    option = {
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
                bottom: 00,
                data: <?=isset($data['base'])?$data['base']:json_encode([])?>,
            },
            title: {
                subtext: '<?= $name?>'
            },
            tooltip: {
                trigger: 'item',
                formatter: "{b}: {c} ({d}%)"
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
            legend: {
                top: 70,
                x: 'left',
                orient: 'vertical',
                data:<?=isset($data['legends'])?$data['legends']:json_encode([])?>,
            },
            calculable: true,
            grid: {
                top: 80,
                bottom: 200
            },
            color: [
                '#C33531','#EFE42A','#64BD3D','#EE9201','#29AAE3',
                '#B74AE5','#0AAF9F','#E89589'
            ],
            series: <?= json_encode($data['series']['base'])?>
        },
        options: <?= json_encode($data['series']['option'])?>
    };
    // console.log(option);
    myChart.setOption(option);

    function getTimeFormat(str) {
        var base = 60;
        if (str / (base * base) >= 1)
            return number_format(str / (base * base), 2) + "Hour";
        else if (str / base >= 1)
            return number_format(str / (base), 2) + "Minute";
        else
            return str + "s";
    }
    function getBytesFormat(str) {
        var base = 1024;
        if (str / (base * base * base) >= 1)
            return number_format(str / (base * base * base), 2) + "Gb";
        else if (str / (base * base) >= 1)
            return number_format(str / (base * base), 2) + "Mb";
        else if (str / base >= 1)
            return number_format(str / (base), 2) + "Kb";
        else
            return str + "b";
    }
    function number_format(str) {
        return str.toFixed(2);
    }
</script>
