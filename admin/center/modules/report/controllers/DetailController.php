<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2017/5/27
 * Time: 15:03
 */

namespace center\modules\report\controllers;

use center\extend\Tool;
use yii;
use yii\helpers\Json;
use center\controllers\ValidateController;
use center\modules\report\models\financial\FinancialDetailMonth;

/**
 * 流量一览详情控制器
 * Class AccountantController
 * @package center\modules\report\controllers
 */
class DetailController extends ValidateController
{
    public function actionIndex()
    {
        $model = new FinancialDetailMonth();
        $data = $model->getRecentlyData();

        return $this->render('index', [
            'data' => $data
        ]);
    }

    /**
     * 获取用户组使用流量top
     * @param int $type
     * @return string
     */
    public function actionAjaxGetGroupData($type = 1)
    {
        //获取用户组缴纳top40
        $model = new FinancialDetailMonth();
        $data = $model->getGroupData($type);
        //var_dump($data);exit;

        return Json::encode($data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 获取产品缴纳费用
     * @param int $type
     * @return string
     */
    public function actionAjaxGetProductData($type = 1)
    {
        //获取上月或者上上月产品消费情况
        //获取用户组缴纳top40
        $model = new FinancialDetailMonth();
        $data = $model->getProductData($type);
        //var_dump($data);exit;

        return Json::encode($data, JSON_UNESCAPED_UNICODE);
    }


    /**
     * 获取按缴费类型缴纳情况
     * @return string
     */
    public function actionAjaxGetRecentlyData()
    {
        //获取最近30天上网情况
        $model = new FinancialDetailMonth();
        $data = $model->getRecentlyBytesData();
        //var_dump($data);exit;

        return Json::encode($data, JSON_UNESCAPED_UNICODE);
    }


    /**
     * 获取最近半年消费情况，平均消费等
     */
    public function actionAjaxGetHalfYear()
    {
        $model = new FinancialDetailMonth();
        $data = $model->getHalfYearData();

        return Json::encode($data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 单个用户财务详情
     * @param string $user_name
     * @return string
     */
    public function actionUser($user_name = '')
    {
        $model = new FinancialDetailMonth();

        $params = Yii::$app->request->queryParams;
        if (!empty($user_name)) {
            //获取产品相关信息
            $rs = $model->getUserDetail($user_name, $params);
            if (!empty($params['showType'])) {
                //var_dump($rs);exit;
                return $rs;
            }
            if ($rs['code'] != 200) {
                Yii::$app->getSession()->setFlash('error', $rs['msg']);
            }
        }

        return $this->render('user', [
            'model' => $model,
            'params' => $params,
            'data' => $rs,
        ]);

    }
}