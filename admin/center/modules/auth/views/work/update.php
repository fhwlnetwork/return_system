<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model center\modules\product\models\MajorWorkRelation */

$this->title = "编辑专业-工作对应关系";
$this->params['breadcrumbs'][] = ['label' => 'Major Work Relations', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="major-work-relation-update">
    <?= $this->render('/layouts/nav') ?>
    <h4><?= Html::encode($this->title) ?></h4>
    <div class="col-md-10 col-lg-10">
        <?= $this->render('_form', [
            'model' => $model,
        ]) ?>
    </div>

</div>