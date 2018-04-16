<?php
use yii\helpers\Html;
use center\widgets\Alert;
use yii\bootstrap\ActiveForm;

\center\assets\ReportAsset::echartsJs($this);
$this->title = Yii::t('app', 'user/online/ips');

//权限
$canRadius = Yii::$app->user->can('user/online/_radius');
$canProxy = Yii::$app->user->can('user/online/_proxy');
$canDhcp = Yii::$app->user->can('user/online/_dhcp');
$canPortal = Yii::$app->user->can('user/online/_portal');
$canDrop = Yii::$app->user->can('user/online/drop');
$canExport = Yii::$app->user->can('user/online/download');
$canIps = Yii::$app->user->can('user/online/ips');
$colors = ['bg-success', 'bg-info', 'bg-warning'];
$i = 0;
?>

<div class="page page-table">
    <?= Alert::widget() ?>
    <div class="panel panel-default">
        <div class="panel-body" id="AlertServicesImg" style="margin-top:10px;">
            <ul class="nav nav-tabs" id="tab">
                <?php if($canRadius): ?>
                    <li <?php if($params['panel'] == 'radius'): ?> class="active" <?php endif?>>
                        <?= Html::a(Yii::t('app', 'online type1'), ['index', 'panel'=>'radius'])?></li>
                <?php endif ?>

                <?php if($canProxy): ?>
                    <li <?php if($params['panel'] == 'proxy'): ?> class="active" <?php endif?>>
                        <?= Html::a(Yii::t('app', 'online type2'), ['index', 'panel'=>'proxy'])?></li>
                <?php endif ?>

                <?php if($canIps): ?>
                    <li <?php if(strpos(Yii::$app->request->url, '/user/online/ips') === 0): ?> class="active" <?php endif?>>
                        <?= Html::a(Yii::t('app', 'user/online/ips'), ['ips'])?></li>
                <?php endif ?>

            </ul>

            <div class="tab-content">
                <div class="divider"></div>
                <div class="row" >
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-body">
                                <!--- 运营商 --->
                                <?php if(!empty($data)){
                                    foreach($data as $service_name => $one):?>
                                <div class="col-lg-4 col-xsm-6 marbottom20">
                                    <div class="panel mini-box ipsModules alertMes <?=$colors[$i]?>">
                                        <table class="table">
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <?=Yii::t('app', 'Service operator')?>
                                                    </td>
                                                    <td>
                                                        <?=$service_name?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <?=Yii::t('app', 'Current online number')?>
                                                    </td>
                                                    <td><?=$one['count']?></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div>
                                        <?= $this->render('report_online',['source'=>$one['source'], 'id'=>$service_name, 'title'=> ''])?>
                                    </div>
                                </div>
                                <?php $i++;
                                    endforeach;
                                }else{
                                    echo Yii::t('app','no record');
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


        </div>
    </div>
</div>