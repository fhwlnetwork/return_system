<?php
namespace center\modules\auth\controllers;

use Yii;
use center\controllers\ValidateController;

class ShowController extends ValidateController
{
    /**
     * 详细讲述权限的使用方法.
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }
}
