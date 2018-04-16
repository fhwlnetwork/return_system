<?php

namespace center\modules\report\controllers;

use center\controllers\ValidateController;
use center\models\CloundOnlineReport;
use center\modules\report\models\CloudPartitionsDay;
use center\modules\report\models\CloudPartitionsHour;
use center\modules\report\models\CloundPartitionsStatus;
use center\modules\report\models\ServerType;
use common\models\CloundSrunLoginLog;
use common\models\Redis;
use center\modules\user\models\Base;
use yii;
use center\modules\strategy\models\Product;
use center\modules\log\models\Login;
use center\modules\report\models\CloundMonitor;
use center\modules\report\models\CloundMemoryStatus;
use yii\data\Pagination;

class MonitorController extends ValidateController
{
    /*
    ** 全局监控
    */
    public function actionIndex()
    {
        $Server = new ServerType();
        $serverType = $Server->findServerType();

        return $this->render('index', [
            'serverType' => $serverType,
            'Server' => $Server,
        ]);
    }

    public function actionGetStatus($type)
    {

    }

    /*
    ** 用户监控
    */
    public function actionUser()
    {
        if (isset($_POST['username']) && !empty($_POST['username'])) {
            $username = trim($_POST['username']);
        }
        $Server = new ServerType();
        $data = Redis::executeCommand('LRANGE', "list:debug:{$username}", [0, -1], 'redis_debug');
        $res = array();
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $res[] = json_decode($value, true);
            }
        }

        $portal_auth = $Server->FindDebugData($res, 'srun_portal_server', 'auth');
        $Radiusd_auth = $Server->FindDebugData($res, 'radiusd', 'auth');
        $third_auth = $Server->FindDebugData($res, 'third_auth', 'auth');
        $proxy_3p = $Server->FindDebugData($res, 'proxy_3p', 'auth');

        $Radiusd_start = $Server->FindDebugData($res, 'radiusd', 'start');
        $Rad_auth_start = $Server->FindDebugData($res, 'rad_auth', 'start');
        $Distribute_start = $Server->FindDebugData($res, 'distribute', 'start');

        $online2db = $Server->FindDebugData($res, 'online2db', 'start');
        $wangkang_3p = $Server->FindDebugData($res, 'wangkang_3p', 'start');
        $allot_3p = $Server->FindDebugData($res, 'allot 3p', 'start');
        $stoneos_3p = $Server->FindDebugData($res, 'stoneos_3p', 'start');
        $shenxunfu_3p = $Server->FindDebugData($res, 'shenxunfu_3p', 'start');


        $Radiusd_update = $Server->FindDebugData($res, 'radiusd', 'update');
        $Rad_auth_update = $Server->FindDebugData($res, 'rad_auth', 'update');


        $Radiusd_stop = $Server->FindDebugData($res, 'radiusd', 'stop');
        $Rad_auth_stop = $Server->FindDebugData($res, 'rad_auth', 'stop');
        $Distribute_stop = $Server->FindDebugData($res, 'distribute', 'stop');
        $wangkang_3p_s = $Server->FindDebugData($res, 'wangkang_3p', 'stop');
        $Online2db_s = $Server->FindDebugData($res, 'online2db', 'stop');
        $allot_3p_s = $Server->FindDebugData($res, 'allot 3p', 'stop');
        $stoneos_3p_s = $Server->FindDebugData($res, 'stoneos_3p', 'stop');
        $shenxunfu_3p_s = $Server->FindDebugData($res, 'shenxunfu_3p', 'stop');

        $portal_stop = $Server->FindDebugData($res, 'srun_portal_server', 'stop');

        $rad_dm = $Server->FindDebugData($res, 'rad_dm', 'dm');
        $rad_auth_dm = $Server->FindDebugData($res, '', 'dm');

        //用户信息
        $model = Base::findOne(['user_name' => "{$username}"]);
        //产品信息

        $product = array();
        if ($model) {
            $product = $model->getOrderedProduct((new Product())->getNameOfList());
        }
        //认证日志，只显示最后一条
        $login_model = new Login();
        $loginQuery = Login::find()
            ->where(['user_name' => $model->user_name]);
        $loginList = $loginQuery
            ->orderBy(['id' => SORT_DESC])
            ->limit(1)
            ->asArray()
            ->all();
        $list = $login_model->msgReplace($loginList);
        return $this->render('user', [
            'portal_auth' => $portal_auth,
            'Radiusd_auth' => $Radiusd_auth,
            'third_auth' => $third_auth,
            'proxy_3p' => $proxy_3p,
            'Radiusd_start' => $Radiusd_start,
            'Rad_auth_start' => $Rad_auth_start,
            'Distribute_start' => $Distribute_start,
            'online2db' => $online2db,
            'wangkang_3p' => $wangkang_3p,
            'allot_3p' => $allot_3p,
            'stoneos_3p' => $stoneos_3p,
            'shenxunfu_3p' => $shenxunfu_3p,
            'Radiusd_update' => $Radiusd_update,
            'Rad_auth_update' => $Rad_auth_update,
            'Radiusd_stop' => $Radiusd_stop,
            'Rad_auth_stop' => $Rad_auth_stop,
            'Distribute_stop' => $Distribute_stop,
            'wangkang_3p_s' => $wangkang_3p_s,
            'Online2db_s' => $Online2db_s,
            'allot_3p_s' => $allot_3p_s,
            'stoneos_3p_s' => $stoneos_3p_s,
            'shenxunfu_3p_s' => $shenxunfu_3p_s,
            'portal_stop' => $portal_stop,
            'rad_auth_dm' => $rad_auth_dm,
            'rad_dm' => $rad_dm,
            'user' => $model,
            'product' => $product,
            'login_log' => $list[0],
        ]);
    }

    /*
    ** 故障状态
    */
    public function actionFault()
    {
        return $this->render('fault', []);
    }

    /**
     * 清空用户认证的流程数据-16388
     * @param $username
     * @return mixed
     */
    public function actionClear()
    {
        $params = Yii::$app->getRequest()->queryParams;
        $username = $params['username'];
        $res = Redis::executeCommand('DEL', "list:debug:{$username}", [0, -1], 'redis_debug');
        if ($res) {
            Yii::$app->getSession()->setFlash('success', Yii::t('app', 'operate success.'));
        } else {
            Yii::$app->getSession()->setFlash('error', Yii::t('app', 'operate failed.'));
        }
        return $this->redirect(['user']);

    }

    /**
     * 调试写入debug-16385
     */
    public function actionDebug()
    {
        $username = Yii::$app->request->post('username');
        $user = Base::findOne(['user_name' => "{$username}"]);
        $msg = 100;
        if (!empty($user)) {
            $res = Redis::executeCommand('SET', "key:debug:{$username}", [1], 'redis_log');
            Redis::executeCommand('EXPIRE', "key:debug:{$username}", [60], 'redis_log');
            if ($res) {
                $msg = 101;
            } else {
                $msg = 100;
            }
        } else {
            $msg = 102;
        }
        echo $msg;
        exit;
    }

    /**
     * 用户实时监控界面
     * @return string
     */
    public function actionMachineState()
    {
        $params = Yii::$app->request->queryParams;
        $cloundControl = new CloundMonitor();
        $res = $cloundControl->getProductsKey();
        $rs = yii\helpers\Json::decode($res, true);
        $code = isset($rs['code']) ? $rs['code'] : '';
        $msg = isset($rs['msg']) ? $rs['msg'] : '';
        $model = new CloundMonitor();

        $productKeys = (!empty($code) && $code == 200) ? $rs['rows'] : '';
        if (empty($productKeys)) {
            Yii::$app->getSession()->setFlash('error', Yii::t('app', 'no user to monitor'));
        }

        return $this->render('machine', [
            'productKeys' => $productKeys,
            'model' => $model,
        ]);
    }

    /**
     * 获取用户监控表所需数据
     */
    public function actionMachineList()
    {
        set_time_limit(0);
        $params = Yii::$app->request->queryParams;
        $cloundControl = new CloundMonitor();
        //获取数据
        $rs = $cloundControl->getData($params);
        $code = isset($rs['code']) ? $rs['code'] : '';
        $msg = isset($rs['msg']) ? $rs['msg'] : '';

        $productKeys = (!empty($code) && $code == 200) ? $rs['productKeys'] : '';
        //利用response，发送json格式数据
        $response = Yii::$app->response;
        $response->format = yii\web\Response::FORMAT_JSON;
        if (!empty($code) && $code == 200) {
            $legend = [Yii::t('app', 'startRes'), Yii::t('app', 'authRes'), Yii::t('app', 'dmRes'),
                Yii::t('app', 'coaRes'), Yii::t('app', 'updateRes'), Yii::t('app', 'stopRes')];
            return $response->data = array('code' => $rs['code'], 'msg' => $msg,
                'productKeys' => $productKeys,
                'proc' => $rs['proc'],
                'startRes' => $rs['startRes'],
                'stopRes' => $rs['stopRes'],
                'authRes' => $rs['authRes'],
                'updateRes' => $rs['updateRes'],
                'coaRes' => $rs['coaRes'],
                'dmRes' => $rs['dmRes'],
                'legend' => $legend,
                'seriesData' => $rs['seriesData'],
            );
        } else {
            return $response->data = array('code' => $rs['code'], 'error' => $msg
            );
        }
    }

    public function actionMonitorAllUser()
    {
        $params = Yii::$app->request->queryParams;
        //var_dump($params);exit;

        $params['timePoint'] = isset($params['timePoint']) ? $params['timePoint'] : '';
        if (!empty($params['timePoint'])) {
            switch ($params['timePoint']) {
                case 1: //十分钟数据
                    $params['start_time'] = date('Y-m-d H:i:s', time() - 10 * 60);
                    $params['end_time'] = date('Y-m-d H:i:s');
                    break;
                case 2:
                    $params['start_time'] = date('Y-m-d H:i:s', time() - 30 * 60);
                    $params['end_time'] = date('Y-m-d H:i:s');
                    break;
                case 3:
                    $params['start_time'] = date('Y-m-d H:i:s', time() - 60 * 60);
                    $params['end_time'] = date('Y-m-d H:i:s');
                    break;
                case 4:
                    $params['start_time'] = date('Y-m-d 00:00:00');
                    $params['end_time'] = date('Y-m-d H:i:s');
                    break;
                case 5:
                    $params['start_time'] = date('Y-m-d H:i:s', strtotime(date('Y-m-d 00:00')) - (3600 * 24));
                    $params['end_time'] = date('Y-m-d 23:59:59', mktime(0,0,0,date('m'),date('d'),date('Y'))-1);
                    break;
                case 6:
                    $params['start_time']= date("Y-m-d H:i:s", mktime(0, 0, 0, date("m"), date("d") - date("w") + 1, date("Y")));
                    $params['end_time'] = date('Y-m-d H:i:s');
                    break;
                case 7:
                    $params['start_time']= date("Y-m-d H:i:s", mktime(0, 0, 0, date("m"), date("d") - date("w")-6, date("Y")));
                    $params['end_time']= date("Y-m-d 23:59:59", mktime(0, 0, 0, date("m"), date("d") - date("w"), date("Y")));
                    break;
                case 8:
                    $params['start_time']= date("Y-m-d H:i:s", mktime(0,0,0,date('m'),1,date('Y')));
                    $params['end_time']= date("Y-m-d 23:59:59",mktime(23,59,59,date('m'),date('t'),date('Y')));
                    break;
                default:
                    $params['start_time'] = date('Y-m-d H:i:s', time() - 30 * 60);
                    $params['end_time'] = date('Y-m-d H:i:s');
            }
        }
        $model = new CloundMonitor();
        $searchInput = [];
        //得到所有用户
        $rs = $model->getAllData($params);

        $code = isset($rs['code']) ? $rs['code'] : '';
        if (isset($params['products_key']) && !empty($params['products_key']) || preg_match('/^0$/', $params['products_key'])) {
            $searchInput['products_key'] = $params['products_key'];
        }
        $searchInput['start_time'] = isset($params['start_time']) ? date('Y-m-d H:i:s', strtotime($params['start_time'])) : date('Y-m-d H:i:s', time() - 30 * 60);
        $searchInput['end_time'] = isset($params['end_time']) ? date('Y-m-d H:i:s', strtotime($params['end_time'])) : date('Y-m-d H:i:s', time());

        //var_dump($rs);exit;
        if (!empty($code) && $code == 200) {

            return $this->render('monitor-user-all', [
                'model' => $model,
                'rows' => $rs['rows'],
                'productsData' => $rs['productsData'],
                'pagination' => $rs['pagination'],
                'searchField' => [Yii::t('app', 'startRes'), Yii::t('app', 'authRes'), Yii::t('app', 'dmRes'),
                    Yii::t('app', 'coaRes'), Yii::t('app', 'updateRes'), Yii::t('app', 'stopRes')],
                'productKeys' => $rs['productKeys'],
                'searchInput' => $searchInput,
            ]);
        } else {
            Yii::$app->getSession()->setFlash('error', $rs['error']);
            return $this->render('monitor-user-all', [
                'model' => $model,
                'searchInput' => $searchInput
            ]);
        }
    }

    /**
     * 用户历史监控
     * @return string
     */
    public function actionMonitorHistory()
    {
        $params = Yii::$app->request->queryParams;
        $cloundControl = new CloundMonitor();
        $res = $cloundControl->getProductsKey($params);
        $rs = yii\helpers\Json::decode($res, true);
        $code = isset($rs['code']) ? $rs['code'] : '';
        $msg = isset($rs['msg']) ? $rs['msg'] : '';
        $model = new CloundMonitor();
        $proc = $model->procs;

        $productKeys = (!empty($code) && $code == 200) ? $rs['rows'] : '';
        if (empty($productKeys)) {
            Yii::$app->getSession()->setFlash('error', Yii::t('app', 'no user to monitor'));
        }

        return $this->render('machine-history', [
            'productKeys' => $productKeys,
            'model' => $model,
            'proc' => $proc,
        ]);
    }

    public function actionMonitorHistoryList()
    {
        set_time_limit(0);
        $params = Yii::$app->request->queryParams;
        $model = new CloundMonitor();
        $proc = $model->procs;
        $procName = isset($proc[$params['proc']]) ? $proc[$params['proc']] : '';
        $res = $model->getMonitorHistoryData($params);
        $code = isset($res['code']) ? $res['code'] : '';

        //利用response，发送json格式数据
        $response = Yii::$app->response;
        $response->format = yii\web\Response::FORMAT_JSON;
        if (!empty($code) && $code == 200) {
            $legend = [Yii::t('app', 'startRes'), Yii::t('app', 'authRes'), Yii::t('app', 'dmRes'),
                Yii::t('app', 'coaRes'), Yii::t('app', 'updateRes'), Yii::t('app', 'stopRes')];
            return $response->data = array('code' => 200,
                'subText' => Yii::t('app', 'user_monitor_help1', [
                    'user' => $params['product_key'],
                    'proc' => $procName
                ]),
                'xAxis' => $res['source']['xAxis'],
                'startRes' => $res['source']['startRes'],
                'stopRes' => $res['source']['stopRes'],
                'authRes' => $res['source']['authRes'],
                'updateRes' => $res['source']['updateRes'],
                'coaRes' => $res['source']['coaRes'],
                'dmRes' => $res['source']['dmRes'],
                'legend' => $legend,
            );
        } else {
            return $response->data = array('code' => $res['code'], 'error' => $res['error']
            );
        }
    }
    public function actionCloudErrorLogin()
    {
        $params = Yii::$app->request->queryParams;
        if (!empty($params['timePoint'])) {
            switch ($params['timePoint']) {
                case 1: //十分钟数据
                    $params['start_time'] = date('Y-m-d H:i:s', time() - 10 * 60);
                    $params['end_time'] = date('Y-m-d H:i:s');
                    break;
                case 2:
                    $params['start_time'] = date('Y-m-d H:i:s', time() - 30 * 60);
                    $params['end_time'] = date('Y-m-d H:i:s');
                    break;
                case 3:
                    $params['start_time'] = date('Y-m-d H:i:s', time() - 60 * 60);
                    $params['end_time'] = date('Y-m-d H:i:s');
                    break;
                case 4:
                    $params['start_time'] = date('Y-m-d 00:00:00');
                    $params['end_time'] = date('Y-m-d H:i:s');
                    break;
                case 5:
                    $params['start_time'] = date('Y-m-d H:i:s', strtotime(date('Y-m-d 00:00')) - (3600 * 24));
                    $params['end_time'] = date('Y-m-d 23:59:59', mktime(0,0,0,date('m'),date('d'),date('Y'))-1);
                    break;
                case 6:
                    $params['start_time']= date("Y-m-d H:i:s", mktime(0, 0, 0, date("m"), date("d") - date("w") + 1, date("Y")));
                    $params['end_time'] = date('Y-m-d H:i:s');
                    break;
                case 7:
                    $params['start_time']= date("Y-m-d H:i:s", mktime(0, 0, 0, date("m"), date("d") - date("w")-6, date("Y")));
                    $params['end_time']= date("Y-m-d 23:59:59", mktime(0, 0, 0, date("m"), date("d") - date("w"), date("Y")));
                    break;
                case 8:
                    $params['start_time']= date("Y-m-d H:i:s", mktime(0,0,0,date('m'),1,date('Y')));
                    $params['end_time']= date("Y-m-d 23:59:59",mktime(23,59,59,date('m'),date('t'),date('Y')));
                    break;
                default:
                    $params['start_time'] = date('Y-m-d H:i:s', time() - 30 * 60);
                    $params['end_time'] = date('Y-m-d H:i:s');
            }
        }
        $model = new CloundSrunLoginLog();
        $data = $model->getAllData($params);
        $code = isset($data['code']) ? $data['code'] : '';
        $msg = isset($data['error']) ? $data['error'] : '';
        $count = isset($data['total']) ? $data['total'] : '';;
        $list = isset($data['list']) ? $data['list'] : [];
        $offset = isset($params['offset']) && $params['offset'] > 0 ? $params['offset'] : 10;
        $pagination = new Pagination([
            'defaultPageSize' => $offset,
            'totalCount' =>$count,
        ]);
        if ($code != '200') {
            Yii::$app->getSession()->setFlash('error', $msg);
        }

        return $this->render('cloud-err-login', [
            'model' => $model,
            'list' => $list,
            'params' => $params,
            'pagination' => $pagination
        ]);
    }

    /**
     * 显示云端账户系统分区使用状况
     * @return string
     */
    public function actionCloudSystemStatus()
    {
        set_time_limit(0);
        $params = Yii::$app->request->queryParams;
        $timePoint = (!empty($params) && isset($params['timePoint'])) ? $params['timePoint'] : '';
        $action = isset($params['action']) ? $params['action'] : 'history';
        $timeStaEnd = $this->getTime($timePoint);
        if (empty($params)) {
            $params['start_time'] = date('Y-m-d');
            $params['end_time'] = date('Y-m-d H:i');
        }

        if (!empty($timePoint)) {
            $params['start_time'] =  $timeStaEnd['start_time'] ;
            $params['end_time'] = $timeStaEnd['end_time'];
        }

        $diff = (!empty($params)) ? strtotime($params['end_time']) - strtotime($params['start_time']) : 86400;
        //var_dump($diff);
        //var_dump($diff, $params, $timeStaEnd);exit;
        if ($diff > 86400) {
            $model = new CloudPartitionsDay();
        } else if ($diff > 3600){
            $model = new CloudPartitionsHour();
        } else {
            $model = new CloundPartitionsStatus();
        }
        //var_dump($model);exit;

        $data = [];

        //获取云端机器使用状态
        if ($action == 'history') {
            $data = $model->getAllData($params);
        } else {
            $data = $model->getTimeData($params);
        }
        //var_dump($data);exit;


        $code = (!empty($data) && isset($data['code'])) ? $data['code'] : '';
        $count = (!empty($data) && isset($data['count'])) ? $data['count'] : '';
        $msg = (!empty($data) && isset($data['error'])) ? $data['error'] : '';
        $offset = isset($params['offset']) && $params['offset'] > 0 ? $params['offset'] : 10;
        $pagination = new Pagination([
            'defaultPageSize' => $offset,
            'totalCount' =>$count,
        ]);
        $list = isset($data['data']) ? $data['data'] : [];
        //var_dump($list);exit;

        if (empty($code) || $code != 200) {
            //获取数据异常
            Yii::$app->getSession()->setFlash('error', $msg);
        }
        //var_dump($data);exit;
        //var_dump($model->searchField);exit;

        return $this->render('system-status',[
            'params' => $params,
            'data' => $list,
            'pagination' => $pagination,
            'model' => $model,
            'action' => $action,
        ]);
    }
    /**
     * 显示云端账户系统内存使用状况
     * @return string
     */
    public function actionCloudSystemsStatus()
    {
        set_time_limit(0);
        $params = Yii::$app->request->queryParams;
        $timePoint = (!empty($params) && isset($params['timePoint'])) ? $params['timePoint'] : '';
        $timeStaEnd = $this->getTime($timePoint);
        if (!empty($timePoint)) {
            $params['start_time'] =  $timeStaEnd['start_time'] ;
            $params['end_time'] = $timeStaEnd['end_time'];
        }


        $model = new CloundMemoryStatus();

        //获取云端机器使用状态
        $data = $model->getAllData($params);

        $code = (!empty($data) && isset($data['code'])) ? $data['code'] : '';
        $count = (!empty($data) && isset($data['count'])) ? $data['count'] : '';
        $msg = (!empty($data) && isset($data['error'])) ? $data['error'] : '';
        $offset = isset($params['offset']) && $params['offset'] > 0 ? $params['offset'] : 10;
        $pagination = new Pagination([
            'defaultPageSize' => $offset,
            'totalCount' =>$count,
        ]);
        $list = isset($data['data']) ? $data['data'] : [];
        // var_dump($list);exit;

        if (empty($code) || $code != 200) {
            //获取数据异常
            Yii::$app->getSession()->setFlash('error', $msg);
        }
        //var_dump($data);exit;
        //var_dump($model->searchField);exit;

        return $this->render('systems-status',[
            'params' => $params,
            'data' => $list,
            'pagination' => $pagination,
            'model' => $model,
        ]);
    }

    private function getTime($timePoint)
    {

        $params = [];
        if (!empty($timePoint)) {
            switch ($timePoint) {
                case 1: //十分钟数据
                    $params['start_time'] = date('Y-m-d H:i:s', time() - 10 * 60);
                    $params['end_time'] = date('Y-m-d H:i:s');
                    break;
                case 2:
                    $params['start_time'] = date('Y-m-d H:i:s', time() - 30 * 60);
                    $params['end_time'] = date('Y-m-d H:i:s');
                    break;
                case 3:
                    $params['start_time'] = date('Y-m-d H:i:s', time() - 60 * 60);
                    $params['end_time'] = date('Y-m-d H:i:s');
                    break;
                case 4:
                    $params['start_time'] = date('Y-m-d 00:00:00');
                    $params['end_time'] = date('Y-m-d H:i:s');
                    break;
                case 5:
                    $params['start_time'] = date('Y-m-d H:i:s', strtotime(date('Y-m-d 00:00')) - (3600 * 24));
                    $params['end_time'] = date('Y-m-d 23:59:59', mktime(0, 0, 0, date('m'), date('d'), date('Y')) - 1);
                    break;
                case 6:
                    $params['start_time'] = date("Y-m-d H:i:s", mktime(0, 0, 0, date("m"), date("d") - date("w") + 1, date("Y")));
                    $params['end_time'] = date('Y-m-d H:i:s');
                    break;
                case 7:
                    $params['start_time'] = date("Y-m-d H:i:s", mktime(0, 0, 0, date("m"), date("d") - date("w") - 6, date("Y")));
                    $params['end_time'] = date("Y-m-d 23:59:59", mktime(0, 0, 0, date("m"), date("d") - date("w"), date("Y")));
                    break;
                case 8:
                    $params['start_time'] = date("Y-m-d H:i:s", mktime(0, 0, 0, date('m'), 1, date('Y')));
                    $params['end_time'] = date("Y-m-d 23:59:59", mktime(23, 59, 59, date('m'), date('t'), date('Y')));
                    break;
                default:
                    $params['start_time'] = date('Y-m-d H:i:s', time() - 30 * 60);
                    $params['end_time'] = date('Y-m-d H:i:s');
            }
        }
        return $params;
    }
}