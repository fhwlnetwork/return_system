<?php
/**
 * Created by PhpStorm.
 * User: DM
 * Date: 17/4/19
 * Time: 16:22
 */

namespace center\modules\log\controllers;

use center\controllers\ValidateController;
use center\modules\log\models\Bills;
use Yii;

class BillsController extends ValidateController
{
    // 首页展示
    public function actionIndex(){
        $get = Yii::$app->request->get();

        return $this->render('index',(new Bills())->getBills($get));
    }
}