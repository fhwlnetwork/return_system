<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;
use center\modules\selfservice\models\Setting;
use center\modules\strategy\models\Product;
use center\modules\auth\models\SrunJiegou;

$this->title = Yii::t('app', 'user/group/bind-product');
$group = new SrunJiegou();
$Product = new Product();
$this->registerJsFile('/js/lib/jquery.js', ['position' => $this::POS_HEAD]);
//zTree
center\assets\ZTreeAsset::register($this);
$this->registerJsFile('/js/ztree_select_multi.js', ['depends' => [center\assets\ZTreeAsset::className()]]);
$this->registerJs("
    createTree('zTreeAddUser');
");
?>
<style type="text/css">
    .ztree li a.curSelectedNode span {
        background-color: #0088cc;
        color: #fff;
        border-radius: 2px;
        padding: 2px;
    }
</style>
<div class="padding-top-15px">
    <?= $this->render('../../../auth/views/layouts/nav'); ?>

    <div class="col-lg-10">
        <h3 class="page-header" style="margin-top:10px;">
            <i class="fa fa-puzzle-piece size-h3"></i>&nbsp;<?= Yii::t('app','is_depend_yes'); ?>
        </h3>
        <div class="panel panel-default">
            <div class="panel-heading">
                <input type="checkbox"
                       name="is_depend" <?php if ($this->params['add_user_depend_bind_relation'] == 'no') {
                    echo 'checked';
                } ?> value="1" id="is_depend" onclick="selectAll();">
                <label for="is_depend" style="color: darkred;"><?= Yii::t('app', 'is_depend_no') ?></label>
            </div>
        </div>

        <h3 class="page-header" style="margin-top:10px;">
            <i class="fa fa-object-group size-h4"></i>&nbsp;<?= Html::encode($this->title); ?>
        </h3>
        <div>
            <?= \center\widgets\Alert::widget(); ?>
            <?php $form = ActiveForm::begin([]); ?>

            <div class="panel panel-default">
                <div class="panel-heading">
                    <?php echo Yii::t('app', 'group bind product');
                    if (!\common\models\User::isSuper()) {
                        echo '<span style="color:red;padding-left:10px">(' . Yii::t('app', 'group_bind_product_msg1') . ')</span>';
                    }
                    ?></div>
                <div class="panel-body" id="groupBindProduct">
                    <div style="max-height: 500px; overflow-y: auto;">
                        <div id="zTreeAddUser" class="ztree"></div>
                    </div>
                </div>
                <input type="hidden" name="Setting[key]" id="groupBindProductKey" value="">
                <div class="panel-footer">
                    <div class="col-md-3">
                        <input type="checkbox" name="select_all" value="1" id="select_all" onclick="selectAll();">
                        <label for="select_all"><?= Yii::t('app', 'group_bind_product_msg3') ?></label>
                    </div>
                    <?= Html::submitButton(Yii::t('app', 'save'), ['class' => 'btn btn-primary col-lg-offset-2']); ?>
                </div>

            </div>

            <?php ActiveForm::end(); ?>
        </div>
        <script>
            function selectAll() {
                var is_all = $("#select_all").prop('checked');
                var ids = $("input[name*='Setting[group_bind_product]']");
                for (var i = 0; i < ids.length; i++) {
                    ids[i].checked = is_all;
                }
            }

            // 用户添加页不依赖绑定关系
            jQuery("#is_depend").change(function () {
                var url = '/user/group/depend';
                var data;
                if ($(this).is(':checked')) {
                    data = {is_depend: 'no'};
                } else {
                    data = {is_depend: 'yes'};
                }
                var type = 'json';
                var fun = function (e) {
                    if (e.status === 1) {
                        toastr.success(e.msg);
                    } else {
                        toastr.error(e.msg);
                    }
                };
                $.post(url, data, fun, type);
            })
        </script>
