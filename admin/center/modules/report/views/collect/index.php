<style>
.collect_report_title{font-size:16px;font-weight:bold;color:#00A2CA;}
</style>
		<script type="text/javascript" src="http://html2canvas.hertzen.com/build/html2canvas.js"></script>
		<script type="text/javascript" src="http://www.boolaw.com/tpl/default/js/jquery-1.8.3.min.js"></script>
<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use center\modules\visitor\models\Setting;
use center\modules\report\models\OnlineReportPoint;

$this->title = Yii::t('app', 'report/online/index');

if(Yii::$app->session->get('searchTable')) {
    $searchField = array_values(Yii::$app->session->get('searchTable'));
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

				<div class="col-md-12">
					<div class="col-md-10">
						<?= Html::checkboxList('ShowTable[]', $searchField, $SelectArray, ['class' => 'drag_inline']) ?>
					</div>
				</div>			
			<div style="height:20px;clear:both;"></div>
			<div class="col-sm-12">
				<div class="col-sm-10"> 
				 <?=html::submitButton(yii::t('app','this day'),['class'=>'btn btn-warning','name'=>'timePoint','value'=>'Today'])?>&nbsp;
				 <?=html::submitButton(yii::t('app','this Yesterday'),['class'=>'btn btn-info','name'=>'timePoint','value'=>'Yesterday'])?>&nbsp;
				 <?=html::submitButton(yii::t('app','this week'),['class'=>'btn btn-primary','name'=>'timePoint','value'=>'week'])?>&nbsp;
				 <?=html::submitButton(yii::t('app','last week'),['class'=>'btn btn-primary','name'=>'timePoint','value'=>'lastweek'])?>
				</div>
			</div>
			<!--
            <div class="col-sm-12" style="text-align:right;">
                <button type="button" class="btn btn-default btn-sm" onclick = "chart()">
                    <span class="glyphicon glyphicon-save"></span> <?= Yii::t('app', 'Download') ?>
                </button>&nbsp;&nbsp;
                <button type="button" class="btn btn-default btn-sm">
                    <span class="glyphicon glyphicon-send"></span> <?= Yii::t('app', 'Sending') ?>
                </button>				
            </div>
			-->
        <?php ActiveForm::end(); ?>
    </div>
</div>

<div style="width:90%;padding:0px 15px;margin:0 auto;">
    <div class="row" id="chart">
			<!---用户在线统计 start--->
			<?php
			if(!empty($onlineData)){
			?>			
			<?= $this->render('user_online',['onlineData'=>$onlineData])?>
			<?php
			}
			?>			
			<!---用户在线统计 end--->			
			<!---认证错误 start--->
			<?php
			if(!empty($errorData)){
			?>
			<?= $this->render('error_login',['errorData'=>$errorData])?>
			<?php
			}
			?>
			<!---认证错误 end--->
			<!---终端分布 start--->
			<?php
			if(!empty($terminalData)){
			?>
			<?= $this->render('terminal-page',['source'=>$terminalData])?>
			<?php
			}
			?>
			<!---终端分布 end--->			
			<!---操作系统分布 start--->
			<?php
			if(!empty($operatingData)){
			?>
			<?= $this->render('terminaltype-page',['source'=>$operatingData])?>
			<?php
			}
			?>
			<!---操作系统分布 end--->	
			
			<!---流量统计 start--->
			<?php
			if(!empty($operateBytesData)){
			?>
			<?= $this->render('bytes-page',['source'=>$operateBytesData])?>
			<?php
			}
			?>
			<!---流量统计 end--->		

			<!---产品统计 start--->
			<?php
			if(!empty($UserproductData)){
			?>
			<?= $this->render('user-product-page',['source'=>$UserproductData])?>
			<?php
			}
			?>
			<!---产品统计 end--->		

			<!---产品收费 start--->
			<?php
			if(!empty($FinancialData)){
			?>
			<?= $this->render('financialproduct',['option'=>$FinancialData])?>
			<?php
			}
			?>			
			<!---产品收费 end--->
			
			<!---用户组收费 start--->
			<?php
			if(!empty($groupoption)){
			?>
			<?= $this->render('usergroup',['option'=>$groupoption])?>
			<?php
			}
			?>			
			<!---用户组收费 end--->			
	</div>	
</div>
