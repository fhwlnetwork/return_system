<?php
namespace center\modules\auth\controllers;

use Yii;
use center\modules\auth\models\RegionGroup;
use center\controllers\ValidateController;

class RegionController extends ValidateController
{
    public $enableCsrfValidation = false;

    /**
     * 保存最新的组织结构.
     * @return bool
     */
    public function actionNode()
    {
        $newNodeData = Yii::$app->request->getRawBody();
        $newNodeData = json_decode($newNodeData);
        //var_dump($newNodeData);exit;
        $model = $this->findModel();
        $source = $model->setOrg($newNodeData);
        Yii::$app->getSession()->setFlash('success', Yii::t('app', 'edit success.'));
        return $source;
    }

    /**
     * AJAX 请求一下数据.
     * @return string
     */
    public function actionAjax()
    {
        return RegionGroup::ajax();
    }

    /**
     * Lists all SrunJiegou models.
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * return SrunJiegou object.
     * @return SrunJiegou
     */
    protected function findModel()
    {
        return new RegionGroup();
    }
}