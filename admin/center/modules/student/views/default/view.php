<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2018/3/9
 * Time: 20:42
 */
$this->title = Yii::t('app', 'product/default/base');

use yii\helpers\Html;
use center\widgets\Alert;
use center\extend\Tool;
use yii\helpers\ArrayHelper;

/**
 * @var yii\web\View $this
 * @var center\modules\user\Base $model
 */

?>

<style>
    .form-control {
        display: block;
        width: 100%;
        height: 30px;
        padding: 6px 12px;
        font-size: 12px;
        line-height: 1.42857;
        color: #767676;
        background-color: white;
        background-image: none;
        border: 1px solid #cbd5dd;
        border-radius: 2px;
        -webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
        box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
        -webkit-transition: border-color ease-in-out 0.15s, box-shadow ease-in-out 0.15s;
        -o-transition: border-color ease-in-out 0.15s, box-shadow ease-in-out 0.15s;
        transition: border-color ease-in-out 0.15s, box-shadow ease-in-out 0.15s;
    }
</style>

<div class="page page-profile" data-ng-controller="userProfileCtrl">
    <?= Alert::widget() ?>
    <div class="row">
        <!--左侧-->
        <div class="col-md-6">
            <!--用户基本信息-->
            <div class="panel panel-profile">
                <div class="panel-heading bg-primary clearfix" title="<?= Yii::t('app', 'refresh'); ?>"
                     style="cursor:pointer" onclick="location.reload();">
                    <!--<a href="" class="pull-left profile">
                        <img alt="" src="/images/g1.jpg" class="img-circle img80_80">
                    </a>-->
                    <h3><?= Html::encode($model->username) ?></h3>
                    <?= Html::a(Html::button('完善个人信息', ['class' => 'btn btn-info btn-sm']), '/auth/assign/update?id='.$id)?>
                </div>
                <ul class="list-group list-unstyled list-info">
                    <li>
                        <i class="icon fa fa-users"></i><label><?= Yii::t('app', '姓名') ?></label><?= $model->person_name ?>
                    </li>
                    <li>
                        <i class="icon fa fa-users"></i><label><?= Yii::t('app', '手机') ?></label><?= $model->mobile_phone ?>
                    </li>
                    <li>
                        <i class="icon fa fa-users"></i><label><?= Yii::t('app', '邮箱') ?></label><?= $model->email ?>
                    </li>
                    <li>
                        <i class="icon fa fa-users"></i><label><?= Yii::t('app', 'group id') ?></label><?= Html::encode(\center\modules\auth\models\SrunJiegou::getOwnParent($model->mgr_org)) ?>
                    </li>
                    <li ng-show="isCollapsed">
                        <i class="icon fa fa-users"></i><label><?= Yii::t('app', '民族') ?></label><?= $model->nation ?>
                    </li>
                    <li ng-show="isCollapsed">
                        <i class="icon fa fa-users"></i><label><?= Yii::t('app', '性别') ?></label><?= $model->sex ?>
                    </li>
                    <li ng-show="isCollapsed">
                        <i class="icon fa fa-users"></i><label><?= Yii::t('app', '身份证') ?></label><?= $model->id_number ?>
                    </li>
                    <li ng-show="isCollapsed">
                        <i class="icon fa fa-users"></i><label><?= Yii::t('app', '专业') ?></label><?= Html::encode($model->major_name) ?>
                    </li>
                    <li>
                        <i class="icon fa fa-users"></i><label><?= Yii::t('app', '入校时间') ?></label><?= date('Y-m-d', $model->begin_time) ?>
                    </li>
                    <li>
                        <i class="icon fa fa-users"></i><label><?= Yii::t('app', '毕业时间') ?></label><?= Html::encode(date('Y-m-d', $model->stop_time)) ?>
                    </li>
                </ul>
                <button type="button" class="btn btn-w-md btn-gap-v btn-primary" ng-click="isCollapsed = !isCollapsed">
                    查看更多            <i ng-show="!isCollapsed" class="fa fa-chevron-down"></i>
                    <i ng-show="isCollapsed" class="fa fa-chevron-up ng-hide"></i>
                </button>
            </div>
        </div>
        <!--右侧-->
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading"><strong><span
                                class="glyphicon glyphicon-th-large"></span> <?= Yii::t('app', '工作经历') ?>
                    </strong>
                    <div class="pull-right">
                        <?= Html::a(Html::button('查看更多 >>', [
                            'class' => 'btn btn-primary btn-sm',
                            'style' => 'margin-top:-5px;'
                        ]), '/product/default/work-history') ?>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="divider"></div>
                    <?= $works ? $works : '无工作经历，请添加' ?>
                </div>
            </div>
        </div>
    </div>
    <!--下边横排-->
    <div class="row">
        <!--已订购产品-->
        <div class="col-md-12">
            <!--已订购产品信息-->
            <div class="panel panel-default">
                <div class="panel-heading"><strong><span
                                class="glyphicon glyphicon-th-large"></span> <?= Yii::t('app', 'product/default/works') ?>
                    </strong>
                    <div class="pull-right">
                        <?= Html::a(Html::button('查看更多 >>', [
                            'class' => 'btn btn-primary btn-sm',
                            'style' => 'margin-top:-5px;'
                        ]), '/product/default/works') ?>
                    </div>
                </div>
                <div class="panel-body">
                    <table class="table table-bordered table-striped table-responsive">
                        <thead>
                        <tr>
                            <th nowrap="nowrap">
                                <div class="th">标题</div>
                            </th>
                            <th nowrap="nowrap">
                                <div class="th">描述</div>
                            </th>
                            <th nowrap="nowrap">
                                <div class="th">发布状态</div>
                            </th>
                            <th nowrap="nowrap">
                                <div class="th">发布时间</div>
                            </th>
                            <th nowrap="nowrap">
                                <div class="th">操作者</div>
                            </th>
                            <th nowrap="nowrap">
                                <div class="th">备注</div>
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($pubs as $pub): ?>
                            <tr>
                                <td><?= $pub['title'] ?></td>
                                <td><?= $pub['desc'] ?></td>
                                <td><?= $status[$pub['status']] ?></td>
                                <td><?= date('Y-m-d H:i', $pub['ctime']) ?></td>
                                <td><?= $pub['operator']?></td>
                                <td><?= $pub['remark']?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                </div>
            </div>
        </div>
        <!--用户日志-->
        <div class="col-md-12">
            <div class="panel panel-default">
            </div>
        </div>
    </div>
</div>

<?php
$this->registerJs("
    $('#tab a').click(function (e) {
          e.preventDefault();//阻止a链接的跳转行为
          $(this).tab('show');//显示当前选中的链接及关联的content
    })
 ");
?>

<script>

</script>





