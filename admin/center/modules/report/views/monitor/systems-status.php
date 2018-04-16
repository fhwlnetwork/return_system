<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2016/9/9
 * Time: 10:35
 */
use yii\widgets\LinkPager;
use yii\helpers\Html;
use center\widgets\Alert;
use yii\bootstrap\ActiveForm;

\center\assets\ReportAsset::echartsJs($this);
$this->title = \Yii::t('app', 'report/monitor/cloud-systems-status');

$canList = Yii::$app->user->can('report/monitor/cloud-systems-status');
$lang = (Yii::$app->session->get('language')) ? Yii::$app->session->get('language') : 'zh-CN';
?>
<div class="page page-table">
    <?= Alert::widget() ?>
    <div class="row">
        <form name="form_constraints" action="<?= \yii\helpers\Url::to(['cloud-systems-status']) ?>"
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
                        class="glyphicon glyphicon-th-large"></span> <?= \Yii::t('app', 'report/monitor/cloud-systems-status'); ?>
                </strong>
            </div>

            <?php if (!empty($data)): ?>
                <div>
                    <table class="table table-bordered  table-responsive table-hover"
                           style="font-size:16px;width:100%;">
                        <thead>

                        <tr style="height: 30px; line-height: 30px;align-content: center;font-size: 14px;font-family: inherit;">

                            <th nowrap="nowrap">
                                <div class="th"><?= Yii::t('app', 'view') ?></div>
                            </th>
                            <th nowrap="nowrap">
                                <div class="th"><?= Yii::t('app', 'cpu max') ?></div>
                            </th>
                            <th nowrap="nowrap">
                                <div class="th"><?= Yii::t('app', 'mem max') ?></div>
                            </th>
                            <th nowrap="nowrap">
                                <div class="th"><?= Yii::t('app', 'mem-cached max') ?></div>
                            </th>
                            <th nowrap="nowrap">
                                <div class="th"><?= Yii::t('app', 'loads max') ?></div>
                            </th>
                            <th nowrap="nowrap">
                                <div class="th"><?= Yii::t('app', 'process max') ?></div>
                            </th>
                            <th nowrap="nowrap">
                                <div class="th"><?= Yii::t('app', 'httpd max') ?></div>
                            </th>
                            <th nowrap="nowrap">
                                <div class="th"><?= Yii::t('app', 'network_user_login_name') ?></div>
                            </th>
                            <th nowrap="nowrap">
                                <div class="th"><?= Yii::t('app', 'school name') ?></div>
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        foreach ($data as $k => $one):; ?>
                            <tr bgcolor="<?php echo $k % 2 == 1 ? "#fff" : '#f1f1f1' ?>"
                                style="height: 30px; line-height: 30px;align-content: center;font-size: 14px;font-family: inherit;">
                                <td><span id="systems_<?= $k ?>" onclick="chgBreak('<?= $k ?>')"
                                          ng-click='systems_<?= $k ?> = !systems_<?= $k ?>'
                                          class="glyphicon glyphicon-plus" style="cursor: pointer"></span>
                                </td>
                                <td><?= isset($one['cpu_max']) ? sprintf("%1.2f", $one['cpu_max'])."%": '0.00%' ?></td>
                                <td><?= isset($one['mem_max']) ? sprintf("%1.2f",$one['mem_max'])."%" : '0.00%' ?></td>
                                <td><?= isset($one['mem_cached_max']) ? sprintf("%1.2f",$one['mem_cached_max'])."%" : '0.00%' ?></td>
                                <td><?= isset($one['loads_max']) ? sprintf("%1.2f",$one['loads_max'])."%" : 0 ?></td>
                                <td><?= isset($one['process_max']) ? $one['process_max'] : 0 ?></td>
                                <td><?= isset($one['httpd_max']) ? $one['httpd_max'] : '0' ?></td>
                                <td><?= $one['product_name'] ?></td>
                                <td><?= $one['school_name'] ?></td>
                            </tr>
                            <tr ng-show="systems_<?= $k ?>">
                                <td colspan="9" ng-show="systems_<?= $k ?>">
                                    <div ng-show="systems_<?= $k ?>" class="panel-heading data-center"><strong><span
                                                class="glyphicon glyphicon-th-large"></span> <?= \Yii::t('app', 'report/monitor/cloud-systems-status'); ?>
                                            ---<?= $one['school_name'] ?></strong>
                                    </div>
                                    <div class="row" ng-show="systems_<?= $k ?>">
                                        <div class="col-md-10 col-sm-10 text-left">
                                            <table ng-show="systems_<?= $k ?>"
                                                   class="table table-bordered table-striped table-responsive"
                                                >
                                                <?php if (!empty($one[$one['product_name']]['partitions'])): $partitions = $one[$one['product_name']]['partitions']; ?>
                                                    <tr style="height: 30px; line-height: 30px;align-content: center;font-size: 14px;font-family: inherit;">
                                                        <?php
                                                        if ($model->searchField) {
                                                            echo '<th nowrap="nowrap"><div class="th">';
                                                            echo Yii::t('app', 'view');
                                                            echo '</div></th>';
                                                            foreach ($model->searchField as $value) {
                                                                $newParams = $params;
                                                                echo '<th nowrap="nowrap"><div class="th">';
                                                                echo $value;
                                                                echo '</div></th>';
                                                            }
                                                        }

                                                        ?>
                                                    </tr>
                                                    <?php foreach ($partitions as $key => $partition): $keyName = mt_rand(1,1000).$key;
                                                        ?>
                                                        <tr style="height: 30px; line-height: 30px;align-content: center;font-size: 14px;font-family: inherit;">
                                                            <td><span id="systems_<?= $keyName ?>"
                                                                      onclick="chgBreak('<?= $keyName ?>')"
                                                                      ng-click='systems_<?= $keyName ?> = !systems_<?= $keyName ?>'
                                                                      class="glyphicon glyphicon-plus"
                                                                      style="cursor: pointer"></span>
                                                            </td>
                                                            <td><?= $partition['device_ip'] ?></td>
                                                            <td><?= sprintf("%1.2f", $partition['cpu_max']).'%' ?></td>
                                                            <td><?= sprintf("%1.2f", $partition['mem_max']).'%' ?></td>
                                                            <td><?= sprintf("%1.2f", $partition['mem_cached_max']). '%' ?></td>
                                                            <td><?= sprintf("%1.2f", $partition['loads_max']).'%' ?></td>
                                                            <td><?= $partition['process_max'] ?></td>
                                                            <td><?= $partition['httpd_max'] ?></td>
                                                        </tr>
                                                        <!--磁盘使用率监控-->
                                                        <tr data-ng-show="systems_<?= $keyName ?>">
                                                            <td colspan="8" data-ng-show="systems_<?= $keyName ?>">
                                                                <div data-ng-show="systems_<?= $keyName ?>"
                                                                     class="col-md-12 col-sm-12 text-left"
                                                                    <?= $this->render('systems_online_status', ['source' => $partition['source'], 'id' => $keyName, 'title' => $partition['device_ip'] . Yii::t('app', 'service status monitor')]) ?>
                                                                </div>
                                                            </td>

                                                        </tr>

                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <?= "<span style='color:red'>".Yii::t('app', 'system status error', ['pro'=>$one['school_name']]).'</span>'?>
                                                <?php endif; ?>
                                            </table>

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
    function chgBreak(id) {
        var obj = $('#systems_' + id);
        var className = obj.attr('class');
        if (className.indexOf('plus') != -1) {
            obj.attr('class', 'glyphicon glyphicon-minus')
        } else {
            obj.attr('class', 'glyphicon glyphicon-plus')
        }

    }
</script>