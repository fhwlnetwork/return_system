<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use center\widgets\Alert;

/**
 * @var yii\web\View $this
 * @var $userArray
 */
$this->title = \Yii::t('app', 'Financial Statistics');
$canTypes = Yii::$app->user->can('report/financial/_list_type');
echo $this->render('/layouts/financial-menu');

?>
<div class="page page-table">
    <?= Alert::widget() ?>
		<div class="panel panel-default">

			<div class="panel-body">
				<ul class="nav nav-tabs" role="tablist" id="myTab">
                    <li role="presentation"
                        <?php if ($model->type == 'methods'): ?>class="active"<?php endif ?>>
                        <?= Html::a(Yii::t('app', 'statistics by pay methods'), ['list', 'type' => 'methods']) ?>
                    </li>
                    <?php if ($canTypes): ?>
                    <li role="presentation" <?php if ($model->type == 'type'): ?>class="active"<?php endif ?>>
                        <?= Html::a(Yii::t('app', 'statistics by pay type'), ['list', 'type' => 'type']) ?>
                        </li><?php endif ?>
				</ul>

				<div class="tab-content">
					<div role="tabpanel" class="tab-pane active">
						<?=
						$this->render('by_methods', [
							'model' => $model,
                            'pagination' => $pagination,
                            'list' => $list,
                            'pay_methods' => $pay_methods,
                            'pay_types' => $pay_types,
                            'params' => $params,
                            'totalMoney' => $totalMoney,
                            'refundMoney'=>$refundMoney,
                            'payNum' => $payNum,
                            'mgrs' => $mgrs,
                            'refund_list' => $refund_list,
						]) ?>
					</div>

				</div>
			</div>
		</div>
</div>