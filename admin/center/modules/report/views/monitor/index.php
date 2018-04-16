<?php
/**
 * 实时监控
 */
use yii\helpers\Html;
use yii\helpers\Url;
use \yii\bootstrap\Modal;
use center\modules\report\models\Efficiency;
use center\modules\report\models\DashboardReports;


$this->title = Yii::t('app', 'report/monitor/index');
$Efficiency = new Efficiency();
$reports = new DashboardReports();
$this->registerJsFile('/js/app-monitor.js', ['depends' => [center\assets\ZTreeAsset::className()]]);
\center\assets\ReportAsset::newEchartsJs($this);
?>

    <div class="page page-dashboard">
        <div class="panel panel-success">
            <div class="panel panel-success panel-heading"><span
                    class="glyphicon glyphicon-th-large"></span><span
                    id="status"><?= Yii::t('app', 'monitor help1') ?></span>
            </div>
        </div>
        <div class="panel panel-default" data-ng-controller='system'>
            <br/>
            <?php
            if (!empty($serverType)):
                ?>

                <?php
                foreach ($serverType as $key => $value):
                    ?>
                    <div class="panel-body">
                        <div class="row">
                            <div class="title-top-border"><span class="title-top-s"><?= $key; ?></span>
                            </div>
                            <?php
                            if (is_array($value) && $value):
                                foreach ($value as $x => $y):
                                    ?>
                                    <div class="col-lg-6 col-md-6 col-xsm-6" style="text-align:center;">
                                        <h5><?= $x ?></h5>

                                        <div class="mini-box" style="margin:0 auto;">
                                            <?php
                                            $x = (preg_match('/Portal/i', $x)) ? 'Portal' : $x;
                                            if (is_array($y) && $y):
                                                foreach ($y as $m):
                                                    $id = str_replace('.', '', $m['ip']) . $m['devicename'] . str_replace(' ', '', $m['type']);
                                                    ?>

                                                    <div style="width:150px;display:inline-block;">
                                                        <div class="system_sta"
                                                             style="margin:0 10px;display:inline-block;cursor:pointer;position:relative;">
                                                            <?= Html::a('<span class="box-icon bg-success fa-shadow" style="margin:0px;" id="' . $id . '_box">
                                                            <i class="fa fa-check" id="' . $id . '_status"></i>
                                                        </span>', '/report/system/get-one-detail?ip=' . $m['ip'] . '&type=' . $x, [
                                                                'class' => 'create',
                                                                'data-toggle' => 'modal',
                                                                'data-target' => '#create-modal',
                                                                'data-msg' => Yii::t('app', 'monitor help1'),
                                                                'data-url' => '/report/system/get-one-detail?ip=' . $m['ip'] . '&type=' . $x,
                                                            ]); ?>

                                                        </div>
                                                        <div class="s_status"
                                                             id="<?= $id . '_msg' ?>">
                                                            <?= Yii::t('app', 'normal') ?><br/>
                                                            <?= $m['ip']; ?><br/>
                                                            <?= !empty($status['error']) ? Yii::t('app', $status['error']) : ''; ?>
                                                        </div>
                                                    </div>


                                                <?php endforeach;endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach;
                            endif;
                            ?>
                        </div>
                    </div>
                <?php endforeach;
                ?>
            <?php endif; ?>
        </div>
    </div>
<?php
Modal::begin([
    'id' => 'create-modal',
    'header' => '<h4 class="modal-title" id="chart_status" name="status"></h4>',
    'footer' => '<a href="#" class="btn btn-primary" data-dismiss="modal">Close</a>',
    'options' => [
        'data-backdrop' => 'static',//点击空白处不关闭弹窗
        'data-keyboard' => false,
    ],
]);
$requestUrl = Url::toRoute('/report/system/get-one-detail');
$js = <<<JS
$(".create").click(function() {
        data_url = $(this).attr('data-url');
        $.get(data_url, {},
                function (data) {
                     $('.modal-body').html(data);
                }
        );
        $($(this).attr('data-target')+" .modal-title").text($(this).attr('data-msg'));
        $($(this).attr('data-target')).modal("show");


      //  $($(this).attr('data-target')).after($($(this).attr('data-target')+" .modal-title").text('xxx'));



        return false;
});

JS;
$this->registerJs($js);
Modal::end();
?>