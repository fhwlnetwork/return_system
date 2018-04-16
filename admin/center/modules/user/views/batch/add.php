<?php

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

$this->title = Yii::t('app', 'Batch Add');

$attributes = $model->getAttributesList();

//绑定产品
$canBindProduct = Yii::$app->user->can('user/group/bind-product');
//ztree
\center\assets\ZTreeAsset::register($this);
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

<div class="page page-table">
    <?= \center\widgets\Alert::widget() ?>
    <section class="panel panel-default">
        <div class="panel-heading"><span class="glyphicon glyphicon-th-large"></span> <?= Yii::t('app', 'Batch Add') ?></div>
        <div class="panel-body">

        <?php $form = ActiveForm::begin([
            'id' => 'add',
            'layout' => 'horizontal',
            'fieldConfig' => [
                'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}",
                'horizontalCssClasses' => [
                    'label' => 'col-sm-2',
                    'offset' => 'col-sm-offset-1',
                    'wrapper' => 'col-sm-8',
                    'error' => '',
                    'hint' => '',
                ],
            ],
        ]); ?>

            <div class="form-group">
                <label class="control-label col-sm-2"><?= Yii::t('app','batch add pre')?></label>
                <div class="col-sm-10">
                    <div class="row">
                        <?= $form->field($model, 'pre', [
                            'template' => "{input}",
                            'options' => [
                                'class' => 'col-sm-3'
                            ]
                        ])->label(false); ?>
                        <div class="col-sm-6"><div class="help-block"><?= Yii::t('app','batch add help10')?></div></div>
                    </div>
                    <div class="help-block help-block-error"></div>
                </div>
            </div>

            <div class="form-group field-batchadd-user_start field-batchadd-user_stop required">
                <label for="batchadd-user_start" class="control-label col-sm-2"><?= Yii::t('app', 'batch add user start2') ?></label>
                <div class="col-sm-10">
                    <div class="row">
                        <?=$form->field($model, 'user_start', [
                            'template' => "{input}",
                            'options'=>[
                                'class' => 'col-sm-3',
                            ],
                            'inputOptions' => [
                                'placeholder' => Yii::t('app', 'batch add user start'),
                            ],
                        ])->label(false)
                        ?>

                        <?=$form->field($model, 'user_stop', [
                            'template' => "{input}",
                            'options'=>['class' => 'col-sm-3'],
                            'inputOptions' => [
                                'placeholder' => Yii::t('app', 'batch add user stop'),
                            ],
                        ])->label(false)
                        ?>
                        <div class="col-sm-3"><div class="help-block"><?= Yii::t('app', 'batch add help4')?></div></div>
                    </div>
                    <div class="help-block help-block-error"></div>
                </div>
            </div>

            <div class="form-group field-batchadd-num_len field-batchadd-user_gen_method required">
                <label for="batchadd-num_len" class=" control-label col-sm-2"><?= Yii::t('app', 'batch add num len') ?></label>
                <div class="col-sm-10">
                    <div class="row">
                        <?=$form->field($model, 'num_len', [
                            'template' => "{input}",
                            'options'=>[
                                'class' => 'col-sm-3',
                            ],
                            'inputOptions' => [
                                'type' => 'number'
                            ]
                        ])->label(false)
                        ?>

                        <?=$form->field($model, 'user_gen_method', [
                            //'template' => "{input}",
                            'options'=>['class' => 'col-sm-9'],
                            'horizontalCssClasses' => [
                                'label' => 'col-sm-3',
                                'offset' => 'col-sm-offset-1',
                                'wrapper' => 'col-sm-9',
                                'error' => '',
                                'hint' => '',
                            ],
                        ])->inline()->radioList($attributes['user_gen_method'])
                        ?>
                    </div>
                    <div class="help-block help-block-error"></div>
                </div>
            </div>

        <?= $form->field($model, 'suffix' ); ?>

            <div class="form-group field-batchadd-user_password required">
                <label for="batchadd-user_pass_value" class="control-label col-sm-2"><?=Yii::t('app', 'batch add user password')?></label>
                <div class="col-sm-10">
                    <div class="row">
                        <?=$form->field($model, 'user_password', [
                            'template' => "{input}",
                            'options'=>['class' => 'col-sm-3'],
                            'inputOptions' => [
                                'data-ng-model' => 'user_password',
                                'data-ng-init' => 'user_password='.$model->user_password,
                            ],
                        ])->dropDownList($attributes['user_password'])
                            ->label(false)
                        ?>

                        <?=$form->field($model, 'user_pass_value', [
                            'template' => "{input}",
                            'options'=>[
                                'class' => 'col-sm-3',
                                'data-ng-show' => 'user_password == 1',
                            ],
                            'inputOptions' => [
                                'placeholder' => Yii::t('app', 'batch add user pass value'),
                                'data-ng-model' => 'user_pass_value',
                            ],
                        ])
                        ?>

                        <?=$form->field($model, 'pw_type', [
                            'template' => "{input}",
                            'options'=>[
                                'class' => 'col-sm-3',
                                'data-ng-show' => 'user_password == 2',
                            ],
                        ])->dropDownList($attributes['pw_type'])
                            ->label(false)
                        ?>

                        <?=$form->field($model, 'passwd_len', [
                            'template' => "{input}",
                            'options'=>[
                                'class' => 'col-sm-3',
                                'data-ng-show' => 'user_password == 2',
                            ],
                            'inputOptions' => [
                                'placeholder' => Yii::t('app', 'batch add passwd len'),
                            ],
                        ])
                        ?>
                    </div>
                    <div class="help-block help-block-error"></div>
                </div>
            </div>

        <?= $form->field($model, 'user_allow_chgpass')->inline()->radioList($attributes['user_allow_chgpass']); ?>

        <?= $form->field($model, 'user_expire_time', [
            'inputOptions'=>['class'=>'form-control inputDate'],
            'horizontalCssClasses' => [
                'label' => 'col-sm-2',
                'offset' => 'col-sm-offset-1',
                'wrapper' => 'col-sm-4',
            ],
        ])->hint(Yii::t('app', 'batch add help5')); ?>

        <?= $form->field($model, 'gen_num', [
            'horizontalCssClasses' => [
                'label' => 'col-sm-2',
                'offset' => 'col-sm-offset-1',
                'wrapper' => 'col-sm-4',
            ],
            'inputOptions' => [
                'type' => 'number'
            ]
        ])->hint(Yii::t('app', 'batch add help6')); ?>

        <?= $form->field($model, 'user_available')->inline()->radioList($attributes['user_available']); ?>

        <div class="form-group field-batchadd-balance required">
            <label for="batchadd-balance" class="control-label col-sm-2"><?=Yii::t('app', 'batch add balance')?></label>
            <div class="col-sm-10">
                <div class="row">
                    <?php if(!empty($attributes['payType'])): ?>

                        <?=$form->field($model, 'payType', [
                            'template' => "{input}",
                            'options'=>['class' => 'col-sm-3'],
                        ])->dropDownList($attributes['payType'])
                          ->label(false)
                        ?>

                        <?=$form->field($model, 'balance', [
                            'template' => "{input}",
                            'options'=>[
                                'class' => 'col-sm-3',
                            ],
                            'inputOptions' => [
                                'type' => 'number'
                            ]
                        ])
                        ?>
                    <?php else: ?>
                        <div class="col-sm-6">
                            <p class="form-control-static">
                                <?=Yii::t('app', 'pay help5') . Html::a(Yii::t('app', 'pay help4'), ['/financial/paytype/index']);?>
                            </p>
                        </div>
                    <?php endif ?>

                </div>
                <div class="help-block help-block-error"></div>
            </div>
        </div>
        <div class="help-block help-block-error" style="color: #ffffff;"><?= $form->errorSummary($model); ?></div>
        <div class="form-group">
            <label class="control-label col-sm-2"><?= Yii::t('app', 'organization help4')?></label>
            <div class="col-sm-8">
                <div class="panel panel-default">
                    <div class="panel-body" style="max-height: 500px; overflow-y: auto;">
                        <?= Html::hiddenInput("BatchAdd[group_id]", 0, [
                            'id' => 'zTreeId',
                        ]) ?>
                        <div><?= Yii::t('app', 'organization help5')?><span class="text-primary" id="zTreeSelect"></span></div>
                        
                        <div id="zTreeAddUser" class="ztree"></div>
                    </div>
                </div>
            </div>
        </div>

        <?php /*= $form->field($model, 'products_id')
            ->inline()
            ->checkboxList($productList, [
                'class'=>'drag',
                'itemOptions' => ['labelOptions'=>'',],
                'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}",
            ])
            ->hint(empty($productList)
                ? Yii::t('app', 'product help11') . Html::a(Yii::t('app', 'product help12'), ['strategy/product/index'])
                : Yii::t('app', 'batch add help8'))
        */?>

        <!--订购产品-->

        <div class="form-group field-batchadd-products_id required" id="productAll">
            <label class="control-label col-sm-2" for="batchadd-products_id"><?=Yii::t('app', 'user products id')?></label>
            <div class="col-sm-8">
                <?php if($productList): ?>
                    <div id="batchadd-products_id" class="drag">
                        <?php foreach ($productList as $pid => $pName) : ?>
                            <label for="a" id ="products_<?=$pid?>_label">
                                <div class="row" id ="products_<?=$pid?>">
                                    <div class="col-sm-6">
                                        <input type="checkbox"
                                               name="BatchAdd[products_id][<?= $pid ?>][open]"
                                               data-ng-model="newProduct.item.id<?= $pid ?>"
                                               data-ng-change="show_input(<?= $pid ?>);"
                                               value="1" get-pro="<?= $pid ?>">
                                        <?= $pName ?>
                                    </div>
                                    <!--如果没有缴费方式，那么不显示产品缴费-->
                                    <?php if(!empty($attributes['payType'])): ?>
                                        <div class="col-sm-6">
                                            <div class="row" ng-show="newProduct.item.id<?= $pid ?>==1">
                                                <div class="col-sm-8">
                                                    <div class="input-group">
                                                        <span class="input-group-addon"><?=Yii::t('app', '$')?></span>
                                                        <input type="number" class="form-control"
                                                               name="BatchAdd[products_id][<?= $pid ?>][num]"
                                                               data-ng-model="newProduct.item.num<?= $pid ?>"
                                                               placeholder="<?=Yii::t('app', 'pay amount')?>">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif ?>
                                </div>
                            </label>
                        <?php endforeach ?>
                    </div>
                    <div class="help-block"><?= Yii::t('app', 'batch add help8') ?></div>
                <?php else: ?>
                    <?php //= $form->field($model, 'products_id', ['template'=>'{input}', 'options'=>['class' => 'hide']])->label(false)->hiddenInput()?>
                    <p class="form-control-static"><?= Yii::t('app', 'product help11') . Html::a(Yii::t('app', 'product help12'), ['/strategy/product/index']) ?></p>
                <?php endif ?>
                <div class="help-block help-block-error "></div>
            </div>
        </div>

        <div class="form-group  required">
            <label class="control-label col-sm-2" for="base-user_password2"></label>
            <div class="col-sm-4" >
                <?= Html::submitButton(Yii::t('app', 'submit'), ['class' => 'btn btn-success','onclick'=>'return befor_sub()']) ?>
            </div>
        </div>

        <?php $form->end()?>
    </section>
