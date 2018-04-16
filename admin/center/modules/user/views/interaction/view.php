<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model center\modules\user\models\UserCloundComplaints */

$this->params['breadcrumbs'][] = ['label' => 'User Clound Complaints', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="user-clound-complaints-update">
    <?= $this->render('_form', [
        'model' => $model,
        'questionTypes'=> $questionTypes,
        'questionStates'=>$questionStates,
        'action' => $action
    ]) ?>

</div>
