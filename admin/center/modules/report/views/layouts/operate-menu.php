<?php
use yii\helpers\Html;

$this->registerCssFile('/styles/report_menu.css');

//运营操作菜单.
$data = ['report/operate/index', 'report/operate/bytes',
    'report/operate/bytes-detail','report/operate/timelong', 'report/operate/activity',
    //'report/operate/usergroup',

    'report/error/login'];

$path = Yii::$app->request->pathInfo;
?>

<div class="ali-common-header">
    <div class="ali-common-header-inner">

        <!-- 导航菜单 -->
        <ul class="menu item pull-left" id="J_common_header_menu" data-spm="201">

            <?php
            foreach($data as $val){
                $can = Yii::$app->user->can($val);
                if($can) {
                    if ($path == $val || ($path == 'report/operate' && $val == 'report/operate/index')) {
                        echo '<li class="top-menu-item" style="background: #0196bd" has-dropdown="true">';
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