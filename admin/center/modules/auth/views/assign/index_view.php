<?php

use yii\grid\GridView;
use center\modules\auth\models\AuthAssignment;
use yii\helpers\Html;

$canEdit = Yii::$app->user->can('auth/assign/update');
$canDel = Yii::$app->user->can('auth/assign/delete');
?>

<div class="padding-right-0px padding-left-0px">
    <div class="panel panel-default">
        <div class="panel-body">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    'username',
                    [
                        'label' => Yii::t('app', 'roles group'),
                        'value' => function($dataProvider){
                            return AuthAssignment::getItemNameData($dataProvider['id']);
                        },

                    ],
                    [
                        'label' => Yii::t('app', 'created_at'),
                        'format' => 'datetime',
                        'value' => 'created_at',
                    ],

                    [
                        'class' => 'yii\grid\ActionColumn',
                        'header' => Yii::t('app', 'operate'),
                        'headerOptions' => ['width' => '50'],
                        'template' => ($canEdit? '{update}' : '').($canDel ? ' {delete}' : '') ,
                        'buttons' => [
                            'delete' => function($url, $model, $key){
                                if ($key != Yii::$app->user->identity->id) {
                                    return Html::a('',
                                        ['delete', 'id' => $key],
                                        [
                                            'class' => 'glyphicon glyphicon-trash',
                                            'data' => ['confirm'=>Yii::t('app', 'assign help1')]
                                        ]);
                                }
                                },
                        ],
                    ],
                ]]); ?>
        </div>
    </div>
</div>

<?php



?>
