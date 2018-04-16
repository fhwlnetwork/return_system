<?php

namespace center\modules\report\controllers;

use center\controllers\ValidateController;
use center\modules\auth\models\SrunJiegou;
use center\modules\auth\models\UserModel;
use center\modules\financial\models\CheckoutList;
use center\modules\financial\models\ExtraPay;
use center\modules\financial\models\PayType;
use center\modules\report\models\Financial;
use center\modules\report\models\financial\FinancialBase;
use center\modules\report\models\financial\FinancialModel;
use center\modules\report\models\FinancialReport;
use center\modules\setting\models\EmailForm;
use center\modules\strategy\models\Package;
use center\modules\strategy\models\Product;
use common\models\FileOperate;
use Yii;
use common\extend\Excel;
use yii\data\Pagination;
use center\modules\financial\models\PayList;
use center\extend\Tool;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;

class FinancialController extends ValidateController
{
    public function actionIndex()
    {
        $reportModel = new Financial();

        $params = Yii::$app->request->post('Financial');
        $operator = isset($params['operator']) ? $params['operator'] : '';
        $start_time = isset($params['start_time']) ? $params['start_time'] : '';
        $end_time = isset($params['end_time']) ? $params['end_time'] : '';

        $pay_methods = $reportModel->getMethods($operator, $start_time, $end_time);
        if (!empty($pay_methods)) {
            $money = $reportModel->getSeriesType($operator, $start_time, $end_time);
        }
        return $this->render('index', [
            'model' => $reportModel,
            'params' => $params,
            'methods' => $pay_methods,
            'money' => isset($money) ? $money : '',
        ]);
    }

    /**
     * 管理员按缴费项目
     * @return string
     */
    public function actionPaytype()
    {
        $reportModel = new FinancialBase();
        $reportModel->getRealModel();
        $model = $reportModel->realModel;

        $params = Yii::$app->request->post('Financial');
        $legend = $reportModel->getLegend();
        $post = Yii::$app->request->post();

        if (!empty($post)) {
            if ($model->load($post) && $model->validateField()) {
                $projects = [];
                if (isset($params['show_projects'])) {
                    Yii::$app->session->set('financial_product', $params['show_projects']);
                    foreach ($params['show_projects'] as $val) {
                        $projects[$val] = $legend[$val];
                    }
                } else {
                    $projects = array_slice($legend, 0, 4, true);
                }
                // $projects = isset($params['show_projects']) ? $params['show_projects'] :
                $source = $model->getData($projects);
            }
        } else {
            if (Yii::$app->session->has('financial_product')) {
                $searchField = Yii::$app->session->get('financial_product');
                foreach ($searchField as $val) {
                    $projects[$val] = $legend[$val];
                }
            } else {
                $projects = array_slice($legend, 0, 4, true);
            }

            $model->setDefault();
            $source = $model->getData($projects);
        }

        Yii::$app->session->set('searchFinProject', $projects);

        return $this->render('paytype', [
            'model' => $model,
            'params' => $params,
            'source' => $source,
            'showField' => $legend,
        ]);

    }

    /**
     * 财务报表整顿
     * @return string
     */
    public function actionList()
    {
        $model = new FinancialModel();
        $post = Yii::$app->request->post();
        $get = Yii::$app->request->queryParams;

        $params = $post ? $post : $get;
        if (!isset($params['type'])) {
            $params['type'] = 'methods';
        }
        $model->type = $params['type'];
        if (isset($get['action'])) {
            //excel导出
            $rs = $model->exportData($params);
            if ($rs['code'] == 200) {
                Yii::$app->getSession()->setFlash('success', $rs['msg']);
            } else {
                Yii::$app->getSession()->setFlash('error', $rs['msg']);
            }
        }
        if (!empty($post)) {
            if ($model->load($post) && $model->validateField()) {
                if (isset($post['timePoint'])) {
                    $model->setDate($params);
                }
                $rs = $model->getData($params);
            }
        } else {
            //$model->setDefault();
            $rs = $model->getData($params);
        }

        return $this->render('list', [
            'model' => $model,
            'pagination' => $rs['pagination'],
            'params' => $post,
            'list' => $rs['list'],
            'pay_methods' =>  $rs['types'],
            'pay_types' => $rs['types'],
            'totalMoney' => $rs['totalMoney'],
            'refundMoney' => $rs['refundMoney'],
            'payNum' => $rs['payNum'],
            'mgrs' => $rs['mgrs'],
            'refund_list' => $rs['refund_list']
        ]);
    }

