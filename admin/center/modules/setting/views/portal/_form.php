<?php

$this->title = Yii::t('app', 'setting/portal/index');

use center\widgets\Alert;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use kucha\ueditor\UEditor;
?>

<style>
    #ads {display: none;}
</style>

<div class="padding-top-15px">
    <div class="col-lg-12">
        <h3 class="page-header">
            <i class="glyphicon glyphicon-wrench"></i>&nbsp;&nbsp;<?= Html::encode($this->title) ?>
            <?= Html::a('<i class="glyphicon glyphicon-share-alt"></i>&nbsp;'. Yii::t('app', 'goBack'), 'index', ['class' => 'btn btn-success pull-right']); ?>
        </h3>

        <div>
            <?= Alert::widget(); ?>
            <div class="panel panel-default">
                <div class="panel-body" ng-control="portalSetting">

                    <!-- pc start-->
                    <h5>
                        <?php
                        if($model->type == 1){
                            echo Yii::t('app', 'setting portal message1');
                        } else {
                            echo Yii::t('app', 'setting portal message2');
                        }
                        ?>
                    </h5>

                    <div class="hr"></div>

                    <?php
                    $form = ActiveForm::begin([
                        'layout' => 'horizontal',
                        'fieldConfig' => [
                            'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}",
                            'horizontalCssClasses' => [
                                'label' => 'col-sm-2',
                                'offset' => 'col-sm-offset-2',
                                'wrapper' => 'col-sm-8',
                                'error' => '',
                                'hint' => '',
                            ],
                        ],
                        'options' => ['enctype' => 'multipart/form-data'],
                    ]);
                    ?>


                    <?php
                    echo $form->field($model, 'logo')->widget(Ueditor::className(), [
                        'id'=> 'Portal[logo]',
                        'clientOptions' => [
                            //编辑区域大小
                            'initialFrameHeight' => '200',
                            //设置语言
                            'lang' =>Yii::$app->language  == 'zh-CN' ? 'zh-cn' : 'en', //中文为 zh-cn
                        ]
                    ]);

                    //pc 需要banner 移动端不需要
                    if($model->type == 1){
                        echo $form->field($model, 'banner')->widget(Ueditor::className(), [
                            'id'=> 'Portal[banner]',
                            'clientOptions' => [
                                //编辑区域大小
                                'initialFrameHeight' => '200',
                                //设置语言
                                'lang' =>Yii::$app->language  == 'zh-CN' ? 'zh-cn' : 'en', //中文为 zh-cn
                            ]
                        ]);
                    } ?>

                    <?php
                    echo $form->field($model, 'top_banner')->hint(Yii::t('app', 'portal_help1'))->widget(Ueditor::className(), [
                        'id'=> 'Portal[top_banner]',
                        'clientOptions' => [
                            //编辑区域大小
                            'initialFrameHeight' => '200',
                            //设置语言
                            'lang' =>Yii::$app->language  == 'zh-CN' ? 'zh-cn' : 'en', //中文为 zh-cn
                        ]
                    ]);
                    echo $form->field($model, 'footer')->hint(Yii::t('app', 'portal_help4'))->widget(Ueditor::className(), [
                        'id'=> 'Portal[footer]',
                        'clientOptions' => [
                            //编辑区域大小
                            'initialFrameHeight' => '200',
                            //设置语言
                            'lang' =>Yii::$app->language  == 'zh-CN' ? 'zh-cn' : 'en', //中文为 zh-cn
                        ]
                    ]);
                    echo $form->field($model, 'examples_name')->textInput();
                    ?>

                    <div class="form-group">
                        <label class="control-label col-sm-2"></label>

                        <div class="col-sm-8">
                            <?= Html::submitButton(Yii::t('app', 'save'), ['class' => 'btn btn-success col-sm-12']) ?>
                        </div>
                    </div>
                    <?php ActiveForm::end(); ?>
                    <!-- pc stop-->
                </div>
            </div>
    </div>
</div>