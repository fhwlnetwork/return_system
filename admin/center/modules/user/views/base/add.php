<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use center\widgets\Alert;
use center\assets\ZTreeAsset;
use common\models\User;
//判断非手动录入情况下隐藏密码输入
$pwd_type = $model->pwd_type;
$attributes = $model->getAttributesList();
unset($attributes['user_available']['2']);//添加和编辑用户页不让选择停机保号
$id = Yii::$app->request->get('id');
$this->title = \Yii::t('app', Yii::t('app', 'User Add'));
//保存模板
$add_temp = Yii::$app->user->can('user/template/add-temp');
//选择模板
$choice_temp = Yii::$app->user->can('user/template/choice_temp');
//取消产品
$canCancelProduct = Yii::$app->user->can('user/base/cancel-product');
//用户组绑定产品
$canBindProduct = Yii::$app->user->can('user/group/bind-product');

//判断产品缴费
$canAllow1 = Yii::$app->user->can('financial/pay/allowpay1');
//判断订购套餐权限
$canAllow2 = Yii::$app->user->can('financial/pay/allowpay2');
//判断电子钱包缴费
$canAllow3 = Yii::$app->user->can('financial/pay/allowpay3');
//修改密码
$canChangepwd = Yii::$app->user->can('user/base/password');

$orderedProductNum = count($model->products_id);

//ztree
ZTreeAsset::register($this);
$this->registerJsFile('/js/ztree_select_yesno.js', ['depends' => [center\assets\ZTreeAsset::className()]]);
?>
<style type="text/css">
    .ztree li a.curSelectedNode span {
        background-color: #0088cc;
        color: #fff;
        border-radius: 2px;
        padding: 2px;
    }
