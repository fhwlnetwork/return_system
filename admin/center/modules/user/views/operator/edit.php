<?php
use center\widgets\Alert;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

$action = Yii::$app->request->get('action');
$this->title = $action == 'bind' ? Yii::t('app', 'bind account') : Yii::t('app', 'action edit');
$user_name = Yii::$app->request->get('user_name');
$uid = Yii::$app->request->get('uid');
$products_id = Yii::$app->request->get('products_id');
?>

<div class="page page-table">
    <?= Alert::widget() ?>
    <section class="panel panel-default" data-ng-controller="packageController" >
        <div class="panel-heading">
            <strong><span class="glyphicon glyphicon-edit"></span>
                <?= $this->title?></strong>
        </div>
        <div class="panel-body">

            <div class="row">
                <div class="col-md-12">
                    <?php $form = ActiveForm::begin([
                        'layout' => 'horizontal',
                        'id' => 'extends-field-form',
                        'action' => 'edit?user_name='.$user_name.'&uid='.$uid.'&products_id='.$products_id,
                        'fieldConfig' => [
                            'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}",
                            'horizontalCssClasses' => [
                                'label' => 'col-sm-2',
                                'wrapper' => 'col-sm-6',
                                'error' => '',
                                'hint' => '',
                            ],
                        ],
                    ]);?>
                    <?= Html::hiddenInput("Operator[products_id]", $products_id) ?>
                    <?= Html::hiddenInput("Operator[uid]", $uid) ?>
                    <?= $form->field($model, 'user_name')->textInput(['value'=>$user_name, 'readonly'=>ture]);?>
                    <?= $form->field($model, 'product_name')->textInput(['value'=>$proObj['product_name'], 'readonly'=>ture]);?>
                    <?= $form->field($model, 'mobile_phone')->textInput(['value'=>$proObj['mobile_phone_show'] ? $proObj['mobile_phone_show'] : (!empty($proObj['mobile_phone'])?'***********':" "),'autocomplete'=>'off','onfocus'=>'this.value=""']);?>
                    <?= $form->field($model, 'mobile_password')->passwordInput();?>
                    <?= $form->field($model, 'checkout_date', ['inputOptions'=>['class'=>'form-control inputDate']])->textInput(['value'=>$proObj['checkout_date']]);?>
                    <?= $form->field($model, 'user_available')->inline()->radioList($model->getAttributesList()['user_available'],['selected'=>0]);?>
                    <div class="col-lg-8 col-sm-offset-3">
                        <?= Html::submitButton(Yii::t('app', 'save'), ['class' => 'btn btn-success']) ?>
                        <?= Html::a(Yii::t('app', 'goBack'), ['index?user_name='.$user_name], ['class' => 'btn btn-default']) ?>
                    </div>


                    <?php ActiveForm::end(); ?>
                </div>
            </div>

        </div>
    </section>
</div>