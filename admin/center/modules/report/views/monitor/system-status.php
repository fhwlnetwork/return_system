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
use center\extend\Tool;

$this->title = \Yii::t('app', 'report/monitor/cloud-system-status');

$canList = Yii::$app->user->can('report/monitor/cloud-system-status'); //磁盘历史监控
$canStatus = Yii::$app->user->can('report/monitor/cloud-disk-status'); //磁盘实时监控
$lang = (Yii::$app->session->get('language')) ? Yii::$app->session->get('language') : 'zh-CN';
?>
<div class="padding-top-15px">

    <div class="col-lg-12">
        <h3 class="page-header">
            <i class="glyphicon glyphicon-list-alt size-h4"></i>&nbsp;<?= Html::encode($this->title); ?>
        </h3>
        <?= Alert::widget(); ?>
        <div class="panel panel-body">
            <ul class="nav nav-tabs">
                <?php if ($canList): ?>
                <li <?php if ($action == 'history'): ?>class="active"<?php endif ?>>
                    <?= Html::a(Yii::t('app', 'history monitor'), ['/report/monitor/cloud-system-status']) ?>
                    </li><?php endif ?>
                <?php if ($canStatus): ?>
                <li <?php if ($action == 'now'): ?>class="active"<?php endif ?>>
                    <?= Html::a(Yii::t('app', 'now monitor'), ['/report/monitor/cloud-system-status?action=now']) ?>
                    </li><?php endif ?>
            </ul>
        </div>
    </div>
</div>
<div class="page page-table">
    <?= Alert::widget() ?>
    <?php if($action == 'history'):?>
        <div class="row">
            <form name="form_constraints" action="<?= \yii\helpers\Url::to(['cloud-system-status']) ?>"
                  class="form-horizontal form-validation" method="get">
                <div class="col-md-2">
                    <?php echo Html::input('text', 'start_time', (!empty($params) && isset($params['start_time']))
                        ? $params['start_time'] : date('Y-m-d H:i:s', time() - 30 * 60), [
                        'class' => 'form-control inputDateTime',
                        'placeHolder' => Yii::t('app', 'Statistical time'),
                    ]) ?>
                </div>
                <input type="hidden" name="action" value="<?=$action?>">;
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
    <?php endif;?>


</div>
<div class="col-md-12">
    <?php if ($canList): ?>
        <section class="panel panel-default table-dynamic">
            <div class="panel-heading data-center"><strong><span
                        class="glyphicon glyphicon-th-large"></span> <?= \Yii::t('app', 'report/monitor/cloud-system-status'); ?>
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
                                <div class="th"><?= Yii::t('app', 'disk total') ?></div>
                            </th>
                            <th nowrap="nowrap">
                                <div class="th"><?= Yii::t('app', 'disk free total') ?></div>
                            </th>
                            <th nowrap="nowrap">
                                <div class="th"><?= Yii::t('app', 'used percent') ?></div>
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
                        $i = 0;
                        foreach ($data['table'] as $k => $one):; ?>
                            <tr bgcolor="<?php echo $i % 2 == 1 ? "#fff" : '#f1f1f1' ?>"
                                style="height: 30px; line-height: 30px;align-content: center;font-size: 14px;font-family: inherit;">
                                <td><span id="systems_<?= $i ?>" onclick="chgBreak('<?= $i ?>')"
                                          ng-click='systems_<?= $i ?> = !systems_<?= $i ?>'
                                          class="glyphicon glyphicon-plus" style="cursor: pointer"></span>
                                </td>
                                <td><?= isset($one['total_bytes']) ? Tool::bytes_format($one['total_bytes']) : 0 ?></td>
                                <td><?= isset($one['free_bytes']) ? Tool::bytes_format($one['free_bytes']) : 0 ?></td>
                                <td><?= isset($one['used_percent']) ? $one['used_percent'].'%' : '0.00%' ?></td>
                                <td><?= $one['product_name'] ?></td>
                                <td><?= $one['school_name'] ?></td>
                            </tr>
                            <tr ng-show="systems_<?= $i ?>">
                                <td colspan="7" ng-show="systems_<?= $i ?>">
                                    <div ng-show="systems_<?= $i ?>" class="panel-heading data-center"><strong><span
                                                class="glyphicon glyphicon-th-large"></span> <?= \Yii::t('app', 'report/monitor/cloud-system-status'); ?>
                                            ---<?= $one['school_name'] ?></strong>
                                    </div>
                                    <div class="row" ng-show="systems_<?= $i ?>">
                                        <div class="col-md-10 col-sm-10 text-left">
                                            <table ng-show="systems_<?= $i ?>"
                                                   class="table table-bordered table-striped table-responsive"
                                                >
                                                <?php if($action != 'now'): ?>
                                                    <?php if (!empty($one['details'])): $partitions = $one['details'];
                                                 ?>
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
                                                        <?php foreach ($partitions as $key => $ipPartition):
                                                            foreach ($ipPartition as $partition):
                                                            $keyName = mt_rand(1,1000) . $i;
                                                            ?>
                                                            <tr style="height: 30px; line-height: 30px;align-content: center;font-size: 14px;font-family: inherit;">
                                                                <td><span id="systems_<?= $keyName ?>"
                                                                          onclick="chgBreak('<?= $keyName ?>')"
                                                                          ng-click='systems_<?= $keyName ?> = !systems_<?= $keyName ?>'
                                                                          class="glyphicon glyphicon-plus"
                                                                          style="cursor: pointer"></span>
                                                                </td>
                                                                <td><?= $partition['device_ip'] ?></td>
                                                                <td><?= $partition['partition_name'] ?></td>
                                                                <td><?= $partition['mount_point'] ?></td>
                                                                <td><?= Tool::bytes_format($partition['total_bytes']) ?></td>
                                                                <td><?= Tool::bytes_format($partition['free_bytes']) ?></td>
                                                                <td><?= sprintf("%1.2f", $partition['used_percent']).'%' ?></td>
                                                            </tr>
                                                            <!--磁盘使用率监控-->
                                                            <tr data-ng-show="systems_<?= $keyName ?>">
                                                                <td colspan="8" data-ng-show="systems_<?= $keyName ?>">
                                                                    <div data-ng-show="systems_<?= $keyName ?>"
                                                                         class="col-md-12 col-sm-12 text-left"
                                                            <?=
                                                                $this->render('system_online_status', [
                                                                  'data' => $data['data'][$k][$partition['device_ip']][$partition['mount_point']],
                                                                   'id' => $keyName,
                                                                   'params' => $params,
                                                                    'title' => $partition['partition_name'] . Yii::t('app', 'partition monitor')])
                                                            ?>
                                                                    </div>
                                                                </td>

                                                            </tr>

                                                        <?php endforeach; endforeach;?>
                                                    <?php else: ?>
                                                        <?= "<span style='color:red'>".Yii::t('app', 'system status error', ['pro'=>$one['school_name']]).'</span>'?>
                                                    <?php endif; ?>
                                                <?php else: ?>
												   <?php if(!empty($one['series'])): ?>
														<?=$this->render('system_online_now_status', ['serieses' => $one['series'], 'id' => $one['product_name'], 'title' => $one['school_name'] . Yii::t('app', 'partition monitor'), 'height'=>$one['height'], 'legend'=>$one['legend']]) ?>	
													<?php endif;?>
                                                <?php endif; ?>
                                            </table>

                                        </div>


                                    </div>
                                </td>
                            </tr>
                        <?php $i++;endforeach; ?>
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