<?php
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var string $name
 * @var string $message
 * @var Exception $exception
 */

$this->title = Yii::t('app', 'message error');
?>

<div class="page-err">
    <div class="text-center">
        <div class="err-status">
            <h1>
                ERROR
            </h1>
        </div>
        <div class="err-message">
            <h2>
                <?php if($message): ?>
                    <?=$message?>
                <?php else: ?>
                    <?= Yii::t('app', 'message unknown error')?>
                <?php endif ?>
            </h2>
        </div>
        <div class="err-body">
            <?= Html::a('<span class="glyphicon glyphicon-home"></span><span class="space"></span>Go Home', ['/'], ['class' => 'btn btn-lg btn-goback']); ?>
        </div>
    </div>
</div>