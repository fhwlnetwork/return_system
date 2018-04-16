<?php
use yii\helpers\Html;
use center\assets\AppAsset;

AppAsset::register($this);
?>

<?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>" class="no-js">
    <head>
        <meta charset="<?= Yii::$app->charset ?>"/>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>
    </head>
    <style media="print" type="text/css">
        .no_prin{display: none;}
    </style>
    <body data-ng-app="app" id="app" data-custom-background="" data-off-canvas-nav="">
    <?php $this->beginBody() ?>
    <div data-ng-controller="AppCtrl">
        <div class="no_prin">
            <section id="header" class="top-header">
                <?= $this->render('header'); ?>
            </section>
            <aside id="nav-container">
                <?= $this->render('nav'); ?>
            </aside>
        </div>
        <div class="view-container">
            <section id="content" class="animate-fade-up">
                <?= $content; ?>
            </section>
        </div>
    </div>
    <?php
        //定义公共js属性，在body开始
        //language 是定义了当前语言，为了配合其他js调用
        $this->registerJs("
            var language = '".Yii::$app->language."';
        ", yii\web\View::POS_BEGIN);
    ?>
    <?php $this->endBody() ?>
    </body>
    </html>
<?php $this->endPage() ?>