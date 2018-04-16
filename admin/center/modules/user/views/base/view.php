<?php
use yii\helpers\Html;
use center\widgets\Alert;
use center\extend\Tool;
use yii\helpers\ArrayHelper;

/**
 * @var yii\web\View $this
 * @var center\modules\user\Base $model
 */

$this->title = $model->user_name . ' ' . Yii::t('app', 'User Detail');
$attributes = $model->getAttributesList();//列表
$labels = $model->attributeLabels();//标签

$productNum = count($model->products_id);//用户的产品总数，用来判断是否可以取消产品

//权限
//缴费
$canPay = Yii::$app->user->can('financial/pay/index');
//订购产品及套餐
//$canOrderProduct = Yii::$app->user->can('financial/pay/_product');
//编辑用户
$canEdit = Yii::$app->user->can('user/base/edit');
//退费
$canRefund = Yii::$app->user->can('financial/refund/index');
//销户
$canDelete = Yii::$app->user->can('user/base/delete');
//禁用产品
$canDisable = Yii::$app->user->can('user/base/disable-product');
//操作
$canViewUserAvailable = Yii::$app->user->can('user/base/_ViewUserAvailable');//修改用户状态
$canViewExpire = Yii::$app->user->can('user/base/_ViewExpire');//修改过期时间
$canMaxOnlineNum = Yii::$app->user->can('user/base/_MaxOnlineNum');//最大连接数
$canMacAuthInfo = Yii::$app->user->can('user/base/_MacAuthInfo');//MAC认证
$canMacInfo = Yii::$app->user->can('user/base/_MacInfo');//MAC绑定
$canNasPortIDInfo = Yii::$app->user->can('user/base/_NasPortIDInfo');//NasPortID绑定
$canVlanIDInfo = Yii::$app->user->can('user/base/_VlanIDInfo');//VlanID绑定
$canIPV4Info = Yii::$app->user->can('user/base/_IPV4Info');//IPV4绑定
$canCDRInfo = Yii::$app->user->can('user/base/_CDRInfo');//CDR绑定号码
$canBind = Yii::$app->user->can('user/base/_Bind');//添加绑定
$canCDRBind = Yii::$app->user->can('user/base/_CDRBind');//添加CDR绑定
$canStopUser = Yii::$app->user->can('user/base/_StopUser');//停机保号
$canAction = Yii::$app->user->can('user/base/_Action');//动作
//转移产品到下个周期
$canProChangeNext = Yii::$app->user->can('user/base/prochangenext');
//立即转移
$canProChangeNow = Yii::$app->user->can('user/base/prochangenow');
//预约转移产品
$canProChangeAppoint = Yii::$app->user->can('user/base/prochangeappoint');
//取消产品
$canCancelProduct = Yii::$app->user->can('user/base/cancel-product');
//取消套餐
$canCancelPackage = Yii::$app->user->can('user/base/cancel-package');
//操作日志
$canOperateLog = Yii::$app->user->can('log/operate/index');
//认证日志
$canLoginLog = Yii::$app->user->can('log/login/index');
//上网明细
$canDetailLog = Yii::$app->user->can('log/detail/index');
//缴费清单
$canPayList = Yii::$app->user->can('financial/pay/list');
//转账清单
$canTransferList = Yii::$app->user->can('financial/transfer/list');
//结算清单
$canCheckoutList = Yii::$app->user->can('financial/checkout/list');
//产品转移记录
$canProchangeList = Yii::$app->user->can('log/prochange/index');
//在线信息
$canOnline = Yii::$app->user->can('user/online/index');
//在线列表
$canOnlist = Yii::$app->user->can('user/online/_radius');
//下线
$canOndown = Yii::$app->user->can('user/online/drop');

//转账
$canTransfer = Yii::$app->user->can('user/base/transfer');
// cdr
$canCdr = Yii::$app->user->can('log/c-d-r/get-ten-cdr');
//动态条件
$canDongtai = Yii::$app->user->can('strategy/condition/edit');
?>

<style>
    .form-control {
        display: block;
        width: 100%;
        height: 30px;
        padding: 6px 12px;
        font-size: 12px;
        line-height: 1.42857;
        color: #767676;
        background-color: white;
        background-image: none;
        border: 1px solid #cbd5dd;
        border-radius: 2px;
        -webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
        box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
        -webkit-transition: border-color ease-in-out 0.15s, box-shadow ease-in-out 0.15s;
        -o-transition: border-color ease-in-out 0.15s, box-shadow ease-in-out 0.15s;
        transition: border-color ease-in-out 0.15s, box-shadow ease-in-out 0.15s;
    }
</style>

