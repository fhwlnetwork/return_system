<?php
use center\widgets\Alert;
use yii\helpers\Html;
use center\extend\Tool;

$this->title = \Yii::t('app', 'illegal user');
$canDel = Yii::$app->user->can('user/illegal/delete');
?>

<div class="page page-table" data-ng-controller="payCtrl">
<?= Alert::widget() ?>
<!--搜索用户页面-->
<section class="panel panel-default table-dynamic">
    <div class="panel-heading"><strong><span class="glyphicon glyphicon-th"></span> <?= Yii::t('app','illegal user')?> </strong></div>
    <div class="panel-body">
        <form class="form-validation form-horizontal ng-pristine ng-valid" name="searchForm"
              action="<?=\yii\helpers\Url::to(['/user/illegal/index'])?>" method="get" role="form">

            <div class="col-lg-2" ng-init="user_name='<?=!empty($params) ? $params['user_name'] : ''?>'">
                <input type="text" class="form-control" name="user_name"
                       data-ng-model="user_name"
                       value="<?=!empty($params) ? $params['user_name'] : ''?>" placeholder="<?= Yii::t('app', 'account')?>">
            </div>
            <div class="col-lg-2">
                <input type="text" class="form-control" name="mac"
                       value="<?=!empty($params) ? $params['mac'] : ''?>" placeholder="<?= Yii::t('app', 'mac')?>">
            </div>
            <div class="col-lg-8">
                <?= \yii\helpers\Html::submitButton(Yii::t('app', 'search'), [
                    'class' => 'btn btn-success',
                    'data-ng-disabled'=>'searchForm.$invalid'
                ])?>
                <label><small><i class="glyphicon glyphicon-volume-up"></i><?= Yii::t('app', 'illegal user help1')?></small></label>
            </div>
        </form>
    </div>
</section>

<?php if($params): ?>
    <!--展示用户信息页面-->
    <div class="panel panel-default">
        <div class="panel-body">
            <!--如果有数据-->
            <?php if($lists): ?>
                <div class="row">
                    <!--<div class="col-sm-2">产品信息：</div>-->
                    <div class="col-sm-12">
                        <table class="table">
                            <thead>
                            <tr>
                                <td><?= Yii::t('app', 'account')?></td>
                                <td><?= Yii::t('app', 'user real name')?></td>
                                <td><?= Yii::t('app', 'mac')?></td>
                                <td><?= Yii::t('app', 'ip')?></td>
                                <td><?= Yii::t('app', 'group id')?></td>
                                <td><?= Yii::t('app', 'err msg')?></td>
                                <td><?= Yii::t('app', 'operate')?></td>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($lists as $v): ?>
                                <tr>
                                    <td><?= Html::encode($v['user_name'])?></td>
                                    <td><?= Html::encode($v['user_real_name'])?></td>
                                    <td><?= Html::encode($v['mac'])?></td>
                                    <td><?= Html::encode($v['ip'])?></td>
                                    <td><?= Html::encode($v['user_group'])?></td>
                                    <td><?= Html::encode($v['err_msg'])?></td>
                                    <td>
                                        <?php if($canDel) echo Html::a(Html::button(Yii::t('app', 'delete'), ['class'=>'btn btn-success btn-xs']), ['delete', 'user_name'=>$params['user_name'], 'mac'=>$v['mac']], ['title'=>Yii::t('app', 'delete'),'data-confirm' => Yii::t('app','confirm delete')])?>
                                    </td>
                                </tr>
                            <?php endforeach ?>
                            </tbody>
                        </table>
                        <footer class="table-footer">
                            <div class="row">
                                <div class="col-md-6">
                                </div>
                                <div class="col-md-6 text-right">
                                    <?php
                                    echo \yii\widgets\LinkPager::widget(['pagination' => $pagination, 'maxButtonCount' => 5]);
                                    ?>
                                </div>
                            </div>
                        </footer>
                    </div>
                </div>
            <?php else: ?>
                <?=Yii::t('app', 'no record')?>
            <?php endif ?>
        </div>
    </div>
<?php endif ?>
</div>