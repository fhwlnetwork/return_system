<?php
use center\widgets\Alert;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = \Yii::t('app', 'batch import');

$action = $this->context->action->id; //动作
$canIndex = Yii::$app->user->can('user/operator/index');
$canExport = Yii::$app->user->can('user/operator/export');
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
                    <li <?php if ($action == 'import'): ?>class="active"<?php endif ?>>
                        <?= Html::a(Yii::t('app', 'batch import'), ['import']) ?>
                    </li>
                    <?php if($canExport):?><li <?php if ($action == 'export'): ?>class="active"<?php endif ?>>
                        <?= Html::a(Yii::t('app', 'batch export'), ['export']) ?>
                        </li><?php endif?>
                    <?php if($canBatchEdit):?><li <?php if ($action == 'batch-edit'): ?>class="active"<?php endif ?>>
                        <?= Html::a(Yii::t('app', 'batch edit'), ['batch-edit']) ?>
                        </li><?php endif?>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active">
                        <div class="page page-table" data-ng-controller="payCtrl">
                            <?= Alert::widget() ?>
                            <div class="panel-body">
                                <div class="col-md-12">
                                    <?php $form = ActiveForm::begin([
                                        'layout' => 'horizontal',
                                        'id' => 'extends-field-form',
                                        'action' => 'import',
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

                                    <div class="col-lg-12">
                                        <div class="divider-xl"></div>
                                        <div class="form-group">
                                            <label class="control-label col-sm-2" for="savingcard-passwd_len"><?= Yii::t('app','reference template')?></label>
                                            <div class="col-sm-6">
                                                <?= Html::submitInput(Yii::t('app', 'batch excel download'), ['name' => 'download', 'class' => 'btn btn-default']) ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-12">
                                        <div class="divider-xl"></div>
                                        <div class="form-group">
                                            <label class="control-label col-sm-2" for="savingcard-passwd_len"><?= Yii::t('app','create card by excel')?></label>
                                            <div class="col-sm-6">
                                                <input type="file" name="Operator[file]" title="<?= Yii::t('app', 'batch excel select file')?>" data-ui-file-upload accept=".xls" >
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-8 col-sm-offset-3">
                                        <?= Html::submitButton(Yii::t('app', 'save'), ['class' => 'btn btn-success', 'onclick' => 'operatorSave("'.Yii::t('app', 'batch edit help8').'");']) ?>
                                    </div>


                                    <?php ActiveForm::end(); ?>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="callout callout-info">
                        <h4><?= Yii::t('app', 'batch excel help help');?>：</h4>
                        <hr/>
                        <p class="text text-primary"><?= Yii::t('app', 'batch excel help font5');?></p>
                        <p class="text text-primary"><?= Yii::t('app', 'batch excel help font6');?></p>
                        <p><?= Yii::t('app', 'batch excel help font7');?></p>
                    </div>


                </div>
                </div>
            </div>
        </div>
    </div>
    <!-- 加载接搜结果集页面 -->
</div>



