<?php
//use yii;
use yii\widgets\LinkPager;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use center\widgets\Alert;

echo $this->render('/layouts/operate-menu');

$this->title = Yii::t('app', 'report/error/user-login');
?>
<div class="panel panel-default">
    <div class="panel-body" style="padding: 10px">
        <?php
        $form = ActiveForm::begin([
            'layout' => 'horizontal',
            'fieldConfig' => [
                'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}"
            ],
            'method'=>'get',
            'action'=>'user-login'
        ]);
        ?>

        <div class="col-md-2">
            <input type="text" name = 'start_At' value="<?=isset($params['start_At']) ? $params['start_At'] : date('Y-m-d')?>" class="form-control inputDate" placeholder="<?=Yii::t('app', 'start time')?>">
        </div>

        <div class="col-md-2">
            <input type="text" name = 'stop_At' value="<?=isset($params['stop_At']) ? $params['stop_At'] : ''?>" class="form-control inputDate" placeholder="<?=Yii::t('app', 'end time')?>">
        </div>

        <div class="col-md-2">
            <?php
            echo '<select class="form-control" name="err_msg">';
            foreach(\center\modules\log\models\Login::getAttributesList()['error_message'] as $key => $val) {
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

        <div class="col-md-1">
            <?= Html::submitButton(Yii::t('app', 'search'), ['class' => 'btn btn-line-info']) ?>
        </div>

        <div class="col-sm-12" style="text-align: left;color: #ffffff;">
            <?= $form->errorSummary($model); ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
<div class="page">
    <?= Alert::widget() ?>
    <div class="row">
        <div class="col-md-12">
            <section class="panel panel-default table-dynamic">
            <?php if (!empty($list['data'])) : ?>
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th><?= Yii::t('app', 'account') ?></th>
                        <th><?= Yii::t('app', 'total') ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($list['data'] as $k => $one) { ?>
                        <tr>
                            <td><?= $k + 1 ?></td>
                            <td><?= Html::a(Html::encode($one['user_name']),['/user/base/view?user_name='.Html::encode($one['user_name'])]) ?></td>
                            <td><?= Html::encode($one['num']) ?></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>

                <footer class="table-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <?=Yii::t('app', 'pagination show1', [
                                'totalCount' => $list['page']->totalCount,
                                'totalPage' => $list['page']->getPageCount(),
                                'perPage' => $list['page']->pageSize,
                            ])?>
                        </div>
                        <div class="col-md-6 text-right">
                            <?php
                            echo LinkPager::widget(['pagination'=>$list['page']]);
                            ?>
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