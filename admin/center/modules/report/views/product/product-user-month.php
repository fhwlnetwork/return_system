<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

\center\assets\ReportAsset::echartsJs($this);
echo $this->render('/layouts/product-menu');

if(Yii::$app->session->get('user_searchProduct')) {
    $searchField = Yii::$app->session->get('user_searchProduct');
} else {
    $searchField = [];
}

$this->title = Yii::t('app', "report/product/user-month");
?>

<div class="panel panel-default">
    <div class="panel-body" style="padding: 10px">
        <?php
        $form = ActiveForm::begin([
            'layout' => 'horizontal',
            'fieldConfig' => [
                'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}"
            ],
        ]);
        ?>

        <div class="col-md-2">
            <?=
            $form->field($model, 'start_At', ['template' => '<div class="col-sm-12">{input}</div>'])
                ->textInput(
                    [
                        'value' => isset($model->start_At) ? $model->start_At : date('Y-m',strtotime('-3 month')),
                        'class' => 'form-control inputMonth',
                        'placeHolder' => Yii::t('app', 'start time')
                    ]);
            ?>
        </div>

        <div class="col-md-2">
            <?=
            $form->field($model, 'stop_At', [
                'template' => '<div class="col-sm-12">{input}</div>'
            ])->textInput(
                [
                    'value' => isset($model->stop_At) ? $model->stop_At : date('Y-m'),
                    'class' => 'form-control inputMonth',
                    'placeHolder' => Yii::t('app', 'end time')
                ]);
            ?>
        </div>


        <div class="col-md-12 form-group">
            <div class="col-md-1"><?= Yii::t('app', 'user products id select') ?>:</div>
            <div class="col-md-1">
                <input class="select" type="checkbox"><?=Yii::t('app','all select/all not select')?>
            </div>
            <div class="col-md-10">
                <?= Html::checkboxList('OnlineProductUserMonth[showField][]', isset($model->showField)?$model->showField:$showField, $showField, ['class' => 'drag_inline']) ?>
            </div>
        </div>

        <?= Html::submitButton(Yii::t('app', 'search'), ['class' => 'btn btn-success']) ?>

        <div class="col-sm-12" style="text-align: left;color: #ffffff;">
            <?= $form->errorSummary($model); ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
<div class="page">
    <section class="panel panel-default table-dynamic">
        <?php
        if(!empty($data['table'])){
            ?>
            <div style="float:right;margin-right:10px;margin-top:5px;">
                <button type="button" class="btn btn-default btn-sm">
                    <a href="user-month?excel=1"><span class="glyphicon glyphicon-log-out"></span>excel</a>
                </button>
            </div>
            <?php
        }
        ?>
        <div class="panel-heading">
            <strong>
                <span class="glyphicon glyphicon-th-large"></span> <?= $this->title; ?>
            </strong>
            <?= !empty($model->start_At)?'（':'' ?><?= $model->start_At;?><?= !empty($model->start_At)?'—':'' ?><?= $model->stop_At?><?= !empty($model->stop_At)?'）':'' ?>
        </div>


        <?if($data['table']):?>
            <table class="table table-bordered table-striped table-responsive">
                <tr>
                    <td width="15%"><?=Yii::t('app', 'date')?></td>
                    <td width="15%"><?=Yii::t('app', 'products name')?></td>
                    <td width="15%"><?=Yii::t('app', 'products id')?></td>
                    <td width="12%"><?=Yii::t('app', 'normal_amount')?></td>
                    <td width="12%"><?=Yii::t('app', 'abnormal_amount')?></td>
                </tr>
                <?foreach($data['table'] as $key => $value):?>
                    <tr>
                        <td width="15%"><?=$value['date']?></td>
                        <td width="15%"><?=$value['product_name']?></td>
                        <td width="15%"><?=$value['product_id']?></td>
                        <td width="12%"><?=$value['normal_amount']?></td>
                        <td width="12%"><?=$value['abnormal_amount']?></td>
                    </tr>
                <?endforeach;?>
            </table>
        <?else:?>
            <table class="table table-bordered table-striped table-responsive">
                <tr><td><?= Yii::t('app', 'user base help10') ?></td></tr>
            </table>
        <?endif?>

    </section>
</div>
<script>
    $('.select').on('click',function () {
        if($(this).prop('checked')){
           $(this).parent().parent().find('.col-md-10 input').prop('checked',true);
        }else{
            $(this).parent().parent().find('.col-md-10 input').prop('checked',false);
        }
    })
</script>






