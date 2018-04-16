<?php
use center\widgets\Alert;
use center\assets\ZTreeAsset;
use yii\helpers\Html;

$session = Yii::$app->session->get('batch_excel');
$this->title = Yii::t('app', 'Batch Excel');
$params = [
    'selectAddField' => $session['addSelectField'] ? $session['addSelectField'] : ['user_name', 'user_password', 'group_id', 'products_id'],
    'selectEditField' => $session['editSelectField'] ? $session['editSelectField'] : ['user_name'],
    'selectExportField' => $session['exportSelectField'] ? $session['exportSelectField'] : ['user_id', 'user_name', 'user_real_name'],
];
$AttributesList = $model->getAttributesList();
//权限
$canAdd = Yii::$app->user->can('user/batch/_excelAdd');
$canEdit = Yii::$app->user->can('user/batch/_excelEdit');
$canDelete = Yii::$app->user->can('user/batch/_excelDelete');
$canExport = Yii::$app->user->can('user/batch/_excelExport');
$canRefund = Yii::$app->user->can('user/batch/_excelRefund');
$canBuy = Yii::$app->user->can('user/batch/_excelBuy');
$canSettleAccounts = Yii::$app->user->can('user/batch/_excelSettleAccounts');
$canBatchRenew = Yii::$app->user->can('user/batch/buy');

if(!$canAdd && !$canEdit && !$canDelete && !$canExport){
    exit('forbid');
}

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

