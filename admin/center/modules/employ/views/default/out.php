<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use center\assets\ZTreeAsset;

$this->title = Yii::t('app', 'employ/default/index');
$canAdd = Yii::$app->user->can('interfaces/default/create');
$canEdit = Yii::$app->user->can('interfaces/default/update');
$canDel = Yii::$app->user->can('interfaces/default/delete');
echo $this->render('/layouts/menu');

//ztree 搜索用
ZTreeAsset::register($this);
ZTreeAsset::addZtreeSelectMulti($this);

?>
<style type="text/css" xmlns="http://www.w3.org/1999/html">
    .ztree li a.curSelectedNode span {
        background-color: #0088cc;
        color: #fff;
        border-radius: 2px;
        padding: 2px;
    }
</style>

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
        <!--组织结构-->
        <div class="form-group" ng-cloak ng-show="advanced==1">
            <div class="col-md-2"><?=Yii::t('app', 'organization help4')?></div>
            <div class="col-md-10">
                <div class="panel panel-default">
                    <div class="panel-body" style="max-height: 500px; overflow-y: auto;">
                        <?= Html::hiddenInput("group_id", '', [
                            'id' => 'zTreeId',
                        ]) ?>
                        <div><?= Yii::t('app', 'organization help5')?><span class="text-primary" id="zTreeSelect"></span></div>
                        <div id="zTreeAddUser" class="ztree"></div>
                    </div>
                </div>
            </div>
        </div>

        <?= Html::submitButton(Yii::t('app', 'search'), ['class' => 'btn btn-success']) ?>
        &nbsp;&nbsp;<label class="text-info"><input type="checkbox" ng-model="advanced" name="advanced" value="1"/><small><?= Yii::t('app', 'advanced')?></small></label>
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
            <?= $this->render('/map/pie', [
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



<?php
$this->registerJs("
    createTree('zTreeAddUser');
");
$groupId = isset($params['group_id']) ? $params['group_id'] : '';

    //声明ztree当前选中的id
    $this->registerJs("
    var currentZTreeId = '" . $groupId . "';
", yii\web\View::POS_BEGIN);
