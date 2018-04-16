<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel center\modules\product\models\MajorWorkRelationSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '专业-工作对应关系';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="major-work-relation-index">
    <?= $this->render('/layouts/nav'); ?>
    <h4><?= Html::encode($this->title) ?></h4>

    <div class="col-lg-10">
        <?php //echo $this->render('_search', ['model' => $searchModel]); ?>
        <p>
            <?= Html::a('创建专业-工作对应关系', ['create'], ['class' => 'btn btn-success']) ?>
        </p>
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],

                'id',
                'work_name',
                'major_name',
                'major_id',
                [
                    'attribute' => 'ctime',
                    'value' => function($model) {
                        return date('Y-m-d H:i:s', $model->ctime);
                    }
                ],

                ['class' => 'yii\grid\ActionColumn'],
        ],
        ]); ?>
    </div>
</div>
