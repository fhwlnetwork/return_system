<?php

namespace center\modules\report\controllers;

use Yii;
use center\modules\report\models\OnlineReportPoint;
use center\modules\report\models\OnlineReportProducts;
use center\modules\report\models\OnlineReportBilling;
use center\modules\report\models\OnlineReportControl;
use center\modules\report\models\OnlineReportClassName;
use center\modules\report\models\OnlineReportOsName;
use center\modules\report\models\SrunDetailDay;
use center\modules\strategy\models\Product;
use center\modules\strategy\models\Billing;
use center\modules\strategy\models\Control;

/**
 * Class OperateController 运营报表
 * @package center\modules\report\controllers
 */
class OnlineController extends \center\controllers\ValidateController
{
    /**
     * 在线数统计
     * @return string
     */
    public function actionIndex()
    {
        $model = new OnlineReportPoint();
        $get = Yii::$app->request->queryParams;
        if (isset($get['export'])) {
            $model->export();
            exit;
        }
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if (isset(Yii::$app->request->post()['timePoint'])) {
                $timePoint = Yii::$app->request->post()['timePoint'];
                switch ($timePoint) {
                    case 'Yesterday':
                        $model->unit = 'minutes';
                        $model->step = 10;
                        $model->start_At = date('Y-m-d', strtotime('-1 days'));
                        $model->stop_At = date('Y-m-d', strtotime('-1 days'));
                        break;
                    case 'Today':
                        $model->unit = 'minutes';
                        $model->step = 10;
                        $model->start_At = date('Y-m-d');
                        $model->stop_At = date('Y-m-d');
                        break;
                    case 'week':
                        $model->unit = 'minutes';
                        $model->step = 5;
                        $model->start_At = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') - date('w') + 1, date('Y')));
                        $model->stop_At = date('Y-m-d');

                        break;
                }
            }
        }
        $flag = false;
        if ($model->start_At == $model->stop_At) {
            $flag = true;
            $source = $model->getOnline($model, 'single');
        } else {
            $source = $model->getOnline($model);
        }
        //var_dump($source);exit;

        Yii::$app->session->set('data', $source['table']);
        Yii::$app->session->set('detail', $source['detail']);

