<?php
$this->title = Yii::t('app', 'welcome');
?>

<div class="page page-general">
    <!-- profile panel -->
    <div class="panel panel-profile">
        <div class="panel-heading text-center bg-info">
            <h3><?= Yii::t('app', 'welcome') ?>：<?= Yii::$app->user->identity->username?></h3>
            <p><?= Yii::t('app', 'roles group') ?>：<?= \common\models\User::getRole() ?></p>
        </div>
        <!--<div class="list-justified-container">
            <ul class="list-justified text-center">
                <li>
                    <p class="size-h3">679</p>
                    <p class="text-muted"></p>
                </li>
                <li>
                    <p class="size-h3">575</p>
                    <p class="text-muted"></p>
                </li>
                <li>
                    <p class="size-h3">23</p>
                    <p class="text-muted"></p>
                </li>
            </ul>
        </div>-->
    </div>
    <!-- end profile panel -->
</div>