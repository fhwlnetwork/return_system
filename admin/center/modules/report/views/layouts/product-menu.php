<?php
use yii\helpers\Html;

$this->registerCssFile('/styles/report_menu.css');

//运维菜单.
$data = ['report/product/index', 'report/product/user-month', 'report/product/user-interval','report/product/user','report/product/product-detail', 'report/actual/index'];
?>

<div class="ali-common-header">
    <div class="ali-common-header-inner">

        <!-- 导航菜单 -->
        <ul class="menu item pull-left" style="margin-bottom: 0px;">

            <?php
            foreach ($data as $val) {
                $can = Yii::$app->user->can($val);
                if ($can) {
                    echo '<li class="top-menu-item" has-dropdown="true">';
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