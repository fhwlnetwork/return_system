<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model center\modules\product\models\Major */

$this->title = "专业详情";
$this->params['breadcrumbs'][] = ['label' => 'Majors', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="major-view">
    <?= $this->render('/layouts/nav'); ?>
    <h4><?= Html::encode($this->title) ?></h4>

    <div class="col-lg-10">
        <p>
            <?= Html::a('编辑', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('删除', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => '你确定要删除该专业吗？ ',
                    'method' => 'post',
                ],
            ]) ?>
        </p>

        <?= DetailView::widget([
            'model' => $model,
            'attributes' => [
                'id',
                'major_name',
                [
                    'attribute' => 'ctime',
                    'value' => date('Y-m-d H:i:s', $model->ctime)
                ],
            ],
        ]) ?>
    </div>
</div>
