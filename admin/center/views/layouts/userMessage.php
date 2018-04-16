<?php
use yii\helpers\Html;

if (!Yii::$app->user->identity) {
    Yii::$app->getSession()->setFlash('error', Yii::t('app', 'login timeout'));
    return yii::$app->controller->redirect('/site/index');
}

?>

<li class="dropdown text-normal nav-profile">
    <?= Html::a(Html::img("/images/user.png", ['class' => 'img-circle11 img30_30']) . '<span class="hidden-xs"><span>' . Yii::$app->user->identity->username . '</span></span>', 'javascript:;', ['class' => 'dropdown-toggle', 'data-toggle' => 'dropdown']); ?>

    <ul class="dropdown-menu with-arrow pull-right">
        <li>
            <a href="<?= \yii\helpers\Url::to(['/report/welcome/index']) ?>">
                <i class="fa fa-user"></i>
                <span><?= Yii::t('app', 'report welcome remind1') ?></span>
            </a>
        </li>
        <?php if (Yii::$app->user->can('auth/assign/update')): ?>
            <li>
                <a href="<?= \yii\helpers\Url::to(['/auth/assign/update', 'id' => Yii::$app->user->identity->getId()]) ?>">
                    <i class="fa fa-lock"></i>
                    <span><?= Yii::t('app', 'report welcome remind2') ?></span>
                </a>
            </li>
        <?php endif ?>
        <li class="divider"></li>
        <li><?= Html::a('<i class="fa fa-sign-out"></i><span>' . Yii::t('app', 'report welcome remind3') . '</span>', ['/site/logout'], ['data-method' => 'post']) ?></li>
    </ul>
</li>