        return $this->render('index-page', [
            'model' => $model,
            'source' => $source,
            'flag' => $flag
        ]);
    }

    //产品分布
    public function actionProduct()
    {
        $model = new OnlineReportProducts();
        $product = new Product();
        $productArray = $product->getList();
        $showField = [];
        //产品数据
        if ($productArray) {
            foreach ($productArray as $val) {
                $showField[$val['products_id']] = $val['products_name'];
            }
        }
        //对请求的方式进行处理
        $post = Yii::$app->request->post();
        $source = [];
        if (!empty($post)) {
            if ($model->load($post) && $model->validate() && $model->validateField()) {
                //如果搜索的产品没有勾选, 则查询所有产品.
                if (isset(Yii::$app->request->post()['OnlineReportProducts']['showField'])) {
                    $searchField = Yii::$app->request->post()['OnlineReportProducts']['showField'];
                    Yii::$app->session->set('online_product', $post['OnlineReportProducts']['showField']);
                    foreach ($searchField as $val) {
                        $fieldArray[$val] = $showField[$val];
                    }
                } else {
                    $fieldArray = $showField;
                }
                $source = $model->peoples($fieldArray);
            }
        } else {
            $model->type = 'count';
            $model->start_At = $model->stop_At = date('Y-m-d');
            $fieldArray = $showField;
            $source = $model->peoples($showField);
        }

        //var_dump($model->flag);exit;
        Yii::$app->session->set('searchProductField', $fieldArray);
        $attributes = OnlineReportProducts::getAttributesList()['type'];
        // var_dump($source);exit;

        return $this->render('product-page', [
            'model' => $model,
            'source' => $source,
            'showField' => $showField,
            'attributes' => $attributes
        ]);
    }

    //计费策略分布
    public function actionBilling()
    {
        $model = new OnlineReportBilling();
        $billing = new Billing();
        $billingArray = $billing->getList();
        //计费策略数据
        if ($billingArray) {
            foreach ($billingArray as $val) {
                $showField[$val['billing_id']] = $val['billing_name'];
            }
        }
        $post = Yii::$app->request->post();
        $source = [];
        if (!empty($post)) {
            if ($model->load($post) && $model->validate() && $model->validateField()) {
                //如果搜索的产品没有勾选, 则查询所有产品.
                if (isset(Yii::$app->request->post()['OnlineReportBilling']['showField'])) {
                    $searchField = Yii::$app->request->post()['OnlineReportBilling']['showField'];
                    Yii::$app->session->set('online_billing', $post['OnlineReportBilling']['showField']);
                    foreach ($searchField as $val) {
                        $fieldArray[$val] = $showField[$val];
                    }
                } else {
                    $fieldArray = $showField;
                }
                $source = $model->peoples($fieldArray); //在线产品使用人数分布
            }
        } else {
            $model->start_At = $model->stop_At = date('Y-m-d');
            $model->type = 'count';
            $fieldArray = $showField;
            $source = $model->peoples($showField);
        }
        // var_dump($source);exit;
        $attributes = $model::getAttributesList()['type'];
        //将查询的数据保存在session中
        Yii::$app->session->set('searchBillingField', $fieldArray);
        //var_dump($source);exit;

        return $this->render('billing-page', [
            'model' => $model,
            'source' => $source,
            'showField' => $showField,
            'attributes' => $attributes
        ]);
    }

    //控制策略分布
    public function actionControl()
    {
        $model = new OnlineReportControl();
        $control = new Control();
        $controlArray = $control->getList();

        //计费策略数据
        if ($controlArray) {
            foreach ($controlArray as $val) {
                $showField[$val['control_id']] = $val['control_name'];
            }
        }
        $post = Yii::$app->request->post();
        $source = [];
        if (!empty($post)) {
            if ($model->load($post) && $model->validate() && $model->validateField()) {

                //如果搜索的产品没有勾选, 则查询所有产品.
                if (isset(Yii::$app->request->post()['OnlineReportControl']['showField'])) {
                    Yii::$app->session->set('online_control', $post['OnlineReportControl']['showField']);
                    $searchField = Yii::$app->request->post()['OnlineReportControl']['showField'];
                    foreach ($searchField as $val) {
                        $fieldArray[$val] = $showField[$val];
                    }
                } else {
                    $fieldArray = $showField;
                }
                $source = $model->peoples($fieldArray); //在线产品使用人数分布
            }
        } else {
            $model->start_At = $model->stop_At = date('Y-m-d');
            $model->type = 'count';
            $fieldArray = $showField;
            $source = $model->peoples($showField);
        }
        $attributes = $model::getAttributesList()['type'];
        //将查询的数据保存在session中
        Yii::$app->session->set('searchControlField', $fieldArray);

        return $this->render('control-page', [
            'model' => $model,
            'source' => $source,
            'showField' => $showField,
            'attributes' => $attributes
        ]);
    }

    //终端分布
    public function actionTerminal()
    {
        $model = new OnlineReportClassName();
        $post = Yii::$app->request->post();
        $showField = $model->getBase();
        $showField = array_combine($showField, $showField);
        if (!empty($post)) {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) {
                if (isset(Yii::$app->request->post()['OnlineReportClassName']['showField'])) {
                    Yii::$app->session->set('online_terminal', $post['OnlineReportClassName']['showField']);
                    $searchField = Yii::$app->request->post()['OnlineReportClassName']['showField'];
                    foreach ($searchField as $val) {
                        $fieldArray[$val] = $showField[$val];
                    }
                } else {
                    $fieldArray = $showField;
                }
                if (isset($post['timePoint']) && !empty($post['timePoint'])) {
                    $timePoint = Yii::$app->request->post()['timePoint'];
                    $model->setTime($timePoint);
                }
                $source = $model->getData($fieldArray);
            }
        } else {
            $model->stop_At = date('Y-m-d');
            $model->start_At = date('Y-m-d');
            if (Yii::$app->session->has('online_terminal')) {
                $searchField = Yii::$app->session->get('online_terminal');
                foreach ($searchField as $val) {
                    $fieldArray[$val] = $showField[$val];
                }
            } else {
                $fieldArray = $showField;
            }
            $source = $model->getData($fieldArray);
        }

        $attributes = $model::getAttributesList()['type'];
        //将查询的数据保存在session中
        Yii::$app->session->set('searchOsField', $fieldArray);
        //var_dump($source);exit;

        return $this->render('terminal-page', [
            'source' => $source,
            'model' => $model,
            'attributes' => $attributes,
            'showField' => $showField,
        ]);
    }

    //终端类型分布
    public function actionTerminaltype()
    {
        $model = new OnlineReportOsName();
        $showField = $model->getBase();
        $showField = array_combine($showField, $showField);
        $post = Yii::$app->request->post();
        if (!empty($post)) {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) {
                if (isset($post['timePoint']) && !empty($post['timePoint'])) {
                    $timePoint = $post['timePoint'];
                    $model->setTime($timePoint);
                }
                if (isset(Yii::$app->request->post()['OnlineReportOsName']['showField'])) {
                    $searchField = Yii::$app->request->post()['OnlineReportOsName']['showField'];
                    Yii::$app->session->set('online_terminal', $post['OnlineReportOsName']['showField']);
                    foreach ($searchField as $val) {
                        $fieldArray[$val] = $showField[$val];
                    }
                } else {
                    $fieldArray = $showField;
                }
                $source = $model->getData($fieldArray);
            }
        } else {
            $model->stop_At = date('Y-m-d');
            $model->start_At = date('Y-m-d');
            if (Yii::$app->session->has('online_terminal_type')) {
                $searchField = Yii::$app->session->get('online_terminal_type');
                foreach ($searchField as $val) {
                    $fieldArray[$val] = $showField[$val];
                }
            } else {
                $fieldArray = $showField;
            }
            $source = $model->getData($fieldArray);
        }

        //var_dump($source);exit;
        $attributes = $model::getAttributesList()['type'];
        Yii::$app->session->set('searchOsNameField', $fieldArray);


        return $this->render('terminaltype-page', [
            'model' => $model,
            'source' => $source,
            'attributes' => $attributes,
            'showField' => $showField,
        ]);

    }
}
