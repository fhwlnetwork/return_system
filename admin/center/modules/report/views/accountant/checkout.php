<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2017/5/27
 * Time: 15:05
 */
use center\assets\ReportAsset;


ReportAsset::newEchartsJs($this);
ReportAsset::checkoutJs($this);


$this->title = Yii::t('app', 'report/accountant/checkout');
echo $this->render('/layouts/accountant-menu');
?>

<div class="page page-dashboard">
    <!--用户详情-->
    <div class='raw' style='padding-bottom: 30px'>
        <div class="col-lg-3 col-xsm-6">
            <div class="panel mini-box">
                <span class="box-icon bg-success">
                    <i class="fa fa-users"></i>
                </span>

                <div class="box-info">
                    <p class="size-h2 school-add-number"><?= sprintf('%.2f', $data['this']) ?></p>

                    <p class="text-muted"><?= Yii::t('app', 'Settlement this month') ?></p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-xsm-6">
            <div class="panel mini-box">
                <span class="box-icon bg-info">
                    <i class="fa fa-user-plus"></i>
                </span>

                <div class="box-info">
                    <p class="size-h2 school-add-number"><?= sprintf('%.2f', $data['30']) ?></p>

                    <p class="text-muted"><?= Yii::t('app', 'Last month settlement') ?></p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-xsm-6">
            <div class="panel mini-box">
                <span class="box-icon bg-warning">
                    <i class="fa fa-user"></i>
                </span>

                <div class="box-info">
                    <p class="size-h2 alliance-auth-number"><?= sprintf('%.2f', $data['60']) ?></p>

                    <p class="text-muted"><?= Yii::t('app', 'Last last month settlement') ?></p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-xsm-6">
            <div class="panel mini-box">
                <span class="box-icon bg-danger">
                    <i class="fa fa-user"></i>
                </span>

                <div class="box-info">
                    <p class="size-h2 alliance-user-number"><?= sprintf('%.2f', $data['all']) ?></p>

                    <p class="text-muted"><?= Yii::t('app', 'Total settlement') ?></p>
                </div>
            </div>
        </div>
    </div>
    <div class='raw' style='padding-top: 30px'>
        <!--    最近六个月消费情况-->
        <div class="col-md-12" style="padding-right: 15px">
            <div id="half_year" class="col-md-12" style="height:400px;padding:30px"></div>
        </div>
    </div>
    <div class="col-md-12" style="height:20px;background: #f1f1f1;"></div>
    <div class='raw' style='padding-top: 30px;'>
        <!--    最近30天消费情况-->
        <div class="col-md-6" style="padding-right: 15px;background: #f1f1f1;">
            <div id="recently_thirty_group" class="col-md-12" style="height:500px;padding:30px"></div>
        </div>
        <!--    最近60天消费情况-->
        <div class="col-md-6" style="padding-left: 15px;background: #f1f1f1;">
            <div id="recently_sixty_group" class="col-md-12" style="height:500px;padding:30px"></div>
        </div>
    </div>
    <div class="col-md-12" style="height:20px;background: #f1f1f1;"></div>
    <div class='raw' style='padding-top: 30px;'>
        <!--   最近用户组消费top40-->
        <div class="col-md-6" style="padding-right: 15px;background: #f1f1f1;">
            <div id="recently-thirty-product" class="col-md-12" style="height:500px;padding:30px"></div>
        </div>
        <!--    最近产品消费-->
        <div class="col-md-6" style="padding-left: 15px;background: #f1f1f1;">
            <div id="recently-sixty-product" class="col-md-12" style="height:500px;padding:30px"></div>
        </div>
    </div>
    <div class="col-md-12" style="height:20px;background: #f1f1f1;"></div>
</div>

<!--    <div class="col-md-12">-->
<!---->
<!--    </div>-->
<?php
$this->registerJs("
    $('#tab a').click(function (e) {
          e.preventDefault();//阻止a链接的跳转行为
          $(this).tab('show');//显示当前选中的链接及关联的content
    })
 ");
?>
