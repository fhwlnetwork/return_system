<?php
use yii\widgets\LinkPager;
use yii\helpers\Html;
use center\widgets\Alert;
use center\extend\Tool;
use center\modules\auth\models\SrunJiegou;
use center\assets\ZTreeAsset;
use common\models\Redis;

$this->title = Yii::t('app', 'User Online');
//$panel = Yii::$app->request;
//是否调试模式
if(in_array($params['panel'], ['portal', 'dhcp'])){
    $debug = 1;
}else{
    $debug = 0;
}

//权限
$canRadius = Yii::$app->user->can('user/online/_radius');
$canProxy = Yii::$app->user->can('user/online/_proxy');
$canDhcp = Yii::$app->user->can('user/online/_dhcp');
$canPortal = Yii::$app->user->can('user/online/_portal');
$canDrop = Yii::$app->user->can('user/online/drop');
$canExport = Yii::$app->user->can('user/online/download');
$canIps = Yii::$app->user->can('user/online/ips');

//ztree 搜索用
ZTreeAsset::register($this);
ZTreeAsset::addZtreeSelectMulti($this);

/*if(!$canRadius && !$canProxy && !$canDhcp && !$canPortal){
    exit('forbid');
}*/
?>

<style type="text/css">
    .ztree li a.curSelectedNode span {
        background-color: #0088cc;
        color: #fff;
        border-radius: 2px;
        padding: 2px;
    }
</style>

