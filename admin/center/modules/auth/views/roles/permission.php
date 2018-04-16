<?php

//权限列表清单
$menu = Yii::$app->params['menu'];

/**
 * @var array $userPermission   当前登录管理员所拥有的权限列表，如果非超级管理员，那么根据此数组进行控制显示哪些权限
 * @var bool $userIsSuper 当前是否超级管理员，如果是那么显示全部权限
 * @var bool $isEdit 是否是编辑状态，编辑状态先菜单默认不做隐藏
 * @var array $items 当前角色的选中状态，在编辑状态下根据此数组对权限进行勾选
 */
?>
<div class="col-lg-12">
    <?php foreach($menu as $key1 => $val1):?>
        <?php if($userIsSuper || in_array($val1['url'], $userPermission)):?>
            <?php $str1 = 'Fir'.md5($key1); ?>
            <!--一级菜单-->
            <h4 class="headline-1">
                <span class="headline-1-index <?= $val1['ico']?>"></span>
                <label>
                    <span class="headline-content"><?= Yii::t('app', $val1['label'])?></span>
                    <span class="headline-content">
                        <label>
                            <input type="checkbox" name="AuthItemChild[child][]" value="<?=$val1['url']?>" id="<?=$str1?>"
                                   onchange="permissionChangeCheck('<?=$str1?>')" <?php if($isEdit && in_array($val1['url'], $items)): ?> checked="checked" <?php endif ?>>
                        </label>
                    </span>
                </label>
            </h4>
            <!--如果存在子菜单-->
            <?php if($val1['items']): ?>
                <div class="<?=$str1?>" <?php if(!$isEdit): ?>style="display: none" <?php endif ?>>
                    <?php foreach($val1['items'] as $key2 => $val2):?>
                        <?php if(!is_array($val2)): ?>
                            <?php if($userIsSuper || in_array($val2, $userPermission)):?>
                                <div class="row">
                                    <div class="col-sm-11 col-sm-offset-1">
                                        <label>
                                            <input type="checkbox" name="AuthItemChild[child][]" value="<?=$val2?>"
                                                <?php if($isEdit && in_array($val2, $items)): ?> checked="checked" <?php endif ?>>
                                            &nbsp;<?= Yii::t('app',$val2)?>
                                        </label>
                                    </div>
                                </div>
                            <?php endif ?>
                        <?php else: ?>
                            <?php if($userIsSuper || in_array($key2, $userPermission)):?>
                                <?php $str2 = 'Sec'.md5($key2) ?>
                                <!--二级菜单-->
                                <div class="row">
                                    <div class="col-sm-11 col-sm-offset-1">
                                        <label>
                                            <input type="checkbox" name="AuthItemChild[child][]" value="<?=strtolower($key2)?>" id="<?=$str2?>"
                                                   onchange="permissionChangeCheck('<?=$str2?>')"
                                                <?php if($isEdit && in_array(strtolower($key2), $items)): ?> checked="checked" <?php endif ?>>
                                            &nbsp;<?= Yii::t('app',$key2)?>
                                        </label>
                                    </div>
                                </div>
                                <!--三级菜单-->
                                <div class="row <?=$str1.' '.$str2?>" <?php if(!$isEdit): ?>style="display: none" <?php endif ?> >
                                    <div class="col-sm-10 col-sm-offset-2">
                                        <?php foreach($val2 as $key3 => $val3):?>
                                            <?php if(is_array($val3)):?>
                                                <?php if($userIsSuper || in_array($key3, $userPermission)):?>
                                                    <?php $str3 = 'Thi'.md5($key3)?>
                                                    <div class="col-sm-12">
                                                        <label>
                                                            <input type="checkbox" name="AuthItemChild[child][]" value="<?=$key3?>" id="<?=$str3?>"
                                                                   onchange="permissionChangeCheck('<?=$str3?>')"
                                                                <?php if($isEdit && in_array($key3, $items)): ?> checked="checked" <?php endif ?>>
                                                            &nbsp;<?= Yii::t('app',$key3)?>
                                                        </label>
                                                    </div>
                                                    <!--四级菜单-->
                                                    <div class="col-sm-11 col-sm-offset-1 <?=$str1.' '.$str2.' '.$str3?>"
                                                         <?php if(!$isEdit): ?>style="display: none" <?php endif ?> >
                                                        <?php foreach ($val3 as $key4 => $val4): ?>
                                                            <?php if($userIsSuper || in_array($val4, $userPermission)):?>
                                                                <!--权限-->
                                                                <div class="col-sm-3">
                                                                    <label>
                                                                        <input type="checkbox" name="AuthItemChild[child][]" value="<?=$val4?>"
                                                                            <?php if($isEdit && in_array($val4, $items)): ?> checked="checked" <?php endif ?>>
                                                                        &nbsp;<?= Yii::t('app',$val4)?>
                                                                    </label>
                                                                </div>
                                                            <?php endif ?>
                                                        <?php endforeach ?>
                                                    </div>
                                                <?php endif ?>
                                            <?php else: ?>
                                                <?php if($userIsSuper || in_array($val3, $userPermission)):?>
                                                    <!--权限-->
                                                    <div class="col-sm-3">
                                                        <label>
                                                            <input type="checkbox" name="AuthItemChild[child][]" value="<?=$val3?>"
                                                                <?php if($isEdit && in_array($val3, $items)): ?> checked="checked" <?php endif ?>>
                                                            &nbsp;<?= Yii::t('app',$val3)?>
                                                        </label>
                                                    </div>
                                                <?php endif ?>
                                            <?php endif ?>

                                        <?php endforeach?>

                                    </div>
                                </div>
                            <?php endif ?>
                        <?php endif ?>

                    <?php endforeach?>
                </div>
            <?php endif ?>

        <?php endif ?>

    <?php endforeach?>
</div>
