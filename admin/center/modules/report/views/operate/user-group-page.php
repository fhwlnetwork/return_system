<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use center\modules\report\models\UserReportProducts;

\center\assets\ReportAsset::newEchartsJs($this);
echo $this->render('/layouts/operate-menu');
use center\assets\ZTreeAsset;

$this->title = Yii::t('app', 'report/operate/usergroup');
//ztree 搜索用
ZTreeAsset::register($this);
$this->registerJsFile('/js/ztree_select.js', ['depends' => [center\assets\ZTreeAsset::className()]]);
if (Yii::$app->session->get('usergroup')) {
    $searchField = array_keys(Yii::$app->session->get('usergroup'));
} else {
    $searchField = [];
}
?>
<style type="text/css" xmlns="http://www.w3.org/1999/html">
    .ztree li a.curSelectedNode span {
        background-color: #0088cc;
        color: #fff;
        border-radius: 2px;
        padding: 2px;
    }
</style>
<div class="panel panel-mint">
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

        <div class="col-md-2" style="width:80px;margin:0px;padding:0px;">
            <span class="input-group-addon" style="border:none;height:32px;background:white;"><?= Yii::t('app', 'report operate remind6') ?></span>
        </div>
        <div class="col-md-2" style="margin:0px;padding:0px;width:200px;">
            <?= $form->field($model, 'start_At', [
                'template' => '<div class="col-sm-12">{input}</div>'
            ])->textInput(
                [
                    'value' => isset($model->start_At) ? $model->start_At : date('Y-m-01'),
                    'class' => 'form-control inputDate',
                    'placeHolder' => Yii::t('app', 'start time')
                ]);
            ?>
        </div>
        <div class="col-md-2" style="width:20px;margin:0px;padding:0px;">
            <span class="input-group-addon" style="border:none;height:32px;background:white;"><?= Yii::t('app', 'report operate remind4') ?></span>
        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'stop_At', [
                'template' => '<div class="col-sm-12">{input}</div>'
            ])->textInput(
                [
                    'value' => isset($model->stop_At) ? $model->stop_At : date('Y-m-d'),
                    'class' => 'form-control inputDate',
                    'placeHolder' => Yii::t('app', 'end time')
                ]);
            ?>
        </div>

        <!--- 选择用户组 start -->

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
            <small><?= Yii::t('app', 'advanced') ?></small>
        </label>&nbsp;

        <?= Html::submitButton(Yii::t('app', 'search'), ['class' => 'btn btn-success','name'=>'search','value'=>'search']) ?>&nbsp;&nbsp;
        <!--- 选择用户组 end -->

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

            <div class="pull-right" style="margin-top:-5px;">
                <a type="button" class="btn btn-primary btn-sm"
                   href="<?= Yii::$app->urlManager->createUrl(array_merge(['report/operate/usergroup'], ['export' => 'excel'])) ?>"><span
                        class="glyphicon glyphicon-log-out"></span><?= Yii::t('app', 'excel export') ?></a>
            </div>
        </div>
        <div style="clear:both;"></div>
        <?php if (!empty($source['table'])) : ?>
            <div class="panel panel-default">
                <?= $this->render('/map/pay', [
                    'data' => $source['data'],
                    'model' => $model,
                    'name' => Yii::t('app', 'report/operate/usergroup'),
                    'type' => Yii::t('app', 'user_groups'),
                ]) ?>
            </div>
            <table class="table table-bordered  table-responsive">
                <thead>
                <tr style="height: 30px; line-height: 30px;align-content: center;">
                    <th nowrap="nowrap"><div class="th"><?= Yii::t('app', 'action') ?></div></th>
                    <th nowrap="nowrap"><div class="th"><?= Yii::t('app', 'user_groups') ?></div></th>
                    <th nowrap="nowrap"><div class="th"><?= Yii::t('app', 'batch excel pay num') ?></div></th>
                </tr>
                </thead>
                <tbody>
                <?php $i = 0;
                foreach ($source['table'] as $time => $one) {
                    $detail = isset($one['detail']) ? $one['detail'] : [];
                    ?>
                    <tr  bgcolor="<?php echo $i % 2 == 1 ? "#fff" : '#f1f1f1' ?>">
                        <td><span id="product_key_<?= $i ?>" onclick="chgBreak('<?= $i ?>')"
                                  ng-click='product_key_<?= $i ?> = !product_key_<?= $i ?>'
                                  class="glyphicon glyphicon-plus" style="cursor: pointer"></span>
                        </td>
                        <td><?= $time ?></td>
                        <td><?=  sprintf('%.2f', $one['data']).Yii::t('app', '$')?></td>
                    </tr>
                    <tr data-ng-show="product_key_<?= $i ?>">
                        <td colspan="3" ng-show="product_key_<?= $i ?>">
                            <div ng-show="product_key_<?= $i ?>" class="panel-heading data-center"><strong><span
                                        class="glyphicon glyphicon-th-large"></span> <?= \Yii::t('app', 'report/operate/usergroup'); ?>
                                    ---<?= $time ?></strong>
                            </div>
                            <?php if (!empty($detail)): ?>
                                <div class="row" ng-show="product_key_<?= $i ?>">
                                    <div class="col-md-8 col-sm-8 text-left">
                                        <table class="table table-bordered table-striped table-responsive">
                                            <thead>
                                            <tr style="height: 30px; line-height: 30px;align-content: center;">
                                                <th nowrap="nowrap"><div class="th"><?= Yii::t('app', 'user_groups') ?></div></th>
                                                <th nowrap="nowrap"><div class="th"><?= Yii::t('app', 'products name') ?></div></th>
                                                <th nowrap="nowrap"><div class="th"><?= Yii::t('app', 'batch excel pay num') ?></div></th>
                                            </tr>
                                            <tbody>
                                            <?php foreach ($detail as $key => $pro): ?>
                                                <tr>
                                                    <td><?= $time ?></td>
                                                    <td><?= $pro['product_id'].":".$source['products'][$pro['product_id']] ?></td>
                                                    <td><?= sprintf('%.2f', $pro['num']).Yii::t('app', '$') ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="panel panel-default"><?= Yii::t('app', 'no record')?></div>
                            <?php endif; ?>

                        </td>
                    </tr>
                    <?php $i++;
                } ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="panel-body">
                <?= Yii::t('app', 'no record') ?>
            </div>
        <?php endif ?>
    </section>

</div>
<script>
    function chgBreak(id) {
        var obj = $('#product_key_' + id);
        var className = obj.attr('class');
        if (className.indexOf('plus') != -1) {
            obj.attr('class', 'glyphicon glyphicon-minus')
        } else {
            obj.attr('class', 'glyphicon glyphicon-plus')
        }

    }
</script>
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
