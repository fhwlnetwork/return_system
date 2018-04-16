<?php
use yii\helpers\Html;
use center\assets\ZTreeAsset;
use yii\bootstrap\ActiveForm;
use center\modules\report\models\SrunDetailDay;
use center\assets\ReportAsset;

ReportAsset::newEchartsJs($this);
echo $this->render('/layouts/operate-menu');

$this->title = Yii::t('app', 'report/operate/timelong');

ZTreeAsset::register($this);
ZTreeAsset::addZtreeSelectMulti($this);
$name = empty($groupName) ? Yii::t('app', 'search result') : Yii::t('app', 'search result').'>'.$groupName;
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
        <?= \center\widgets\Alert::widget(); ?>
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

            <div class="col-md-2" style="width:270px;">
                <div class="input-group" style="border:1px solid #cbd5dd">
                    <?= Html::dropDownList('SrunDetailDay[step]', isset($model->step) ? $model->step : '5H', SrunDetailDay::getTimLong()['step'], ['class' => 'form-control', 'style' => 'border:0px;height:32px;']); ?>
                    <span class="input-group-addon" style="border:none;height:32px"> <?= Yii::t('app', 'show') ?>
                        <?= Html::dropDownList('SrunDetailDay[unit]', isset($model->unit) ? $model->unit : '5', SrunDetailDay::getbytestype()['unit']); ?>
                        <?= Yii::t('app', 'section') ?>
                    </span>
                </div>
            </div>

            <!--- 选择用户组 start -->
            <div class="form-group" ng-cloak ng-show="advanced==1">
                <div class="col-md-2"><?= Yii::t('app', 'organization help4') ?></div>
                <div class="col-md-10">
                    <div class="panel panel-default">
                        <div class="panel-body" style="max-height: 500px; overflow-y: auto;">
                            <?= Html::hiddenInput("SrunDetailDay[group_id]", '', [
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

            <div class="col-sm-12" style="text-align: left;color: #ffffff;">
                <?= $form->errorSummary($model); ?>
            </div>
            <?php $form->end(); ?>
        </div>
    </div>
    <div class="row" style="border:none;margin: 0;padding:0;margin-top:10px;overflow-x: auto;">
        <section class="panel panel-default table-dynamic" style="margin:0;padding:0;">
            <div class="panel-heading"><strong><span
                        class="glyphicon glyphicon-th-large"></span> <?= $name ?></strong>
            </div>
            <div style="clear:both;"></div>
            <?php if (empty($source['table'])): ?>
            <?php else: ?>
                <div class="panel panel-default">
                    <?= $this->render('/map/times', [
                        'data' => $source['data'],
                        'model' => $model,
                        'name' => Yii::t('app', 'report/operate/timelong')
                    ]) ?>
                </div>
                <table class="table table-bordered table-striped table-responsive table-hover">
                    <thead>
                    <tr>
                        <th nowrap="nowrap">
                            <div class="th"><?= Yii::t('app', 'Time section') ?></div>
                        </th>
                        <th nowrap="nowrap">
                            <div class="th"><?= Yii::t('app', 'number of people') ?></div>
                        </th>
                        <th nowrap="nowrap">
                            <div class="th"><?= Yii::t('app', 'action') ?></div>
                        </th>
                    </tr>
                    </thead>
                    </thead>
                    <tbody>
                    <?php foreach ($source['table'] as $id => $v): ?>
                        <tr>
                            <td><?= $id ?></td>
                            <td><?= $v ?></td>
                            <td>
                                <a href="detail?sql_type=<?= $model->sql_type ?>&user_group_id=<?= $model->user_group_id ?>&start_At=<?= $model->start_At ?>&stop_At=<?= $model->stop_At ?>&section=<?php echo $id; ?>"><?= Html::button(Yii::t('app', 'download'), ['class' => 'btn btn-warning btn-xs']); ?></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
    </div>
<?php
$this->registerJs("
    createTree('zTreeAddUser');
");
$groupId = isset($params['SrunDetailDay']['group_id']) ? $params['SrunDetailDay']['group_id'] : '';

//声明ztree当前选中的id
$this->registerJs("
    var currentZTreeId = '" . $groupId . "';
", yii\web\View::POS_BEGIN);
?>