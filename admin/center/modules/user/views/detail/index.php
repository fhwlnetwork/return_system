<?php
/**
 * @var yii\web\View $this
 * @var $list Array 列表
 * @var $model center\modules\user\models\Detail
 */

use yii\widgets\LinkPager;
use yii\helpers\Html;
use center\widgets\Alert;

$this->title = \Yii::t('app', 'User Add Detail');
$attributes = $model->getAttributesList();
$select = Yii::t('app', 'select type');
$attributes['type'] = ['' => "{$select}"] + $attributes['type'];
$canView = Yii::$app->user->can('user/detail/view');
?>

<div class="page page-table">

    <?= Alert::widget() ?>
    <form name="detailSearch" action="<?= \yii\helpers\Url::to(['index']) ?>" class="form-horizontal form-validation"
          method="get">
        <div class="panel" style="padding: 10px 15px 0 15px">

            <div class="form-group">
                <div class="col-sm-2">
                    <?= Html::dropDownList('type', isset($params['type']) ? $params['type'] : '', $attributes['type'], ['class' => 'form-control']) ?>
                </div>
                <div class="col-sm-2">
                    <div class="input-group">
                        <input type="text" class="form-control" name="mgr_name"
                               value="<?= isset($params['mgr_name']) ? $params['mgr_name'] : '' ?>"
                               placeholder="<?= Yii::t('app', 'operate operator') ?>"/>
                    </div>
                </div>
                <div class="col-sm-2">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search"
                               value="<?= isset($params['search']) ? $params['search'] : '' ?>"
                               placeholder="<?= Yii::t('app', 'enter a user name or name') ?>"/>
                    </div>
                </div>
                <div class="col-sm-2">
                    <?=
                    Html::input('text', 'add_time', isset($params['add_time']) ? $params['add_time'] : '', [
                        'class' => 'form-control inputDate',
                        'placeholder' => Yii::t('app', 'start opt time')
                    ]) ?>
                </div>
                <div class="col-sm-2">
                    <?=
                    Html::input('text', 'end_time', isset($params['end_time']) ? $params['end_time'] : '', [
                        'class' => 'form-control inputDate',
                        'placeholder' => Yii::t('app', 'end opt time')
                    ]) ?>
                </div>
                <div class="col-sm-1">
                    <?= Html::submitButton(Yii::t('app', 'search'), ['class' => 'btn btn-primary']) ?>
                </div>
            </div>

        </div>

        <div class="panel panel-default">
            <div style="float:right;margin-right:10px;margin-top:5px;">

                <a type="button" class="btn btn-default btn-sm"
                   href="<?= strstr(yii::$app->request->url, '&action=excel') ? yii::$app->request->url : yii::$app->request->url; ?><?= strstr(yii::$app->request->url, '?') ? '&' : '?'; ?>action=excel"><span
                            class="glyphicon glyphicon-log-out"></span>excel</a>

            </div>
            <div class="panel-heading">
                <strong>
                    <span class="glyphicon glyphicon-th-large"></span> <?= Yii::t('app', 'list') ?>
                </strong>
            </div>

            <?php if (!empty($list)) : ?>

            <table class="table table-hover">
                <thead>
                <tr>
                    <th>#</th>
                    <th><?= Yii::t('app', 'Account status') ?></th>
                    <th><?= Yii::t('app', 'network_user_login_name') ?></th>
                    <th><?= Yii::t('app', 'name') ?></th>
                    <th><?= Yii::t('app', 'operate time') ?></th>
                    <th><?= Yii::t('app', 'IP') ?></th>
                    <th><?= Yii::t('app', 'operate operator') ?></th>
                    <?php
                    if ($canView):
                        ?>
                        <th><?= Yii::t('app', 'show detail') ?></th>
                        <?php
                    endif
                    ?>
                    <!--                <th>--><? //= Yii::t('app', 'print') ?><!--</th>-->
                </tr>
                </thead>
                <tbody>
                <?php foreach ($list as $one) { ?>
                    <tr>
                        <td><?= $one['id'] ?></td>
                        <td><?= Html::encode(isset($attributes['type'][$one['type']]) ? $attributes['type'][$one['type']] : $one['type']) ?></td>
                        <td><?= Html::encode($one['user_name']) ?></td>
                        <td><?= Html::encode($one['user_real_name']) ?></td>
                        <td><?= Html::encode(date('Y-m-d H:i:s', $one['operate_time'])) ?></td>
                        <td><?= Html::encode($one['operate_ip']) ?></td>
                        <td><?= Html::encode($one['mgr_name']) ?></td>
                        <?php
                        if ($canView):
                            ?>
                            <td>
                                <?php if ($canView) {
                                    echo Html::a('<span class="glyphicon glyphicon-eye-open"></span>',
                                        ['view', 'id' => $one['id']],
                                        ['title' => Yii::t('app', 'data detail')]);
                                };

                                echo Html::a
                                (
                                    '&nbsp;&nbsp;&nbsp;&nbsp;<span class="glyphicon glyphicon-print"></span>',
                                    ['print', 'id' => $one['id'],'user_name' => $one['user_name']],
                                    ['title' => Yii::t('app', 'print'),'style' => 'text-decoration: none;']
                                );
                                ?>
                            </td>
                        <?php endif ?>
                    </tr>
                <?php } ?>
                </tbody>
            </table>

            <footer class="table-footer">
                <div class="row">
                    <div class="col-md-6">
                        <?=
                        Yii::t('app', 'pagination show page', [
                            'totalCount' => $pagination->totalCount,
                            'totalPage' => $pagination->getPageCount(),
                            'perPage' => '<input type=text name=offset size=3 value=' . $pagination->defaultPageSize . '>',
                            'pageInput' => '<input type=text name=page size=4>',
                            'buttonGo' => '<input type=submit value=go>',
                        ]) ?>
                    </div>
                    <div class="col-md-6 text-right">
                        <?= LinkPager::widget(['pagination' => $pagination, 'maxButtonCount' => 5]); ?>
                    </div>
                </div>
            </footer>

        </div>
    </form>
<?php else: ?>
    <div class="panel-body">
        <?= Yii::t('app', 'no record') ?>
    </div>
    <?php
endif ?>
</div>

</div>
