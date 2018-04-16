<?php

namespace center\modules\employ\controllers;

use center\controllers\ValidateController;
use center\modules\student\models\StuWorksNow;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use center\modules\interfaces\models\IpBindingToken;
use center\modules\interfaces\models\IpBindingTokenSearch;
use center\modules\auth\models\AuthItemChild;
use common\models\Redis;
use common\models\User;

/**
 * BindingController implements the CRUD actions for IpBindingToken model.
 */
class  DefaultController extends ValidateController
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @return \yii\web\Response
     */
    public function actionRedirect()
    {
        if (Yii::$app->user->can('employ/default/index')) {
            return $this->redirect('index');
        }
    }

    /**
     * Lists all IpBindingToken models.
     * @return mixed
     */
    public function actionIndex()
    {
        //组装echarts
        $model = new StuWorksNow();
        $param = Yii::$app->request->post();
        $rs = $model->getRates($param);

        return $this->render('index', [
            'data' => $rs,
            'model' => $model
        ]);
    }

    /**
     * 按班统计
     * @return string
     */
    public function actionLevel()
    {
        //组装echarts
        $model = new StuWorksNow();
        $param = Yii::$app->request->post();
        $rs = $model->getRatesByLevel($param);

        return $this->render('level', [
            'data' => $rs,
            'model' => $model
        ]);
    }

    /**
     * 按专业统计
     * @return string
     */
    public function actionPosition()
    {
        //组装echarts
        $model = new StuWorksNow();
        $param = Yii::$app->request->post();
        $rs = $model->getRatesByMajor($param);

        return $this->render('position', [
            'data' => $rs,
            'model' => $model
        ]);

    }

    /**
     * 按专业统计
     * @return string
     */
    public function actionOut()
    {
        //组装echarts
        $model = new StuWorksNow();
        $param = Yii::$app->request->post();
        $rs = $model->getRatesOut($param);

        return $this->render('out', [
            'data' => $rs,
            'model' => $model,
            'params' => $param
        ]);

    }
}
