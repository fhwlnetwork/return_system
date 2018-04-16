<?php
/**
 * 默认的首页模板文件
 * 如果有列表和添加的权限，那么显示两个，上面是添加表单，通过折叠进行显示隐藏，下面是列表形式
 * 如果只有添加权限，没有列表权限，那么直接在本页面显示添加表单，去掉折叠功能
 * @var yii\web\View $this
 * @var $list Array 套餐列表
 * @var $model center\modules\strategy\models\Package
 */

use yii\helpers\Html;
use center\widgets\Alert;
use yii\grid\GridView;
use center\modules\setting\models\ExtendsField;

$this->title = \Yii::t('app', 'Extends Field');
$attributes = ExtendsField::getAttributesList();

//******权限控制******
//是否有添加权限
$canAdd = Yii::$app->user->can('setting/extends-field/add');
//是否有编辑权限
$canEdit = Yii::$app->user->can('setting/extends-field/edit');
//是否有删除权限
$canDelete = Yii::$app->user->can('setting/extends-field/delete');
?>

<div class="padding-top-15px">

    <div class="col-lg-12">

        <h3 class="page-header">
            <i class="glyphicon glyphicon-user size-h4"></i>&nbsp;&nbsp;<?= Html::encode($this->title) ?>
            <?= $canAdd ? Html::a('<i class="fa fa-plus"></i>&nbsp;' . Yii::t('app', 'add'), ['add'], ['class' => 'btn btn-success pull-right']) : ''; ?>
        </h3>

        <div>
            <?= Alert::widget(); ?>
            <div class="panel panel-default">
                <div class="panel-body">
                    <?=
                    GridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'columns' => [
                            [
                                'attribute' => 'id',
                                'headerOptions' => ['width' => '50'],
                            ],
                            'field_desc',
                            'field_name',
                            [
                                'attribute' => 'table_name',
                                'headerOptions' => ['width' => '150'],
                                'value' => function($dataProvider){
                                        $tables = ExtendsField::getAttributesList()['table_name'];
                                        if(array_key_exists($dataProvider['table_name'], $tables)){
                                            return $tables[$dataProvider['table_name']];
                                        }
                                    },
                                'filter' => $attributes['table_name'],
                            ],
                            [
                                'attribute' => 'is_must',
                                'headerOptions' => ['width' => '90'],
                                'value' => function($dataProvider){
                                        switch($dataProvider['is_must']) {
                                            case 0:
                                                return Yii::t('app', 'no');
                                                break;
                                            case 1:
                                                return Yii::t('app', 'yes');
                                                break;
                                            default:
                                                break;
                                        }
                                    },
                                'filter' => $attributes['is_must'],
                            ],
                            [
                                'attribute' => 'can_search',
                                'headerOptions' => ['width' => '120'],
                                'value' => function($dataProvider){
                                        switch($dataProvider['can_search']) {
                                            case 0:
                                                return Yii::t('app', 'no');
                                                break;
                                            case 1:
                                                return Yii::t('app', 'yes');
                                                break;
                                            default:
                                                break;
                                        }
                                    },
                                'filter' => $attributes['can_search'],
                            ],
                            [
                                'attribute' => 'type',
                                'headerOptions' => ['width' => '80'],
                                'value' => function($dataProvider){
                                        switch($dataProvider['type']) {
                                            case 0:
                                                return Yii::t('app', 'field type1');
                                                break;
                                            case 1:
                                                return Yii::t('app', 'field type2');
                                                break;
                                            default:
                                                break;
                                        }
                                    },
                                'filter' => $attributes['type'],
                            ],

                            [
                                'label' => Yii::t('app', 'default value'),
                                'value' => function($dataProvider){
                                        return $dataProvider['default_value'];
                                    }
                            ],
                            [
                                'label' => Yii::t('app', 'field sort'),
                                'value' => function($dataProvider){
                                        return $dataProvider['sort'];
                                    },
                                'headerOptions' => ['width' => '80'],
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'header' => Yii::t('app', 'operate'),
                                'headerOptions' => ['width' => '50'],
                                'template' => ($canEdit ? '{update}' : '') . ($canDelete ? ' {delete}' : ''),
                            ],
                        ],
                    ]);
                    ?>
                </div>
            </div>
        </div>

    </div>
</div>