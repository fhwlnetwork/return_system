<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2016/6/15
 * Time: 14:59
 */
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use center\widgets\Alert;
use yii\helpers\Url;

$this->title = \Yii::t('app', 'user/base/transfer');
?>
<div class="page page-table">
    <?= Alert::widget() ?>
    <section class="panel panel-default table-dynamic">
        <div class="panel-heading"><strong><span
                    class="glyphicon glyphicon-th"></span> <?= Yii::t('app', 'user/base/transfer') ?> </strong>
        </div>

        <div class="panel-body">
            <div class="col-md-12">
                <?php $form = ActiveForm::begin([
                    'layout' => 'horizontal',
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
                ]); ?>



                <?= $form->field($transferModel, 'user_name_from')->textInput(['maxlength' => true, 'readonly' => true]) ?>
                <div class="form-group field-transferbalance-balance">
                    <label class="control-label col-sm-2"
                           for="transferbalance-balance"><?= Yii::t('app', 'Electronic wallet') ?></label>

                    <div class="col-sm-8" style="line-height:32px; height:32px;">
                        <?= $model->balance ?>
                    </div>
                </div>

                <?= $form->field($transferModel, 'user_name_to')->textInput(['maxlength' => true,]) ?>
                <?= $form->field($transferModel, 'transfer_num')->textInput(['type' => 'number', 'step' => '0.01']) ?>


                <div class="form-group" style="margin-left:220px;">
                    <?= Html::submitButton(Yii::t('app', 'save'), ['class' => 'btn btn-success']) ?>
                </div>
            </div>
        </div>


        <?php ActiveForm::end(); ?>
    </section>
</div>
