<?php
\center\assets\ZTreeAsset::register($this);
$this->registerJsFile('/js/ztree_struct.js', ['depends' => [center\assets\ZTreeAsset::className()]]);
?>

<div style="height: 40px">
    <span id="save" style="display: none;">
        <button class="btn btn-success" onclick="if(confirm('<?= Yii::t('app', 'organization help1')?>'))saveAll()"><?= Yii::t('app', 'organization help2')?></button>
        <button class="btn btn-default" onclick="location.reload();"><?= Yii::t('app', 'organization help3')?></button>
    </span>
</div>

<ul id="treeStruct" class="ztree col-lg-12"></ul>
