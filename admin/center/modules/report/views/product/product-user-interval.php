<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use center\modules\report\models\ProductUserNumber;
use center\modules\visitor\models\Setting;
use center\modules\report\models\OnlineReportPoint;

\center\assets\ReportAsset::echartsJs($this);
echo $this->render('/layouts/product-menu');

if(Yii::$app->session->get('user_searchProduct')) {
    $searchField = Yii::$app->session->get('user_searchProduct');
} else {
    $searchField = [];
}

$this->title = Yii::t('app', "report/product/user-interval");
?>

<div class="panel panel-default">
    <div class="panel-body" style="padding: 10px">
        <?php
        $form = ActiveForm::begin([
            'layout' => 'horizontal',
            'fieldConfig' => [
                'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}"
            ],
        ]);
        ?>

        <div class="col-md-2">
            <?=
            $form->field($model, 'start_At', ['template' => '<div class="col-sm-12">{input}</div>'])
                ->textInput(
                    [
                        'value' => isset($model->start_At) ? $model->start_At : date('Y-m-d',strtotime('-7 day')),
                        'class' => 'form-control inputDate',
                        'placeHolder' => Yii::t('app', 'start time')
                    ]);
            ?>
        </div>

        <div class="col-md-2">
            <?=
            $form->field($model, 'stop_At', [
                'template' => '<div class="col-sm-12">{input}</div>'
            ])->textInput(
                [
                    'value' => isset($model->stop_At) ? $model->stop_At : date('Y-m-d', strtotime('+1 hour')),
                    'class' => 'form-control inputDate',
                    'placeHolder' => Yii::t('app', 'end time')
                ]);
            ?>
        </div>


        <div class="col-md-12 form-group">
            <div class="col-md-1"><?= Yii::t('app', 'user products id select') ?>:</div>
            <div class="col-md-1">
                <input class="select" type="checkbox"><?=Yii::t('app','all select/all not select')?>
            </div>
            <div class="col-md-10">
                <?= Html::checkboxList('OnlineProductUser[showField][]', !empty($model->showField)?$model->showField:array_keys($showField), $showField, ['class' => 'drag_inline']) ?>
            </div>
        </div>

        <?= Html::submitButton(Yii::t('app', 'search'), ['class' => 'btn btn-success']) ?>

        <div class="col-sm-12" style="text-align: left;color: #ffffff;">
            <?= $form->errorSummary($model); ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

<div id="main" style="height:80%;padding:15px"></div>
<!-- ECharts单文件引入 -->
<script type="text/javascript">

    // 路径配置
    require.config({
        paths: {
            echarts: '/lib/echarts/build/dist'
        }
    });

    // 使用
    require(
        [
            'echarts',
            'echarts/chart/line', // 使用柱状图就加载bar模块，按需加载
            'echarts/chart/bar' // 使用柱状图就加载bar模块，按需加载
        ],
        function (ec) {
            // 基于准备好的dom，初始化echarts图表
            var myChart = ec.init(document.getElementById('main'));

            var option = {
                title : {
                    text: '<?= $this->title;?>',
                    subtext: '<?= !empty($BeginDate) ? $BeginDate." — ".$EndingDate : ''; ?>'
                },
                tooltip : {
                    trigger: 'axis'
                },
                legend: {
                    data:<?= !empty($data['legend']) ? json_encode($data['legend']) : '[]'; ?>
                },
                toolbox: {
                    show : true,
                    showTitle:false,
                    feature : {
                        mark : {show: false},
                        dataView : {show: true, readOnly: false},
                        magicType : {show: true, type: ['line', 'bar']},
                        restore : {show: true},
                        saveAsImage : {show: true}
                    }
                },
                calculable : true,
                grid:{
                    y2:'25%'
                },
                xAxis : [
                    {
                        type : 'category',
                        axisLabel : {
                            interval:0,
                            rotate:45
                        },
                        data : <?= !empty($data['xAxis']) ? json_encode($data['xAxis']) : '[]'; ?>
                    }
                ],
                yAxis : [
                    {
                        type : 'value'
                    }
                ],
                series : <?= !empty($data['series']) ? json_encode($data['series']) : []; ?>
            };

            // 为echarts对象加载数据
            myChart.setOption(option);
        }
    );
</script>

<div class="page">
    <section class="panel panel-default table-dynamic">
        <?php
        if(!empty($data['table'])){
            ?>
            <div style="float:right;margin-right:10px;margin-top:5px;">
                <button type="button" class="btn btn-default btn-sm">
                    <a href="user-interval?excel=1"><span class="glyphicon glyphicon-log-out"></span>excel</a>
                </button>
            </div>
            <?php
        }
        ?>
        <div class="panel-heading">
            <strong>
                <span class="glyphicon glyphicon-th-large"></span> <?= $this->title; ?>
            </strong>
            <?= !empty($model->start_At)?'（':'' ?><?= $model->start_At;?><?= !empty($model->start_At)?'—':'' ?><?= $model->stop_At?><?= !empty($model->stop_At)?'）':'' ?>

        </div>


        <?if($data['table']):?>
            <table class="table table-bordered table-striped table-responsive">
                <tr>
                    <td width="15%"><?=Yii::t('app', 'products name')?></td>
                    <td width="12%"><?=Yii::t('app', 'normal_amount')?></td>
                    <td width="12%"><?=Yii::t('app', 'abnormal_amount')?></td>
                </tr>
                <?foreach($data['table'] as $key => $value):?>
                    <tr>
                        <td width="15%"><?=$value['product_name']?></td>
                        <td width="12%"><?=$value['normal_amount']?></td>
                        <td width="12%"><?=$value['abnormal_amount']?></td>
                    </tr>
                <?endforeach;?>
            </table>
        <?else:?>
            <table class="table table-bordered table-striped table-responsive">
                <tr><td><?= Yii::t('app', 'user base help10') ?></td></tr>
            </table>
        <?endif?>

    </section>
</div>
<script>
    $('.select').on('click',function () {
        if($(this).prop('checked')){
            $(this).parent().parent().find('.col-md-10 input').prop('checked',true);
        }else{
            $(this).parent().parent().find('.col-md-10 input').prop('checked',false);
        }
    })
</script>


