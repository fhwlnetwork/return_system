<?php

namespace center\controllers;


use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use center\modules\report\models\detail\BaseModel;

/**
 * 系统运维
 * @package center\modules\report\controllers
 */
class SystemController extends Controller
{
    public $layout = 'system';

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'login', 'index', 'create-pdf'],
                'rules' => [
                    [
                        'actions' => ['index', 'create-pdf'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                ],
            ],
        ];
    }

    //系统数据
    public function actionIndex($ip = '127.0.0.1', $type = 'cpu')
    {
        $model = new BaseModel();
        $model->timePoint = 6;
        $model->setTime();
        $model->device_ip = $ip;
        $model->sql_type = $type;
        $source = $model->getSourceByType($type);

        if (!empty($data) && $data['code'] != 200) {
            Yii::$app->getSession()->setFlash('error', $data['msg']);
        }

        echo $this->render('create-pdf', [
            'model' => $model,
            'data' => $data,
            'source' => $source,
            'unit' => ($model->sql_type == 'cpu') ? '%' : '',

        ]);
        exit;
    }
}
