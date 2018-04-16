<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2016/11/7
 * Time: 15:23
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
                <li role="presentation" class="active">
                    <?= Html::a(Yii::t('app', 'statistics by pay methods')) ?>
                </li>
                <?php if($canTypes):?>
                <li>
                    <?= Html::a(Yii::t('app', 'statistics by pay type'), ['projects'], ['target'=>'_blank']) ?>
                    </li><?php endif ?>
            </ul>
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane active">
                    <div class="page">
                        <?= Alert::widget() ?>
                        <div class="row">
                            <div class="panel-body">
                                <form name="form_constraints" action="<?= \yii\helpers\Url::to(['methods']) ?>"
                                      class="form-horizontal form-validation" method="get">
                                    <div class="form-group">
                                        <div class="col-md-2">
                                            <input type="text" class="form-control inputDate" id="statis_start_time"
                                                   name="start_time"
                                                   value="<?= isset($params['start_time']) ? $params['start_time'] : '' ?>"
                                                   placeholder="<?= yii::t('app', 'start time') ?>">
                                        </div>
                                        <div class="col-md-2">
                                            <input type="text" class="form-control inputDate" id="statis_end_time" name="end_time"
                                                   value="<?= isset($params['end_time']) ? $params['end_time'] : '' ?>"
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
                        <?php if (!empty($list)) : ?>
                            <!--startprint-->
                            <div style="font-size: 16px;text-align: center;display: none"
                                 class="print_show"><?= Yii::t('app', 'report print message1') ?></div>
                            <table class="table table-hover" id="finPriTable">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th><?= Yii::t('app', 'pay methods') ?></th>
                                    <th><?= Yii::t('app', 'pay count') ?></th>
                                    <th><?= Yii::t('app', 'total amount') ?></th>
                                    <!-- <th><? /*= Yii::t('app', 'refund amount') */ ?></th>-->
                                    <th class="print_hidden"><?= Yii::t('app', 'operate') ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($list as $k => $one) { ?>
                                    <?php if (is_numeric($k)) : ?>
                                        <tr>
                                            <td><?= $k + 1 ?></td>
                                            <td><?= Html::encode($one['pay_type_name']) ?></td>
                                            <td><?= Html::encode($one['total_bytes']) ?></td>
                                            <td><?= Html::encode(sprintf("%.2f", $one['pay_nums'])) ?></td>
                                            <!-- <td><? /*= Html::encode(sprintf("%.2f", $one['refund_num'])) */ ?></td>-->
                                            <td class="print_hidden">
                                                <?php if ($canPayList) {
                                                    echo Html::a(Html::button(Yii::t('app', 'toll detail'), ['class' => 'btn btn-success btn-xs']), [
                                                        '/financial/pay/list',
                                                        'pay_type_id' => $one['pay_type_id'],
                                                        'mgr_name' => $one['mgr_name'],
                                                        'start_add_time' => isset($params['start_time']) ? $params['start_time'] : '',
                                                        'end_add_time' => isset($params['end_time']) ? $params['end_time'] : ''
                                                    ], ['title' => Yii::t('app', 'toll detail'), 'target' => '_blank']);
                                                } ?>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                <?php } ?>
                                </tbody>
                            </table>
                            <!--endprint-->
                        <?php else: ?>
                            <div class="panel-body">
                                <?= Yii::t('app', 'no record') ?>
                            </div>
                        <?php endif ?>
                    </section>

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
                            <div class="col-md-3" style="color:red">
                                <?= Yii::t('app', 'Total', [
                                    'totalMoney' => sprintf("%.2f", $list['totalMoney']),
                                ]) ?>
                            </div>
                            <div class="col-md-6 text-right print_hidden">
                                <?php
                                echo LinkPager::widget(['pagination' => $pagination]);
                                ?>
                            </div>

                        </div>
                    </footer>
                </div>
            </div>
        </div>
    </div>
</div>