    /**
     * 产品收入分析
     * @return string
     */
    public function actionProduct()
    {
        $baseModel = new FinancialBase();
        $baseModel->getRealModel();
        $model = $baseModel->realModel;
        $post = Yii::$app->request->post();
        $legend = $baseModel->getLegend();
        $params = $post['Financial'];


        if (!empty($post)) {
            //var_dump($post);exit;
            if ($model->load($post) && $model->validateField()) {
                $projects = [];
                if (isset($params['show_products'])) {
                    Yii::$app->session->set('financial_products', $params['show_products']);
                    foreach ($params['show_products'] as $val) {
                        $projects[$val] = $legend[$val];
                    }
                } else {
                    $projects = array_slice($legend, 0, 4, true);
                }
                $source = $model->getDataByTime($projects);
            }
        } else {
            if (Yii::$app->session->has('financial_products')) {
                $searchField = Yii::$app->session->get('financial_products');
                foreach ($searchField as $val) {
                    $projects[$val] = $legend[$val];
                }
            } else {
                $projects = array_slice($legend, 0, 4, true);
            }
            $model->setDefault();
            $source = $model->getDataByTime($projects);
        }
        Yii::$app->session->set('searchProductField', $projects);
        //var_dump($legend);exit;

        return $this->render('product', [
            'model' => $model,
            //'option' => $option,
            'source' => $source,
            'showField' => $legend,
        ]);
    }

    /**
     * 用户组收入分析
     * @return string
     */
    public function actionUsergroup()
    {
        $baseModel = new FinancialBase();
        $baseModel->getRealModel();
        $model = $baseModel->realModel;
        $post = Yii::$app->request->post();
        //默认提交按天统计和当天的日期

        //用户组数据
        $groups = SrunJiegou::canMgrGroupNameList();
        $delkey = array_search('/', $groups);
        unset($groups[$delkey]);

        if (!empty($post)) {
            if ($model->load($post) && $model->validateField()) {
                if (isset($post['group_id'])) {
                   $baseModel->realModel->group_id = $post['group_id'];
                }
                //var_dump($groups_report);exit;
                $source = $model->getDataByGroup();
            }
        } else {
            $model->setDefault();
            $source = $model->getDataByGroup();
        }
        $attributes = $model::getAttributesList()['data_source'];

        //echo $option;exit;
        return $this->render('usergroup', [
            'params' => $post,
            'model' => $model,
            'source' => $source,
            'showField' => $groups,
            'attribute' => $attributes,
        ]);
    }

    public function actionCheckout()
    {
        $reportModel = new FinancialBase();
        $reportModel->getRealModel();
        $model = $reportModel->realModel;
        $post = Yii::$app->request->post();
        $get = Yii::$app->request->queryParams;
        $params = $post ? $post : $get;
        //产品
        //如果选择用户组，那么搜索出相关的管理员，进行发邮件
        if (isset($params['group_id']) && !empty($params['group_id'])) {
            $mgrs = UserModel::find()->select(['username', 'email'])->leftJoin('auth_assignment', 'manager.id=auth_assignment.user_id')->where(['item_name' => SUPER_ROLE])->orWhere('find_in_set("' . $params['group_id'] . '",mgr_org)')->all();

        }

        //var_dump($params);exit;
        if (isset($get['action']) && in_array($get['action'], ['csv', 'excel'])) {
            //整理数据
            $rs = $model->exportData($get);
            Yii::$app->getSession()->setFlash('error', $rs['msg']);

            return $this->redirect('checkout');
        } elseif (isset($get['action']) && $get['action'] == 'send_email') {
            //整理数据
            $rs = $model->sendEmail($params, $mgrs);
            if ($rs['code'] == 200) {
                Yii::$app->getSession()->setFlash('success', $rs['msg']);
            } else {
                Yii::$app->getSession()->setFlash('error', $rs['msg']);
            }

            return $this->redirect('checkout');
        }

        if (!empty($post)) {
            if ($model->load($post) && $model->validateField()) {
                $model->setDate($post);
                $rs = $model->getCheckoutData($post);
            }
        } else {
            $model->setDefault();
            $rs = $model->getCheckoutData($post);
        }

        $list = $rs['list'];
        $pages = $rs['pages'];
        $total_num = $rs['spend'];
        $product = $model->names;

        return $this->render('checkout', [
            'model' => $model,
            'product' => $product,
            'pages' => $pages,
            'list' => $list,
            'total_num' => $total_num,
            'mgrs' => $mgrs,
            'params' => $params
        ]);

    }

