<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2017/6/1
 * Time: 16:40
 */
use center\widgets\Alert;
use yii\helpers\Html;
use center\extend\Tool;

\center\assets\ReportAsset::newEchartsJs($this);
$this->title = Yii::t('app', 'report/detail/user');
echo $this->render('/layouts/accountant-menu');
?>
<div class="page page-table">
    <?= Alert::widget() ?>
    <section class="panel panel-default table-dynamic">
        <div class="panel-heading"><strong><span
                    class="glyphicon glyphicon-th"></span> <?= $this->title ?> </strong></div>
        <div class="panel-body">
            <form class="form-validation form-horizontal ng-pristine ng-valid" name="searchForm"
                  action="<?= \yii\helpers\Url::to(['user']) ?>" method="get" role="form">
                <div class="col-lg-2"
                     ng-init="user_name='<?= !is_null($params['user_name']) ? $params['user_name'] : '' ?>'">
                    <div class="form-group required">
                        <input type="text" class="form-control" name="user_name" required
                               data-ng-model="user_name"
                               value="<?= !is_null($params['user_name']) ? $params['user_name'] : '' ?>'"
                               placeholder="<?= Yii::t('app', 'account') ?>">
                    </div>
                </div>
                <div class="col-lg-8">
                    <?=
                    \yii\helpers\Html::submitButton(Yii::t('app', 'search'), [
                        'class' => 'btn btn-success',
                        'data-ng-disabled' => 'searchForm.$invalid'
                    ]) ?>
                </div>
            </form>
        </div>
    </section>
