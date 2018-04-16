<?php
use yii\helpers\Html;
use center\assets\AppAsset;

AppAsset::register($this);
?>

<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <link rel="stylesheet" href="/styles/bootstrap.min.css"/>
    <script src="/lib/echarts/build/dist/echarts3.js"></script>
</head>
<style>
</style>
<body>
<?= $content ?>
</body>