<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2016/5/9
 * Time: 11:56
 */

use yii\widgets\LinkPager;
use yii\helpers\Html;
use center\widgets\Alert;
use yii\bootstrap\ActiveForm;

\center\assets\ReportAsset::echartsJs($this);
$this->title = \Yii::t('app', 'Monitor All Users');

$lang = (Yii::$app->session->get('language')) ? Yii::$app->session->get('language') : 'zh-CN';
?>
<div style="padding: 20px;font-family: inherit;">
    <?= Alert::widget() ?>
    <div class="row">
        <form name="form_constraints" action="<?= \yii\helpers\Url::to(['monitor-all-user']) ?>"
              class="form-horizontal form-validation" method="get">
            <div class="col-md-2">
                <?php echo Html::input('text', 'start_time', (!empty($searchInput) && isset($searchInput['start_time']))
                    ? $searchInput['start_time'] : date('Y-m-d H:i:s', time() - 30 * 60), [
                    'class' => 'form-control inputDateTime',
                    'placeHolder' => Yii::t('app', 'Statistical time'),
                ]) ?>
            </div>
            <div class="col-md-2">
                <?php echo Html::input('text', 'end_time', (!empty($searchInput) && isset($searchInput['end_time']))
                    ? $searchInput['end_time'] : date('Y-m-d H:i:s'), [
                    'class' => 'form-control inputDateTime',
                    'placeHolder' => Yii::t('app', 'end time'),
                ]) ?>
            </div>
            <div class="col-md-2" style="width:150px;">
                <?= Html::input('text', 'products_key', (!empty($searchInput) && isset($searchInput['products_key'])) ? $searchInput['products_key'] : '', [
                    'class' => 'form-control',
                    'placeHolder' => Yii::t('app', 'network_user_login_name')
                ]) ?>
            </div>
            <div class="col-md-1" <?php $length = ($lang == 'zh-CN') ? 60 : 75; ?>style="width:<?= $length ?>px;">
                <?= Html::submitButton(Yii::t('app', 'search'), ['class' => 'btn btn-success']) ?>
            </div>
            <div class="col-md-1" <?php $length = ($lang == 'zh-CN') ? 75 : 110; ?>style="width:<?= $length ?>px;">
                <?= html::submitButton(yii::t('app', 'half an hour'), ['class' => 'btn btn-warning', 'name' => 'timePoint', 'value' => '2']) ?>
            </div>
            <div class="col-md-1" <?php $length = ($lang == 'zh-CN') ? 75 : 83; ?>style="width:<?= $length ?>px;">
                <?= html::submitButton(yii::t('app', 'an hour'), ['class' => 'btn btn-primary', 'name' => 'timePoint', 'value' => '3']) ?>
            </div>
            <div class="col-md-1" <?php $length = ($lang == 'zh-CN') ? 60 : 70; ?>style="width:<?= $length ?>px;">
                <?= html::submitButton(yii::t('app', 'this day'), ['class' => 'btn btn-info', 'name' => 'timePoint', 'value' => '4']) ?>
            </div>
            <div class="col-md-1" <?php $length = ($lang == 'zh-CN') ? 60 : 95; ?>style="width:<?= $length ?>px;">
                <?= html::submitButton(yii::t('app', 'this Yesterday'), ['class' => 'btn btn-warning', 'name' => 'timePoint', 'value' => '5']) ?>
            </div>
            <div class="col-md-1" <?php $length = ($lang == 'zh-CN') ? 60 : 90; ?>style="width:<?= $length ?>px;">
                <?= html::submitButton(yii::t('app', 'this week'), ['class' => 'btn btn-primary', 'name' => 'timePoint', 'value' => '6']) ?>
            </div>
            <div class="col-md-1" <?php $length = ($lang == 'zh-CN') ? 60 : 90; ?>style="width:<?= $length ?>px;">
                <?= html::submitButton(yii::t('app', 'last week'), ['class' => 'btn btn-info', 'name' => 'timePoint', 'value' => '7']) ?>
            </div>
            <div class="col-md-1" <?php $length = ($lang == 'zh-CN') ? 60 : 90; ?>style="width:<?= $length ?>px;">
                <?= html::submitButton(yii::t('app', 'this month'), ['class' => 'btn btn-warning', 'name' => 'timePoint', 'value' => '8']) ?>
            </div>
        </form>
    </div>


