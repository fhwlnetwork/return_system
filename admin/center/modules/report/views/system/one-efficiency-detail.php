<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2017/6/26
 * Time: 13:47
 */

?>

    <div class="row page" style="margin:0;padding:0;">
        <div class="col-md-12 col-lg-12 col-sm-12">
            <?php if ($show): ?>
                <img src="../../uploads/monitor/efficiency<?= $id . '.png' ?>" alt="">
            <?php else : ?>
                <div id="<?= $id ?>" style="height:400px;"></div>
            <?php endif; ?>
        </div>
        <div class="col-md-12 col-lg-12 col-sm-12">
            <div class="page row" style="margin:0;padding:0;">
                <section class="panel panel-default table-dynamic">
                    <div class="panel-heading data-center"><strong><span
                                class="glyphicon glyphicon-th-large"></span> <?= $this->title ?>(<?= $subtext ?>)(<?=
                            $model->start_time . '--' . $model->stop_time;
                            ?>)
                        </strong>
                    </div>
                    <table class="table table-bordered table-striped table-responsive">
                        <thead>
                        <tr>
                            <?php foreach ($header as $v) : ?>
                                <th>
                                    <div class='th'><?= $v; ?></div>
                                </th>
                            <?php endforeach ?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($table)): foreach ($table as $one) : ?>
                            <tr>
                                <?php foreach ($one as $k => $v) : ?>
                                    <?php $color = '';if ($v > 100) : $color = 'red';?>
                                    <?php endif; ?>
                                    <td <?php if ($k != 0): ?>style="color: <?= $color?>"<?php endif;?>><?= $v; ?></td>
                                <?php endforeach ?>
                            </tr>
                        <?php endforeach;endif; ?>
                        </tbody>
                    </table>
                </section>
            </div>
        </div>
    </div>
<?php if (!$show): ?>
    <script>
        // 基于准备好的dom，初始化echarts图表
        var myChart = echarts.init(document.getElementById('<?=$id?>'));
        var colors = ['#5793f3', '#d14a61', '#675bba'];
        option = {
            backgroundColor: '#fff',
            color: ['#ff7f50', '#87cefa', '#7b68ee', '#00fa9a', '#ffd700', '#3cb371', '#b8860b', '#30e0e0'],
            title: {
                text: '<?= $text?>',
                subtext: '<?= $subtext?>'
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
                data:  <?=!empty($legends)? json_encode($legends):json_encode([])?>,
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
                    data:  <?=!empty($xAxis)? json_encode($xAxis):json_encode([])?>,
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
                        formatter: '{value} ' + '<?=$unit?>'
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
                        formatter: '{value}'
                    }
                }
                <?php endif;?>
            ],
            series: <?=!empty($series)? json_encode($series):json_encode([])?>,
        };
        console.log(option);
        myChart.setOption(option);
        var picInfo = myChart.getDataURL();
        if (picInfo) {
            if (picInfo) {
                $.ajax({
                    type: "post",
                    data: {
                        baseimg: picInfo,
                        sql_type: '<?= $model->sql_type?>',
                        proc: '<?= $id?>'
                    },
                    url: '/report/system/image-save',
                    async: true,
                    success: function (data) {
                        console.log(picInfo);
                    },
                    error: function (err) {
                        console.log('图片保存失败');
                        alert('图片保存失败');
                    }
                });
            }
        }
    </script>
<?php endif; ?>