<div class="page page-profile" data-ng-controller="userProfileCtrl">
    <?= Alert::widget() ?>
    <div class="row">
        <!--左侧-->
        <div class="col-md-6">
            <!--用户基本信息-->
            <div class="panel panel-profile">
                <div class="panel-heading bg-primary clearfix" title="<?= Yii::t('app', 'refresh'); ?>"
                     style="cursor:pointer" onclick="location.reload();">
                    <!--<a href="" class="pull-left profile">
                        <img alt="" src="/images/g1.jpg" class="img-circle img80_80">
                    </a>-->
                    <h3><?= Html::encode($model->user_name) ?></h3>

                    <p><?= Html::encode($model->user_real_name) ?></p>
                </div>
                <ul class="list-group list-unstyled list-info">
                    <li>
                        <i class="icon fa fa-dollar"></i>&nbsp;
                        <label><?= Yii::t('app', 'account balance') ?></label><?= Html::encode($model->balance) ?>
                    </li>
                    <li>
                        <i class="icon fa fa-users"></i><label><?= Yii::t('app', 'group id') ?></label><?= Html::encode(\center\modules\auth\models\SrunJiegou::getOwnParent($model->group_id)) ?>
                    </li>
                    <?php foreach (\center\modules\setting\models\ExtendsField::getAllData() as $one): ?>
                        <li>
                            <i class="icon fa fa-circle-o"></i>
                            <?php
                            echo '<label>' . $one['field_desc'] . '</label>';
                            $tmp = $one['field_name'];
                            if (isset($attributes[$one['field_name']][$model->$tmp])) {
                                echo Html::encode($attributes[$one['field_name']][$model->$tmp]);
                            } else {
                                echo Html::encode($model->$tmp);
                            }
                            ?>
                        </li>
                    <?php endforeach ?>
                    <li>
                        <i class="icon fa fa-user"></i>
                        <label><?= Yii::t('app', 'mgr name update') ?></label><?= Html::encode($model->mgr_name_update) ?>
                        &nbsp;&nbsp;(<?= Html::encode(date('Y-m-d H:i:s', $model->user_update_time)) ?>)
                    </li>
                    <li>
                        <i class="icon fa fa-user"></i>
                        <label><?= Yii::t('app', 'mgr name create') ?></label><?= Html::encode($model->mgr_name_create) ?>
                        &nbsp;&nbsp;(<?= Html::encode(date('Y-m-d H:i:s', $model->user_create_time)) ?>)
                    </li>
                </ul>
            </div>

            <!--在线信息-->
            <?php if ($canOnline || $canOnlist): ?>
                <div class="panel panel-default">
                    <div class="panel-heading"><strong><span
                                class="glyphicon glyphicon-th-large"></span> <?= Yii::t('app', 'user base help13') ?>
                        </strong></div>
                    <div class="panel-body">
                        <?php if ($onlineList): ?>
                            <table class="table table-hover">
                                <thead>
                                <tr>
                                    <th><?= Yii::t('app', 'user ip') ?></th>
                                    <th><?= Yii::t('app', 'user mac') ?></th>
                                    <th><?= Yii::t('app', 'bytes in') ?></th>
                                    <th><?= Yii::t('app', 'bytes out') ?></th>
                                    <th><?= Yii::t('app', 'add time') ?></th>
                                    <th><?= Yii::t('app', 'operate') ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($onlineList as $online):
                                    if (empty($online['user_name']) || empty($online['ip'])) continue;
                                    ?>
                                    <tr>
                                        <td><?= Html::encode($online['ip']) ?></td>
                                        <td><?= Html::encode($online['user_mac']) ?></td>
                                        <td><?= Html::encode(isset($online['bytes_in']) ? Tool::bytes_format($online['bytes_in'] - $online['bytes_in1']) : '') ?></td>
                                        <td><?= Html::encode(isset($online['bytes_out']) ? Tool::bytes_format($online['bytes_out'] - $online['bytes_out1']) : '') ?></td>
                                        <td><?= Html::encode(isset($online['add_time']) ? date('Y-m-d H:i:s', $online['add_time']) : '') ?></td>
                                        <td>
                                            <?php if ($canOndown): ?>
                                                <?= Html::a(Yii::t('app', 'off line'), ['operate', 'action' => 'drop', 'user_name' => $model->user_name, 'type' => isset($online['bytes_in']) ? 'radius' : 'proxy', 'id' => $online['rad_online_id']], [
                                                    'class' => 'btn btn-danger btn-xs',
                                                    'data' => [
                                                        'confirm' => Yii::t('app', 'user online help3'),
                                                        'method' => 'post',
                                                    ],
                                                ]) ?>
                                            <?php endif ?>
                                        </td>
                                    </tr>
                                <?php endforeach ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <?= Yii::t('app', 'user base help10'); ?>
                        <?php endif ?>
                    </div>
                </div>
            <?php endif ?>
        </div>
        <!--右侧-->
        <div class="col-md-6">

            <!--操作-->
            <div class="panel panel-default">
                <div class="panel-heading"><strong><span
                            class="glyphicon glyphicon-th-large"></span> <?= Yii::t('app', 'function') ?></strong></div>
                <div class="panel-body">
                    <?php if ($canPay): echo Html::a('<i class="fa fa-usd"></i>&nbsp' . Yii::t('app', 'Financial Payment'), ['/financial/pay/index', 'action' => 'product', 'user_name' => $model->user_name], ['class' => 'btn btn-success btn-sm']); endif; ?>
                    <?php if ($canEdit): echo Html::a('<i class="fa fa-pencil"></i>&nbsp' . Yii::t('app', 'Modify'), ['edit', 'id' => $model->user_id], ['class' => 'btn btn-info btn-sm']); endif; ?>
                    <?php if ($canRefund): echo Html::a('<i class="fa fa-credit-card"></i>&nbsp' . Yii::t('app', 'Financial Refund'), ['/financial/refund/index', 'user_name' => $model->user_name], ['class' => 'btn btn-warning btn-sm']); endif; ?>
                    <?php if ($canDelete): echo Html::a(Html::button(Yii::t('app', 'User Delete'), ['class' => 'btn btn-danger btn-sm']), ['delete', 'id' => $model->user_id], [
                        'title' => Yii::t('app', 'User Delete'),
                        'data' => [
                            'method' => 'post',
                            'confirm' => Yii::t('app', 'user base help5'),
                        ],
                    ]); endif; ?>
                    <?php if ($canTransfer && $model->balance > 0): echo Html::a(Html::button(Yii::t('app', 'operate type Financial Transfer'), ['class' => 'btn btn-primary btn-sm']), ['transfer', 'id' => $model->user_id], [
                        'title' => Yii::t('app', 'operate type Financial Transfer'),]);
                    endif; ?>
                    <div class="divider"></div>
                    <table class="table">
                        <tbody>
                        <tr><!--用户状态-->
                            <td width="30%"><?= $labels['user_available'] ?></td>
                            <td width="70%">
                                <?php $form = \yii\widgets\ActiveForm::begin([
                                    'action' => 'operate',
                                    'id' => 'available',
                                    'method' => 'get',
                                ]) ?>
                                <?= Html::hiddenInput('action', 'available') ?>
                                <?= Html::hiddenInput('user_name', $model->user_name) ?>
                                <?php $available_css = $model->user_available ? 'text-danger' : 'text-success'; ?>
                                <?= '<span class="' . $available_css . '">' . $attributes['user_available'][$model->user_available] . '</span>' ?>
                                &nbsp;&nbsp;
                                <?php
                                if ($canViewUserAvailable) {
                                    //如果用户状态==2
                                    if ($model->user_available == 2) {
                                        echo Html::a(Yii::t('app', 'user available2'), ['operate', 'type' => 0], [
                                            'class' => 'btn btn-xs btn-success',
                                            'data' => [
                                                'method' => 'post',
                                            ],
                                        ]);
                                    } else {
                                        echo Html::a(Yii::t('app', $model->user_available == 1 ? 'user available2' : 'user available1'), ['operate', 'type' => $model->user_available == 1 ? 0 : 1], [
                                            'class' => $model->user_available == 1 ? 'btn btn-xs btn-success' : 'btn btn-xs btn-danger',
                                            'data' => [
                                                'method' => 'post',
                                            ],
                                        ]);
                                        echo '&nbsp;&nbsp';
                                        echo Html::a(Yii::t('app', $model->user_available == 3 ? 'user available2' : 'user available4'), ['operate', 'type' => $model->user_available == 3 ? 0 : 3], [
                                            'class' => $model->user_available == 3 ? 'btn btn-xs btn-success' : 'btn btn-xs btn-danger',
                                            'data' => [
                                                'method' => 'post',
                                            ],
                                        ]);
                                        echo '&nbsp;&nbsp';
                                        echo Html::a(Yii::t('app', $model->user_available == 4 ? 'user available2' : 'user available5'), ['operate', 'type' => $model->user_available == 4 ? 0 : 4], [
                                            'class' => $model->user_available == 4 ? 'btn btn-xs btn-success' : 'btn btn-xs btn-danger',
                                            'data' => [
                                                'method' => 'post',
                                            ],
                                        ]);
                                    }
                                }
                                ?>
                                <?php $form->end() ?>
                            </td>
                        </tr>
                        <tr>
                            <td><?= $labels['user_expire_time'] ?></td>
                            <td>
                                <?php if ($canViewExpire): ?>
                                    <?php $form = \yii\widgets\ActiveForm::begin([
                                        'action' => 'operate',
                                        'id' => 'expire',
                                    ]) ?>
                                    <?= Html::hiddenInput('action', 'expire') ?>
                                    <?= Html::hiddenInput('user_name', $model->user_name) ?>
                                    <table>
                                        <tr>
                                            <td><input name="user_expire_time" class="form-control inputDateHour"
                                                       value="<?= $model->user_expire_time ?>"></td>
                                            <td><?= Html::submitButton(Yii::t('app', 'save'), ['class' => 'btn btn-success btn-sm margin-left-5px']) ?></td>
                                        </tr>
                                    </table>
                                    <?php $form->end() ?>
                                <?php else: ?>
                                    <?= $model->user_expire_time ?>
                                <?php endif ?>
                            </td>
                        </tr>
                        <?php if ($canMaxOnlineNum) :?>
                        <tr>
                            <td><?= $labels['max_online_num'] ?></td>
                            <td>
                                <?php $form = \yii\widgets\ActiveForm::begin([
                                    'action' => 'operate',
                                ]) ?>
                                <?= Html::hiddenInput('action', 'max_online_num') ?>
                                <?= Html::hiddenInput('user_name', $model->user_name) ?>
                                <table>
                                    <tr>
                                        <td>
                                            <?= Html::dropDownList('max_online_num', $model->max_online_num, $attributes['max_online_num_selection'], ['class' => 'form-control']) ?>
                                        </td>
                                        <td><?= Html::submitButton(Yii::t('app', 'save'), ['class' => 'btn btn-success btn-sm margin-left-5px']) ?></td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"
                                            class="help-block"><?= Yii::t('app', 'user base help22') ?></td>
                                    </tr>
                                </table>
                                <?php $form->end() ?>
                            </td>
                        </tr>
                        <?php endif;?>
                        <?php
                        if ($bindList) {
                            $no_mac_auth = isset($bindList['no_mac_auth']) ? $bindList['no_mac_auth'] : 0;
                            unset($bindList['no_mac_auth']);
                            foreach ($bindList as $key => $item) {
                                if($key == 'mac_auths' && !$canMacAuthInfo){
                                    continue;
                                }
                                if($key == 'macs' && !$canMacInfo){
                                    continue;
                                }
                                if($key == 'nas_port_ids' && !$canNasPortIDInfo){
                                    continue;
                                }
                                if($key == 'vlan_ids' && !$canVlanIDInfo){
                                    continue;
                                }
                                if($key == 'ips' && !$canIPV4Info){
                                    continue;
                                }
                                echo '<tr><td>';
                                if ($key == 'mac_auths') {
                                    $form = \yii\widgets\ActiveForm::begin([
                                        'action' => 'operate'
                                    ]);
                                    echo Html::hiddenInput('action', 'no_mac');
                                    echo Html::hiddenInput('user_name', $model->user_name);
                                    echo Html::hiddenInput('type', $no_mac_auth ? 1 : 0);
                                    echo $labels[$key];
                                    if ($no_mac_auth) {
                                        echo '<span class="text-danger">' . Yii::t('app', 'mac auth2') . '</span>';
                                        if ($canOperate) {
                                            echo Html::submitButton(Yii::t('app', 'mac auth open'), ['class' => 'btn btn-success btn-xs']);
                                        }
                                    } else {
                                        echo '<span class="text-success">' . Yii::t('app', 'mac auth1') . '</span>';
                                        if ($canOperate) {
                                            echo Html::submitButton(Yii::t('app', 'mac auth close'), ['class' => 'btn btn-danger btn-xs']);
                                        }
                                    }
                                    $form->end();
                                } else {
                                    echo $labels[$key];
                                }
                                echo '</td><td>';
                                if ($item) {
                                    echo '<table class="table table-hover"><tbody>';
                                    foreach ($item as $v) {
                                        echo '<tr>
                                                          <td width="80%">' . $v . '</td>';
                                        if ($canOperate) {
                                            echo '<td width="20%">' .
                                                Html::a(Yii::t('app', 'delete'), ['operate', 'action' => 'delBind', 'type' => $key, 'user_name' => $model->user_name, 'bindVal' => $v], [
                                                    //'class' => 'btn btn-danger btn-xs',
                                                    'class' => 'text-danger',
                                                    'data' => [
                                                        'confirm' => Yii::t('app', 'user base help9'),
                                                        'method' => 'post',
                                                    ],
                                                ]) .
                                                '</td>';
                                        }
                                        echo '</tr>';
                                    }
                                    echo '</tbody></table>';
                                } else {
                                    echo Yii::t('app', 'user base help10');
                                }
                                echo '</td></tr>';
                            }
                        }
                        ?>
                        <?php
                        if($canCDRInfo){
                            echo '<tr><td>';
                            echo Yii::t('app', 'CDR bind');
                            echo '</td><td>';
                            if ($bindCDRList) {
                                echo '<table class="table table-hover"><tbody>';
                                foreach ($bindCDRList as $key => $item) {
                                    echo '<tr>
                                  <td width="80%">' . $item . '</td>';
                                    if ($canOperate) {
                                        echo '<td width="20%">' .
                                            Html::a(Yii::t('app', 'delete'), ['operate', 'action' => 'delCDRBind', 'user_name' => $model->user_name, 'bindCDRVal' => $item], [
                                                //'class' => 'btn btn-danger btn-xs',
                                                'class' => 'text-danger',
                                                'data' => [
                                                    'confirm' => Yii::t('app', 'user base help9'),
                                                    'method' => 'post',
                                                ],
                                            ]) .
                                            '</td>';
                                    }
                                    echo '</tr>';
                                }
                                echo '</tbody></table>';
                            } else {
                                echo Yii::t('app', 'user base help10');
                            }
                            echo '</td></tr>';
                        }
                        ?>
                        <?php if ($canBind): ?>
                            <tr>
                                <td><?= Yii::t('app', 'add bind') ?></td>
                                <td>
                                    <?php
                                    $form = \yii\widgets\ActiveForm::begin([
                                        'action' => 'operate'
                                    ]);
                                    echo Html::hiddenInput('action', 'addBind');
                                    echo Html::hiddenInput('user_name', $model->user_name);
                                    ?>
                                    <table>
                                        <tr>
                                            <td>
                                                <?= Html::dropDownList('type', 1, $attributes['bindType'], ['class' => 'form-control']) ?>
                                            </td>
                                            <td data-ng-init="bindVal=''">
                                                <input class="form-control" type="text" name="bindVal" value=""
                                                       data-ng-model="bindVal"/>
                                            </td>
                                            <td><?= Html::submitButton(Yii::t('app', 'save'), [
                                                    'class' => 'btn btn-success btn-sm margin-left-5px',
                                                    'data-ng-disabled' => 'bindVal==""'
                                                ]) ?>
                                            </td>
                                        </tr>
                                    </table>
                                    <?php $form->end() ?>
                                </td>
                            </tr>
                        <?php endif ?>
                        <!--添加CDR-->
                        <?php if ($canCDRBind): ?>
                            <tr>
                                <td><?= Yii::t('app', 'add CDR bind') ?></td>
                                <td>
                                    <?php
                                    $form = \yii\widgets\ActiveForm::begin([
                                        'action' => 'operate'
                                    ]);
                                    echo Html::hiddenInput('action', 'addCDRBind');
                                    echo Html::hiddenInput('user_name', $model->user_name);
                                    ?>
                                    <table>
                                        <tr>
                                            <td>
                                                <?= Html::dropDownList('type', 1, $attributes['bindCDRType'], ['class' => 'form-control']) ?>
                                            </td>
                                            <td data-ng-init="bindCDRVal=''">
                                                <input class="form-control" type="text" name="bindCDRVal" value=""
                                                       data-ng-model="bindCDRVal"/>
                                            </td>
                                            <td><?= Html::submitButton(Yii::t('app', 'save'), [
                                                    'class' => 'btn btn-success btn-sm margin-left-5px',
                                                    'data-ng-disabled' => 'bindCDRVal==""'
                                                ]) ?>
                                            </td>
                                        </tr>
                                    </table>
                                    <?php $form->end() ?>
                                </td>
                            </tr>
                        <?php endif ?>
                        <!--停机保号-->
                        <?php if ($canStopUser): ?>
                            <tr>
                                <td><?= Yii::t('app', 'stop to protect') ?></td>
                                <td>
                                    <?php /*$form = \yii\widgets\ActiveForm::begin([
                                            'action'=>'operate',
                                            'options' => [
                                                'novalidate' => true,
                                                'name' => 'stopToProtect',
                                                'class' => 'form-validation',
                                            ],
                                        ])*/ ?>
                                    <?= Html::hiddenInput('action', 'stopToProtect') ?>
                                    <?= Html::hiddenInput('user_name', $model->user_name) ?>
                                    <table>
                                        <tr>
                                            <td width="25%">
                                                <input class="form-control" type="number" required
                                                       data-ng-pattern="/^[0-9]*[1-9][0-9]*$/" name="num"
                                                       data-ng-model="num" value=""
                                                       placeholder="<?= Yii::t('app', 'stop num') ?>"/>
                                            </td>
                                            <td width="30%">
                                                <?= Html::dropDownList('type1', 'months', $attributes['stopType'], ['class' => 'form-control']) ?>
                                            </td>
                                            <td width="25%">
                                                <input class="form-control" type="number" required data-min=0
                                                       name="money" data-ng-model="money" value=""
                                                       placeholder="<?= Yii::t('app', 'amount') ?>"/>
                                            </td>
                                            <td>
                                                <?= Html::button(Yii::t('app', 'save'), [
                                                    'class' => 'btn btn-success btn-sm margin-left-5px',
                                                    'data-ng-disabled' => 'stopToProtect.$invalid',
                                                    'onclick' => 'stopProtect("' . $model->user_name . '")',
                                                ]) ?>
                                            </td>
                                        </tr>
                                        <!--停机保号结果提示-->
                                        <tr>
                                            <td colspan="2" id="res"
                                                style="display:none;padding-top:8px;"><?= Yii::t('app', 'money shortage') . Html::a(Yii::t('app', 'user base help17'), ['/financial/pay/index?user_name=' . $model->user_name]); ?></td>
                                        </tr>
                                    </table>
                                    <?php //$form::end()?>
                                </td>
                            </tr>
                        <?php endif ?>

                        <!--充值流量卡-->
                        <?php if ($canAction): ?>
                            <tr>
                                <td><?= Yii::t('app', 'recharge key') ?></td>
                                <td>
                                    <table>
                                        <tr>
                                            <td width="75%">
                                                <input class="form-control" data-ng-pattern="/^[A-Za-z0-9]{10,32}$/"
                                                       name="key" data-ng-model="key" value=""
                                                       placeholder="<?= Yii::t('app', 'key_value') ?>"/>
                                            </td>
                                            <td>
                                                <?= Html::button(Yii::t('app', 'pay'), [
                                                    'class' => 'btn btn-success btn-sm margin-left-5px',
                                                    'data-ng-disabled' => 'rechargeFlowCard.$invalid',
                                                    'onclick' => 'rechargeKey("' . $model->user_name . '")',
                                                ]) ?>
                                            </td>
                                        </tr>
                                        <!--key充值流量卡结果提示-->
                                        <tr>
                                            <td colspan="2" id="key_res"
                                                style="display:none;padding-top:8px;color:red;"></td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        <?php endif ?>

                        <!--按钮操作-->
                        <tr>
                            <td colspan="2">
                                <!--转移产品按钮-->
                                <?php if (!empty($unorderedProductList)): ?>
                                    <button data-ng-model="changeNow" data-ng-click="changeNow = !changeNow"
                                            type="button" class="btn btn-w-md btn-gap-v btn-info">
                                        <?= Yii::t('app', 'action changeProduct') ?>
                                        <i class="fa fa-chevron-down" ng-show="!changeNow"></i>
                                        <i class="fa fa-chevron-up ng-hide" ng-show="changeNow"></i>
                                    </button>
                                <?php endif; ?>
                                <!--动态条件按钮-->
                                <?php if ($canDongtai): ?>
                                    <button data-ng-model="Condition" data-ng-click="Condition = !Condition"
                                            type="button" class="btn btn-w-md btn-gap-v btn-info">
                                        <?= Yii::t('app', 'strategy/condition/index') ?>
                                        <i class="fa fa-chevron-down" ng-show="!Condition"></i>
                                        <i class="fa fa-chevron-up ng-hide" ng-show="Condition"></i>
                                    </button>
                                <?php endif ?>
                            </td>
                        </tr>

                        <!--立即转移产品-->
                        <?php if ($canProChangeNow && !empty($unorderedProductList)): ?>
                            <tr ng-show="changeNow">
                                <td><?= Yii::t('app', 'product change now') ?></td>
                                <td>
                                    <?= Html::hiddenInput('fee', $fee) ?>
                                    <?= Html::hiddenInput('halffee', $halffee) ?>
                                    <?= Html::hiddenInput('daysfee', $daysfee) ?>
                                    <?= Html::hiddenInput('discount', 1); ?>
                                    <table width="100%">
                                        <tr>
                                            <td width="30%"><?= Yii::t('app', 'product from') ?></td>
                                            <td width="70%"><?= Html::dropDownList('pid_from_now', '', ArrayHelper::map($orderedProductList, 'products_id', 'products_name'), ['class' => 'form-control', 'onchange' => 'getDedfee(this.value,"' . $model->user_name . '")']) ?></td>
                                        </tr>
                                        <tr style="line-height: 34px;">
                                            <td><?= Yii::t('app', 'product to') ?></td>
                                            <td><?= Html::dropDownList('pid_to_now', '', ArrayHelper::map($unorderedProductList, 'products_id', 'products_name'), ['class' => 'form-control']) ?></td>
                                        </tr>
                                        <tr style="line-height: 34px;" class="dedfee">
                                            <td></td>
                                            <td>
                                                <?= Html::dropDownList('dedFeeType', 'allfee', $attributes['dedFeeType'], ['class' => 'form-control', 'onchange' => 'getDedType()']) ?>
                                            </td>
                                        </tr>
                                        <tr style="line-height: 34px;">
                                            <td></td>
                                            <td class="dedfee">
                                                <table width="100%">
                                                    <tr style="line-height: 34px;">
                                                        <td width="20%"><?= Yii::t('app', 'ded fee msg') ?></td>
                                                        <td width="30%"><input type="text" class="form-control"
                                                                               id="dedfeeamount" name='dedfeeval'
                                                                               value="<?= $fee ?>"></td>
                                                        <td width="30%"
                                                            style="padding-left: 10px"><?= Yii::t('app', 'currency') ?></td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr style="line-height: 26px;">
                                            <td></td>
                                            <td>
                                                <?= Html::button(Yii::t('app', 'save'), [
                                                    'class' => 'btn btn-success btn-xs',
                                                    'onclick' => 'changeProductNow("' . $model->user_name . '","' . $model->user_id . '")',
                                                ]) ?>
                                            </td>
                                        </tr>
                                        <!--立即转移产品结果提示-->
                                        <tr style="line-height: 26px;">
                                            <td colspan="2" id="nowres"></td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        <?php endif ?>

                        <!--转移产品到下个周期-->
                        <?php if ($canProChangeNext && !empty($unorderedProductList)): ?>
                            <tr ng-show="changeNow">
                                <td><?= Yii::t('app', 'product change next') ?></td>
                                <td>
                                    <table width="100%">
                                        <tr>
                                            <td width="30%">
                                                <?= Yii::t('app', 'product from') ?>
                                            </td>
                                            <td width="70%">
                                                <?= Html::dropDownList('pid_from_next', '', ArrayHelper::map($orderedProductList, 'products_id', 'products_name'), ['class' => 'form-control']) ?>
                                            </td>
                                        </tr>
                                        <tr style="line-height: 34px;">
                                            <td><?= Yii::t('app', 'product to') ?></td>
                                            <td>
                                                <?= Html::dropDownList('pid_to_next', '', ArrayHelper::map($unorderedProductList, 'products_id', 'products_name'), ['class' => 'form-control']) ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td>
                                                <?= Html::button(Yii::t('app', 'save'), [
                                                    'class' => 'btn btn-success btn-xs',
                                                    'onclick' => 'changeProductNext("' . $model->user_name . '","' . $model->user_id . '")',
                                                ]) ?>
                                            </td>
                                        </tr>
                                        <!--转移产品到下个周期结果提示-->
                                        <tr>
                                            <td colspan="2" id="nextres"></td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        <?php endif ?>
                        <!--预约转移产品-->
                        <?php if ($canProChangeAppoint && !empty($unorderedProductList)): ?>
                            <tr ng-show="changeNow">
                                <td><?= Yii::t('app', 'product change appoint') ?></td>
                                <td>
                                    <table width="100%">
                                        <tr>
                                            <td width="30%">
                                                <?= Yii::t('app', 'product from') ?>
                                            </td>
                                            <td width="70%">
                                                <?= Html::dropDownList('pid_from_appoint', '', ArrayHelper::map($orderedProductList, 'products_id', 'products_name'), ['class' => 'form-control']) ?>
                                            </td>
                                        </tr>
                                        <tr style="line-height: 34px;">
                                            <td><?= Yii::t('app', 'product to') ?></td>
                                            <td>
                                                <?= Html::dropDownList('pid_to_appoint', '', ArrayHelper::map($unorderedProductList, 'products_id', 'products_name'), ['class' => 'form-control']) ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><?= Yii::t('app', 'change date') ?></td>
                                            <td>
                                                <input name="change_time" class="form-control inputDateAfterToday"
                                                       value="">
                                            </td>
                                        </tr>
                                        <tr style="line-height: 34px;">
                                            <td></td>
                                            <td>
                                                <?= Html::button(Yii::t('app', 'save'), [
                                                    'class' => 'btn btn-success btn-xs',
                                                    'onclick' => 'changeProductAppoint("' . $model->user_name . '","' . $model->user_id . '")',
                                                ]) ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" id="nextres"></td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        <?php endif ?>
                        <!--动态条件-->
                        <tr ng-show="Condition">
                            <td><?= Html::dropDownList('type', 1, $attributes['condition'], ['class' => 'form-control', onchange => 'selectCondition(this.value)']) ?></td>
                            <td>
                                <?php
                                $form = \yii\widgets\ActiveForm::begin([
                                    'action' => 'operate'
                                ]);
                                echo Html::hiddenInput('action', 'addCondition');
                                echo Html::hiddenInput('user_name', $model->user_name);
                                ?>
                                <table class="table">
                                    <tr>
                                        <td>
                                            <?= Yii::t('app', 'Condition Key') ?>:
                                        </td>
                                        <td colspan="2" id="condition_key">

                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            key:
                                        </td>
                                        <td colspan="2" id="condition_key_new">
                                        </td>
                                        <input type="hidden" name="condition_key" value="">
                                    </tr>
                                    <tr id="condition_group_id" style="display: none">
                                        <td>
                                            {GROUP_ID}:
                                        </td>
                                        <td colspan="2">
                                            <input type="text" class="form-control" name="condition_group_id" value="">
                                        </td>
                                    </tr>
                                    <tr id="condition_products_id" style="display: none">
                                        <td>
                                            {PRODUCTS_ID}:
                                        </td>
                                        <td colspan="2">
                                            <input type="text" class="form-control" name="condition_products_id"
                                                   value="">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>value:</td>
                                        <td id="InputsWrapper">
                                            <input class="form-control" style="display: inline;width: 75%" type="text"
                                                   name="value" id="condition_value" value=""/>
                                        </td>
                                        <td>
                                            <?= Html::submitButton(Yii::t('app', 'save'), [
                                                'class' => 'btn btn-success btn-sm margin-left-5px',
                                            ]) ?>
                                        </td>
                                    </tr>
                                </table>
                                <?php $form->end() ?>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!--下边横排-->
    <div class="row">
        <!--已订购产品-->
        <div class="col-md-12">
            <!--已订购产品信息-->
            <div class="panel panel-default">
                <div class="panel-heading"><strong><span
                            class="glyphicon glyphicon-th-large"></span> <?= Yii::t('app', 'user base help12') ?>
                    </strong></div>
                <div class="panel-body">
                    <?php if ($orderedProductList): ?>
                        <table class="table">
                            <thead>
                            <tr>
                                <th><?= Yii::t('app', 'products id') ?></th>
                                <th><?= Yii::t('app', 'products name') ?></th>
                                <th><?= Yii::t('app', 'sum bytes') ?></th>
                                <th><?= Yii::t('app', 'sum seconds') ?></th>
                                <th><?= Yii::t('app', 'sum times') ?></th>
                                <th><?= Yii::t('app', 'user charge') ?></th>
                                <th><?= Yii::t('app', 'products balance') ?></th>
                                <th><?= Yii::t('app', 'checkout date') ?></th>
                                <th><?= Yii::t('app', 'product expire date') ?></th>
                                <th><?= Yii::t('app', 'next product') ?></th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($orderedProductList as $pid => $product): ?>
                                <tr>
                                    <td><?= Html::encode($pid) ?></td>
                                    <td><?= Html::encode(isset($product['products_name']) ? $product['products_name'] : '--') ?></td>
                                    <td><?= Html::encode(isset($product['used']['sum_bytes']) ? Tool::bytes_format($product['used']['sum_bytes']) : '--') ?></td>
                                    <td><?= Html::encode(isset($product['used']['sum_seconds']) ? Tool::seconds_format($product['used']['sum_seconds']) : '--') ?></td>
                                    <td><?= Html::encode(isset($product['used']['sum_times']) ? $product['used']['sum_times'] : '--') ?></td>
                                    <td><?= Html::encode($product['used']['user_charge']) ?></td>
                                    <td><?= Html::encode($product['used']['user_balance']) ?></td>
                                    <td><?= Html::encode(isset($product['checkout_date']) ? $product['checkout_date'] : ($model->user_available == 3 ? Yii::t('app', 'user have been suspended') : '--')) ?></td>
                                    <td><?= Html::encode(isset($product['expire_date']) ? $product['expire_date'] : ($model->user_available == 3 ? Yii::t('app', 'user have been suspended') : '--')) ?></td>
                                    <td><?= Html::encode(isset($product['next_product']) ? $product['next_product'] : $product['products_name']) ?></td>
                                    <td><?php if ($canCancelProduct && $productNum > 1): ?>
                                            <?= Html::a(Yii::t('app', 'user/base/cancel-product'), ['cancel-product', 'user_name' => $model->user_name, 'id' => $pid], ['class' => 'btn btn-danger btn-xs',
                                                'data' => [
                                                    'confirm' => Yii::t('app', 'user base help16')
                                                ],
                                            ]) ?>

                                        <?php endif ?>
                                        <?php
                                        //禁用和启用产品
                                        if ($canDisable) {
                                            if (!isset($product['used']['user_available']) || $product['used']['user_available'] == 0) {
                                                echo Html::a(Yii::t('app', 'user/base/disable_product'),
                                                    ['disable-product', 'user_name' => $model->user_name, 'id' => $pid],
                                                    ['class' => 'btn btn-danger btn-xs', 'data' => [
                                                        'confirm' => Yii::t('app', 'user base help24')],
                                                    ]);
                                            } else {
                                                echo Html::a(Yii::t('app', 'user/base/enable_product'),
                                                    ['enable-product', 'user_name' => $model->user_name, 'id' => $pid],
                                                    ['class' => 'btn btn-success btn-xs', 'data' => [
                                                        'confirm' => Yii::t('app', 'user base help25')],
                                                    ]);
                                            }

                                            echo '<div style="margin-top:7px;"></div>';
                                        }
                                        /*//触发上线
                                        echo Html::a(Yii::t('app', 'user/base/online_product'),
                                            ['online-product', 'user_name'=>$model->user_name, 'id'=>$pid],
                                            ['class' => 'btn btn-success btn-xs','data' => [
                                                'confirm' => Yii::t('app', 'user base help32') ],
                                            ]);
                                        //触发下线
                                        echo Html::a(Yii::t('app', 'user/base/offline_product'),
                                            ['offline-product', 'user_name'=>$model->user_name, 'id'=>$pid],
                                            ['class' => 'btn btn-warning btn-xs','data' => [
                                                'confirm' => Yii::t('app', 'user base help32') ],
                                            ]);*/
                                        ?>


                                        <?= isset($product['package']['detail']) ?
                                            Html::button(Yii::t('app', 'view package'), ['class' => 'btn btn-primary btn-xs',
                                                'ng-model' => 'package' . $pid,
                                                'ng-click' => "chgDisplay( 'package$pid', '' )"]) : '';
                                        ?>
                                        <?php if (count($product['package']['detail']) > 5): ?>
                                            <?= isset($product['package']['detail']) ?
                                                Html::button(Yii::t('app', 'view package total'),
                                                    ['class' => 'btn btn-primary btn-xs',
                                                        'ng-model' => 'package' . $pid . '_all',
                                                        'ng-click' => "chgDisplay('package$pid', 'all')",
                                                    ]
                                                ) : '';
                                            ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php $i = 1;
                                if (isset($product['package']['detail'])): ?>
                                    <?php if ($i <= 5): ?>
                                        <tr ng-show="package<?= $pid ?>">
                                    <?php else: ?>
                                    <tr ng-show="package<?= $pid ?> && amount==1">
                                <?php endif; ?>

                                <td colspan="13">
                                    <table class="table">
                                        <thead>
                                        <tr>
                                            <th nowrap="nowrap"><?= Yii::t('app', 'package id') ?></th>
                                            <th nowrap="nowrap"><?= Yii::t('app', 'package name') ?></th>
                                            <th nowrap="nowrap"><?= Yii::t('app', 'condition') ?></th>
                                            <!--                                            <th nowrap="nowrap">-->
                                            <? //= Yii::t('app', 'total bytes')?><!--(B)</th>-->
                                            <!--                                            <th nowrap="nowrap">-->
                                            <? //= Yii::t('app', 'time long')?><!--(S)</th>-->
                                            <!--                                            <th nowrap="nowrap">-->
                                            <? //= Yii::t('app', 'remain bytes')?><!--(B)</th>-->
                                            <!--                                            <th nowrap="nowrap">-->
                                            <? //= Yii::t('app', 'remain seconds')?><!--(S)</th>-->
                                            <!--                                            <th nowrap="nowrap">-->
                                            <? //= Yii::t('app', 'used bytes')?><!--(B)</th>-->
                                            <!--                                            <th nowrap="nowrap">-->
                                            <? //= Yii::t('app', 'used times')?><!--(S)</th>-->
                                            <th nowrap="nowrap"><?= Yii::t('app', 'buy time') ?></th>
                                            <th nowrap="nowrap"><?= Yii::t('app', 'package valid day') ?></th>
                                            <th nowrap="nowrap"><?= Yii::t('app', 'expire time') ?></th>
                                            <th nowrap="nowrap"><?= Yii::t('app', 'statistics') ?></th>
                                            <th nowrap="nowrap"></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach ($product['package']['detail'] as $package_id => $package): ?>
                                            <tr>
                                                <td><?= Html::encode($package_id) ?></td>
                                                <td><?= Html::encode($package['package_name']) ?></td>
                                                <td><?= Html::encode($package['condition']) ?></td>
                                                <!--                                                <td>-->
                                                <? //= Html::encode(Tool::bytes_format($package['bytes']))?><!--</td>-->
                                                <!--                                                <td>-->
                                                <? //= Html::encode(Tool::seconds_format($package['seconds']))?><!--</td>-->
                                                <!--                                                <td>-->
                                                <? //= Html::encode(Tool::bytes_format($package['remain_bytes']))?><!--</td>-->
                                                <!--                                                <td>-->
                                                <? //= Html::encode(Tool::seconds_format($package['remain_seconds']))?><!--</td>-->
                                                <!--                                                <td>-->
                                                <? //= Html::encode(Tool::bytes_format($package['bytes']-$package['remain_bytes']))?><!--</td>-->
                                                <!--                                                <td>-->
                                                <? //= Html::encode(Tool::seconds_format($package['seconds']-$package['remain_seconds']))?><!--</td>-->
                                                <td><?= Html::encode(date('Y-m-d H:i:s', $package['add_time'])) ?></td>
                                                <td><?= Html::encode($package['valid_day']) . (isset($package['valid_cycle']) && !empty($package['valid_cycle']) ? Yii::t('app', $package['valid_cycle'] . 's') : Yii::t('app', 'days')) ?></td>
                                                <td><?= Html::encode($package['expire_time'] == 0 ? Yii::t('app', 'no expire time') : date('Y-m-d H:i:s', $package['expire_time'])) ?></td>
                                                <td>
                                                    <?php
                                                    $class = "bg-success";
                                                    $ratio = $package['usage_rate'];
                                                    if ($ratio > 0.5) {
                                                        $class = "bg-success";
                                                    } elseif (($ratio > 0.2) && ($ratio <= 0.5)) {
                                                        $class = "bg-warning";
                                                    } else {
                                                        $class = "bg-danger";
                                                    }
                                                    ?>
                                                    <span ng-show="<?= $package['billing_mode'] == 3 ?>"
                                                          class="bg-info"><?= Yii::t('app', 'total bytes') ?><?= Html::encode(Tool::bytes_format($package['bytes'])) ?></span>
                                                    <span ng-show="<?= $package['billing_mode'] == 3 ?>"
                                                          class=<?= $class ?>><?= Yii::t('app', 'remain bytes') ?><?= Html::encode(Tool::bytes_format($package['remain_bytes'])) ?></span>
                                                    <span ng-show="<?= $package['billing_mode'] == 3 ?>"
                                                          class=<?= $class ?>><?= (round($ratio, 2) * 100) . "%" ?></span>
                                                    <span ng-show="<?= $package['billing_mode'] == 4 ?>"
                                                          class="bg-info"><?= Yii::t('app', 'time long') ?><?= Html::encode(Tool::seconds_format($package['seconds'])) ?></span>
                                                    <span ng-show="<?= $package['billing_mode'] == 4 ?>"
                                                          class=<?= $class ?>><?= Yii::t('app', 'remain seconds') ?><?= Html::encode(Tool::seconds_format($package['remain_seconds'])) ?></span>
                                                    <span ng-show="<?= $package['billing_mode'] == 4 ?>"
                                                          class=<?= $class ?>><?= (round($ratio, 2) * 100) . "%" ?></span>
                                                    <span ng-show="<?= $package['billing_mode'] == 5 ?>"
                                                          class="bg-info"><?= Yii::t('app', 'total times') ?><?= Html::encode($package['times']) ?></span>
                                                    <span ng-show="<?= $package['billing_mode'] == 5 ?>"
                                                          class=<?= $class ?>><?= Yii::t('app', 'remain times') ?><?= Html::encode($package['remain_times']) ?></span>
                                                    <span ng-show="<?= $package['billing_mode'] == 5 ?>"
                                                          class=<?= $class ?>><?= (round($ratio, 2) * 100) . "%" ?></span>
                                                </td>
                                                <td>
                                                    <?php if ($canCancelPackage && $ratio == 1 && (($package['expire_time'] > 0 && $package['expire_time'] > time()) || $package['expire_time'] == 0)) {
                                                        echo Html::a(Yii::t('app', 'user/base/cancel-package'), ['cancel-package', 'user_name' => $model->user_name, 'product_id' => $pid, 'package_id' => $package_id, 'amount' => $package['amount']], ['class' => 'btn btn-danger btn-xs',
                                                            'data' => [
                                                                'confirm' => Yii::t('app', 'user base help37', ['amount' => $package['amount']])
                                                            ],
                                                        ]);
                                                    } ?>
                                                </td>
                                            </tr>
                                            <?php $i++;endforeach ?>
                                        </tbody>
                                    </table>
                                </td>
                                </tr>
                                <?php endif ?>
                            <?php endforeach ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <?= Yii::t('app', 'user base help11'); ?>
                    <?php endif ?>
                </div>
            </div>
        </div>
        <!--用户日志-->
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading"><span
                        class="glyphicon glyphicon-th-large"></span> <?= Yii::t('app', 'user view log') ?></div>
                <div class="panel-body">
                    <?php if ($canOperateLog || $canLoginLog || $canDetailLog || $canPayList || $canTransferList || $canCheckoutList || $canCdr): ?>
                        <ul class="nav nav-tabs" id="tab" data-ng-init="logType='operate'">
                            <?php if ($canOperateLog): ?>
                                <li class="active"><a href="#"
                                                      ng-click="logType='operate'"><?= Yii::t('app', 'Operate Log') ?></a>
                                </li><?php endif ?>
                            <?php if ($canLoginLog): ?>
                                <li><a href="#" ng-click="logType='login'"><?= Yii::t('app', 'Login Log') ?></a>
                                </li><?php endif ?>
                            <?php if ($canDetailLog): ?>
                                <li><a href="#"
                                       ng-click="logType='detail'"><?= Yii::t('app', 'Detail Log') . '(' . Yii::t('app', 'this month') . ')' ?></a>
                                </li><?php endif ?>

                            <?php if ($canPayList): ?>
                                <li><a href="#" ng-click="logType='pay'"><?= Yii::t('app', 'Financial PayList') ?></a>
                                </li><?php endif ?>

                            <?php if ($canTransferList): ?>
                                <li><a href="#"
                                       ng-click="logType='transfer'"><?= Yii::t('app', 'Financial TransferList') ?></a>
                                </li><?php endif ?>
                            <?php if ($canCheckoutList): ?>
                                <li><a href="#"
                                       ng-click="logType='checkout'"><?= Yii::t('app', 'Financial CheckoutList') ?></a>
                                </li><?php endif ?>
                            <?php if ($canProchangeList): ?>
                                <li><a href="#"
                                       ng-click="logType='prochange'"><?= Yii::t('app', 'Product Change Log') ?></a>
                                </li><?php endif ?>
                            <!--                            -->
                            <?php //if($canCdr): ?><!--<li><a href="#" ng-click="logType='cdr'">-->
                            <? //= Yii::t('app', 'log/c-d-r/index')?><!--</a></li>--><?php //endif ?>
                        </ul>
                        <div class="tab-content" data-ng-init="user_name='<?= $model->user_name ?>'">
                            <!--操作日志-->
                            <div ng-show="logType=='operate'">
                                <?php if (!empty($operateContent)): ?>
                                    <div class="page">
                                        <div class="side-timline-container">
                                            <section class="side-timeline">
                                                <?= $operateContent ?>
                                            </section>
                                        </div>
                                    </div>
                                    <footer class="table-footer">
                                        <div class="row">
                                            <div class="col-sm-12" style="text-align: center; padding-bottom: 30px;">
                                                <?= Html::a(Yii::t('app', 'user base help8'), ['/log/operate/index', 'target' => $model->user_name]) ?>
                                            </div>
                                        </div>
                                    </footer>
                                <?php else: ?>
                                    <div class="page">
                                        <?= Yii::t('app', 'user base help10') ?>
                                    </div>
                                <?php endif ?>
                            </div>
                            <!--操作日志结束-->
                            <!--其他几种动态展示-->
                            <div id="log_cdra" ng-hide="logType=='operate'">

                                <table class="table table-hover" data-ng-show="showBody.length>0">
                                    <thead>
                                    <th ng-repeat="key in showHead track by $index">{{key}}</th>
                                    </thead>
                                    <tbody>
                                    <tr data-ng-repeat="item in showBody  track by $index">
                                        <td ng-repeat="value in item  track by $index">{{value}}</td>
                                    </tr>
                                    </tbody>
                                </table>

                                <footer class="table-footer" data-ng-show="showBody.length>0">
                                    <div class="row">
                                        <div class="col-sm-12" style="text-align: center; padding-bottom: 30px;">
                                            <div data-ng-show="showBody.length>=20 && logType=='login'">
                                                <?= Html::a(Yii::t('app', 'user base help8'), ['/log/login/index', 'user_name' => $model->user_name]) ?>
                                            </div>
                                            <div data-ng-show="showBody.length>=10 && logType=='detail'">
                                                <?= Html::a(Yii::t('app', 'user base help84444'), ['/log/detail/index', 'user_name' => $model->user_name, 'start_add_time' => date('Y-m-01')]) ?>
                                            </div>
                                            <div data-ng-show="showBody.length>=10 && logType=='pay'">
                                                <?= Html::a(Yii::t('app', 'user base help8'), ['/financial/pay/list', 'user_name' => $model->user_name]) ?>
                                            </div>
                                            <div data-ng-show="showBody.length>=10 && logType=='transfer'">
                                                <?= Html::a(Yii::t('app', 'user base help8'), ['/financial/transfer/list', 'user_name_from' => $model->user_name]) ?>
                                            </div>
                                            <div data-ng-show="showBody.length>=10 && logType=='checkout'">
                                                <?= Html::a(Yii::t('app', 'user base help8'), ['/financial/checkout/list', 'user_name' => $model->user_name]) ?>
                                            </div>
                                            <div data-ng-show="showBody.length>=10 && logType=='prochange'">
                                                <?= Html::a(Yii::t('app', 'user base help8'), ['/log/prochange/index', 'user_name' => $model->user_name]) ?>
                                            </div>
                                            <div data-ng-show="showBody.length>=10 && logType=='cdr'">
                                                <?= Html::a(Yii::t('app', 'user base help8'), ['/log/c-d-r/index', 'user_name' => $model->user_name]) ?>
                                            </div>
                                        </div>
                                    </div>
                                </footer>

                                <div style="padding: 15px 0;" data-ng-show="showBody.length==0">
                                    <?= Yii::t('app', 'user base help10'); ?>
                                </div>
                            </div>
                        </div>
                    <?php endif ?>
                </div>
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

