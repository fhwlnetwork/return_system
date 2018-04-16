<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace center\assets;

use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AjaxAsset extends AssetBundle
{
    public $js = [];
    public $basePath = '@webroot';
    //public $baseUrl = '@web';
    public $css = [
        //'styles/googleFontcss.css',
        'bower_components/font-awesome/css/font-awesome.min.css',
        'bower_components/weather-icons/css/weather-icons.min.css',
        'styles/loading-bar.css',
        'styles/main.css',
        'sources/toastr/toastr.css', /* 漂亮的弹出层 DM */
        'lib/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css',//日期控件css
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];

    public $jsOptions = [];

    public function init()
    {
        $this->js = [
            'js/lib/angular.js',/* angularjs 基础类库 */
            'js/lib/ui-bootstrap-tpls-0.11.2.min.js',/* angularjs ui bootstrap类库 */
            'js/lib/slimscroll.js',/* 下拉滚动条 */
            'js/lib/jquery_spinner.js',/* 滑动条库 by 李刚 */
            'js/lib/jquery.sortable.js',/* 拖放排序 */
            'js/lib/bootstrap-fileinput.js',/* 文件上传控件 */
            'js/lib/bootstrap.js', /* bootstrap类库 */
            'js/app.js',/* 基础配置库 */
            'js/app-setting.js',/* 设置模块js */
            'js/app-user.js',/* user模块js */
            'js/app-strategy.js',/* 策略模块js */
            'js/app-financial.js',/* 财务模块js */
            'js/app-log.js',/* 日志模块js */
            'js/app-auth.js',/* 权限模块js */
            'sources/toastr/toastr.js', /* 漂亮的弹出层 DM */
        ];
        parent::init();
    }
}
