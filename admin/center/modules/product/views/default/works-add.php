<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2018/3/9
 * Time: 20:42
 */
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use center\widgets\Alert;

$this->title = Yii::t('app', 'product/default/works-add');

echo $this->render('/layouts/menu');
//权限
$canCheck = Yii::$app->user->can('product/work/pub-check');


$canList = Yii::$app->user->can('user/interaction/index');
$canEdit = Yii::$app->user->can('user/interaction/edit');
$canListAll = Yii::$app->user->can('user/interaction/index-all');

if ($action != 'add') {
    $this->title = \Yii::t('app', '文章编辑');
}
/* @var $this yii\web\View */
/* @var $model center\modules\user\models\UserCloundComplaints */
/* @var $form yii\widgets\ActiveForm */
?>

    <div class="col-md-12">
        <?= Alert::widget() ?>
        <div class="panel-heading"><strong>
                <?php if ($action == 'edit') {
                    echo '<span class="glyphicon glyphicon-edit"></span> ';
                    echo Yii::t('app', 'edit');
                } else if ($action == 'view' || $action == 'view-all') {
                    echo '<span class="glyphicon glyphicon-check"></span> ';
                    echo Yii::t('app', 'view');
                } else if ($action == 'add') {
                } ?>
            </strong></div>

        <div class="panel panel-default">
            <div class="panel-body">
                <?php if ($action == 'view' || $action == 'edit'): ?>
                    <?php $form = ActiveForm::begin([
                        'layout' => 'horizontal',
                        'options' => ['enctype' => 'multipart/form-data'],
                        'fieldConfig' => [
                            'template' => "{label}\n{beginWrapper}\n{input}\n{error}\n{hint}\n{endWrapper}",
                            'horizontalCssClasses' => [
                                'label' => 'col-sm-2',
                                'offset' => 'col-sm-offset-4',
                                'wrapper' => 'col-sm-8',
                                'error' => '',
                                'hint' => '',
                            ],
                        ],
                    ]); ?>
                <?php else: ?>
                    <?php $form = ActiveForm::begin([
                            'action' => yii\helpers\Url::to('works-add'),
                            'options' => ['enctype' => 'multipart/form-data'],
                            'layout' => 'horizontal',
                            'fieldConfig' => [
                                'template' => "{label}\n{beginWrapper}\n{input}\n{error}\n{hint}\n{endWrapper}",
                                'horizontalCssClasses' => [
                                    'label' => 'col-sm-2',
                                    'offset' => 'col-sm-offset-4',
                                    'wrapper' => 'col-sm-8',
                                    'error' => '',
                                    'hint' => '',
                                ],
                            ],]
                    ); ?>
                <?php endif; ?>
                <?= $form->field($model, 'title') ?>
                <?= $form->field($model, 'desc') ?>
                <?= $form->field($model, 'content')->textarea() ?>
                <?php if ($action != 'add'): ?>
                    <div class="form-group field-news-pic">
                        <label class="control-label col-sm-2" for="news-pic">文章图片</label>
                        <div class="col-sm-8">
                            <img src="/<?= $model->pic ?>" alt="" style="width:200px;height:200px;">
                            <?php if ($action == 'edit'): ?>
                                <input type="file" id="news-pic" name="News[pic]">
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else : ?>
                    <?= $form->field($model, 'pic')->fileInput() ?>
                <?php endif; ?>

                <div class="form-group" style="margin-left:220px;">
                    <?php if ($action == 'add'): ?>
                        <?= Html::submitButton(Yii::t('app', 'save'), ['class' => 'btn btn-success']) ?>
                    <?php endif; ?>
                    <?php if ($canList && $action == 'view'): ?>
                        <?= Html::a(Html::button(Yii::t('app', '返回'), ['class' => 'btn btn-primary']),
                            ['/product/default/works']
                        ) ?>
                    <?php endif; ?>
                    <?php if ($canEdit && $action == 'edit'): ?>
                        <?= Html::submitButton(Yii::t('app', 'save'), ['class' => 'btn btn-success']) ?>
                    <?php endif; ?>
                    <?php if ($canListAll && $action == 'edit' || $action == 'view-all'): ?>
                        <?= Html::a(Html::button(Yii::t('app', '返回'), ['class' => 'btn btn-primary']),
                            ['/product/default/works']
                        ) ?>
                    <?php endif; ?>
                    <?php if ($canCheck && $action == 'edit' && $model->status == 0): ?>
                        <?= Html::a(Html::button(Yii::t('app', '通过审核'), ['class' => 'btn btn-warning']),
                            ['/product/work/check', 'id' => $model['id'], 'status' => 1],
                            ['title' => Yii::t('app', '审核')]);?>
                        <?= Html::button(Yii::t('app', '不通过'), ['class' => 'btn btn-warning btn-danger uncheck',]);?>
                    <?php endif;?>
                </div>
            </div>
        </div>

        <?php ActiveForm::end(); ?>

    </div>
    <div class="modal fade col-sm-offset-2" id="myModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <button type="button" class="btn btn-primary">不通过原因</button>
                    <button type="button" class="btn btn-info pull-right" data-dismiss="modal">关闭</button>
                </div>
                <div class="modal-body" id="stage1_content" style="height: 200px;">
                    <textarea name="" id="uncheck" style="width:100%; height: 100px;"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary sure">确定</button>
                    <button type="button" class="btn btn-info pull-right" data-dismiss="modal">关闭</button>
                </div>
            </div>
        </div>
    </div>
<?php
$isNew = $model->getIsNewRecord();
$js = <<<JS
 $(document).ready(function() {
     if ('$action' == 'view') {
         $('.form-control').css('border', 'none');
     }
     
      if ('$action' == 'edit') {
          $('.uncheck').click(function () {
                
            $('#myModal').modal({backdrop: 'static', keyboard: false});
        });
          $('.sure').click(function() {
              var remark = $('#uncheck').val();
              if (remark) {
                  var id = '{$model->id}';
                  var status = 2;
                  $.get('/product/work/check', {'id': id, 'status' : status, 'remark' : remark}, function (res) {
                      res = eval('('+res+')');
                      layer.msg(res.msg);
                      location.reload();
                  })
              } else {
                  layer.msg('不通过原因不能为空');
              }
          })
     }
 })
JS;
$this->registerJs($js);

