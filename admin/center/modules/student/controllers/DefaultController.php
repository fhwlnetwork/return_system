<?php

namespace center\modules\student\controllers;

use yii;
use yii\web\NotFoundHttpException;
use center\modules\student\models\Manager;
use center\controllers\ValidateController;


class DefaultController extends ValidateController
{
    public function actionRedirect()
    {
        if (Yii::$app->user->can('student/default/index')) {
            return $this->redirect('index');
        }
    }

    /**
     * 学生管理首页
     * @return string
     */
    public function actionIndex()
    {
        $model = new Manager();
        $param = Yii::$app->request->queryParams;
        $listRs = $model->getList($param);
        $list = isset($listRs['data']) ? $listRs['data'] : [];
        $pagination = isset($listRs['pagination']) ? $listRs['pagination'] : [];
        $param['showField'] = $model->showField;

        return $this->render('index', [
            'list' => $list,
            'pagination' => $pagination,
            'model' => $model,
            'params' => $param,
            'showField' => $model->showField
        ]);
    }

    public function actionView($id = null)
    {
        $id = $id ? $id : Yii::$app->user->identity->getId();
        $model = $this->findModel($id);
        $works = $model->getWorkHistory($model->id);
        $pubs = $model->getStuPub($model->id);
        $status  = [
            '' => '全部',
            0 => '待审核',
            1 => '审核通过',
            2 => '未通过'
        ];

        return $this->render('view', [
            'model' => $model,
            'works' => $works['data'] ? $works['data'] : [],
            'pubs' => $pubs['data'] ? $pubs['data'] : [],
            'status' => $status,
            'id' => $id
        ]);

    }
    /**
     * Finds the MajorWorkRelation model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Manager the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Manager::findOne($id)) !== null) {
            return $model;
        } else{
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
