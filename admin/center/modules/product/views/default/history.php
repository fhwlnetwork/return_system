<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2018/3/18
 * Time: 20:16
 */

use yii\helpers\Html;
use center\widgets\Alert;
use yii\widgets\LinkPager;


$canAdd = Yii::$app->user->can('message/work/add');
$canList = Yii::$app->user->can('message/work/list');
$canView = Yii::$app->user->can('message/work/view');
$canDelete = Yii::$app->user->can('message/work/del');
$canEdit = Yii::$app->user->can('message/work/edit');

//权限操作
$isOnlyAdd = $canAdd && !$canList;
$errors = $model->getErrors();
$isStudent = \common\models\User::isStudent();
$this->title = $isStudent ? Yii::t('app', 'message/work/index') : '学生工作中心';
echo $this->render('/layouts/menu');
?>
<div class="page page-table">
    <?= Alert::widget() ?>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <form name="form_constraints" action="<?= \yii\helpers\Url::to(['work-history']) ?>"
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
                <?php if ($isStudent): ?>
                    <div class="pull-right">
                        <?= Html::a(Html::button('增加工作', ['class' => 'btn btn-info btn-xs']), '/product/work/add') ?>
                    </div>
                <?php else: ?>
                    <div class="pull-right">
                        <a type="button" class="btn btn-primary btn-sm"
                           href="<?= Yii::$app->urlManager->createUrl(array_merge(['/product/default/export'], $params, ['export' => 'excel'])) ?>"><span
                                    class="glyphicon glyphicon-log-out"></span><?= Yii::t('app', 'excel export') ?></a>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($list)) : ?>

                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th><?= Yii::t('app', '公司名称') ?></th>
                        <th><?= Yii::t('app', '工作名称') ?></th>
                        <th><?= Yii::t('app', '专业名称') ?></th>
                        <th><?= Yii::t('app', '薪资范围') ?></th>
                        <th><?= Yii::t('app', '开始时间') ?></th>
                        <th><?= Yii::t('app', '结束时间') ?></th>
                        <th><?= Yii::t('app', '毕业去向代码') ?></th>
                        <th><?= Yii::t('app', '单位组织机构代码') ?></th>
                        <th><?= Yii::t('app', '单位性质代码') ?></th>
                        <th><?= Yii::t('app', '单位行业代码') ?></th>
                        <th><?= Yii::t('app', '单位所在地代码') ?></th>
                        <th><?= Yii::t('app', '工作职位类别代码') ?></th>
                        
                        <?php if (!$flag): ?>
                            <th><?= Yii::t('app', '是否跨行') ?></th>
                        <?php else: ?>
                            <th><?= Yii::t('app', '操作') ?></th>
                        <?php endif; ?>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($list as $id => $one) { ?>
                        <tr>
                            <td></td>
                            <td><?= $one['company_name'] ?></td>
                            <td><?= $one['work_name'] ?></td>
                            <td><?= $one['major_name'] ?></td>
                            <td><?= $one['salary'] ?></td>
                            <td><?= date('Y-m-d H:i', $one['stime']) ?></td>
                            <td><?= $one['is_end'] ? '至今' : date('Y-m-d H:i', $one['utime']) ?></td>
                            <td><?= $one['Byqxdm'] ?></td>
                            <td><?= $one['Dwzzjgdm'] ?></td>
                            <td><?= $one['Dwxzdm'] ?></td>
                            <td><?= $one['Dwhydm'] ?></td>
                            <td><?= $one['Dwszddm'] ?></td>
                            <td><?= $one['Gzzwlbdm'] ?></td>
                            <?php if (!$flag): ?>
                                <td><?= $one['is_same'] ? '否' : '是' ?></td>
                            <?php else: ?>
                                <td>
                                    <?php echo Html::a(Html::button(Yii::t('app', 'edit'), ['class' => 'btn btn-danger btn-xs']),
                                        ['/product/work/edit', 'id' => $one['id']]); ?>
                                </td>
                            <?php endif; ?>
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
