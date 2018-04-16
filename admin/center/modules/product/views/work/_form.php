<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2018/3/18
 * Time: 16:42
 */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use center\widgets\Alert;

$canList = Yii::$app->user->can('user/interaction/index');
$canEdit = Yii::$app->user->can('user/interaction/edit');
$canList = Yii::$app->user->can('product/work/list');
$this->title = \Yii::t('app', 'product/work/' . $action);

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
                <?= $form->field($model, 'company_name') ?>
                <?= $form->field($model, 'salary') ?>
                <?= $form->field($model, 'work_name') ?>
                <?= $form->field($model, 'Byqxdm') ?>
                <?= $form->field($model, 'Dwzzjgdm') ?>
                <?= $form->field($model, 'Dwxzdm') ?>
                <?= $form->field($model, 'Dwhydm') ?>
                <?= $form->field($model, 'Dwszddm') ?>
                <?= $form->field($model, 'Gzzwlbdm') ?>

                <?= $form->field($model, 'stime', [
                    'inputOptions' => [
                        'class' => 'form-control inputDate'
                    ]
                ]) ?>
                <?= $form->field($model, 'stop_time', [
                    'inputOptions' => [
                        'class' => 'form-control inputDate'
                    ]
                ])->hint('不填写代表至今') ?>
                <?= $form->field($model, 'major_id')->dropDownList($major) ?>
                <div class="form-group" style="margin-left:220px;">
                    <?php if ($action == 'add'): ?>
                        <?= Html::submitButton(Yii::t('app', 'save'), ['class' => 'btn btn-success']) ?>
                    <?php endif; ?>
                    <?php if ($canList && $action == 'view'): ?>
                        <?= Html::a(Html::button(Yii::t('app', '返回'), ['class' => 'btn btn-primary']),
                            ['/product/default/work-history']
                        ) ?>
                    <?php endif; ?>
                    <?php if ($action == 'edit'): ?>
                        <?= Html::submitButton(Yii::t('app', 'save'), ['class' => 'btn btn-success']) ?>
                    <?php endif; ?>
                    <?php if ($canList): ?>
                        <?= Html::a(Html::button(Yii::t('app', '返回'), ['class' => 'btn btn-primary']),
                            ['/product/default/work-history']
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
