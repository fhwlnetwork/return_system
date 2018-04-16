<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use center\modules\report\models\SrunDetailDay;
use center\modules\visitor\models\Setting;

\center\assets\ReportAsset::echartsJs($this);
echo $this->render('/layouts/operate-menu');

$this->title = Yii::t('app', 'report/operate/index');

if (Yii::$app->session->get('bytes_usergroup')) {
    $searchField = array_keys(Yii::$app->session->get('bytes_usergroup'));
} else {
    $searchField = [];
}
?>
<div class="panel panel-default">
    <div class="panel-body" style="padding: 10px">

        <?php
        $form = ActiveForm::begin([
            'layout' => 'horizontal',
            'fieldConfig' => [
                'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}",
                'horizontalCssClasses' => [],
            ],
        ]);
        ?>

        <div class="col-md-2">
            <?= $form->field($model, 'start_At', [
                'template' => '<div class="col-sm-12">{input}</div>'
            ])->textInput(
                [
                    'value' => isset($model->start_At) ? $model->start_At : date('Y-m-01'),
                    'class' => 'form-control inputDate',
                    'placeHolder' => Yii::t('app', 'start time')
                ]);
            ?>
        </div>

        <div class="col-md-2">
            <?= $form->field($model, 'stop_At', [
                'template' => '<div class="col-sm-12">{input}</div>'
            ])->textInput(
                [
                    'value' => isset($model->stop_At) ? $model->stop_At : date('Y-m-d'),
                    'class' => 'form-control inputDate',
                    'placeHolder' => Yii::t('app', 'end time')
                ]);
            ?>
        </div>

        <div class="col-md-2" style="width:270px;">
            <div class="input-group" style="border:1px solid #cbd5dd">
                <?= Html::dropDownList('SrunDetailDay[step]', isset($model->step) ? $model->step : '500M', SrunDetailDay::getbytestype()['step'], ['class' => 'form-control', 'style' => 'border:0px;height:32px;']); ?>

                <span class="input-group-addon" style="border:none;height:32px"> <?= Yii::t('app', 'show') ?>
                    <?= Html::dropDownList('SrunDetailDay[unit]', isset($model->unit) ? $model->unit : '5', SrunDetailDay::getbytestype()['unit']); ?>
                    <?= Yii::t('app', 'section') ?>
                    </span>
            </div>
        </div>

        <!--- 选择用户组 start -->
        <div class="col-md-12 form-group" ng-cloak ng-show="advanced==1">
            <div class="col-md-2"><?= Yii::t('app', 'report operate remind1') ?></div>
            <div class="col-md-10">
                <?= Html::checkboxList('SrunDetailDay[user_group_id][]', $searchField, $showField, ['class' => 'drag_inline']) ?>
            </div>
        </div>

        <label class="text-info"><input type="checkbox" ng-model="advanced"/>
            <small><?= Yii::t('app', 'advanced') ?></small>
        </label>&nbsp;
        <!-- 选择用户组 end -->

        <?= Html::submitButton(Yii::t('app', 'search'), ['class' => 'btn btn-success']) ?>
        <div class="col-sm-12" style="text-align: left;color: #ffffff;">
            <?= $form->errorSummary($model); ?>
        </div>
        <?php $form->end(); ?>
    </div>
</div>

<!-- 为ECharts准备一个具备大小（宽高）的Dom -->
<div id="main" style="height:75%;padding:15px"></div>


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
            'echarts/chart/pie', // 使用柱状图就加载bar模块，按需加载
            'echarts/chart/funnel' // 使用柱状图就加载bar模块，按需加载
        ],
        function (ec) {
            // 基于准备好的dom，初始化echarts图表
			var myChart = ec.init(document.getElementById('main')	,{
				  noDataLoadingOption:{
				  text :"<?= Yii::t('app', 'user base help10') ?>",
				  effect : 'bubble',
				}
			});			
            var option = {
                title: {
                    text: '<?= $this->title;?>',
                    x: 'center'
                },
                tooltip: {
                    trigger: 'item',
                    formatter: "{a} <br/>{b} : {c} ({d}%)"
                },
                legend: {
                    orient: 'vertical',
                    x: 'left',
                    data: [<?= isset($source['legend']) ? $source['legend'] : '0'; ?>]
                },
                toolbox: {
                    show: true,
                    feature: {
                        mark: {show: false},
                        dataView: {show: false, readOnly: false},
                        magicType: {
                            show: true,
                            type: ['pie', 'funnel'],
                            option: {
                                funnel: {
                                    x: '25%',
                                    width: '50%',
                                    funnelAlign: 'left',
                                    max: <?= isset($source['max']) ? $source['max'] : '0'; ?>
                                }
                            }
                        },
                        restore: {show: true},
                        saveAsImage: {show: true}
                    }
                },
                calculable: true,
                series: [
                    {
                        name: '<?= Yii::t('app', 'report online bilingfont3') ?>',
                        type: 'pie',
                        radius: '67%',
                        center: ['50%', '50%'],
                        data: [<?= $source['xAxis'];?>]
                    }
                ]
            };

            // 为echarts对象加载数据
            myChart.setOption(option);
        }
    );
</script>