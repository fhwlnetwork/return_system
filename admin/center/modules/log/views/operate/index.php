<?php
use yii\widgets\LinkPager;
use yii\helpers\Html;
use center\widgets\Alert;

$this->title = \Yii::t('app', 'Operate Log');

$canExport = Yii::$app->user->can('user/operate/export');

$url = (!empty($params))? '/log/operate/index?'.http_build_query($params).'&export=true' : '/log/operate/index?export=true';

/**
 * $this yii\web\View
 */
?>
<style>
    .positions {
        position:absolute;
        left : 100px;
        bottom : 35px;
    }
</style>
<div class="page">
    <?= Alert::widget() ?>
    <div class="row">
        <div class="col-md-3" >
            <div class="panel panel-default">
                <div class="panel-heading"><strong><span class="glyphicon glyphicon-search"></span> <?= Yii::t('app',
                            'search') ?></strong></div>
                <div class="panel-body">
                    <form name="form_constraints" action="<?= \yii\helpers\Url::to(['index']) ?>"
                          class="form-horizontal form-validation" method="get">
                        <?php
                        if ($model->searchInput) {
                            $searchInput = $model->searchInput;
                            foreach ($searchInput as $key => $value) {
                                echo '<div class="form-group">';
                                if(isset($value['type']) && $value['type']=='dropList'){
                                    $content = Html::dropDownList($key, isset($params[$key]) ? $params[$key] : '', \center\modules\log\models\Operate::getAttributesList()[$key], ['class'=>'form-control']);
                                }else{
                                    $content = Html::input('text', $key, isset($params[$key]) ? $params[$key] : '', [
                                        'class' => (isset($value['type']) && $value['type'] == 'date') ? 'form-control inputDateTime' : 'form-control',
                                        'placeHolder' => isset($value['label']) ? $value['label'] : '',
                                    ]);
                                }

                                echo Html::tag('div', $content, ['class' => 'col-md-12']);
                                echo '</div>';
                            }
                        }
                        ?>
                        <?= Html::submitButton(Yii::t('app', 'search'), ['class' => 'btn btn-success']) ?>

                    </form>
                    <?php if ($canExport):?>
                        <?= Html::submitButton(Yii::t('app', 'export'), ['class' => 'btn btn-info positions', 'id'=> 'batchExportLog', 'onclick'=>'batchExportLog()'])?>
                    <?php endif;?>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <section class="panel panel-default" style="background-color:#f6f6f6;">
                <?php if (!empty($listContent)): ?>
                    <div class="page">
                        <div class="side-timline-container">
                            <section class="side-timeline" id="showList">
                                <?=$listContent?>
                            </section>
                        </div>
                    </div>
                    <footer class="table-footer">
                        <div class="row">
                            <div class="col-sm-12" style="text-align: center; padding-bottom: 30px;">
                                <a href="javascript:void(0)" onclick="showMore()" id="showMoreText"
                                    <?php if($model->lastId == 0): ?>style="display: none;"<?php endif?>><?=Yii::t('app', 'operate help1')?></a>
                                <span id="noMoreText" <?php if($model->lastId!=0): ?>style="display: none;"<?php endif?>><?=Yii::t('app', 'operate help2')?></span>
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
</div>
<?php
$url = Yii::$app->request->getUrl();
$this->registerJs('
    searchUrl = "'.$url.'";
    last_id="'.$model->lastId.'";
    key="'.$model->sessionKey.'";
');

//$this->registerJsFile('js/operate.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
?>
<script>
    function batchExportLog()
    {
        var operator = document.getElementsByName('operator')[0].value;
        var target = document.getElementsByName('target')[0].value;
        var action = document.getElementsByName('action')[0].value;
        var start_opt_time = document.getElementsByName('start_opt_time')[0].value;
        var end_opt_time = document.getElementsByName('end_opt_time')[0].value;
        var opt_ip = document.getElementsByName('opt_ip')[0].value;

        if(window.confirm("<?=Yii::t('app', 'batch export log help')?>")){
            $.ajax({
                type:"POST",
                url:"<?=\yii\helpers\Url::to(['index'])?>",
                data:{'operator':operator,'action':action, 'target':target, 'start_opt_time': start_opt_time,  'end_opt_time': end_opt_time,'opt_ip': opt_ip, 'export': true, },
                dataType:'json',
                success:function (){
                    window.location.reload();//刷新当前页面.
                }
            });
        }
    }
</script>

