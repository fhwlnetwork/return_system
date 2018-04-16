<?php
/**
 * Created by PhpStorm.
 * User: ligang
 * Date: 2015/1/29
 * Time: 14:57
 */

use center\widgets\Alert;

$this->title = \Yii::t('app', 'field edit');
$canEdit = true;

?>

<?php if($canEdit): ?>
<div class="page page-table">
    <?= Alert::widget() ?>
    <section class="panel panel-default" data-ng-controller="packageController" >
        <div class="panel-heading">
            <strong><span class="glyphicon glyphicon-edit"></span> <?= Yii::t('app', 'edit');?></strong>
        </div>
        <div class="panel-body">
        <?php
            echo $this->render('_form', [
                'model' => $model,
            ]);
        ?>
        </div>
    </section>
</div>
<?php endif?>