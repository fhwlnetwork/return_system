<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model center\modules\product\models\MajorWorkRelation */

$this->title = '创建工作';
$this->params['breadcrumbs'][] = ['label' => '创建工作', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="major-work-relation-create">
    <?= $this->render('/layouts/nav'); ?>
    <h4><?= Html::encode($this->title) ?></h4>
    <div class="col-lg-10">
        <?= $this->render('_form', [
            'model' => $model,
        ]) ?>
    </div>
</div>