</style>
<div class="page page-table" id="body" data-ng-controller="addUserCtrl">
    <?= Alert::widget() ?>
    <section class="panel panel-default">
        <div class="panel-heading"><strong>
                <?= '<span class="glyphicon glyphicon-plus"></span> ';
                echo Yii::t('app', 'add'); ?>
            </strong></div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-12">
                    <?php $form = ActiveForm::begin([
                        'layout' => 'horizontal',
                        'id' => 'base-add-form',
                        'action' => ['add'],
                        'options' => [
                            'onsubmit' => 'return check();'
                        ],
                        'fieldConfig' => [
                            'template' => "{label}\n{beginWrapper}\n{input}\n{error}\n{hint}\n{endWrapper}",
                            'horizontalCssClasses' => [
                                'label' => 'col-sm-2',
                                'offset' => 'col-sm-offset-4',
                                'wrapper' => 'col-sm-8',
                                'error' => '',
                                'hint' => '',
                            ],
                        ],
                    ]);
                    ?>

                    <!--判断选择模板权限-->
                    <?php if($choice_temp): ?>
                        <?php if (count($temNameList) > 1): ?>
                            <div class="form-group field-base-selecttemplate">
                                <label class="control-label col-sm-2"
                                       for="base-selecttemplate"><?= Yii::t('app', 'select template') ?></label>
                                <div class="col-sm-8">
                                    <div class="col-sm-6">
                                        <?= $form->field($model, 'selectTemplate', ['template' => '{input}'])->dropDownList($temNameList) ?>
                                    </div>
                                    <div class="col-sm-6">
                                        <?php
                                        if(isset($_GET['tem']) && !empty($_GET['tem'])){
                                            $isSuper = User::isSuper();
                                            if ($isSuper || (!$isSuper && array_key_exists($model->selectTemplate, $allTemList['self']))) {
                                                echo Html::button(Yii::t('app', 'delete template'), ['class' => 'btn btn-default', 'id' => 'delTem']);
                                            }
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>


                        <?php endif ?>
                    <?php endif ?>

                    <?= $form->field($model, 'user_name', [
                        'inputOptions' => [
                            'placeholder' => Yii::t('app', 'user base help1'),
                            'autocomplete' => 'off',
                            //'value' => Yii::t('app', 'user base help1'),
                            //'onfocus' => 'this.value=""',
                            'onblur' => 'checkusername()',
                        ],
                    ]);
                    ?>
                    <div class="form-group field-base-user_name required" id="error_show" style="display:none;">
                        <label class="control-label col-sm-2" for="base-user_name"></label>
                        <div class="col-sm-8">
                            <span class="text-danger ng-binding" id="username_error"><?= Yii::t('app', 'user name available')?></span>
                        </div>
                    </div>

                    <div class="form-group field-base-selecttemplate">
                        <label class="control-label col-sm-2"
                               for="base-selecttemplate"><?= Yii::t('app', 'pwd_type') ?></label>

                        <div class="col-sm-8">
                            <div class="col-sm-6">
                                <?= $form->field($model, 'pwd_type', ['template' => '{input}'])->dropDownList($pwdTypeList,['onchange'=>'changepw()'])->label(false) ?>

                            </div>
                            <div class="col-sm-6">
                                <?= $form->field($model, 'user_allow_chgpass')->checkbox()?>
                            </div>
                        </div>
                    </div>
                    <div id="pw_div_show" <?php if(isset($pwd_type) && $pwd_type != '1' ){?> style="display: none" <?php }?>>

                    <?= $form->field($model, 'user_password')->passwordInput(['options']) ?>
                    <?= $form->field($model, 'user_confirm_password')->passwordInput(['options']) ?>
                    </div>
                    <?= $form->field($model, 'user_real_name') ?>
                    <!--扩展字段(必填项)-->
                    <?php
                    foreach ($extendField as $one) {
                        if($one['is_must'] != 0){
                            $field = $form->field($model, $one['field_name']);
                            //如果输入类型是数组
                            if ($one['type'] == 1 && isset($attributes[$one['field_name']])) {
                                //如果是下拉框
                                if ($one['show_type'] == 0) {
                                    //如果没有合法的值，再进行赋空值
                                    if(!in_array($model->{$one['field_name']}, array_keys($attributes[$one['field_name']]))){
                                        $model->{$one['field_name']} = '';
                                    }
                                    $attributes[$one['field_name']] = ['' => Yii::t('app', 'Please Select')]+$attributes[$one['field_name']];
                                    echo $field->dropDownList($attributes[$one['field_name']]);
                                } //单选框
                                else if ($one['show_type'] == 1) {
                                    echo $field->inline()->radioList($attributes[$one['field_name']]);
                                } else {
                                    echo $field;
                                }
                            } else {
                                echo $field;
                            }
                        }
                    }
                    ?>
                    <div class="form-group">
                        <div class="help-block help-block-error"
                             style="color: #ffffff;"><?= $form->errorSummary($model); ?></div>
                        <div class="col-sm-10 col-sm-offset-2">
                            <button class="btn btn-info btn-show" type="button" data-ng-model="moreChoice" data-ng-click="moreChoice = !moreChoice">
                                <?= Yii::t('app', 'More Choice') ?>
                                <i class="fa fa-chevron-down" ng-show="!moreChoice"></i>
                                <i class="fa fa-chevron-up ng-hide" ng-show="moreChoice"></i>
                            </button>
                        </div>
                    </div>
                    <div ng-show="moreChoice">
                        <?php
                        if($model->user_available == 2){
                            echo $form->field($model, 'user_available')->inline()->radioList($attributes['stopTypeStatus']);
                        }else{
                            echo $form->field($model, 'user_available')->inline()->radioList($attributes['user_available']);
                        }
                        ?>
                        <?= $form->field($model, 'user_expire_time', ['inputOptions' => ['class' => 'form-control inputDateTime']])->hint(Yii::t('app', 'user base help4')) ?>
                        <?= $form->field($model, 'mobile_phone')->hint(Yii::t('app', 'selected add')) ?>
                        <?= $form->field($model, 'mobile_is_text')->inline()->radioList($attributes['mobile_is_text']) ?>
                        <?= $form->field($model, 'mobile_password')->textInput(['value'=>'******', 'onfocus' => 'this.type="password"', 'ng-model' => 'greeting'])->hint(Yii::t('app', 'selected add') . Yii::t('app', 'user base help21')) ?>
                        <?= Html::hiddenInput("Base[mobile_password_hidden]", "{{greeting || '".$model->mobile_password."'}}") ?>
                        <?= $form->field($model, 'max_online_num')->hint(Yii::t('app', 'user base help22')) ?>

                        <!--免认证账号-->
                        <?= $form->field($model, 'interface_name')->hint(Yii::t('app', 'interface help1')) ?>
                        <!--免认证状态-->
                        <?= $form->field($model, 'interface_status')->inline()->radioList($attributes['interface_status']) ?>

                        <!--扩展字段(选填项)-->
                        <?php
                        foreach ($extendField as $one) {
                            if($one['is_must'] == 0){
                                $field = $form->field($model, $one['field_name']);
                                //如果输入类型是数组
                                if ($one['type'] == 1 && isset($attributes[$one['field_name']])) {
                                    //如果是下拉框
                                    if ($one['show_type'] == 0) {
                                        //如果没有合法的值，再进行赋空值
                                        if(!in_array($model->{$one['field_name']}, array_keys($attributes[$one['field_name']]))){
                                            $model->{$one['field_name']} = '';
                                        }
                                        $attributes[$one['field_name']] = ['' => Yii::t('app', 'Please Select')]+$attributes[$one['field_name']];
                                        echo $field->dropDownList($attributes[$one['field_name']]);
                                    } //单选框
                                    else if ($one['show_type'] == 1) {
                                        echo $field->inline()->radioList($attributes[$one['field_name']]);
                                    } else {
                                        echo $field;
                                    }
                                } else {
                                    echo $field;
                                }
                            }
                        }
                        ?>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-2"><?= Yii::t('app', 'organization help4') ?></label>

                        <div class="col-sm-8">
                            <div class="panel panel-default">
                                <div class="panel-body">
                                    <?= Html::hiddenInput("Base[group_id]", $model->group_id, [
                                        'id' => 'zTreeId',
                                    ]) ?>

                                    <div><?= Yii::t('app', 'organization help5') ?><span class="text-primary" id="zTreeSelect"></span></div>
                                    <div style="max-height: 500px; overflow-y: auto;">
                                        <div id="zTreeAddUser" class="ztree"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php
                        if($canAllow1 || $canAllow2 || $canAllow3){
                            echo $form->field($model, 'payType')->dropDownList($attributes['payType']);
                        }
                     ?>
                    <!--判断电子缴费权限-->
                    <? $totalStr = '' ?>
                    <?php if(!empty($attributes['payType']) && $canAllow3):?>
                    <div class="form-group field-base-balance">
                        <label class="control-label col-sm-2" for="base-balance" ><?=Yii::t('app', 'Electronic wallet')?></label>
                        <div class="col-sm-8">
                            <input
                                type="number"
                                data-ng-init="user_balance ="
                                id="base-balance"
                                class="form-control"
                                data-ng-model="user_balance"
                                name="Base[balance]" value=""
                                placeholder="<?=Yii::t('app', 'user pay money')?>"
                                />
                        </div>
                    </div>
                    <?php $totalStr .= '+user_balance';endif ?>
                    <div class="form-group field-batchadd-products_id required" id="productAll">
                        <label class="control-label col-sm-2"
                               for="batchadd-products_id"><?= Yii::t('app', 'user products id') ?></label>

                        <div class="col-sm-8">
                            <?php if ($productList && $productList['status'] !== '1' ): ?>

                                <!--根据url里的tem值来判断是否隐藏-->
                                <div id="batchadd-products_id" <?php if((!isset($_GET['tem']) || empty($_GET['tem'])) && $yes_no != 'no'){?> style="display:none;"<?php }?> class="drag product_list_hide_">
                                    <?php foreach ($productList as $pid => $pName) : ?>
                                        <?php $totalStr .= '+newProduct.item.num' . $pid ?>
                                        <?php $totalStr .= '+oneProPackTotal.item.' . $pid ?>
                                        <div data-ng-init="oneProPackTotal.item.<?= $pid ?>=0"></div>
                                        <?php $ordered = in_array($pid, $model->products_id); //此产品是否已经订购 ?>
                                        <label id="products_<?=$pid?>_label" <?php if ($ordered) echo 'class="green"'; ?> for="a"
                                                                                            style="height: auto;">
                                            <div class="row" id="products_<?=$pid?>">
                                                <div class="col-sm-6">
                                                    <?php if ($ordered): ?>
                                                        <?= Html::hiddenInput('Base[products_id][' . $pid . '][open]', 1) ?>
                                                    <?php else: ?>
                                                        <input type="checkbox"
                                                               name="Base[products_id][<?= $pid ?>][open]"
                                                               data-ng-model="newProduct.item.id<?= $pid ?>"
                                                               data-ng-change="chgProduct(<?= $pid ?>)"
                                                               id="pro<?= $pid ?>"
                                                               value="1" get-pro="<?= $pid ?>">
                                                    <?php endif ?>
                                                    <?= $pName ?>
                                                </div>

                                                <div class="col-sm-6">
                                                    <div class="row">
                                                        <!--如果没有缴费方式，那么不显示产品缴费-->
                                                        <?php if (!empty($attributes['payType']) && $canAllow1): ?>
                                                            <div
                                                                class="col-sm-8" <?php if (!$ordered): ?> ng-show="newProduct.item.id<?= $pid ?>==1" <?php endif ?> >
                                                                <div class="input-group">
                                                                    <span
                                                                        class="input-group-addon"><?= Yii::t('app', '$') ?></span>
                                                                    <input type="number" class="form-control"
                                                                           name="Base[products_id][<?= $pid ?>][num]"
                                                                           data-ng-model="newProduct.item.num<?= $pid ?>"
                                                                           placeholder="<?= Yii::t('app', 'pay amount') ?>">
                                                                </div>
                                                            </div>
                                                        <?php endif ?>
                                                        <!--取消产品-->
                                                        <?php if ($ordered && $canCancelProduct && $orderedProductNum > 1): ?>
                                                            <div class="col-sm-4">
                                                                <?= Html::a(Yii::t('app', 'user/base/cancel-product'), ['cancel-product', 'user_name' => $model->user_name, 'id' => $pid], ['class' => 'btn btn-danger btn-xs',
                                                                    'data' => [
                                                                        'confirm' => Yii::t('app', 'user base help16')
                                                                    ],
                                                                ]) ?>
                                                            </div>
                                                        <?php endif ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php  if (!empty($packageList) && $canAllow2): ?>
                                                <div class="row" id='packagesFor<?= $pid ?>'
                                                     data-ng-init="amount_<?= $pid ?>=0"
                                                     ng-show="newProduct.item.id<?= $pid ?>==1">
                                                    <div class="col-sm-12"
                                                         style="color: red"><?= Yii::t('app', 'user base help29') ?></div>
                                                    <?php foreach ($packageList as $packageOne): ?>
                                                        <div class="col-sm-4">
                                                            <input type="checkbox"
                                                                   name="Base[products_id][<?= $pid ?>][packages][]"
                                                                   value="<?= $packageOne['package_id'] ?>"
                                                                   data-ng-model="buyPackage.item.id<?= $pid . $packageOne['package_id'] ?>"
                                                                   ng-change="chgPackage(<?= $pid ?>, 'id<?= $pid . $packageOne['package_id'] ?>', <?= $packageOne['amount'] ?>)"
                                                                   class="ng-pristine ng-valid">
                                                            <?= $packageOne['package_name'] ?>
                                                        </div>
                                                    <?php endforeach ?>
                                                </div>
                                            <?php endif ?>
                                        </label>
                                    <?php endforeach ?>

                                </div>
                                <!--product_list_word_hide_|product_list_money_hide_控制提示文字和总金额隐藏（配合默认没有选组之前，产品隐藏）-->
                                <div class="help-block product_list_word_hide_" <?php if((!isset($_GET['tem']) || empty($_GET['tem'])) && $yes_no != 'no'){?> style="display:none;"<?php }?>><?= Yii::t('app', 'batch add help8') ?></div>
                                <!--总金额-->
                                <div class="form-group product_list_money_hide_" <?php if((!isset($_GET['tem']) || empty($_GET['tem'])) && $yes_no != 'no'){?> style="display:none;"<?php }?>>
                                    <div class="row">
                                        <label class="control-label col-sm-2"><?= Yii::t('app', 'pay help11') ?></label>

                                        <div class="col-sm-9 form-control-static">
                                            <span
                                                class="text-danger" id="total_money"><?= '{{' . $totalStr . '>0 ? ' . $totalStr . ' : 0}}' ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <p class="form-control-static"><?= Yii::t('app', 'product help11') . Html::a(Yii::t('app', 'product help12'), ['/strategy/product/index']) ?></p>
                            <?php endif ?>
                        </div>
                    </div>
                    <!--判断保存模板权限-->
                    <?php if($add_temp): ?>
                    <div class="form-group field-saveTem required">
                        <div class="col-sm-8 col-sm-offset-2">
                            <div class="col-sm-3 form-group ">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" id="billing-saveTem" name="Base[saveTem]" value="1"
                                               data-ng-model="saveTem">
                                        <?= Yii::t('app', 'save add template') ?>
                                    </label>
                                </div>
                            </div>
                            <div class="col-sm-6" ng-show="saveTem==1">
                                <div class="form-group field-base-temName">
                                    <div class="col-sm-12">
                                        <input type="text" id="base-temName" class="form-control" name="Base[temName]"
                                               value="" placeholder="<?= Yii::t('app', 'template name') ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-3 form-group" ng-show="saveTem==1">
                                <div class="checkbox">
                                    <label>
                                        <!--<input type="hidden" name="Base[saveTem]" value="0">-->
                                        <input type="checkbox" id="billing-saveTem" name="Base[commonTem]"
                                               value="1">
                                        <?= Yii::t('app', 'as common template') ?>
                                    </label>
                                </div>
                            </div>

                        </div>
                    </div>
                    <?php endif ?>

                    <div class="form-group field-required">
                        <div class="col-sm-10 col-sm-offset-2">
                            <?= Html::submitButton(Yii::t('app', 'save'), ['class' => 'btn btn-success','onclick'=>'return befor_sub()']) ?>
                            <?= Html::a(Yii::t('app', 'list'), ['index'], ['class' => 'btn btn-default']) ?>
                        </div>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </section>
</div>
<!--产品实例错误-->
<div class="modal fade" id="productError" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                    <h4 class ="modal-title text-center">
                        <?php if ($canBindProduct): ?>
                            <span style="line-height: 2em;margin-top:10px;padding-left:20px;"><a
                                        style="color:#0000aa;text-decoration: underline;" href="/user/group/bind-product"
                                        target="_blank"><?= Yii::t('app', 'set user group bind') ?></a></span>
                        <?php else: ?>
                            <span style="line-height: 2em;margin-top:10px;color:#b13d31; ">
                                      <?= Yii::t('app', 'group msg12') ?></span>
                        <?php endif;?>
                    </h4>


            </div>
        </div>
    </div>
</div>
<script>
    //改变，选择密码类型（手工录入）触发事件
    //val值为123456只是为了避免验证时报错，提交后程序会再次生成val值
    function changepw(){
        var pw_type = $("#base-pwd_type option:selected").val();
        if(pw_type == 1){
            $("#base-user_password").val('');
            $("#base-user_confirm_password").val('');
            $("#pw_div_show").show();
        }else if(pw_type == 2){
            var name = $("#base-user_name").val();
            if(name == ''){
                $("#base-user_password").val(123456);
                $("#base-user_confirm_password").val(123456);
            }else{
                $("#base-user_password").val(name);
                $("#base-user_confirm_password").val(name);
            }
            $("#pw_div_show").hide();
        }else if(pw_type == 3){
            $("#pw_div_show").hide();
            $("#base-user_password").val('123456');
            $("#base-user_confirm_password").val('123456');
        }
    }
    //end
</script>
<?php
//声明ztree当前选中的id
/*
 * j声明一个yes_no用来记录，用户添加不依赖绑定关系（默认为空或yes，如果不依赖   no ）
 * */
$this->registerJs("
    var currentZTreeId = '" . $model->group_id . "';
    var yes_no = '" . $yes_no . "';
", yii\web\View::POS_BEGIN);

$this->registerJs("
    $('#base-selecttemplate').change(function(){
        var temValue = $(this).children('option:selected').val();
        if(temValue>=0){
            window.location = '?tem='+temValue;
        }
    });
    $('#delTem').click(function(){
        var temValue = $('#base-selecttemplate').children('option:selected').val();
        if(temValue>0){
            window.location = '?action=delTem&tem='+temValue;
        }
        //alert(1);
    });
    createTree('zTreeAddUser');
");
?>

<script>
    function chgPassword() {
        var obj = document.getElementById('test_ch');
        var str = '';
        if (obj) {
            if (obj.style.display == 'block') {
                obj.style.display = 'none';
            } else {
                obj.style.display = 'block';
                str += "<input id='base-is_edit_password' class='form-control' type='hidden' value='1' name='Base[is_edit_password]'>";
                str += "<div class='form-group field-base-user_new_password'>";
                str += "<label class='control-label col-sm-2' for='base-user_new_password'><?=Yii::t('app', 'User New Password')?></label>";
                str += "<div class='col-sm-8'>";
                str += "<input id='base-user_new_password' class='form-control' type='password' 0='options' name='Base[user_new_password]'>";
                str += '<div class="help-block help-block-error "></div>';
                str += '<div class="help-block "><?=Yii::t("app", "user base help6")?></div>';
                str += "</div></div>";
                str += "<div class='form-group field-base-user_confirm_password'>";
                str += "<label class='control-label col-sm-2' for='base-user_confirm_password'><?=Yii::t('app', 'Confirm Password')?></label>";
                str += "<div class='col-sm-8'>";
                str += "<input id='base-user_confirm_password' class='form-control' type='password'  name='Base[user_confirm_password]'>"
                str +=  "<div class = 'help-block help-block-error'></div>";
                str += "</div></div>";
            }
            obj.innerHTML = str;
        }

    }

    function checkusername() {
        var username = document.getElementById('base-user_name').value;

        if(username == ''){
            return false;
        }
        var ret = /^[a-zA-Z0-9][a-zA-Z0-9@._-]{0,63}$/;
        if(!ret.test(username)){
            return false;
        }
        $.ajax({
            url: '/user/base/checkusername',
            type: "POST",
            dataType: "json",
            data: {'username': username},
            success: function (person) {
                if (person) {
                    $('#error_show').show();
                    $('#username_error').html('<?= Yii::t('app', 'batch add help2')?>');
                } else {
                    $('#error_show').show();
                    $('#username_error').html("<span style='color:#229173;'><?= Yii::t('app', 'user name available')?></span>");
                }
            }
        });
    }
    function check()
    {
        var total = $('#total_money').html();
        if (total > 0) {
            if ($('#base-paytype').val() == 0) {
                var parent = $('#base-paytype').parent().parent();
                parent.removeClass('has-success');
                parent.addClass('has-error');
                //parent.css('border', '1px solid #b13d31');
                alert('<?=Yii::t('app', 'Please Select Pay Type')?>');
                return false;
            }
        }
    }
</script>
<script>
    function befor_sub(){
        //var user_num = $("#batchadd-gen_num").val();
        var gro = $('#zTreeId').val();
        var str = '';
        $('#batchadd-products_id input[type="checkbox"]:checked').each(function(){
            str += $(this).attr('get-pro')+',';
        });
        if(gro != 0 && str !=0 && str != 'undefined'){
            $.ajax({
                'url':'/user/batch/ip-nums-ajax',
                'data':{'group_id':gro,'product_id':str},
                'type':'POST',
                success:function(data){
                    //ippart表没有数据
                    //no_num表示没有ip段，直接提交
                    if(data == 'no_num'){
                        $("#base-add-form").submit();
                    }
                    //no表示没有ip可用
                    if(data == 'no'){
                        data = 0;
                    }
                    //如果小于，给出提示
                    if(1 > Number(data)){
                        firm = confirm('用于分配的ip('+data+'个)小于开户数，是否继续操作?');
                        if(firm == false){
                            return false;
                        }else{
                            $("#base-add-form").submit();
                        }
                    }else{
                        //如果大于，或等于，提交
                        $("#base-add-form").submit();
                    }
                }
            });
            return false;
        }
    }
</script>