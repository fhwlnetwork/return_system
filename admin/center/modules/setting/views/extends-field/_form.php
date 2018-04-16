<?php
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

$action = $this->context->action->id; //动作
$isEdit = $action == 'update' ? true : false; //是否是编辑
$id = Yii::$app->request->get('id'); //编辑的ID
$attributes = $model->getAttributesList();
?>

<style>
    .tag-list{
        padding-left:0px;
        margin: 0 0px;
    }

    .tag-list li {
        display: inline-block;
        line-height: 2em;
        margin: 0 1px;
    }
</style>

<div class="row">
    <div class="col-md-12">
        <?php $form = ActiveForm::begin([
            'layout' => 'horizontal',
            'id' => 'extends-field-form',
            'action' => $isEdit ? ['update?id=' . $id] : ['add'],
            'fieldConfig' => [
                'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}",
                'horizontalCssClasses' => [
                    'label' => 'col-sm-2',
                    'wrapper' => 'col-sm-9',
                    'error' => '',
                    'hint' => '',
                ],
            ],
        ]); ?>
        <?php if($isEdit):?>
            <?=$form->field($model, 'table_name')->dropDownList($attributes['table_name'],['disabled' => true])?>
        <?php else:?>
        <?=$form->field($model, 'table_name')->dropDownList($attributes['table_name'])?>
        <?php endif;?>
        <div class="form-group">
            <label class="control-label col-sm-2"><?= Yii::t('app', 'defined field') ?></label>

            <div class="col-sm-9">
                <ul class="tag-list">
                    <?php

                    $fieldArr = $model::getFieldDiff($model->table_name); //获取展示.
                    if(!empty($fieldArr)) {
                        foreach($fieldArr as $val){
                            $field_desc = $attributes['system_save'][$val];
                            $class = \common\models\SystemComponents::getColorClass('extends-field', rand(0, 5));
                            echo '<li>';
                            echo Html::a($field_desc, '', ['class' => $class, 'data-ng-click' => 'field_name="' .$val. '";field_desc="' .$field_desc. '"']);
                            echo '</li>';
                        }
                    } else {
                        echo '<li>';
                        echo Html::a(Yii::t('app', 'No results found.'), '', ['class' => 'label label-primary']);
                        echo '</li>';
                    }
                    ?>
                </ul>
            </div>
        </div>


        <?php
        if($model->isNewRecord) {
            echo $form->field($model, 'field_desc', ['inputOptions' => ['data-ng-model' => 'field_desc']])->hint(Yii::t('app', 'field help1'));
            echo $form->field($model, 'field_name', [
                'inputOptions' => [
                    'placeholder' => Yii::t('app', 'field help2'),
                    'data-ng-model' => 'field_name'
                ],
            ])->hint(Yii::t('app', 'field help3'));
        } else {
            echo $form->field($model, 'field_desc')->hint(Yii::t('app', 'field help1'));
            echo $form->field($model, 'field_name', [
                'inputOptions' => [
                    'placeholder' => Yii::t('app', 'field help2'),
                    'readonly' => $model->field_type == 1 ? true : false,
                ],
            ])->hint(Yii::t('app', 'field help3'));
        } ?>

        <?= $form->field($model, 'is_must')->inline()->radioList($attributes['is_must']) ?>

        <!--<div class="row" id="showMoreText">
            <div class="col-sm-12" style="text-align: center; padding-bottom: 30px;">
                <a href="javascript:void(0)" onclick="showMoreField()"><? /*= Yii::t('app', 'field help7')*/ ?></a>
            </div>
        </div>
        <div id="showMore" style="display: none;">-->

        <?= $form->field($model, 'can_search')->inline()->radioList($attributes['can_search']) ?>

        <?= $form->field($model, 'type')->inline()->radioList($attributes['type'], ['itemOptions' => ['ng-model' => 'type']]) ?>

        <div ng-cloak ng-show="type==1" ng-init="type=<?= $model->type ?>">

            <?= $form->field($model, 'value')->textarea(['rows' => 4])->hint(Yii::t('app', 'field help4')) ?>

            <?= $form->field($model, 'show_type')->dropDownList($attributes['show_type'])->hint(Yii::t('app', 'field help5')) ?>

        </div>

        <?= $form->field($model, 'default_value')->hint(Yii::t('app', 'field remind1')) ?>

        <?php /*= $form->field($model, 'rule')*/ ?>

        <?= $form->field($model, 'sort') ?>

        <?= $form->field($model, 'field_hint')->hint(Yii::t('app', 'field remind2')) ?>

        <div class="form-group field- required">
            <div class="col-sm-10 col-sm-offset-2">
                <?= Html::submitButton(Yii::t('app', 'save'), ['class' => 'btn btn-success']) ?>
                <?= Html::a(Yii::t('app', 'goBack'), ['index'], ['class' => 'btn btn-default']) ?>
            </div>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>

<script>
    function showMoreField() {
        $("#showMore").show();
        $("#showMoreText").hide();
    }
</script>
