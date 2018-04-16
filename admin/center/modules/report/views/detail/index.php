<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2017/6/1
 * Time: 9:07
 */
use center\assets\ReportAsset;
use center\widgets\Alert;
use yii\helpers\Html;


ReportAsset::newEchartsJs($this);
ReportAsset::detailJs($this);


$this->title = Yii::t('app', 'report/detail/index');
echo $this->render('/layouts/accountant-menu');
?>
<div class="padding-top-15px">

    <div class="col-lg-12">
        <h3 class="page-header">
            <i class="glyphicon glyphicon-list-alt size-h4"></i>&nbsp;<?= Html::encode($this->title); ?>
        </h3>
        <?= Alert::widget(); ?>
        <div class="panel panel-body">
            <ul class="nav nav-tabs" id="tab" data-ng-init="type='bytes'">
                <li class="active"><a ng-click="type='bytes'"><?= $this->title ?></a></li>
                <li><a href="#" ng-click="type='times'"><?= Yii::t('app', 'timelong summary') ?></a></li>
            </ul>
        </div>
    </div>
</div>
<div class="page page-dashboard">
    <!--用户详情-->
    <div class='raw' style='padding-bottom: 30px' data-ng-show="type == 'times'">
        <div class="col-lg-3 col-xsm-6">
            <div class="panel mini-box">
                <span class="box-icon bg-success">
                    <i class="fa fa-users"></i>
                </span>

                <div class="box-info">
                    <p class="size-h2 school-add-number" style="font-size:14px;"><?= $data['this']['times'] ?></p>

                    <p class="text-muted"><?= Yii::t('app', 'Internet access time this month') ?></p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-xsm-6">
            <div class="panel mini-box">
                <span class="box-icon bg-info">
                    <i class="fa fa-user-plus"></i>
                </span>

                <div class="box-info">
                    <p class="size-h2 school-add-number" style="font-size:14px;"><?= $data['30']['times'] ?></p>

                    <p class="text-muted"><?= Yii::t('app', 'Last month time') ?></p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-xsm-6">
            <div class="panel mini-box">
                <span class="box-icon bg-warning">
                    <i class="fa fa-user"></i>
                </span>

                <div class="box-info">
                    <p class="size-h2 school-add-number" style="font-size:14px;"><?= $data['60']['times'] ?></p>

                    <p class="text-muted"><?= Yii::t('app', 'Last Last month traffic') ?></p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-xsm-6">
            <div class="panel mini-box">
                <span class="box-icon bg-danger">
                    <i class="fa fa-user"></i>
                </span>

                <div class="box-info">
                    <p class="size-h2 school-add-number" style="font-size:14px;"><?= $data['all']['times'] ?></p>

                    <p class="text-muted"><?= Yii::t('app', 'Total Internet time') ?>
                        <small>(<?= Yii::t('app', 'Does not include today')?>)</small>
                    </p>
                </div>
            </div>
        </div>
    </div>
    <div class='raw' style='padding-bottom: 30px' data-ng-show="type == 'bytes'">
        <div class="col-lg-3 col-xsm-6">
            <div class="panel mini-box">
                <span class="box-icon bg-success">
                    <i class="fa fa-users"></i>
                </span>

                <div class="box-info">
                    <p class="size-h2 school-add-number"><?= $data['this']['bytes'] ?></p>

                    <p class="text-muted"><?= Yii::t('app', 'Internet traffic this month') ?></p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-xsm-6">
            <div class="panel mini-box">
                <span class="box-icon bg-info">
                    <i class="fa fa-user-plus"></i>
                </span>

                <div class="box-info">
                    <p class="size-h2 school-add-number"><?= $data['30']['bytes'] ?></p>

                    <p class="text-muted"><?= Yii::t('app', 'Last month traffic') ?></p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-xsm-6">
            <div class="panel mini-box">
                <span class="box-icon bg-warning">
                    <i class="fa fa-user"></i>
                </span>

                <div class="box-info">
                    <p class="size-h2 alliance-auth-number"><?= $data['60']['bytes'] ?></p>

                    <p class="text-muted"><?= Yii::t('app', 'Last Last month traffic') ?></p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-xsm-6">
            <div class="panel mini-box">
                <span class="box-icon bg-danger">
                    <i class="fa fa-user"></i>
                </span>

                <div class="box-info">
                    <p class="size-h2 alliance-user-number"><?= $data['all']['bytes'] ?></p>

                    <p class="text-muted"><?= Yii::t('app', 'Total Internet traffic') ?>
                        <small>(<?= Yii::t('app', 'Does not include today')?>)</small>
                    </p>
                </div>
            </div>
        </div>
    </div>
    <div class='raw' style='padding-top: 30px'>
        <!--    最近六个月消费情况-->
        <div class="col-md-12" style="padding-right: 15px" data-ng-show="type == 'bytes'">
            <div id="half_year_bytes" class="col-md-12" style="height:400px;padding:30px"></div>
        </div>
        <div class="col-md-12" style="padding-left: 15px" data-ng-show="type == 'times'">
            <div id="half_year_times" class="col-md-12" style="height:400px;padding:30px"></div>
        </div>
    </div>

    <div class="col-md-12" style="height:20px;background: #f1f1f1;"></div>
    <div class='raw' style='padding-top: 30px;'>
        <!--    最近30天消费情况-->
        <div class="col-md-6" style="padding-right: 15px;background: #f1f1f1;" data-ng-show="type == 'bytes'">
            <div id="recently_thirty_group_bytes" class="col-md-12" style="height:500px;padding:30px"></div>
        </div>
        <div class="col-md-6" style="padding-right: 15px;background: #f1f1f1;" data-ng-show="type == 'bytes'">
            <div id="recently_sixty_group_bytes" class="col-md-12" style="height:500px;padding:30px"></div>
        </div>
        <!--    最近60天消费情况-->
        <div class="col-md-6" style="padding-left: 15px;background: #f1f1f1;" data-ng-show="type == 'times'">
            <div id="recently_thirty_group_times" class="col-md-12" style="height:500px;padding:30px"></div>
        </div>
        <div class="col-md-6" style="padding-left: 15px;background: #f1f1f1;" data-ng-show="type == 'times'">
            <div id="recently_sixty_group_times" class="col-md-12" style="height:500px;padding:30px"></div>
        </div>
    </div>
    <div class="col-md-12" style="height:20px;background: #f1f1f1;"></div>
    <div class='raw' style='padding-top: 30px;'>
        <!--   最近用户组消费top40-->
        <div class="col-md-6" style="padding-right: 15px;background: #f1f1f1;" data-ng-show="type == 'bytes'">
            <div id="recently_thirty_products_bytes" class="col-md-12" style="height:500px;padding:30px"></div>
        </div>
        <div class="col-md-6" style="padding-right: 15px;background: #f1f1f1;" data-ng-show="type == 'bytes'">
            <div id="recently_sixty_products_bytes" class="col-md-12" style="height:500px;padding:30px"></div>
        </div>
        <!--    最近产品消费-->
        <div class="col-md-6" style="padding-left: 15px;background: #f1f1f1;" data-ng-show="type == 'times'">
            <div id="recently_thirty_products_times" class="col-md-12" style="height:500px;padding:30px"></div>
        </div>
        <div class="col-md-6" style="padding-left: 15px;background: #f1f1f1;" data-ng-show="type == 'times'">
            <div id="recently_sixty_products_times" class="col-md-12" style="height:500px;padding:30px"></div>
        </div>
    </div>
    <div class="col-md-12" style="height:20px;background: #f1f1f1;"></div>
    <div class='raw' style='margin-top: 30px;margin-bottom: 30px;'>
        <!--   最近用户组消费top40-->
        <!--    最近产品消费-->
        <div class="col-md-12" style="padding-left: 15px;background: #f1f1f1;" data-ng-show="type == 'bytes'">
            <div id="recently-bytes" class="col-md-12" style="height:500px;padding:30px"></div>
        </div>
        <div class="col-md-12" style="padding-left: 15px;background: #f1f1f1;" data-ng-show="type == 'times'">
            <div id="recently-times" class="col-md-12" style="height:500px;padding:30px"></div>
        </div>
    </div>
</div>

<?php
$this->registerJs("
    $('#tab a').click(function (e) {
          e.preventDefault();//阻止a链接的跳转行为
          $(this).tab('show');//显示当前选中的链接及关联的content
    })
 ");
?>
