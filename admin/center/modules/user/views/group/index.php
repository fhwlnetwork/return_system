<?php
use center\widgets\Alert;
use center\assets\ZTreeAsset;
use yii\helpers\Html;

$this->title = Yii::t('app', 'User Groups');

//权限
$canBatchRenew = Yii::$app->user->can('user/group/batch-renew');
$canBatchDelete = Yii::$app->user->can('user/group/batch-delete');
$canBatchEnable = Yii::$app->user->can('user/group/batch-enable');
$canBatchDisable = Yii::$app->user->can('user/group/batch-disable');
$canBatchBuy = Yii::$app->user->can('user/group/batch-buy');
$canBatchMacOpen = Yii::$app->user->can('user/group/batch-mac-open');
$canBatchMacClose = Yii::$app->user->can('user/group/batch-mac-close');
$canBatchStop = Yii::$app->user->can('user/group/batch-stop');

$type = ['days'=>'天','week'=>'周','months'=>'月'];

//ztree
ZTreeAsset::register($this);
$this->registerJsFile('/js/ztree_select_multi.js', ['depends' => [center\assets\ZTreeAsset::className()]]);
?>
<style type="text/css">
    .ztree li a.curSelectedNode span {
        background-color: #0088cc;
        color: #fff;
        border-radius: 2px;
        padding: 2px;
    }
