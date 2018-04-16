<?php
/**
 * ZTree包
 * User: ligang
 * Date: 2015/4/2
 * Time: 14:04
 */

namespace center\assets;


use yii\web\AssetBundle;

class ZTreeAsset extends AssetBundle
{
    public $depends = [
        'center\assets\AppAsset',
    ];
    public $js = [
        'lib/ztree/js/jquery.ztree.core-3.5.js',
        'lib/ztree/js/jquery.ztree.excheck-3.5.js',
        'lib/ztree/js/jquery.ztree.exedit-3.5.js'
    ];
    public $css = [
        'lib/ztree/css/zTreeStyle/zTreeStyle.css',
        'styles/ztree-custom.css',
    ];

    //导入 ztree_select_multi.js 文件  ztree_select_mult.js 文件是一个多选js 文件.
    public static function addZtreeSelectMulti($view) {
        $view->registerJsFile('/js/ztree_select_multi.js', ['depends' => [\center\assets\ZTreeAsset::className()]]);
    }
    public static function addZtreeSelectMulti_from($view) {
        $view->registerJsFile('/js/ztree_select_multi_from.js', ['depends' => [\center\assets\ZTreeAsset::className()]]);
    }
    //组织结构js
    public static function addZtreeStructure($view) {
        $view->registerJsFile('/js/ztree_struct.js', ['depends' => [\center\assets\ZTreeAsset::className()]]);
    }
}