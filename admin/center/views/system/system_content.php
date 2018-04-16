<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2017/6/22
 * Time: 11:48
 */
?>

<?php if (!$show): ?>
    <div class="page row" style="margin:0;padding:0;">

        <div class="col-md-12 col-sm-12 col-lg-12">
            <div class="col-md-12 col-sm-12 col-lg-12">
                    <?php if ($data['single']): ?>
                        <?= $this->render('/map/system-single', [
                            'data' => $data,
                            'unit' => $unit,
                            'save' => $save,
                            'model' => $model
                        ]) ?>
                    <?php else : ?>
                        <?= $this->render('/map/system-multi', [
                            'data' => $data
                        ]) ?>
                    <?php endif; ?>

            </div>
        </div>

    </div>
<?php endif; ?>
<div class="page row" style="margin:0;padding:0;">
    <section class="panel panel-default table-dynamic">
        <div class="panel-heading data-center"><strong><span
                    class="glyphicon glyphicon-th-large"></span> <?= $this->title ?>(<?=
                $model->start_time . '--' . $model->stop_time;
                ?>)
            </strong>
        </div>
        <?php if ($data['single']) : ?>
        <table class="table table-bordered mytable" cellpadding="0" cellspacing="0" style="height: auto;">
            <?php else: ?>
            <table class="table table-bordered  table-responsive ">
                <?php endif; ?>
                <thead>
                <tr>
                    <?php if ($data['single']) : ?>
                        <?php foreach ($data['table']['header'] as $v) : ?>
                            <th>
                                <div class='th'><?= $v; ?></div>
                            </th>
                        <?php endforeach ?>
                    <?php else: ?>
                        <th>
                            <div class='th'><?= Yii::t('app', 'action'); ?></div>
                        </th>
                        <?php foreach ($data['table']['top_header'] as $v) : ?>
                            <th>
                                <div class='th'><?= $v; ?></div>
                            </th>
                        <?php endforeach ?>
                    <?php endif; ?>
                </tr>
                </thead>
                <tbody>
                <?php if ($data['single']) : ?>
                    <?php foreach ($data['table']['data'] as $one) : ?>
                        <tr>
                            <?php foreach ($one as $v) : ?>
                                <td><?= $v; ?></td>
                            <?php endforeach ?>
                        </tr>
                    <?php endforeach ?>
                <?php else: ?>
                <?php $i = 0;
                foreach ($data['table']['data'] as $date => $one) : ?>
                <tr bgcolor="<?php echo $i % 2 == 1 ? "#fff" : '#f1f1f1' ?>">
                    <td><span id="product_key_<?= $i ?>" onclick="chgBreak('<?= $i ?>')"
                              data-ng-click='product_key_<?= $i ?> = !product_key_<?= $i ?>'
                              class="glyphicon glyphicon-plus" style="cursor: pointer"></span>
                    </td>
                    <?php foreach ($one['data'] as $v) : ?>
                        <td><?= $v; ?></td>
                    <?php endforeach ?>
                </tr>
                <tr data-ng-show="product_key_<?= $i ?>">
                    <td colspan="7">
                        <table class="table table-bordered table-striped table-responsive">
                            <tr>
                                <?php foreach ($data['table']['detail_header'] as $v) : ?>
                                    <th>
                                        <div class='th'><?= $v; ?></div>
                                    </th>
                                <?php endforeach ?>
                            </tr>
                            <?php foreach ($one['detail'] as $vv) : ?>
                                <tr>
                                    <?php foreach ($vv as $vvv) : ?>
                                        <td><?= $vvv; ?></td>
                                    <?php endforeach ?>
                                </tr>
                            <?php endforeach ?>
                        </table>
                        <?php $i++;
                        endforeach ?>
                        <?php endif; ?>
                </tbody>
            </table>
    </section>
</div>

<script>
    function chgBreak(id) {
        var obj = $('#product_key_' + id);
        var className = obj.attr('class');
        if (className.indexOf('plus') != -1) {
            obj.attr('class', 'glyphicon glyphicon-minus')
        } else {
            obj.attr('class', 'glyphicon glyphicon-plus')
        }

    }
</script>
