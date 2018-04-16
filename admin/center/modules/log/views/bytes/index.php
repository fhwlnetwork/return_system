<?php
use yii\helpers\Html;
use center\widgets\Alert;
use center\widgets\SuperLinkPager;
use center\extend\Tool;
use center\assets\ZTreeAsset;
use center\modules\auth\models\SrunJiegou;

$this->title = \Yii::t('app', '流量统计');
//流量字段
$bytesArr = ['total_bytes', 'bytes_in', 'bytes_out', 'bytes_in6', 'bytes_out6'];
//ztree 搜索用
ZTreeAsset::register($this);
$this->registerJsFile('/js/ztree_select.js', ['depends' => [center\assets\ZTreeAsset::className()]]);
$state = (!empty($params) && isset($params['advanced'])) ? 'true' :'false';
?>

<?php
$this->registerJs("
    createTree('zTreeAddUser');
");
$groupId = isset($params['group_id']) ? $params['group_id'] : '';
if ($state) {
    //声明ztree当前选中的id
    $this->registerJs("
    var currentZTreeId = '" . $groupId . "';
", yii\web\View::POS_BEGIN);
}

?>
<style type="text/css">
    .ztree li a.curSelectedNode span {
        background-color: #0088cc;
        color: #fff;
        border-radius: 2px;
        padding: 2px;
    }
</style>
<div class="page">
    <form name="form_constraints" action="<?=\yii\helpers\Url::to(['index'])?>" class="form-horizontal form-validation" method="get">
        <?= Alert::widget() ?>
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="form-group">
                            <div class="col-md-2"><?=Html::input('text', 'user_name', isset($params['user_name'])?$params['user_name']:'', ['class'=>'form-control', 'placeHolder'=>Yii::t('app', 'account')])?></div>
                            <div class="col-md-2"><?=Html::input('text', 'start_add_time', isset($params['start_add_time'])?$params['start_add_time']:'', ['class'=>'form-control inputDate', 'placeHolder'=>Yii::t('app', 'start add time')])?></div>
                            <div class="col-md-2"><?=Html::input('text', 'end_add_time', isset($params['end_add_time'])?$params['end_add_time']:'', ['class'=>'form-control inputDate', 'placeHolder'=>Yii::t('app', 'end add time')])?></div>
                            <div class="col-md-2"><?=Html::input('text', 'user_ip_start', isset($params['user_ip_start'])?$params['user_ip_start']:'', ['class'=>'form-control', 'placeHolder'=>Yii::t('app', '开始ip')])?></div>
                            <div class="col-md-2"><?=Html::input('text', 'user_ip_end', isset($params['user_ip_end'])?$params['user_ip_end']:'', ['class'=>'form-control', 'placeHolder'=>Yii::t('app', '结束ip')])?></div>
                            <div class="col-md-2"><?=Html::input('text', 'user_ip6_start', isset($params['user_ip6_start'])?$params['user_ip6_start']:'', ['class'=>'form-control', 'placeHolder'=>Yii::t('app', '开始ipV6')])?></div>
                            <div class="col-md-2" style="margin-top:10px;"><?=Html::input('text', 'user_ip6_end', isset($params['user_ip6_end'])?$params['user_ip6_end']:'', ['class'=>'form-control', 'placeHolder'=>Yii::t('app', '结束ipV6')])?></div>

                            <div class="col-md-2" style="margin-top:10px;"><select class="form-control" name="products_id">
                                    <option value="" <?= (!isset($params['products_id']) || empty($params['products_id'])) ? 'selected = "selected"' : ''?>><?= Yii::t('app', 'select product') ?></option>
                                    <?php
                                    foreach ($model->products as $id => $one) {
                                        $products[$id] = $one; //从产品ID得到名称用于列表中的产品名称显示
                                        ?>
                                        <option value="<?= $id ?>" <?= (isset($params['products_id']) && !empty($params['products_id']) && $params['products_id']==$id) ? 'selected = "selected"' : ''?>><?= $one ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group" ng-cloak ng-show="advanced==1">
                            <div class="col-md-2"><?=Yii::t('app', 'log detail help1')?></div>
                            <div class="col-md-10">
                                <?= Html::checkboxList('showField[]', $params['showField'], $model->searchField, ['class'=>'drag_inline']) ?>
                            </div>
                        </div>
                        <!--组织结构-->
                        <div class="form-group" ng-cloak ng-show="advanced==1">
                            <div class="col-md-2"><?=Yii::t('app', '请选择部门')?></div>
                            <div class="col-md-10">
                                <div class="panel panel-default">
                                    <div class="panel-body" style="max-height: 500px; overflow-y: auto;">
                                        <?= Html::hiddenInput("group_id", '', [
                                            'id' => 'zTreeId',
                                        ]) ?>
                                        <div><?= Yii::t('app', 'organization help5')?><span class="text-primary" id="zTreeSelect"></span></div>
                                        <div id="zTreeAddUser" class="ztree"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?= Html::submitButton(Yii::t('app', 'search'), ['class' => 'btn btn-success']) ?>
                        &nbsp;&nbsp;&nbsp;&nbsp;<label class="text-info"><input type="checkbox" ng-model="advanced" ng-init = "advanced=<?=$state?>" name="advanced" value="1"/><small><?= Yii::t('app', 'advanced')?></small></label>

                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <section class="panel panel-default table-dynamic">
                    <div style="float:right;margin-right:10px;margin-top:5px;">
                        <a type="button" class="btn btn-default btn-sm" href="<?=Yii::$app->urlManager->createUrl(array_merge(['log/detail/index'],$params,['action'=>'excel'])) ?>"><span
                                class="glyphicon glyphicon-log-out"></span>excel</a>
                    </div>
                    <div class="panel-heading"><strong><span class="glyphicon glyphicon-th-large"></span> <?=Yii::t('app', 'search result')?></strong></div>
                    <?php if (!empty($list)): ?>
                        <div style="overflow-x: auto;">
                            <table class="table table-bordered  table-responsive table-hover" style="border-top:0;">
                                <thead>
                                <tr>
                                    <?php
                                    if( isset($params['showField']) && $params['showField'] ){
                                        foreach( $params['showField'] as $value ){
                                            $newParams = $params;
                                            echo '<th nowrap="nowrap"><div class="th">';
                                            echo $model->searchField[$value];
                                            if($value !== 'vlan_id' && $value !== 'vlan_zone' && $value !== 'ip_zone'){
                                                $newParams['orderBy'] = $value;
                                                array_unshift($newParams, '/log/detail/index');

                                                //上面
                                                $newParams['sort'] = 'asc';
                                                $upActive = (isset($params['orderBy'])
                                                    && $params['orderBy'] == $value
                                                    && isset($params['sort'])
                                                    && $params['sort'] == 'asc') ? 'active' : '';
                                                echo Html::a('<span class="glyphicon glyphicon-chevron-up '.$upActive.'"></span>', $newParams);

                                                //下面
                                                $newParams['sort'] = 'desc';
                                                $downActive = (isset($params['orderBy'])
                                                    && $params['orderBy'] == $value
                                                    && isset($params['sort'])
                                                    && $params['sort'] == 'desc') ? 'active' : '';
                                                echo Html::a('<span class="glyphicon glyphicon-chevron-down '.$downActive.'"></span>', $newParams);
                                            }
                                            echo '</div></th>';
                                        }
                                    }

                                    ?>
                                </tr>
                                </thead>
                                <tbody>
                                <?php $i =0;foreach ($list as $one): ?>
                                    <tr bgcolor="<?php echo $i % 2 == 1 ? "#fff" : '#f1f1f1' ?>">
                                        <?php foreach ($params['showField'] as $value): ?>
                                            <td>
                                                <?php
                                                if( $value == 'user_name' ){
                                                    echo Html::a($one[$value], ['/user/base/view','user_name'=>$one[$value]]);
                                                } else {
                                                    //显示产品名称
                                                    if( $value == 'products_id' ){
                                                        $one['products_id'] = $one['products_id']. "." . (isset($products[$one['products_id']]) ? $products[$one['products_id']] : '');
                                                    }elseif( $value == 'billing_id' ){
                                                        $one['billing_id'] = $one['billing_id'].".". (isset($model->billings[$one['billing_id']]) ? $model->billings[$one['billing_id']] : '');
                                                    }elseif( $value == 'control_id' ){
                                                        $one['control_id'] = $one['control_id'].".". (isset($model->controls[$one['control_id']]) ? $model->controls[$one['control_id']] : '');
                                                    } else if($value == 'vlan_id'){
                                                        $one['vlan_id'] = (!empty($one['vlan_id']) ? $one['vlan_id']: '');
                                                        echo Html::encode($one[$value]);
                                                        if(!empty($one['vlan_id'])){
                                                            echo '&nbsp;&nbsp;'.Html::button(Yii::t('app', 'view'), ['class' => 'btn btn-primary btn-xs',
                                                                    'ng-model'=>'batchOperate'.$i.'', 'ng-click'=>'batchOperate'.$i.' = !batchOperate'.$i.'']);
                                                        }
                                                        continue;
                                                    } else if( $value == 'group_id' ){
                                                        //echo Html::encode($groups[$one[$value]]); //显示所在用户组
                                                        echo Html::encode(SrunJiegou::getOwnParent([$one[$value]])); //显示层级用户组
                                                    }else{
                                                        echo Html::encode($one[$value]);
                                                    }
                                                }
                                                ?>
                                            </td>
                                        <?php endforeach ?>
                                    </tr>
                                    <tr ng-show="batchOperate<?= $i;?>">
                                        <td colspan="<?= count($one)?>" style="line-height:22px;">
                                            <?php
                                            $locationMessage = \center\modules\strategy\models\Corresponding::findVlanMes($one['vlan_id']);
                                            if(!empty($locationMessage)){
                                                ?>
                                                设备管理ip：<?= $locationMessage['device_ip'];?><br />
                                                交换机接口：<?= $locationMessage['switch_port'];?><br />
                                                汇聚设备管理ip：<?= $locationMessage['cdevice_ip'];?><br />
                                                汇聚设备类型：<?= $locationMessage['cdevice_type'];?><br />
                                                配电间：<?= $locationMessage['power'];?><br />
                                                物理位置：<?= $locationMessage['locations'];?><br />
                                                设备安装位置：<?= $locationMessage['install'];?>
                                            <?php
                                            }else{
                                                echo Yii::t('app', 'no record');
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <?php $i++;endforeach ?>


                                </tbody>
                            </table>
                        </div>
                        <footer class="table-footer">
                            <div class="row">
                                <div class="col-md-5">

                                    <?php

                                    if(!empty($resArr)) {
                                        echo Yii::t('app', 'pagination show2', [
                                            'totalCount' => $pagination->totalCount,
                                            'totalPage' => $pagination->getPageCount(),
                                            'perPage' => $pagination->pageSize,
                                            'total_bytes' => Tool::bytes_format(array_sum(array_filter($resArr['total_bytes']))),
                                            'bytes_in' => Tool::bytes_format(array_sum(array_filter($resArr['bytes_in']))),
                                            'bytes_out' => Tool::bytes_format(array_sum(array_filter($resArr['bytes_out']))),
                                            'time_long' => Tool::seconds_format(array_sum(array_filter($resArr['time_long']))),
                                            'user_charge' => array_sum(array_filter($resArr['user_charge'])),
                                        ]);
                                    } else {
                                        //echo Yii::t('app', 'pagination show1', ['totalCount' => $pagination->totalCount,'totalPage' => $pagination->getPageCount(), 'perPage' => $pagination->pageSize]);
                                        echo Yii::t('app', 'pagination show page', [
                                            'totalCount' => $pagination->totalCount,
                                            'totalPage' => $pagination->getPageCount(),
                                            'perPage' => '<input type=text name=offset size=3 value='.$pagination->defaultPageSize.'>',
                                            'pageInput'=>'<input type=text name=page size=4>',
                                            'buttonGo'=>'<input type=submit value=go>',
                                        ]);
                                    }
                                    ?>
                                </div>
                                <div class="col-md-7 text-right">
                                    <?= SuperLinkPager::widget(['pagination' => $pagination, 'maxButtonCount'=>8]); ?>
                                </div>
                            </div>

                        </footer>

                    <?php else: ?>

                        <div class="panel-body">
                            <?=Yii::t('app', 'no record')?>
                        </div>

                    <?php endif ?>
                </section>
            </div>
        </div>
    </form>
</div>


