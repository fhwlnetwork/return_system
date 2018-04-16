<?php
use center\extend\Tool;
\center\assets\ReportAsset::echartsJs($this);
use center\modules\report\models\Efficiency;
$this->title = Yii::t('app', 'report/monitor/fault');
 ?>

<div class="page page-dashboard">
	<div class="panel panel-default">
			<div class="panel-body">
			故障状态
			</div>
	</div>	
</div>