<?php
use center\widgets\Alert;
use yii\helpers\Html;
use center\extend\Tool;

$this->title = \Yii::t('app', 'carrier operator business');
$status = $model->getAttributesList()['user_available'];

$action = $this->context->action->id; //动作
$canImport = Yii::$app->user->can('user/operator/import');
$canExport = Yii::$app->user->can('user/operator/export');
$canBatchEdit = Yii::$app->user->can('user/operator/batch-edit');
?>
<div class="page page-table">
    <div>
        <div class="panel panel-default">
            <div class="panel-heading">
                <strong><span class="glyphicon glyphicon-credit-card"></span> <?= Yii::t('app', 'carrier operator business') ?> </strong>
            </div>
            <div class="panel-body">
                <ul class="nav nav-tabs">
                    <li <?php if ($action == 'index' || $action == 'edit'): ?>class="active"<?php endif ?>>
                        <?= Html::a(Yii::t('app', 'carrier operator bind mobile'), ['index']) ?>
                    </li>
                    <?php if($canImport):?><li <?php if ($action == 'import'): ?>class="active"<?php endif ?>>
                        <?= Html::a(Yii::t('app', 'batch import'), ['import']) ?>
                        </li><?php endif?>
                    <?php if($canExport):?><li <?php if ($action == 'export'): ?>class="active"<?php endif ?>>
                        <?= Html::a(Yii::t('app', 'batch export'), ['export']) ?>
                        </li><?php endif?>
                    <?php if($canBatchEdit):?><li <?php if ($action == 'batch-edit'): ?>class="active"<?php endif ?>>
                        <?= Html::a(Yii::t('app', 'batch edit'), ['batch-edit']) ?>
                        </li><?php endif?>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active">
                        <div class="page page-table" data-ng-controller="payCtrl">
                            <?= Alert::widget() ?>
                            <!--搜索用户页面-->
                            <div class="panel-body">
                                <form class="form-validation form-horizontal ng-pristine ng-valid" name="searchForm"
                                      action="<?=\yii\helpers\Url::to(['/user/operator/index'])?>" method="get" role="form">
                                    <div class="col-lg-2" ng-init="user_name='<?=!is_null($userModel) ? $userModel->user_name : ''?>'">
                                        <div class="form-group required">
                                            <input type="text" class="form-control" name="user_name" required
                                                   data-ng-model="user_name"
                                                   value="<?=!is_null($userModel) ? $userModel->user_name : ''?>" placeholder="<?= Yii::t('app', 'account')?>">
                                        </div>
                                    </div>
                                    <div class="col-lg-2">
                                        <?= \yii\helpers\Html::submitButton(Yii::t('app', 'search'), [
                                            'class' => 'btn btn-success',
                                            'data-ng-disabled'=>'searchForm.$invalid'
                                        ])?>
                                    </div>
                                </form>
                                <div class="col-lg-2">
                                    <div class="form-control" style="border: 0">
                                        <?php if($userModel){
                                            echo Yii::t('app', 'name').': ' . $userModel->user_real_name;
                                        }?>
                                    </div>
                                </div>
                            </div>
                            <?php if($userModel): ?>
                                <!--展示用户信息页面-->
                                <div class="panel panel-default">
                                    <div class="panel-body">
                                        <!--如果订购了产品-->
                                        <?php if($lists): ?>
                                            <div class="row">
                                                <!--<div class="col-sm-2">产品信息：</div>-->
                                                <div class="col-sm-12">
                                                    <table class="table">
                                                        <thead>
                                                        <tr>
                                                            <td><?= Yii::t('app', 'products name')?></td>
                                                            <td><?= Yii::t('app', 'mobile phone')?></td>
                                                            <td><?= Yii::t('app', 'Status')?></td>
                                                            <td><?= Yii::t('app', 'sum bytes')?></td>
                                                            <td><?= Yii::t('app', 'sum seconds')?></td>
                                                            <td><?= Yii::t('app', 'user charge')?></td>
                                                            <td><?= Yii::t('app', 'products balance')?></td>
                                                            <td><?= Yii::t('app', 'checkout date')?></td>
                                                            <td><?= Yii::t('app', 'operate')?></td>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        <?php foreach ($lists as $product): ?>
                                                            <tr>
                                                                <td><?= Html::encode($product['product_name'])?></td>
                                                                <td><?= Html::encode($product['mobile_phone_show'] ? $product['mobile_phone_show'] : ($product['mobile_phone']?'***********':'--'))?></td>
                                                                <td><?= Html::encode(isset($product['user_available'])?$status[$product['user_available']]:Yii::t('app','user available0'))?></td>
                                                                <td><?= Html::encode(isset($product['sum_bytes']) ? Tool::bytes_format($product['sum_bytes']) : '--')?></td>
                                                                <td><?= Html::encode(isset($product['sum_seconds']) ? Tool::seconds_format($product['sum_seconds']) : '--')?></td>
                                                                <td><?= Html::encode(isset($product['user_charge']) ? Tool::money_format($product['user_charge']) : '0')?></td>
                                                                <td><?= Html::encode(isset($product['user_balance']) ? Tool::money_format($product['user_balance']) : '0')?></td>
                                                                <td><?= Html::encode(isset($product['checkout_date']) ? $product['checkout_date'] : '--')?></td>
                                                                <td>
                                                                    <?php if(empty($product['mobile_phone'])):
                                                                        echo Html::a(Html::button(Yii::t('app','bind account'),['class'  => 'btn btn-success btn-xs']), ['edit','user_name'=>$userModel->user_name, 'uid'=>$product['user_id'], 'products_id'=>$product['products_id'], 'action' => 'bind']);
                                                                    else:
                                                                        echo Html::a(Html::button(Yii::t('app','action edit'),['class'  => 'btn btn-info btn-xs']), ['edit','user_name'=>$userModel->user_name, 'uid'=>$product['user_id'], 'products_id'=>$product['products_id'], 'action' => 'edit']);
                                                                        echo ' '.Html::a(Html::button(Yii::t('app','relieve bind account'),['class'  => 'btn btn-danger btn-xs']), ['edit','user_name'=>$userModel->user_name, 'uid'=>$product['user_id'], 'products_id'=>$product['products_id'], 'action' => 'relieve']);
                                                                    endif ?>

                                                                    <?php if(isset($product['user_available'])):
                                                                        if($product['user_available'] == $model::STATUS_S):
                                                                            echo Html::a(Html::button(Yii::t('app','carrier operator available1'),['class'  => 'btn btn-warning btn-xs']), ['edit','user_name'=>$userModel->user_name, 'uid'=>$product['user_id'], 'products_id'=>$product['products_id'], 'action' => 'close']);
                                                                        else:
                                                                            echo Html::a(Html::button(Yii::t('app','carrier operator available0'),['class'  => 'btn btn-primary btn-xs']), ['edit','user_name'=>$userModel->user_name, 'uid'=>$product['user_id'], 'products_id'=>$product['products_id'], 'action' => 'start']);
                                                                        endif;
                                                                    endif?>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        <?php endif ?>
                                    </div>
                                </div>
                            <?php endif ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- 加载接搜结果集页面 -->
</div>



