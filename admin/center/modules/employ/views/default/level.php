<?php
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\grid\GridView;

$this->title = Yii::t('app', 'employ/default/level');
$canAdd = Yii::$app->user->can('interfaces/default/create');
$canEdit = Yii::$app->user->can('interfaces/default/update');
$canDel = Yii::$app->user->can('interfaces/default/delete');
echo $this->render('/layouts/menu');
?>


<div class="panel panel-default">
    <div class="panel-body" style="padding: 10px">
        <?php
        $form = ActiveForm::begin([
            'layout' => 'horizontal',
            'fieldConfig' => [
                'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}"
            ],
        ]);
        ?>

        <div class="col-md-2">
            <?=
            $form->field($model, 'created_at', [
                'template' => '<div class="col-sm-12">{input}</div>'
            ])->textInput(
                [
                    'value' => isset($model->created_at) ? $model->created_at : '',
                    'class' => 'form-control inputDate',
                    'placeHolder' => Yii::t('app', '时间')
                ]);
            ?>
        </div>
        <?= Html::submitButton(Yii::t('app', 'search'), ['class' => 'btn btn-success']) ?>

        <div class="col-sm-12" style="text-align: left;color: #ffffff;">
            <?= $form->errorSummary($model); ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

<div class="row" style="border:none;margin: 0;padding:0;margin-top:10px;overflow-x: auto;">
    <section class="panel panel-default table-dynamic" style="margin:0;padding:0;">
        <div class="panel-heading"><strong><span
                        class="glyphicon glyphicon-th-large"></span> <?= Yii::t('app', 'search result') ?></strong>
        </div>
        <div style="clear:both;"></div>
        <?php if ($data['code'] == 200) : ?>
            <?= $this->render('/map/line', [
                'data' => $data['data'],
                'model' => $model,
            ]) ?>
        <?php else: ?>
            <div class="panel-body">
                <?= Yii::t('app', 'no record') ?>
            </div>
        <?php endif ?>
</div>

</section>
</div>




