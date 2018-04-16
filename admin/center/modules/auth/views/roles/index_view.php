<?php
use yii\grid\GridView;

$canEdit = Yii::$app->user->can('auth/roles/update');
$canDel = Yii::$app->user->can('auth/roles/delete');
?>

<div class="panel panel-default">
    <div class="panel-body">
        <?=
        GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => [
                [
                    'class' => 'yii\grid\SerialColumn',
                    'options' => ['width' => '40'],
                ],

                'name',
                'description',
                [
                    'label' => Yii::t('app', 'updated_at'),
                    'format' => 'datetime',
                    'value' => 'updated_at',
                ],

                [
                    'class' => 'yii\grid\ActionColumn',
                    'header' => Yii::t('app', 'operate'),
                    'headerOptions' => ['width' => '50'],
                    'template' => ($canEdit? '{update}' : '').($canDel? ' {delete}' : '') ,
                ],
            ],
        ]);
        ?>
    </div>
</div>