    /**
     * 按缴费方式统计
     * @return string
     */
    public function actionMethods()
    {
        $params = Yii::$app->request->queryParams;
        //获取缴费方式
        $payModel = new PayList();
        $data = $payModel->getAllFee($params);
        if (!$data) {
            Yii::$app->getSession()->setFlash('danger', Yii::t('app', 'no user to account'));
        }
        $action = isset($params['action']) && $params['action'] == 'excel' ? 'excel' : '';
        $fileName = Yii::t('app', 'pay type');

        if (!empty($action)) {
            $start_time = isset($params['start_time']) ? $params['start_time'] : '';
            $end_time = isset($params['end_time']) ? $params['end_time'] : '';
            //生成excel
            $fileTimeTitle = !empty($start_time) || !empty($start_time) ? '(' . $start_time . '-' . $end_time . ')' : '';
            $title = Yii::t('app', 'financial report excel', ['type' => $fileName]) . $fileTimeTitle;
            $file = $title . '.xls';
            Excel::header_file($data, $file, $fileTimeTitle ? $fileTimeTitle : $title);
            exit;
        }

        $pages = new Pagination([
            'totalCount' => count($data) - 1,
            'pageSize' => 10
        ]);

        return $this->render('methods', [
            'list' => $data,
            'model' => $payModel,
            'pagination' => $pages,
            'params' => $params,
        ]);
    }

    public function actionProjects()
    {
        $params = Yii::$app->request->queryParams;
        $payModel = new PayList();
        //统计产品，套餐，附加费用，电子钱包
        list($products, $packages, $extras, $balance) = $payModel->getProjects($params);

        //可以管理的产品
        if ($products) {
            $productModel = new Product();
            $productNameList = $productModel->getNameOfList();
        }

        //套餐
        if ($packages) {
            $model_package = new Package();
            $packageNameList = ArrayHelper::map($model_package->getList(), 'package_id', 'package_name');
        }

        //附加费用
        if ($extras) {
            $extraModel = new ExtraPay();
            $extra_payments = $extraModel->getNameOfList();
        }

        //excel
        if (isset($params['action']) && $params['action'] == 'excel') {
            $fileName = Yii::t('app', 'statistics by pay type');
            $start_time = isset($params['start_time']) ? $params['start_time'] : '';
            $end_time = isset($params['end_time']) ? $params['end_time'] : '';

            $data = $payModel->project_excel($products, $packages, $extras, $balance, $productNameList, $packageNameList, $extra_payments);
            $fileTimeTitle = !empty($start_time) || !empty($start_time) ? '(' . $start_time . '-' . $end_time . ')' : '';
            $title = Yii::t('app', 'financial report excel', ['type' => $fileName]) . $fileTimeTitle;
            $file = $title . '.xls';
            Excel::header_file($data, $file, $fileTimeTitle ? $fileTimeTitle : $title);
            exit;
        }

        return $this->render('projects', [
            'products' => $products,
            'packages' => $packages,
            'extras' => $extras,
            'productNameList' => $productNameList,
            'packageNameList' => $packageNameList,
            'extra_payments' => $extra_payments,
            'balance' => $balance,
            'params' => $params
        ]);
    }

    /**
     * 导出产品费用明细
     * @param $type
     * @return \yii\web\Response
     */
    public function actionDetail($type = 'pay_list')
    {
        //导出某个产品的期间费用
        $reportModel = new FinancialBase();
        $reportModel->getRealModel();
        $params = Yii::$app->request->queryParams;
        if (!empty($params)) {
            foreach ($params as $k => $v) {
                $reportModel->realModel->$k = $v;
            }
        }
        $rs = $reportModel->realModel->exportData($type);
        if ($rs['code'] != 200) {
            Yii::$app->getSession()->setFlash('error', $rs['msg']);
            return $this->redirect('product');
        }
    }
}