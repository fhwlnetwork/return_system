<?php
use yii\helpers\Html;
use center\widgets\Alert;
use yii\widgets\LinkPager;

$this->title = Yii::t('app', 'message/news/index');

$canAdd = Yii::$app->user->can('message/news/add');
$canList = Yii::$app->user->can('message/news/list');
$canView = Yii::$app->user->can('message/news/view');
$canDelete = Yii::$app->user->can('message/news/del');
$canEdit = Yii::$app->user->can('message/news/edit');

//权限操作
$isOnlyAdd = $canAdd && !$canList;
$errors = $model->getErrors();
$attr = $model->getAttributesList();
?>
<div class="page page-table">
    <?= Alert::widget() ?>

    <?php
    //权限操作
    if ($canAdd):
        ?>
        <?php if (!$isOnlyAdd): ?>
        <button type="button" class="btn btn-w-md btn-gap-v btn-primary" ng-click="isCollapsed = !isCollapsed">
            <?= Yii::t('app', 'add') ?>
            <i ng-show="!isCollapsed" class="fa fa-chevron-down"></i>
            <i ng-show="isCollapsed" class="fa fa-chevron-up"></i>
        </button>
    <?php endif ?>
        <div class="panel panel-default" data-ng-controller="packageController"
             <?php if (!empty($errors)){ ?>ng-cloak collapse="isCollapsed" <?php }elseif (!$isOnlyAdd){ ?>ng-cloak
             collapse="!isCollapsed"<?php } ?>>
            <div class="panel-heading">
                <strong><span class="glyphicon glyphicon-plus"></span> <?= Yii::t('app', 'add'); ?></strong>
            </div>
            <div class="panel-body">
                <?php
                //展现表单
                echo $this->render('_form', [
                    'model' => $model,
                    'action' => 'add',
                    'types' => $types
                ]);
                ?>
            </div>
        </div>
    <?php endif ?>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <form name="form_constraints" action="<?=\yii\helpers\Url::to(['index'])?>" class="form-horizontal form-validation" method="get">
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
                                }
                                //日期插件格式
                                else  if ($key == 'ctime') {
                                    $content = Html::input('text', $key, isset($params[$key]) ? $params[$key] : '', [
                                        'class' => 'form-control inputDateTime',
                                        'placeHolder' => isset($value['label']) ? $value['label'] : '',
                                        'id' => isset($value['id']) ? $value['id'] : '',
                                    ]);
                                }
                                //普通文本格式
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
                            class="glyphicon glyphicon-list-alt text-small"></span> <?= Yii::t('app', 'list') ?></strong>
            </div>

            <?php if (!empty($list)) : ?>

                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th><?= Yii::t('app', '文章标题') ?></th>
                        <th><?= Yii::t('app', '文章描述') ?></th>
                        <th><?= Yii::t('app', '点击率') ?></th>
                        <th><?= Yii::t('app', '文章分类') ?></th>
                        <th><?= Yii::t('app', '发布时间') ?></th>
                        <th><?= Yii::t('app', '最后修改时间') ?></th>
                        <?php
                        if ($canView || $canDelete || $canEdit):
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
                            <td><?= $one['title']?></td>
                            <td><?= $one['desc']?></td>
                            <td><?= $one['click_rate']?></td>
                            <td><?= $attr['types'][$one['type']]?></td>
                            <td><?=  is_numeric($one['ctime']) ? date('Y-m-d H:i', $one['ctime']) : $one['ctime']?></td>
                            <td><?=  is_numeric($one['utime']) ? date('Y-m-d H:i', $one['utime']) : $one['utime']?></td>
                            <?php
                            if ($canDelete || $canView):
                                ?>
                                <td>
                                    <?php if ($canView) {
                                        echo Html::a(Html::button(Yii::t('app', 'view'), ['class' => 'btn btn-warning btn-xs']),
                                            ['view', 'id' => $one['id']],
                                            ['title' => Yii::t('app', 'view')]);
                                    } ?>
                                    <?php if (false) {
                                        echo Html::a(Html::button(Yii::t('app', 'delete'), ['class' => 'btn btn-danger btn-xs']),
                                            ['delete', 'id' => $id], [
                                                'title' => Yii::t('app', 'delete'),
                                                'data' => [
                                                    'method' => 'post',
                                                    'confirm' => Yii::t('app', 'package help9'),
                                                ],
                                            ]);
                                    } ?>
                                    <?php if ($canEdit) {
                                        echo Html::a(Html::button(Yii::t('app', 'edit'), ['class' => 'btn btn-danger btn-xs']),
                                            ['edit', 'id' => $one['id']]);
                                    } ?>

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
                            ])?>

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
