<?php
use center\widgets\Alert;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = \Yii::t('app', 'dongrun');

$action = $this->context->action->id; //动作

$attributes = $model->getAttributesList();

?>
<div class="page page-table" data-ng-controller="batch-excel">
    <div>
        <div class="panel panel-default">
            <div class="panel-heading">
                <strong><span class="glyphicon glyphicon-credit-card"></span> <?= Yii::t('app', 'dongrun') ?> </strong>
            </div>
            <div class="panel-body">
                <div class="panel-body">
                    <ul class="nav nav-tabs" id="tab" data-ng-init="batchType=1">
                        <li class="active"><a href="#" ng-click="batchType=1"><?= Yii::t('app', 'batch excel import')?></a></li>
                        <li><a href="#" ng-click="batchType=2"><?= Yii::t('app', 'batch excel update')?></a></li>
                    </ul>
                </div>
            </div>
            <div class="tab-content">
                <div class="divider-md"></div>
                <div class="row">
                    <div class="col-md-12 col-sm-12 col-lg-12">
                        <!--操作流程-->
                        <div class="col-md-2 col-sm-2 col-lg-2">
                            <?= Yii::t('app', 'batch excel tip') ?>
                        </div>
                        <div class="col-md-10 col-sm-10 col-lg-10">
                            <?= Yii::t('app', 'dongrun help4')?>
                        </div>
                    </div>
                </div>
                <div class="tab-content">
                    <div class="tab-pane active">
                        <div class="page page-table" data-ng-controller="payCtrl">
                            <?= Alert::widget() ?>
                            <div class="panel-body">
                                <div class="col-md-12">
                                    <?php $form = ActiveForm::begin([
                                        'layout' => 'horizontal',
                                        'id' => 'extends-field-form',
                                        'action' => '/user/dongrun/index',
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
                                    <?= Html::hiddenInput('batchType', "{{batchType}}")?>

                                    <div class="col-lg-12 col-md-12 col-sm-12">
                                        <div class="divider-xl"></div>
                                        <div class="form-group">
                                            <label class="control-label col-sm-2" for="savingcard-passwd_len"><?= Yii::t('app','dongrun must handle')?></label>
                                            <div class="col-sm-6" ng-show="batchType == 1">
                                                <?= Html::checkboxList('DongrunModel[mustAddExecFields][]', $model->mustAddExecFields, $ssoFields, ['class'=>'drag_inline']) ?>
                                            </div>
                                            <div class="col-sm-6" ng-show="batchType == 2">
                                                <?= Html::checkboxList('DongrunModel[mustEditExecFields][]', $model->mustEditExecFields, $ssoFields, ['class'=>'drag_inline']) ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?= $form->field($model, 'password_type')->dropDownList($attributes['password_type'])?>
                                    <?= $form->field($model, 'state')->textInput(['maxlength' => true, 'placeholder'=>Yii::t('app', 'dongrun action type')]) ?>
                                    <?= $form->field($model, 'user_type')->textInput(['maxlength' => true, 'placeholder'=>Yii::t('app', 'dongrun user type')]) ?>
                                    <div class="col-sm-12 col-md-12 col-lg-12">
                                        <div class="divider-xl"></div>
                                        <div class="form-group">
                                            <label class="control-label col-sm-2" for="savingcard-passwd_len"><?= Yii::t('app','dongrun help2')?></label>

                                            <div class="col-sm-10 col-md-10 col-lg-10">
                                                <?php $i=0;foreach ($userFields as $k => $v): ?>
                                                <?php if ($i == 0): ?>
                                                <div class="row">
                                                    <?php endif; ?>
                                                    <?php if ($i % 3 == 0 && $i != 0): ?>
                                                </div>
                                                <div class="row">
                                                    <?php endif ?>
                                                    <?= '<div class="col-md-3 col-sm-3 col-lg-3">'.$k.'<i class="fa fa-arrow-circle-o-right fa-2"></i> '.$v.'</div>'; ?>
                                                    <?php $i++;endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?= $form->field($model, 'allow_delete')->inline()->radioList([Yii::t('app', 'allow'), Yii::t('app', 'deny')]); ?>
                                    <div class="col-lg-8 col-sm-offset-2">
                                        <p class="text text-danger"><?= Yii::t('app', 'dongrun help8'); ?></p>
                                    </div>
                                    <div style="clear:both;"></div>
                                    <div ng-show="batchType ==1">
                                        <?= $form->field($model, 'sso_add_fields')->textInput(['maxlength' => true, 'placeholder'=>Yii::t('app', 'dongrun help1')]) ?>
                                        <?= $form->field($model, 'user_add_fields')->textInput(['placeholder'=>Yii::t('app', 'dongrun help2')])  ?>
                                    </div>
                                    <div ng-show="batchType ==2">
                                        <?= $form->field($model, 'sso_edit_fields')->textInput(['maxlength' => true, 'placeholder'=>Yii::t('app', 'dongrun help1')]) ?>
                                        <?= $form->field($model, 'user_edit_fields')->textInput(['placeholder'=>Yii::t('app', 'dongrun help2')])  ?>
                                    </div>

                                    <div class="col-lg-8 col-sm-offset-2">
                                        <p class="text text-danger"><?= Yii::t('app', 'dongrun help3'); ?></p>
                                    </div>

                                    <div class="col-lg-8 col-sm-offset-2">
                                        <?= Html::submitButton(Yii::t('app', 'save'), ['class' => 'btn btn-success']) ?>
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
<?php
$this->registerJs("
    $('#tab a').click(function (e) {
          e.preventDefault();//阻止a链接的跳转行为
          $(this).tab('show');//显示当前选中的链接及关联的content

    })
 ");
?>
<script>
    function operatorSave(msg){
        $("input[name='download']").val(msg)
    }
</script>


