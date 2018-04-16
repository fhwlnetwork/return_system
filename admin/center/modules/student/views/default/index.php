<?php

use yii\widgets\LinkPager;
use yii\helpers\Html;
use center\widgets\Alert;
use center\modules\auth\models\SrunJiegou;
use center\assets\ZTreeAsset;

$this->title = \Yii::t('app', 'student/default/index');
$attributes = $model->getAttributesList();


//权限
$canEdit = Yii::$app->user->can('student/default/edit');
$canAdd = Yii::$app->user->can('auth/assign/signup');
$canView = Yii::$app->user->can('student/default/view');
//ztree 搜索用
ZTreeAsset::register($this);
ZTreeAsset::addZtreeSelectMulti($this);

?>
<style type="text/css" xmlns="http://www.w3.org/1999/html">
    .ztree li a.curSelectedNode span {
        background-color: #0088cc;
        color: #fff;
        border-radius: 2px;
        padding: 2px;
    }
</style>
<div class="page">
    <?= Alert::widget() ?>

    <form name="form_constraints" action="<?= \yii\helpers\Url::to(['index']) ?>"
          class="form-horizontal form-validation" method="get">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
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
                                    $content = Html::dropDownList($key, isset($params[$key]) ? $params[$key] : '', $value['list'], ['class' => 'form-control']);
                                } //普通文本格式
                                else {
                                    $content = Html::input('text', $key, isset($params[$key]) ? $params[$key] : '', [
                                        'class' => 'form-control' . (isset($value['class']) ? $value['class'] : ''),
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

                        <div class="form-group" ng-cloak ng-show="advanced==1">
                            <div class="col-md-2"><?= Yii::t('app', 'user base help2') ?></div>
                            <div class="col-md-10">
                                <?= Html::checkboxList('showField[]', $params['showField'], $model->searchField, ['class' => 'drag_inline']) ?>
                            </div>
                        </div>
                        <!--组织结构-->
                        <div class="form-group" ng-cloak ng-show="advanced==1">
                            <div class="col-md-2"><?= Yii::t('app', 'organization help4') ?></div>
                            <div class="col-md-10">
                                <div class="panel panel-default">
                                    <div class="panel-body" style="max-height: 500px; overflow-y: auto;">
                                        <?= Html::hiddenInput("group_id", '', [
                                            'id' => 'zTreeId',
                                        ]) ?>
                                        <div><?= Yii::t('app', 'organization help5') ?><span class="text-primary"
                                                                                             id="zTreeSelect"></span>
                                        </div>
                                        <div id="zTreeAddUser" class="ztree"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?= Html::submitButton(Yii::t('app', 'search'), ['class' => 'btn btn-success']) ?>
                        &nbsp;&nbsp;
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <section class="panel panel-default table-dynamic">
                    <div class="panel-heading">
                        <strong><span class="glyphicon glyphicon-th-large"></span> <?= Yii::t('app', 'search result') ?>
                        </strong>
                        <div class="pull-right">
                            <?php if ($canAdd): ?>
                                <?= Html::a(Html::button('增加学生', ['class' => 'btn btn-danger btn-sm', 'style' => 'margin-top:-5px']), '/auth/assign/signup') ?>
                            <?php endif; ?>
                        </div>
                    </div>


                    <div style="clear:both;"></div>

                    <?php if (!empty($list)): ?>
                        <div style="overflow-x: auto;">
                            <table class="table table-bordered table-striped table-responsive">
                                <thead>
                                <tr>
                                    <?php
                                    if (isset($params['showField']) && $params['showField']) {
                                        //不需要排序的字段
                                        $no_sort_field = ['group_id', 'products_id', 'user_available', 'user_balance', 'user_name', 'user_real_name', 'user_online_status'];
                                        echo '<td><input type="checkbox" id="all"/></td>';
                                        foreach ($params['showField'] as $value) {
                                            if (in_array($value, $no_sort_field)) {
                                                echo '<th nowrap="nowrap"><div class="th">' . $model->searchField[$value] . '</div></th>';
                                            } else {
                                                $newParams = $params;
                                                echo '<th nowrap="nowrap"><div class="th">';
                                                echo $model->searchField[$value];

                                                $newParams['orderBy'] = $value;
                                                array_unshift($newParams, 'index');

                                                //上面按钮
                                                $newParams['sort'] = 'asc';
                                                $upActive = (isset($params['orderBy'])
                                                    && $params['orderBy'] == $value
                                                    && isset($params['sort'])
                                                    && $params['sort'] == 'asc') ? 'active' : '';
                                                echo Html::a('<span class="glyphicon glyphicon-chevron-up ' . $upActive . '"></span>', $newParams);

                                                //下面按钮
                                                $newParams['sort'] = 'desc';
                                                $downActive = (isset($params['orderBy'])
                                                    && $params['orderBy'] == $value
                                                    && isset($params['sort'])
                                                    && $params['sort'] == 'desc') ? 'active' : '';
                                                echo Html::a('<span class="glyphicon glyphicon-chevron-down ' . $downActive . '"></span>', $newParams);

                                                echo '</div></th>';
                                            }
                                        }
                                    }

                                    ?>

                                        <th nowrap="nowrap">
                                            <div class="th"><?= Yii::t('app', 'operate') ?></div>
                                        </th>

                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($list as $one): ?>
                                    <tr>
                                        <td><input type="checkbox" name="id[]" value="<?php echo $one['id'] ?>"/></td>
                                        <?php foreach ($params['showField'] as $value): ?>
                                            <td>
                                                <?php
                                                //用户名
                                                if ($value == 'username') {
                                                    echo Html::a($one[$value], ['view', 'username' => $one[$value]]);
                                                } //用户组
                                                else if ($value == 'mgr_org') {
                                                    echo Html::encode(SrunJiegou::getOwnParent([$one[$value]])); //显示层级用户组
                                                } //产品
                                                else if (preg_match('/time/', $value)) {
                                                    echo date('Y-m-d', $one[$value]);
                                                } else if ($value == 'user_balance') {
                                                    echo isset($one['user_balance']) ? implode('<br>', $one['user_balance']) : '';
                                                } //状态
                                                else if ($value == 'user_available' && isset($one['user_available'])) {
                                                    $available_css = $one['user_available'] == 0 ? 'label-success' : 'label-danger';
                                                    echo '<span class="label ' . $available_css . ' label-xs">' . $attributes['user_available'][$one['user_available']] . '</span>';
                                                } //在线状态
                                                else if ($value == 'user_online_status' && isset($one['user_online_status'])) {
                                                    $available_css = $one['user_online_status'] == 1 ? 'btn-success' : 'btn-xs';
                                                    echo '<button class="btn ' . $available_css . ' btn-xs">' . $attributes['user_online_status'][$one['user_online_status']] . '</button>';
                                                } //过期时间
                                                else if ($value == 'user_expire_time') {
                                                    echo $one['user_expire_time'] == 0 ? Yii::t('app', 'user expire time2') : date('Y-m-d H:i', $one['user_expire_time']);
                                                } //时间格式的字段
                                                else if (in_array($value, ['user_create_time', 'user_update_time'])) {
                                                    echo date('Y-m-d H:i:s', $one[$value]);
                                                } //列表形式的字段
                                                else if (isset($attributes[$value]) && !empty($attributes[$value])) {
                                                    if ($one[$value] !== '' || !is_null($one[$value])) {
                                                        echo Html::encode($attributes[$value][$one[$value]]);
                                                    }
                                                } else {
                                                    echo Html::encode($one[$value]);
                                                }
                                                ?>
                                            </td>
                                        <?php endforeach ?>

                                            <td>
                                                <?php  echo Html::a(Html::button(Yii::t('app', 'view'), ['class' => 'btn btn-success btn-xs']), ['view', 'id' => $one['id']], ['title' => Yii::t('app', 'view')]) ?>

                                                <?php  echo Html::a(Html::button(Yii::t('app', 'edit'), ['class' => 'btn btn-warning btn-xs']), ['/auth/assign/update', 'id' => $one['id']], ['title' => Yii::t('app', 'edit')]) ?>
                                            </td>

                                    </tr>
                                <?php endforeach ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="divider"></div>
                        <footer class="table-footer">
                            <div class="row">
                                <div class="col-md-6">
                                    <?php
                                    echo Yii::t('app', 'pagination show page', [
                                        'totalCount' => $pagination->totalCount,
                                        'totalPage' => $pagination->getPageCount(),
                                        'perPage' => '<input type=text name=offset size=3 value=10>',
                                        'pageInput' => '<input type=text name=page size=4>',
                                        'buttonGo' => '<input type=submit value=go>',
                                    ]);
                                    ?>
                                </div>
                                <div class="col-md-6 text-right">
                                    <?= LinkPager::widget(['pagination' => $pagination, 'maxButtonCount' => 5]); ?>
                                </div>
                            </div>
                        </footer>

                    <?php else: ?>
                        <div class="panel-body">
                            <?= Yii::t('app', 'no record') ?>
                        </div>
                    <?php endif ?>
                </section>
            </div>
        </div>
    </form>
</div>

<?php
$this->registerJs("
    createTree('zTreeAddUser');
");
$groupId = isset($params['group_id']) ? $params['group_id'] : '';
//声明ztree当前选中的id
$this->registerJs("
    var currentZTreeId = '" . $groupId . "';
", yii\web\View::POS_BEGIN);

?>
<script>

</script>
