<?php
use yii\helpers\Html;

$this->registerCssFile('/styles/report_menu.css');

//运维菜单.
$path = Yii::$app->request->pathInfo;
$data = ['report/accountant/index', 'report/accountant/checkout', 'report/detail/index', 'report/dashboard/system','report/detail/user'];
?>

<div class="ali-common-header">
    <div class="ali-common-header-inner">

        <!-- 导航菜单 -->
        <ul class="menu item pull-left" style="margin-bottom: 0px;">

            <?php
            foreach ($data as $val) {
                $can = Yii::$app->user->can($val);
                if ($can) {
                    if ($path == $val) {
                        echo '<li class="top-menu-item" has-dropdown="true" style="background: #0196bd">';
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