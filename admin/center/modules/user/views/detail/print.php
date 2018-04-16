<?php
/**
 * Created by PhpStorm.
 * User: DM
 * Date: 2017/5/26
 * Time: 13:10
 */
use center\widgets\Alert;

$this->title = Yii::t('app', '开户详情'.'-'.$model->user_name);
$labels = $model->attributeLabels();
$attributes = $model->getAttributesList();
$userLabels = $userModel->attributeLabels();

// 头部加载 jQuery
$this->registerJsFile("/js/lib/jquery-2.1.1.js",['position' => \yii\web\View::POS_HEAD]);
?>
<style media="print" type="text/css">
    *{visibility: hidden;}
    .yes_print{visibility: visible;}
    .no_print{visibility: hidden;}
</style>
<div class="page page-table yes_print">
    <?= Alert::widget() ?>
    <section class="panel panel-default table-dynamic">
        <div class="panel-heading"><strong><span class="glyphicon glyphicon-th-large"></span> <?= Yii::t('app', 'show detail') ?></strong></div>
        <div class="divider"></div>
        <table class="table table-bordered table-striped">
            <tbody>
            <tr>
                <td><?= Yii::t('app', 'User Name') ?></td>
                <td><?= $model->user_name ?></td>
            </tr>
            <tr>
                <td><?= Yii::t('app', 'user_real_name') ?></td>
                <td><?= $model->user_real_name ?></td>
            </tr>
            <tr>
                <td><?= Yii::t('app', 'action type') ?></td>
                <td><?= $attributes['type'][$model->type] ?></td>
            </tr>
            <tr>
                <td><?= Yii::t('app', 'operate time') ?></td>
                <td><?= $model->operate_time ?></td>
            </tr>
            <tr>
                <td><?= Yii::t('app', 'operate ip') ?></td>
                <td><?= $model->operate_ip ?></td>
            </tr>
            <tr>
                <td><?= Yii::t('app', 'operate operator') ?></td>
                <td><?= $model->mgr_name ?></td>
            </tr>
            <tr>
                <td><?= Yii::t('app', 'data detail') ?></td>
                <td>
                    <?php if($model->detail):?>
                        <table class="table table-hover">
                            <tbody>
                            <?php foreach($user_detail as $key => $value): ?>
                                <tr>
                                    <td><code><?= $userLabels[$key]?></code> <?= $value?></td>
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
    <button class="btn btn-primary no_print" onclick="window.print()"><?=Yii::t('app','print')?></button>
    <button class="btn btn-success no_print" onclick="window.history.back()"><?=Yii::t('app','goBack')?></button>
</div>

<script>
    $(".yes_print").find("*").each(function () {
        $(this).addClass('yes_print');
    });
    $(":not(.yes_print)").each(function () {
        $(this).addClass('no_print');
    })
</script>