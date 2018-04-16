<?php
use yii\helpers\Html;
use center\assets\ZTreeAsset;
use yii\bootstrap\ActiveForm;

\center\assets\ReportAsset::newEchartsJs($this);
echo $this->render('/layouts/financial-menu');
//默认session选择的产品

$this->title = Yii::t('app', 'report/financial/usergroup');

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
<div class="panel panel-default" data-ng-controller="report-financial">
    <div class="panel-body" style="padding: 10px">

        <?php
        $form = ActiveForm::begin([
            'layout' => 'horizontal',
            'fieldConfig' => [
                'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}",
                'horizontalCssClasses' => [
                ],
            ],
        ]);
        ?>

        <div class="col-md-2">
            <?= $form->field($model, 'data_source', [
                'template' => '<div class="col-sm-12">{input}</div>'
            ])->dropDownList($model::getAttributesList()['data_source']);
            ?>
        </div>

        <div class="col-md-2">
            <?= $form->field($model, 'start_time', [
                'template' => '<div class="col-sm-12">{input}</div>'
            ])->textInput(
                [
                    'class' => 'form-control inputDate',
                    'placeHolder' => Yii::t('app', 'start date')
                ]);
            ?>
        </div>

        <div class="col-md-2">
            <?= $form->field($model, 'stop_time', [
                'template' => '<div class="col-sm-12">{input}</div>'
            ])->textInput(
                [
                    'class' => 'form-control inputDate',
                    'placeHolder' => Yii::t('app', 'end date')
                ]);
            ?>
        </div>
        <div class="form-group" ng-cloak ng-show="advanced==1">
            <div class="col-md-2"><?= Yii::t('app', 'organization help4') ?></div>
            <div class="col-md-10">
                <div class="panel panel-default">
                    <div class="panel-body" style="max-height: 500px; overflow-y: auto;">
                        <?= Html::hiddenInput("group_id", '', [
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
            <small><?= Yii::t('app', 'select usergroup') ?></small>
        </label>

        <?= Html::submitButton(Yii::t('app', 'search'), ['class' => 'btn btn-success']) ?>

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
        <?php if (!empty($source['table'])) : ?>
            <?= $this->render('/map/pie', [
                'data' => $source['data'],
                'subtext' => empty($model->data_source) ? $attribute['all'] : $attribute[$model->data_source]
            ]) ?>
            <table class="table table-bordered table-striped table-responsive table-hover">
                <thead>
                <tr>
                    <th nowrap="nowrap">
                        <div class="th"><?= Yii::t('app', 'group id') ?></div>
                    </th>
                    <th nowrap="nowrap">
                        <div class="th"><?= Yii::t('app', 'amount') ?></div>
                    </th>
                    <th nowrap="nowrap">
                        <div class="th"><?= Yii::t('app', 'Export expense breakdown') ?></div>
                    </th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($source['table'] as $id => $v): ?>
                    <tr>
                        <td><?= $showField[$id] ?></td>
                        <td><?= $v['nums'].Yii::t('app', '$') ?></td>
                        <td>
                            <a href="detail?type=group&start_time=<?= $model->start_time?>&stop_time=<?=$model->stop_time?>&product_id=<?php echo $id; ?>"><?= Html::button(Yii::t('app', 'Export expense breakdown'), ['class' => 'btn btn-warning btn-xs']); ?></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="panel-body">
                <?= Yii::t('app', 'no record') ?>
            </div>
        <?php endif ?>
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
?>
