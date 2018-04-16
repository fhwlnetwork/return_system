<?php
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var string $name
 * @var string $message
 * @var Exception $exception
 */

$this->title = $name;
?>

<style>
    .panel {
        text-align: center;
    }

    p.error-title {
        color: #333;
        font-size: 30px;
        line-height: 2;
        font-weight: 400
    }

    p.error-title {
        color: #777;
        font-size: 20px
    }
</style>

<div class="padding-top-15px">

    <div class="col-lg-12">

        <div class="panel panel-body">
            <div class="col-sm-12">
                <?= Html::img('/images/error-404.gif'); ?>
            </div>
            <div class="col-sm-12">
                <p class="error-title"><b>“</b><?= \Yii::t('app', 'message error') ?><b>”</b></p>

                <p class="error-desc">
                    <?= nl2br(Html::encode(Yii::$app->errorHandler->exception->getMessage())); ?>
                </p>
            </div>
        </div>

    </div>

</div>