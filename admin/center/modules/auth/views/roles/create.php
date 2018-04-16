<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$isEdit = $this->context->action->id == 'update' ? true : false;
$this->title = Yii::t('app', $isEdit ? 'edit roles' : 'add roles');
?>

<div class="padding-top-15px">
    <?= $this->render('/layouts/nav'); ?>

    <div class="col-lg-10">
        <h3 class="page-header">
            <i class="glyphicon glyphicon-pencil"></i>&nbsp;<?= $this->title ?><?php if ($isEdit) echo '：' . $model->name ?>
        </h3>

        <div class="panel panel-default">
            <div class="panel-body" style="padding-top:10px;">
                <?php $form = ActiveForm::begin([
                    'layout' => 'horizontal',
                    'fieldConfig' => [
                        'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{endWrapper}\n{error}",
                        'horizontalCssClasses' => [
                            'label' => 'col-sm-2',
                            'offset' => 'col-sm-offset-2',
                            'wrapper' => 'col-sm-4',
                        ],
                    ],
                ]);?>

                <!--  角色组开始 -->
                <div class="form-group">
                    <label class="control-label col-sm-6" style="text-align: left;">
                        <i class="glyphicon glyphicon-user"></i>&nbsp;<?= Yii::t('app', 'Roles Manager'); ?>
                    </label>

                    <div class="col-sm-4"></div>
                </div>

                <?= $form->field($model, 'name')->textInput(); ?>
                <?= $form->field($model, 'description')->textInput(); ?>
                <!-- 角色组结束 -->

                <!-- 权限组编辑开始 -->
                <div class="form-group">
                    <label class="control-label col-sm-6" style="text-align: left;">
                        <i class="glyphicon glyphicon-star glyphicon"></i>&nbsp;<?= Yii::t('app', 'Permission Edit'); ?>
                    </label>

                    <div class="col-sm-4"></div>
                </div>

                <!-- 加载权限数据 -->
                <?=
                $this->render('permission', [
                    'userPermission' => $userPermission,
                    'userIsSuper' => $userIsSuper,
                    'isEdit' => $isEdit,
                    'items' => $isEdit ? $items : [],
                ]) ?>
                <!-- 权限组编辑结束 -->
            </div>

            <div class="panel-footer">
                <?= Html::submitButton('<i class="glyphicon glyphicon-flag"></i>&nbsp;' . Yii::t('app', 'save'), ['class' => 'btn btn-primary']); ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>