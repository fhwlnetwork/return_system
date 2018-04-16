<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2016/5/11
 * Time: 16:23
 */


use yii\widgets\LinkPager;
use yii\helpers\Html;
use center\widgets\Alert;

$this->title = \Yii::t('app', 'user/interaction/index');



$canAdd = Yii::$app->user->can('user/interaction/add');
$canList = Yii::$app->user->can('user/interaction/list');
$canView = Yii::$app->user->can('user/interaction/view');
$canDelete = Yii::$app->user->can('user/interaction/del');

//权限操作
$isOnlyAdd = $canAdd && !$canList;
$errors = $model->getErrors();
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
                    'questionTypes'=> $questionTypes,
                    'action' => 'add',
                    'questionStates' => $questionStates
                ]);
                ?>
            </div>
        </div>
    <?php endif ?>

    <?php
    //是否有权限
    if ($canList):
        ?>
        <div class="panel panel-default">
            <div class="panel-heading"><strong><span
                        class="glyphicon glyphicon-list-alt text-small"></span> <?= Yii::t('app', 'list') ?></strong>
            </div>

            <?php if (!empty($list)) : ?>

                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th><?= Yii::t('app', 'question type') ?></th>
                        <th><?= Yii::t('app', 'question title') ?></th>
                        <th><?= Yii::t('app', 'question description') ?></th>
                        <th><?= Yii::t('app', 'question publish time') ?></th>
                        <th><?= Yii::t('app', 'question state') ?></th>
                        <th><?= Yii::t('app', 'question solution time') ?></th>
                        <th><?= Yii::t('app', 'operate type User Base') ?></th>
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
                            <?php foreach ($one as $k=>$v) {
                                if ($k == 'question_pub_at') {
                                    $v = date('Y-m-d H:i:s', $v);
                                }
                                if ($k == 'question_solution_time') {
                                    if ($v == 0) {
                                        $v = '';
                                    } else {
                                        $v = date('Y-m-d H:i:s', $v);
                                    }

                                }
                                echo "<td>{$v}</td>";
                            }
                            ?>

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

            <?php else: ?>
                <div class="panel-body">
                    <?= Yii::t('app', 'no record') ?>
                </div>
            <?php endif ?>
        </div>
    <?php endif ?>
</div>
