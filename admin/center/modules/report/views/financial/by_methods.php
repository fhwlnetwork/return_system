<?php
//use yii;
use yii\widgets\LinkPager;
use yii\helpers\Html;
use center\widgets\Alert;
use center\assets\ZTreeAsset;
use yii\bootstrap\ActiveForm;

/**
 * @var yii\web\View $this
 * @var $userArray
 */
//权限
$canPayList = Yii::$app->user->can('financial/pay/list');
$canRefundList = Yii::$app->user->can('financial/refund/list');
//$pay_types = $model->payTypes;
//ztree 搜索用
ZTreeAsset::addZtreeSelectMulti($this);
?>
    <style type="text/css">
        .ztree li a.curSelectedNode span {
            background-color: #0088cc;
            color: #fff;
            border-radius: 2px;
            padding: 2px;
        }
    </style>
    <div class="page">
        <?= Alert::widget() ?>
        <div class="row">
            <div class="panel panel-default">
                <div class="panel-body">
                    <?php
                    $form = ActiveForm::begin([
                        'layout' => 'horizontal',
                        'fieldConfig' => [
                            'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}",
                            'horizontalCssClasses' => [],
                        ],
                    ]);
                    ?>

                    <div class="col-md-2 col-lg-2 col-sm-2">
                        <?= $form->field($model, 'operator', [
                            'template' => '<div class="col-sm-12">{input}</div>'
                        ])->textInput(
                            [
                                'class' => 'form-control',
                                'placeHolder' => Yii::t('app', 'operator')
                            ]);
                        ?>
                    </div>
                    <div class="col-md-2 col-lg-2 col-sm-2">
                        <?= $form->field($model, 'start_time', [
                            'template' => '<div class="col-sm-12">{input}</div>'
                        ])->textInput(
                            [
                                'class' => 'form-control inputDate',
                                'placeHolder' => Yii::t('app', 'start time')
                            ]);
                        ?>
                    </div>

                    <div class="col-md-2 col-lg-2 col-sm-2">
                        <?= $form->field($model, 'stop_time', [
                            'template' => '<div class="col-sm-12">{input}</div>'
                        ])->textInput(
                            [
                                'class' => 'form-control inputDate',
                                'placeHolder' => Yii::t('app', 'end time')
                            ]);
                        ?>
                    </div>

                    <div class="col-md-4 col-sm-4 col-lg-4">
                        &nbsp;&nbsp;&nbsp;&nbsp;<label class="text-info"><input type="checkbox" ng-model="advanced"
                                                                                name="advanced" value="1"/>
                            <small><?= Yii::t('app', 'advanced') ?></small>
                        </label>
                        <?= Html::submitButton(Yii::t('app', 'search'), ['class' => 'btn btn-success']) ?>
                        <?= html::submitButton(yii::t('app', 'this month'), ['class' => 'btn btn-success', 'name' => 'timePoint', 'value' => '3']) ?>
                        <?= html::submitButton(yii::t('app', 'this quarter'), ['class' => 'btn btn-info', 'name' => 'timePoint', 'value' => '5']) ?>
                        <?= html::submitButton(yii::t('app', 'this year'), ['class' => 'btn btn-danger', 'name' => 'timePoint', 'value' => '7']) ?>
                    </div>

                    <div class="form-group" ng-cloak ng-show="advanced==1">
                        <!--组织结构-->
                        <div class="row col-md-12">
                            <div class="col-md-2" style="width:100px;"><?= Yii::t('app', 'organization help4') ?></div>
                            <div class="col-md-10">
                                <div class="panel panel-default">
                                    <div class="panel-body" style="max-height: 500px; overflow-y: auto;">
                                        <?= Html::hiddenInput("group_id", '', [
                                            'id' => 'zTreeId',
                                        ]) ?>
                                        <div><?= Yii::t('app', 'organization help5') ?><span class="text-primary"
                                                                                             id="zTreeSelect"></span>
                                        </div>
                                        <div id="zTreeAddUser" class="ztree"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-2">
                            <?php yii::$app->request->url = strstr(yii::$app->request->url, '?type=') ? yii::$app->request->url : yii::$app->request->url . '?type=methods';//如果url没有type参数要加上?>
                            <a type="button" class="btn btn-default btn-sm"
                               href="<?= Yii::$app->urlManager->createUrl(array_merge(['report/financial/list'], $params, ['action' => 'excel', 'type' => $model->type])); ?>"><span
                                    class="glyphicon glyphicon-log-out"></span>excel</a>
                            <?php if ((isset($params['type']) && $params['type'] == 'methods') || !isset($params['type'])): ?>
                                <button type="button" class="btn btn-default btn-sm" onclick="financePrint();">
                                    <a href="javascript:;">
                                        <span class="glyphicon glyphicon-print"></span> <?= Yii::t('app', 'print') ?>
                                    </a>
                                </button>
                            <?php endif ?>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12" style="text-align: left;color: #ffffff;">
                    <?= $form->errorSummary($model); ?>
                </div>

                <?php $form->end(); ?>
            </div>
            <div class="col-md-12">
                <section class="panel panel-default table-dynamic">
                    <?php if (!empty($mgrs)) : ?>
                        <!--startprint-->
                        <div style="font-size: 16px;text-align: center;display: none"
                             class="print_show"><?= Yii::t('app', 'report print message1') ?></div>
                        <?php if (!empty($model->start_time)): ?>
                            <div style="font-size: 16px;text-align: center;display: none"
                                 class="print_show"><?= Yii::t('app', 'start time').':'.$model->start_time .','
                                .Yii::t('app', 'end time').':'. $model->stop_time?></div>
                        <?php endif; ?>
                        <table class="table table-hover" id="finPriTable">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th><?= Yii::t('app', 'toll taker') ?></th>
                                <th><?= Yii::t('app', 'pay count') ?></th>
                                <th><?= Yii::t('app', 'money summary') ?></th>
                                <th><?= Yii::t('app', 'total revenue') ?></th>
                                <?php if ($model->type == 'type'): ?>
                                    <?php foreach ($model->pay_type as $v) : ?>
                                        <th><?= $v ?></th>
                                    <?php endforeach ?>
                                <?php elseif ($model->type == 'methods'): ?>
                                    <?php foreach ($pay_methods as $v) : ?>
                                        <th><?= $v ?></th>
                                    <?php endforeach ?>
                                <?php endif ?>
                                <th><?= Yii::t('app', 'refund amount') ?></th>
                                <th class="print_hidden"><?= Yii::t('app', 'operate') ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $i = 0;
                            foreach ($mgrs as $mgr_name) { ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><?= Html::encode($mgr_name) ?></td>
                                    <td><?= Html::encode(isset($list[$mgr_name]) ? $list[$mgr_name]['count'] : 0) ?></td>
                                    <td><?= Html::encode(isset($list[$mgr_name]) ? sprintf("%.2f", $list[$mgr_name]['money_summary']) : 0.00) ?></td>
                                    <td><?= Html::encode(isset($list[$mgr_name]) ? sprintf("%.2f", $list[$mgr_name]['pay_num']) : 0.00) ?></td>

                                    <?php foreach ($pay_methods as $id => $v) : ?>
                                        <td><?= isset($list[$mgr_name][$id]) ? sprintf("%.2f", $list[$mgr_name][$id]) : 0.00 ?></td>
                                    <?php endforeach ?>
                                    <td><?= Html::encode(isset($list[$mgr_name]) ? sprintf("%.2f", $list[$mgr_name]['refund_num']) : 0.00) ?></td>
                                    <td class="print_hidden">
                                        <?php if ($canPayList) {
                                            echo Html::a(Html::button(Yii::t('app', 'toll detail'), ['class' => 'btn btn-success btn-xs']), [
                                                '/financial/pay/list',
                                                'mgr_name' => $mgr_name,
                                                'start_add_time' => $model->start_time,
                                                'end_add_time' => $model->stop_time
                                            ], ['title' => Yii::t('app', 'toll detail'), 'target' => '_blank']);
                                        } ?>
                                        <?php if ($canRefundList) {
                                            echo Html::a(Html::button(Yii::t('app', 'refund detail'), ['class' => 'btn btn-info btn-xs']), [
                                                '/financial/refund/list',
                                                'mgr_name' => $mgr_name,
                                                'type' => '0',
                                                'start_add_time' => $model->start_time,
                                                'end_add_time' => $model->stop_time
                                            ], ['title' => Yii::t('app', 'refund detail'), 'target' => '_blank']);
                                        } ?>
                                    </td>
                                </tr>
                                <?php $i++;
                            } ?>
                            </tbody>
                        </table>
                        <div style="font-size: 16px;text-align: center;display: none"
                              class="print_show" style="color:red;text-align: center;">
                            <?= Yii::t('app', 'money summary1', [
                                'totalMoney' => sprintf("%.2f", $totalMoney),
                            ]) ?>&nbsp;&nbsp;
                            <?= Yii::t('app', 'total revenue1', [
                                'totalMoney' => sprintf("%.2f", $payNum),
                            ]) ?>&nbsp;&nbsp;
                            <?= Yii::t('app', 'refund total', [
                                'totalMoney' => sprintf("%.2f", $refundMoney),
                            ]) ?></div>


                        <!--endprint-->

                        <footer class="table-footer">
                            <div class="row">
                                <div class="col-md-3">
                            <span class="print_hidden">
                                <?= Yii::t('app', 'pagination show1', [
                                    'totalCount' => $pagination->totalCount,
                                    'totalPage' => $pagination->getPageCount(),
                                    'perPage' => $pagination->pageSize,
                                ]) ?>
                            </span>
                                </div>
                                <div class="col-md-4" style="color:red;text-align: center;">
                                    <?= Yii::t('app', 'money summary1', [
                                        'totalMoney' => sprintf("%.2f", $totalMoney),
                                    ]) ?>&nbsp;&nbsp;
                                    <?= Yii::t('app', 'total revenue1', [
                                        'totalMoney' => sprintf("%.2f", $payNum),
                                    ]) ?>&nbsp;&nbsp;
                                    <?= Yii::t('app', 'refund total', [
                                        'totalMoney' => sprintf("%.2f", $refundMoney),
                                    ]) ?>

                                </div>
                                <div class="col-md-5 text-right print_hidden">
                                    <?php
                                    echo LinkPager::widget(['pagination' => $pagination]);
                                    ?>
                                </div>

                            </div>
                        </footer>
                    <?php else: ?>
                        <div class="panel-body">
                            <?= Yii::t('app', 'no record') ?>
                        </div>
                    <?php endif ?>
                </section>

            </div>
        </div>
    </div>
<?php
$this->registerJs("
    createTree('zTreeAddUser');
");
//声明ztree当前选中的id
$this->registerJs("
    var currentZTreeId = '" . $params['group_id'] . "';
", yii\web\View::POS_BEGIN);

?>