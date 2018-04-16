<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2018/3/9
 * Time: 20:46
 */

use yii\helpers\Html;

$this->registerCssFile('/styles/report_menu.css');

//运维菜单.
if (\common\models\User::isSuper()) {
    $data = ['product/default/work-history', 'product/default/works'];
} else {
    $data = ['product/default/base', 'product/default/work-history', 'product/default/works'];
}

$path = Yii::$app->request->pathInfo;
?>

<div class="ali-common-header">
    <div class="ali-common-header-inner">

        <!-- 导航菜单 -->
        <ul class="menu item pull-left" style="margin-bottom: 0px;">

            <?php
            foreach ($data as $val) {
                $can = Yii::$app->user->can($val);
                if ($can) {
                    if ($val == $path) {
                        echo '<li class="top-menu-item" style="background:#1c7ebb" has-dropdown="true">';
                    } else {
                        echo '<li class="top-menu-item" has-dropdown="true">';
                    }
                    echo '<span class="menu-hd">';
                    echo Html::a(Yii::t('app', $val), ['/' . $val]);
                    echo '</span>';
                    echo '</li>';
                }
            }
            ?>

        </ul>
    </div>
</div>