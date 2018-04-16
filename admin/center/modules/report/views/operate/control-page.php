<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2017/5/24
 * Time: 13:49
 */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use center\assets\ReportAsset;
use center\assets\ZTreeAsset;

ReportAsset::newEchartsJs($this);


echo $this->render('/layouts/operate-menu');

$this->title = Yii::t('app', 'report/operate/index');

if (Yii::$app->session->get('searchStrategy')) {
    $searchField = array_keys(Yii::$app->session->get('searchStrategy'));
} else {
    $searchField = [];
}
?>
<div class="panel panel-default">
    <div class="panel-body" style="padding: 10px">

        <?php
        $form = ActiveForm::begin([
            'layout' => 'horizontal',
            'fieldConfig' => [
                'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}",
                'horizontalCssClasses' => [],
            ],
        ]);
        ?>
        <div class="col-md-2">
            <?= $form->field($model, 'user_name', [
                'template' => '<div class="col-sm-12">{input}</div>'
            ])->textInput(
                [
                    'class' => 'form-control',
                    'placeHolder' => Yii::t('app', 'username')
                ]);
            ?>
        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'start_At', [
                'template' => '<div class="col-sm-12">{input}</div>'
            ])->textInput(
                [
                    'value' => isset($model->start_At) ? $model->start_At : date('Y-m-01'),
                    'class' => 'form-control inputMonth',
                    'placeHolder' => Yii::t('app', 'start time')
                ]);
            ?>
        </div>

        <div class="col-md-2">
            <?=
            $form->field($model, 'sql_type', [
                'template' => '<div class="col-sm-12">{input}</div>'
            ])->dropDownList($attributes);
            ?>
        </div>
        <div class="col-md-12 form-group" ng-cloak ng-show="advanced==1">
            <div class="col-md-2"><?= Yii::t('app', 'report online bilingfont2') ?>.</div>
            <div class="col-md-10">
                <?= Html::checkboxList('showField[]', $searchField, $showField, ['class' => 'drag_inline']) ?>
            </div>
        </div>

        <label class="text-info"><input type="checkbox" ng-model="advanced"/>
            <small><?= Yii::t('app', 'advanced') ?></small>
        </label>
        <?= Html::submitButton(Yii::t('app', 'search'), ['class' => 'btn btn-success']) ?>
        <?= Html::submitButton(Yii::t('app', 'this month'), ['class' => 'btn btn-primary', 'name' => 'DetailDay[btn_chooses]', 'value' => 1]) ?>
        <?= Html::submitButton(Yii::t('app', 'last month'), ['class' => 'btn btn-primary', 'name' => 'DetailDay[btn_chooses]', 'value' => 2]) ?>
        <?= Html::submitButton(Yii::t('app', 'this quarter'), ['class' => 'btn btn-success', 'name' => 'DetailDay[btn_chooses]', 'value' => 3]) ?>
        <?= Html::submitButton(Yii::t('app', 'last quarter'), ['class' => 'btn btn-danger', 'name' => 'DetailDay[btn_chooses]', 'value' => 4]) ?>
        <div class="col-sm-12" style="text-align: left;color: #ffffff;">
            <?= $form->errorSummary($model); ?>
        </div>
        <?php $form->end(); ?>
    </div>
</div>
<?php if ($model->flag == 1): ?>
    <div class="panel panel-default">
        <?= $this->render('/map/control-map', [
            'data' => $source,
            'model' => $model,
            'title' => $title.':'.$type,
            'name' =>$name
        ]) ?>
    </div>
<?php elseif ($model->flag == 2): ?>
    <div class="panel panel-default">
        <?= $this->render('/map/control-map-multi', [
            'data' => $source,
            'model' => $model,
            'title' => $title.':'.$type,
            'name' =>$name
        ]) ?>
    </div>
<?php endif;?>


<div class="page">
    <section class="panel panel-default table-dynamic">
        <?php
        if(!empty($source)){
            ?>
            <div style="float:right;margin-right:10px;margin-top:5px;">
                <button type="button" class="btn btn-default btn-sm">
                    <a href="export?action=excel"><span class="glyphicon glyphicon-log-out"></span>excel</a>
                </button>
            </div>
            <?php
        }
        ?>
        <div class="panel-heading">
            <strong>
                <span class="glyphicon glyphicon-th-large"></span> <?= $this->title; ?>
            </strong>
            <?= !empty($BeginDate)?'（':'' ?><?= $BeginDate;?><?= !empty($BeginDate)?'—':'' ?><?= $EndingDate?><?= !empty($BeginDate)?'）':'' ?>

        </div>
        <?php

        // 重转数组
        if(!empty($source['table'])){
        ?>
        <table class="table table-bordered table-striped table-responsive">
            <tr>
                <td width="20%"><?= $name ?></td>
                <td width="15%"><?= Yii::t('app', 'use count') ?></td>
                <td width="20%"><?= Yii::t('app', 'total bytes') ?></td>
                <td width="15%"><?= Yii::t('app', 'time count') ?></td>
                <td width="20%"><?= Yii::t('app', 'time long') ?></td>
                <td width="10%"><?= Yii::t('app', 'action') ?></td>
            </tr>

            <?php
            if($source){
                foreach($source as $key=>$value){
                    ?>
                    <tr>
                        <td width=""><?= $value['products_name'];?></td>
                        <td width=""><?= $value['usercount'];?></td>
                        <td width=""><?= $value['total_bytes'];?></td>
                        <td width=""><?= $value['user_login_count'];?></td>
                        <td width=""><?= $value['time_long'];?></td>
                        <td width="">
                            <a href="detail?action=excel&pid=<?php echo $value['products_id'];?>"><?= Html::button(Yii::t('app', 'download'), ['class' => 'btn btn-warning btn-xs']); ?></a>
                        </td>
                    </tr>
                    <?php
                }
            }
            ?>

            <?php
            }else{
                ?>
                <table class="table table-bordered table-striped table-responsive">
                    <tr><td><?= Yii::t('app', 'user base help10') ?></td></tr>
                </table>

                <?php
            }
            ?>
    </section>
</div>
