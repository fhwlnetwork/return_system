<?php
namespace center\modules\log\controllers;

use center\modules\auth\models\SrunJiegou;
use center\modules\log\models\Login;
use center\modules\user\models\Base;
use common\extend\Excel;
use common\models\Redis;
use common\models\User;
use yii;
use center\controllers\ValidateController;
use yii\data\Pagination;
use common\models\ManagerLoginLog;

class LoginController extends ValidateController
{

    const LOGIN_EXPORT_LIMIT = 5000;

    public function actionIndex()
    {
        //请求的参数

        $params = Yii::$app->getRequest()->queryParams;
        //如果不输入任何条件，就查询当天的明细
        if (empty($params)) {
            $params["start_log_time"] = date("Y-m-d 00:00:00");
        }

        $model = new Login();
        //查询表名
        $res = $model::resetPartitionIndex($params);
        if(!empty($res)){
            Yii::$app->getSession()->setFlash('danger', $res);
        }
        $query = Login::find();
        $tableName = Login::tableName();

        // 从redis中获取用户默认的在线菜单
        $paramKey = 'key:log:login:search:params';
        $paramRedis = Redis::executeCommand('get', $paramKey, [], 'redis_manage');

        //整理要查询数据库的字段
        if (empty($params['showField'])) {
            // 从redis中获取此管理员之前勾选过的字段
            $defaultField = $paramRedis ? yii\helpers\Json::decode($paramRedis) : false;
            $params['showField'] = is_array($defaultField) ? $defaultField : $model->defaultField;
        }
        $sortField = [];
        foreach ($params['showField'] as $val) {
            if (array_key_exists($val, $model->searchField)) {
                $query->addSelect($tableName . '.' . $val);
                //将搜索字段压入新数组
                $sortField[$val] = $model->searchField[$val];
            }
        }

        //将记录保存在redis中
        Redis::executeCommand('set', $paramKey, [yii\helpers\Json::encode($params['showField'])], 'redis_manage');

        //重新排序searchField
        $model->searchField = $sortField + $model->searchField;

        //过滤查询条件字段
        foreach ($params as $field => $value) {
            if ($value != '') {
                switch ($field) {
                    case 'start_log_time':
                        $query->andWhere(['>=', $tableName . '.log_time', strtotime($value)]);
                        break;
                    case 'end_log_time':
                        $query->andWhere(['<', $tableName . '.log_time', strtotime('+1 days ' . $value)]);
                        break;
                    case 'err_msg':
                        $query->andWhere(['like', $tableName . '.err_msg', $value]);
                        break;
                    default:
                        if (array_key_exists($field, $model->searchField)) {
                            $query->andWhere(['=', $tableName . '.' . $field, $value]);
                        }
                        break;
                }
            }
        }

        //用组织结构过滤
        if (!User::isSuper()) {
            //所有可以管理的组
            $canMgrOrg = SrunJiegou::getAllNode();
            $userTable = Base::tableName();
            $query->leftJoin($userTable, $userTable . '.user_name=' . $tableName . '.user_name');
            $query->andWhere([$userTable . '.group_id' => $canMgrOrg]);
        }

        //排序
        if (isset($params['orderBy']) && array_key_exists($params['orderBy'], $model->searchField)) {
            $query->orderBy([$tableName . '.' . $params['orderBy'] => $params['sort'] == 'desc' ? SORT_DESC : SORT_ASC]);
        } else {
            $query->orderBy([$tableName . '.id' => SORT_DESC]);
        }

        //生成excel
        if (isset($params['action']) && !empty($params['action'])) {
            $list = $query->asArray()
                ->all();
            if (count($list) > self::LOGIN_EXPORT_LIMIT) {
                Yii::$app->session->setFlash('error', Yii::t('app', 'batch export help1', [
                    'num' => self::LOGIN_EXPORT_LIMIT,
                ]));
            } elseif (count($list) == 0) {
                Yii::$app->session->setFlash('error', Yii::t('app', 'batch export help2'));
            } else {
                $dataList = $model->formatExcelData($list, $params['showField']);
                $title = Yii::t('app', 'Login Log');
                $file = $title . '.xls';
                Excel::header_file($dataList, $file, $title);
                exit;
            }

        }

        //分页
        $offset = isset($params['offset']) && $params['offset'] > 0 ? $params['offset'] : 10;

        $pagination = new Pagination([
            'defaultPageSize' => $offset,
            'totalCount' => $query->count(),
        ]);

        //列表
        $list = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->asArray()
            ->all();
        $list = $model->msgReplace($list);

        //处理ajax请求
        if (isset($params['showType']) && $params['showType'] == 'ajax') {
            $header = [];
            foreach ($params['showField'] as $value) {
                $header[0][$value] = $model->searchField[$value];
            }
            $newList = yii\helpers\ArrayHelper::merge($header, $list);
            foreach ($newList as $k => $v) {
                $v = $model->formattedData($v);
                $newList[$k] = array_values($v);
            }
            return yii\helpers\Json::encode($newList);
        }

        return $this->render('index', [
            'model' => $model,
            'list' => $list,
            'pagination' => $pagination,
            'params' => $params,
        ]);
    }

