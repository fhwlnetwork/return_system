<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2018/3/10
 * Time: 8:59
 */

namespace center\modules\message\controllers;

use center\modules\message\models\WorkInformation;
use center\modules\product\models\MajorWorkRelation;
use yii;
use center\controllers\ValidateController;
use yii\web\NotFoundHttpException;

/**
 * 招聘控制器
 * Class DefaultController
 * @package center\modules\message\controllers
 */
class WorkController extends ValidateController
{
    public function actionRedirect()
    {
        if (Yii::$app->user->can('message/default/index')) {
            return $this->redirect('index');
        }
    }

    /**
     * 发布招聘工作中心
     * @return string
     */
    public function actionIndex()
    {
        $model = new WorkInformation();
        $attributes = $model->getAttributesList();
        $major = isset($attributes['major']) ? $attributes['major'] : [];
        $params = Yii::$app->request->queryParams;
        $listRs = $model->getList($params);
        $list = isset($listRs['data']) ? $listRs['data'] : [];
        $pagination = isset($listRs['pagination']) ? $listRs['pagination'] : [];

        return $this->render('index', [
            'model' => $model,
            'list' => $list,
            'major' => $major,
            'pagination' => $pagination,
            'params' => $params
        ]);
    }

    /**
     * 发布招聘信息
     * @return yii\web\Response
     */
    public function actionAdd()
    {
        $model = new WorkInformation();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $rs = $model->save();
            if ($rs) {
                Yii::$app->getSession()->setFlash('success', '添加招聘信息成功');
            } else {
                Yii::$app->getSession()->setFlash('success', '添加招聘信息失败');
            }
        }

        return $this->redirect('index');
    }

    /**
     * 编辑招聘信息
     * @param $id
     * @return string|yii\web\Response
     */
    public function actionEdit($id)
    {
        $model = $this->findModel($id);
        $attributes = $model->getAttributesList();
        $major = isset($attributes['major']) ? $attributes['major'] : [];
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('_form', [
                'model' => $model,
                'action' => 'edit',
                'major' => $major,
            ]);
        }
    }
    /**
     * 查看招聘信息
     * @param $id
     * @return string|yii\web\Response
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $attributes = $model->getAttributesList();
        $major = isset($attributes['major']) ? $attributes['major'] : [];
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('_form', [
                'model' => $model,
                'action' => 'view',
                'major' => $major
            ]);
        }
    }
    /**
     * 删除招聘
     * @param $id
     * @return yii\web\Response
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * @param $major_id
     * @return string
     */
    public function actionWorks($major_id)
    {
        try {
            $data = MajorWorkRelation::find()
                ->where(['major_id' => $major_id])
                ->indexBy('id')
                ->asArray()
                ->all();
            if ($data) {
                $rs = ['code' => 1, 'msg' => 'ok', 'data' => $data];
            } else {
                $rs = ['code' => 404, 'msg' => '没有工作相关，请添加'];
            }
        } catch(\Exception $e) {
            $rs = ['code' => 500, 'msg' => '获取招聘信息异常'];
        }

        return json_encode($rs);
    }

    /**
     * Finds the MajorWorkRelation model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return WorkInformation the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = WorkInformation::findOne($id)) !== null) {
            return $model;
        } else{
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

}