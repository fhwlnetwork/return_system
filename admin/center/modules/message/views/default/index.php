<?php

use yii\helpers\Html;
use center\widgets\Alert;
use yii\widgets\LinkPager;

$this->title = Yii::t('app', 'message/default/index');

$canAdd = Yii::$app->user->can('user/interaction/add');
$canList = Yii::$app->user->can('user/interaction/list');
$canView = Yii::$app->user->can('user/interaction/view');
$canDelete = Yii::$app->user->can('message/default/delete');

//权限操作
$isOnlyAdd = $canAdd && !$canList;
$errors = $model->getErrors();
$attr = $model->getAttributesList();
?>
    <div class="page page-table">
        <?= Alert::widget() ?>
        <div class="col-md-12">
            <div class="panel panel-default">
                <form name="form_constraints" action="<?= \yii\helpers\Url::to(['index']) ?>"
                      class="form-horizontal form-validation" method="get">
                    <div class="panel-body">
                        <?php
                        if ($model->searchInput) {
                            $searchInput = $model->searchInput;
                            $count = count($searchInput);
                            $i = 0;
                            foreach ($model->searchInput as $key => $value) {
                                if ($i % 6 == 0) {
                                    echo '<div class="form-group">';
                                }
                                //列表形式
                                if (isset($value['list']) && !empty($value['list'])) {
                                    $content = Html::dropDownList($key, isset($params[$key]) ? $params[$key] : '', $value['list'], ['class' => 'form-control',]);
                                } //日期插件格式
                                else if ($key == 'ctime') {
                                    $content = Html::input('text', $key, isset($params[$key]) ? $params[$key] : '', [
                                        'class' => 'form-control inputDateTime',
                                        'placeHolder' => isset($value['label']) ? $value['label'] : '',
                                        'id' => isset($value['id']) ? $value['id'] : '',
                                    ]);
                                } //普通文本格式
                                else {
                                    $content = Html::input('text', $key, isset($params[$key]) ? $params[$key] : '', [
                                        'class' => 'form-control',
                                        'placeHolder' => isset($value['label']) ? $value['label'] : '',
                                        'id' => isset($value['id']) ? $value['id'] : '',
                                    ]);
                                }

                                echo Html::tag('div', $content, ['class' => 'col-md-2']);

                                $i++;
                                if ($i % 6 == 0 || $i == $count) {
                                    echo '</div>';
                                }
                            }
                        }
                        ?>
                        <?= Html::submitButton(Yii::t('app', 'search'), ['class' => 'btn btn-success']) ?>
                    </div>

            </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading"><strong><span
                            class="glyphicon glyphicon-list-alt text-small"></span> <?= Yii::t('app', 'list') ?>
                </strong>
            </div>

            <?php if (!empty($list)) : ?>

                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th><?= Yii::t('app', '留言用户') ?></th>
                        <th><?= Yii::t('app', '留言内容') ?></th>
                        <th><?= Yii::t('app', '留言状态') ?></th>
                        <th><?= Yii::t('app', '留言时间') ?></th>
                        <th><?= Yii::t('app', '更新时间') ?></th>
                        <?php
                        if ($canView || $canDelete):
                            ?>
                            <th><?= Yii::t('app', 'operate') ?></th>
                        <?php
                        endif
                        ?>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($list as $id => $one) { ?>
                        <tr>
                            <td></td>
                            <td><?= $one['username'] ?></td>
                            <td><?= $one['message'] ?></td>
                            <td><?= $attr['status'][$one['status']] ?></td>
                            <td><?= date('Y-m-d H:i:s', $one['ctime']) ?></td>
                            <td><?= date('Y-m-d H:i:s', $one['utime']) ?></td>
                            <?php
                            if ($canDelete || $canView):
                                ?>
                                <td>
                                    <?php if ($canView) {
                                        echo Html::a(Html::button(Yii::t('app', 'view'), ['class' => 'btn btn-warning btn-xs']),
                                            ['view', 'id' => $one['id']],
                                            ['title' => Yii::t('app', 'view')]);
                                    } ?>
                                    <?php echo Html::a(Html::button(Yii::t('app', 'edit'), ['class' => 'btn btn-danger btn-xs']),
                                        ['edit', 'id' => $one['id']]); ?>
                                    <?php if ($one['status'] == 2) : ?>
                                        <?php echo Html::button(Yii::t('app', '查看原因'), ['class' => 'btn btn-info btn-xs view-remark', 'remark' => $one['remark']]) ?>
                                    <?php endif; ?>
                                    <?php if ($canDelete): ?>
                                        <?php echo Html::a(Html::button(Yii::t('app', 'delete'), ['class' => 'btn btn-danger btn-xs']),
                                            ['delete', 'id' => $one['id']], [
                                                'title' => Yii::t('app', 'User Delete'),
                                                'data' => [
                                                    'method' => 'post',
                                                    'confirm' => Yii::t('app', '请确定要删除吗？'),
                                                ],
                                            ]); ?>
                                    <?php endif; ?>
                                </td>
                            <?php endif ?>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>

                <div class="divider"></div>

                <footer class="table-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <?=
                            Yii::t('app', 'pagination show1', [
                                'totalCount' => $pagination->totalCount,
                                'totalPage' => $pagination->getPageCount(),
                                'perPage' => $pagination->pageSize,
                            ]) ?>

                        </div>
                        <div class="col-md-6 text-right">
                            <?php
                            echo LinkPager::widget(['pagination' => $pagination, 'maxButtonCount' => 5]);
                            ?>
                        </div>
                    </div>
                </footer>
                </form>

            <?php else: ?>
                <div class="panel-body">
                    <?= Yii::t('app', 'no record') ?>
                </div>
            <?php endif ?>
        </div>
    </div>
<?php
$js = <<< JS
     $('.view-remark').click(function()
     {
         layer.alert($(this).attr('remark'));
     })
JS;
$this->registerJs($js);