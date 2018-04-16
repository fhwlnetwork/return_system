<?php

use center\widgets\Alert;

$this->title = Yii::t('app', '开销户详情'.'-'.$model->user_name);
$labels = $model->attributeLabels();
$attributes = $model->getAttributesList();
$userLabels = $userModel->attributeLabels();
?>
<div class="page page-table">
    <?= Alert::widget() ?>
    <section class="panel panel-default table-dynamic">
        <div class="panel-heading"><strong><span class="glyphicon glyphicon-th-large"></span> <?= Yii::t('app', 'show detail') ?></strong></div>
        <div class="divider"></div>
        <table class="table table-bordered table-striped">
            <tbody>
            <tr>
                <td width="20%"><?= Yii::t('app', 'User Name') ?></td>
                <td width="80%"><?= $model->user_name ?></td>
            </tr>
            <tr>
                <td width="20%"><?= Yii::t('app', 'user_real_name') ?></td>
                <td width="80%"><?= $model->user_real_name ?></td>
            </tr>
            <tr>
                <td width="20%"><?= Yii::t('app', 'action type') ?></td>
                <td width="80%"><?= $attributes['type'][$model->type] ?></td>
            </tr>
            <tr>
                <td width="20%"><?= Yii::t('app', 'operate time') ?></td>
                <td width="80%"><?= $model->operate_time ?></td>
            </tr>
            <tr>
                <td width="20%"><?= Yii::t('app', 'operate ip') ?></td>
                <td width="80%"><?= $model->operate_ip ?></td>
            </tr>
            <tr>
                <td width="20%"><?= Yii::t('app', 'operate operator') ?></td>
                <td width="80%"><?= $model->mgr_name ?></td>
            </tr>
            <tr>
                <td width="20%"><?= Yii::t('app', 'data detail') ?></td>
                <td width="80%">
                    <?php if($model->detail):?>
                    <table class="table table-hover">
                        <tbody>
                        <?php $detail = \yii\helpers\Json::decode($model->detail)?>
                        <?php foreach($detail as $key => $value): ?>
                        <tr>
                            <td width="30%"><?= $userLabels[$key]?></td>
                            <td><?= $value?></td>
                        </tr>
                        <?php endforeach?>
                        </tbody>
                    </table>
                    <?php endif ?>
                </td>
            </tr>
            </tbody>
        </table>
    </section>
</div>
