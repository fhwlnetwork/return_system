<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2018/3/9
 * Time: 20:40
 */

namespace  center\modules\product\controllers;

use yii;
use common\models\User;

class DefaultController extends \center\controllers\ValidateController
{
    /**
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * 学生基本信息
     * @return string
     */
    public function actionBase()
    {
        if (User::isRoot()) {
            $param = Yii::$app->request->queryParams;

            return $this->render('list');
        } else {
            return $this->render('base');
        }
    }

    public function actionHistory()
    {
        return $this->render('history');
    }

}