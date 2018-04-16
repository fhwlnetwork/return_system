<?php
use yii\helpers\Html;

// 头部加载 jQuery
$this->registerJsFile("/js/lib/jquery-2.1.1.js",['position' => \yii\web\View::POS_HEAD]);
?>

<div class="panel panel-default">
    <div class="panel-body">
        <div class="col-lg-12 padding-left-0px">
            <h4 class="headline-1">
                <span class="headline-1-index"></span>
                <span class="headline-content"><?= Yii::t('app', 'pwd_strong') ?></span>
            </h4>

            <div class="col-lg-12">
                <input type="radio" name="pwd_strong" <?=$rs->value == 1 ? 'checked' : '';?> id="pwd_strong1" value="1">  <label for="pwd_strong1"><?=Yii::t('app','low')?></label>
                <span style="font-size: 12px;">(<?=Yii::t('app','pwd_6')?>)</span><br>
                <input type="radio" name="pwd_strong" <?=$rs->value == 2 ? 'checked' : '';?> id="pwd_strong2" value="2">  <label for="pwd_strong2"><?=Yii::t('app','middle')?></label>
                <span style="font-size: 12px;">(<?=Yii::t('app','pwd_8_20')?>)</span><br>
                <input type="radio" name="pwd_strong" <?=$rs->value == 3 ? 'checked' : '';?> id="pwd_strong3" value="3">  <label for="pwd_strong3"><?=Yii::t('app','high')?></label>
                <span style="font-size: 12px;">(<?=Yii::t('app','pwd_10_20')?>)</span><br>
                <input type="radio" name="pwd_strong" <?=$rs->value == 4 ? 'checked' : '';?> id="pwd_strong4" value="4">  <label for="pwd_strong4"><?=Yii::t('app','no_validate')?></label><br>
            </div>
        </div>
    </div>
</div>

<script>
    $(function () {
        $(':input[name="pwd_strong"]').click(function () {
            $.post(
                '/user/user-setting/set-pwd-strong',
                {value: $(this).val()},
                function (e) {
                    toastr.info(e.msg);
                },'json'
            )
        })
    })
</script>