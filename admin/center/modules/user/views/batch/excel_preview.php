<?php
use center\widgets\Alert;
use yii\helpers\Html;

$this->title = Yii::t('app', 'Batch Excel');

$typeArr = [
    '1' => Yii::t('app', 'batch excel import'),
    '2' => Yii::t('app', 'batch excel update'),
    '3' => Yii::t('app', 'batch excel delete'),
];

?>

<div class="page page-table">
    <?= Alert::widget() ?>
    <section class="panel panel-default">
        <div class="panel-heading"><strong><span class="glyphicon glyphicon-th-large"></span>
                <?=Yii::t('app', 'Batch Excel')?> -> <?= $typeArr[$model->batchType]?> -> <?=Yii::t('app', 'preview')?></strong></div>
        <div class="panel-body">

            <div style="overflow-x: auto;">
                <table class="table table-bordered table-striped table-responsive">
                    <thead>
                    <tr>
                        <?php foreach($model->selectField as $one): ?>
                        <th nowrap="nowrap"><?= Html::encode($model->showField[$one]?$model->showField[$one]:($model->showEditField[$one]?$model->showEditField[$one] : $model->buyField[$one])) ?></th>
                        <?php endforeach ?>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($model->excelData as $key => $value): ?>
                        <?php if($key==1) continue ?>
                        <?php if($key>=12) break ?>
                    <tr>
                        <?php foreach($value as $k=>$v): ?>
                        <td><?= Html::encode($v)?></td>
                        <?php endforeach ?>
                    </tr>
                    <?php endforeach ?>
                    </tbody>
                </table>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <?= Yii::t('app', 'batch excel help24') ?>
                </div>
            </div>
            <div class="divider-md"></div>

            <?php $form = \yii\bootstrap\ActiveForm::begin(['options' => [
            ],'id' => '#w1', 'action' => 'operate'])?>
            <div class="row">
                <div class="col-md-12">
                    <?php if($model->batchType == 1){?>
                        <?= Html::submitInput(Yii::t('app', 'confirm'), ['name' => 'confirm', 'class' => 'btn btn-success','onclick'=>'return befor_sub()']) ?>
                    <?php }else{?>
                        <?= Html::submitInput(Yii::t('app', 'confirm'), ['name' => 'confirm', 'class' => 'btn btn-success']) ?>
                    <?php }?>
                    <?= Html::a(Yii::t('app', 'cancel'), null, ['class' => 'btn btn-default', 'onclick'=>'window.history.back()']) ?>
                </div>
            </div>
            <?php $form->end()?>
        </div>
    </section>
</div>
<script>
    function before_sub(){
        var get_group_id = "<?php echo $get_group_id?>";
        var get_product_id = "<?php echo $get_product_id?>";
        //要添加的用户总数
        var user_count = "<?php echo $user_count?>";

        if(user_count != '' && get_group_id != '' && get_product_id != ''){
            $.ajax({
                'url':'/user/batch/ip-nums-ajax',
                'data':{'group_id':get_group_id,'product_id':get_product_id},
                'type':'POST',
                success:function(data){
                    //no_num表示没有ip段，直接提交
                    if(data == 'no_num'){
                        $("#w1").submit();
                    }
                    //no表示没有ip可用
                    if(data == 'no'){
                        data = 0;
                    }
                    //如果小于，给出提示
                    if(Number(user_count) > Number(data)){
                        firm = confirm('用于分配的ip('+data+'个)小于开户数，是否继续操作?');

                        if(firm == false){
                            return false;
                        }else{
                            $("#w1").submit();
                        }
                    }else{
                        //如果大于，或等于，提交
                        $("#w1").submit();
                    }
                }
            });
        }
        return false;
    }
</script>