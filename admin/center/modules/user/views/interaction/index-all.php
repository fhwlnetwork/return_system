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

$this->title = \Yii::t('app', 'user/interaction/index-all');
$attributes = $model->getAttributesList();



$canList = Yii::$app->user->can('user/complaints/index-all');
$canView = Yii::$app->user->can('user/complaints/view-all');
$canEdit = Yii::$app->user->can('user/complaints/edit');
$canDelete = Yii::$app->user->can('user/complaints/del');

//权限操作
$isOnlyAdd = $canAdd && !$canList;
$errors = $model->getErrors();
?>
<div class="page page-table">
    <?= Alert::widget() ?>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <form name="form_constraints" action="<?=\yii\helpers\Url::to(['index-all'])?>" class="form-horizontal form-validation" method="get">
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
                            else  if ($key == 'question_pub_time') {
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

                </div>
               <div style="margin-left:15px;margin-bottom:25px;margin-top:-10px;"><?= Html::submitButton(Yii::t('app', 'search'), ['class' => 'btn btn-success']) ?></div>
               </form>
            </div>
    </div>
    <div class="col-md-12">
        <?php
        //是否有权限
        if ($canList):
            ?>

                <section class="panel panel-default table-dynamic" style="border:none;">
                    <div class="panel-heading"><strong><span
                                class="glyphicon glyphicon-th-large"></span> <?= Yii::t('app', 'search result') ?>
                        </strong>
                    </div>

                    <?php if (!empty($list)) : ?>

                        <table class="table table-bordered table-striped table-responsive table-hover">
                            <thead>
                            <tr style="height: 30px; line-height: 30px;align-content: center;">
                                <?php
                                if( isset($params['showField']) && $params['showField'] ){
                                    //不需要排序的字段
                                    $no_sort_field = ['question_title', 'question_type',  'products_key', 'question_state'];
                                    echo '<td><input type="checkbox" id="all"/></td>';
                                    foreach( $params['showField'] as $value ){
                                        if(in_array($value, $no_sort_field)){
                                            echo '<th nowrap="nowrap"><div class="th">'.$model->searchField[$value].'</div></th>';
                                        }
                                        else{
                                            $newParams = $params;
                                            echo '<th nowrap="nowrap"><div class="th">';
                                            echo $model->searchField[$value];

                                            $newParams['orderBy'] = $value;
                                            array_unshift($newParams, '/user/interaction/index-all');

                                            //上面按钮
                                            $newParams['sort'] = 'asc';
                                            $upActive = (isset($params['orderBy'])
                                                && $params['orderBy'] == $value
                                                && isset($params['sort'])
                                                && $params['sort'] == 'asc') ? 'active' : '';
                                            echo Html::a('<span class="glyphicon glyphicon-chevron-up '.$upActive.'"></span>', $newParams);

                                            //下面按钮
                                            $newParams['sort'] = 'desc';
                                            $downActive = (isset($params['orderBy'])
                                                && $params['orderBy'] == $value
                                                && isset($params['sort'])
                                                && $params['sort'] == 'desc') ? 'active' : '';
                                            echo Html::a('<span class="glyphicon glyphicon-chevron-down '.$downActive.'"></span>', $newParams);

                                            echo '</div></th>';
                                        }
                                    }
                                }

                                ?>
                                <?php if($canView || $canEdit || $canDelete):?>
                                    <th nowrap="nowrap"><div class="th"><?=Yii::t('app', 'operate')?></div></th>
                                <?php endif ?>
                            </tr>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($list as $id => $one) { ?>
                                <tr>
                                    <?php foreach ($one as $k => $v) {
                                        if (in_array($k, ['question_pub_at'])) {
                                            $v = date('Y-m-d H:i:s', $v);
                                        }
                                        if ($k == 'bug_attach') {
                                            continue;
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
                                    if ($canDelete || $canEdit):
                                        ?>
                                        <td>
                                            <?php if ($canView) {
                                                echo Html::a(Html::button(Yii::t('app', 'view'), ['class' => 'btn btn-success btn-xs']),
                                                    ['view-all', 'id' => $one['id']],
                                                    ['title' => Yii::t('app', 'view')]);
                                            } ?>
                                            <?php if ($canEdit) {
                                                echo Html::a(Html::button(Yii::t('app', 'edit'), ['class' => 'btn btn-warning btn-xs']),
                                                    ['edit', 'id' => $one['id']],
                                                    ['title' => Yii::t('app', 'edit')]);
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
                                    ]) ?>

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
                </section>

        <?php endif ?>
    </div>
</div>