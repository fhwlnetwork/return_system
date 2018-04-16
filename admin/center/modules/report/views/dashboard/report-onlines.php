<?php$height = ($id == 'test') ? 400 : 310;?><div id="<?= $id; ?>" style="height:<?= $height ?>px;margin-top:20px;"></div><script type="text/javascript">    var myChart = echarts.init(document.getElementById("<?= $id;?>"));    option = {        tooltip: {            trigger: 'axis',        },        title: {            text: "<?= $title;?>"        },        xAxis: {            type: 'category',            boundaryGap: false,            data: <?=$source['legends']?>        },        legend: {            data: ['<?= Yii::t('app', 'user online')?>']        },        yAxis: {            type: 'value',            boundaryGap: [0, '100%']        },        <?php if($id == 'test'): ?>        toolbox: {            show: true,            feature: {                dataView: {show: true, readOnly: false},                magicType: {show: true, type: ['line', 'bar', 'stack', 'tiled']},                restore: {show: true},                saveAsImage: {show: true}            }        },        dataZoom: [{            type: 'inside',            start: 0,            end: 100        }, {            start: 0,            end: 100        }],        <?php endif;?>        series: [            {                name: '<?=Yii::t('app','user online')?>',                type: 'line',                smooth: true,                symbol: 'none',                sampling: 'average',                itemStyle: {                    normal: {                        color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{                            offset: 0,                            color: 'rgb(255, 158, 68)'                        }, {                            offset: 1,                            color: 'rgb(255, 70, 131)'                        }])                    }                },                areaStyle: {                    normal: {}                },                data: <?= $source['series']?>,                markPoint: {                    data: [                        {type: 'max', name: '<?= Yii::t('app', 'max') ?>'},                        {type : 'min', name: '<?= Yii::t('app', 'min') ?>'}                    ]                },            }        ]    };    // 为echarts对象加载数据    myChart.setOption(option);</script>