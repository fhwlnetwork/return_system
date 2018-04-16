<?php
/**
 * Created by PhpStorm.
 * User: ligang
 * Date: 2015/3/28
 * Time: 18:49
 */

namespace center\modules\user\controllers;

use center\controllers\ValidateController;
use center\modules\auth\models\SrunJiegou;
use center\modules\user\models\Base;
use common\extend\Excel;
use common\models\Redis;
use common\models\User;
use yii;
use center\modules\user\models\Detail;

class DetailController extends ValidateController
{

    const DETAIL_EXPORT_LIMIT = 5000;

    public function actionIndex()
    {
        //请求的参数
        $params = Yii::$app->getRequest()->queryParams;

        $query = Detail::find();
        $tableName = Detail::tableName();
        if (isset($params['search']) && !empty($params['search'])) {
            $query->Where([
                'or',
                ['like', $tableName . '.user_name', $params['search']],
                ['like', $tableName . '.user_real_name', $params['search']],
                ['like', $tableName . '.mgr_name', $params['search']],
                ['like', $tableName . '.operate_ip', $params['search']]
            ]);
        }
        if (isset($params['type']) && in_array($params['type'], [0, 1])) {
            $query->andWhere([$tableName . '.type' => $params['type']]);
        }
        if (isset($params['add_time']) && !empty($params['add_time'])) {
            $query->andWhere(['>=', $tableName . '.operate_time', strtotime($params['add_time'])]);
        }
        if (isset($params['end_time']) && !empty($params['end_time'])) {
            $query->andWhere(['<=', $tableName . '.operate_time', strtotime($params['end_time'])]);
        }
        if (isset($params['mgr_name']) && !empty($params['mgr_name'])) {
            $query->andWhere(['like', 'mgr_name', $params['mgr_name']]);
        }
        //非超级管理员
        if (!User::isSuper()) {
            //可以管理的组织结构
            $canMgrOrg = SrunJiegou::getAllNode();
            $userTable = Base::tableName();
            $query->leftJoin($userTable, $userTable . '.user_name=' . $tableName . '.user_name');
            $query->andWhere(['or', ['users.group_id' => $canMgrOrg], ['users.group_id' => null]]);

            //可以管理的管理员
            $canMgrAdmin = (new User())->getChildIdAll();
            $query->andWhere([$tableName . '.mgr_name' => $canMgrAdmin]);
        }

        //排序
        $query->orderBy(['id' => SORT_DESC]);

        //生成excel
        if (isset($params['action']) && !empty($params['action'])) {
            $model = new Detail();
            $list = $query->asArray()
                ->all();
            if (count($list) > self::DETAIL_EXPORT_LIMIT) {
                Yii::$app->session->setFlash('error', Yii::t('app', 'batch export help1', [
                    'num' => self::DETAIL_EXPORT_LIMIT,
                ]));
            } elseif (count($list) == 0) {
                Yii::$app->session->setFlash('error', Yii::t('app', 'batch export help2'));
            } else {
                $dataList = $model->formatExcelData($list);
                $title = Yii::t('app', 'User Add Detail');
                $file = $title . '.xls';
                Excel::header_file($dataList, $file, $title);
                exit;
            }

        }

        //分页
        $offset = isset($params['offset']) && $params['offset'] > 0 ? $params['offset'] : 10;

        $pagination = new yii\data\Pagination([
            'defaultPageSize' => $offset,
            'totalCount' => $query->count(),
        ]);

        //列表
        $list = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->asArray()
            ->all();

        return $this->render('index', [
            'model' => new Detail(),
            'list' => $list,
            'pagination' => $pagination,
            'params' => $params,
        ]);
    }

    public function actionView($id)
    {
        $model = Detail::findOne(intval($id));
        if (!$model) {
            throw new yii\web\NotFoundHttpException(Yii::t('app', 'No results found.'));
        }
        $userModel = new Base();
        return $this->render('view', [
            'model' => $model,
            'userModel' => $userModel,
        ]);
    }

    // 打印
    public function actionPrint($id, $user_name)
    {
        $model = Detail::findOne(intval($id));
        if (!$model) {
            throw new yii\web\NotFoundHttpException(Yii::t('app', 'No results found.'));
        }
        $user_detail = json_decode($model->detail,true);

        //
        $user_password = Redis::executeCommand('get', $user_detail['user_id'] . '_' . $user_name);

        if($user_password){
            $user_detail['user_password'] = $user_password;
        }else{
            $user_detail['user_password'] = '';
        }
        $userModel = new Base();
        return $this->render('print', [
            'user_detail' => $user_detail,
            'model' => $model,
            'userModel' => $userModel,
        ]);
    }
}