</style>
<div class="page page-table" data-ng-controller="userProfileCtrl">
<?= Alert::widget() ?>
    <section class="panel panel-default">
        <div class="panel-heading"><strong><span class="glyphicon glyphicon-th-large"></span> <?=Yii::t('app', 'batch operate')?></strong></div>
        <div class="panel-body">
            <?php $form = \yii\bootstrap\ActiveForm::begin(['id' => 'form']) ?>
            <div class="tab-content">
                <div>
                    <!--选择组织结构-->
                    <div>
                        <div class="divider-xl"></div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="col-md-2">
                                    <?= Yii::t('app', 'organization help4');?>
                                </div>
                                <div class="col-md-10">
                                    <div class="panel panel-default">
                                        <div class="panel-body" style="max-height: 500px; overflow-y: auto;">
                                            <?= Html::hiddenInput("group_id", '', [
                                                'id' => 'zTreeId',
                                            ]) ?>
                                            <div><?= Yii::t('app', 'organization help5')?><span class="text-primary" id="zTreeSelect"></span></div>
                                            <div id="zTreeAddUser" class="ztree"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="col-sm-2"><?=Yii::t('app','batch operate')?></div>
                            <div class="col-sm-10">
                                <div class="row form-group">
                                    <div class="col-md-12">
                                        <?php if($canBatchRenew):?>
                                            <div class="col-sm-2">
                                                <?=Html::button(Yii::t('app', 'batch renew'), ['class' => 'btn btn-success btn-xs',
                                                    'ng-model'=>'batchRenew', 'ng-click'=>"chgBatchDisplay( 'batchRenew', 'renew')"]) ?>
                                            </div>
                                        <?php endif?>
                                        <?php if($canBatchDelete):?>
                                            <div class="col-sm-2">
                                                <?=Html::button(Yii::t('app', 'batch delete'), [
                                                    'class'=>'btn btn-danger btn-xs',
                                                    'onClick' => 'checkGroupBatchOperate(
                                                        "'.Yii::t('app', 'user base help20').'",
                                                        "'.Yii::t('app', 'product help16', ['action' => Yii::t('app', 'batch delete')]).'",
                                                        "batch-delete")'])?>
                                            </div>
                                        <?php endif?>
                                        <?php if($canBatchEnable):?>
                                            <div class="col-sm-2">
                                                <?=Html::button(Yii::t('app', 'batch enable'), [
                                                    'class'=>'btn btn-success btn-xs',
                                                    'onClick' => 'checkGroupBatchOperate(
                                                        "'.Yii::t('app', 'user base help20').'",
                                                        "'.Yii::t('app', 'product help16', ['action' => Yii::t('app', 'batch enable')]).'",
                                                        "batch-enable")'])?>
                                            </div>
                                        <?php endif?>
                                        <?php if($canBatchDisable):?>
                                            <div class="col-sm-2">
                                                <?=Html::button(Yii::t('app', 'batch disable'), [
                                                    'class'=>'btn btn-danger btn-xs',
                                                    'onClick' => 'checkGroupBatchOperate(
                                                        "'.Yii::t('app', 'user base help20').'",
                                                        "'.Yii::t('app', 'product help16', ['action' => Yii::t('app', 'batch disable')]).'",
                                                        "batch-disable")'])?>
                                            </div>
                                        <?php endif?>

                                        <?php if($canBatchStop):?>
                                            <div class="col-sm-2">
                                                <?=Html::button(Yii::t('app', 'batch stop'), [
                                                    'class'=>'btn btn-danger btn-xs',
                                                    'ng-model' => 'batchStop','ng-click' => "chgBatchDisplay( 'batchStop','batch_stop')",
                                                    ])?>
                                            </div>
                                        <?php endif?>

                                        <?php if($canBatchBuy):?>
                                            <div class="col-sm-2">
                                                <?=Html::button(Yii::t('app', 'batch buy'), ['class'=>'btn btn-success btn-xs', 'ng-model'=>'batchBuy'.$id, 'ng-click'=>"chgBatchDisplay( 'batchBuy$id', 'buy')"]) ?>
                                            </div>
                                        <?php endif?>
                                    </div>
                                </div>
                                <div class="row form-group">
                                    <div class="col-md-12">
                                        <?php if($canBatchMacOpen):?>
                                            <div class="col-sm-2">
                                                <?=Html::button(Yii::t('app', 'batch mac auth open'), [
                                                    'class'=>'btn btn-success btn-xs',
                                                    'onClick' => 'checkGroupBatchOperate(
                                                            "'.Yii::t('app', 'user base help20').'",
                                                            "'.Yii::t('app', 'product help16', ['action' => Yii::t('app', 'batch mac auth open')]).'",
                                                            "batch-mac-open")'])?>
                                            </div>
                                        <?php endif?>
                                        <?php if($canBatchMacClose):?>
                                            <div class="col-sm-2">
                                                <?=Html::button(Yii::t('app', 'batch mac auth close'), [
                                                    'class'=>'btn btn-danger btn-xs',
                                                    'onClick' => 'checkGroupBatchOperate(
                                                            "'.Yii::t('app', 'user base help20').'",
                                                            "'.Yii::t('app', 'product help16', ['action' => Yii::t('app', 'batch mac auth close')]).'",
                                                            "batch-mac-close")'])?>
                                            </div>
                                        <?php endif?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div data-ng-show="batchRenew && renew==1">
                        <div class="divider-xl"></div>
                        <div  class="row">
                            <div class="col-md-12">
                                <div class="col-sm-2"></div>
                                <div class="col-sm-3">
                                    <?=Html::dropDownList('product_id', '', $productList, ['class' => 'form-control', 'placeholder'=>Yii::t('app', 'select product'), 'id' => 'product_id'])?>
                                </div>
                            </div>
                        </div>

                        <div class="divider-md"></div>
                        <div  class="row">
                            <div class="col-md-12">
                                <div class="col-sm-2"></div>
                                <div class="col-sm-3">
                                    <?=Html::input('number', 'batchRenewAmount', '',['class' => 'form-control', 'placeholder'=>Yii::t('app', 'batch renew amount').' '.Yii::t('app', 'package help2'), 'id' => 'renew_num'])?>
                                </div>
                                <div class="col-sm-2">
                                    <?=Html::button(Yii::t('app', 'operate'), ['class' => 'btn btn-success', 'onclick' => 'checkGroupBatchRenew("'.Yii::t('app', 'user base help20').'",
                                    "'.Yii::t('app', 'user products id select').'",
                                    "'.Yii::t('app', 'renew msg2').'",
                                    "'.Yii::t('app','product help16',['action'=>Yii::t('app', 'batch renew')]).'"
                                    );']) ?>
                                </div>
                            </div>
                        </div>
                    </div>
<!--                    批量停机保号-->
                    <div data-ng-show="batchStop && renew==2">
                        <form action="" method="post" name="myform" id="myform">
                            <div class="form-group">
                                <div class="divider-xl"></div>
                                <div  class="row">
                                    <div class="col-md-12">
                                        <div class="col-sm-2"></div>
                                        <div class="col-sm-3">
                                            <label for="num">时长</label><input type="text" class="form-control" name="num" id="num">
                                            <select class="form-control" name="type" id="type">
                                                <option value="days">天</option>
                                                <option value="months">月</option>
                                                <option value="years">年</option>
                                            </select><label for="type"></label>
                                            <label for="money">金额(元)</label><input type="text" class="form-control" name="money" id="money">
                                            <br>
                                            <button type="button" class="btn btn-primary" onclick="batchStop(this);">提交</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="row" data-ng-show="batchBuy && renew == 0">
                        <div class="divider-xl"></div>
                        <div  class="row">
                            <div class="col-md-12">
                                <div class="col-sm-2"></div>
                                <div class="col-sm-3">
                                    <?=Html::dropDownList('product_id', '', $productList, ['class' => 'form-control', 'placeholder'=>Yii::t('app', 'select product'), 'id' => 'product_id_package'])?>
                                </div>
                            </div>
                        </div>

                        <div class="divider-md"></div>
                        <div class="col-md-12">
                            <div class="col-sm-2"></div>
                            <div class="col-sm-10">
                                    <?php foreach($packageList as $packageOne): ?>
                                        <?=Html::hiddenInput('product_id', $id);?>
                                        <div class="col-sm-3 form-group">
                                            <div class="">
                                                <label title="<?=Yii::t('app', '$')?> <?= $packageOne['amount']?>">
                                                    <input type="checkbox"
                                                           name="buyPackage[item][]"
                                                           value="<?=$packageOne['package_id']?>"
                                                           onclick="changePackage(<?= $packageOne['package_id']?>, '<?=$packageOne['amount']?>')"
                                                           >
                                                    <?= $packageOne['package_name']?>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach ?>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="col-sm-2"></div>
                            <div class="col-sm-3 form-group checkbox">
                                <span style="padding: 5px;"><?=Yii::t('app', 'renew msg3')?><label id="buyPackageTotal">0</label><?=Yii::t('app', 'currency')?></span>
                            </div>
                            <div class="col-sm-1">
                                <?=Html::button(Yii::t('app', 'operate'), ['class' => 'btn btn-success', 'onclick' => 'checkGroupBatchBuy("'.Yii::t('app', 'user base help20').'",
                                    "'.Yii::t('app', 'user products id select').'",
                                    "'.Yii::t('app', 'group msg3').'",
                                    "'.Yii::t('app','product help16',['action'=>Yii::t('app', 'batch renew')]).'"
                                    );']) ?>
                            </div>
                        </div>
                    </div>
                    <div class="divider-xl"></div>
                </div>
            </div>
            <?php $form->end()?>
        </div>
    </section>
</div>
<?php
$this->registerJs("
    createTree('zTreeAddUser');
");
?>
