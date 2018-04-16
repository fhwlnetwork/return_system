<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2017/6/21
 * Time: 11:03
 */

use yii\bootstrap\Html;
use yii\bootstrap\ActiveForm;
use center\assets\ZTreeAsset;

$this->title = Yii::t('app', 'report/actual/index');
//ztree 搜索用
ZTreeAsset::register($this);
ZTreeAsset::addZtreeSelectMulti($this);
\center\assets\ReportAsset::newEchartsJs($this);
echo $this->render('/layouts/product-menu');
?>
<style type="text/css" xmlns="http://www.w3.org/1999/html">
    .ztree li a.curSelectedNode span {
        background-color: #0088cc;
        color: #fff;
        border-radius: 2px;
        padding: 2px;
    }
</style>
<div class="panel panel-default" data-ng-controller="selectType">
    <?= \center\widgets\Alert::widget();?>
    <div class="panel-body" style="padding: 10px">
        <?php
        $form = ActiveForm::begin([
            'layout' => 'horizontal',
            'fieldConfig' => [
                'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}",
                'horizontalCssClasses' => [],
            ],
        ]);
        ?>
        <div class="col-md-2">
            <?= $form->field($model, 'start_time', [
                'template' => '<div class="col-sm-12">{input}</div>'
            ])->textInput(
                [
                    'class' => 'form-control inputDate',
                    'placeHolder' => Yii::t('app', 'start time')
                ]);
            ?>
        </div>

        <div class="col-md-2">
            <?= $form->field($model, 'stop_time', [
                'template' => '<div class="col-sm-12">{input}</div>'
            ])->textInput(
                [
                    'class' => 'form-control inputDate',
                    'placeHolder' => Yii::t('app', 'end time')
                ]);
            ?>
        </div>

        <!--- 选择用户组 start -->
        <div class="form-group" ng-cloak ng-show="advanced==1">
            <div class="row col-md-12">
                <!--产品-->
                <div class="col-md-2" style="width:100px;"><?= Yii::t('app', 'select product') ?></div>
                <div class="col-md-10">
                    <?= $form->field($model, 'product_id')->inline()->radioList($model->product_list)->label(false) ?>
                </div>
            </div>
            <div class="col-md-2"><?= Yii::t('app', 'organization help4') ?></div>
            <div class="col-md-10">
                <div class="panel panel-default">
                    <div class="panel-body" style="max-height: 500px; overflow-y: auto;">
                        <?= Html::hiddenInput("Actual[group_id]", '', [
                            'id' => 'zTreeId',
                        ]) ?>
                        <div><?= Yii::t('app', 'organization help5') ?><span class="text-primary"
                                                                             id="zTreeSelect"></span></div>
                        <div id="zTreeAddUser" class="ztree"></div>
                    </div>
                </div>
            </div>
        </div>

        <label class="text-info"><input type="checkbox" ng-model="advanced"/>
            <small><?= Yii::t('app', 'advanced') ?></small>
        </label>&nbsp;
        <!-- 选择用户组 end -->

        <?= Html::submitButton(Yii::t('app', 'search'), ['class' => 'btn btn-success']) ?>
        <?= Html::submitButton(Yii::t('app', 'this month'), ['class' => 'btn btn-primary', 'name' => 'Actual[timePoint]', 'value' => 1]) ?>
        <?= Html::submitButton(Yii::t('app', 'last month'), ['class' => 'btn btn-primary', 'name' => 'Actual[timePoint]', 'value' => 2]) ?>
        <?= Html::submitButton(Yii::t('app', 'this quarter'), ['class' => 'btn btn-success', 'name' => 'Actual[timePoint]', 'value' => 3]) ?>
        <?= Html::submitButton(Yii::t('app', 'last quarter'), ['class' => 'btn btn-danger', 'name' => 'Actual[timePoint]', 'value' => 4]) ?>
        <div class="col-sm-12" style="text-align: left;color: #ffffff;">
            <?= $form->errorSummary($model); ?>
        </div>
        <?php $form->end(); ?>
    </div>
</div>
<div class="row" style="border:none;margin: 0;padding:0;margin-top:10px;overflow-x: auto;">
    <section class="panel panel-default table-dynamic" style="margin:0;padding:0;">
        <div class="panel-heading"><strong><span
                    class="glyphicon glyphicon-th-large"></span> <?= Yii::t('app', 'search result') ?></strong>
        </div>
        <div style="clear:both;"></div>

        <?php if ($source['code'] == 200) : ?>
        <div class="panel panel-body">
            <?= $this->render('/map/system-single',[
                'data' => $source['data'],
            ])?>

        </div>
        <table class="table table-bordered table-striped table-responsive">
                <thead>
                <tr style="height: 30px; line-height: 30px;align-content: center;">
                    <th nowrap="nowrap"><div class="th"><?= Yii::t('app', 'Statistical time') ?></div></th>
                    <th nowrap="nowrap"><div class="th"><?= Yii::t('app', 'actual_number') ?></div></th>
                    <th nowrap="nowrap"><div class="th"><?= Yii::t('app', 'action') ?></div></th>
                </tr>
                </thead>
                <tboday>
                    <?php foreach($source['table'] as $date => $val): ?>
                        <tr>
                            <td><?= $date?></td>
                            <td><?= $val?></td>
                            <td><?= Html::a(Html::button(Yii::t('app', 'Download'), ['class' => 'btn btn-warning btn-xs']),['/report/actual/download',
                                    'date' => $date
                                ])?></td>
                        </tr>
                    <?php endforeach;?>
                </tboday>
                <tbody>
        <?php else: ?>
            <div class="panel-body">
                <?= $source['msg']; ?>
            </div>
        <?php endif ?>
    </section>

</div>
<?php
$this->registerJs("
    createTree('zTreeAddUser');
");
$groupId = isset($params['Actual']['group_id']) ? $params['Actual']['group_id'] : '';

//声明ztree当前选中的id
$this->registerJs("
    var currentZTreeId = '" . $groupId . "';
", yii\web\View::POS_BEGIN);
?>
