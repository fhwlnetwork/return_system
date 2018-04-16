<?php foreach (Yii::$app->session->getAllFlashes() as $key => $message) { ?>
    <div class="alert alert-<?= $key ?>" role="alert">
        <button type="button" class="close" data-dismiss="alert">
            <span aria-hidden="true">&times;</span><span class="sr-only">Close</span>
        </button>
        <i class="glyphicon glyphicon-volume-up"></i>&nbsp;&nbsp;<?= $message[0]; ?>
    </div>
<?php } ?>