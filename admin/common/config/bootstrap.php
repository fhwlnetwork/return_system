<?php
Yii::setAlias('common', dirname(__DIR__));
Yii::setAlias('center', dirname(dirname(__DIR__)) . '/center');
Yii::setAlias('console', dirname(dirname(__DIR__)) . '/console');
Yii::setAlias('web', dirname(dirname(__DIR__)) . '/web');
Yii::setAlias('uploads', dirname(dirname(__DIR__)) . '/web/uploads');
if (!YII_ENV_DEV) {
    Yii::setAlias('include', '/srun3/www/include');
}
Yii::setAlias('@data', dirname(dirname(__DIR__)) . '/data'); //文件上传下载目录.


//定义常量
defined('SRUN_MGR') or define('SRUN_MGR','srun_mgr');
defined('SRUN_CHECKOUT') or define('SRUN_CHECKOUT','srun_checkout');