</div>
<div class="col-md-12">
    <section class="panel panel-default table-dynamic">
        <div class="panel-heading data-center"><strong><span
                    class="glyphicon glyphicon-th-large"></span> <?= \Yii::t('app', 'Monitor All Users'); ?></strong>
        </div>

        <?php if (!empty($rows)): ?>
            <div>
                <table class="table table-bordered  table-responsive">
                    <thead style="width:1000px;">
                    <tr>
                        <?php
                        if (isset($searchField)) {
                            echo '<th nowrap="nowrap"><div class="th">' . Yii::t('app', 'action');
                            echo '</div></th>';

                            foreach ($searchField as $value) {
                                $newParams = $params;
                                echo '<th nowrap="nowrap"><div class="th">';
                                echo $value;
                                echo '</div></th>';
                            }
                            echo '<th nowrap="nowrap"><div class="th">' . Yii::t('app', 'user max online').'</div></th>';
                            echo '<th nowrap="nowrap"><div class="th">' . Yii::t('app', 'network_user_login_name').'</div></th>';;
                            echo '<th nowrap="nowrap"><div class="th">' . Yii::t('app', 'school name').'</div></th>';;
                        }
                        ?>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $i = 0;
                    foreach ($rows as $k => $one):; ?>
                        <tr bgcolor="<?php echo $i % 2 == 1 ? "#fff" : '#f1f1f1' ?>">
                            <td><span id="product_key_<?= $i ?>" onclick="chgBreak('<?=$i?>')"  ng-click='product_key_<?= $i ?> = !product_key_<?= $i ?>'
                                      class="glyphicon glyphicon-plus" style="cursor: pointer"></span>
                            </td>
                            <?php foreach ($one as $key => $value):?>

                                <?php if ($key == 'source') {
                                    continue;
                                } ?>
                            <td>
                                <?php if ($key != 'productKey' && $key != 'user_account' && $key != 'school_name' && $key != 'user_max_account') {
                                    echo $value, 'ms</td>';
                                } else {
                                    echo $value, '</td>';
                                } ?>

                                <?php endforeach; ?>
                            <td><?= $k;?></td>
                            <td><?= \center\modules\user\models\Base::findOne($k)->user_real_name;?></td>
                        </tr>
                        <tr ng-show="product_key_<?= $i ?>">
                            <td colspan="10" ng-show="product_key_<?= $i ?>">
                                <div ng-show="product_key_<?= $i ?>" class="panel-heading data-center"><strong><span
                                            class="glyphicon glyphicon-th-large"></span> <?= \Yii::t('app', 'report/monitor/machine-state'); ?>
                                        ---<?= $k ?></strong>
                                </div>
                                <div class="row" ng-show="product_key_<?= $i ?>">
                                    <div class="col-md-8 col-sm-8 col-lg-8 text-left">
                                        <table ng-show="product_key_<?= $i ?>" title="<?= $i ?>"
                                               class="table table-bordered table-striped table-responsive"
                                               >
                                            <?php if (!empty($productsData) && isset($productsData[$k])): ?>
                                                <?php
                                                if (isset($searchField)) {
                                                    echo '<th nowrap="nowrap"><div class="th">';
                                                    echo Yii::t('app', 'proc');
                                                    echo '</div></th>';
                                                    foreach ($searchField as $value) {
                                                        $newParams = $params;
                                                        echo '<th nowrap="nowrap"><div class="th">';
                                                        echo $value;
                                                        echo '</div></th>';
                                                    }
                                                }
                                                ?>
                                                <?php foreach ($productsData[$k] as $key => $pro): ?>
                                                    <tr>
                                                        <td><?= $key ?></td>
                                                        <td><?= $pro[0]['start_response'] ?>ms</td>
                                                        <td><?= $pro[0]['auth_response'] ?>ms</td>
                                                        <td><?= $pro[0]['dm_response'] ?>ms</td>
                                                        <td><?= $pro[0]['coa_response'] ?>ms</td>
                                                        <td><?= $pro[0]['update_response'] ?>ms</td>
                                                        <td><?= $pro[0]['stop_response'] ?>ms</td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </table>
                                    </div>

                                    <!--用户在线数-->
                                    <div class="col-md-4 col-sm-4 col-lg-4 text-left" ng-show="product_key_<?= $i ?>">
                                        <?= $this->render('monitor-online-report', ['source' => $rows[$k]['source'], 'id' => "report-online-$k", 'title' => $k . Yii::t('app', 'report/online/index'), 'model' => $model]) ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php $i++;endforeach ?>
                    </tbody>
                </table>
            </div>

            <div class="divider"></div>
            <footer class="table-footer">
                <div class="row">
                    <?php if(!$model->flag): ?>
                    <div class="col-md-6">

                        <?php
                        echo Yii::t('app', 'pagination show page', [
                            'totalCount' => $pagination->totalCount,
                            'totalPage' => $pagination->getPageCount(),
                            'perPage' => '<input type=text name=offset size=3 value=' . $pagination->defaultPageSize . '>',
                            'pageInput' => '<input type=text name=page size=4>',
                            'buttonGo' => '<input type=submit value=go>',
                        ]);
                        ?>
                    </div>
                    <div class="col-md-6 text-right">
                        <?= LinkPager::widget(['pagination' => $pagination, 'maxButtonCount' => 5]); ?>
                    </div>
                    <?php endif;?>
                </div>
            </footer>

        <?php else: ?>
            <div class="panel-body">
                <?= Yii::t('app', 'no record') ?>
            </div>
        <?php endif ?>
    </section>
</div>

<script>
    function chgBreak(id)
    {
        var obj = $('#product_key_'+id);
        var  className =   obj.attr('class');
        if (className.indexOf('plus') != -1) {
            obj.attr('class', 'glyphicon glyphicon-minus')
        } else {
            obj.attr('class', 'glyphicon glyphicon-plus')
        }

    }
</script>