</div>
    <!--产品实例错误-->
    <div class="modal fade" id="productError" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class ="modal-title text-center"><?= Yii::t('app', 'user group error') ?>
                        <?php if ($canBindProduct): ?>
                            <span style="line-height: 2em;margin-top:10px;padding-left:20px;"><a
                                    style="color:#0000aa;text-decoration: underline;" href="/user/group/bind-product"
                                    target="_blank"><?= Yii::t('app', 'set user group bind') ?></a></span>
                        <?php else:?>
                            <span style="line-height: 2em;margin-top:10px;color:#b13d31; ">
                                  <?= Yii::t('app', 'group msg12') ?></span>
                        <?php endif;?>
                    </h4>


                </div>
            </div>
        </div>
    </div>
<?php
$this->registerJs("
    createTree('zTreeAddUser');
");
?>

<script>
    function befor_sub(){
        var user_num = $("#batchadd-gen_num").val();
        var gro = $('#zTreeId').val();
        var str = '';
        $('input[type="checkbox"]:checked').each(function(){
            str += $(this).attr('get-pro')+',';
        });
        if(gro != 0 && str != '' && user_num != ''){
            $.ajax({
                'url':'/user/batch/ip-nums-ajax',
                'data':{'group_id':gro,'product_id':str},
                'type':'POST',
                success:function(data){
                    //no_num表示没有ip段，直接提交
                    if(data == 'no_num'){
                        $("#add").submit();
                    }
                    //no表示没有ip可用
                    if(data == 'no'){
                        data = 0;
                    }
                    //如果小于，给出提示
                    if(Number(user_num) > Number(data)){
                        firm = confirm('用于分配的ip('+data+'个)小于开户数，是否继续操作?');
                        if(firm == false){
                            return false;
                        }else{
                            $("#add").submit();
                        }
                    }else{
                        //如果大于，或等于，提交
                        $("#add").submit();
                    }
                }
            });
            return false;
        }
    }
</script>