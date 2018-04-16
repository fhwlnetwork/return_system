<?php
use yii\helpers\Html;

$this->registerCssFile('/styles/report_menu.css');

//财务操作菜单.
$data = [
    'report/financial/methods',
    'report/financial/list',
    'report/financial/index',
    'report/financial/paytype',
    'report/financial/product',
    'report/financial/usergroup',
    'report/financial/checkout',
];
$path = Yii::$app->request->pathInfo;
?>

<div class="ali-common-header">
    <div class="ali-common-header-inner">

        <!-- 导航菜单 -->
        <ul class="menu item pull-left" id="J_common_header_menu" data-spm="201">

            <?php
            foreach ($data as $val) {
                $can = Yii::$app->user->can($val);
                if ($can) {
                    if($path == $val || ($path == 'report/financial/projects' && $val == 'report/financial/methods')){
                        echo '<li class="top-menu-item" has-dropdown="true" style="background: #0196bd">';
                    }else{
                        echo '<li class="top-menu-item" has-dropdown="true">';
                    }
                    echo '<span class="menu-hd">';
                    if ($val == 'report/financial/list') {
                        echo Html::a(Yii::t('app', 'table report'), ['/' . $val, 'type' => 'methods']);
                    } else {
                        echo Html::a(Yii::t('app', $val), ['/' . $val]);
                    }

                    echo '</span>';
                    echo '</li>';
                }
            }
            ?>

        </ul>

        <!-- search -->
        <div class="pull-right" id="J_common_header_search_wrap"></div>
    </div>
</div>