    /**
     * 显示用户登录日志
     * @Author: wjh
     * @return string
     */
    public function actionList()
    {
        $params = Yii::$app->request->queryParams;
        $model = new ManagerLoginLog();
        $query = $model->find();
        if (!empty($params) && $params['manager_name']) {
            if (isset($params['exact_tag'])) {
                $query->andWhere('manager_name = :mgr', [':mgr' => $params['manager_name']]);
            } else {
                $mgrName = $params['manager_name'];
                $query->andWhere('manager_name LIKE :mgr', [':mgr' => "%$mgrName%"]);
            }
        }


        if (!empty($params) && isset($params['start_time']) && !empty($params['start_time'])) {
            $start = strtotime($params['start_time']);
            $query->andWhere('login_time >= :str', [':str' => $start]);
        }

        if (!empty($params) && isset($params['end_time']) && !empty($params['end_time'])) {
            $end = strtotime($params['end_time']);
            $query->andWhere('login_time <= :end', [':end' => $end]);
        }

        //如果是非超管，查看只可管理的管理员
        if(!User::isSuper()){
            //判断管理员
            $userModel = new User();
            $canMgrope = $userModel->getChildIdAll();
            $query->andWhere(['manager_name' => $canMgrope]);
        }

        //分页
        //一页多少条
        $offset = isset($params['offset']) && $params['offset'] > 0 ? $params['offset'] : 10;
        $pagination = new Pagination([
            'defaultPageSize' => $offset,
            'totalCount' => $query->count(),
        ]);

        $params['showField'] = $model->defaultField;


        foreach ($params['showField'] as $val) {
            if (array_key_exists($val, $model->searchField)) {
                //将搜索字段压入新数组
                $sortField[$val] = $model->searchField[$val];
                $validParam[] = $val; //把有效的数据再压入搜索中
                $query->addSelect($val);
            }
        }


        //排序
        if (isset($params['orderBy']) && array_key_exists($params['orderBy'], $model->searchField)) {
            $query->orderBy([$params['orderBy'] => $params['sort'] == 'desc' ? SORT_DESC : SORT_ASC]);
        } else {
            $query->orderBy(['id' => SORT_DESC]);
        }


        $list = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->asArray()
            ->all();


        return $this->render('list',
            [
                'list' => $list,
                'pagination' => $pagination,
                'model' => $model,
                'params' => $params
            ]);
    }


    /**
     * 删除三个月之前的登录日志
     * * @return array
     */
    public function actionDeleteAll()
    {
        $model = new ManagerLoginLog();
        $time = time() - 3 * 30 * 86400; //删除三个月前的数据
        try {
            $rs = $model::deleteAll('login_time < :time', [':time' => $time]);
            if ($rs) {
                //写批量删除登录日志记录
                $logContent = Yii::t('app', 'batch login delete help2', [
                    'mgr' => Yii::$app->user->identity->username,
                    'total' => $rs,
                ]);
                $model->batchWriteLog($logContent);
                Yii::$app->getSession()->setFlash('success', $logContent);
            } else {
                $logContent = Yii::t('app', 'no record');
                Yii::$app->getSession()->setFlash('error', Yii::t('app', 'no record'));
            }
        } catch (\Exception $e) {
            $logContent = Yii::t('app', 'batch login delete help4', ['msg' => $e->getMessage()]);
            Yii::$app->getSession()->setFlash('error', $logContent);
        }


        //利用response，发送json格式数据
        $response = Yii::$app->response;
        $response->format = yii\web\Response::FORMAT_JSON;

        return $response->data = [$logContent];
    }


    public function actionDelete($id)
    {
        $model = ManagerLoginLog::findOne($id);


        if (!$model) { //没找到用户跳到列表
            //throw new NotFoundHttpException(Yii::t('app', 'No results found.'));
            Yii::$app->getSession()->setFlash('error', Yii::t('app', 'No results found.'));

            return $this->redirect(['list']);
        }
        try {
            $rs = $model->delete();
            if ($rs) {
                Yii::$app->getSession()->setFlash('success', Yii::t('app', 'operate success.'));
            } else {
                Yii::$app->getSession()->setFlash('error', Yii::t('app', 'operate failed.'));
            }
        } catch (\Exception $e) {
            $logContent = Yii::t('app', 'batch login delete help4', ['msg' => $e->getMessage()]);
            Yii::$app->getSession()->setFlash('error', $logContent);
        }

        return $this->redirect('list');
    }
}