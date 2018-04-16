<?php
use yii\widgets\LinkPager;
use yii\helpers\Html;
use center\widgets\Alert;
use center\modules\auth\models\SrunJiegou;
use center\assets\ZTreeAsset;

$this->title = \Yii::t('app', 'User List');
$attributes = $model->getAttributesList();


//权限
$canView = Yii::$app->user->can('user/base/view');
$canEdit = Yii::$app->user->can('user/base/edit');
$canPay = Yii::$app->user->can('financial/pay/index');
$canDelete = Yii::$app->user->can('user/base/delete');
$canBatchRenew = Yii::$app->user->can('user/group/batch-renew');
$canBatchDelete = Yii::$app->user->can('user/group/batch-delete');
$canBatchEnable = Yii::$app->user->can('user/group/batch-enable');
$canBatchDisable = Yii::$app->user->can('user/group/batch-disable');
$canBatchBuy = Yii::$app->user->can('user/group/batch-buy');
$canBatchMacOpen = Yii::$app->user->can('user/group/batch-auth-open');
$canBatchMacClose = Yii::$app->user->can('user/group/batch-auth-close');
$canBatchClear = Yii::$app->user->can('user/base/_batchClear');
$canBachOperate = Yii::$app->user->can('user/base/batch-operate');

//ztree 搜索用
ZTreeAsset::register($this);
ZTreeAsset::addZtreeSelectMulti($this);

?>
<style type="text/css" xmlns="http://www.w3.org/1999/html">
    .ztree li a.curSelectedNode span {
        background-color: #0088cc;
        color: #fff;
        border-radius: 2px;
        padding: 2px;
    }
