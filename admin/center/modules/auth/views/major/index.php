<?php

use yii\helpers\Html;
use yii\grid\GridView;
use \yii\bootstrap\Modal;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel center\modules\product\models\MajorSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '专业列表';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="major-index">
    <?= $this->render('/layouts/nav'); ?>
    <h4><?= Html::encode($this->title) ?></h4>

    <div class="col-lg-10">
        <?php //echo $this->render('_search', ['model' => $searchModel]); ?>
        <p>
            <button type="button" class="btn btn-primary" id="stage1_submit">创建</button>
        </p>
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                'major_name',
                [
                    'attribute' => 'ctime',
                    'value' => function ($model) {
                        return date('Y-m-d H:i:s', $model->ctime);
                    },
                ],

                ['class' => 'yii\grid\ActionColumn'],
            ],
        ]); ?>
    </div>
</div>
<div class="modal fade col-sm-offset-2" id="myModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <button type="button" class="btn btn-primary">添加专业</button>
                <button type="button" class="btn btn-info pull-right" data-dismiss="modal">关闭</button>
            </div>
            <div class="modal-body" id="stage1_content" style="height: 200px;">
            </div>
        </div>
    </div>
</div>
<p></p>

<?php
$requestUrl = Url::toRoute('create');
$js = <<<JS
 $('#stage1_submit').click(function () {
        $.get('create', {},
            function (data) {
                $('.modal-body').html(data);
            }  
        );

                 
     $('#myModal').modal({backdrop: 'static', keyboard: false});
 });
JS;
$this->registerJs($js);
?>
<script>

</script>