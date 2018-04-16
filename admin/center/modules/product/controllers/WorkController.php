<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2018/3/9
 * Time: 20:40
 */

namespace center\modules\product\controllers;

use center\modules\product\models\Major;
use center\modules\student\models\StuPubCenter;
use center\modules\student\models\StuWorks;
use center\modules\student\models\StuWorksNow;
use yii;
use common\models\User;

class WorkController extends \center\controllers\ValidateController
{
    /**
     * 添加工作经历
     * @return string
     */
    public function actionAdd()
    {
        $model = new StuWorks();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $rs = $model->save();
            if ($rs) {
                Yii::$app->getSession()->setFlash('success', '增加工作经历成功');
            } else {
                Yii::$app->getSession()->setFlash('error', '增加工作经历失败');
            }
        }
        $major = Major::getMajor();

        return $this->render('_form', [
            'model' => $model,
            'major' => $major,
            'action' => 'add'
        ]);
    }


    /**
     * @return string
     */
    public function actionList()
    {
        $model = new StuWorksNow();
        $params = Yii::$app->request->queryParams;
        $listRs = $model->getList($params);
        $list = isset($listRs['data']) ? $listRs['data'] : [];
        $pagination = isset($listRs['pagination']) ? $listRs['pagination'] : [];

        return $this->render('list', [
            'model' => $model,
            'list' => $list,
            'pagination' => $pagination
        ]);
    }

    public function actionEdit($id)
    {
        $model = StuWorks::findOne($id);
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $rs = $model->save();
            if ($rs) {
                Yii::$app->getSession()->setFlash('success', '编辑工作经历成功');
            } else {
                Yii::$app->getSession()->setFlash('error', '编辑工作经历失败');
            }
        }
        $model->stime = date('Y-m-d', $model->stime);
        $major = Major::getMajor();

        return $this->render('_form', [
            'model' => $model,
            'major' => $major,
            'action' => 'edit'
        ]);
    }

    /**
     * 编辑发布
     * @param $id
     * @return string
     */
    public function actionPubEdit($id)
    {
        $model = StuPubCenter::findOne($id);

        return $this->render('/default/works-add', [
            'model' => $model,
            'action' => 'edit'
        ]);
    }

    /**
     * 查看发布信息
     * @param $id
     * @return string
     */
    public function actionPubView($id)
    {
        $model = StuPubCenter::findOne($id);

        return $this->render('/default/works-add', [
            'model' => $model,
            'action' => 'view'
        ]);
    }

    /**
     * 审核用户发布
     * @return string
     */
    public function actionCheck($id, $status, $remark = '')
    {
        $model = StuPubCenter::findOne($id);
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

            return $this->redirect('/product/default/works');
        }


       return $this->redirect('pub-edit');
    }
}