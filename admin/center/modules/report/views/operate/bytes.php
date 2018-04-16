<?php
use yii\helpers\Html;
use center\extend\Tool;
use yii\bootstrap\ActiveForm;
use center\modules\report\models\SrunDetailDay;
use center\assets\ReportAsset;

ReportAsset::newEchartsJs($this);
echo $this->render('/layouts/operate-menu');

$this->title = Yii::t('app', 'report/operate/bytes');

if (Yii::$app->session->get('time_usergroup')) {
    $searchField = array_keys(Yii::$app->session->get('time_usergroup'));
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
                    'placeHolder' => Yii::t('app', 'user_name')
                ]);
            ?>
        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'start_At', [
                'template' => '<div class="col-sm-12">{input}</div>'
            ])->textInput(
                [
                    'value' => isset($model->start_At) ? $model->start_At : date('Y-m-01'),
                    'class' => 'form-control inputDate',
                    'placeHolder' => Yii::t('app', 'start time')
                ]);
            ?>
        </div>

        <div class="col-md-2">
            <?= $form->field($model, 'stop_At', [
                'template' => '<div class="col-sm-12">{input}</div>'
            ])->textInput(
                [
                    'value' => isset($model->stop_At) ? $model->stop_At : date('Y-m-d'),
                    'class' => 'form-control inputDate',
                    'placeHolder' => Yii::t('app', 'end time')
                ]);
            ?>
        </div>


        <?= Html::submitButton(Yii::t('app', 'search'), ['class' => 'btn btn-success']) ?>
        <?= Html::submitButton(Yii::t('app', 'this month'), ['class' => 'btn btn-primary', 'name' => 'SrunDetailDay[btn_chooses]', 'value' => 1]) ?>
        <?= Html::submitButton(Yii::t('app', 'last month'), ['class' => 'btn btn-primary', 'name' => 'SrunDetailDay[btn_chooses]', 'value' => 2]) ?>
        <?= Html::submitButton(Yii::t('app', 'this quarter'), ['class' => 'btn btn-success', 'name' => 'SrunDetailDay[btn_chooses]', 'value' => 3]) ?>
        <?= Html::submitButton(Yii::t('app', 'last quarter'), ['class' => 'btn btn-danger', 'name' => 'SrunDetailDay[btn_chooses]', 'value' => 4]) ?>
        <div class="col-sm-12" style="text-align: left;color: #ffffff;">
            <?= $form->errorSummary($model); ?>
        </div>
        <?php $form->end(); ?>
    </div>
</div>


<?php ?>

<div class="row" style="border:none;margin: 10px auto;padding:0;margin-bottom:50px;overflow-x: auto;">
    <section class="panel panel-default table-dynamic" style="margin:0;padding:0;">
        <div class="panel-heading"><strong><span
                    class="glyphicon glyphicon-th-large"></span> <?= Yii::t('app', 'search result') ?></strong>

            <div class="pull-right" style="margin-top:-5px;">
                <a type="button" class="btn btn-primary btn-sm"
                   href="<?= Yii::$app->urlManager->createUrl(array_merge(['report/operate/bytes'], ['export' => 'excel'])) ?>"><span
                        class="glyphicon glyphicon-log-out"></span><?= Yii::t('app', 'excel export') ?></a>
            </div>
        </div>
        <div style="clear:both;"></div>

        <?php if (!empty($source['table'])) : ?>
        <div class="row" style="margin:0 auto;padding:0;width:95%;">
            <?php if ($model->flag == 2): ?>
                <?= $this->render('/map/user-bytes-detail', [
                    'data' => $source['data'],
                    'model' => $model,
                    'name' => Yii::t('app', 'report/operate/bytes')
                ]) ?>
            <?php else : ?>
                <?= $this->render('/map/bytes-detail', [
                    'data' => $source['data'],
                    'model' => $model,
                    'name' => $model->flag ? Yii::t('app', 'report msg1', [
                        'today' => $model->start_At
                    ]) : Yii::t('app', 'report/operate/bytes')
                ]) ?>
            <?php endif; ?>
        </div>
        <table class="table table-bordered table-striped table-responsive table-hover" style="margin:0;padding:0;">
            <thead>
            <tr style="height: 30px; line-height: 30px;align-content: center;">

                <th nowrap="nowrap">
                    <div class="th"><?= Yii::t('app', 'date') ?></div>
                </th>
                <?php if ($model->flag == 2): ?>
                    <th nowrap="nowrap"><div class="th"><?= Yii::t('app', 'username') ?></div></th>
                    <th nowrap="nowrap"><div class="th"><?= Yii::t('app', 'flux') ?></div></th>
                    <th nowrap="nowrap"><div class="th"><?= Yii::t('app', 'time_long') ?></div></th>
                <?php else : ?>
                    <?php if (!$model->flag): ?>
                        <th nowrap="nowrap"><div class="th"><?= Yii::t('app', 'sum_bytes') ?></div></th>
                        <th nowrap="nowrap"><div class="th"><?= Yii::t('app', 'max_bytes') ?></div></th>
                        <th nowrap="nowrap"><div class="th"><?= Yii::t('app', 'aver_bytes') ?></div></th>
                        <th nowrap="nowrap"><div class="th"><?= Yii::t('app', 'user_number') ?></div></th>
                    <?php else: ?>
                        <th nowrap="nowrap"><div class="th"><?= Yii::t('app', 'username') ?></div></th>
                        <th nowrap="nowrap"><div class="th"><?= Yii::t('app', 'flux') ?></div></th>
                    <?php endif; ?>
                <?php endif; ?>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($source['table'] as $time => $v): ?>
                <tr>
                    <?php if ($model->flag == 2): ?>
                        <td><?= date('Y-m-d', $time) ?></td>
                        <td><?= $v['user_name'] ?></td>
                        <td><?= Tool::bytes_format($v['total_bytes']) ?></td>
                        <td><?= Tool::seconds_format($v['time_long']) ?></td>
                    <?php else : ?>
                        <td><?= ($model->flag) ? $model->start_At : date('Y-m-d', $time) ?></td>
                        <?php if (!$model->flag): ?>
                            <td><?= Tool::bytes_format($v['total']) ?></td>
                            <td><?= Tool::bytes_format($v['max_bytes']) ?></td>
                            <td><?= Tool::bytes_format($v['total'] / $v['user_number']); ?></td>
                            <td><?= $v['user_number'] ?></td>
                        <?php else: ?>
                            <td><?= $time ?></td>
                            <td><?= Tool::bytes_format($v['total']) ?></td>
                        <?php endif; ?>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>

            </tbody>

            <tbody>
            <?php else: ?>
                <div class="panel-body">
                    <?= Yii::t('app', 'no record') ?>
                </div>
            <?php endif ?>
    </section>

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