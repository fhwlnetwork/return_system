<?php
use yii\helpers\Html;


$canAddMgr = Yii::$app->user->can('auth/assign/signup');
?>

<div class="panel panel-default">
    <div class="panel-body">
        <div class="col-lg-12 padding-left-0px">
            <h4 class="headline-1">
                <span class="headline-1-index">1</span>
                <span class="headline-content"><?= Yii::t('app', 'create organization') ?></span>
            </h4>

            <div class="col-lg-12">
                <?= Yii::t('app', 'create organization1') ?>
                <?= Html::a(Yii::t('app', 'create organization2'), ['/auth/structure/index'], ['class' => 'btn btn-info btn-xs']) ?>
            </div>
        </div>
        <div class="col-lg-12 padding-left-0px">
            <h4 class="headline-1">
                <span class="headline-1-index">2</span>
                <span class="headline-content"> <?= Yii::t('app', 'create role') ?></span>
            </h4>

            <div class="col-lg-12">
                <?= Yii::t('app', 'role info') ?>
                <?= Html::a(Yii::t('app', 'go create role'), ['/auth/roles/index'], ['class' => 'btn btn-warning btn-xs']); ?>
            </div>
        </div>
        <div class="col-lg-12 padding-left-0px">
            <h4 class="headline-1">
                <span class="headline-1-index">3</span>
                <span class="headline-content"> <?= Yii::t('app', 'add account for one role') ?></span>
            </h4>

            <div class="col-lg-12">
                <?= Yii::t('app', 'add account for one role') ?>
                <?php if ($canAddMgr): ?>
                    <?= Html::a(Yii::t('app', 'Add Manager'), ['/auth/assign/signup'], ['class' => 'btn btn-success btn-xs']); ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-lg-12 padding-left-0px">
            <h4 class="headline-1">
                <span class="headline-1-index">3</span>
                <span class="headline-content"> <?= Yii::t('app', 'Role description') ?></span>
            </h4>

            <div class="col-lg-12">
                <?php if ($canAddMgr): ?>
                    <?= Html::button(Yii::t('app', 'role help1'),  ['class' => 'btn btn-success btn-xs']); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>