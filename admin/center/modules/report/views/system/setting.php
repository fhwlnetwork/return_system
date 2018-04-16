<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2017/6/30
 * Time: 14:23
 */
use \yii\helpers\Html;

$this->title = '8080ip设置';
?>
<style>
    .form-group {
        margin-top: 10px;
    }
</style>
<div class="padding-top-15px">
    <?= $this->render('../../../auth/views/layouts/nav'); ?>
    <form action="setting" method="post">
        <div class="col-lg-10">
            <?= \center\widgets\Alert::widget();?>
            <h3 class="page-header">
                <i class="glyphicon glyphicon-user"></i>&nbsp;&nbsp;<?= Html::encode($this->title) ?>
            </h3>

            <div class="form-group">
                <div class="col-sm-9 col-sm-offset-2">
                    <label class="control-label col-sm-3">8080 ip类型</label>

                    <div class="col-md-6">
                        <?= Html::dropDownList('http_type', $detail['http_type'], ['https' => 'https', 'http' => 'http'], [
                            'class' => 'form-control',
                            'title' => '8080 ip类型'
                        ]) ?>
                    </div>
                </div>
            </div>
            <div class="form-group" style="margin-top:50px;">
                <div class="col-sm-9 col-sm-offset-2">
                    <label class="control-label col-sm-3">8080 ip地址</label>

                    <div class="col-md-6">
                        <?= Html::textInput('http_ip', $detail['http_ip'] ? $detail['http_ip'] : $_SERVER['REMOTE_ADDR'], [
                            'class' => 'form-control',
                            'title' => '8080 ip地址'
                        ]) ?>
                    </div>
                </div>
            </div>
            <div class="form-group" style="margin-top:90px;">
                <div class="col-sm-9 col-sm-offset-2">
                    <label class="control-label col-sm-3">8080 ip端口</label>

                    <div class="col-md-6">
                        <?= Html::textInput('http_port', $detail['http_port'] ? $detail['http_port'] : 8080, [
                            'class' => 'form-control',
                            'title' => '8080 ip端口',
                            'type' => 'number'
                        ]) ?>

                    </div>
                </div>
            </div>
            <div class="form-group"  style="margin-top:130px;">
                <label class="control-label col-sm-5"></label>
                <?= Html::submitButton(Yii::t('app', 'Setting'), ['class' => 'btn btn-primary ', 'style' => 'min-width: 150px;']); ?>
            </div>
        </div>
    </form>
</div>