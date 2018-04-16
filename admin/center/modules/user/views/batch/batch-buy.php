<?php
use center\widgets\Alert;
use yii\helpers\Html;
use center\modules\financial\models\PayType;
use center\extend\Tool;

/**
 * 批量缴费
 * @var yii\web\View $this
 * @var center\modules\financial\models\PayList $model 缴费模型
 * @var center\modules\User\models\base $userModel 用户模型
 * @var array $lists 各种列表数据
 * @var array $params 参数列表
 */
//求总金额的字符串
$totalStr = 0;
//是否是订购产品，订购产品的状态下用户余额缴费，附加费用缴费不显示，直接把产品续费显示出来，显示用户余额支付的方式
$isOrderProduct = (isset($params['action']) && $params['action'] == 'product') ? true : false;
$this->title = Yii::t('app', 'user/batch/buy');
$payAttributes = $model->getExtendAttributesList();
?>

<div class="page page-table" data-ng-controller="payCtrl">
    <?= Alert::widget() ?>
    <!--搜索用户页面-->
    <section class="panel panel-default table-dynamic">
        <div class="panel-heading"><strong><span
                    class="glyphicon glyphicon-th"></span> <?= Yii::t('app', 'user/batch/buy') ?> </strong></div>
        <div class="panel-body">
            <form class="form-validation form-horizontal ng-pristine ng-valid" name="searchForm"
                  action="<?= \yii\helpers\Url::to(['buy']) ?>" method="get" role="form">
                <div class="col-lg-8 col-md-8 col-sm-8" ng-init="user_name='<?= !empty($params) ? $params['username'] : '' ?>'">
                    <div class="form-group required">
                        <input type="text" class="form-control" name="username" required
                               data-ng-model="user_name"
                               value="<?= !empty($params) ? $params['username'] : '' ?>"
                               placeholder="<?= Yii::t('app', 'account') ?>">
                    </div>
                </div>
                <div class="col-lg-4">
                    <?=
                    \yii\helpers\Html::submitButton(Yii::t('app', 'search'), [
                        'class' => 'btn btn-success',
                        'data-ng-disabled' => 'searchForm.$invalid'
                    ]) ?>
            </form>
        </div>
    </section>

    <?php if ($userModel): ?>
        <!--缴费：余额缴费，附加费用，产品续费，订购套餐，订购新产品-->
        <?php $form = \yii\bootstrap\ActiveForm::begin([
            'layout' => 'horizontal',
            'options' => [
                'novalidate' => true,
                'name' => 'pay',
                'class' => 'form-validation',
                'onsubmit' => 'return check("' . Yii::t('app', 'pay help12') . '");'
            ],
        ]) ?>
        <!--展示用户信息页面-->
        <div class="panel panel-default">
            <div class="panel-body">
                <!--基本信息-->
                <div class="row">
                    <div class="col-sm-12">
                        <table class="table table-bordered table-striped table-responsive">
                            <thead>
                            <tr>
                                <th><?= Yii::t('app', 'id') ?></th>
                                <th><?= Yii::t('app', 'network_user_login_name') ?></th>
                                <th><?= Yii::t('app', 'name') ?></th>
                                <th><?= Yii::t('app', 'user electronic balance') ?></th>
                                <th><?= Yii::t('app', 'products_id') ?></th>
                                <th><?= Yii::t('app', 'products balance') ?></th>
                                <th><?= Yii::t('app', 'checkout date') ?></th>
                                <th><?= Yii::t('app', 'batch excel pay num') ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $totalMoney = 0;foreach ($userModel as $k => $v): ?>
                                <tr>
                                    <td><?= $k ?></td>
                                    <td><?= $v['user_name'] ?></td>
                                    <td><?= $v['user_real_name'] ?></td>
                                    <td><?= $v['balance'] ?></td>
                                    <td><?php echo isset($v['product_name']) ? implode('<br>', $v['product_name']) : ''; ?></td>
                                    <td><?php echo isset($v['product_balance']) ? implode('<br>', $v['product_balance']) : '' ?></td>
                                    <td><?php echo isset($v['checkout_date']) ? implode('<br/>',$v['checkout_date']) : '--'; ?></td>
                                    <td>
                                        <?php if (isset($v['products_id'])): ; ?>
                                            <?php foreach ($v['products_id'] as $key => $product):?>
                                                <input
                                                    style="display:inline;width:200px;margin-bottom:5px;"
                                                    class="form-control"
                                                    type="number"
                                                    step = '0.01'
                                                    name="users[<?= $v['user_id'] ?>][products][<?=$product?>]"
                                                    data-ng-model="username_<?= $v['user_name'] ?>_<?=$product?>_money"
                                                    data-ng-init="username_<?= $v['user_name'] ?>_<?=$product?>_money ="
                                                    data-ng-pattern="/^\d+(\.\d{1,2})?$/"
                                                    min="0.01" 
                                                    />
                                                <br/>
                                                <?php $totalMoney .= '+username_' .$v['user_name'].'_'.$product.'_money' ?>

                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <tr style="border:none;">
                                <td colspan="8" style="padding-left:900px;">
                                    <?php
                                    echo '总金额: ' . '{{' . $totalMoney . '>0 ? ' . $totalMoney . ' : 0}}';
                                    ?>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>


        <div class="panel panel-default">
            <div class="panel-heading">
                <span class="glyphicon glyphicon-usd"></span> <?= Yii::t('app', 'user/batch/buy') ?>&nbsp;&nbsp;
                &nbsp;&nbsp;
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-sm-10">

                        <!--缴费方式-->
                        <div>
                            <div class="form-group">
                                <div class="row">
                                    <label
                                        class="control-label col-sm-3"><?= Yii::t('app', 'Financial PayType') ?></label>

                                    <div class="col-sm-4">
                                        <?= Html::dropDownList('PayList[payType]', 0, $list['payTypeList'], ['id' => 'payType', 'class' => 'form-control', 'ng-model' => 'typeValue', 'ng-init' => 'typeValue=' . PayType::getDefaultType(),]) ?>
                                    </div>
                                    <div class="col-sm-4 control-label" id="payList_error" style="display:none;color:red;"></div>
                                </div>
                            </div>
                        </div>


                        <hr/>

                        <!--扩展字段-->
                        <div class="form-group">

                            <?php if ($extendFields) {
                                    foreach ($extendFields as $one) {
                                        $fieldName = $one['field_name'];

                                        $field = $form->field($model, $one['field_name']);
                                        //$field = preg_replace('/name\=\"(.*)\"/', "<font style='color:red;'>\\1</font>", $field);

                                        //如果输入类型是数组
                                        if ($one['type'] == 1 && isset($payAttributes[$one['field_name']])) {
                                            //如果是下拉框
                                            if ($one['show_type'] == 0) {
                                                //如果没有合法的值，再进行赋空值
                                                if (!in_array($model->$one['field_name'], array_keys($payAttributes[$one['field_name']]))) {
                                                    $model->$one['field_name'] = '';
                                                }
                                                $payAttributes[$one['field_name']] = ['' => Yii::t('app', 'Please Select')] + $payAttributes[$one['field_name']];
                                                $field = $field->dropDownList($payAttributes[$one['field_name']]);
                                            } //单选框
                                            else if ($one['show_type'] == 1) {
                                                $field = $field->inline()->radioList($payAttributes[$one['field_name']]);
                                            }
                                        }
                                        preg_match('/name\=\"([\w|\d|_|\[|\]]*)\"/', $field, $array);
                                        $field = str_replace($array[1], "PayList[extendFields][$fieldName]", $field);
                                        echo $field;

                                    }

                            }?>
                        </div>

                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-9 col-sm-offset-3">
                                    <?=
                                    Html::submitButton(Yii::t('app', 'submit'), [
                                        'class' => 'btn btn-success',
                                    ]); ?>
                                </div>
                            </div>
                        </div>


                    </div>

                </div>
            </div>
        </div>
        <?php $form::end() ?>

    <?php endif ?>
</div>
<script>
    //缴费验证 如果电子钱包 余额不足，那么不让缴费
    function check(msg) {
        var typeValue = $("#payType").val();
        var totalNum = $('#totalNum').html();
        var value = document.getElementsByName('PayList[payType]')[0].value;
        if (value == 0) {
            var errorObj = $('#payList_error');
            errorObj.css('display', 'block');
            errorObj.html("<?=Yii::t('app', 'payType select')?>");
            return false;
        }
        if (typeValue == '4' && totalNum > 0) {
            $('#totalMsg').html(msg);
            return false;
        }
    }
    document.getElementById('payType').onchange = function () {
        var value = document.getElementsByName('PayList[payType]')[0].value;
        if (value != 0) {
            $('#payList_error').css('display', 'none');
        } else {
            $('#payList_error').css('display', 'block');
            $('#payList_error').html("<?=Yii::t('app', 'payType select')?>")
        }
    }
</script>