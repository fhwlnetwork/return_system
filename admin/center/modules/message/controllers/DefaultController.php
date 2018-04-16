<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2018/3/10
 * Time: 8:59
 */

namespace center\modules\message\controllers;

use center\modules\message\models\Message;
use center\modules\product\models\MajorWorkRelation;
use yii;
use center\controllers\ValidateController;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * 招聘控制器
 * Class DefaultController
 * @package center\modules\message\controllers
 */
class DefaultController extends ValidateController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }
    public function actionRedirect()
    {
        if (Yii::$app->user->can('message/default/index')) {
            return $this->redirect('index');
        }
    }

    /**
     * 发布新闻工作中心
     * @return string
     */
    public function actionIndex()
    {
        $model = new Message();
        $params = Yii::$app->request->queryParams;
        $listRs = $model->getList($params);
        $list = isset($listRs['data']) ? $listRs['data'] : [];
        $pagination = isset($listRs['pagination']) ? $listRs['pagination'] : [];

        return $this->render('index', [
            'model' => $model,
            'list' => $list,
            'pagination' => $pagination,
            'params' => $params
        ]);
    }

    /**
     * 审核评论
     * @return yii\web\Response
     */
    public function actionCheck($id, $status, $remark = '')
    {
        $model = Message::findOne($id);

        if ($status == 2 && empty($remark)) {
            Yii::$app->getSession()->setFlash('error', '备注原因不能为空');
        } else {
            $isAjax = Yii::$app->request->isAjax;
            $model->status = $status;
            $model->mid = Yii::$app->user->identity->getId();
            $model->operator = Yii::$app->user->identity->username;
            $model->remark = $remark;
            $rs = $model->save(false);
            if ($rs) {
                Yii::$app->getSession()->setFlash('success', '审核成功');
            } else {
                Yii::$app->getSession()->setFlash('error', '审核失败');
            }

            if ($isAjax) {
                $code = $rs ? 1 : 0;
                $msg = $rs ? '成功' : '失败';

                return json_encode(['code' => $code, 'msg' => $msg]);
            }

            return $this->redirect('index');
        }
    }

    /**
     * @param $id
     * @return string|yii\web\Response
     */
    public function actionEdit($id)
    {
        $model = $this->findModel($id);
        $attributes = $model->getAttributesList();
        $status = isset($attributes['status']) ? $attributes['status'] : [];
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('_form', [
                'model' => $model,
                'action' => 'edit',
                'status' => $status
            ]);
        }
    }

    /**
     * 查看文章
     * @param $id
     * @return string|yii\web\Response
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $attributes = $model->getAttributesList();
        $types = isset($attributes['types']) ? $attributes['types'] : [];
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('_form', [
                'model' => $model,
                'action' => 'view',
                'types' => $types
            ]);
        }
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
        } catch (\Exception $e) {
            $rs = ['code' => 500, 'msg' => '获取招聘信息异常'];
        }

        return json_encode($rs);
    }

    /**
     * 删除留言s
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
     * Finds the MajorWorkRelation model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Message the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Message::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

}