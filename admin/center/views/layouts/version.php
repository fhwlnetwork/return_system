<?php
use yii\helpers\Html;

$lang = Yii::$app->language;
?>

<li class="dropdown langs text-normal">
    <?= Html::a(Yii::t('app', 'version') . ': TS-V.1', 'javascript:;'); ?>
</li>
