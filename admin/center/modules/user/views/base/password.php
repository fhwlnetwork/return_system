<?php

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

$this->title = Yii::t('app', 'User Edit Pswd');
$attributes = $model->getAttributesList();
?>

<div class="page page-table">
    <?= \center\widgets\Alert::widget() ?>
    <section class="panel panel-default">
        <div class="panel-heading"><span class="glyphicon glyphicon-th-large"></span> <?= Yii::t('app', 'User Edit Pswd') ?></div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-12">
                <?php $form = ActiveForm::begin(['id'=>'search', 'layout' => 'horizontal']); ?>
                    <?= Html::hiddenInput('Base[type]', 'search')?>
                    <div class="col-md-2" data-ng-init = "user_name='<?=$model->user_name?>'">
                        <?= $form->field($model, 'user_name', [
                            'template' => '{input} {error}',
                            'inputOptions' =>[
                                'placeholder' => Yii::t('app', 'account'),
                                'data-ng-model' => 'user_name',
                            ]]) ?>
                    </div>
                    <div class="col-md-2">
                        <?= Html::submitButton(Yii::t('app', 'search'), [
                            'class' => 'btn btn-success',
                            'data-ng-disabled'=>'user_name==""'
                        ])?>
                    </div>
                <?php $form->end() ?>
                </div>
            </div>
            <?php if($model->user_name):?>
                <hr>
                <?php $form = ActiveForm::begin([
                    'id' => 'chgPass',
                    'layout' => 'horizontal',
                    'fieldConfig' => [
                        //'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}",
                        'horizontalCssClasses' => [
                            'label' => 'col-sm-2',
                            'offset' => 'col-sm-offset-1',
                            'wrapper' => 'col-sm-4',
                            'error' => '',
                            'hint' => '',
                        ],
                    ],
                ]); ?>

                <?= Html::hiddenInput('Base[type]', 'edit')?>

                <?= Html::hiddenInput('Base[user_name]', $model->user_name)?>

                <?= $form->field($model, 'user_name', [
                    'template' => "{label}\n{beginWrapper}\n<p class='form-control-static'>".Html::encode($model->user_name)."</p>\n{endWrapper}"
                ]);
                ?>
                <?= $form->field($model, 'user_real_name',[
                    'template' => "{label}\n{beginWrapper}\n<p class='form-control-static'>".Html::encode($model->user_real_name)."</p>\n{endWrapper}"
                ]);?>

                <?php if(isset($model->cert_type) && isset($attributes['cert_type'])){
                    echo $form->field($model, 'cert_type',[
                        'template' => "{label}\n{beginWrapper}\n<p class='form-control-static'>".Html::encode($attributes['cert_type'][$model->cert_type])."</p>\n{endWrapper}"
                    ]);
                }?>
                <?php if(isset($model->cert_num)){
                    echo $form->field($model, 'cert_num',[
                        'template' => "{label}\n{beginWrapper}\n<p class='form-control-static'>".Html::encode($model->cert_num)."</p>\n{endWrapper}"
                    ]);
                }?>
                <?= $form->field($model, 'user_password')->passwordInput(['maxlength' => 64]) ?>

                <?= $form->field($model, 'user_password2')->passwordInput(['maxlength' => 64]) ?>

                <div class="form-group field-base-user_password2 required">
                    <label class="control-label col-sm-2" for="base-user_password2"></label>
                    <div class="col-sm-4">
                        <?= Html::submitButton(Yii::t('app', 'save'), ['class' => 'btn btn-success']) ?>
                    </div>
                </div>

                <?php $form->end()?>
            <?php endif?>
    </section>
</div>