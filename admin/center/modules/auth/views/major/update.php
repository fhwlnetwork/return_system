<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model center\modules\product\models\Major */

$this->title = '编辑专业';
$this->params['breadcrumbs'][] = ['label' => 'Majors', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="major-update">
    <?= $this->render('/layouts/nav')?>
    <h4><?= Html::encode($this->title) ?></h4>
    <div class="col-lg-10 col-md-10">
        <?= $this->render('_form', [
            'model' => $model,
        ]) ?>
    </div>
</div>
