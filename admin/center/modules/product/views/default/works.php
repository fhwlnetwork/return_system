<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2018/3/9
 * Time: 20:42
 */

use center\widgets\Alert;
use yii\widgets\LinkPager;
use yii\helpers\Html;


$this->title = Yii::t('app', 'product/default/works');

echo $this->render('/layouts/menu');
$attr = $model->getAttributesList();
//权限
$canEdit = Yii::$app->user->can('product/work/pub-edit');
?>

<div class="page page-table">
    <?= Alert::widget() ?>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <form name="form_constraints" action="<?= \yii\helpers\Url::to(['works']) ?>"
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
                                        'class' => 'form-control inputDate',
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
                <?php if (\common\models\User::isStudent()): ?>
                    <div class="pull-right">
                        <?= Html::a(Html::button('发布作品', ['class' => 'btn btn-info btn-sm', ['style' => 'margin-top:-5px;']]), '/product/default/works-add') ?>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($list)) : ?>

                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th><?= Yii::t('app', '标题') ?></th>
                        <th><?= Yii::t('app', '描述') ?></th>
                        <th><?= Yii::t('app', '发布状态') ?></th>
                        <th><?= Yii::t('app', '发布时间') ?></th>
                        <th><?= Yii::t('app', '修改时间') ?></th>
                        <th><?= Yii::t('app', '发布者') ?></th>
                        <th><?= Yii::t('app', 'operate') ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($list as $id => $one) { ?>
                        <tr>
                            <td></td>
                            <td><?= $one['title'] ?></td>
                            <td><?= $one['desc'] ?></td>
                            <td><?= $attr['status'][$one['status']] ?></td>
                            <td><?= date('Y-m-d H:i', $one['ctime']) ?></td>
                            <td><?= date('Y-m-d H:i', $one['utime']) ?></td>
                            <td><?= $one['stu_name'] ?></td>
                            <td>
                                <?= Html::a(Html::button(Yii::t('app', 'view'), ['class' => 'btn btn-warning btn-xs']),
                                    ['/product/work/pub-view', 'id' => $one['id']],
                                    ['title' => Yii::t('app', 'view')]);?>
                                <?php if ($canEdit): ?>
                                    <?php echo Html::a(Html::button(Yii::t('app', 'edit'), ['class' => 'btn btn-danger btn-xs']),
                                        ['/product/work/pub-edit', 'id' => $one['id']]);?>
                                    <?php if ($one['status'] == 2) : ?>
                                        <?php echo Html::button(Yii::t('app', '查看原因'), ['class' => 'btn btn-info btn-xs view-remark', 'remark' => $one['remark']])?>
                                    <?php endif;?>
                                <?php endif;?>
                            </td>
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


