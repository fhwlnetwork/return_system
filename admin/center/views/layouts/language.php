<?php
use yii\helpers\Html;

$lang = Yii::$app->language;
?>
<li class="dropdown langs text-normal">
    <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown">
        <?= Yii::t('app', $lang) ?>
    </a>
    <ul class="dropdown-menu with-arrow pull-right list-langs" role="menu">
        <li data-ng-show="<?= $lang != 'zh-CN' ?>">
            <?= Html::a('<div class="flag flags-china"></div> ' . Yii::t('app', 'zh-CN'), ['/site/language', 'l' => 'zh-CN']) ?>
        <li data-ng-show="<?= $lang != 'en' ?>">
            <?= Html::a('<div class="flag flags-american"></div> ' . Yii::t('app', 'en'), ['/site/language', 'l' => 'en']) ?>
        <li data-ng-show="<?= $lang != 'zh-HK' ?>">
            <?= Html::a('<div class="flag flags-china"></div> ' . Yii::t('app', 'zh-HK'), ['/site/language', 'l' => 'zh-HK']) ?>
    </ul>
</li>