<div class="page page-table" data-ng-controller="batch-excel">
    <?= Alert::widget() ?>
    <section class="panel panel-default">
        <div class="panel-heading"><strong><span class="glyphicon glyphicon-th-large"></span> <?=Yii::t('app', 'Batch Excel')?></strong></div>
        <div class="panel-body">
            <ul class="nav nav-tabs" id="tab" data-ng-init="batchType=1">
                <?php if($canAdd): ?><li class="active"><a href="#" onclick="getType(1)" ng-click="batchType=1"><?= Yii::t('app', 'batch excel import')?></a></li><?php endif ?>
                <?php if($canEdit): ?><li><a href="#" onclick="getType(2)" ng-click="batchType=2"><?= Yii::t('app', 'batch excel update')?></a></li><?php endif ?>
                <?php if($canDelete): ?><li><a href="#" onclick="getType(3)" ng-click="batchType=3"><?= Yii::t('app', 'batch excel delete')?></a></li><?php endif ?>
                <?php if($canExport): ?><li><a href="#" onclick="getType(4)" ng-click="batchType=4"><?= Yii::t('app', 'batch excel export')?></a></li><?php endif ?>
                <?php if($canRefund): ?><li><a href="#" onclick="getType(5)" ng-click="batchType=5"><?= Yii::t('app', 'batch excel refund')?></a></li><?php endif ?>
                <?php if($canBuy): ?><li><a href="#" onclick="getType(6)" ng-click="batchType=6"><?= Yii::t('app', 'buy')?></a></li><?php endif ?>
                <?php if($canSettleAccounts): ?><li><a href="#" onclick="getType(7)" ng-click="batchType=7"><?= Yii::t('app', 'checkout')?></a></li><?php endif ?>
                <?php if($canBatchRenew): ?><li><a href="#" onclick="getType(8)" ng-click="batchType=8"><?= Yii::t('app', 'user/batch/buy')?></a></li><?php endif ?>
            </ul>

            <?php $form = \yii\bootstrap\ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data'], 'id' => 'form', 'action' => 'preview']) ?>

            <?= Html::hiddenInput('batchType', "{{batchType}}")?>

            <div class="tab-content">
                <div class="panel panel-success">
                    <div class="panel-heading"><span
                            class="glyphicon glyphicon-th-large"></span><span id="msg_heading"><?= $msg ?></span>
                    </div>
                </div>
                <div ng-cloak ng-show="batchType==3">
                    <div class="divider-xl"></div>
                    <div class="row">

                        <div class="col-md-12">
                            <div class="col-md-2">
                                <?= Yii::t('app', 'batch excel delete select');?>
                            </div>
                            <div class="col-md-10">
                                <div class="col-md-12">
                                    	<input type="radio" name="deleteType" value="1" ng-click="deleteType=1" checked><?= Yii::t('app', 'batch excel delete file');?>&nbsp;
                                    	<input type="radio" name="deleteType" value="2" ng-click="deleteType=2"><?= Yii::t('app', 'batch excel delete group');?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!--选择购买类型-->
                <div ng-cloak ng-show="batchType==6">
                    <div class="divider-xl"></div>
                    <div class="row">

                        <div class="col-md-12">
                            <div class="col-md-2">
                                <?= Yii::t('app', 'batch excel buy object');?>
                            </div>
                            <div class="col-md-10">
                                <div class="col-md-12">
                                    <input type="radio" name="buyObject" value="1" checked><?= Yii::t('app', 'batch excel buy package');?>
                                    <input type="radio" name="buyObject" value="2" ><?= Yii::t('app', 'transfer type0');?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="divider-md"></div>
                <div class="row">
                    <div class="col-md-12">
                        <!--操作流程-->
                        <div class="col-md-2" ng-show="batchType==1 || batchType==2 || batchType==3 || batchType==4 || batchType==5 || batchType == 7">
                            <?= Yii::t('app', 'batch excel tip')?>
                        </div>
                        <!--操作说明-->
                        <div class="col-md-2" ng-show="batchType==6">
                            <?= Yii::t('app', 'batch excel explain')?>
                        </div>
                        <div class="col-md-10" ng-cloak ng-show="batchType==1 || batchType==2">
                            <?= Yii::t('app', 'batch excel help19');?>
                        </div>
                        <div class="col-md-10" ng-cloak ng-show="batchType==3 && deleteType!=2">
                            <?= Yii::t('app', 'batch excel help20');?>
                        </div>
                        <div class="col-md-10" ng-cloak ng-show="batchType==3 && deleteType==2">
                            <?= Yii::t('app', 'batch excel help20.1');?>
                        </div>
                        <div class="col-md-10" ng-cloak ng-show="batchType==4">
                            <?= Yii::t('app', 'batch excel help21');?>
                        </div>
                        <div class="col-md-10" ng-cloak ng-show="batchType==5">
                            <?= Yii::t('app', 'batch excel help33');?>
                        </div>
                        <div class="col-md-10" ng-cloak ng-show="batchType==6" style="color: red">
                            <?= Yii::t('app', 'batch excel help54');?>
                        </div>
                        <div class="col-md-10" ng-cloak ng-show="batchType==7">
                            <?= Yii::t('app', 'batch excel help56');?>
                        </div>
                    </div>
                </div>


                <!--设置项-->
                <div ng-cloak ng-show="batchType==2">
                    <div class="divider-xl"></div>
                    <div class="row">

                        <div class="col-md-12">
                            <div class="col-md-2">
                                <?= Yii::t('app', 'batch excel setting');?>
                            </div>
                            <div class="col-md-10">
                                <div class="col-md-12">
                                    <?= Html::radioList('setting[pay_where]', 1, [
                                        1=>Yii::t('app', 'batch excel pay_where1'),
                                        2=>Yii::t('app', 'batch excel pay_where2')
                                    ])?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--操作产品动作-->
                    <div class="divider-xl"></div>
                    <div class="row">

                        <div class="col-md-12">
                            <div class="col-md-2">
                                <?= Yii::t('app', 'batch excel setting product action');?>
                            </div>
                            <div class="col-md-10">
                                <div class="col-md-12">
                                    <?= Html::radioList('setting[operate_product_action]', 'set_product', $AttributesList['operate_product_action'])?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div ng-cloak ng-show="batchType==5">
                    <div class="divider-xl"></div>
                    <div class="row">

                        <div class="col-md-12">
                            <div class="col-md-2">
                                <?= Yii::t('app', 'batch excel setting');?>
                            </div>
                            <div class="col-md-10">
                                <div class="col-md-12">
                                    <?= Html::radioList('setting[refund_where]', 1, [
                                        1=>Yii::t('app', 'batch excel refund_where1'),
                                        2=>Yii::t('app', 'batch excel refund_where2')
                                    ])?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div ng-cloak ng-show="batchType==5">
                    <div class="divider-xl"></div>
                    <div class="row">

                        <div class="col-md-12">
                            <div class="col-md-2">
                                <?= Yii::t('app', 'batch_refund_checkout_setting');?>
                            </div>
                            <div class="col-md-10">
                                <div class="col-md-12">
                                    <?= Html::radioList('setting[refund_is_checkout]', 1, [
                                        1=>Yii::t('app', 'yes'),
                                        0=>Yii::t('app', 'no')
                                    ])?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!--勾选要操作的数据，用于新增-->
                <div ng-cloak ng-show="batchType==1">
                    <div class="divider-xl"></div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="col-md-2">
                                <?= Yii::t('app', 'batch excel select');?>
                            </div>
                            <div class="col-md-10">
                                <div class="col-md-10">
                                    <?php unset($model->showField['balance_add'])?>
                                    <?= Html::checkboxList('addSelectField[]', ($session['addSelectField'])?$session['addSelectField']:$params['selectAddField'], $model->showField, ['class'=>'drag_inline']) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div ng-cloak ng-show="batchType==2">
                    <div class="divider-xl"></div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="col-md-2">
                                <?= Yii::t('app', 'batch excel select');?>
                            </div>
                            <div class="col-md-10">
                                <div class="col-md-10">
                                    <?= Html::checkboxList('editSelectField[]', ($session['editSelectField'])?$session['editSelectField']:$params['selectEditField'], $model->showEditField, ['class'=>'drag_inline']) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!--要导出的字段-->
                <div ng-cloak ng-show="batchType==4">
                    <div class="divider-xl"></div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="col-md-2">
                                <?= Yii::t('app', 'batch excel select export');?>
                            </div>
                            <div class="col-md-10">
                                <?= Html::checkboxList('selectExportField[]', $params['selectExportField'], $model->exportField, ['class'=>'drag_inline']) ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!--购买的字段-->
                <div ng-cloak ng-show="batchType==6">
                    <div class="divider-xl"></div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="col-md-2">
                                <?= Yii::t('app', 'batch excel select');?>
                            </div>
                            <div class="col-md-10">
                                <?= Html::checkboxList('selectField[]', array_keys($model->buyField), $model->buyField, ['class'=>'drag_inline']) ?>
                            </div>
                        </div>
                    </div>
                </div>


                <div ng-cloak ng-show="batchType==4">
                    <!--选择用户状态-->
                    <div class="divider-xl"></div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="col-md-2">
                                <?= Yii::t('app', 'select user_available');?>
                            </div>
                            <div class="col-md-10">
                                <?= Html::dropDownList('user_available', null, $model->user_available, ['class' => 'form-control']) ?>
                            </div>
                        </div>
                    </div>
                    <!--选择产品-->
                    <div class="divider-xl"></div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="col-md-2">
                                <?= Yii::t('app', 'select product');?>
                            </div>
                            <div class="col-md-10">
                                <?= Html::dropDownList('product_id', null, [0 => Yii::t('app', 'Please Select')] + $model->can_product, ['class' => 'form-control']) ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!--选择批量操作的类型-->
                <div ng-cloak ng-show="batchType==5 || batchType == 7">
                    <div class="divider-xl"></div>
                    <div class="row">

                        <div class="col-md-12">
                            <div class="col-md-2">
                                <?= Yii::t('app', 'batch excel setting type');?>
                            </div>
                            <div class="col-md-10">
                                <div class="col-md-12">
                                    <?= Html::radioList('setting[get_data_type]', 1, [
                                        1=>Yii::t('app', 'batch excel refund_type1'),
                                        2=>Yii::t('app', 'batch excel refund_type2')
                                    ],
                                    [
                                        'ng-init' => 1,
                                        'ng-model' =>'type_value',
                                        'ng-click' => 'getDataType()',
                                    ])?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!--选择批量操作的类型-->
                <div ng-cloak ng-show="batchType==5">
                    <div class="divider-xl"></div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="col-md-2">
                                <?= Yii::t('app', 'batch excel refund package');?>
                            </div>
                            <div class="col-md-10">
                                <div class="col-md-12">
                                    <?= Html::radioList('setting[isRefundPackages]', 1, [0 => Yii::t('app', 'no'), 1 => Yii::t('app', 'yes')])?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!--选择组织结构-->
                <div ng-show="(batchType==3 && deleteType==2) || batchType==4 || (batchType==5 && type_value==2) || (batchType==7 && type_value==2)">
                    <div class="divider-xl"></div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="col-md-2">
                                <?= Yii::t('app', 'organization help4');?>
                            </div>
                            <div class="col-md-10">
                                <div class="panel panel-default">
                                    <div class="panel-body" style="max-height: 500px; overflow-y: auto;">
                                        <?= Html::hiddenInput("export_group_id", '', [
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
                <div data-ng-show="batchType == 8" class="row">
                    <div class="col-lg-2">
                        <div class="form-group required">
                            <input type="text" class="form-control" name="username"
                                   data-ng-model="username"
                                   placeholder="<?= Yii::t('app', 'account') ?>">
                            <div class="help-block "><?= Yii::t('app', 'batch user buy help')?></div>
                        </div>
                    </div>

                    <div class="col-lg-8">
                        <?=
                        \yii\helpers\Html::submitButton(Yii::t('app', 'search'), [
                            'class' => 'btn btn-success',
                            'data-ng-disabled' => 'searchForm.$invalid'
                        ]) ?>
                    </div>

                </div>

                <!--下载模板-->
                <div ng-hide="deleteType == 2 || batchType == 4 || batchType == 8 || (batchType==5 && type_value == 2) || (batchType==7 && type_value == 2)" class="excel">
                    <div class="divider-xl"></div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="col-md-2">
                                <?= Yii::t('app', 'batch excel download template');?>
                            </div>
                            <div class="col-md-10">
                                <?= Html::submitInput(Yii::t('app', 'batch excel download'), ['name' => 'download', 'class' => 'btn btn-default']) ?>
                                <span ng-cloak ng-show="batchType==1 || batchType==2 "> <?= Yii::t('app', 'batch excel help22');?> </span>
                                <span ng-cloak ng-show="batchType==3 || batchType==5 || batchType==6"> <?= Yii::t('app', 'batch excel help23');?> </span>
                            </div>
                        </div>
                    </div>
                </div>
                <!--上传文件-->
                <div ng-hide="deleteType == 2 || batchType == 4 || batchType == 8 ||  (batchType == 5 && type_value == 2) || (batchType == 7 && type_value == 2)" class="excel">
                    <div class="divider-xl"></div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="col-md-2">
                                <?= Yii::t('app', 'batch excel upload');?>
                            </div>
                            <div class="col-md-2">
                                <input type="file" name="BatchExcel[file]" title="<?= Yii::t('app', 'batch excel select file')?>" data-ui-file-upload accept=".xls" >
                            </div>
                            <div class="col-md-8">
                                <?= Html::a(Yii::t('app', 'batch excel view group id'), ['/auth/structure/index'], ['class' => 'btn btn-warning btn-xs','target' => '_blank']); ?>
                                <?= Html::a(Yii::t('app', 'batch excel view products id'), ['/strategy/product/index'], ['class' => 'btn btn-info btn-xs','target' => '_blank']); ?>
                                <?= Html::a(Yii::t('app', 'batch excel view packages id'), ['/strategy/package/index'], ['class' => 'btn btn-primary btn-xs','target' => '_blank']); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="divider-xl"></div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="col-md-2"></div>
                            <div class="col-md-10" ng-hide="batchType==4 || batchType == 8 || (batchType==5 && type_value == 2) || (batchType==7 && type_value == 2)">
                                <?= Html::submitInput(Yii::t('app', 'preview'), ['name' => 'preview', 'class' => 'btn btn-success']) ?>
                            </div>
                            <div class="col-md-10" ng-cloak ng-show="batchType==4">
                                <?= Html::submitInput(Yii::t('app', 'export'), ['name' => 'export', 'class' => 'btn btn-success']) ?>
                            </div>
                            <div class="col-md-10" ng-cloak ng-show="batchType==5 && type_value == 2 ">
                                <?= Html::hiddenInput('refund','1')?>
                                <?= Html::button(Yii::t('app', 'refund'), ['class' => 'btn btn-success', 'onclick' => 'show_confirm()']) ?>
                            </div>
                            <div class="col-md-10" ng-cloak ng-show="batchType==7 && type_value == 2 ">
                                <?= Html::hiddenInput('refund','2')?>
                                <?= Html::button(Yii::t('app', 'checkout'), ['class' => 'btn btn-success', 'onclick' => 'show_confirm2()']) ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="callout callout-info" ng-cloak ng-show="batchType==1 || batchType==2">
                    <h4><?= Yii::t('app', 'batch excel help help');?>：</h4>
                    <hr/>
                    <p class="text text-primary"><?= Yii::t('app', 'batch excel help font1');?></p>
                    <p class="text text-primary"><?= Yii::t('app', 'batch excel help font2');?></p>
                    <p style="line-height:28px;"><?= Yii::t('app', 'batch excel help font3');?></p>
                    <p class="text text-danger"><?= Yii::t('app', 'batch excel help font4');?></p>

                    <br />
                    <!--<h4><?/*= Yii::t('app', 'batch excel Advanced Options');*/?>：</h4>-->
                    <hr/>
                    <h4><?= Yii::t('app', 'batch excel advanced font1');?></h4>
                    <p><?= Yii::t('app', 'batch excel advanced font2');?></p>

                    <!--<h4>重置用户的产品并缴费：（对新增用户和修改用户适用）</h4>
                    <p>绑定产品：设置用户绑定的产品ID，多个产品ID用英文逗号,分隔，用户需要最少绑定一个产品；格式：比如单产品：1 或者多产品：1,2</p>
                    <p>对绑定产品缴费：如果没有此选项，则不对绑定的产品缴费。此项依赖于上面的绑定产品，多个产品缴费用英文逗号,分隔，按照产品ID来依次匹配缴费，比如按照上面的绑定产品的设置，
                        单产品缴费：10，则会对此用户ID=1的产品缴费10元，多产品：10,20，则会对ID=1的产品缴费10元，对ID=2的产品缴费20元 </p>

                    <h4>在用户原有产品基础上绑定新产品并缴费：（仅适用于于“修改用户”）</h4>
                    <p>绑定新产品：此产品将绑定到用户，绑定多个产品用英文逗号,分开；参考“绑定产品”的格式</p>
                    <p>对新产品缴费：如果没有此项，则不对绑定的新产品缴费。此项依赖于绑定新产品，对多个新产品缴费用英文逗号,分开；参考“对产品缴费”的格式</p>

                    <h4>给用户已有的产品缴费：（仅适用于于“修改用户”）</h4>
                    <p>指定产品ID：缴费给指定的产品ID</p>
                    <p>指定产品缴费金额：依赖于指定产品ID，对此产品缴费的金额</p>-->

                </div>


            </div>
            <?php $form->end()?>
         </div>
    </section>
</div>

<?php
 $this->registerJs("
    $('#tab a').click(function (e) {
          e.preventDefault();//阻止a链接的跳转行为
          $(this).tab('show');//显示当前选中的链接及关联的content

    })
    createTree('zTreeAddUser');
 ");
?>
<script>
    /*function confirm_refund(){
        if(!confirm('确定要批量退费吗？')){
            return false;
        }
    }*/
    function show_confirm()
    {
        if (confirm("确认批量退费吗？"))
        {
            document.getElementById("form").submit();
            //return true;
        }
        else
        {
            return false;
        }
    }
    function show_confirm2()
    {
        if (confirm("<?=Yii::t('app', 'batch settle account')?>"))
        {
            document.getElementById("form").submit();
            //return true;
        }
        else
        {
            return false;
        }
    }
    /**
     * ajax获取操作用户总数
     * @param type
     */
    function getType(type)
    {
        $.ajax({
            type:"GET",
            url:"/user/batch/ajax-get-limit?type="+type,
            dataType:'json',
            success:function (res){
                $('#msg_heading').html(res.msg);
            },
            error: function()
            {
                alert('获取限制总数失败')
            }
        })
    }
</script>