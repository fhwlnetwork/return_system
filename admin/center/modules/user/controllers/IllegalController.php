<?php
/**
 * Created by PhpStorm.
 * User: qk
 * Date: 15-9-1
 * Time: 下午4:05
 */

namespace center\modules\user\controllers;
use center\controllers\ValidateController;
use center\extend\Tool;
use center\modules\auth\models\SrunJiegou;
use center\modules\log\models\Login;
use center\modules\log\models\SrunLoginLog;
use center\modules\user\models\Illegal;
use common\models\User;
use yii;

class IllegalController extends ValidateController{
    public function actionIndex(){
        //请求的参数
        $params = Yii::$app->getRequest()->queryParams;
        $model = new Illegal();
        $modelLoginLog = new SrunLoginLog();
        $query = $modelLoginLog->find()
            ->select([$modelLoginLog::tableName().'.user_name','user_ip','user_mac','err_msg'])
            ->where(['>=','log_time',strtotime(date('Y-m-d'))]);
        if(isset($params['user_name']) && !empty($params['user_name'])){
            $query->andWhere([$modelLoginLog::tableName().'.user_name'=>$params['user_name']]);
        }
        if(isset($params['mac']) && !empty($params['mac'])){
            $query->andWhere(['user_mac'=>$params['mac']]);
        }
        //如果非超级管理员，则需要去判断
        if(!User::isSuper()){
            //所有可以管理的组
            $canMgrOrg = SrunJiegou::getAllNode();
            $query->leftJoin('users', 'users.user_name='.$modelLoginLog::tableName().'.user_name');
            $query->andWhere(['users.group_id'=>$canMgrOrg]);
        }
        $list = $query->groupBy($modelLoginLog::tableName().'.user_name')->all();
        $list = (new Login())->msgReplace($list);
        $list = $model->getIllegalUsers($list);

        //分页
        $pagesize = 10;
        $current = !isset($params['page']) ? 1 : $params['page'];
        $data = Tool::cuttingArray($list, $current, $pagesize);

        $pagination = new yii\data\Pagination([
            'defaultPageSize' => $pagesize,
            'totalCount' => count($list),
        ]);
        return $this->render('index', [
            'lists' => $data,
            'params' => $params,
            'pagination' => $pagination,
        ]);
    }

    public function actionDelete(){
        $model = new Illegal();
        $params = Yii::$app->request->queryParams;
        $user_name = isset($params['user_name']) ? $params['user_name'] : '';
        $mac = isset($params['mac']) ? $params['mac'] : '';
        $res = $model->deleteIllegalUsers($mac);
        if($res){
            Yii::$app->getSession()->setFlash('success', Yii::t('app', 'operate success.'));
        }else{
            Yii::$app->getSession()->setFlash('danger', Yii::t('app', 'operate failed.'));
        }
        return $this->redirect(['index?user_name='.$user_name]);
    }
} 