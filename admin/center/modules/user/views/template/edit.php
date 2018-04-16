<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use center\widgets\Alert;
use center\assets\ZTreeAsset;

$attributes = $model->getAttributesList();
unset($attributes['user_available']['2']);//添加和编辑用户页不让选择停机保号
$isEdit = $action;
$id = Yii::$app->request->get('id');
$this->title = \Yii::t('app', $isEdit ? Yii::t('app', 'User Edit') : Yii::t('app', 'User Add'));

//添加为公共模板
$canAddCommonTemplate = Yii::$app->user->can('user/base/_addCommonTemplate');
//取消产品
$canCancelProduct = Yii::$app->user->can('user/base/cancel-product');
//用户组绑定产品
$canBindProduct = Yii::$app->user->can('user/group/bind-product');
//充值
$canPay = Yii::$app->user->can('financial/pay/index');


//ztree
ZTreeAsset::register($this);
$this->registerJsFile('/js/ztree_select.js', ['depends' => [center\assets\ZTreeAsset::className()]]);
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
                <?php if ($isEdit) {
                    echo '<span class="glyphicon glyphicon-edit"></span> ';
                    echo Yii::t('app', 'edit');
                } else {
                    echo '<span class="glyphicon glyphicon-plus"></span> ';
                    echo Yii::t('app', 'add');
                } ?>
            </strong></div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-12">
                    <?php $form = ActiveForm::begin([
                        'layout' => 'horizontal',
                        'id' => 'Template-edit-form',
                        'action' => $isEdit ? ['edit?id=' . $id] : ['add'],
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


                    <?php
                    echo $form->field($model, 'name', [
                            'inputOptions' => [
                                'placeholder' => Yii::t('app', 'user base help1'),
                                'autocomplete' => 'off',
                                //'value' => Yii::t('app', 'user base help1'),
                                //'onfocus' => 'this.value=""',
                            ],
                        ]);
                    ?>


                    <?= $form->field($model, 'user_allow_chgpass')->inline()->radioList($attributes['user_allow_chgpass']) ?>

                    <?= $form->field($model, 'user_available')->inline()->radioList($attributes['user_available']) ?>


                    <?= $form->field($model, 'user_type')->inline()->dropDownList($attributes['user_type']) ?>

                    <?= $form->field($model, 'user_expire_time', ['inputOptions' => ['class' => 'form-control inputDateTime']])->hint(Yii::t('app', 'user base help4')) ?>
                    <div class="form-group">
                        <label class="control-label col-sm-2"><?= Yii::t('app', 'organization help4') ?></label>

                        <div class="col-sm-8">
                            <div class="panel panel-default">
                                <div class="panel-body">
                                    <?= Html::hiddenInput("Template[group_id]", $model->group_id, [
                                        'id' => 'zTreeId',
                                    ]) ?>
                                    <div><?= Yii::t('app', 'organization help5') ?><span class="text-primary"
                                                                                         id="zTreeSelect"></span></div>
                                    <div style="max-height: 500px; overflow-y: auto;">
                                        <div id="zTreeAddUser" class="ztree"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="form-group field-batchadd-products_id required" id="productAll">
                        <label class="control-label col-sm-2"
                               for="batchadd-products_id"><?= Yii::t('app', 'user products id') ?></label>

                        <div class="col-sm-8">
                            <?php if ($productList): ?>
                                <? $totalStr = '' ?>
                                <div id="batchadd-products_id" class="drag">
                                    <?php foreach ($productList as $pid => $pName) : ?>
                                        <?php $totalStr .= '+newProduct.item.num' . $pid ?>
                                        <?php $totalStr .= '+oneProPackTotal.item.' . $pid ?>
                                        <div data-ng-init="oneProPackTotal.item.<?= $pid ?>=0"></div>
                                        <?php $ordered = array_key_exists($pid, !empty($model->products_id)?$model->products_id:array()); //此产品是否已经订购 ?>
                                        <label id="products_<?=$pid?>_label"  for="a"
                                                                                            style="height: auto;">
                                            <div class="row" id="products_<?=$pid?>">
                                                <div class="col-sm-6">
                                                        <input type="checkbox"
                                                               name="Template[products_id][<?= $pid ?>]"
                                                               id="pro<?= $pid ?>"
                                                               value="1" <?php if ($ordered)echo 'checked';?>>
                                                    <?= $pName ?>
                                                </div>

                                                <div class="col-sm-6">
                                                    <div class="row">
                                                        <!--如果没有缴费方式，那么不显示产品缴费-->
                                                        <?php if ($canPay && !empty($attributes['payType'])): ?>
                                                            <div
                                                                class="col-sm-8" <?php if (!$ordered): ?> ng-show="newProduct.item.id<?= $pid ?>==1" <?php endif ?> >
                                                                <div class="input-group">
                                                                    <span
                                                                        class="input-group-addon"><?= Yii::t('app', '$') ?></span>
                                                                    <input type="number" class="form-control"
                                                                           name="Template[products_id][<?= $pid ?>][num]"
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
                                        </label>
                                    <?php endforeach ?>
                                </div>
                                <div class="help-block"><?= Yii::t('app', 'batch add help8') ?></div>
                            <?php else: ?>
                                <p class="form-control-static"><?= Yii::t('app', 'product help11') . Html::a(Yii::t('app', 'product help12'), ['/strategy/product/index']) ?></p>
                            <?php endif ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-sm-2"><?= Yii::t('app', 'yes no common template') ?></label>
                        <div class="col-sm-8">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" id="billing-saveTem" name="Template[type]" value="1" <?php if ($model->type == 1)echo 'checked';?>>
                                    <?= Yii::t('app', 'as common template') ?>
                                </label>
                            </div>
                        </div>

                    </div>

                    <div class="form-group field- required">
                        <div class="col-sm-10 col-sm-offset-2">
                            <?= Html::submitButton(Yii::t('app', 'save'), ['class' => 'btn btn-success']) ?>
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
                    <h4 class ="modal-title text-center"><?= Yii::t('app', 'user group error') ?>,
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

<?php
//声明ztree当前选中的id
$this->registerJs("
    var currentZTreeId = '" . $model->group_id . "';
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
    function showhidediv() {

        var sbtitle = document.getElementById('form-hide');
        if (sbtitle) {
            if (sbtitle.style.display == 'block') {
                sbtitle.style.display = 'none';
            } else {
                sbtitle.style.display = 'block';
            }
        }
    }
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
        $.ajax({
            url: '/user/base/checkusername',
            type: "POST",
            dataType: "json",
            data: {'username': username},
            success: function (person) {
                if (person) {
                    $('#username_error').html('<?= Yii::t('app', 'batch add help2')?>');
                } else {
                    $('#username_error').html('<?= Yii::t('app', 'user name available')?>');
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
