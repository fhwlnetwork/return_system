<?php

namespace center\modules\report\controllers;

use center\controllers\ValidateController;
use center\modules\log\models\Login;
use yii;
use center\modules\report\models\OnlineReportPoint;
use center\modules\report\models\OnlineReportClassName;
use center\modules\report\models\OnlineReportOsName;
use center\modules\report\models\Dashboard;
use center\modules\report\models\Report;
use center\modules\auth\models\SrunJiegou;
use center\modules\report\models\SrunDetailDay;
use center\modules\report\models\UserReportProducts;
use center\modules\report\models\Financial;
use common\extend\Tool;
use common\models\Feita;

class CollectController extends ValidateController
{
    public function actionIndex()
    {	
		$model = new Report();
		//定义报表名称
		$SelectArray = array(
			'onlineData' => Yii::t('app', 'user online count'),
			'errorData' => Yii::t('app','login log report'),
			'terminalData' => Yii::t('app','online terminaltype chart'),
			'operatingData' => Yii::t('app','online operating system'),
			'operateBytes' => Yii::t('app','report online bilingfont3'),
			'operateUserproduct' => Yii::t('app','report/operate/userproduct'),
			'financialProduct' => Yii::t('app','report/financial/product'),
			'financialusergroup' => Yii::t('app','report/financial/usergroup'),
		);
		if(isset($_POST) && !empty($_POST)){
			Yii::$app->session->set('searchTable', $_POST['ShowTable']);
		}	
		
		$showTable = Yii::$app->session->get('searchTable');
		
		$times = $model::computingTime(Yii::$app->request->post()['timePoint']);				
		if(empty($showTable)){
			$showTable = [];
		}
		

		//用户在线统计表  start
		if(in_array("onlineData",$showTable)){
			$onlineModel = new OnlineReportPoint();
			$onlineModel->unit = $times['unit'];
			$onlineModel->step = $times['step'];
			$onlineModel->start_At = $times['start_At'];
			$onlineModel->stop_At = $times['stop_At'];
			$onlineData = $onlineModel->getOnline($onlineModel);					
		}
		//错误认证  start			
		if(in_array("errorData",$showTable)){
			$params = Yii::$app->request->post('Login');
			$LoginModel = new Login();
			$Errormodel = new \center\modules\report\models\Login();
			$Errormodel->start_At = $times['start_At'];
			$Errormodel->stop_At = $times['start_At'];
			$legend = $Errormodel::getLegend($LoginModel);
			$data = $Errormodel->getData($Errormodel);
			$errorData['legend'] = json_encode($legend);
			$errorData['data'] = json_encode($data);	
		}
		//终端分布
		if(in_array("terminalData",$showTable)){
			$terminalmodel = new OnlineReportClassName();
			$terminalmodel->unit = 'days';
			$terminalmodel->step = 1;
			$terminalmodel->start_At = $times['start_At'];;
			$terminalmodel->stop_At = $times['stop_At'];				
			$terminalData = $terminalmodel->getData($terminalmodel);	
		}
		//操作系统分布
		if(in_array("operatingData",$showTable)){
			$operatingmodel = new OnlineReportOsName();  
			$operatingmodel->unit = 'days';
			$operatingmodel->step = 1;
			$operatingmodel->start_At = $times['start_At'];
			$operatingmodel->stop_At = $times['stop_At'];			
			$operatingData = $operatingmodel->getData($operatingmodel);	
		}
		//流量统计
		if(in_array("operateBytes",$showTable)){
			$operateBytesmodel = new SrunDetailDay();
			$showField = SrunJiegou::getAllIdNameVal();
			$delkey = array_search('/', $showField);
			unset($showField[$delkey]);	
			$fieldArray = $showField;	
			$operateBytesmodel->step = '1G';
			$operateBytesmodel->unit = '10';
			$operateBytesmodel->start_At = $times['start_At'];
			$operateBytesmodel->stop_At = $times['stop_At'];				
			$operateBytesData = $operateBytesmodel->getBytes($operateBytesmodel, $fieldArray);			
		}
		//产品统计表
		if(in_array("operateUserproduct",$showTable)){
			$Userproduct = new UserReportProducts();
			$showField = SrunJiegou::getAllIdNameVal();
			$delkey = array_search('/', $showField);
			unset($showField[$delkey]);		
			$fieldArray = $showField;
			$Userproduct->start_At = $times['start_At'];
			$Userproduct->stop_At = $times['stop_At'];			
			$UserproductData = $Userproduct->getData($Userproduct, $fieldArray,true);		
		}		
		//产品收入统计表
		if(in_array("financialProduct",$showTable)){
			$Financial = new Financial();			
			$products = $Financial->getProNames();
				$time  = Yii::$app->request->post()['timePoint'];
				if($time == 'Today' || $time == 'Yesterday'){
					$time = 'day';
				}else{
					$time = 'week';
				}
				$params = [];
				$params['data_source'] = 'all';
				$params['statistical_cycle'] = $time;
				$params['start_time_day'] = $times['start_At'];
				$params['start_time'] = $times['start_At'];						
				$params['end_time'] = $times['stop_At'];						
				$params['start_time_year'] = date('Y');
				$products_report = [];
				foreach ($products as $key=>$val) {
					 $products_report[$key] = $key;
				}	
				$params['show_products'] = $products_report;
				if($time == 'day'){
					$FinancialData = $Financial->getProductsIncomeByDay($params, $products);
				}elseif($time == 'week'){
					$FinancialData = $Financial->getProductsIncomeByWeek($params, $products);
				}
		}		
		//用户组收入统计表
		if(in_array("financialusergroup",$showTable)){
			
			$FinancialGroup = new Financial();
				$time  = Yii::$app->request->post()['timePoint'];
				if($time == 'Today' || $time == 'Yesterday'){
					$time = 'day';
				}else{
					$time = 'week';
				}
			//用户组数据
			$groups = SrunJiegou::canMgrGroupNameList();
			$delkey = array_search('/', $groups);
			unset($groups[$delkey]);
			$params = [];
			$params['data_source']= 'all';
			$params['statistical_cycle']= $time;
			$params['start_time_day']= $times['start_At'];
			$params['start_time']= $times['start_At'];
			$params['end_time']= $times['stop_At'];		
			$params['start_time_year']= date('Y');

			if($time == 'day'){
				$groupoption = $FinancialGroup->getGroupsIncomeByDay($params, $groups);
			}elseif($time == 'week'){
				$groupoption = $FinancialGroup->getGroupsIncomeByWeek($params, $groups);
			}
		}		
		
        return $this->render('index',[
            'SelectArray' => $SelectArray,		
            'onlineData' => $onlineData,		
            'errorData' => $errorData,
            'terminalData' => $terminalData,
            'operatingData' => $operatingData,
            'operateBytesData' => $operateBytesData,
            'UserproductData' => $UserproductData,
            'FinancialData' => $FinancialData,
            'groupoption' => $groupoption,
        ]);		

    }
}