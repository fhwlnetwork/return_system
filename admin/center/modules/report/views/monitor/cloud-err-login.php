<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2016/7/26
 * Time: 9:25
 */

use yii\widgets\LinkPager;
use yii\helpers\Html;
use center\widgets\Alert;
use yii\bootstrap\ActiveForm;

\center\assets\ReportAsset::echartsJs($this);
$this->title = \Yii::t('app', 'report/monitor/cloud-error-login');

$canList = Yii::$app->user->can('report/monitor/cloud-error-login');
$lang = (Yii::$app->session->get('language')) ? Yii::$app->session->get('language') : 'zh-CN' ;
?>
<div class="page page-table">
    <?= Alert::widget() ?>
    <div class="row">
        <form name="form_constraints" action="<?= \yii\helpers\Url::to(['cloud-error-login']) ?>"
              class="form-horizontal form-validation" method="get">
            <div class="col-md-2">
                <?php echo Html::input('text', 'start_time', (!empty($params) && isset($params['start_time']))
                    ? $params['start_time'] : date('Y-m-d H:i:s', time() - 30 * 60), [
                    'class' => 'form-control inputDateTime',
                    'placeHolder' => Yii::t('app', 'Statistical time'),
                ]) ?>
            </div>
            <div class="col-md-2">
                <?php echo Html::input('text', 'end_time', (!empty($params) && isset($params['end_time']))
                    ? $params['end_time'] : date('Y-m-d H:i:s'), [
                    'class' => 'form-control inputDateTime',
                    'placeHolder' => Yii::t('app', 'end time'),
                ]) ?>
            </div>
            <div class="col-md-2">
                <?= Html::input('text', 'products_key', (!empty($params) && isset($params['products_key'])) ? $params['products_key'] : '', [
                    'class' => 'form-control',
                    'placeHolder' => Yii::t('app', 'network_user_login_name')
                ]) ?>
            </div>
            <div class="col-md-1" <?php $length = ($lang == 'zh-CN') ? 60 : 75; ?>style="width:<?= $length ?>px;">
                <?= Html::submitButton(Yii::t('app', 'search'), ['class' => 'btn btn-success']) ?>
            </div>
            <div class="col-md-1" <?php $length = ($lang == 'zh-CN') ? 75 : 85; ?>style="width:<?= $length ?>px;">
                <?= html::submitButton(yii::t('app', 'an hour'), ['class' => 'btn btn-primary', 'name' => 'timePoint', 'value' => '3']) ?>
            </div>
            <div class="col-md-1" <?php $length = ($lang == 'zh-CN') ? 60 : 70; ?>style="width:<?= $length ?>px;">
                <?= html::submitButton(yii::t('app', 'this day'), ['class' => 'btn btn-info', 'name' => 'timePoint', 'value' => '4']) ?>
            </div>
            <div class="col-md-1" <?php $length = ($lang == 'zh-CN') ? 60 : 95; ?>style="width:<?= $length ?>px;">
                <?= html::submitButton(yii::t('app', 'this Yesterday'), ['class' => 'btn btn-warning', 'name' => 'timePoint', 'value' => '5']) ?>
            </div>
            <div class="col-md-1" <?php $length = ($lang == 'zh-CN') ? 60 : 90; ?>style="width:<?= $length ?>px;">
                <?= html::submitButton(yii::t('app', 'this week'), ['class' => 'btn btn-primary', 'name' => 'timePoint', 'value' => '6']) ?>
            </div>
            <div class="col-md-1" <?php $length = ($lang == 'zh-CN') ? 60 : 90; ?>style="width:<?= $length ?>px;">
                <?= html::submitButton(yii::t('app', 'last week'), ['class' => 'btn btn-info', 'name' => 'timePoint', 'value' => '7']) ?>
            </div>
            <div class="col-md-1" <?php $length = ($lang == 'zh-CN') ? 60 : 90; ?>style="width:<?= $length ?>px;">
                <?= html::submitButton(yii::t('app', 'this month'), ['class' => 'btn btn-warning', 'name' => 'timePoint', 'value' => '8']) ?>
            </div>
        </form>
    </div>


