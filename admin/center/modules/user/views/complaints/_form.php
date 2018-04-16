<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use center\widgets\Alert;
use yii\helpers\Url;

$canList = Yii::$app->user->can('user/complaints/index');
$canEdit = Yii::$app->user->can('user/complaints/edit');
$canListAll = Yii::$app->user->can('user/complaints/index-all');

if ($action != 'add') {
    $this->title = \Yii::t('app', 'user/complaints/' . $action);
}
/* @var $this yii\web\View */
/* @var $model center\modules\user\models\UserCloundComplaints */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="col-md-12">
    <?= Alert::widget() ?>
    <div class="panel-heading"><strong>
            <?php if ($action == 'edit') {
                echo '<span class="glyphicon glyphicon-edit"></span> ';
                echo Yii::t('app', 'edit');
            } else if ($action == 'view' || $action == 'view-all') {
                echo '<span class="glyphicon glyphicon-check"></span> ';
                echo Yii::t('app', 'view');
            } else if ($action == 'add') {
                echo '<span class="glyphicon glyphicon-plus"></span> ';
                echo Yii::t('app', 'add');
            } ?>
        </strong></div>

    <div class="panel panel-default">
        <div class="panel-body">
            <?php if ($action == 'view' || $action == 'edit'): ?>
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
            <?php else: ?>
                <?php $form = ActiveForm::begin(['action' => yii\helpers\Url::to('add'), 'options' => ['enctype' => 'multipart/form-data'],
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
                        ],]
                ); ?>
            <?php endif; ?>
            <?php if ($action == 'edit'): ?>
                <?= $form->field($model, 'bug_title')->textInput(['maxlength' => true, 'disabled' => true]) ?>
                <?= $form->field($model, 'bug_type')->dropDownList($bugTypes, ['prompt' => Yii::t('app', 'bug type'), 'disabled' => true]) ?>
                <?= $form->field($model, 'bug_content')->textarea(['rows' => 6, 'disabled' => true]) ?>
                <?= $form->field($model, 'bug_target')->textInput(['maxlength' => true, 'disabled' => true]) ?>
            <?php else: ?>
                <?= $form->field($model, 'bug_title')->textInput(['maxlength' => true,]) ?>
                <?= $form->field($model, 'bug_type')->dropDownList($bugTypes, ['prompt' => Yii::t('app', 'bug type')]) ?>
                <?= $form->field($model, 'bug_content')->textarea(['rows' => 6]) ?>
                <?= $form->field($model, 'bug_target')->textInput(['maxlength' => true]) ?>
            <?php endif; ?>
            <?php if ($action != 'add'): ?>
                <?= $form->field($model, 'bug_solution')->textarea(['rows' => 6]) ?>
                <div class="form-group field-usercloundcomplaints-bug_pub_at">
                    <label class="control-label col-sm-2"
                           for="usercloundcomplaints-bug_pub_at"><?= Yii::t('app', 'hope process time') ?></label>

                    <div class="col-sm-8">
                        <span style="line-height:2.2em;"> <?= $model->hope_process_time; ?></span>

                        <div class="help-block help-block-error "></div>
                    </div>
                </div>
            <?php endif; ?>
            <?php if ($action == 'add'): ?>
                <?= $form->field($model, 'hope_process_time')->textInput(['maxlength' => true, 'class' => 'form-control inputDate']) ?>
            <?php endif; ?>
            <?php if ($action != 'add'): ?>
                <?= $form->field($model, 'bug_state')->inline()->radioList($bugState, ['prompt' => Yii::t('app', 'bug state')]) ?>
            <?php endif; ?>
            <?php if ($action != 'add'): ?>
                <div class="form-group field-usercloundcomplaints-bug_pub_at">
                    <label class="control-label col-sm-2"
                           for="usercloundcomplaints-bug_pub_at"><?= Yii::t('app', 'bug publish time') ?></label>

                    <div class="col-sm-8">
                        <span style="line-height:2.2em;"> <?= $model->bug_pub_at; ?></span>

                        <div class="help-block help-block-error "></div>
                    </div>
                </div>
            <?php endif; ?>
            <?php if (!empty($model->bug_solution_time)): ?>
                <div class="form-group field-usercloundcomplaints-bug_solution_time">
                    <label class="control-label col-sm-2"
                           for="usercloundcomplaints-bug_solution_time"><?= Yii::t('app', 'bug solution time') ?></label>

                    <div class="col-sm-8">
                        <span style="line-height:2.2em;"> <?php echo date('Y-m-d H:i:s', $model->bug_solution_time); ?></span>

                        <div class="help-block help-block-error "></div>
                    </div>
                </div>
            <?php endif; ?>
            <!--bug附件下载-->
            <?php if (!empty($model->bug_attach) && $action == 'view-all' || $action == 'edit'): ?>
                <div class="form-group field-usercloundcomplaints-bug_pub_at">
                    <label class="control-label col-sm-2"
                           for="usercloundcomplaints-bug_pub_at"><?= Yii::t('app', 'bug attach') ?></label>

                    <div class="col-sm-8">
                            <span style="line-height:2.2em;">
                                <?php
                                Yii::$app->session->set('batch_excel_download_file', $model->bug_attach);
                                Yii::$app->getSession()->setFlash('success', '' . Yii::t('app', 'down info1', ['download_url' => Url::to(['/user/group/down-load'])]));
                                echo Yii::$app->getSession()->getFlash('success');
                                ?>
                            </span>

                        <div class="help-block help-block-error "></div>
                    </div>
                </div>
            <?php endif; ?>
            <!--bug附件上传-->
            <?php if ($action == 'add'): ?>
                <?= $form->field($model, 'bug_attach')->fileInput() ?>
            <?php endif; ?>


            <div class="form-group" style="margin-left:220px;">
                <?php if ($action == 'add'): ?>
                    <?= Html::submitButton(Yii::t('app', 'save'), ['class' => 'btn btn-success']) ?>
                <?php endif; ?>
                <?php if ($canEdit && $action == 'edit'): ?>
                    <?= Html::submitButton(Yii::t('app', 'save'), ['class' => 'btn btn-success']) ?>
                <?php endif; ?>
                <?php if ($canList && $action == 'view'): ?>
                    <?= Html::a(Html::button(Yii::t('app', 'goBack'), ['class' => 'btn btn-primary']),
                        ['index']
                    ) ?>
                <?php endif; ?>
                <?php if ($canListAll && $action == 'edit' || $action == 'view-all'): ?>
                    <?= Html::a(Html::button(Yii::t('app', 'goBack'), ['class' => 'btn btn-primary']),
                        ['index-all']
                    ) ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
