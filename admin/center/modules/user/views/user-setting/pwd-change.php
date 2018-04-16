<?php
use yii\helpers\Html;
$this->title = \Yii::t('app', 'user/user-setting/pwd');
?>

<div class="padding-top-15px">
    <?= $this->render('/layouts/nav'); ?>

    <div class="col-lg-10">
        <h3 class="page-header">
            <i class="fa fa-comments-o"></i>&nbsp;&nbsp;<?= Html::encode($this->title) ?>
        </h3>

        <div>
            <?= $this->render('pwd-change_view',['rs'=>$rs,'rs2'=>$rs2]); ?>
        </div>
    </div>
</div>