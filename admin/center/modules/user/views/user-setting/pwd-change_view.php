<?php
use yii\helpers\Html;
//dd($rs2['way']);
// 头部加载 jQuery
$this->registerJsFile("/js/lib/jquery-2.1.1.js",['position' => \yii\web\View::POS_HEAD]);
?>

<div class="panel panel-default">
    <div class="panel-body">
        <div class="col-lg-12 padding-left-0px">
            <h4 class="headline-1">
                <span class="headline-1-index"></span>
                <span class="headline-content"><?= Yii::t('app', 'mod_pwd_first') ?></span>
            </h4>

            <div class="col-lg-12">
                <input type="checkbox" name="pwd_change_first" <?=$rs->value == 1 ? 'checked' : '';?> id="pwd_strong1">  <label for="pwd_strong1"><?=Yii::t('app','must_mod_first')?></label><br>
            </div>
            <h4 class="headline-1">
                <span class="headline-1-index"></span>
                <span class="headline-content"><?= Yii::t('app', 'mod_pwd_other') ?></span>
            </h4>

            <div class="col-lg-12">
                <input type="radio" name="pwd_change_way" <?=isset($rs2['way']) && $rs2['way'] == 'day' ? 'checked' : ''; ?>id="day" value="day"> <label for="day"><?=Yii::t('app','do_day')?></label>
                &nbsp;&nbsp;<?=Yii::t('app','do')?> <input type="text" name="" id="" value="<?=isset($rs2['way']) && $rs2['way'] == 'day' ? $rs2['num'] : ''; ?>"> <?=Yii::t('app','day')?> <?=Yii::t('app','mod_pwd_once')?>
                <br><br>
                <input type="radio" name="pwd_change_way" <?=isset($rs2['way']) && $rs2['way'] == 'week' ? 'checked' : '';?> id="week" value="week"> <label for="week"><?=Yii::t('app','do_week')?></label>
                &nbsp;&nbsp;<?=Yii::t('app','do')?> <input type="text" name="" id="" value="<?=isset($rs2['way']) && $rs2['way'] == 'week' ? $rs2['num'] : ''; ?>"> <?=Yii::t('app','week_')?> <?=Yii::t('app','mod_pwd_once')?>
                <br><br>
                <input type="radio" name="pwd_change_way" <?=isset($rs2['way']) && $rs2['way'] == 'month' ? 'checked' : '';?> id="month" value="month"> <label for="month"><?=Yii::t('app','do_month')?></label>
                &nbsp;&nbsp;<?=Yii::t('app','do')?> <input type="text" name="" id="" value="<?=isset($rs2['way']) && $rs2['way'] == 'month' ? $rs2['num'] : ''; ?>"> <?=Yii::t('app','month')?> <?=Yii::t('app','mod_pwd_once')?>
                <br><br>
                <input type="radio" name="pwd_change_way" <?=isset($rs2['way']) && $rs2['way'] == 'no' ? 'checked' : '';?> id="no" value="no"> <label for="no"><?=Yii::t('app','no_set')?></label>
                &nbsp;&nbsp; <input type="hidden" name="" id="" value="<?=isset($rs2['way']) && $rs2['way'] == 'no' ? $rs2['num'] : '1'; ?>"> <br><br>
                <button type="button" class="btn btn-primary" id="sure"> <?=Yii::t('app','sure')?> </button>
            </div>
        </div>
    </div>
</div>

<script>
    $(function () {
        $(':input[name="pwd_change_first"]').click(function () {
            var first = $(this).is(":checked");
            $.post(
                '/user/user-setting/set-pwd-change-first',
                {first: first},
                function (e) {
                    toastr.info(e.msg);
                },'json'
            )
        });

        $('#sure').click(function () {
            var way = $('input[name="pwd_change_way"]:checked').val();
            var num = $('input[name="pwd_change_way"]:checked').next().next().val();
            $.post(
                '/user/user-setting/set-pwd-change-way',
                {way: way,num: num},
                function (e) {
                    toastr.info(e.msg);
                },'json'
            )
        })
    })
</script>