<div class="page page-table">
    <?= Alert::widget() ?>
    <section class="panel panel-default table-dynamic">
        <div class="panel-body">
            <ul class="nav nav-tabs" id="tab">
                <?php if($canRadius): ?>
                <li <?php if($params['panel'] == 'radius'): ?> class="active" <?php endif?>>
                    <?= Html::a(Yii::t('app', 'online type1'), ['index', 'panel'=>'radius'])?></li>
                <?php endif ?>

                <?php if($canProxy): ?>
                <li <?php if($params['panel'] == 'proxy'): ?> class="active" <?php endif?>>
                    <?= Html::a(Yii::t('app', 'online type2'), ['index', 'panel'=>'proxy'])?></li>
                <?php endif ?>

                <?php if($canDhcp): ?>
                <li <?php if($params['panel'] == 'dhcp'): ?> class="active" <?php endif?> ng-cloak ng-show="debug==1">
                    <?= Html::a(Yii::t('app', 'online type3'), ['index', 'panel'=>'dhcp'])?></li>
                <?php endif ?>

                <?php if($canPortal): ?>
                <li <?php if($params['panel'] == 'portal'): ?> class="active" <?php endif?> ng-cloak ng-show="debug==1">
                    <?= Html::a(Yii::t('app', 'online type4'), ['index', 'panel'=>'portal'])?></li>
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
                                <form name="form_constraints" action="<?=\yii\helpers\Url::to(['index'])?>"
                                      class="form-horizontal form-validation" method="get">
                                    <?php
                                        echo Html::input('hidden', 'panel', $params['panel']);
                                        if($model->searchInput){
                                            $searchInput = $model->searchInput;
                                            $count = count($searchInput);
                                            $i = 0;
                                            foreach ($model->searchInput as $key => $value) {
                                                if($i%6==0){
                                                    echo '<div class="form-group">';
                                                }
                                                if($key == "group_id"){ //用户组显示在下面，这里不显示
                                                	continue;
                                                }
                                                else if($key == "products_id"){ //产品列表
                                                	$content = Html::dropDownList($key, 
                                                											isset($params[$key]) ? $params[$key] : '', 
                                                											[''=>Yii::t('app','select product')]+$products,
                                                											['class' =>'form-control']
                                                			);
                                                	
                                                }
                                                else{
                                                $content = Html::input('text', $key, isset($params[$key]) ? $params[$key] : '', [
                                                    'class' => (isset($value['type']) && $value['type']=='dateTime') ? 'form-control inputDateTime' : 'form-control',
                                                    'placeHolder' => isset($value['label']) ? $value['label'] : '',
                                                ]);
                                                }
                                                echo Html::tag('div', $content, ['class' => 'col-md-2']);

                                                $i++;
                                                if($i%6==0 || $i==$count){
                                                    echo '</div>';
                                                }
                                            }
                                            if($i%count($model->searchInput) != 0){//如果不是正好满行，最后加一个</div>
                                                echo  '</div>';
                                            }
                                        }
                                    ?>
                                    <?php if(!empty($model->searchField)):?>
                                    <div class="form-group" ng-cloak ng-show="advanced==1">
                                        <div class="col-md-2"><?=Yii::t('app', 'user online help1')?></div>
                                        <div class="col-md-10">
                                            <?= Html::checkboxList('showField[]', $params['showField'], $model->searchField, [
                                                'class'=>'drag_inline'
                                            ]) ?>
                                        </div>
                                    </div>
                                    <?php endif ?>
                                    
				                                <!--组织结构-->
				                        <div class="form-group" ng-cloak ng-show="advanced==1">
				                            <div class="col-md-2"><?=Yii::t('app', 'organization help4')?></div>
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
                                 <?php if(!empty($params) && $params['panel'] == 'radius'):?>
                                    &nbsp;&nbsp;&nbsp;&nbsp;<label class="text-info"><input type="checkbox" name="vague_tag" value='1' <?php if(isset($params['vague_tag']) && $params['vague_tag'] == 1):?>checked<?php endif;?>/><small><?= Yii::t('app', 'search vague')?></small></label>  &nbsp;&nbsp;&nbsp;&nbsp;<label class="text-info"><input type="checkbox" ng-model="advanced" /><small><?= Yii::t('app', 'advanced')?></small></label>
                                   <?php endif;?>
                                    <?php if(false && ($canDhcp || $canPortal)): ?>
                                    &nbsp;&nbsp;&nbsp;&nbsp;<label class="text-info" data-ng-init="debug=<?=$debug?>"><input type="checkbox" ng-model="debug" ng-checked="debug==1" /><small><?= Yii::t('app', 'user online font1')?></small></label>
                                    <?php endif ?>


                            </div>
                        </div>
                    </div>

                    <!--搜索结果start-->
                    <div class="col-md-12">
                        <section class="panel panel-default table-dynamic">
                            <div class="panel-heading"><strong><span class="glyphicon glyphicon-th-large"></span> <?=Yii::t('app', 'search result')?></strong>
                                <?php if($canExport):?>
                                    <a type="button" class="pull-right" href="<?=Yii::$app->urlManager->createUrl(array_merge(['user/online/index'],$params,['export'=>'true'])) ?>"><span
                                            class="glyphicon glyphicon-log-out"></span><?=Yii::t('app', 'export')?></a>
                                <?php endif;?>
                            </div>

                            <!-- 如果 $list 有数据，则遍历数据 -->
                            <?php if (!empty($list)): ?>
                                <?php
                                //需要转换时间格式的字段
                                $timeArr = ['add_time', 'drop_time', 'update_time', 'keepalive_time'];
                                //需要转换格式的流量信息
                                $bytesArr = ['bytes_in', 'bytes_out', 'bytes_in6', 'bytes_out6', 'sum_bytes', 'remain_bytes'];
                                //时长信息
                                $timeLong = ['sum_seconds', 'remain_seconds'];
                                //金额
                                $moneyArr = ['user_balance', 'user_charge'];
                                //其他从redis中获取的数据
                                $otherItems = ['rad_online_id', 'session_id', 'domain', 'uid', 'ip6', 'nas_ip1', 'nas_port',
                                    'nas_port_id', 'station_id', 'filter_id', 'pbhk', 'vlan_id1', 'vlan_id2', 'line_type','os_name',
                                    'class_name', 'client_type',
                                ];
                                //$otherItems = ['os_name', 'class_name', 'client_type'];
                                //所有需要从redis表中获取的字段数组
                                $needRedisArr = array_merge($bytesArr, $timeLong, $moneyArr, $otherItems);
                                //需要去对应查找名称的字段
                                $nameArr = ['products_id', 'billing_id', 'control_id'];
                                ?>
                                <div style="overflow-x: auto;">
                                    <table class="table table-bordered  table-responsive table-hover" >
                                        <thead>
                                            <tr>
                                                <?php
                                                if( isset($params['showField']) && $params['showField'] ){
                                                    foreach( $params['showField'] as $value ){
                                                        //从redis中获取数据的不显示上下排序箭头 代理在线表因为从REDIS中直读，也不能排序
                                                        if(in_array($value, $needRedisArr) || $params["panel"]=="proxy" || $value == 'vlan_zone' || $value == 'vlan_id' || $value == 'ip_zone'){
                                                            echo '<th nowrap="nowrap"><div class="th">'.$model->searchField[$value].'</div></th>';
                                                        }
                                                        //显示排序箭头
                                                        else{
                                                            $newParams = $params;
                                                            echo '<th nowrap="nowrap"><div class="th">';
                                                            echo $model->searchField[$value];
                                                            $newParams['orderBy'] = $value;
                                                            array_unshift($newParams, '/user/online/index');

                                                            //上箭头
                                                            $newParams['sort'] = 'asc';
                                                            $upActive = (isset($params['orderBy'])
                                                                && $params['orderBy'] == $value
                                                                && isset($params['sort'])
                                                                && $params['sort'] == 'asc') ? 'active' : '';
                                                            echo Html::a('<span class="glyphicon glyphicon-chevron-up '.$upActive.'"></span>', $newParams);

                                                            //下箭头
                                                            $newParams['sort'] = 'desc';
                                                            $downActive = (isset($params['orderBy'])
                                                                && $params['orderBy'] == $value
                                                                && isset($params['sort'])
                                                                && $params['sort'] == 'desc') ? 'active' : '';
                                                            echo Html::a('<span class="glyphicon glyphicon-chevron-down '.$downActive.'"></span>', $newParams);

                                                            echo '</div></th>';
                                                        }
                                                    }
                                                }

                                                ?>
                                                <?php if($canDrop): ?>
                                                <th nowrap="nowrap"><div class="th"><?=Yii::t('app', 'operate')?></div></th>
                                                <?php endif ?>
                                            </tr>
                                        </thead>

                                        <tbody>
                                        <?php $i=0;foreach ($list as $one): ?>
										<?php
										//2015-07-11改为全部数据从REDIS中获取
										$redisData = $model->getValueInRedis($one["rad_online_id"]);
                                        if(empty($redisData['user_name'])){
                                            continue;
                                        }
										?>
                                            <tr bgcolor="<?php echo $i % 2 == 1 ? "#fff" : '#f1f1f1' ?>">
                                                <!--遍历数据-->
                                                <?php foreach ($params['showField'] as $value): ?>
                                                    <td>
													<?php											
													if(empty($redisData)){
														echo '';
														continue;
													}
													
													if( $value == 'user_name' ){
													echo Html::a($redisData[$value], ['/user/base/view','user_name'=>$redisData[$value]]);
													}
													//解析时间戳
													else if(in_array($value, $timeArr)){
														echo Yii::$app->formatter->asDatetime($redisData[$value], 'php:Y-m-d H:i:s');
													}
													//特殊流量处理
													//入流量
													else if($value == 'bytes_in'){
														echo Tool::bytes_format($redisData['bytes_in'] - $redisData['bytes_in1']);
													}
													//出流量
													else if($value == 'bytes_out'){
														echo Tool::bytes_format($redisData['bytes_out'] - $redisData['bytes_out1']);
													}
													//流量信息
													else if(in_array($value, $bytesArr)){
														echo Tool::bytes_format($redisData[$value]);
													}
													//时长信息
													else if(in_array($value, $timeLong)){
														echo Tool::seconds_format($redisData[$value]);
													}
													//金额信息
													else if(in_array($value, $moneyArr)){
														echo number_format($redisData[$value], 2);
													}
													//其他信息
													else if(in_array($value, $otherItems)){
														echo isset($redisData[$value]) ? $redisData[$value] : '' ;
													}
                                                    //显示区域
                                                    else if($value == 'vlan_zone'){
                                                        echo $one['vlan_zone'];
                                                    }
                                                    //显示ip区域
                                                    else if($value == 'ip_zone'){
                                                        echo $one['ip_zone'];
                                                    }
                                                    //显示vlan区域
                                                    else if ($value ==  'vlan_id') {
                                                          echo Html::encode($one[$value]);
                                                         if(!empty($one['vlan_id'])){
                                                            echo '&nbsp;&nbsp;'.Html::button(Yii::t('app', 'view'), ['class' => 'btn btn-primary btn-xs',
                                                                    'ng-model'=>'batchOperate'.$i.'', 'ng-click'=>'batchOperate'.$i.' = !batchOperate'.$i.'']);
                                                        }
                                                    }
													//解析产品，计费组，控制组
													else if ( in_array($value, $nameArr)){
														echo $redisData[$value].':'.$model->getNameInRedis($value, $redisData[$value]);
                                                    } else if($value == 'group_id'){
                                                        $group_id = Redis::executeCommand('hget', 'hash:users:'.$redisData['uid'], ['group_id']);
                                                        echo $group_id ? Html::encode(SrunJiegou::getOwnParent([$group_id])) : '--'; //显示层级用户组
													} else {
														echo Html::encode($redisData[$value]);
													}
                                                    ?>
                                                    </td>
                                                <?php endforeach; ?>

                                                <?php if($canDrop): ?>
                                                    <!--下线按钮start-->
                                                    <td>
                                                        <?=Html::a(Yii::t('app', 'off line'), ['drop', 'panel' => $params['panel'], 'id'=>$one["rad_online_id"]], [
                                                            'class' => 'btn btn-danger btn-xs',
                                                            'data' => [
                                                                'confirm' => Yii::t('app', 'user online help3'),
                                                                'method' => 'post',
                                                            ],
                                                        ])?>
                                                    </td>
                                                    <!--下线按钮stop-->
                                                <?php endif ?>
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

                                <!--分页和页面信息start-->
                                <footer class="table-footer">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <?=Yii::t('app', 'pagination show page', [
                                             'totalCount' => $pagination->totalCount,
                                             'totalPage' => $pagination->getPageCount(),
                                             'perPage' => '<input type=text name=offset size=3 value='.$pagination->defaultPageSize.'>',
                                             'pageInput'=>'<input type=text name=page size=4>',
                                             'buttonGo'=>'<input type=submit value=go>',
                                            ])?>
                                        </div>
                                        <div class="col-md-6 text-right">
                                            <?= LinkPager::widget(['pagination' => $pagination, 'maxButtonCount' => 5]); ?>
                                        </div>
                                    </div>
                                </footer>
                              </form>
                                <!--分页和页面信息stop-->

                            <?php else: ?>
                                <div class="panel-body">
                                    <?=Yii::t('app', 'no record')?>
                                </div>
                            <?php endif ?>
                        </section>
                    </div>
                    <!--搜索结果stop-->
                </div>
            </div>
        </div>
    </section>
</div>
<?php
$this->registerJs("
    createTree('zTreeAddUser');
");
?>
