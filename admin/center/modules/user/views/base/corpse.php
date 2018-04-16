<?php
use yii\widgets\LinkPager;
use yii\helpers\Html;
use center\modules\auth\models\SrunJiegou;
use center\widgets\Alert;

/**
 * @var yii\web\View $this
 * @var $userArray
 */
$this->title = \Yii::t('app', 'user/base/corpse-users');
?>
<div class="page">
    <?= Alert::widget() ?>
    <div class="panel panel-default">
        <div class="panel-body">
            <form name="form_constraints" action="<?= \yii\helpers\Url::to(['corpse-users']) ?>"
                  class="form-horizontal form-validation" method="get">
                <div class="form-group">
                    <div class="col-md-2">
                        <input type="text" class="form-control inputDate" name="start_date"
                               id="start_date"
                               value="<?= isset($params['start_date']) ? $params['start_date'] : '' ?>"
                               placeholder="<?= Yii::t('app', 'start date') ?>">
                    </div>
                    <div class="col-md-2">
                        <input type="text" class="form-control inputDate" name="end_date"
                               id="end_date"
                               value="<?= isset($params['end_date']) ? $params['end_date'] : '' ?>"
                               placeholder="<?= Yii::t('app', 'end date') ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-success"><?= Yii::t('app', 'search') ?></button>
                    </div>
                </div>
                </label>

            </form>
        </div>
    </div>

    <section class="panel panel-default table-dynamic">
        <?php if($corpseUsers){?>
            <div style="float:right;margin-right:10px;margin-top:5px;">
                <a type="button" class="btn btn-default btn-sm" href="<?= strstr(yii::$app->request->url, 'action=excel') ? yii::$app->request->url : yii::$app->request->url; ?><?= strstr(yii::$app->request->url, '?') ? '&' : '?'; ?>action=excel"><span
                        class="glyphicon glyphicon-log-out"></span>excel</a>

            </div>
        <?php }?>
        <div class="panel-heading">
            <strong><span class="glyphicon glyphicon-list-alt text-small"></span> <?= Yii::t('app', 'user/base/corpse-users') ?>
            </strong>
        </div>
        <?php if (!empty($corpseUsers)): ?>
            <table class="table table-bordered table-striped table-responsive" style="border-top:0;">
                <thead>
                <tr>
                    <th>
                        <div align="center"><input type="checkbox" id="all"/></div>
                    </th>
                    <th>
                        <div class='th'><?=Yii::t('app','account')?></div>
                    </th>
                    <th>
                        <div class='th'><?=Yii::t('app','name')?></div>
                    </th>
                    <th>
                        <div class='th'><?=Yii::t('app','user products id')?></div>
                    </th>
                    <th>
                        <div class='th'><?=Yii::t('app','products balance')?></div>
                    </th>
                    <th>
                        <div class='th'><?=Yii::t('app','user available')?></div>
                    </th>
                    <th>
                        <div class='th'><?=Yii::t('app','group id')?></div>
                    </th>
                    <th>
                        <div class='th'><?=Yii::t('app','users balance')?></div>
                    </th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($corpseUsers as $id => $one) { ?>
                    <tr>
                        <td align="center"><input type="checkbox" name="id[]" value="<?= $one['user_id']?>" /></td>
                        <td><?= Html::a($one['user_name'], ['view','user_name'=>$one['user_name']]);?></td>
                        <td><?=Html::encode($one['user_real_name'])?></td>
                        <td><?=!empty($one['product_name'])?implode('<br>', $one['product_name']):''?></td>
                        <td><?=!empty($one['product_balance'])?implode('<br>', $one['product_balance']):''?></td>
                        <td><?php $available_css = $one['user_available'] == 0 ? 'label-success' : 'label-danger';
                            echo '<span class="label '.$available_css.' label-xs">'.$attributes['user_available'][$one['user_available']].'</span>';?></td>
                        <td><?=Html::encode(SrunJiegou::getOwnParent([$one['group_id']]))?></td>
                        <td><?=Html::encode($one['balance'])?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
            <label class="text-info"><button style="margin-left: 5px;margin-top:5px;" type="button" class="btn btn-success" onclick="batch()"><?= Yii::t('app', 'batch disable') ?></button></label>
            <div class="divider"></div>
            <footer class="table-footer">
                <div class="row">
                    <div class="col-md-6">
                        <?php
                        echo Yii::t('app', 'pagination show1', ['totalCount' => $pages->totalCount, 'totalPage' => $pages->getPageCount(), 'perPage' => $pages->pageSize]);
                        ?>
                    </div>
                    <div class="col-md-6 text-right">
                        <?php
                        echo LinkPager::widget(['pagination' => $pages, 'maxButtonCount' => 5]);
                        ?>
                    </div>
                </div>
            </footer>
        <?php else: ?>
            <div class="panel-body">
                <?= Yii::t('app', 'no record') ?>
            </div>
        <?php endif ?>
    </section>
</div>

</div>
<script>

    var all = document.getElementById('all');
    var ids = document.getElementsByName('id[]');
    all.onclick=function(){
        var stats = this.checked;
        for(var i=0;i<ids.length;i++){
            ids[i].checked = stats;
        }
    }
    function batch(){
        var action = 'batchdisable';
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

        if(window.confirm("<?=Yii::t('app', 'user base help35')?>")){
            $.ajax({
                type:"POST",
                url:"<?=\yii\helpers\Url::to(['batch-operate'])?>",
                data:{'user_id':ids,'action':action, 'products_id':product, 'renew_num': renew_num,  'extend_fields': extendFields,'extend_values': extendValues, 'buy_num': buy_num, 'packages_num': package_num },
                dataType:'json',
                success:function (){

                    window.location.reload();//刷新当前页面.

                }
            });
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
</script>