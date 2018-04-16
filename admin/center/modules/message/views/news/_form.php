<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use center\widgets\Alert;

$canList = Yii::$app->user->can('user/interaction/index');
$canEdit = Yii::$app->user->can('user/interaction/edit');
$canListAll = Yii::$app->user->can('user/interaction/index-all');

if ($action != 'add') {
    $this->title = \Yii::t('app', 'user/interaction/' . $action);
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
                } ?>
            </strong></div>

        <div class="panel panel-default">
            <div class="panel-body">
                <?php if ($action == 'view' || $action == 'edit'): ?>
                    <?php $form = ActiveForm::begin([
                        'layout' => 'horizontal',
                        'options' => ['enctype' => 'multipart/form-data'],
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
                    <?php $form = ActiveForm::begin([
                            'action' => yii\helpers\Url::to('add'),
                            'options' => ['enctype' => 'multipart/form-data'],
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
                <?= $form->field($model, 'title') ?>
                <?= $form->field($model, 'desc') ?>
                <?= $form->field($model, 'type')->dropDownList($types) ?>
                <?= $form->field($model, 'content')->textarea() ?>
                <?php if ($action != 'add'): ?>
                    <div class="form-group field-news-pic">
                        <label class="control-label col-sm-2" for="news-pic">文章图片</label>
                        <div class="col-sm-8">
                            <img src="/<?= $model->pic ?>" alt="" style="width:200px;height:200px;">
                            <?php if ($action == 'edit'): ?>
                                <input type="file" id="news-pic" name="News[pic]">
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else : ?>
                    <?= $form->field($model, 'pic')->fileInput() ?>
                <?php endif; ?>

                <div class="form-group" style="margin-left:220px;">
                    <?php if ($action == 'add'): ?>
                        <?= Html::submitButton(Yii::t('app', 'save'), ['class' => 'btn btn-success']) ?>
                    <?php endif; ?>
                    <?php if ($canList && $action == 'view'): ?>
                        <?= Html::a(Html::button(Yii::t('app', '返回'), ['class' => 'btn btn-primary']),
                            ['index']
                        ) ?>
                    <?php endif; ?>
                    <?php if ($canEdit && $action == 'edit'): ?>
                        <?= Html::submitButton(Yii::t('app', 'save'), ['class' => 'btn btn-success']) ?>
                    <?php endif; ?>
                    <?php if ($canListAll && $action == 'edit' || $action == 'view-all'): ?>
                        <?= Html::a(Html::button(Yii::t('app', '返回'), ['class' => 'btn btn-primary']),
                            ['index']
                        ) ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php ActiveForm::end(); ?>

    </div>
<?php
$isNew = $model->getIsNewRecord();
$js = <<<JS
 $(document).ready(function() {
     if ('$action' == 'view') {
         $('.form-control').css('border', 'none');
     }
 })
JS;
$this->registerJs($js);
