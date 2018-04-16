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
                            <select class="form-control" name="svc_type">
                                <option value=""><?= Yii::t('app', 'choose_type') ?></option>
                                <option <?php if ($get['svc_type'] == 'p2p voice') echo 'selected="selected"'; ?>
                                        value="p2p voice">p2p voice
                                </option>
                                <option <?php if ($get['svc_type'] == 'p2p video') echo 'selected="selected"'; ?>
                                        value="p2p video">p2p video
                                </option>
                                <option <?php if ($get['svc_type'] == 'group') echo 'selected="selected"'; ?>
                                        value="group">group
                                </option>
                                <option <?php if ($get['svc_type'] == 'group speaker') echo 'selected="selected"'; ?>
                                        value="group speaker">group speaker
                                </option>
                                <option <?php if ($get['svc_type'] == 'video upload') echo 'selected="selected"'; ?>
                                        value="video upload">video upload
                                </option>
                                <option <?php if ($get['svc_type'] == 'video dispatch') echo 'selected="selected"'; ?>
                                        value="video dispatch">video dispatch
                                </option>
                                <option <?php if ($get['svc_type'] == 'ambience listening call') echo 'selected="selected"'; ?>
                                        value="ambience listening call">ambience listening call
                                </option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input class="form-control" type="text" name="caller" value="<?= $get['caller'] ?: ''; ?>"
                                   placeholder="<?= Yii::t('app', 'caller') ?>">
                        </div>
                        <div class="col-md-2">
                            <input class="form-control" type="text" name="callee" value="<?= $get['callee'] ?: ''; ?>"
                                   placeholder="<?= Yii::t('app', 'callee') ?>">
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
                        <div class="col-md-2">
                            <label class="radio-inline" for="inlineRadio1">
                                <input type="radio"
                                       name="result" <?php if ($get['result'] == '1') echo 'checked="checked"'; ?>
                                       id="inlineRadio1" value="1"> <?= Yii::t('app', 'success') ?>
                            </label>
                            <label class="radio-inline" for="inlineRadio2">
                                <input type="radio"
                                       name="result" <?php if ($get['result'] == '2') echo 'checked="checked"'; ?>
                                       id="inlineRadio2" value="2"> <?= Yii::t('app', 'failed') ?>
                            </label>
                            <input type="hidden" name="user_name" value="<?= $get['user_name'] ?: ''; ?>">
                        </div>
                    </div>
                </div>
                <div class="panel-footer">
                    <button type="submit" onclick="$('input[name=\'user_name\']').val('')"
                            class="btn btn-default btn-block"><span
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
                                <th><?= $c['column_comment'] ?></th>
                            <?php endforeach; ?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($models as $v): ?>
                            <tr>
                                <td><?= $v['id'] ?></td>
                                <td><?= $v['svc_type'] ?></td>
                                <td><?= $v['session_id'] ?></td>
                                <td>
                                    <a href="<?= \yii\helpers\Url::to(['/user/base/view', 'user_name' => $v['caller']]) ?>"><?= $v['caller'] ?></a>
                                </td>
                                <td>
                                    <a href="<?= \yii\helpers\Url::to(['/user/base/view', 'user_name' => $v['callee']]) ?>"><?= $v['callee'] ?></a>
                                </td>
                                <!--                <td>--><? //=$v['caller']?><!--</td>-->
                                <!--                <td>--><? //=$v['callee']?><!--</td>-->
                                <td><?= date('Y-m-d H:i:s', $v['start_time']) ?></td>
                                <td><?= date('Y-m-d H:i:s', $v['end_time']) ?></td>
                                <td><?= $v['result'] ?></td>
                                <td><?= $v['cause'] ?></td>
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