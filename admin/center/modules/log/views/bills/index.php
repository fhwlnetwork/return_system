<?php
use yii\widgets\LinkPager;

?>
<div class="padding-top-15px">
    <form action="index" method="get">
        <div class="col-lg-12">
            <div class="panel panel-default" style="border-top:2px solid red;">
                <div class="panel-heading">
                    <h3 class="panel-title"><span
                                class="glyphicon glyphicon-search"></span> <?= Yii::t('app', 'search') ?></h3>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-2">
                            <input class="form-control" type="text" name="user_name"
                                   value="<?= $get['user_name'] ?: ''; ?>"
                                   placeholder="<?= Yii::t('app', 'user_name') ?>">
                        </div>
                        <div class="col-md-2">
                            <input class="form-control" type="text" name="user_real_name"
                                   value="<?= $get['user_real_name'] ?: ''; ?>"
                                   placeholder="<?= Yii::t('app', 'user_real_name') ?>">
                        </div>

                        <div class="col-md-2">
                            <input class="form-control" type="text" name="remark" value="<?= $get['remark'] ?: ''; ?>"
                                   placeholder="<?= Yii::t('app', 'remark') ?>">
                        </div>
                        <div class="col-md-2">
                            <input class="form-control" type="text" name="mgr_name"
                                   value="<?= $get['mgr_name'] ?: ''; ?>"
                                   placeholder="<?= Yii::t('app', 'mgr_name') ?>">
                        </div>
                        <div class="col-md-2">
                            <input class="form-control" type="text" name="api_name"
                                   value="<?= $get['api_name'] ?: ''; ?>"
                                   placeholder="<?= Yii::t('app', 'api_name') ?>">
                        </div>
                        <div class="col-md-2">
                            <input class="form-control" type="text" name="operate_user_name"
                                   value="<?= $get['operate_user_name'] ?: ''; ?>"
                                   placeholder="<?= Yii::t('app', 'operate_user_name') ?>">
                        </div>
                        <div class="col-md-2">
                            <input class="form-control" type="text" name="action" value="<?= $get['action'] ?: ''; ?>"
                                   placeholder="<?= Yii::t('app', 'action') ?>">
                        </div>

                        <div class="col-md-2">
                            <input class="form-control inputDateTime" type="text"
                                   value="<?= $get['start_time'] ?: ''; ?>" name="start_time"
                                   placeholder="<?= Yii::t('app', 'start_time') ?>">
                        </div>
                        <div class="col-md-2">
                            <input class="form-control inputDateTime" type="text" value="<?= $get['end_time'] ?: ''; ?>"
                                   name="end_time" placeholder="<?= Yii::t('app', 'end_time') ?>">
                        </div>
                    </div>
                </div>
                <div class="panel-footer">
                    <button type="submit"
                            class="btn btn-primary"><span
                                class="glyphicon glyphicon-ok"></span> <?= Yii::t('app', 'sure') ?></button>
                </div>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><span
                                class="glyphicon glyphicon-th-list"></span> <?= Yii::t('app', 'result') ?></h3>
                    <div class="pull-right" style="margin-top:-24px;">
                        <!--            <a type="button" class="btn btn-primary btn-sm" href=""><span class="glyphicon glyphicon-log-out"></span>-->
                        <? //= Yii::t('app', 'excel export') ?><!--</a>-->
                        <!--            <a type="button" class="btn btn-info btn-sm" href="/"><span class="glyphicon glyphicon-log-out"></span> -->
                        <? //= Yii::t('app', 'csv export') ?><!--</a>-->
                        <!--            <button type="submit" name="export" value="excel" class="btn btn-primary btn-sm"><span class="glyphicon glyphicon-log-out"></span> -->
                        <? //= Yii::t('app', 'excel export') ?><!--</button>-->
                        <button type="submit" name="export" value="csv" class="btn btn-info btn-sm"><span
                                    class="glyphicon glyphicon-log-out"></span> <?= Yii::t('app', 'csv export') ?>
                        </button>
                    </div>
                </div>
                <div class="panel-body">
                    <table class="table table-hover table-bordered table-striped">
                        <thead>
                        <tr>
                            <?php foreach ($col as $c): ?>
                                <th><?= Yii::t('app',$c['column_name']) ?></th>
                            <?php endforeach; ?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($models as $v): ?>
                            <tr>
                                <td><?= $v['id'] ?></td>
                                <td>
                                    <a href="<?= \yii\helpers\Url::to(['/user/base/view', 'user_name' => $v['user_name']]) ?>"><?= $v['user_name'] ?></a>
                                </td>
                                <td><?= $v['group_id'] ?></td>
                                <td><?= $v['user_real_name'] ?></td>
                                <td><?= $v['action'] ?></td>
                                <td><?= $v['target_id'] ?></td>
                                <td><?= $v['change_amount'] ?></td>
                                <td><?= $v['before_amount'] ?></td>
                                <td><?= $v['before_balance'] ?></td>
                                <td><?= $v['after_amount'] ?></td>
                                <td><?= $v['remark'] ?></td>
                                <td><?= date('Y-m-d H:i:s', $v['operate_time']) ?></td>
                                <td><?= $v['mgr_name'] ?></td>
                                <td><?= $v['api_name'] ?></td>
                                <td><?= $v['operate_user_name'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="panel-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <?=
                            Yii::t('app', 'pagination show1', [
                                'totalCount' => $pages->totalCount,
                                'totalPage' => $pages->getPageCount(),
                                'perPage' => '<span>' . $pages->defaultPageSize . '</span>',
                                'pageInput' => '<input type=text name=page size=4>',
                                'buttonGo' => '<input type=submit value=go>',
                            ]) ?>
                        </div>
                        <div class="col-md-6 text-right">
                            <?= LinkPager::widget(['pagination' => $pages, 'maxButtonCount' => 5]); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>