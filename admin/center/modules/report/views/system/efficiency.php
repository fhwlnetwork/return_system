<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;


\center\assets\ReportAsset::newEchartsJs($this);

$this->title = Yii::t('app', 'report/system/efficiency');
echo $this->render('/layouts/system-menu');
?>

<style>
    .system-basic ol li {
        height: 35px;
        line-height: 35px;
        border-bottom: 1px solid #eeeeee;
        padding-left: 10px;
    }

    .panel {
        margin-bottom: 10px;
        background-color: white;
        border: 1px solid transparent;
        border-radius: 2px;
        -webkit-box-shadow: 0 1px 1px rgba(0, 0, 0, 0.05);
        box-shadow: 0 1px 1px rgba(0, 0, 0, 0.05);
    }
</style>
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
            <?= $form->field($model, 'process_default', [
                'template' => '<div class="col-sm-12">{input}</div>'
            ])->dropDownList($model->process,
                [
                    'class' => 'form-control',
                    'placeHolder' => Yii::t('app', 'Select proc')
                ]);
            ?>
        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'my_ip', [
                'template' => '<div class="col-sm-12">{input}</div>'
            ])->textInput(
                [
                    'class' => 'form-control',
                    'placeHolder' => Yii::t('app', 'my ip')
                ]);
            ?>
        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'start_time', [
                'template' => '<div class="col-sm-12">{input}</div>'
            ])->textInput(
                [
                    'class' => 'form-control inputDate',
                    'placeHolder' => Yii::t('app', 'start time')
                ]);
            ?>
        </div>

        <div class="col-md-2">
            <?= $form->field($model, 'stop_time', [
                'template' => '<div class="col-sm-12">{input}</div>'
            ])->textInput(
                [
                    'class' => 'form-control inputDate',
                    'placeHolder' => Yii::t('app', 'end time')
                ]);
            ?>
        </div>

        <!-- 选择用户组 end -->

        <?= Html::submitButton(Yii::t('app', 'search'), ['class' => 'btn btn-success']) ?>
        <?= Html::submitButton(Yii::t('app', 'this month'), ['class' => 'btn btn-primary', 'name' => 'EfficiencyBase[timePoint]', 'value' => 1]) ?>
        <?= Html::submitButton(Yii::t('app', 'last month'), ['class' => 'btn btn-primary', 'name' => 'EfficiencyBase[timePoint]', 'value' => 2]) ?>
        <?= Html::submitButton(Yii::t('app', 'this quarter'), ['class' => 'btn btn-success', 'name' => 'EfficiencyBase[timePoint]', 'value' => 3]) ?>
        <?= Html::submitButton(Yii::t('app', 'last quarter'), ['class' => 'btn btn-danger', 'name' => 'EfficiencyBase[timePoint]', 'value' => 4]) ?>
        <div class="col-sm-12" style="text-align: left;color: #ffffff;">
            <?= $form->errorSummary($model); ?>
        </div>
        <?php $form->end(); ?>
    </div>
</div>
<div class="page row" style="margin:0;padding:0;">
    <?= \center\widgets\Alert::widget(); ?>
    <div class="col-md-12 col-sm-12 col-lg-12">
        <?php if ($data['code'] == 200): ?>
            <?php if ($data['single']): ?>
                <?= $this->render('/map/system-single', [
                    'data' => $data,
                    'unit' => 'ms',
                ]) ?>
            <?php else : ?>
                <?= $this->render('/map/efficiency-multi', [
                    'data' => $data
                ]) ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
<div class="page row" style="margin:0;padding:0;">
    <section class="panel panel-default table-dynamic">
        <div class="panel-heading data-center"><strong><span
                    class="glyphicon glyphicon-th-large"></span> <?= $this->title ?>(<?=
                $model->start_time . '--' . $model->stop_time;
                ?>)
            </strong>
        </div>
        <?php if ($data['code'] == 200) : ?>
        <?php if ($data['single']) : ?>
        <table class="table table-bordered table-striped table-responsive">
            <?php else: ?>
            <table class="table table-bordered  table-responsive">
                <?php endif; ?>
                <thead>
                <tr>
                    <?php if ($data['single']) : ?>
                        <?php foreach ($data['table']['header'] as $v) : ?>
                            <th>
                                <div class='th'><?= $v; ?></div>
                            </th>
                        <?php endforeach ?>
                    <?php else: ?>
                        <th>
                            <div class='th'><?= Yii::t('app', 'action'); ?></div>
                        </th>
                        <?php foreach ($data['table']['top_header'] as $v) : ?>
                            <th>
                                <div class='th'><?= $v; ?></div>
                            </th>
                        <?php endforeach ?>
                    <?php endif; ?>
                </tr>
                </thead>
                <tbody>
                <?php if ($data['single']) : ?>
                    <?php if (!empty($data['table']['data'])): foreach ($data['table']['data'] as $one) : ?>
                        <tr>
                            <?php foreach ($one as $v) : ?>
                                <td><?= $v; ?></td>
                            <?php endforeach ?>
                        </tr>
                    <?php endforeach;endif; ?>
                <?php else: ?>
                <?php $i = 0;
                if (!empty($data['table']['data'])):foreach ($data['table']['data'] as $date => $one) : ?>
                <tr bgcolor="<?php echo $i % 2 == 1 ? "#fff" : '#f1f1f1' ?>">
                    <td><span id="product_key_<?= $i ?>" onclick="chgBreak('<?= $i ?>')"
                              data-ng-click='product_key_<?= $i ?> = !product_key_<?= $i ?>'
                              class="glyphicon glyphicon-plus" style="cursor: pointer"></span>
                    </td>
                    <?php foreach ($one['data'] as $v) : ?>
                        <td><?= $v; ?></td>
                    <?php endforeach ?>
                </tr>
                <tr data-ng-show="product_key_<?= $i ?>">
                    <td colspan="7">
                        <table class="table table-bordered table-striped table-responsive">
                            <tr>
                                <?php foreach ($data['table']['detail_header'] as $v) : ?>
                                    <th>
                                        <div class='th'><?= $v; ?></div>
                                    </th>
                                <?php endforeach ?>
                            </tr>
                            <?php foreach ($one['detail'] as $vv) : ?>
                                <tr>
                                    <?php foreach ($vv as $vvv) : ?>
                                        <td><?= $vvv; ?></td>
                                    <?php endforeach ?>
                                </tr>
                            <?php endforeach ?>
                        </table>
                        <?php $i++;
                        endforeach;endif; ?>
                        <?php endif; ?>

                </tbody>
            </table>
    </section>
    <?php endif; ?>
</div>

<script>
    function chgBreak(id) {
        var obj = $('#product_key_' + id);
        var className = obj.attr('class');
        if (className.indexOf('plus') != -1) {
            obj.attr('class', 'glyphicon glyphicon-minus')
        } else {
            obj.attr('class', 'glyphicon glyphicon-plus')
        }

    }
</script>
