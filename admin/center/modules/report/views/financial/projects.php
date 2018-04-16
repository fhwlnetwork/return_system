<?php
/**
 * Created by PhpStorm.
 * User: qk
 * Date: 2016/12/14
 * Time: 11:23
 */
use yii\helpers\Html;
use yii\widgets\LinkPager;
use center\widgets\Alert;

/**
 * @var yii\web\View $this
 * @var $userArray
 */
$this->title = \Yii::t('app', 'report/financial/methods');
$canTypes = Yii::$app->user->can('report/financial/projects');
echo $this->render('/layouts/financial-menu');
//权限
$canPayList = Yii::$app->user->can('financial/pay/list');
?>
<div class="page page-table">
    <?= Alert::widget() ?>
    <div class="panel panel-default">

        <div class="panel-body">
            <ul class="nav nav-tabs" role="tablist" id="myTab">
                <li>
                    <?= Html::a(Yii::t('app', 'statistics by pay methods'), ['methods'], ['target'=>'_blank']) ?>
                </li>
                <li class="active">
                <?= Html::a(Yii::t('app', 'statistics by pay type'), ['projects'], ['target'=>'_blank']) ?>
                </li>
            </ul>
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane active">
                    <div class="page">
                        <?= Alert::widget() ?>
                        <div class="row">
                            <div class="panel-body">
                                <form name="form_constraints" action="<?= \yii\helpers\Url::to(['projects']) ?>"
                                      class="form-horizontal form-validation" method="get">
                                    <div class="form-group">
                                        <div class="col-md-2">
                                            <input type="text" class="form-control inputDate" id="statis_start_time"
                                                   name="start_date"
                                                   value="<?= isset($params['start_date']) ? $params['start_date'] : '' ?>"
                                                   placeholder="<?= yii::t('app', 'start time') ?>">
                                        </div>
                                        <div class="col-md-2">
                                            <input type="text" class="form-control inputDate" id="statis_end_time" name="end_date"
                                                   value="<?= isset($params['end_date']) ? $params['end_date'] : '' ?>"
                                                   placeholder="<?= yii::t('app', 'end time') ?>">
                                        </div>
                                        <div class="col-md-4">
                                            <?= html::button(yii::t('app', 'this day'), ['class' => 'btn btn-warning', 'onclick' => 'get_date(1)']) ?>
                                            <?= html::button(yii::t('app', 'this week'), ['class' => 'btn btn-primary', 'onclick' => 'get_date(2)']) ?>
                                            <?= html::button(yii::t('app', 'this month'), ['class' => 'btn btn-success', 'onclick' => 'get_date(3)']) ?>
                                            <?= html::button(yii::t('app', 'this quarter'), ['class' => 'btn btn-info', 'onclick' => 'get_date(4)']) ?>
                                            <?= html::button(yii::t('app', 'this year'), ['class' => 'btn btn-danger', 'onclick' => 'get_date(5)']) ?>
                                        </div>
                                        <span class="input-group-btn">
                                            <button type="submit" class="btn btn-success"><?= yii::t('app', 'search') ?></button>
                                        </span>
                                        <span class="input-group-btn">
                                            <?php yii::$app->request->url = strstr(yii::$app->request->url, '?') ? yii::$app->request->url : yii::$app->request->url . '?action=excel';//如果url没有type参数要加上
                                            ?>
                                            <button type="button" class="btn btn-default btn-sm"
                                                    onclick="finReportExcel('<?= strstr(yii::$app->request->url, '?') ? yii::$app->request->url : yii::$app->request->url . '?action=excel'//如果url没有action参数要加上     ?>');">
                                                <a href="javascript:;">
                                                    <span class="glyphicon glyphicon-log-out"></span> excel
                                                </a>
                                            </button>
                                            <button type="button" class="btn btn-default btn-sm" onclick="financePrint();">
                                                <a href="javascript:;">
                                                    <span class="glyphicon glyphicon-print"></span> <?= Yii::t('app', 'print') ?>
                                                </a>
                                            </button>
                                        </span>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <section class="panel panel-default table-dynamic">
                        <?php if (!empty($products) || !empty($packages) || !empty($extras) || !empty($balance)) : ?>
                            <!--startprint-->
                            <div style="font-size: 16px;text-align: center;display: none"
                                 class="print_show"><?= Yii::t('app', 'report print message1') ?></div>
                            <table class="table table-hover" id="finPriTable">
                                <thead>
                                <tr>
                                    <th><?= Yii::t('app', 'item')?></th>
                                    <th><?= Yii::t('app', 'Income amount').'('.Yii::t('app', 'currency').')' ?></th>
                                    <th><?= Yii::t('app', 'Refund amount').'('.Yii::t('app', 'currency').')' ?></th>
                                    <th><?= Yii::t('app', 'Transfer amount').'('.Yii::t('app', 'currency').')' ?></th>
                                    <th><?= Yii::t('app', 'Balance amount').'('.Yii::t('app', 'currency').')' ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr class="success">
                                    <td colspan="5">
                                        <?= Yii::t('app', 'product')?>
                                        <span class="glyphicon glyphicon-plus" style="cursor:pointer"  ng-click="product_show = !product_show" ng-show="!product_show"></span>
                                        <span class="glyphicon glyphicon-minus" style="cursor:pointer"  ng-click="product_show = !product_show" ng-show="product_show"></span>
                                    </td>
                                </tr>
                                <?php
                                    $products_pay_total = $products_refund_total = $products_out_total = 0;
                                    foreach ($products as $k => $one) {
                                        $products_pay_total += $one['pay_num'];
                                        $products_refund_total += $one['refund_num'];
                                        $products_out_total += $one['out_num'];
                                ?>
                                    <tr ng-show="product_show">
                                        <td><?= Html::encode(isset($productNameList[$k])?$productNameList[$k]:'ID:'.$k) ?></td>
                                        <td><?= Html::encode($one['pay_num']) ?></td>
                                        <td><?= Html::encode($one['refund_num']) ?></td>
                                        <td><?= Html::encode($one['out_num']) ?></td>
                                        <td><?= Html::encode($one['pay_num']-$one['refund_num']-$one['out_num']) ?></td>
                                    </tr>
                                <?php } ?>
                                <!--产品总计-->
                                <tr class="danger">
                                    <td><?= Yii::t('app', 'total money')?></td>
                                    <td><?=$products_pay_total?></td>
                                    <td><?=$products_refund_total?></td>
                                    <td><?=$products_out_total?></td>
                                    <td><?=$products_pay_total-$products_refund_total-$products_out_total?></td>
                                </tr>
                                <tr class="success">
                                    <td colspan="5">
                                        <?= Yii::t('app', 'package')?>
                                        <span class="glyphicon glyphicon-plus" style="cursor:pointer"  ng-click="package_show = !package_show" ng-show="!package_show"></span>
                                        <span class="glyphicon glyphicon-minus" style="cursor:pointer"  ng-click="package_show = !package_show" ng-show="package_show"></span>
                                    </td>
                                </tr>
                                <?php
                                $packages_pay_total = $packages_refund_total = $packages_out_total = 0;
                                foreach ($packages as $k => $one) {
                                    $packages_pay_total += $one['pay_num'];
                                    $packages_refund_total += $one['refund_num'];
                                    $packages_out_total += $one['out_num'];
                                ?>
                                    <tr ng-show="package_show">
                                        <td><?= Html::encode(isset($packageNameList[$k])?$packageNameList[$k]:'ID:'.$k) ?></td>
                                        <td><?= Html::encode($one['pay_num']) ?></td>
                                        <td><?= Html::encode($one['refund_num']) ?></td>
                                        <td><?= Html::encode($one['out_num']) ?></td>
                                        <td><?= Html::encode($one['pay_num']-$one['refund_num']-$one['out_num']) ?></td>
                                    </tr>
                                <?php } ?>
                                <!--套餐总计-->
                                <tr class="danger">
                                    <td><?= Yii::t('app', 'total money')?></td>
                                    <td><?=$packages_pay_total?></td>
                                    <td><?=$packages_refund_total?></td>
                                    <td><?=$packages_out_total?></td>
                                    <td><?=$packages_pay_total-$packages_refund_total-$packages_out_total?></td>
                                </tr>
                                <tr class="success">
                                    <td colspan="5">
                                        <?= Yii::t('app', 'Financial ExtraPay')?>
                                        <span class="glyphicon glyphicon-plus" style="cursor:pointer"  ng-click="extra_show = !extra_show" ng-show="!extra_show"></span>
                                        <span class="glyphicon glyphicon-minus" style="cursor:pointer"  ng-click="extra_show = !extra_show" ng-show="extra_show"></span>
                                    </td>
                                </tr>
                                <?php
                                $extras_pay_total = $extras_refund_total = $extras_out_total = 0;
                                foreach ($extras as $k => $one) {
                                    $extras_pay_total += $one['pay_num'];
                                    $extras_refund_total += $one['refund_num'];
                                    $extras_out_total += $one['out_num'];
                                ?>
                                    <tr ng-show="extra_show">
                                        <td><?= Html::encode(isset($extra_payments[$k])?$extra_payments[$k]:'ID:'.$k) ?></td>
                                        <td><?= Html::encode($one['pay_num']) ?></td>
                                        <td><?= Html::encode($one['refund_num']) ?></td>
                                        <td><?= Html::encode($one['out_num']) ?></td>
                                        <td><?= Html::encode($one['pay_num']-$one['refund_num']-$one['out_num']) ?></td>
                                    </tr>
                                <?php } ?>
                                <!--附加费用总计-->
                                <tr class="danger">
                                    <td><?= Yii::t('app', 'total money')?></td>
                                    <td><?=$extras_pay_total?></td>
                                    <td><?=$extras_refund_total?></td>
                                    <td><?=$extras_out_total?></td>
                                    <td><?=$extras_pay_total-$extras_refund_total-$extras_out_total?></td>
                                </tr>
                                <tr class="success"><td colspan="5"><?= Yii::t('app', 'electronic purse')?></td></tr>
                                <tr class="danger">
                                    <td><?= Yii::t('app', 'total money')?></td>
                                    <td><?= Html::encode($balance['pay_num']) ?></td>
                                    <td><?= Html::encode($balance['refund_num']) ?></td>
                                    <td><?= Html::encode($balance['out_num']) ?></td>
                                    <td><?= Html::encode($balance['pay_num']-$balance['refund_num']-$balance['out_num']) ?></td>
                                </tr>
                                </tbody>
                            </table>
                            <!--endprint-->
                        <?php else: ?>
                            <div class="panel-body">
                                <?= Yii::t('app', 'no record') ?>
                            </div>
                        <?php endif ?>
                    </section>
                </div>
            </div>
        </div>
    </div>
</div>