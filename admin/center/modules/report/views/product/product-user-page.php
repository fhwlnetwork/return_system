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

$this->title = Yii::t('app', "report/product/user");
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
                <?= Html::checkboxList('ProductUserNumber[showField][]', $searchField, $showField, ['class' => 'drag_inline']) ?>
            </div>
        </div>

        <?= Html::submitButton(Yii::t('app', 'search'), ['class' => 'btn btn-success']) ?>

        <div class="col-sm-12" style="text-align: left;color: #ffffff;">
            <?= $form->errorSummary($model); ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

<div class="page">
    <section class="panel panel-default table-dynamic">
        <?php
        if(!empty($tableSeries)){
            ?>
            <div style="float:right;margin-right:10px;margin-top:5px;">
                <button type="button" class="btn btn-default btn-sm">
                    <a href="user-excel?action=excel"><span class="glyphicon glyphicon-log-out"></span>excel</a>
                </button>
            </div>
            <?php
        }
        ?>
        <div class="panel-heading">
            <strong>
                <span class="glyphicon glyphicon-th-large"></span> <?= $this->title; ?>
            </strong>
            <?= !empty($BeginDate)?'（':'' ?><?= $BeginDate;?><?= !empty($BeginDate)?'—':'' ?><?= $EndingDate?><?= !empty($BeginDate)?'）':'' ?>

        </div>
        <?php

        // 重转数组
        if(!empty($tableSeries)){
            $new_html = '<table class="table table-bordered table-striped table-responsive">';
            $new_html .= '<td width="15%">'.Yii::t('app', 'products name').'</td>';
            $new_html .= '<td width="15%">'.Yii::t('app', 'user time').'</td>';
            $new_html .= '<td width="12%">'.Yii::t('app', 'total number').'</td>';
            $new_html .= '<td width="12%">'.Yii::t('app', 'active number').'</td>';
            $new_html .= '<td width="12%">'.Yii::t('app', 'download').'</td>';
            $new_html .= '<tr>';
            foreach ($tableSeries as $productName => $data) {
                if(count($data) >= 1){
                    $rowspan = count($data);
                    $new_html .= '<td rowspan='.$rowspan.'>'.$productName.'</td>';
                    foreach ($data as $date => $value) {
                        $new_html .= '<td>'.$date.'</td>';
                        $new_html .= '<td>'.$value['total_number'].'</td>';
                        $new_html .= '<td>'.$value['active_number'].'</td>';
                        $new_html .= '<td><a href="product-user-excel?products_id='.$value['product_id'].'&date='.$date.'">'.Html::button(Yii::t('app', 'download'), ['class' => 'btn btn-warning btn-xs']).'</a></td>';
                        $new_html .= '</tr><tr>';
                    }
                }else{
                    $background = ($data[0]['time'] == "/")?'style="background-color:#F6F6D7;"':'';
                    $new_html .= '<td '.$background.'>'.'/'.'</td>';
                    $new_html .= '<td '.$background.'>'.'/'.'</td>';
                    $new_html .= '<td '.$background.'>'.'/'.'</td>';
                    $new_html .= '</tr><tr>';
                }
            }
            $new_html .= '</tr></table>';
            print_r($new_html);
        }else{
            ?>
            <table class="table table-bordered table-striped table-responsive">
                <tr><td><?= Yii::t('app', 'user base help10') ?></td></tr>
            </table>

            <?php
        }
        ?>
    </section>
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
                    data:<?= !empty($lengend) ? json_encode($lengend) : '[]'; ?>
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
                xAxis : [
                    {
                        type : 'category',
                        data : <?= !empty($xAxisTime) ? json_encode($xAxisTime) : '[]'; ?>
                    }
                ],
                yAxis : [
                    {
                        type : 'value'
                    }
                ],
                series : [<?= !empty($chartSeries) ? $chartSeries : ''; ?>]
            };

            // 为echarts对象加载数据
            myChart.setOption(option);
        }
    );
</script>
<script>
    $('.select').on('click',function () {
        if($(this).prop('checked')){
            $(this).parent().parent().find('.col-md-10 input').prop('checked',true);
        }else{
            $(this).parent().parent().find('.col-md-10 input').prop('checked',false);
        }
    })
</script>