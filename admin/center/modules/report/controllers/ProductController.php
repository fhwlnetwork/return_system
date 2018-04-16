<?php

namespace center\modules\report\controllers;

use center\modules\report\models\OnlineProductUser;
use center\modules\report\models\OnlineProductUserMonth;
use center\modules\report\models\ProductUserNumber;
use center\modules\strategy\models\Nas;
use Yii;
use center\modules\report\models\OnlineReportProducts;
use center\modules\report\models\SrunProductDetail;
use center\modules\report\models\SrunProduct;
use center\modules\strategy\models\Product;
use common\extend\Excel;

/**
 * Class OperateController 运营报表
 * @package center\modules\report\controllers
 */
class ProductController extends \center\controllers\ValidateController
{

    public function actionRedirect(){
        $front = 'report/product/';
        if(Yii::$app->user->can($front.'index')){
            $this->redirect('index');
        }elseif (Yii::$app->user->can($front.'online')){
            $this->redirect('online');
        }elseif (Yii::$app->user->can($front.'user')){
            $this->redirect('user');
        }elseif (Yii::$app->user->can($front.'product-detail')){
            $this->redirect('product-detail');
        }else{
        }
    }

    //产品上网明细
    public function actionIndex()
    {
        $model = new SrunProductDetail();

        $product = new Product();
        $productArray = $product->getList();

        //产品数据
        if ($productArray) {
            foreach ($productArray as $val) {
                $showField[$val['products_id']] = $val['products_name'];
            }
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->validateField()) {
            if (isset(Yii::$app->request->post()['SrunProductDetail']['showField'])) {
                $searchField = Yii::$app->request->post()['SrunProductDetail']['showField'];
                if($searchField){
                    foreach ($searchField as $val) {
                        $fieldArray[$val] = $showField[$val];
                    }
                }


            } else {
                $fieldArray = $showField;
            }

            Yii::$app->session->set('selectProduct', $fieldArray);
            $start_At = Yii::$app->request->post()['SrunProductDetail']['start_At'];
            Yii::$app->session->set('start_At', $start_At);
            Yii::$app->session->set('bytes_limit',$model->getBytesLimit());

            $source = $model->getCountData($model, $fieldArray);
            $sourcedata = $source['arr'];
            $BeginDate = $source['BeginDate'];
            $EndingDate = $source['EndingDate'];
            return $this->render('index-page', [
                'model' => $model,
                'source' => $sourcedata,
                'BeginDate' => $BeginDate,
                'EndingDate' => $EndingDate,
                'showField' => $showField
            ]);

        } else {
            return $this->render('index-page', [
                'model' => $model,
                'showField' => $showField
            ]);
        }
    }

    //导出上网明细
    public function actionDetail(){

        if(isset($_GET['action']) && $_GET['action'] == 'excel'){
            $model = new SrunProductDetail();
            $start_At = Yii::$app->session->get('start_At');
            $bytesLimit = Yii::$app->session->get('bytes_limit');
            $pid = $_GET['pid'];
            //取出该产品中的数据
            $result = $model->getDetailData($pid, $start_At,$bytesLimit);
            $reportTitle =  [
                Yii::t('app', 'products name'),
                Yii::t('app', 'User Name'),
                Yii::t('app', 'realname'),
                Yii::t('app', 'ID NO'),
                Yii::t('app', 'total bytes'),
                Yii::t('app', 'time count'),
                Yii::t('app', 'time long')
            ];
            //$reportTitle = array('产品名称','用户账号','姓名','证件号','总流量','上网次数','总时长');
            array_unshift($result,$reportTitle);
            $title = Yii::t('app','report product detail excel')."-".$start_At;
            $file = $title . '.xls';
            Excel::header_file($result, $file, $title);
            exit;
        }
    }

