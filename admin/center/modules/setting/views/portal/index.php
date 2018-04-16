<?php

$this->title = Yii::t('app', 'setting/portal/index');

use yii\helpers\Html;
use yii\bootstrap\ButtonDropdown;
use center\modules\setting\models\Portal;
use center\widgets\Alert;
use common\models\UserModel;

//权限验证
$canAdd = yii::$app->user->can('setting/portal/create');
$canGive = yii::$app->user->can('setting/portal/give');
$canEdit = yii::$app->user->can('setting/portal/update');
$canDel = yii::$app->user->can('setting/portal/delete');
?>

<style>
    .btn-group {
        padding: 0px 5px 2px 0px;
    }
    .btn-group .dropdown-menu{
        font-size: 12px;
    }
    .dropdown-menu li:last-child a{
        font-weight: bold;
        color: red;
    }
</style>

<div class="padding-top-15px">
    <div class="col-lg-12">
        <h3 class="page-header">
            <i class="glyphicon glyphicon-wrench"></i>&nbsp;&nbsp;<?= Html::encode($this->title) ?>
            <span style="float:right"><?= Html::a(Yii::t('app','help?'),Yii::$app->params['help_url'].md5(Yii::$app->request->pathInfo),['class' => 'btn btn-warning btn-xs','target'=>'_blank'])?></a></span>
        </h3>

        <div>
            <?= Alert::widget(); ?>
            <div class="panel panel-default">
                <div class="panel-body">

                    <?php

                    //所有系统预定义模板
                    foreach ($tmp_arr as $key => $val) {
                        //一个模板为一大块 DIV
                        echo "<div class='col-sm-12 page-header' style='padding-top:5px'>";

                        //预定义模板名称
                        echo "<div class='col-sm-2'>$val</div>";

                        if(\common\models\User::isSuper()) {
                            $portal = Portal::find()->select(['pid', 'portal_name', 'examples_name'])->where(['portal_name' => $key])->asArray()->all();//可管理的模板实例
                        } else {
                            $portal = Portal::find()->select(['pid', 'portal_name', 'examples_name'])->where(['portal_name' => $key, 'pid' => Yii::$app->user->getId()])->asArray()->all();//可管理的模板实例
                        }

                        // 模板被实例过
                        if(!empty($portal)) {

                            foreach ($portal as $val) {
                                $pid[] = $val['pid'];

                                if($val['portal_name'] === $key) {
                                    $examples_name[$val['pid']][] = $val['examples_name'];
                                }
                            }
                            $pid = array_unique($pid);//删除重复的值

                            //管理员以及管理实例部分
                            echo "<div class='col-sm-10'>";

                                foreach ($examples_name as $keys => $val) {

                                    //查询管理员名称
                                    $name = UserModel::getUserName($keys);
                                    echo "<div class='col-sm-12'>";
                                        echo "<div class='col-sm-2'>" .$name. "</div>";
                                        echo "<div class='col-sm-2'>";
                                            //添加实例按钮 判断是走控制器的什么方法,判断 pc 是否存在于字符串中
                                            if(strstr($key, 'pc') !== false) {
                                                echo Html::a(Yii::t('app', 'setting/portal/create'), ['create',['name' => $key]], ['class' => 'btn btn-info btn-xs']);
                                            } else {
                                                echo Html::a(Yii::t('app', 'setting/portal/create'), ['create',['name' => $key]], ['class' => 'btn btn-info btn-xs']);
                                            }
                                        echo "</div>";

                                        //管理员管理本套模板的所有所有实例
                                        echo "<div class='col-sm-8'>";
                                            if(is_array($val)) {
                                                foreach ($val as $keyss => $value) {

                                                    $portals = Portal::find()->where(['portal_name' => $key])->all();
                                                    $dir = Yii::$aliases['@webroot'] . '/portal/' . $name . '/' .$value; //模板文件路径
                                                    $tager = '/portal/' . $name . '/' . $value . '/srun_portal.php';

                                                    //保证实例真是存在并且数据库中保存有实例相关信息
                                                    if(file_exists($dir) && !empty($portals)) {
                                                        echo '<div class="btn-group">
                                                                <button id="w1" class="btn btn-primary btn-xs">' .$value. '</button>
                                                                <button id="w1" class="btn btn-primary btn-xs dropdown-toggle" data-toggle="dropdown" aria-expanded="true"><span class="caret"></span></button>
                                                                <ul id="w2" class="dropdown-menu">';
                                                                    echo $canAdd ? '<li>' .Html::a(Yii::t('app', 'portal_help8'), ['index', ['action' => 'download', 'name' => $value, 'p' => $name]], ['target' => '_blank']). '</li>' : '';
                                                                    echo '<li>' .Html::a(Yii::t('app', 'portal_help7'), [$tager], ['target' => '_blank']). '</li>';
                                                                    echo $canEdit ? '<li>' .Html::a(Yii::t('app', 'setting/portal/update'), ['update', 'name' => $key, 'examples_name' => $value], ['target' => '_blank']). '</li>' : '';
                                                                    echo $canGive ? '<li>' .Html::a(Yii::t('app', 'setting/portal/give'), ['give', 'name' => $name, 'examples_name' => $value], ['target' => '_blank']). '</li>' : '';
                                                                    echo '<li class="divider" role="separator"></li>';
                                                                    echo $canDel ? '<li>' .Html::a(Yii::t('app', 'setting/portal/delete'), ['delete', 'name' => $value], ['data' => ['method' => 'post', 'confirm' => Yii::t('app', 'user base help5')]]). '</li>' : '';
                                                                echo '</ul>';
                                                             echo '</div>';
                                                        ButtonDropdown::widget();
                                                    } else {
                                                        if($keyss == 0) {
                                                            echo "<div style='margin:0'></div>";
                                                        }
                                                    }
                                                }
                                            }
                                        echo "</div>";
                                    echo "</div>";
                                }

                            echo "</div>";
                        } else {
                            //如果模板未曾被实例过
                            echo "<div class='col-sm-10'>";
                                echo "<div class='col-sm-12'>";
                                    echo "<div class='col-sm-2'>".Yii::t('app', 'report operate remind21')."</div>";
                                    echo "<div class='col-sm-1'>";
                                    //判断是走控制器的什么方法,判断 pc 是否存在于字符串中
                                    if(strstr($key, 'pc') !== false) {
                                        echo Html::a(Yii::t('app', 'setting/portal/create'), ['create',['name' => $key]], ['class' => 'btn btn-info btn-xs col-sm-*']);
                                    } else {
                                        echo Html::a(Yii::t('app', 'setting/portal/create'), ['create',['name' => $key]], ['class' => 'btn btn-info btn-xs col-sm-*']);
                                    }
                                    echo "</div>";
                                echo "</div>";
                            echo "</div>";
                        }

                        echo "</div>";
                        unset($examples_name, $pid);//销毁临时数组
                    }
                    ?>

                    <div style="clear: both"></div>

                    <!-- helper-->
                    <div class="callout callout-info">
                        <p class="ng-binding"><?= Yii::t('app', 'portal_help3')?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>