</style>
<div class="page">
    <?= Alert::widget() ?>
    <form name="form_constraints" action="<?=\yii\helpers\Url::to(['index'])?>" class="form-horizontal form-validation" method="get" onsubmit="return checkPerPage();">
    <div class="row" >
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-body">

                        <?php
                        if($model->searchInput){
                            $searchInput = $model->searchInput;
                            $count = count($searchInput);
                            $i = 0;
                            foreach ($model->searchInput as $key => $value) {
                                if($i%6==0){
                                    echo '<div class="form-group">';
                                }
                                //列表形式
                                if( isset($value['list']) && !empty($value['list']) ){
                                    $content = Html::dropDownList($key, isset($params[$key]) ? $params[$key] : '', $value['list'], ['class' =>'form-control' ]);
                                }
                                //普通文本格式
                                else{
                                    $content = Html::input('text', $key, isset($params[$key]) ? $params[$key] : '', [
                                        'class' => 'form-control'.(isset($value['class']) ? $value['class'] : ''),
                                        'placeHolder' => isset($value['label']) ? $value['label'] : '',
                                        'id' => isset($value['id']) ? $value['id'] : '',
                                    ]);
                                }
                                
                                echo Html::tag('div', $content, ['class' => 'col-md-2']);

                                $i++;
                                if($i%6==0 || $i==$count){
                                    echo '</div>';
                                }
                            }
                        }
                        ?>

                        <div class="form-group" ng-cloak ng-show="advanced==1">
                                <div class="col-md-2"><?=Yii::t('app', 'user base help2')?></div>
                                <div class="col-md-10">
                                    <?= Html::checkboxList('showField[]', $params['showField'], $model->searchField, ['class'=>'drag_inline']) ?>
                                </div>
                        </div>
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
                        &nbsp;&nbsp;&nbsp;&nbsp;<label class="text-info"><input type="checkbox" name="exact_tag" value='1'/><small><?= Yii::t('app', 'search exact')?></small></label>
                        &nbsp;&nbsp;&nbsp;&nbsp;<label class="text-info"><input type="checkbox" ng-model="advanced" name="advanced" value="1"/><small><?= Yii::t('app', 'advanced')?></small></label>
                        &nbsp;&nbsp;&nbsp;&nbsp;<label><small><i class="glyphicon glyphicon-volume-up"></i><?= Yii::t('app', 'base_search_help1')?></small></label>


                </div>
            </div>
        </div>
        <div class="col-md-12">
            <section class="panel panel-default table-dynamic">
                <div class="panel-heading"><strong><span class="glyphicon glyphicon-th-large"></span> <?=Yii::t('app', 'search result')?></strong>
                    <div class="pull-right" style="margin-top:-5px;">
                        <a type="button" class="btn btn-primary btn-sm"
                           href="<?= Yii::$app->urlManager->createUrl(array_merge(['user/base/index'], $params, ['export' => 'excel'])) ?>"><span
                                class="glyphicon glyphicon-log-out"></span><?= Yii::t('app', 'excel export') ?></a>
                        <a type="button" class="btn btn-info btn-sm"
                           href="<?= Yii::$app->urlManager->createUrl(array_merge(['user/base/index'], $params, ['export' => 'csv'])) ?>"><span
                                class="glyphicon glyphicon-log-out"></span><?= Yii::t('app', 'csv export') ?></a>
                    </div>
                </div>

                <div style="clear:both;"></div>

                <?php if (!empty($list)): ?>
                    <div style="overflow-x: auto;">
                        <table class="table table-bordered table-striped table-responsive">
                            <thead>
                            <tr>
                                <?php
                                if( isset($params['showField']) && $params['showField'] ){
                                    //不需要排序的字段
                                    $no_sort_field = ['group_id', 'products_id', 'user_available', 'user_balance', 'user_name', 'user_real_name', 'user_online_status'];
                                    echo '<td><input type="checkbox" id="all"/></td>';
                                    foreach( $params['showField'] as $value ){
                                        if(in_array($value, $no_sort_field)){
                                            echo '<th nowrap="nowrap"><div class="th">'.$model->searchField[$value].'</div></th>';
                                        }
                                        else{
                                            $newParams = $params;
                                            echo '<th nowrap="nowrap"><div class="th">';
                                            echo $model->searchField[$value];

                                            $newParams['orderBy'] = $value;
                                            array_unshift($newParams, '/user/base/index');

                                            //上面按钮
                                            $newParams['sort'] = 'asc';
                                            $upActive = (isset($params['orderBy'])
                                                && $params['orderBy'] == $value
                                                && isset($params['sort'])
                                                && $params['sort'] == 'asc') ? 'active' : '';
                                            echo Html::a('<span class="glyphicon glyphicon-chevron-up '.$upActive.'"></span>', $newParams);

                                            //下面按钮
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
                                <?php if($canView || $canEdit || $canDelete):?>
                                    <th nowrap="nowrap"><div class="th"><?=Yii::t('app', 'operate')?></div></th>
                                <?php endif ?>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($list as $one): ?>
                                <tr>
                                    <td><input type="checkbox" name="id[]" value="<?php echo $one['user_id']?>" /></td>
                                    <?php foreach ($params['showField'] as $value): ?>
                                        <td>
                                            <?php
                                                //用户名
                                                if( $value == 'user_name' ){
                                                    echo Html::a($one[$value], ['view','user_name'=>$one[$value]]);
                                                }
                                                //用户组
                                                else if( $value == 'group_id' ){
                                                    //echo Html::encode($groups[$one[$value]]); //显示所在用户组
                                                    echo Html::encode(SrunJiegou::getOwnParent([$one[$value]])); //显示层级用户组
                                                }
                                                //产品
                                                else if($value == 'products_id'){
                                                    echo isset($one['products_id']) ? implode('<br>', $one['products_id']) : '';
                                                }
                                                else if($value == 'user_balance'){
                                                    echo isset($one['user_balance']) ? implode('<br>', $one['user_balance']) : '';
                                                }
                                                //状态
                                                else if($value == 'user_available' && isset($one['user_available'])){
                                                    $available_css = $one['user_available'] == 0 ? 'label-success' : 'label-danger';
                                                    echo '<span class="label '.$available_css.' label-xs">'.$attributes['user_available'][$one['user_available']].'</span>';
                                                }
                                                //在线状态
                                                else if($value == 'user_online_status' && isset($one['user_online_status'])){
                                                    $available_css = $one['user_online_status'] == 1 ? 'btn-success' : 'btn-xs';
                                                    echo '<button class="btn '.$available_css.' btn-xs">'.$attributes['user_online_status'][$one['user_online_status']].'</button>';
                                                }
                                                //过期时间
                                                else if($value == 'user_expire_time'){
                                                    echo $one['user_expire_time'] == 0 ? Yii::t('app', 'user expire time2') : date('Y-m-d H:i', $one['user_expire_time']);
                                                }
                                                //时间格式的字段
                                                else if(in_array($value, ['user_create_time', 'user_update_time'])){
                                                    echo date('Y-m-d H:i:s', $one[$value]);
                                                }
                                                //列表形式的字段
                                                else if( isset($attributes[$value]) && !empty($attributes[$value]) ){
                                                    if($one[$value] !== '' || !is_null($one[$value])){
                                                        echo Html::encode($attributes[$value][$one[$value]]);
                                                    }
                                                }
                                                else{
                                                    echo Html::encode($one[$value]);
                                                }
                                            ?>
                                        </td>
                                    <?php endforeach ?>
                                    <?php if($canView || $canEdit || $canDelete): ?>
                                    <td>
                                        <?php if($canView) echo Html::a(Html::button(Yii::t('app', 'view'), ['class'=>'btn btn-success btn-xs']), ['view', 'user_name'=>$one['user_name']], ['title'=>Yii::t('app', 'view')])?>
                                        <?php if($canEdit) echo Html::a(Html::button(Yii::t('app', 'edit'), ['class'=>'btn btn-warning btn-xs']), ['edit', 'id'=>$one['user_id']], ['title'=>Yii::t('app', 'edit')])?>
                                        <?php if($canPay) echo Html::a(Html::button(Yii::t('app', 'Financial Payment'), ['class'=>'btn btn-info btn-xs']), ['/financial/pay/index', 'action'=>'product', 'user_name'=>$one['user_name']], ['title'=>Yii::t('app', 'edit')])?>
                                        <?php if($canDelete) echo Html::a(Html::button(Yii::t('app', 'User Delete'), ['class'=>'btn btn-danger btn-xs']), ['delete', 'id' => $one['user_id']], [
                                            'title' => Yii::t('app', 'User Delete'),
                                            'data' => [
                                                'method' => 'post',
                                                'confirm' => Yii::t('app', 'user base help5'),
                                            ],
                                        ]) ?>
                                    </td>
                                    <?php endif ?>
                                </tr>
                            <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                       <?php if($canBachOperate ): ?>
                            <select name="operate" id="operate"  class="form-control" style="width: 200px;display: inline;margin-left:20px;margin-top:5px;">
                                <option value=""><?=Yii::t('app', 'batch operate')?></option>
                                <?php if($canBatchDelete) echo '<option value="batchdel">'.Yii::t('app', 'batch delete').'</option>';?>
                                <?php if($canBatchBuy) echo  ' <option value="batchbuy">'.Yii::t('app', 'batch buy').'</option>';?>
                                <?php if($canBatchDisable) echo '<option value="batchdisable">'.Yii::t('app', 'batch disable').'</option>';?>
                                <?php if($canBatchEnable) echo ' <option value="batchenable">'.Yii::t('app', 'batch enable').'</option>';?>
                                <?php if($canBatchRenew) echo ' <option value="batcherenew">'.Yii::t('app', 'batch renew').'</option>';?>
                                <?php if($canBatchMacOpen) echo ' <option value="batchMacOpen">'.Yii::t('app', 'batch mac auth open').'</option>';?>
                                <?php if($canBatchMacClose) echo ' <option value="batcheMacClose">'.Yii::t('app', 'batch mac auth close').'</option>';?>
                                <?php if($canBatchClear) echo ' <option value="batchClearMacAuths">'.Yii::t('app', 'batch clear bind', ['bindType' => Yii::t('app', 'mac_auths')]).'</option>';?>
                                <?php if($canBatchClear) echo ' <option value="batchClearMacs">'.Yii::t('app', 'batch clear bind', ['bindType' => Yii::t('app', 'macs')]).'</option>';?>
                                <?php if($canBatchClear) echo ' <option value="batchClearNasPortIds">'.Yii::t('app', 'batch clear bind', ['bindType' => Yii::t('app', 'nas_port_ids')]).'</option>';?>
                                <?php if($canBatchClear) echo ' <option value="batchClearVlanIds">'.Yii::t('app', 'batch clear bind', ['bindType' => Yii::t('app', 'vlan_ids')]).'</option>';?>
                                <?php if($canBatchClear) echo ' <option value="batchClearIPV4s">'.Yii::t('app', 'batch clear bind', ['bindType' => Yii::t('app', 'ips')]).'</option>';?>
                            </select>
                        <div id="test" style="margin-left:20px;display: none"></div>
                        <span type="button" id="batch" class="btn btn-primary"  onclick="batch()" style="margin-left:20px;margin-top:5px;margin-bottom: 5px;"><?=Yii::t('app', 'confirm')?></span>
                    <?php endif;?>

                   <div class="divider"></div>
                    <footer class="table-footer">
                        <div class="row">
                            <div class="col-md-6">
                                <?php
                                echo Yii::t('app', 'pagination show page', [
                                    'totalCount' => $pagination->totalCount,
                                    'totalPage' => $pagination->getPageCount(),
                                    'perPage' => '<input type=text name=offset size=3 value='.$params['offset'].'>',
                                    'pageInput'=>'<input type=text name=page size=4>',
                                    'buttonGo'=>'<input type=submit value=go>',
                                ]);
                                ?>
                            </div>
                            <div class="col-md-6 text-right">
                                <?= LinkPager::widget(['pagination' => $pagination, 'maxButtonCount' => 5]); ?>
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
<script>

    var all = document.getElementById('all');
    var ids = document.getElementsByName('id[]');
    all.onclick=function(){
        var stats = this.checked;
        for(var i=0;i<ids.length;i++){
            ids[i].checked = stats;
        }
    }
    var operate = document.getElementById('operate');
    function batch(){
        var index=operate.selectedIndex ;
        var action = operate.options[index].value;
        var pay_type_id='';
        var product='';
        var renew_num = '';
        var buy_num = [];
        var package_num = [];
        var extendFields = [];
        var extendValues = [];

        var ids = getSelections();
        //console.log(ids);

        if (ids == '') {
            alert("<?=Yii::t('app', 'user base help34')?>");
            return ;
        }
        if (!action) {
            alert("<?=Yii::t('app', 'user base help36')?>");
            return ;
        }
        if (action == 'batcherenew') {
            pay_type_id = document.getElementById('pay_type_id').value;
            if(pay_type_id == 0){
                alert("<?=Yii::t('app', 'select pay mothod')?>");
                return ;
            }
            product = document.getElementById('product').value;
            if (product == 0) {
                alert("<?=Yii::t('app', 'user products id select')?>");
                return ;
            }
            var reg = /\d+(\.\d+)?/;
            renew_num = document.getElementById('renew_num').value;
            if (!reg.test(renew_num) || renew_num == '') {
                alert("<?=Yii::t('app', 'renew msg5')?>");
                return ;
            }
            var extendObj = document.getElementsByName("extends[]");

            for (var i= 0,len=extendObj.length; i<len; i++) {
                extendFields.push(extendObj[i].value);
                extendValues.push(document.getElementById(extendObj[i].value).value);
            }
            console.log(extendFields);
        } else if(action == 'batchbuy'){
            pay_type_id = document.getElementById('pay_type_id').value;
            if(pay_type_id == 0){
                alert("<?=Yii::t('app', 'select pay mothod')?>");
                return ;
            }
            product = document.getElementById('product').value;
            if (product == 0) {
                alert("<?=Yii::t('app', 'user products id select')?>");
                return ;
            }
            var buys = document.getElementsByName('buyPackage[item][]');
            var packages = document.getElementsByName('buyPackage[product_num][]');
            for (var i= 0, len= buys.length; i < len; i++) {
                if (buys[i].checked) {
                    buy_num.push(buys[i].value);
                    package_num.push(packages[i].value)
                }
            }
            if (!buy_num) {
                alert("<?=Yii::t('app', 'renew msg4')?>");
                return ;
            }
        }

        if(window.confirm("<?=Yii::t('app', 'user base help35')?>")){
            $.ajax({
                type:"POST",
                url:"<?=\yii\helpers\Url::to(['batch-operate'])?>",
                data:{'user_id':ids,'action':action, 'pay_type_id':pay_type_id, 'products_id':product, 'renew_num': renew_num,  'extend_fields': extendFields,'extend_values': extendValues, 'buy_num': buy_num, 'packages_num': package_num },
                dataType:'json',
                success:function (){
                        window.location.reload();//刷新当前页面.
                }
            });
        }
    }
    operate.onchange = function(){
        var index=operate.selectedIndex ;
        var action = operate.options[index].value;
        var buy = /buy/;
        var renew = /renew/;
        var str = '';
       if (buy.test(action)) {
           //批量购买
           test.style.display = 'inline';
           //缴费方式
           str +='<select id="pay_type_id" class="form-control" style="width:200px;margin-top:10px;display:inline;">';
           <?php if($pay_types):foreach ($pay_types as $id => $one):?>
           str += '<option value="<?=$id;?>" <?php if($id==$default_type){echo "selected";}?>><?=Html::encode($one);?></option>';
           <?php endforeach;endif;?>
           str += '</select>';
           str += '<span style="margin-left:20px;">';
           //增加产品
            str += '<select id ="product" class="form-control" style="width:200px;margin-top:10px;display: inline; ">'
            <?php if($products):foreach ($products as $key => $product):?>
                str += '<option value="<?=$key;?>"><?=Html::encode($product);?></option>';
           <?php endforeach;endif;?>

           str += '</select>';

        //   console.log(str);
           <?php $i=0;if($packages):foreach ($packages as $key => $packageOne):?>
            <?php if ($i % 4 == 0):$j = $i /4;?>
           str +="<div class='row' style='width:80%;margin:10px;'>";

           <?php endif;?>
           str +=   '<div class="col-md-3"><input  onchange = "count(<?=$packageOne['package_id']?>, <?=$packageOne['amount']?>)" type="checkbox" name="buyPackage[item][]" id="pid_<?=$packageOne['package_id']?>" value="<?=$packageOne['package_id']?>">'+"<?= Html::encode($packageOne['package_name'])?>";
           str += '<input type="number" name="buyPackage[product_num][]" id="package_num<?=$packageOne['package_id']?>" value="1" style="display:none" onblur="count(<?=$packageOne['package_id']?>, <?=$packageOne['amount']?>,this)" min="1" max="15"/>';

           str += '<input type="hidden" name="amount[]" value="<?=$packageOne['amount']?>">';
           str += '<input type="hidden" id="p_amount<?=$packageOne['package_id']?>" name="p_amount[]" value="">';
           str +=  "</div>";
           <?php if ($i == ($j+1)*4-1):?>
           str +="</div>";
           <?php endif;?>


           <?php $i++;endforeach;endif;?>
           str += "</div><p class='row' style='width:80%;margin:10px 30px;' class='text-muted'><small><?=Yii::t('app', 'renew msg3')?><label id='buyPackageTotal'>0</label><?=Yii::t('app', 'currency')?></small></p>";
           document.getElementById('test').innerHTML = str;
        } else if(renew.test(action)) {
           test.style.display = 'inline';

           //缴费方式
           str +='<select id="pay_type_id" class="form-control" style="width:200px;margin-top:10px;display:inline;">';
           <?php if($pay_types):foreach ($pay_types as $id => $one):?>
           str += '<option value="<?=$id?>" <?php if($id==$default_type){echo "selected";}?>><?=$one;?></option>';
           <?php endforeach;endif;?>
           str += '</select>';

           str += '<span style="margin-left:20px;">';
           //批量续费
           str += '<select id ="product" class="form-control" style="width:200px;margin-top:10px;display: inline; ">'
           <?php if($products):foreach ($products as $key => $product):?>
           str += '<option value="<?=$key;?>"><?=$product;?></option>';
           <?php endforeach;endif;?>

           str += '</select>';
           str += '<span style="margin-left:20px;">';
           str += '<input type="number" class="form-control"  placeholder="<?=Yii::t('app', 'batch renew amount');?>" name="batchRenewAmount" id="renew_num"  style="width:200px;margin-top:10px;display: inline; ">';
           str += '</span>';
           str += '<span style="margin-left:20px;">';
           <?php if(!empty($extendFields)):?>
               <?php $i=0;foreach ($extendFields as $one):?>
                  <?php  if ($i>2 && $i<6):?>
                         str += '<span style="margin-left:20px;"></span>';
                  <?php endif;?>

           str += '<input type="text" class="form-control" name="<?=$one['field_name']?>" placeholder="<?=$one['field_hint']?$one['field_hint']:$one['field_desc']?>" id="<?=$one['field_name']?>"  style="width:200px;margin-top:10px;display: inline;margin-right:10px; ">';
           str += '<input type="hidden" name="extends[]" value="<?=$one['field_name']?>">';
              <?php $i++;endforeach;?>

           <?php endif;?>
           str += '</span>';

           document.getElementById('test').innerHTML = str;

        } else {
           test.style.display = 'none';
           document.getElementById('test').innerHTML = str;
       }
    }
    /**
     *列表得到选中的id
     */
    function getSelections()
    {
        var ids = [];
        var checkBoxes = document.getElementsByName('id[]');
        for (var i= 0, len= checkBoxes.length; i < len; i++) {
            var status = checkBoxes[i].checked;
            if (status) {//选中了
               ids.push(checkBoxes[i].value)
            }
        }

      return ids;
    }
    function count(pid, amount, num){
        var sta = typeof (num);
        if (sta.toLowerCase() == 'object') {
            num = num.value;
        } else {
            num = document.getElementById('package_num'+pid).value;
        }

        var buys = document.getElementsByName('buyPackage[item][]');
        if (document.getElementById('pid_'+pid).checked)
        {
            document.getElementById('package_num'+pid).style.display = 'inline';
            document.getElementById('package_num'+pid).style.width = '80px';
            document.getElementById('package_num'+pid).style.marginLeft = '10px';
            document.getElementById('p_amount'+pid).value = amount*num;
        } else {
            document.getElementById('package_num'+pid).style.display = 'none';

        }

        var amounts = document.getElementsByName('amount[]');
        var pAmounts = document.getElementsByName('p_amount[]')
        var count = 0;
        for (var i= 0, len= buys.length; i < len; i++) {
            if (buys[i].checked) {
                count  += parseFloat(pAmounts[i].value) ;
            }
        }
       
        document.getElementById('buyPackageTotal').innerHTML = count;
    }
    function checkPerPage(){
        var offset = $("input[name*='offset']").val();
        var page = $("input[name*='page']").val();
        var maxOffset = "<?=$pagination->pageSizeLimit[1]?>";
        var maxPage = "<?=$pagination->getPageCount()?>";
        if(parseInt(offset)>parseInt(maxOffset)){
            alert("<?=Yii::t('app', 'page offset msg', ['maxSize' => '"+maxOffset+"'])?>");
            return false;
        }
        if(parseInt(page)>parseInt(maxPage)){
            alert("<?=Yii::t('app', 'page number msg', ['maxSize' => '"+maxPage+"'])?>");
            return false;
        }
    }
</script>
