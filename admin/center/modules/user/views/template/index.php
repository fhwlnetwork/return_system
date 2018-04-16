<?php
use yii\widgets\LinkPager;
use yii\helpers\Html;
use center\widgets\Alert;
use yii\helpers\ArrayHelper;
use common\models\User;

/**
 * @var yii\web\View $this
 * @var $TemplateArray
 */
$this->title = \Yii::t('app', 'user/template/index');
?>
<div class="page">
    <?= Alert::widget() ?>

    <section class="panel panel-default table-dynamic">
        <div class="panel-heading">
            <strong><span class="glyphicon glyphicon-list-alt text-small"></span> <?= Yii::t('app', 'search result') ?>
            </strong>
            <div class="pull-right" style="margin-top:-5px;">
                <a type="button" class="btn btn-primary btn-sm" href="<?= Yii::$app->urlManager->createUrl(['user/template/add-temp']) ?>"><span class="glyphicon glyphicon-log-out"></span><?= Yii::t('app', 'setting/template/create') ?></a>
            </div>
        </div>
        <?php if (!empty($list)): ?>
            <table class="table table-bordered table-striped table-responsive" style="border-top:0;">
                <thead>
                <tr>
                    <th>
                        <div class='th'><?=Yii::t('app','action_id')?></div>
                    </th>
                    <th>
                        <div class='th'><?=Yii::t('app','mgr name create')?></div>
                    </th>
                    <th>
                        <div class='th'><?=Yii::t('app','action_name')?></div>
                    </th>
                </tr>
                </thead>
                <tbody>
                    <?php foreach ($list as $id => $one) { ?>
                        <tr>
                            <td><?=Html::encode($one['id'])?></td>
                            <td><?=Html::encode($one['create'])?></td>
                            <td><?=Html::encode($one['name'])?></td>
                            <td><?=Html::a(Html::button(Yii::t('app', 'edit'), ['class'=>'btn btn-warning btn-xs']), ['edit', 'id'=>$one['id']], ['title'=>Yii::t('app', 'edit')])?>
                        <?=Html::a(Html::button(Yii::t('app', 'interfaces/binding/delete'), ['class'=>'btn btn-danger btn-xs']), ['delete', 'id'=>$one['id']], ['title'=>Yii::t('app', 'User Delete')])?></td> 
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
    </section></form>
</div>
