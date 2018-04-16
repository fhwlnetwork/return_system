<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2017/6/8
 * Time: 8:37
 */

use center\assets\ReportAsset;
use yii\helpers\Html;

ReportAsset::newEchartsJs($this);
$this->title = Yii::t('app', 'report/dashboard/system');
$this->registerJsFile('/js/app-dashboard.js', ['depends' => [center\assets\ZTreeAsset::className()]]);
echo $this->render('/layouts/accountant-menu');
?>
<div class="panel panel-default">
    <ul data-ng-init="type='system'" class="nav nav-tabs" id="tab">
        <li data-ng-click="type='system'" class="active"><a href="#"><?= Yii::t('app', 'system monitor')?></a></li>
        <li data-ng-click="type='efficiency'"><a href="#"><?= Yii::t('app', 'efficiency monitor')?></a></li>
    </ul>
</div>
<div class="page page-dashboard" data-ng-controller="system">
    <div class="row">
        <div class="col-md-12" style="height:500px;" id="system" data-ng-show="type == 'system'"><?= Yii::t('app', 'system monitor')?></div>
        <div class="col-md-12" style="height:40px; line-height:40px;" data-ng-show="type == 'efficiency'">
            <center><b><?= Yii::t('app', 'efficiency monitor')?></b></center>
        </div>
        <?php $i = 0;
        foreach ($efficiency->process as $key => $val): ?>
            <?php if (count($efficiency->process) > 4): ?>
                <?php if ($i > 4) : ?>
                    <div class="col-md-12" style="height:500px;width:100%;" ng-show="showMore && type == 'efficiency'" id="<?= $key ?>">
                        进程<?= $key ?>监控
                    </div>
                <?php else : ?>
                    <div class="col-md-12" style="height:500px;" id="<?= $key ?>"
                         data-ng-show="type == 'efficiency'">进程<?= $key ?>监控
                    </div>
                <?php endif; ?>
            <?php else : ?>
                <div class="col-md-12" style="height:500px;" id="<?= $key ?>" data-ng-show="type == 'efficiency'">
                    进程<?= $key ?>监控
                </div>
            <?php endif; ?>
            <?php $i++;endforeach; ?>
        <?php if ($i > 5): ?>
            <div style="text-align:center; margin-top:10px;width:100%;" data-ng-show="type == 'efficiency'">
                <button data-ng-model="changeNow" data-ng-click="showMore = !showMore" type="button"
                        class="btn btn-w-md btn-gap-v btn-info">
                    <?= Yii::t('app', 'see all') ?>
                    <i class="fa fa-chevron-down" ng-show="!showMore"></i>
                    <i class="fa fa-chevron-up ng-hide" ng-show="showMore"></i>
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>
<div class="panel panel-default" style="padding-top:15px;" data-ng-show="type =='system'">
    <section class="panel panel-default table-dynamic">
        <div class="panel-heading"><strong><span
                    class="glyphicon glyphicon-th"></span> <?= Yii::t('app', 'system monitor') . '(' . $model->start_time . '-' . $model->stop_time . ')' ?>
            </strong></div>

        <?php if ($data['code'] == 200) : ?>
        <?php if ($data['single']) : ?>
        <table class="table table-bordered table-striped table-responsive">
            <?php else: ?>
            <table class="table table-bordered  table-responsive">
                <?php endif; ?>
                <thead>
                <tr>
                    <?php if ($data['single']) : ?>
                        <?php foreach ($data['table']['header'] as $v) : ?>
                            <th>
                                <div class='th'><?= $v; ?></div>
                            </th>
                        <?php endforeach ?>
                    <?php else: ?>
                        <th>
                            <div class='th'><?= Yii::t('app', 'action'); ?></div>
                        </th>
                        <?php foreach ($data['table']['top_header'] as $v) : ?>
                            <th>
                                <div class='th'><?= $v; ?></div>
                            </th>
                        <?php endforeach ?>
                    <?php endif; ?>
                </tr>
                </thead>
                <tbody>
                <?php if ($data['single']) : ?>
                    <?php foreach ($data['table']['data'] as $one) : ?>
                        <tr>
                            <?php foreach ($one as $v) : ?>
                                <td><?= $v; ?></td>
                            <?php endforeach ?>
                        </tr>
                    <?php endforeach ?>
                <?php else: ?>
                <?php $i = 0;
                foreach ($data['table']['data'] as $date => $one) : ?>
                <tr bgcolor="<?php echo $i % 2 == 1 ? "#fff" : '#f1f1f1' ?>">
                    <td><span id="product_key_<?= $i ?>" onclick="chgBreak('<?= $i ?>')"
                              data-ng-click='product_key_<?= $i ?> = !product_key_<?= $i ?>'
                              class="glyphicon glyphicon-plus" style="cursor: pointer"></span>
                    </td>
                    <?php foreach ($one['data'] as $v) : ?>
                        <td><?= $v; ?></td>
                    <?php endforeach ?>
                </tr>
                <tr ng-show="product_key_<?= $i ?>">
                    <td colspan="7">
                        <table class="table table-bordered table-striped table-responsive">
                            <tr>
                                <?php foreach ($data['table']['detail_header'] as $v) : ?>
                                    <th>
                                        <div class='th'><?= $v; ?></div>
                                    </th>
                                <?php endforeach ?>
                            </tr>
                            <?php foreach ($one['detail'] as $vv) : ?>
                                <tr>
                                    <?php foreach ($vv as $vvv) : ?>
                                        <td><?= $vvv; ?></td>
                                    <?php endforeach ?>
                                </tr>
                            <?php endforeach ?>
                        </table>
                        <?php $i++;
                        endforeach ?>
                        <?php endif; ?>

                </tbody>
            </table>
            <?php endif; ?>
    </section>

</div>
<?php
$this->registerJs("
    $('#tab a').click(function (e) {
          e.preventDefault();//阻止a链接的跳转行为
          $(this).tab('show');//显示当前选中的链接及关联的content
    })
 ");
?>

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
