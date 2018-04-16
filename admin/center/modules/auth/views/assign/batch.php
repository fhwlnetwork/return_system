<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2018/3/9
 * Time: 21:38
 */


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
        <div class="panel-heading"><span class="glyphicon glyphicon-th-large"></span> <?= Yii::t('app', '批量添加学生') ?></div>
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
            <?= $form->field($model, 'begin_time',  [
                'inputOptions'=>['class'=>'form-control inputDate'],
            ]); ?>
            <?= $form->field($model, 'stop_time' , [
                'inputOptions'=>['class'=>'form-control inputDate'],
            ]); ?>
            <?= $form->field($model, 'major_id' )->dropDownList($major); ?>
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

            <div class="form-group  required">
                <label class="control-label col-sm-2" for="base-user_password2"></label>
                <div class="col-sm-4" >
                    <?= Html::submitButton(Yii::t('app', 'submit'), ['class' => 'btn btn-success']) ?>
                </div>
            </div>

            <?php $form->end()?>
    </section>
</div>
<?php
$this->registerJs("
    createTree('zTreeAddUser');
");
?>

<script>

</script>