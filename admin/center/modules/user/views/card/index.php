<?php
use yii\widgets\LinkPager;
use yii\helpers\Html;
use center\widgets\Alert;
use yii\grid\GridView;

$this->title = \Yii::t('app', 'User List');
$attributeLabels = $model->attributeLabels();
?>

<div class="page page-table">
<?= Alert::widget() ?>


<div class="panel panel-default">
            <section class="panel panel-default table-dynamic" style="marign:0px;border:0px;">
				<?php
				if(!empty($provider)){
				?>
				<div style="float:right;margin-right:10px;margin-top:5px;">
					<button type="button" class="btn btn-default btn-sm"><a href="export?action=excel"><span class="glyphicon glyphicon-log-out"></span>excel</a></button>	
				</div>
				<?php
				}
				?>			
                <div class="panel-heading"><strong><span class="glyphicon glyphicon-th-large"></span> <?=Yii::t('app', 'search result')?></strong></div>	
			</section>
<?php if (!empty($provider)): ?>    
	<?=
        GridView::widget([
            'dataProvider' => $provider,
			'layout'=> '{items}<div class="text-right tooltip-demo">{pager}&nbsp;&nbsp;</div>',
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                [
                    'attribute' => Yii::t('app', 'User Name'),
                    'value' => 'user_name',
                ],
                [
                    'attribute' => Yii::t('app', 'Charge standard'),
                    'value' => 'balance',
                ], 
                [
                    'attribute' => Yii::t('app', 'Card number'),
                    'value' => 'card_num',
                ], 				
                [
                    'attribute' => Yii::t('app', 'Card Owner'),
                    'value' => 'card_owner',
                ], 	
                [
                    'attribute' => Yii::t('app', 'Email'),
                    'value' => 'email',
                ], 							
            ],
        ]);
    ?>  
                <?php else: ?>
                    <div class="panel-body">
                        <?=Yii::t('app', 'no record')?>
                    </div>
                <?php endif ?>
</div>
</div>