    //导出上网统计
    public function actionExport(){
        $model = new SrunProductDetail();
        $fieldArray = Yii::$app->session->get('selectProduct');
        $start_At = Yii::$app->session->get('start_At');
        $model->start_At = $start_At;
        $source = $model->getCountData($model, $fieldArray);
        $dataList = $source['arr'];
        //var_dump(Yii::$app->request->queryParams);exit;
        $reportTitle =  [
            Yii::t('app', 'products name'),
            Yii::t('app', 'user count'),
            Yii::t('app', 'total bytes'),
            Yii::t('app', 'time count'),
            Yii::t('app', 'time long')
        ];
        $result = array($reportTitle);
        $i = 1;
        foreach($dataList as $key=>$value){
            $arr  = array();
            $arr['products_name'] = $value['products_name'];
            $arr['usercount'] = $value['usercount'];
            $arr['total_bytes'] = $value['total_bytes'];
            $arr['user_login_count'] = $value['user_login_count'];
            $arr['time_long'] = $value['time_long'];
            $result[$i]=array_values($arr);
            $i++;
        }

        $title = Yii::t('app','report product excel');
        $file = $title . '.xls';
        Excel::header_file($result, $file, $title);
        exit;
    }

    //产品分布
    public function actionOnline()
    {
        $model = new OnlineReportProducts();

        $product = new Product();
        $productArray = $product->getList();

        //产品数据
        if ($productArray) {
            foreach ($productArray as $val) {
                $showField[$val['products_id']] = $val['products_name'];
            }
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->validateField()) {

            if (isset(Yii::$app->request->post()['OnlineReportProducts']['showField'])) {
                $searchField = Yii::$app->request->post()['OnlineReportProducts']['showField'];
                foreach ($searchField as $val) {
                    $fieldArray[$val] = $showField[$val];
                }
            } else {
                $fieldArray = $showField;
            }

            //将查询的数据保存在session中
            Yii::$app->session->set('searchProduct', $fieldArray);
            $start_At = Yii::$app->request->post()['OnlineReportProducts']['start_At'];
            Yii::$app->session->set('start', $start_At);
            $stop_At = Yii::$app->request->post()['OnlineReportProducts']['stop_At'];
            Yii::$app->session->set('stop', $stop_At);
            $step = Yii::$app->request->post()['OnlineReportProducts']['step'];
            Yii::$app->session->set('step', $step);
            $source = $model->getpeoples($model, $fieldArray); //在线产品使用人数分布

            return $this->render('product-page', [
                'model' => $model,
                'showField' => $showField,
                'source' => $source['total'],
                'BeginDate' => $source['start_At'],
                'EndingDate' => $source['stop_At'],
                'xAxistime' => $source['xAxistime'],
                'tabletitle' => $source['tabletitle'],
                'tableseries' => $source['tableseries']
            ]);
        } else {
            return $this->render('product-page', [
                'model' => $model,
                'showField' => $showField
            ]);
        }
    }

    //导出上网明细
    public function actionProduct(){
        $model = new OnlineReportProducts();
        $fieldArray = Yii::$app->session->get('searchProduct');
        $model->start_At = Yii::$app->session->get('start');
        $model->stop_At = Yii::$app->session->get('stop');
        $model->step = Yii::$app->session->get('step');
        $model->unit = 'minutes';
        $source = $model->getpeoples($model, $fieldArray);
        $dataList = $source['total'];
        $reportTitle = array('产品名称','时间','在线人数');
        $result = array($reportTitle);
        $i = 1;
        foreach($dataList as $key=>$value){
            $result[$i]=array_values($value);
            $i++;
        }
        $title = Yii::t('app','online product user excel');
        $startTime = date('Y-m-d',strtotime($model->start_At));
        $stopTime = date('Y-m-d',strtotime($model->stop_At));
        $title = $title."-".$startTime."-".$stopTime;
        $file = $title . '.xls';
        Excel::header_file($result, $file, $title);
        exit;
    }

