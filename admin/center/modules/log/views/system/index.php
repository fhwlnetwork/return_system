<?php
use yii\widgets\LinkPager;
use yii\helpers\Html;
use center\widgets\Alert;

$this->title = \Yii::t('app', 'System Log');

?>
<div class="page">
<form name="form_constraints" action="<?=\yii\helpers\Url::to(['index'])?>" class="form-horizontal form-validation" method="get">
    <?= Alert::widget() ?>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-body">
                        <div class="form-group">
                            <div class="col-md-2"><?=Html::input('text', 'user_name', isset($params['user_name'])?$params['user_name']:'', ['class'=>'form-control', 'placeHolder'=>Yii::t('app', 'account')])?></div>
                            <div class="col-md-2"><?=Html::input('text', 'user_ip', isset($params['user_ip'])?$params['user_ip']:'', ['class'=>'form-control', 'placeHolder'=>Yii::t('app', 'user ip')])?></div>
                            <div class="col-md-2"><?=Html::input('text', 'user_mac', isset($params['user_mac'])?$params['user_mac']:'', ['class'=>'form-control', 'placeHolder'=>Yii::t('app', 'user mac')])?></div>
                            <div class="col-md-2"><?=Html::input('text', 'start_log_time', isset($params['start_log_time'])?$params['start_log_time']:'', ['class'=>'form-control inputDate', 'placeHolder'=>Yii::t('app', 'start log time')])?></div>
                            <div class="col-md-2"><?=Html::input('text', 'end_log_time', isset($params['end_log_time'])?$params['end_log_time']:'', ['class'=>'form-control inputDate', 'placeHolder'=>Yii::t('app', 'end log time')])?></div>
                            <div class="col-md-2">
                                <?php
                                //var_dump($params);exit;
                                echo '<select class="form-control" name="err_msg">';
                                foreach(\center\modules\log\models\System::getAttributesList()['system_error_message'] as $key => $val) {
                                    if(isset($params['err_msg'])) {
                                        if($params['err_msg'] === $key){
                                            echo '<option value="' .$key. '" selected="selected">' .$val. '</option>';
                                        } else {
                                            echo '<option value="' .$key. '">' .$val. '</option>';
                                        }
                                    } else {
                                        echo '<option value="' .$key. '">' .$val. '</option>';
                                    }
                                }
                                echo '</select>';
                                ?>
                            </div>
                        </div>
                        <div class="form-group" ng-cloak ng-show="advanced==1">
                                <div class="col-md-2"><?=Yii::t('app', 'log login help1')?></div>
                                <div class="col-md-10">
                                    <?= Html::checkboxList('showField[]', $params['showField'], $model->searchField, ['class'=>'drag_inline']) ?>
                                </div>
                        </div>
                        <?= Html::submitButton(Yii::t('app', 'search'), ['class' => 'btn btn-success']) ?>
                        &nbsp;&nbsp;&nbsp;&nbsp;<label class="text-info"><input type="checkbox" ng-model="advanced" /><small><?= Yii::t('app', 'advanced')?></small></label>

                    
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <section class="panel panel-default table-dynamic">
                <div class="panel-heading"><strong><span class="glyphicon glyphicon-th-large"></span> <?=Yii::t('app', 'search result')?></strong></div>
                <?php if (!empty($list)): ?>
                    <div style="overflow-x: auto;">
                    <table class="table table-bordered table-striped table-responsive" style="border-top:0;">
                        <thead>
                        <tr>
                            <?php
                            if( isset($params['showField']) && $params['showField'] ){
                                foreach( $params['showField'] as $value ){
                                    $newParams = $params;
                                    echo '<th nowrap="nowrap"><div class="th">';
                                    echo $model->searchField[$value];

                                    $newParams['orderBy'] = $value;
                                    array_unshift($newParams, '/log/system/index');

                                    //上面
                                    $newParams['sort'] = 'asc';
                                    $upActive = (isset($params['orderBy'])
                                        && $params['orderBy'] == $value
                                        && isset($params['sort'])
                                        && $params['sort'] == 'asc') ? 'active' : '';
                                    echo Html::a('<span class="glyphicon glyphicon-chevron-up '.$upActive.'"></span>', $newParams);

                                    //下面
                                    $newParams['sort'] = 'desc';
                                    $downActive = (isset($params['orderBy'])
                                        && $params['orderBy'] == $value
                                        && isset($params['sort'])
                                        && $params['sort'] == 'desc') ? 'active' : '';
                                    echo Html::a('<span class="glyphicon glyphicon-chevron-down '.$downActive.'"></span>', $newParams);

                                    echo '</div></th>';
                                }
                            }

                            ?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($list as $one): ?>
                            <tr>
                                <?php foreach ($params['showField'] as $value): ?>
                                    <td>
                                        <?php
                                            if( $value == 'user_name' ){
                                                echo Html::a($one[$value], ['/user/base/view','user_name'=>$one[$value]]);
                                            }
                                            else if($value == 'log_time'){
                                                echo Yii::$app->formatter->asDatetime($one[$value], 'php:Y-m-d H:i:s');
                                            }
                                            else{
                                                echo Html::encode($one[$value]);
                                            }
                                        ?>
                                    </td>
                                <?php endforeach ?>
                            </tr>
                        <?php endforeach ?>
                        </tbody>
                    </table>
                    </div>
                    <footer class="table-footer">
                        <div class="row">
                            <div class="col-md-6">
                                <?=Yii::t('app', 'pagination show page', [
                                    'totalCount' => $pagination->totalCount,
                                    'totalPage' => $pagination->getPageCount(),
                                    'perPage' => '<input type=text name=offset size=3 value='.$pagination->defaultPageSize.'>',
                                		'pageInput'=>'<input type=text name=page size=4>',
                                		'buttonGo'=>'<input type=submit value=go>',
                                ])?>
                            </div>
                            <div class="col-md-6 text-right">
                                <?= LinkPager::widget(['pagination' => $pagination]); ?>
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

