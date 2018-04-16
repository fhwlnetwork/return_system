<?php
use center\widgets\Alert;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = \Yii::t('app', 'batch import');

$action = $this->context->action->id; //动作
$canIndex = Yii::$app->user->can('user/operator/index');
$canImport = Yii::$app->user->can('user/operator/import');
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
                    <?php if($canIndex):?><li <?php if ($action == 'index' || $action == 'edit'): ?>class="active"<?php endif ?>>
                        <?= Html::a(Yii::t('app', 'carrier operator bind mobile'), ['index']) ?>
                    </li><?php endif?>
                    <?php if($canImport):?><li <?php if ($action == 'import'): ?>class="active"<?php endif ?>>
                        <?= Html::a(Yii::t('app', 'batch import'), ['import']) ?>
                    </li><?php endif?>
                    <li <?php if ($action == 'export'): ?>class="active"<?php endif ?>>
                        <?= Html::a(Yii::t('app', 'batch export'), ['export']) ?>
                        </li>
                    <?php if($canBatchEdit):?><li <?php if ($action == 'batch-edit'): ?>class="active"<?php endif ?>>
                        <?= Html::a(Yii::t('app', 'batch edit'), ['batch-edit']) ?>
                        </li><?php endif?>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active">
                        <div class="page page-table">
                            <?= Alert::widget() ?>
                            <div class="panel-body">
                                <div class="col-md-12">
                                    <?php $form = ActiveForm::begin([
                                        'layout' => 'horizontal',
                                        'id' => 'extends-field-form',
                                        'action' => 'export',
                                        'fieldConfig' => [
                                            'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}",
                                            'horizontalCssClasses' => [
                                                'label' => 'col-sm-2',
                                                'wrapper' => 'col-sm-6',
                                                'error' => '',
                                                'hint' => '',
                                            ],
                                        ],
                                        'options' => ['enctype' => 'multipart/form-data']
                                    ]);?>

                                    <?= $form->field($model, 'product_id')->dropDownList($model->products);?>


                                    <div class="col-lg-8 col-sm-offset-3">
                                        <?= Html::submitButton(Yii::t('app', 'batch export'), ['class' => 'btn btn-success']) ?>
                                    </div>

                                    <?php ActiveForm::end(); ?>
                                </div>

                            </div>
                        </div>
                    </div>

                </div>
                </div>
            </div>
        </div>
    </div>
    <!-- 加载接搜结果集页面 -->
</div>



