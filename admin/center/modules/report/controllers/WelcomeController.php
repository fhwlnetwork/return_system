<?php
/**
 * Created by PhpStorm.
 * User: ligang
 * Date: 2015/4/1
 * Time: 19:03
 */

namespace center\modules\report\controllers;

use center\controllers\ValidateController;

/**
 * Class WelcomeController
 * @package center\modules\report\controllers
 */
class WelcomeController extends ValidateController
{
    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ]
        ];
    }

    public function actionIndex()
    {
        try {
            return $this->render('index');
        } catch (\Exception $e) {
            var_dump($e->getMessage());exit;
        }

    }
}