</div>
<div class="col-md-12">
    <?php if ($canList): ?>
        <section class="panel panel-default table-dynamic">
            <div class="panel-heading data-center"><strong><span
                        class="glyphicon glyphicon-th-large"></span> <?= \Yii::t('app', 'report/monitor/cloud-error-login'); ?>
                </strong>
            </div>

            <?php if (!empty($list)): ?>
                <div style="overflow-x: auto;" class="col-md-12 col-sm-12">
                    <table class="table table-bordered  table-responsive table-hover"
                           style="font-size:16px;">
                        <thead>
                        <tr style="height: 30px; line-height: 30px;align-content: center;font-size: 14px;font-family: inherit;">
                            <th nowrap="nowrap">
                                <div class="th"><?= Yii::t('app', 'view') ?></div>
                            </th>
                            <th nowrap="nowrap">
                                <div class="th"><?= Yii::t('app', 'operate type User Base') ?></div>
                            </th>
                            <th nowrap="nowrap">
                                <div class="th"><?= Yii::t('app', 'school name') ?></div>
                            </th>
                            <th nowrap="nowrap">
                                <div class="th"><?= Yii::t('app', 'error count1') ?></div>
                            </th>
                            <th nowrap="nowrap">
                                <div class="th"><?= Yii::t('app', 'error count2') ?></div>
                            </th>
                            <th nowrap="nowrap">
                                <div class="th"><?= Yii::t('app', 'error count3') ?></div>
                            </th>
                            <th nowrap="nowrap">
                                <div class="th"><?= Yii::t('app', 'error count4') ?></div>
                            </th>
                            <th nowrap="nowrap">
                                <div class="th"><?= Yii::t('app', 'error count') ?></div>
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        foreach ($list as $k => $one):;?>
                            <tr bgcolor="<?php echo $k % 2 == 1 ? "#fff" : '#f1f1f1' ?>"
                                style="height: 30px; line-height: 30px;align-content: center;font-size: 14px;font-family: inherit;">
                                <td><span id="users_<?= $k ?>" onclick="chgBreak('<?=$k?>')" ng-click='users_<?= $k ?> = !users_<?= $k ?>'
                                          class="glyphicon glyphicon-plus" style="cursor: pointer"></span>
                                </td>
                                <td><?= $one['product_key'] ?></td>
                                <td><?= $one['school_name'] ?></td>
                                <td><?= $one['error_count1'] ?></td>
                                <td><?= $one['error_count2'] ?></td>
                                <td><?= $one['error_count3'] ?></td>
                                <td><?= $one['error_count4'] ?></td>
                                <td><?= $one['error_count'] ?></td>
                            </tr>
                            <tr  ng-show="users_<?= $k ?>">
                                <td colspan="10" ng-show="users_<?= $k ?>">
                                    <div ng-show="users_<?= $k ?>" class="panel-heading data-center"><strong><span
                                                class="glyphicon glyphicon-th-large"></span> <?= \Yii::t('app', 'report/monitor/cloud-error-login'); ?>
                                            ---<?= $one['school_name'] ?></strong>
                                    </div>
                                    <div class="row" ng-show="users_<?= $k ?>">
                                        <!--在线报表-->
                                        <div class="col-md-8 col-sm-8 text-left" ng-show="users_<?= $k ?>" title="<?= $one['school_name'] ?>">
                                            <?= $this->render('users_cloud_detail', ['source' => $one, 'id' => "report-online-$k", 'title' => $one['school_name'] . '--'.Yii::t('app', 'report/monitor/cloud-error-login')]) ?>
                                        </div>


                                    </div>
                                </td>
                            </tr>
                        <?php endforeach ?>
                        </tbody>

                    </table>

                </div>

                <div class="divider"></div>
                <footer class="table-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <?php
                            echo Yii::t('app', 'pagination show page', [
                                'totalCount' => $pagination->totalCount,
                                'totalPage' => $pagination->getPageCount(),
                                'perPage' => '<input type=text name=offset size=3 value=' . $pagination->defaultPageSize . '>',
                                'pageInput' => '<input type=text name=page size=4>',
                                'buttonGo' => '<input type=submit value=go>',
                            ]);
                            ?>
                        </div>
                        <div class="col-md-6 text-right">
                            <?= LinkPager::widget(['pagination' => $pagination, 'maxButtonCount' => 5]); ?>
                        </div>
                    </div>
                </footer>

            <?php else: ?>
                <div class="panel-body">
                    <?= Yii::t('app', 'no record') ?>
                </div>
            <?php endif ?>
        </section>
    <?php endif; ?>
</div>

<script>
    function chgBreak(id)
    {
        var obj = $('#users_'+id);
        var  className =   obj.attr('class');
        if (className.indexOf('plus') != -1) {
            obj.attr('class', 'glyphicon glyphicon-minus')
        } else {
            obj.attr('class', 'glyphicon glyphicon-plus')
        }

    }
</script>