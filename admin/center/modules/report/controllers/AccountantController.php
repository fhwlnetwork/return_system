<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2017/5/27
 * Time: 15:03
 */

namespace center\modules\report\controllers;

use yii\helpers\Json;
use center\controllers\ValidateController;
use center\modules\report\models\financial\FinancialDetail;

/**
 * 支付一览详情控制器
 * Class AccountantController
 * @package center\modules\report\controllers
 */
class AccountantController extends ValidateController
{
    public function actionIndex()
    {
        $model = new FinancialDetail();
        $data = $model->getPayOrCheckoutData();

        return $this->render('index', [
            'data' => $data
        ]);
    }

    /**
     * 获取用户组缴纳费用
     * @param int $type
     * @param string $pay
     * @return string
     */
    public function actionAjaxGetGroupData($type = 1, $pay = 'pay_list')
    {
        //获取用户组缴纳top40
        $model = new FinancialDetail();
        $data = $model->getGroupData($type, $pay);
        //var_dump($data);exit;

        return Json::encode($data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 获取产品缴纳费用
     * @param int $type
     * @param string $pay
     * @return string
     */
    public function actionAjaxGetProductData($type = 1, $pay = 'transfer')
    {
        //获取上月或者上上月产品消费情况
        //获取用户组缴纳top40
        $model = new FinancialDetail();
        $data = $model->getProductData($type, $pay);
        //var_dump($data);exit;

        return Json::encode($data, JSON_UNESCAPED_UNICODE);
    }


    /**
     * 获取按缴费类型缴纳情况
     * @param int $type
     * @return string
     */
    public function actionAjaxGetTypeData($type = 1)
    {
        //根据pay_type
        //获取上月或者上上月产品消费情况
        //获取用户组缴纳top40
        $model = new FinancialDetail();
        $data = $model->getPayTypeData($type);
        //var_dump($data);exit;

        return Json::encode($data, JSON_UNESCAPED_UNICODE);
    }


    /**
     * 获取最近半年消费情况，平均消费等
     */
    public function actionAjaxGetHalfYear($type = 'pay_list')
    {
        $model = new FinancialDetail();
        $data = $model->getHalfYearData($type);

        return Json::encode($data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 结算一览
     * @return string
     */
    public function actionCheckout()
    {
        //本月结算
        $model = new FinancialDetail();
        $data = $model->getPayOrCheckoutData('checkout_list');

        return $this->render('checkout', [
            'data' => $data
        ]);
    }
}