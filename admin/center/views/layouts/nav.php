<?php
use yii\helpers\Html;
use center\extend\Tool;
use common\models\User;

$permission = Yii::$app->user->can(Yii::$app->request->pathInfo);
$current_module = $this->context->module->id;
$path = Yii::$app->request->pathInfo;

//是否超管
$isRoot = User::isRoot();

//菜单
$menu = Yii::$app->params['menu'];
$permission = [];
$permission = \center\models\CustomUser::getAuthItems();
$isAllShow = $isRoot && empty($permission) ? true : false;//如果没有给root设置权限，那么现实全部菜单

?>
<div id="nav-wrapper">
    <ul id="nav" data-ng-controller="NavCtrl" data-collapse-nav data-slim-scroll data-highlight-active>

        <?php foreach ($menu as $key1 => $val1) {
            //如果是超级管理员或者有此菜单的权限
            if ($isAllShow || in_array($val1['url'], $permission) || ($isRoot && $val1['url'] == 'setting')) {
                //一级菜单
                $open = false;
                if (in_array($val1['url'], ['Log System', '8980 System', 'cloud_monitor', 'mso'])) {
                    continue;
                }
                //if($path==$val1['url'] || ($val1['items'] && (array_key_exists($path, $val1['items']) || in_array($path, $val1['items']))) ){
                if ($path == $val1['url'] || ($val1['items'] && Tool::array_key_value_exists($path, $val1['items']))) {
                    echo '<li class="open active">';
                    $open = true;
                } else {
                    echo '<li>';
                }

                if ($val1['url'] !== 'report/dashboard/index') {
                    echo '<a href="javascripts:;"><i class="' . $val1['ico'] . '"><span class="icon-bg ' . $val1['color'] . '"></span></i><span>' . Yii::t('app', $val1['label']) . '</span></a>';
                } else {
                    echo Html::a('<i class="' . $val1['ico'] . '"><span class="icon-bg ' . $val1['color'] . '"></span></i><span>' . Yii::t('app', $val1['label']) . '</span>', ['/' . $val1['url']]);
                }

                if ($val1['items']) {
                    //开始二级菜单
                    echo $open ? '<ul style="display:block">' : '<ul>';
                    foreach ($val1['items'] as $key2 => $val2) {
                        if ($isAllShow || ((is_array($val2) && (in_array($key2, $permission) || ($isRoot && $key2 == 'auth/show/index'))) || (!is_array($val2) && in_array($val2, $permission)))) {
                            if (is_array($val2)) {
                                $is_active = $path == $key2 || Tool::array_key_value_exists($path, $val2);
                                echo $is_active ? '<li class="nav_active2">' : '<li>';
                                echo Html::a('<i class="fa fa-caret-right"></i><span>' . Yii::t('app', $key2) . '</span>', ['/' . $key2]);
                            } else {
                                echo $path == $val2 ? '<li class="nav_active2">' : '<li>';
                                echo Html::a('<i class="fa fa-caret-right"></i><span>' . Yii::t('app', $val2) . '</span>', ['/' . $val2]);
                            }
                            echo '</li>';
                        }
                    }
                    echo '</ul>';
                    echo '<i class="fa fa-caret-right icon-has-ul"></i>';
                }
                echo '</li>';
            }
        }
        ?>
        <li>
            <a href="">前台</a>
        </li>

    </ul>
</div>