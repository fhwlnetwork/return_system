<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2017/6/8
 * Time: 8:37
 */

use center\assets\ReportAsset;
use yii\helpers\Html;

ReportAsset::newEchartsJs($this);
$this->title = '性能监控总结';
$this->registerJsFile('/js/app-efficiency.js', ['depends' => [center\assets\ZTreeAsset::className()]]);
?>

<div class="panel panel-default" data-ng-controller="efficiency">
    <div class="panel-body" style="padding: 10px;margin:0;">
        <div class="col-md-2">
            <?=  Html::dropDownList('proc', $model->process_default, $model->process, [
                'ng-model' => 'proc',
                'ng-init' => "proc = '$model->process_default'",
                'class' => 'form-control'
            ])?>
        </div>
        <div class="col-md-2">
            <input type="text" placeholder="开始时间" value=" <?= $model->start_time ?>" class='form-control inputDate'
                   id="start_time" data-ng-model="start_time" data-ng-init="start_time= '<?= $model->start_time ?>'"/>
        </div>
        <div class="col-md-2">
            <input type="text" placeholder="结束时间" value=" <?= $model->stop_time ?>" class='form-control inputDate'
                   id="stop_time" data-ng-model="stop_time" data-ng-init="stop_time= '<?= $model->stop_time ?>' "/>
        </div>
        <div class="col-md-4">
            <?= Html::button(Yii::t('app', 'search'), ['class' => 'btn btn-success', 'ng-click' => 'getSystemStatus()']) ?>
        </div>
    </div>
</div>
<div class="page page-dashboard">
    <div class="row">
        <div class="col-md-12" style="height:500px;" id="efficiency">系统cpu状态</div>
    </div>
    <div id="log_cdra">
        <table class="table table-hover">
            <thead>
            <tr>test
                <th data-ng-repeat="key in head">{{key}}</th>
            </tr>
            </thead>
            <tbody>
            <tr data-ng-repeat="item in showBody  track by $index">
                <td data-ng-repeat="value in item  track by $index">{{value}}</td>
            </tr>
            </tbody>
        </table>
    </div>
</div>
