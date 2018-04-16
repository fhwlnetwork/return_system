<?php
//use yii;
use yii\widgets\LinkPager;
use yii\helpers\Html;
use center\widgets\Alert;
use center\extend\Tool;
use center\assets\ZTreeAsset;
use yii\bootstrap\ActiveForm;
use center\modules\auth\models\SrunJiegou;


/**
 * @var yii\web\View $this
 * @var $userArray
 */
$this->title = \Yii::t('app', 'report/financial/checkout');
$action = $this->context->action->id;
$isEdit = $action == 'edit' ? true : false;
$id = Yii::$app->request->get('id');
$attributes = $model->getAttributesList();
//ztree
ZTreeAsset::register($this);
ZTreeAsset::addZtreeSelectMulti($this);
echo $this->render('/layouts/financial-menu');
?>

    <style type="text/css">
        .ztree li a.curSelectedNode span {
            background-color: #0088cc;
            color: #fff;
            border-radius: 2px;
            padding: 2px;
        }
    </style>
    <div class="page">
        <?= Alert::widget() ?>
        <div class="panel panel-default">
            <div class="panel-body">
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
                    <?= $form->field($model, 'user_name', [
                        'template' => '<div class="col-sm-12">{input}</div>'
                    ])->textInput(
                        [
                            'class' => 'form-control',
                            'placeHolder' => Yii::t('app', 'financial pay account')
                        ]);
                    ?>
                </div>
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

                <div class="col-md-4">
                    &nbsp;&nbsp;&nbsp;&nbsp;<label class="text-info"><input type="checkbox" ng-model="advanced" name="advanced" value="1"/>
                        <small><?= Yii::t('app', 'advanced') ?></small>
                    </label>
                    <?= Html::submitButton(Yii::t('app', 'search'), ['class' => 'btn btn-success']) ?>
                    <?= html::submitButton(yii::t('app', 'this month'), ['class' => 'btn btn-success', 'name' => 'timePoint', 'value' => '3']) ?>
                    <?= html::submitButton(yii::t('app', 'this quarter'), ['class' => 'btn btn-info', 'name' => 'timePoint', 'value' => '5']) ?>
                    <?= html::submitButton(yii::t('app', 'this year'), ['class' => 'btn btn-danger', 'name' => 'timePoint', 'value' => '7']) ?>
                </div>

                <div class="form-group" ng-cloak ng-show="advanced==1">
                    <div class="row col-md-12">
                        <!--产品-->
                        <div class="col-md-2" style="width:100px;"><?= Yii::t('app', 'select product') ?></div>
                        <div class="col-md-10">
                            <?= Html::checkboxList('product_id[]', $params['product_id'], $product, []) ?>
                        </div>
                    </div>
                    <!--组织结构-->
                    <div class="row col-md-12">
                        <div class="col-md-2" style="width:100px;"><?= Yii::t('app', 'organization help4') ?></div>
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
                </div>
            </div>
            <div class="col-sm-12" style="text-align: left;color: #ffffff;">
                <?= $form->errorSummary($model); ?>
            </div>
            <?php $form->end(); ?>

        </div>


        <section class="panel panel-default table-dynamic">
            <div style="float:right;margin-right:10px;margin-top:5px;">
                <?php $excel_url = (strpos(yii::$app->request->url, '?')) ? (yii::$app->request->url . '&action=excel') : (yii::$app->request->url . '?action=excel'); ?>
                <a type="button" class="btn btn-default btn-sm"
                   href="<?= Yii::$app->urlManager->createUrl(array_merge(['report/financial/checkout'], $params, ['action' => 'excel'])); ?>"><span
                        class="glyphicon glyphicon-log-out"></span>excel</a>
                <a type="button" class="btn btn-default btn-sm"
                   href="<?= Yii::$app->urlManager->createUrl(array_merge(['report/financial/checkout'], $params, ['action' => 'csv'])); ?>"><span
                        class="glyphicon glyphicon-log-out"></span>csv</a>

            </div>
            <div class="panel-heading">
                <strong>
                    <span
                        class="glyphicon glyphicon-th-large"></span> <?= Yii::t('app', 'report/financial/checkout') . Yii::t('app', 'checkout report help1') ?>
                </strong>
            </div>

            <?php if (!empty($list)): ?>
                <table class="table table-bordered table-striped table-responsive">
                    <thead>
                    <tr>
                        <th>
                            <div class='th'><?= Yii::t('app', 'account') ?></div>
                        </th>
                        <th>
                            <div class='th'><?= Yii::t('app', 'name') ?></div>
                        </th>
                        <th>
                            <div class='th'><?= Yii::t('app', 'checkout amount') ?></div>
                        </th>
                        <th>
                            <div class='th'><?= Yii::t('app', 'group id') ?></div>
                        </th>
                        <th>
                            <div class='th'><?= Yii::t('app', 'product') ?></div>
                        </th>
                        <th>
                            <div class='th'><?= Yii::t('app', 'flux') ?></div>
                        </th>
                        <th>
                            <div class='th'><?= Yii::t('app', 'time lenth') ?></div>
                        </th>
                        <th>
                            <div class='th'><?= Yii::t('app', 'checkout time') ?></div>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($list as $id => $one) { ?>
                        <tr>
                            <td><?= Html::encode($one['user_name']); ?></td>
                            <td><?= Html::encode(\center\modules\user\models\Base::findOne(['user_name' => $one['user_name']])->user_real_name); ?></td>
                            <td><?= Html::encode(sprintf('%.2f', $one['spend_num'] + $one['rt_spend_num'])); ?></td>
                            <td><?= Html::encode(isset($model->can_group[$one['group_id']]) ? $model->can_group[$one['group_id']] : $one['group_id']); ?></td>
                            <td><?= Html::encode(isset($product[$one['product_id']]) ? $product[$one['product_id']] : $one['product_id']); ?></td>
                            <td><?= Html::encode(Tool::bytes_format($one['flux'])); ?></td>
                            <td><?= Html::encode(Tool::seconds_format($one['minutes'])); ?></td>
                            <td><?= Html::encode(date('Y-m-d H:i', $one['create_at'])); ?></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>

                <footer class="table-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <?= Yii::t('app', 'pagination show checkout', ['totalCount' => $pages->totalCount, 'totalPage' => $pages->getPageCount(), 'perPage' => $pages->pageSize, 'user_charge' => $total_num]);
                            ?>
                        </div>

                        <div class="col-md-6 text-right">
                            <?= LinkPager::widget(['pagination' => $pages]) ?>
                        </div>
                    </div>
                </footer>
                <!--用户组对应的管理员-->
                <?php if ($mgrs): ?>
                    <div class="panel-heading">
                        <strong>
                            <span class="glyphicon glyphicon-user"></span> <?= Yii::t('app', 'group manager') ?>
                        </strong>
                    </div>
                    <table class="table table-bordered table-striped table-responsive">
                        <thead>
                        <tr>
                            <th>
                                <div class='th'><?= Yii::t('app', 'name') ?></div>
                            </th>
                            <th>
                                <div class='th'><?= Yii::t('app', 'email') ?></div>
                            </th>
                            <th>
                                <div class='th'><?= Yii::t('app', 'operate') ?></div>
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($mgrs as $key => $one) { ?>
                            <tr>
                                <td><?= Html::encode($one['username']); ?></td>
                                <td><?= Html::encode($one['email']); ?></td>
                                <?php if ($key == 0): ?>
                                    <td rowspan="<?= count($mgrs) ?>"
                                        style="text-align: center;height:auto;"><?= Html::a(Html::button(Yii::t('app', 'send email'), ['class' => 'btn btn-success btn-xs']), [Yii::$app->urlManager->createUrl(array_merge(['report/financial/checkout'], $params , ['action'=>'send_email']))], ['title' => Yii::t('app', 'send email')]) ?></td>
                                <?php endif ?>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                <?php endif ?>
            <?php else: ?>
                <div class="panel-body">
                    <?= Yii::t('app', 'no record') ?>
                </div>
            <?php endif ?>
        </section>

    </div>
<?php
//声明ztree当前选中的id
$this->registerJs("
    createTree('zTreeAddUser');
");

//声明ztree当前选中的id
$this->registerJs("
    var currentZTreeId = '" . $params['group_id'] . "';
", yii\web\View::POS_BEGIN);


?>