<script>
    window.onload = function () {
        if (parseFloat("<?=$fee?>") > 0) {
            $(".dedfee").show();
        } else {
            $(".dedfee").hide();
        }
    }
    function stopProtect(username) {
        $('#res').hide();
        var num = $("input[name='num']").val();
        var type = $("select[name='type1']").val();
        var money = $("input[name='money']").val();
        $.ajax({
            url: '/user/base/operate',
            type: 'POST',
            data: 'action=stopToProtect&user_name=' + username + '&num=' + num + '&type=' + type + '&money=' + money,
            success: function (res) {
                if (res != "<?= Yii::t('app', 'money shortage')?>") {
                    $('#res').html(res);
                }
                $('#res').show();
            }
        })
    }

    function rechargeKey(username) {
        $('#key_res').hide();
        var key = $("input[name='key']").val();
        $.ajax({
            url: '/user/base/operate',
            type: 'POST',
            data: 'action=rechargeKey&user_name=' + username + '&key=' + key,
            success: function (res) {
                $('#key_res').html(res);
                $("input[name='key']").val('');
                $('#key_res').show();
            }
        })
    }
    function getDedfee(product_id, user_name) {
        $('#nowres').html('');
        var dedtype = $("select[name='dedFeeType']").val();
        $.ajax({
            url: '/user/base/getprofee',
            type: 'POST',
            data: 'product_id=' + product_id + '&user_name=' + user_name,
            dataType: 'json',
            success: function (msg) {
                var fee = msg.checkout_amount;
                $("input[name='fee']").val(fee);
                $("input[name='halffee']").val(msg.checkout_amount_byhalf);
                $("input[name='daysfee']").val(msg.checkout_amount_byday);
                if (fee > 0) {
                    var daysfee = msg.checkout_amount_byday;
                    var halffee = msg.checkout_amount_byhalf;
                    msg = feedByDiscount(fee, dedtype, halffee, daysfee);
                    $(".dedfee").show();
                    $("#dedfeeamount").val(msg);
                } else {
                    $(".dedfee").hide();
                }
            }
        })
    }
    function getDedType() {
        $('#nowres').html('');
        var dedtype = $("select[name='dedFeeType']").val();
        var fee = $("input[name='fee']").val();
        var halffee = $("input[name='halffee']").val();
        var daysfee = $("input[name='daysfee']").val();
        var msg = feedByDiscount(fee, dedtype, halffee, daysfee);
        $("#dedfeeamount").val(msg);
    }
    function feedByDiscount(fee, dedtype, halffee, daysfee) {
        var mydate = new Date();
        var dat = mydate.getDate();
        var msg = fee;
        if (dedtype == 'halffee') {
            var msg = halffee;
        } else if (dedtype == 'byday') {
            var msg = daysfee;
        } else if (dedtype == 'nonded') {
            var msg = 0;
        }
        $("input[name='discount']").val(msg / fee);
        return msg;
    }

    function changeProductNow(user_name, user_id) {
        if (typeof $("#dedfeeamount") !== 'undefined') {
            var amount = $("#dedfeeamount").val();
        } else {
            var amount = 0;
        }


        if (confirm("<?=Yii::t('app','confirm change product')?>" + "<?=Yii::t('app','ded fee msg')?>" + amount + "<?=Yii::t('app','currency')?>")) {
            $('#nowres').html('');
            var pid_from = $("select[name='pid_from_now']").val();
            var pid_to = $("select[name='pid_to_now']").val();
            var discount = $("input[name='discount']").val();
            var fee = $("input[name='fee']").val();
            var is_change = true;
            $.ajax({
                url: '/log/prochange/ajax-get-next-product',
                type: 'POST',
                data: {"user_id": user_id, "product_id": pid_from},
                async: false,
                success: function (msg) {
                    if (msg) {
                        if (confirm("<?=Yii::t('app','change product help1')?>")) {
                            $.ajax({
                                url: '/log/prochange/ajax-del-next-product',
                                type: 'POST',
                                data: {"user_id": user_id, "product_id": pid_from}
                            });
                        } else {
                            is_change = false;
                        }
                    }
                }
            });
            if (!is_change) {
                return false;
            }
            $.ajax({
                url: '/user/base/operate',
                type: 'POST',
                data: 'action=changeProductNow&user_name=' + user_name + '&user_id=' + user_id + '&pid_from=' + pid_from + '&pid_to=' + pid_to + '&discount=' + discount + "&fee=" + fee + "&amount=" + amount,
                success: function (res) {
                    alert(res);
                    location.reload();
                    //$('#nowres').html(res);
                }
            })
        }
    }
    function changeProductNext(user_name, user_id) {
        if (confirm("<?=Yii::t('app','confirm change product')?>")) {
            var pid_from = $("select[name='pid_from_next']").val();
            var pid_to = $("select[name='pid_to_next']").val();

            var is_change = true;
            $.ajax({
                url: '/log/prochange/ajax-get-next-product',
                type: 'POST',
                data: {"user_id": user_id, "product_id": pid_from},
                async: false,
                success: function (msg) {
                    if (msg) {
                        if (confirm("<?=Yii::t('app','change product help1')?>")) {
                            $.ajax({
                                url: '/log/prochange/ajax-del-next-product',
                                type: 'POST',
                                data: {"user_id": user_id, "product_id": pid_from}
                            });
                        } else {
                            is_change = false;
                        }
                    }
                }
            });
            if (!is_change) {
                return false;
            }
            $.ajax({
                url: '/user/base/operate',
                type: 'POST',
                data: 'action=changeProductNext&user_name=' + user_name + '&user_id=' + user_id + '&pid_from=' + pid_from + '&pid_to=' + pid_to,
                success: function (res) {
                    alert(res);
                    location.reload();
                    //$('#nextres').html(res);
                }
            })
        }
    }
    function changeProductAppoint(user_name, user_id) {
        var pid_from = $("select[name='pid_from_appoint']").val();
        var pid_to = $("select[name='pid_to_appoint']").val();
        var change_date = $("input[name='change_time']").val();
        if (change_date == '') {
            alert("<?=Yii::t('app','select change date')?>");
            return false;
        }
        if (confirm("<?=Yii::t('app','confirm change product')?>")) {
            var is_change = true;
            $.ajax({
                url: '/log/prochange/ajax-get-next-product',
                type: 'POST',
                data: {"user_id": user_id, "product_id": pid_from},
                async: false,
                success: function (msg) {
                    if (msg) {
                        if (confirm("<?=Yii::t('app','change product help1')?>")) {
                            $.ajax({
                                url: '/log/prochange/ajax-del-next-product',
                                type: 'POST',
                                data: {"user_id": user_id, "product_id": pid_from}
                            });
                        } else {
                            is_change = false;
                        }
                    }
                }
            });
            if (!is_change) {
                return false;
            }
            $.ajax({
                url: '/user/base/operate',
                type: 'POST',
                data: 'action=changeProductAppoint&user_name=' + user_name + '&user_id=' + user_id + '&pid_from=' + pid_from + '&pid_to=' + pid_to + '&change_date=' + change_date,
                success: function (res) {
                    alert(res);
                    location.reload();
                    //$('#nextres').html(res);
                }
            })
        }
    }

    //动态条件
    function selectCondition(key) {
        //清空输入框
        $("#condition_key_new").html('');
        $("#condition_value").val('');
        $("input[name=condition_key]").val('');
        $("#condition_key").html('');
        $("input[name='condition_value']").val("");
        $("input[name=condition_group_id]").val('');
        $("input[name=condition_products_id]").val('');
        $("#condition_group_id").hide();
        $("#condition_products_id").hide();
        if (key !== '') {
            if (key.indexOf('{USER_NAME}') != -1) {
                var key_new = key.replace('{USER_NAME}', "<?=$model->user_name?>");
                $("#condition_key_new").html(key_new);
                $.ajax({
                    url: '/strategy/condition/get-value-by-user',
                    type: 'POST',
                    data: {"user_name": "<?=$model->user_name?>", "key": key_new},
                    success: function (res) {
                        $("#condition_value").val(res);
                    }
                });
            }
            if (key.indexOf('{GROUP_ID}') != -1) {
                $("#condition_group_id").show();
            } else {
                $("#condition_group_id").hide();
            }
            if (key.indexOf('{PRODUCTS_ID}') != -1) {
                $("#condition_products_id").show();
            } else {
                $("#condition_products_id").hide();
            }
            $("input[name=condition_key]").val(key);
            $("#condition_key").html(key);
            $("#condition_key").show();
        }
    }
</script>