</div>
<div class="col-md-12">
    <div class="panel panel-success">
        <div class="panel-heading"><span
                class="glyphicon glyphicon-th-large"></span> <?= Yii::t('app', 'user checkout detail') ?>
        </div>
    </div>

    <div class="panel panel-default" data-ng-controller="userRechargeCtrl">
        <div class="panel-body">
            <ul class="nav nav-tabs" id="tab" data-ng-init="logType='operate'">
                <li class="active"><a href="#"
                                      data-ng-click="logType='operate'"><?= Yii::t('app', 'checkout prev') ?></a></li>
                <li><a href="#" data-ng-click="logType='detail'"><?= Yii::t('app', 'checkout prev prev') ?></a>
                </li>
                <li><a href="#" data-ng-click="logType='history'"><?= Yii::t('app', 'history checkout') ?></a></li>
            </ul>
            <div class="tab-content" data-ng-init="user_name='<?= $params['user_name'] ?>'">
                <!--操作日志-->
                <div class="panel panel-success" data-ng-show="logType=='operate'">
                    <div class="panel panel-success panel-heading"><span
                            class="glyphicon glyphicon-th-large"></span> <?= Yii::t('app', 'checkout prev total') ?>
                    </div>
                    <?php if (!empty($data['user'])): ?>
                    <div class="page">
                        <table class="table table-bordered table-striped table-responsive">
                            <tbody>
                            <tr>
                                <td><div class='th'><?= Yii::t('app', 'account') ?></div></td>
                                <td><div class='th'><?= Yii::t('app', 'name') ?></div></td>
                                <td><div class='th'><?= Yii::t('app', 'group_id') ?></div></td>
                                <td><?= Yii::t('app', 'user available') ?></td>
                                <td><?= Yii::t('app', 'total fee') ?></td>
                                <td><?= Yii::t('app', 'refund fee') ?></td>
                                <td><?= Yii::t('app', 'pay fee') ?></td>
                                <td><?= Yii::t('app', 'transfer from fee') ?></td>
                                <td><?= Yii::t('app', 'transfer to fee') ?></td>
                            </tr>
                            <tr>
                                <td><?= $data['user']['user_name'] ?></td>
                                <td><?= $data['user']['user_real_name'] ?></td>
                                <td><?= \center\modules\auth\models\SrunJiegou::getOwnParent($data['user']['group_id']) ?></td>
                                <td><?= $data['user']['user_available'] ?></td>
                                <td><?= Html::encode($data['detail']['all']['checkout_num']) ?></td>
                                <td><?= $data['detail']['all']['refund_num'] ?></td>
                                <td><?= $data['detail']['all']['pay_num'] ?></td>
                                <td><?= $data['detail']['all']['transfer_num_from'] ?></td>
                                <td><?= $data['detail']['all']['transfer_num_to'] ?></td>
                            </tr>
                            </tbody>
                        </table>
                        <table class="table table-bordered table-striped table-responsive">
                            <thead>
                            <tr>
                                <td><?= Yii::t('app', 'Settlement cycle') ?></td>
                                <td><?= Yii::t('app', 'product id') ?></td>
                                <td><?= Yii::t('app', 'product name') ?></td>
                                <td><?= Yii::t('app', 'total fee') ?></td>
                                <td><?= Yii::t('app', 'fee detail') ?></td>
                                <td><?= Yii::t('app', 'pay fee detail') ?></td>
                            </tr>
                            <div class="panel panel-success">
                                <div class="panel-heading"><span
                                        class="glyphicon glyphicon-th-large"></span>
                                    <?= Yii::t('app', 'product checkout date') ?>
                                </div>
                            </div>
                            <?php if (!empty($data['detail'])) : ?>
                                <?php foreach ($data['detail'] as $id => $val):
                                    if (!is_numeric($id) || $id == 0) {
                                        continue;
                                    }
                                     if ($val['code'] == 200):
                                         //var_dump($val);exit;
                                    ?>

                                    <tr>
                                        <td> <?= $val['dates']['sta'] . '-' . $val['dates']['end'] ?></td>
                                        <td><?= $id ?></td>
                                        <td><?= $val['name'] ?></td>
                                        <td><?= $val['checkout_num'] ?></td>
                                        <td>
                                            <?= Yii::t('app', 'product') ?>: <?= sprintf('%.2f', $val['pay_num']) ?>
                                            <br/>
                                            <?php if (!empty($val['package'])) : ?>
                                                <?= Yii::t('app', 'package fee') ?>:  <?= $val['package']['checkout_num']; ?>
                                                <button data-ng-model="changeNow"
                                                        data-ng-click="changeNow = !changeNow" type="button"
                                                        class="btn btn-w-md btn-gap-v btn-info">
                                                    <?= Yii::t('app', 'package fee detail') ?>
                                                    <i class="fa fa-chevron-down" ng-show="!changeNow"></i>
                                                    <i class="fa fa-chevron-up ng-hide" ng-show="changeNow"></i>
                                                </button>
                                                <div data-ng-show="changeNow">
                                                    <?php foreach ($val['package'] as $package_id => $num):
                                                        if ($package_id == 'checkout_num') {
                                                            continue;
                                                        }
                                                        ?>
                                                        <?= Yii::t('app', 'package id') ?>: <?= $id ?>;
                                                        <?= Yii::t('app', 'package name') ?>: <?= $num['name'] ?>;
                                                        <?= Yii::t('app', 'checkout amount') ?>: <?= $num['checkout_num'] ?>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>

                                        </td>
                                        <td>
                                            <?= Yii::t('app', 'saving_products') ?>
                                            : <?= sprintf('%.2f', $val['pay_num']) ?><br/>
                                            <?php if (!empty($val['package'])) : ?>
                                                <?= Yii::t('app', 'package pay fee') ?>:  <?= $val['package']['pay_num']; ?>
                                                <button data-ng-model="changeNow"
                                                        data-ng-click="changeNowPay = !changeNowPay" type="button"
                                                        class="btn btn-w-md btn-gap-v btn-info">
                                                    <?= Yii::t('app', 'package pay detail') ?>
                                                    <i class="fa fa-chevron-down" ng-show="!changeNowPay"></i>
                                                    <i class="fa fa-chevron-up ng-hide" ng-show="changeNowPay"></i>
                                                </button>
                                                <div data-ng-show="changeNowPay">
                                                    <?php foreach ($val['package'] as $package_id => $num):
                                                        if ($package_id == 'checkout_num') {
                                                            continue;
                                                        }
                                                        ?>
                                                        <?= Yii::t('app', 'package id') ?>: <?= $id ?>;
                                                        <?= Yii::t('app', 'package name') ?>: <?= $num['name'] ?>;
                                                        <?= Yii::t('app', 'payment') ?>: <?= $num['pay_num'] ?>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                         <?php else: ?>
                                         <tr>
                                             <td colspan="7"><?= $val['msg']?></td>
                                         </tr>
                                         <?php endif;?>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            </thead>
                        </table>
                    </div>
                    <div class="panel panel-success">
                        <div class="panel-heading"><span
                                class="glyphicon glyphicon-th-large"></span> <?= Yii::t('app', 'checkout detail') ?>
                        </div>
                    <?php if (!empty($data['detail']['checkout']['detail'])): ?>
                            <table class="table table-bordered table-striped table-responsive">
                                <thead>
                                <tr>
                                    <th><div class='th'><?= Yii::t('app', 'account') ?></div></th>
                                    <th><div class='th'><?= Yii::t('app', 'name') ?></div></th>
                                    <th><div class='th'><?= Yii::t('app', 'checkout amount') ?></div></th>
                                    <th><div class='th'><?= Yii::t('app', 'product') ?></div></th>
                                    <th><div class='th'><?= Yii::t('app', 'package') ?></div></th>
                                    <th><div class='th'><?= Yii::t('app', 'flux') ?></div></th>
                                    <th><div class='th'><?= Yii::t('app', 'time lenth') ?></div></th>
                                    <th><div class='th'><?= Yii::t('app', 'checkout time') ?></div></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($data['detail']['checkout']['detail'] as $one): ?>
                                    <tr>
                                        <td><?= $data['user']['user_name'] ?></td>
                                        <td><?= $data['user']['user_real_name'] ?></td>
                                        <td><?= Html::encode(sprintf('%.2f', $one['spend_num'] + $one['rt_spend_num'])); ?></td>
                                        <td><?= Html::encode(isset($product[$one['product_id']]) ? $product[$one['product_id']] : $one['product_id']); ?></td>
                                        <td><?= ($model->getPackageName($one['buy_id'])) ?></td>
                                        <td><?= Html::encode(Tool::bytes_format($one['flux'])); ?></td>
                                        <td><?= Html::encode(Tool::seconds_format($one['minutes'])); ?></td>
                                        <td><?= Html::encode(date('Y-m-d H:i', $one['create_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                    <?php else :?>
                        <div style="padding: 15px 0;">
                            <?= Yii::t('app', 'user base help10');?>
                        </div>
                    <?php endif; ?>
                    </div>
                    <div class="panel panel-success">
                        <div class="panel-heading"><span
                                class="glyphicon glyphicon-th-large"></span> <?= Yii::t('app', 'pay detail') ?>
                        </div>
                    <?php if (!empty($data['detail']['pay']['detail'])): ?>
                            <table class="table table-bordered table-striped table-responsive">
                                <thead>
                                <tr>
                                    <th><div class='th'><?= Yii::t('app', 'account') ?></div></th>
                                    <th><div class='th'><?= Yii::t('app', 'name') ?></div></th>
                                    <th><div class='th'><?= Yii::t('app', 'batch excel pay num') ?></div></th>
                                    <th><div class='th'><?= Yii::t('app', 'product') ?></div></th>
                                    <th><div class='th'><?= Yii::t('app', 'package') ?></div></th>
                                    <th><div class='th'><?= Yii::t('app', 'operator') ?></div></th>
                                    <th><div class='th'><?= Yii::t('app', 'report operate remind6') ?></div></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($data['detail']['pay']['detail'] as $one): ?>
                                    <tr>
                                        <td><?= $data['user']['user_name'] ?></td>
                                        <td><?= $data['user']['user_real_name'] ?></td>
                                        <td><?= Html::encode(sprintf('%.2f', $one['pay_num'] + $one['rt_spend_num'])); ?></td>
                                        <td><?= Html::encode(isset($product[$one['product_id']]) ? $product[$one['product_id']] : $one['product_id']); ?></td>
                                        <td><?= Html::encode($model->getPackageName($one['package_id'])); ?></td>
                                        <td><?= Html::encode($one['operator']); ?></td>
                                        <td><?= Html::encode(date('Y-m-d H:i', $one['create_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                    <?php else :?>
                        <div style="padding: 15px 0;">
                            <?= Yii::t('app', 'user base help10');?>
                        </div>
                    <?php endif; ?>
                    </div>
                    <div class="panel panel-success">
                        <div class="panel-heading"><span
                                class="glyphicon glyphicon-th-large"></span> <?= Yii::t('app', 'report help23') ?>
                        </div>
                        <?php if (!empty($data['detail']['refund']['detail'])): ?>
                            <table class="table table-bordered table-striped table-responsive">
                                <thead>
                                <tr>
                                    <th><div class='th'><?= Yii::t('app', 'account') ?></div></th>
                                    <th><div class='th'><?= Yii::t('app', 'refund amount') ?></div></th>
                                    <th><div class='th'><?= Yii::t('app', 'refund time') ?></div></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($data['detail']['refund']['detail'] as $one): ?>
                                    <tr>
                                        <td><?= $one['user_name'] ?></td>
                                        <td><?= Html::encode(sprintf('%.2f', $one['refund_num'])); ?></td>
                                        <td><?= Html::encode(date('Y-m-d H:i', $one['create_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>

                        <?php else :?>
                            <div style="padding: 15px 0;">
                                <?= Yii::t('app', 'user base help10');?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="panel panel-success">
                        <div class="panel-heading"><span
                                class="glyphicon glyphicon-th-large"></span> <?= Yii::t('app', 'report help21') ?>
                        </div>
                    <?php if (!empty($data['detail']['transfer_from']['detail'])): ?>
                            <table class="table table-bordered table-striped table-responsive">
                                <thead>
                                <tr>
                                    <th><div class='th'><?= Yii::t('app', 'account') ?></div></th>
                                    <th><div class='th'><?= Yii::t('app', 'transfer num') ?></div></th>
                                    <th><div class='th'><?= Yii::t('app', 'operator') ?></div></th>
                                    <th><div class='th'><?= Yii::t('app', 'transfer time') ?></div></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($data['detail']['transfer_from']['detail'] as $one): ?>
                                    <tr>
                                        <td><?= $one['user_name_from'] ?></td>
                                        <td><?= Html::encode(sprintf('%.2f', $one['transfer_num'])); ?></td>
                                        <td><?= Html::encode($one['mgr_name']); ?></td>
                                        <td><?= Html::encode(date('Y-m-d H:i', $one['create_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>

                    <?php else :?>
                        <div style="padding: 15px 0;">
                            <?= Yii::t('app', 'user base help10');?>
                        </div>
                    <?php endif; ?>
                    </div>

                    <div class="panel panel-success">
                        <div class="panel-heading"><span
                                class="glyphicon glyphicon-th-large"></span> <?= Yii::t('app', 'report help22') ?>
                        </div>
                        <?php if (!empty($data['detail']['transfer_to']['detail'])): ?>
                            <table class="table table-bordered table-striped table-responsive">
                                <thead>
                                <tr>
                                    <th><div class='th'><?= Yii::t('app', 'account') ?></div></th>
                                    <th><div class='th'><?= Yii::t('app', 'transfer num') ?></div></th>
                                    <th><div class='th'><?= Yii::t('app', 'operator') ?></div></th>
                                    <th><div class='th'><?= Yii::t('app', 'transfer time') ?></div></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($data['detail']['transfer_to']['detail'] as $one): ?>
                                    <tr>
                                        <td><?= $one['user_name_from'] ?></td>
                                        <td><?= Html::encode(sprintf('%.2f', $one['transfer_num'])); ?></td>
                                        <td><?= Html::encode($one['mgr_name']); ?></td>
                                        <td><?= Html::encode(date('Y-m-d H:i', $one['create_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>

                        <?php else :?>
                            <div style="padding: 15px 0;">
                                <?= Yii::t('app', 'user base help10');?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div id="log_cdra" data-ng-show="logType=='detail'">
                    <div class="panel panel-success">
                        <div class="panel panel-success panel-heading"><span
                                class="glyphicon glyphicon-th-large"></span> <?= Yii::t('app', 'report help17') ?>
                        </div>

                    </div>
                    <table class="table table-bordered table-striped table-responsive">
                        <thead>
                        <tr>
                            <th data-ng-repeat="key in userHead">{{key}}</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td data-ng-repeat="item in showUserBody">{{item}}</td>
                        </tr>
                        </tbody>
                    </table>

                    <div class="panel panel-success">
                        <div class="panel panel-success panel-heading"><span
                                class="glyphicon glyphicon-th-large"></span> <?= Yii::t('app', 'report help18') ?>
                        </div>

                    </div>
                    <table class="table table-bordered table-striped table-responsive">
                        <thead>
                        <tr>
                            <th data-ng-repeat="key in productHead">{{key}}</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr data-ng-repeat="item in showProductBody  track by $index">
                            <td ng-repeat="value in item  track by $index"><pre>{{value}}</pre></td>
                        </tr>
                        </tbody>
                    </table>
                    <div class="panel panel-success">
                        <div class="panel panel-success panel-heading"><span
                                class="glyphicon glyphicon-th-large"></span> <?= Yii::t('app', 'report help19') ?>
                        </div>

                    </div>
                    <table class="table table-bordered table-striped table-responsive" data-ng-show="showPayBody.length>0">
                        <thead>
                        <tr>
                            <th data-ng-repeat="key in payHead">{{key}}</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr data-ng-repeat="item in showPayBody  track by $index">
                            <td ng-repeat="value in item  track by $index">{{value}}</td>
                        </tr>
                        </tbody>
                    </table>
                    <div style="padding: 15px 0;" data-ng-show="showPayBody.length==0">
                                    <?= Yii::t('app', 'user base help10');?>
                    </div>
                    <div class="panel panel-success">
                        <div class="panel panel-success panel-heading"><span
                                class="glyphicon glyphicon-th-large"></span> <?= Yii::t('app', 'report help20') ?>
                        </div>

                    </div>
                    <table class="table table-bordered table-striped table-responsive" data-ng-show="showCheckoutBody.length>0">
                        <thead>
                        <tr>
                            <th data-ng-repeat="key in checkoutHead">{{key}}</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr data-ng-repeat="item in showCheckoutBody  track by $index">
                            <td ng-repeat="value in item  track by $index">{{value}}</td>
                        </tr>
                        </tbody>
                    </table>
                     <div style="padding: 15px 0;" data-ng-show="showCheckoutBody.length==0">
                                    <?= Yii::t('app', 'user base help10');?>
                    </div>
                    <div class="panel panel-success">
                        <div class="panel panel-success panel-heading"><span
                                class="glyphicon glyphicon-th-large"></span> <?= Yii::t('app', 'report help23') ?>
                        </div>

                    </div>
                    <table class="table table-bordered table-striped table-responsive" data-ng-show="showRefundBody.length>0">
                        <thead>
                        <tr>
                            <th data-ng-repeat="key in refundHead">{{key}}</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr data-ng-repeat="item in showRefundBody  track by $index">
                            <td ng-repeat="value in item  track by $index">{{value}}</td>
                        </tr>
                        </tbody>
                    </table>
                    <div style="padding: 15px 0;" data-ng-show="showRefundBody.length==0">
                        <?= Yii::t('app', 'user base help10');?>
                    </div>
                    <div class="panel panel-success">
                        <div class="panel panel-success panel-heading"><span
                                class="glyphicon glyphicon-th-large"></span> <?= Yii::t('app', 'report help21') ?>
                        </div>

                    </div>
                    <table class="table table-bordered table-striped table-responsive" data-ng-show="showTransferFromBody.length>0">
                        <thead>
                        <tr>
                            <th data-ng-repeat="key in transferHead">{{key}}</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr data-ng-repeat="item in showTransferFromBody  track by $index">
                            <td ng-repeat="value in item  track by $index">{{value}}</td>
                        </tr>
                        </tbody>
                    </table>
                    <div style="padding: 15px 0;" data-ng-show="showTransferFromBody.length==0">
                        <?= Yii::t('app', 'user base help10');?>
                    </div>
                    <div class="panel panel-success">
                        <div class="panel panel-success panel-heading"><span
                                class="glyphicon glyphicon-th-large"></span> <?= Yii::t('app', 'report help22') ?>
                        </div>

                    </div>
                    <table class="table table-bordered table-striped table-responsive" data-ng-show="showTransferToBody.length>0">
                        <thead>
                        <tr>
                            <th data-ng-repeat="key in transferHead">{{key}}</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr data-ng-repeat="item in showTransferToBody  track by $index">
                            <td ng-repeat="value in item  track by $index">{{value}}</td>
                        </tr>
                        </tbody>
                    </table>
                    <div style="padding: 15px 0;" data-ng-show="showTransferToBody.length==0">
                        <?= Yii::t('app', 'user base help10');?>
                    </div>
                </div>

                <div data-ng-show="logType == 'history'" style="background: #f1f1f1;">
                    <div class="col-md-6">
                        <div id ='recently_pay' style="height: 400px;" class="col-md-12"></div>
                    </div>
                    <div class="col-md-6">
                        <div id ='recently_checkout' style="height: 400px;"  class="col-md-12"></div>
                    </div>
                    <div class="col-md-12" style="height:20px;background: #f1f1f1;"></div>
                    <div class="col-md-6">
                        <div id ='transfer_from' style="height: 400px;" class="col-md-12"></div>
                    </div>
                    <div class="col-md-6">
                        <div id ='transfer_to' style="height: 400px;"  class="col-md-12"></div>
                    </div>
                    <div class="col-md-12" style="height:20px;background: #f1f1f1;"></div>
                    <div class="col-md-12" style="background: #f1f1f1;height: 400px;" id ='refund'></div>
                </div>

                <?php endif ?>
            </div>
        </div>
    </div>
</div>

<?php
$this->registerJs("
    $('#tab a').click(function (e) {
          e.preventDefault();//阻止a链接的跳转行为
          $(this).tab('show');//显示当前选中的链接及关联的content
    })
 ");
?>
