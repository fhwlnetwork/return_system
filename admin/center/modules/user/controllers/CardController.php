<?php
/**
 * 用户基本控制器
 */
namespace center\modules\user\controllers;

use center\modules\auth\models\SrunJiegou;
use center\controllers\ValidateController;
use common\extend\Excel;
use common\models\Redis;
use common\models\User;
use yii;
use center\modules\strategy\models\Product;
use center\modules\user\models\Card;
use center\modules\user\models\Base;
use yii\data\Pagination;
use yii\web\NotFoundHttpException;
use yii\data\ArrayDataProvider;

class CardController extends ValidateController
{
    /**
     * 用户经费卡
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionIndex()
    {
		$monthStart = strtotime(date('Y-m-01'));
        //请求的参数
        $params = Yii::$app->getRequest()->queryParams;
        $model = new Card();
		$User = new Base();
		$Product  = new Product();
        $query = Card::find();
        $query->addSelect(['user_id', 'user_name','balance','card_num','card_owner','email','user_create_time']);
		$query->andWhere(['pay_type' => 1]);
		$query->orderBy([ 'user_id' => SORT_DESC ]);
/*      $query->andWhere(['>=', 'user_create_time', $startTime]);
        $query->andWhere(['<', 'user_create_time', $endTime]);		
        $query->andWhere(['=', 'balance', 0]);		 */
        
 		//分页
/*         $pagination = new Pagination([
            'defaultPageSize' => 10,
            'totalCount' => $query->count(),
        ]); */

        //列表
        $list = $query->asArray()->all();	

		/////////////////////////////////////////////////////////////////////////////////		
		$ValidData = array();
		if(!empty($list)){
			$i =0;
			$y = 0;		
			foreach($list as $key=>$value){
				//根据用户名取出Redis中的用户信息
				$userRedis = $model->getUserInRedis($value['user_name']);
				//判断用户的状态是否正常
				if($userRedis['user_available'] == 0){
					
					//根据用户ID+产品ID  取出用户产品实例
					$productMessage = $User->getOneProductObj($userRedis['user_id'],$userRedis['products_id']);
					$user_balance = $productMessage['user_balance'];  // 用户产品余额 				
					//根据产品ID 取出产品的结算金额
					$checkout_amount = $Product->getProOne($userRedis['products_id']);//产品结算金额
					$Product_amount = $checkout_amount['checkout_amount'];	
 					//判断新老用户	
					if($value['user_create_time'] >= $monthStart){
					//新用户
							//判断用户产品余额是否小于产品结算金额
							if($user_balance <= 0){
								//var_dump($user_balance);
								$i++;
								$value['balance'] = $Product_amount;					
								$ValidData[$y] = $value;
								$y++;						
							}
					
					}else{
					//老用户
							//判断用户产品余额是否小于产品结算金额
							if($user_balance <= $Product_amount){
								//加入到数组中
								$i++;						
								$value['balance'] = $Product_amount;
								$ValidData[$y] = $value;
								$y++;							
							}				
					} 
					
				}// 用户状态正常
				
			}// foreach
		
		}
			//将数据存储到session中
			Yii::$app->session->set('UserCardData', $ValidData);
			$provider = new ArrayDataProvider([
				'allModels' => $ValidData,
				 'pagination' => [
					'pageSize' => 10,
				 ],
			]);           

        return $this->render('index',['model'=>$model,'pagination' => $pagination,'list'=>$list,'provider'=>$provider]);
    }
	
	
    public function actionExport()
    {
		$model = new Card();
		//准备要导出的数据
		$UserData = Yii::$app->session->get('UserCardData');
		$attributeLabels = $model->attributeLabels();
		$ExcelTitle[] = array_values($attributeLabels);
		$arr = array();
		foreach($UserData as $key=>$value){
			$Temporary = array();
			foreach($value as $m=>$n){
				if(array_key_exists($m,$attributeLabels)){
					$Temporary[]= $n;
				}
			}
			$arr[]=$Temporary;
		}

		$result = array_merge($ExcelTitle,$arr);
		//导出经费卡用户
		$title = Yii::t('app','report user card');
		$file = $title . '.xls';
		Excel::header_file($result, $file, $title);		
	}
	
	// 根据产品计算收费标准
	function findNumber($str){
		if(!empty($str)){
			if(preg_match('/\d+/',$str,$arr)){
				return $arr[0];
			}
		}
	}
	
	
}
