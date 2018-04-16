<?php

$this->title = Yii::t('app', 'portal_help7');

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\ButtonGroup;
use yii\bootstrap\Button;
use center\modules\setting\models\Portal;
?>

<style>
    .btn-group .dropdown-menu{
        font-size: 12px;
    }
</style>

<div class="padding-top-15px">
    <div class="col-lg-12">
        <h3 class="page-header">
            <i class="glyphicon glyphicon-transfer"></i>&nbsp;&nbsp;<?= Html::encode($this->title) ?>
        </h3>

        <div>
            <div class="panel panel-default">
                <div class="panel-body">

                    <?php
                    $form = ActiveForm::begin([
                        'layout' => 'horizontal',
                        'fieldConfig' => [
                            'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{endWrapper}\n{error}",
                            'horizontalCssClasses' => [
                                'label' => 'col-sm-2',
                                'offset' => 'col-sm-offset-3',
                                'wrapper' => 'col-sm-3',
                                'error' => '',
                                'hint' => '',
                            ],
                        ],
                    ]);
                    ?>

                        <?= $form->field($model, 'action', ['template' => '{input}'])->hiddenInput(); ?>
                        <?= $form->field($model, 'source_path', ['template' => '{input}'])->hiddenInput(); ?>
                        <?= $form->field($model, 'dest_ip')->textInput(['placeholder' => Yii::t('app', 'portal_help11')]); ?>
                        <?= $form->field($model, 'dest_path', ['template' => '{input}'])->hiddenInput(); ?>
                        <?= \yii\helpers\Html::submitButton(Yii::t('app', 'submit'), ['class' => 'btn btn-primary col-sm-offset-3']); ?>

                    <?php $form->end(); ?>

                    <!-- helper-->
                    <div class="callout callout-info">
                        <p>
                            <span style="font-family: 微软雅黑, &#39;Microsoft YaHei&#39;; font-size: 14px; color: rgb(12, 12, 12);"><?= Yii::t('app', 'setting portal give1') ?></span>
                        </p>
                        <table border="1" style="line-height: 25px;text-indent: 1em;margin-left: 2em;">
                            <tr class="firstRow">
                                <td width="150" valign="top" style="word-break: break-all;" height="0">
                                    <span style="font-family: 微软雅黑, &#39;Microsoft YaHei&#39;; font-size: 12px; color: rgb(12, 12, 12);"><?= Yii::t('app', 'setting portal give2') ?></span>
                                </td>
                                <td width="350" valign="top" style="word-break: break-all;" height="0">
                                    <span style="font-family: 微软雅黑, &#39;Microsoft YaHei&#39;; font-size: 12px; color: rgb(12, 12, 12);"><?= Yii::t('app', 'setting portal give3') ?></span>
                                </td>
                            </tr>
                            <tr>
                                <td width="" valign="top" style="word-break: break-all;">
                                    <p style="margin-bottom: 0px;">
                                        <span style="color: rgb(12, 12, 12); font-family: 微软雅黑, &#39;Microsoft YaHei&#39;; font-size: 12px; font-weight: bold; line-height: 19px; text-align: right;">Server Ip</span>
                                    </p>
                                </td>
                                <td width="" valign="top" style="word-break: break-all;">
                                    <span style="font-family: 微软雅黑, &#39;Microsoft YaHei&#39;; font-size: 12px; color: rgb(12, 12, 12);"><?= Yii::t('app', 'setting portal give4') ?></span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>