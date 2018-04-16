<?php
/**
 * @var yii\web\View $this
 * @var $list Array 列表
 * @var $model center\modules\user\models\Detail
 */

use yii\widgets\LinkPager;
use yii\helpers\Html;
use center\widgets\Alert;

$this->title = \Yii::t('app', 'Product Change Log');
$attributes = $model->getAttributesList();
?>

<div class="page page-table">
    <?= Alert::widget() ?>

    <div class="panel" style="padding: 10px 15px 0 15px">
        <form name="detailSearch" action="<?=\yii\helpers\Url::to(['index'])?>" class="form-horizontal form-validation" method="get">
            <div class="form-group">
                <div class="col-sm-3">
                    <div class="input-group col-sm-12">
                        <input type="text" class="form-control" name="user_name" value="<?= isset($params['user_name'])?$params['user_name']:''?>" placeholder="<?= Yii::t('app', 'account')?>" />
                    </div>
                </div>
                <div class="col-sm-2">
                    <?= Html::submitButton(Yii::t('app', 'search'), ['class' => 'btn btn-primary']) ?>
                </div>
            </div>
        </form>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading"><strong><span class="glyphicon glyphicon-th-large"></span> <?=Yii::t('app', 'list')?></strong></div>

        <?php if(!empty($list)) : ?>
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>#</th>
                    <th><?=Yii::t('app', 'account')?></th>
                    <th><?=Yii::t('app', 'products id from')?></th>
                    <th><?=Yii::t('app', 'products id to')?></th>
                    <th><?=Yii::t('app', 'change status')?></th>
                    <th><?=Yii::t('app', 'change date')?></th>
                    <th><?=Yii::t('app', 'operate time')?></th>
                    <th><?=Yii::t('app', 'operate')?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach($list as  $one){ ?>
                    <tr>
                        <td><?= $one['change_id']?></td>
                        <td><?= Html::encode($one['user_name'])?></td>
                        <td><?= Html::encode($one['products_id_from'])?></td>
                        <td><?= Html::encode($one['products_id_to'])?></td>
                        <td><?= Html::encode($attributes['change_status'][$one['change_status']])?></td>
                        <td><?php if($one['change_date']>0){
                                echo Html::encode(date('Y-m-d', $one['change_date']));
                            }else{
                                echo Yii::t('app', 'effect next cycle');
                            }?></td>
                        <td><?= Html::encode(date('Y-m-d H:i:s', $one['operating_date']))?></td>
                        <td>
                            <?php if($one['change_status'] == 0){
                                echo Html::a(Html::button(Yii::t('app', 'delete'), ['class' => 'btn btn-danger btn-xs']),
                                    ['delete', 'id' => $one['change_id']], [
                                        'title' => Yii::t('app', 'delete'),
                                        'data' => [
                                            'confirm' => Yii::t('app', 'change product msg2'),
                                        ],
                                    ]);
                            }?>
                        </td>
                    </tr>
                <?php }?>
                </tbody>
            </table>

            <footer class="table-footer">
                <div class="row">
                    <div class="col-md-6">
                        <?=Yii::t('app', 'pagination show1', [
                            'totalCount' => $pagination->totalCount,
                            'totalPage' => $pagination->getPageCount(),
                            'perPage' => $pagination->defaultPageSize,
                        ])?>
                    </div>
                    <div class="col-md-6 text-right">
                        <?= LinkPager::widget(['pagination' => $pagination, 'maxButtonCount'=>5]); ?>
                    </div>
                </div>
            </footer>

        <?php else: ?>

            <div class="panel-body">
                <?=Yii::t('app', 'no record')?>
            </div>

        <?php endif; ?>
    </div>
</div>