    //导出产品和人数的统计
    public function actionUserExcel(){
        $model = new ProductUserNumber();
        $model->setScenario('product-user');
        $model->start_At = Yii::$app->session->get('user_start');
        $model->stop_At = Yii::$app->session->get('user_stop');
        $model->showField = Yii::$app->session->get('user_searchProduct');
        $result = $model->getExcelUserNumber();
        //$reportTitle = ['产品名称','日期','总用户数','正常用户数'];
        $reportTitle = [
            Yii::t('app','product name'),
            Yii::t('app','date'),
            Yii::t('app','total user number'),
            Yii::t('app','normal user number'),
        ];
        array_unshift($result,$reportTitle);
        $title = Yii::t('app','product user number excel');
        $startTime =  $model->start_At;
        $stopTime = $model->stop_At;
        $title = $title."-" .$startTime."-".$stopTime;
        $file = $title . '.xls';
        $data = [];
        foreach($result as $Key => $value){
            $data[] = array_values($value);
        }
        Excel::header_file($data, $file, $title);
        exit;
    }


    //产品和人数的统计
    public function actionUser(){
        $model = new ProductUserNumber();

        $product = new Product();
        $productArray = $product->getList();

        //产品数据
        if ($productArray) {
            foreach ($productArray as $val) {
                $showField[$val['products_id']] = $val['products_name'];
            }
        }
        $model->setScenario('product-user');
        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->validateField()) {
            if (isset(Yii::$app->request->post()['ProductUserNumber']['showField'])) {
                $searchField = Yii::$app->request->post()['ProductUserNumber']['showField'];
                foreach ($searchField as $val) {
                    $fieldArray[$val] = $showField[$val];
                }
            } else {
                $model->showField = array_keys($showField);
                $fieldArray = $showField;
            }

            //将查询的数据保存在session中
            Yii::$app->session->set('user_searchProduct', $model->showField);
            $start_At = $model->start_At;
            Yii::$app->session->set('user_start', $start_At);
            $stop_At = $model->stop_At;
            Yii::$app->session->set('user_stop', $stop_At);
            $source = $model->getProductUserNumber(); //在线产品使用人数分布

            return $this->render('product-user-page', [
                'model' => $model,
                'showField' => $showField,
                'source' => $source['total'],
                'BeginDate' => $source['start_At'],
                'EndingDate' => $source['stop_At'],
                'xAxisTime' => $source['chart']['xAxis'],
                'lengend' => $source['chart']['lengend'],
                'chartSeries' => $source['chart']['data'],
                'tableSeries' => $source['table']['data']
            ]);
        } else {
            return $this->render('product-user-page', [
                'model' => $model,
                'showField' => $showField
            ]);
        }
    }

    public function actionProductUserExcel(){
        $productId =  \Yii::$app->request->get('products_id');
        $date = \Yii::$app->request->get('date');
        if(isset($productId)&&isset($date)){
            $onlineProductUser = new OnlineProductUser();
            $onlineProductUser->date = $date;
            $result = $onlineProductUser->getProductUserExcel($productId);
            if(!$result){
                echo 'no data';
                exit;
            }
       //     $reportTitle = ['产品名称','产品id','用户id','用户姓名','产品状态','证件类型','证件号码'];
            $reportTitle = [
                Yii::t('app','product name'),
                Yii::t('app','product id'),
                Yii::t('app','user id'),
                Yii::t('app','user name'),
                Yii::t('app','product status'),
                Yii::t('app','cert type'),
                Yii::t('app','cert number'),
                Yii::t('app','mobile phone'),
            ];
            $product = new Product();
            array_unshift($result,$reportTitle);
            $title = $onlineProductUser->date.'-'.'export';
            $file = $title.'.xls';
            Excel::header_file($result, $file, $title);
            exit;
        }
    }


    public function actionProductDetailExcel(){
        $model = new SrunProduct();
        $session = Yii::$app->session->get('product-detail');
        if($model->load($session)){
            $products_id = \Yii::$app->request->get('products_id');
            $result = $model->getProductDetailExcel($products_id);
            $reportTitle = [
                Yii::t('app','user id'),
                Yii::t('app','user name'),
                Yii::t('app','cert id'),
                Yii::t('app','online time'),
                Yii::t('app','total bytes'),
                Yii::t('app','bytes in'),
                Yii::t('app','bytes out'),
                Yii::t('app','time long'),
                Yii::t('app','mobile phone'),
            ];
            array_unshift($result,$reportTitle);
            $product  = new Product();
            //获取统计的日期和产品名称
//            $title = $product->getOne($products_id)['products_name']."-".$model->login_time_start."-".$model->login_time_stop;
            $title = $product->getOne($products_id)['products_name'];
            $file = $title . '.xls';
            Excel::header_file($result, $file, $title);
            exit;
        }
    }



    //从srun_detail中统计产品明细数据
    public function actionProductDetail(){
        //获取post数据
        $post = \Yii::$app->request->post();
        $model = new SrunProduct();
        //获取产品所有类别
        $product = new Product();
        $productArray = $product->getList();
        //获取所有的nas_ip
        $nas = new Nas();
        $nasList = $nas->getNasList();
        //产品数据
        if ($productArray) {
            foreach ($productArray as $val) {
                $showField[$val['products_id']] = $val['products_name'];
            }
        }
        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->validateField()) {
           $result = $model->getProductDetailData();
            Yii::$app->session->set('product-detail', $post);
            return $this->render('product-detail', [
                'model' => $model,
                'showField' => $showField,
                'source' => $result,
                'nasList' => $nasList
            ]);
        }else{
            return $this->render('product-detail', [
                'model' => $model,
                'showField' => $showField,
                'nasList' => $nasList
            ]);
        }
    }

    /**
     * 获取一段时间内产品的总人数以及正常人数
     *
     *
     * */
    public function actionUserInterval(){
        $model = new OnlineProductUser();

        $product = new Product();
        $productArray = $product->getList();

        //产品数据
        if ($productArray) {
            foreach ($productArray as $val) {
                $showField[$val['products_id']] = $val['products_name'];
            }
        }
        $model->setScenario('user-interval');
        $get = Yii::$app->request->get();
        
        if($get['excel'] == 1){
            $data['OnlineProductUser'] = Yii::$app->session->get('history');
        }else{
            $data = Yii::$app->request->post();
        }
        if ($model->load($data) && $model->validate()) {
            //将查询的数据保存在session中
            Yii::$app->session->set('history',Yii::$app->request->post()['OnlineProductUser'] );
            $source = $model->getIntervalUserNumber($get['excel']); //在线产品使用人数分布
            if($get['excel'] == 1){
                Excel::header_file($source['data'], $source['file'], $source['title']);
                exit;
            }
            return $this->render('product-user-interval', [
                'model' => $model,
                'showField' => $showField,
                'data' => $source,
            ]);
        } else {
            return $this->render('product-user-interval', [
                'model' => $model,
                'showField' => $showField
            ]);
        }
    }

    /**
     * 按月份获取正常的人数和非正常的人数
     *
     *
     * */
    public function actionUserMonth(){
        $model = new OnlineProductUserMonth();

        $product = new Product();
        $productArray = $product->getList();

        //产品数据
        if ($productArray) {
            foreach ($productArray as $val) {
                $showField[$val['products_id']] = $val['products_name'];
            }
        }
        $model->setScenario('user-month');
        $get = Yii::$app->request->get();

        if($get['excel'] == 1){
            $data['OnlineProductUserMonth'] = Yii::$app->session->get('history');
        }else{
            $data = Yii::$app->request->post();
        }
        if ($model->load($data) && $model->validate()) {
            //将查询的数据保存在session中
            Yii::$app->session->set('history',Yii::$app->request->post()['OnlineProductUserMonth'] );
            $source = $model->getUserAmountMonth($get['excel']); //在线产品使用人数分布
            if($get['excel'] == 1){
                Excel::header_file($source['data'], $source['file'], $source['title']);
                exit;
            }
            return $this->render('product-user-month', [
                'model' => $model,
                'showField' => $showField,
                'data' => $source,
            ]);
        } else {
            return $this->render('product-user-month', [
                'model' => $model,
                'showField' => $showField
            ]);
        }
    }



}
