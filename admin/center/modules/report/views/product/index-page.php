<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use center\modules\visitor\models\Setting;
use center\modules\report\models\OnlineReportPoint;

\center\assets\ReportAsset::echartsJs($this);
echo $this->render('/layouts/product-menu');

$this->title = Yii::t('app', 'report/product/index');
if (Yii::$app->session->get('selectProduct')) {
	$searchField = array_keys(Yii::$app->session->get('selectProduct'));
} else {
	$searchField = [];
}
?>

<div class="panel panel-default">
	<div class="panel-body" style="padding: 10px">
		<?php
		$form = ActiveForm::begin([
			'layout' => 'horizontal',
			'fieldConfig' => [
				'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}"
			],
		]);
		?>
		<div class="col-md-2">
			<?= $form->field($model, 'start_At', ['template' => '<div class="col-sm-12">{input}</div>'])
				->textInput(
					[
						'value' => isset($model->start_At) ? $model->start_At : date('Y-m',time()),
						'class'=>'form-control inputMonth',
						'placeHolder'=>Yii::t('app', 'start time')
					]);
			?>
		</div>

		<div class="col-md-2" style="height: 34px">
			<div class="input-group" style="border:1px solid #cbd5dd">
				<span class="input-group-addon" style="border:none;height:32px;">大于</span>
				<input type="number" id="product-checkout_amount" class="form-control" name="SrunProductDetail[bytes_limit]" value="<?=isset($model->bytes_limit)?$model->bytes_limit:0 ?>" >
				<span class="input-group-addon" style="border:none;height:32px;"><?= Yii::t('app', 'MB') ?></span>
			</div>
		</div>

		<div class="col-md-12 form-group">
			<div class="col-md-2" style="width:150px;"><?= Yii::t('app', 'user products id select') ?>.</div>
			<div class="col-md-10">
				<?= Html::checkboxList('SrunProductDetail[showField][]', $searchField, $showField, ['class' => 'drag_inline']) ?>
			</div>
		</div>
		<?= Html::submitButton(Yii::t('app', 'search'), ['class' => 'btn btn-success']) ?>
		<div class="col-sm-12" style="text-align: left;color: #ffffff;">
			<?= $form->errorSummary($model); ?>
		</div>
		<?php ActiveForm::end(); ?>
	</div>
</div>

<div class="page">
	<section class="panel panel-default table-dynamic">
		<?php
		if(!empty($source)){
			?>
			<div style="float:right;margin-right:10px;margin-top:5px;">
				<button type="button" class="btn btn-default btn-sm">
					<a href="export?action=excel"><span class="glyphicon glyphicon-log-out"></span>excel</a>
				</button>
			</div>
			<?php
		}
		?>
		<div class="panel-heading">
			<strong>
				<span class="glyphicon glyphicon-th-large"></span> <?= $this->title; ?>
			</strong>
			<?= !empty($BeginDate)?'（':'' ?><?= $BeginDate;?><?= !empty($BeginDate)?'—':'' ?><?= $EndingDate?><?= !empty($BeginDate)?'）':'' ?>

		</div>
		<?php

		// 重转数组
		if(!empty($source)){
		?>
		<table class="table table-bordered table-striped table-responsive">
			<tr>
				<td width="20%"><?= Yii::t('app', 'products name') ?></td>
				<td width="15%"><?= Yii::t('app', 'use count') ?></td>
				<td width="20%"><?= Yii::t('app', 'total bytes') ?></td>
				<td width="15%"><?= Yii::t('app', 'time count') ?></td>
				<td width="20%"><?= Yii::t('app', 'time long') ?></td>
				<td width="10%"><?= Yii::t('app', 'action') ?></td>
			</tr>

			<?php
			if($source){
				foreach($source as $key=>$value){
					?>
					<tr>
						<td width=""><?= $value['products_name'];?></td>
						<td width=""><?= $value['usercount'];?></td>
						<td width=""><?= $value['total_bytes'];?></td>
						<td width=""><?= $value['user_login_count'];?></td>
						<td width=""><?= $value['time_long'];?></td>
						<td width="">
							<a href="detail?action=excel&pid=<?php echo $value['products_id'];?>"><?= Html::button(Yii::t('app', 'download'), ['class' => 'btn btn-warning btn-xs']); ?></a>
						</td>
					</tr>
					<?php
				}
			}
			?>

			<?php
			}else{
				?>
				<table class="table table-bordered table-striped table-responsive">
					<tr><td><?= Yii::t('app', 'user base help10') ?></td></tr>
				</table>

				<?php
			}
			?>
	</section>
</div>
	