<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace center\assets;

use yii\web\AssetBundle;

class ReportAsset extends AssetBundle
{
    public $basePath = '@webroot';

    public $css = [];

    public $js = [];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];

    public static function echartsJs($view){
        return $view->registerJsFile('lib/echarts/build/dist/echarts.js', ['depends' => [\center\assets\ReportAsset::className()], 'position' => $view::POS_HEAD]);
    }
    public static function newEchartsJs($view){
        return $view->registerJsFile('lib/echarts/build/dist/echarts3.js', ['depends' => [\center\assets\ReportAsset::className()], 'position' => $view::POS_HEAD]);
    }
    public static function accountantJs($view){
        return $view->registerJsFile('js/app-accountant.js', ['depends' => [\center\assets\ReportAsset::className()], 'position' => $view::POS_HEAD]);
    }
    public static function checkoutJs($view){
        return $view->registerJsFile('js/app-checkout.js', ['depends' => [\center\assets\ReportAsset::className()], 'position' => $view::POS_HEAD]);
    }
    public static function detailJs($view){
        return $view->registerJsFile('js/app-detail.js', ['depends' => [\center\assets\ReportAsset::className()], 'position' => $view::POS_HEAD]);
    }
}
