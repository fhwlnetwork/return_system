<?php
/**
 * Created by PhpStorm.
 * User: ligang
 * Date: 2015/1/29
 * Time: 14:57
 */

use center\widgets\Alert;

$this->title = Yii::t('app', 'field add');
$canAdd = true;
?>

<?php if ($canAdd): ?>
    <div class="page page-table">
        <?= Alert::widget() ?>
        <section class="panel panel-default">
            <div class="panel-heading">
                <strong><span class="glyphicon glyphicon-plus"></span> <?= Yii::t('app', 'add'); ?></strong>
            </div>
            <div class="panel-body">
                <?=
                $this->render('_form', [
                    'model' => $model,
                ]);
                ?>
            </div>
        </section>
    </div>
<?php endif ?>