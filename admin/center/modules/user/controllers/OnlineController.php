<?php
/**
 * Created by PhpStorm.
 * User: ligang
 * Date: 2015/1/14
 * Time: 11:59
 */

namespace center\modules\user\controllers;

use center\controllers\ValidateController;
use center\modules\auth\models\SrunJiegou;
use center\modules\user\models\OnlinePppoePoint;
use center\modules\user\models\OnlinePppoe;
use center\modules\user\models\Base;
use center\modules\strategy\models\Product;
use common\models\Redis;
use common\models\User;
use yii;
use center\modules\user\models\Online;
use yii\data\Pagination;
use yii\base\Object;
use common\models\FileOperate;
use common\extend\Excel;
use  yii\helpers\Url;
use center\extend\Tool;
use center\modules\strategy\models\IpArea;

class OnlineController extends ValidateController
{
    //允许导出最大在线用户数
    const ONLINE_USER_EXPORT_LIMIT = 5000;

    /**
     * 在线列表
     * @return string
     * @throws yii\base\InvalidConfigException
     */
    public function actionIndex()
    {
        set_time_limit(0);
        //请求的参数
        $params = Yii::$app->request->queryParams;
        $post = Yii::$app->request->post();
        $params = !empty($params) ? $params : $post;
        $vlanZone = (!empty($params) && !empty($params['showField']) && in_array('vlan_zone', $params['showField'])) ? true : false;
        if (!empty($params) && !empty($params['showField']) && in_array('vlan_zone', $params['showField'])) {
            $pos = array_search('vlan_zone', $params['showField']);
            unset($params['showField'][$pos]);
        }
        $ipZone = (!empty($params) && !empty($params['showField']) && in_array('ip_zone', $params['showField'])) ? true : false;
        $ipPos = '';
        if ($ipZone) {
            $ipPos = array_search('ip_zone', $params['showField']);
            unset($params['showField'][$ipPos]);
        }

        $export = isset($params['export']) ? $params['export'] : '';

        $corePanels = Online::corePanels();
        // 从redis中获取用户默认的在线菜单
        $panelKey = 'key:user:online:panel';
        $panel = Redis::executeCommand('get', $panelKey, [], 'redis_manage');
        $panel = isset($params['panel']) ? $params['panel'] : ($panel ? $panel : 'radius');
        $panel = array_key_exists($panel, $corePanels) ? $panel : 'radius';
        // 保存在redis中
        Redis::executeCommand('set', $panelKey, [$panel], 'redis_manage');
        $params['panel'] = $panel;
        //创建对象
        $model = \Yii::createObject($corePanels[$panel]);
        //类名称
        $modelClass = $corePanels[$panel]['class'];
        //表名称
        $modelTable = $modelClass::tableName();
        $list = $groups = $products = [];

        if ($params["panel"] == "proxy")//代理表直接查询REDIS
        {
            $paramKey = 'key:user:online:' . $panel . ':params:' . Yii::$app->user->identity->username;

            //整理要查询数据库的字段
            if (empty($params['showField'])) {
                //将记录保存在redis中
                $defaultField = Redis::executeCommand('get', $paramKey, [], 'redis_manage');
                $defaultField = $defaultField ? yii\helpers\Json::decode($defaultField) : false;
                $params['showField'] = is_array($defaultField) ? $defaultField : $model->defaultField;
            }
            if (isset($params["user_name"]) && $params["user_name"])//按用户名查询
            {
                $page = (!empty($params) && isset($params['page'])) ? $params['page'] : 1;
                $rad_online_ids = $model->getProxyListByUserName($params["user_name"], $page);
            } else if (isset($params["ip"]) && $params["ip"]) //按IP查询
            {
                $rad_online_ids = $model->getProxyListByIp($params["ip"]);
            } else {
                if (!isset($params['page']))
                    $params['page'] = 1;
                $rad_online_ids = $model->getProxyList($params['page']);
            }
            if ($rad_online_ids) {
                $n = 0;
                foreach ($rad_online_ids as $k => $v) {
                    if ($v > 0) {
                        $list[$n]["rad_online_id"] = $v;
                        $n++;
                    }
                }
            }

            if (!empty($export)) {
                $count = $model->getSum();
                if ($count > 0) {
                    if ($count > self::ONLINE_USER_EXPORT_LIMIT) {
                        $logContent = Yii::t('app', 'group msg6', [
                            'mgr' => Yii::$app->user->identity->username,
                            'limit' => self::ONLINE_USER_EXPORT_LIMIT
                        ]);

                        Yii::$app->getSession()->setFlash('error', $logContent);
                    } else {
                        $excelData = [];
                        $excelData[0] = [];
                        $i = 1;

                        $timeArr = ['add_time', 'drop_time', 'update_time', 'keepalive_time'];
                        $times = array_intersect($params['showField'], $timeArr);
                        //需要去对应查找名称的字段
                        $nameArr = ['products_id', 'billing_id', 'control_id'];
                        $names = array_intersect($params['showField'], $nameArr);
                        //流量信息
                        $bytesArr = ['bytes_in', 'bytes_out', 'bytes_in6', 'bytes_out6', 'sum_bytes', 'remain_bytes'];
                        $bytes = array_intersect($params['showField'], $bytesArr);
                        //金额信息
                        $moneyArr = ['user_balance', 'user_charge'];
                        $moneys = array_intersect($params['showField'], $moneyArr);
                        //其他从redis中获取的数据
                        $otherItems = ['rad_online_id', 'session_id', 'domain', 'uid', 'ip6', 'nas_ip1', 'nas_port',
                            'nas_port_id', 'station_id', 'filter_id', 'pbhk', 'vlan_id1', 'vlan_id2', 'line_type', 'os_name',
                            'class_name', 'client_type', 'condition'
                        ];
                        $others = array_intersect($params['showField'], $otherItems);


                        foreach ($list as $value) {
                            $redisData = $model->getValueInRedis($value["rad_online_id"]);
                            //流量处理
                            if (in_array('bytes_in', $params['showField'])) {
                                $value['bytes_in'] = Tool::bytes_format($redisData['bytes_in'] - $redisData['bytes_in1']);
                            }
                            if (in_array('bytes_out', $params['showField'])) {
                                $value['bytes_out'] = Tool::bytes_format($redisData['bytes_out'] - $redisData['bytes_out1']);
                            }

                            if (isset($value['rad_online_id'])) {
                                unset($value['rad_online_id']);
                            }
                            foreach ($bytes as $byte) {
                                $value[$byte] = Tool::bytes_format($redisData[$byte]);
                            }
                            foreach ($moneys as $money) {
                                $value[$money] = number_format($redisData[$money], 2);
                            }

                            if (!empty($times)) {
                                foreach ($times as $time) {
                                    $value[$time] = date('Y-m-d H:i:s', $value[$time]);
                                }
                            }
                            foreach ($names as $name) {
                                $value[$name] = $redisData[$name] . ':' . $model->getNameInRedis($name, $redisData[$name]);
                            }
                            foreach ($others as $other) {
                                $value[$other] = isset($redisData[$other]) ? $redisData[$other] : '';
                            }


                            if (empty($excelData[0])) {
                                foreach (array_keys($value) as $k => $v) {
                                    $excelData[0][] = $model->searchField[$v];
                                }
                            }
                            $excelData[$i] = array_values($value);
                            $i++;
                        }

                        $file = FileOperate::dir('account') . 'batch' . '_' . date('YmdHis') . '.xls';
                        $title = Yii::t('app', 'batch export');
                        Excel::header_file($list, $file, $title);

                        exit;
                    }

                } else {
                    Yii::$app->getSession()->setFlash('error', Yii::t('app', 'no record'));
                }
            }
            //分页
            $pagination = new Pagination([
                'defaultPageSize' => 20,
                'totalCount' => $model->getSum(),
            ]);
        } else {
            //创建查询
            $query = $modelClass::find();

            $paramKey = 'key:user:online:' . $panel . ':params:' . Yii::$app->user->identity->username;

            //整理要查询数据库的字段
            if (empty($params['showField'])) {
                //将记录保存在redis中
                $defaultField = Redis::executeCommand('get', $paramKey, [], 'redis_manage');
                $defaultField = $defaultField ? yii\helpers\Json::decode($defaultField) : false;
                $params['showField'] = is_array($defaultField) ? $defaultField : $model->defaultField;
            }
            $sortField = [];
            //无论如何要搜索id字段
            $query->addSelect($modelTable . '.' . $model->primaryKey()[0]);
            foreach ($params['showField'] as $val) {
                if (array_key_exists($val, $model->searchField)) {
                    if($val !== 'group_id'){
                        $query->addSelect($modelTable . '.' . $val);
                    }
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
                        case 'start_add_time':
                            $query->andWhere(['>=', $modelTable . '.add_time', strtotime($value)]);
                            break;
                        case 'end_add_time':
                            $query->andWhere(['<=', $modelTable . '.add_time', strtotime($value)]);
                            break;
                        case 'err_msg':
                            $query->andWhere(['like', $modelTable . '.err_msg', $value]);
                            break;
                        case 'ip': //ip需要右模糊查询，其他字段按照这样添加即可
                            $query->andWhere("ip LIKE :$field", [":$field" => "$value%"]);
                            break;
                        case 'group_id': //忽略联查的字段
                            break;
                        case 'products_id':
                            $query->andWhere(['=', $modelTable . '.products_id', $value]);
                            break;
                        default:
                            if (array_key_exists($field, $model->searchInput)) {
                                if (empty ($params['vague_tag'])) {
                                    $query->andWhere(['=', $modelTable . '.' . $field, $value]);
                                } else {
                                    $query->andWhere(['like', $modelTable . '.' . $field, $value]);
                                }
                                //$query->andWhere("$field LIKE :$field", [":$field"=>"%$value%"]);
                            }
                            break;
                    }
                }
            }

            //产品列表
            $product_model = new Product();
            $products = $product_model->getNameOfList();

            //加入组织结构
            //如果非超级管理员，则需要去判断
            if (isset($params["group_id"]) && !empty($params['group_id'])) {
                $group_id = explode(',', $params['group_id']);
                $canMgrOrg = SrunJiegou::getNodeId($group_id);
                $userTable = Base::tableName();
                $is_left = true;
                $query->leftJoin($userTable, $userTable . '.user_name=' . $modelTable . '.user_name');
                $query->andWhere([$userTable . '.group_id' => $canMgrOrg]);
            }
            if (!User::isSuper()) {
                //所有可以管理的组
                $canMgrOrg = SrunJiegou::getAllNode();
                if(!isset($is_left)){
                    $userTable = Base::tableName();
                    $query->leftJoin($userTable, $userTable . '.user_name=' . $modelTable . '.user_name');
                }
                $query->andWhere([$userTable . '.group_id' => $canMgrOrg]);
                //判断产品
                //所有可以管理的产品
                $proKey = array_keys($products);
                $query->andWhere(['products_id' => $proKey]);
            }

            //组织结构数组
            $groups = SrunJiegou::getAllIdNameVal();


            //排序
            if (isset($params['orderBy']) && array_key_exists($params['orderBy'], $model->searchField)) {
                $query->orderBy([$modelTable . '.' . $params['orderBy'] => $params['sort'] == 'desc' ? SORT_DESC : SORT_ASC]);
            } else {
                $query->orderBy([$modelTable . '.' . $model->primaryKey()[0] => SORT_DESC]);
            }

            //分页
            $pagination = new Pagination([
                'defaultPageSize' => 20,
                'totalCount' => $query->count(),
            ]);
            if (!empty($vlanZone)) {
                $vlan_id = $model->findBySql("SELECT userarea_id,vlan_id FROM rad_user_area")->asArray()->all();
                $vlans = [];
                foreach ($vlan_id as &$v) {
                    $vlans[$v['userarea_id']] = [];
                    $data = explode(',', $v['vlan_id']);
                    if (count($data) > 1) {
                        foreach ($data as $vlan_zone) {
                            $vlan_other = explode('-', $vlan_zone);
                            if (count($vlan_other) > 1) {
                                if (!empty($vlans[$v['userarea_id']])) {
                                    $vlans[$v['userarea_id']] = array_merge($vlans[$v['userarea_id']], range(trim($vlan_other[0]), trim($vlan_other[1])));
                                } else {
                                    $vlans[$v['userarea_id']] = array_merge(range(trim($vlan_other[0]), trim($vlan_other[1])));
                                }

                            } else {
                                if (!empty($vlans[$v['userarea_id']])) {
                                    $vlans[$v['userarea_id']] = array_merge($vlans[$v['userarea_id']], [$vlan_zone]);
                                } else {
                                    $vlans[$v['userarea_id']] = [[$vlan_zone]];
                                }

                            }
                        }
                    } else {
                        $vlan_other = explode('-', $v['vlan_id']);
                        if (count($vlan_other) > 1) {
                            $vlans[$v['userarea_id']] = range(trim($vlan_other[0]), trim($vlan_other[1]));
                        } else {
                            $vlans[$v['userarea_id']] = [$v['vlan_id']];
                        }
                    }

                }
            }


            if (!empty($export)) {

                //导出在线用户
                //列表
                try {
                    $count = $query->count();
                    if ($count > 0) {
                        if ($count > self::ONLINE_USER_EXPORT_LIMIT) {
                            $logContent = Yii::t('app', 'group msg6', [
                                'mgr' => Yii::$app->user->identity->username,
                                'limit' => self::ONLINE_USER_EXPORT_LIMIT
                            ]);

                            Yii::$app->getSession()->setFlash('error', $logContent);
                        } else {
                            $list = $query->offset($pagination->offset)
                                ->asArray()
                                ->all();
                            $excelData = [];
                            $excelData[0] = [];
                            $i = 1;
                            $timeArr = ['add_time', 'drop_time', 'update_time', 'keepalive_time'];
                            $times = array_intersect($params['showField'], $timeArr);
                            //需要去对应查找名称的字段
                            $nameArr = ['products_id', 'billing_id', 'control_id'];
                            $names = array_intersect($params['showField'], $nameArr);
                            //流量信息
                            $bytesArr = ['bytes_in', 'bytes_out', 'bytes_in6', 'bytes_out6', 'sum_bytes', 'remain_bytes'];
                            $bytes = array_intersect($params['showField'], $bytesArr);
                            //金额信息
                            $moneyArr = ['user_balance', 'user_charge'];
                            $moneys = array_intersect($params['showField'], $moneyArr);
                            //其他从redis中获取的数据
                            $otherItems = ['rad_online_id', 'session_id', 'domain', 'uid', 'ip6', 'nas_ip1', 'nas_port',
                                'nas_port_id', 'station_id', 'filter_id', 'pbhk', 'vlan_id1', 'vlan_id2', 'line_type', 'os_name',
                                'class_name', 'client_type', 'condition',
                            ];
                            $others = array_intersect($params['showField'], $otherItems);


                            foreach ($list as $value) {
                                $redisData = $model->getValueInRedis($value["rad_online_id"]);
                                //流量处理
                                if (in_array('bytes_in', $params['showField'])) {
                                    $value['bytes_in'] = Tool::bytes_format($redisData['bytes_in'] - $redisData['bytes_in1']);
                                }
                                if (in_array('bytes_out', $params['showField'])) {
                                    $value['bytes_out'] = Tool::bytes_format($redisData['bytes_out'] - $redisData['bytes_out1']);
                                }

                                if (isset($value['rad_online_id'])) {
                                    unset($value['rad_online_id']);
                                }
                                foreach ($bytes as $byte) {
                                    $value[$byte] = Tool::bytes_format($redisData[$byte]);
                                }
                                foreach ($moneys as $money) {
                                    $value[$money] = number_format($redisData[$money], 2);
                                }

                                if (!empty($times)) {
                                    foreach ($times as $time) {
                                        $value[$time] = date('Y-m-d H:i:s', $value[$time]);
                                    }
                                }
                                foreach ($names as $name) {
                                    $value[$name] = $redisData[$name] . ':' . $model->getNameInRedis($name, $redisData[$name]);
                                }
                                foreach ($others as $other) {
                                    $value[$other] = isset($redisData[$other]) ? $redisData[$other] : '';
                                }


                                if (empty($excelData[0])) {
                                    foreach (array_keys($value) as $k => $v) {
                                        $excelData[0][] = $model->searchField[$v];
                                    }
                                }
                                $excelData[$i] = array_values($value);
                                $i++;
                            }

                            $title = Yii::t('app', 'batch export');
                            $file = FileOperate::dir('account') . 'batch' . '_' . date('YmdHis') . '.xls';

                            Excel::header_file($excelData, $file, $title);

                            exit;
                        }
                    } else {
                        Yii::$app->getSession()->setFlash('error', Yii::t('app', 'no record'));
                    }

                } catch (\Exception $e) {
                    Yii::$app->getSession()->setFlash('error', $e->getMessage());
                    return false;
                }


            } else {
                //列表
                $list = $query->offset($pagination->offset)
                    ->limit($pagination->limit)
                    ->asArray()
                    ->all();


                if ($ipPos) {
                    $ipModel = new IpArea();
                    $query = $ipModel->find();
                    $query->select('area_id, area_name, area_ip_start, area_ip_end');
                    $ipData = $query->asArray()->all();
                    $ipZones = [];
                    if (!empty($ipData)) {
                        foreach ($ipData as $ip) {
                            for ($lip = $ip['area_ip_start'], $end = $ip['area_ip_end']; $lip <= $end; $lip++) {
                                $lipAddr = long2ip($lip);
                                $ipZones[$ip['area_id']][] = $lipAddr;
                            }
                            $ipZones[$ip['area_id']][] = $ip['area_name'];
                        }
                    }
                }


                foreach ($list as &$v) {
                    if (!empty($vlanZone)) {
                        // 获取vlan区域
                        foreach ($vlans as $key => $vlan) {
                            if (in_array($v['vlan_id'], $vlan)) {
                                $vans = $model->findBySql("SELECT user_area FROM rad_user_area WHERE userarea_id=" . $key)->asArray()->all();
                                if ($vans) {
                                    $v['vlan_zone'] = $vans[0]['user_area'];
                                    continue;
                                }
                            }
                        }
                        $v['vlan_zone'] = isset($v['vlan_zone']) ? $v['vlan_zone'] : $v['vlan_id'];
                    }
                    if (!empty($ipZone)) {
                        if (!empty($ipZones)) {
                            foreach ($ipZones as $i => $ip) {
                                if (in_array($v['ip'], $ip)) {
                                    $v['ip_zone'] = array_pop($ip);
                                    continue;
                                }
                            }
                        }
                    }
                    $v['ip_zone'] = isset($v['ip_zone']) ? $v['ip_zone'] : '';
                }
            }

            if (!empty($pos)) {
                $params['showField'][$pos] = 'vlan_zone';
            }

            if (!empty($ipPos)) {
                $params['showField'][$ipPos] = 'ip_zone';
            }
            ksort($params['showField']);
        }

            return $this->render('index', [
                'model' => $model,
                'list' => $list,
                'pagination' => $pagination,
                'params' => $params,
                'groups' => $groups,
                'products' => $products,
            ]);

        }

        /**
         * 下线操作
         * @param $panel
         * @param $id
         * @return yii\web\Response
         * @throws yii\web\BadRequestHttpException
         * @throws yii\web\ForbiddenHttpException
         * @throws yii\web\NotFoundHttpException
         */
        public
        function actionDrop($panel, $id)
        {
            $corePanels = Online::corePanels();
            if (!array_key_exists($panel, $corePanels)) {
                throw new yii\web\BadRequestHttpException(Yii::t('app', 'message 400'));
            }

            if ($panel == proxy)//代理表由于本地没有用户，不需要检测权限，直接踢掉
            {
                $my_ip = Redis::executeCommand("HGET", "hash:proxy:" . $id, ["my_ip"], "redis_online");
                $array["action"] = 5; //proxy dm
                $array["serial_code"] = time() . rand(111111, 999999); //唯一的流水号
                $array["time"] = time();
                $array["online_id"] = $id;
                $json = json_encode($array);
                $res = Redis::executeCommand("RPUSH", "list:rad_dm:" . $my_ip, [$json], "redis_online");
            } else {
                Online::getModel($panel, $id);

                if (!Online::$model) {
                    Yii::$app->getSession()->setFlash('error', Yii::t('app', 'user online help5'));
                    return $this->goBack(Yii::$app->request->getReferrer());
                    //throw new yii\web\NotFoundHttpException(Yii::t('app', 'message 404'));
                }

                //如果非超级管理员，则需要去判断
                if (!User::isSuper()) {
                    $userModel = Base::findOne(['user_name' => Online::$model->user_name]);
                    if (!$userModel) {
                        throw new yii\web\NotFoundHttpException(Yii::t('app', 'The user does not exist.'));
                    }
                    //判断是否可以管理此用户
                    if (!User::canManage('org', $userModel->group_id)) {
                        throw new yii\web\ForbiddenHttpException(Yii::t('app', 'message 401 3'));
                    }
                }

                $res = Online::dropOnline($panel);
            }


            if ($res) {
                Yii::$app->getSession()->setFlash('success', Yii::t('app', 'operate success.'));
            } else {
                Yii::$app->getSession()->setFlash('error', Yii::t('app', 'operate failed.'));
            }
            return $this->goBack(Yii::$app->request->getReferrer());
        }

    /**
     * 运营商在线
     */
    public function actionIps(){
        $model = new OnlinePppoe();
        $services = $model->getServices();
        $report = new OnlinePppoePoint();

        $data = [];
        foreach($services  as $one){
            //服务号
            $report->service_name = $one;
            //当前在线数
            $data[$one]['count'] = $model->getOnlineNum($one);
            //在线数报表
            $data[$one]['source'] = $report->getOnlineByService($report);
        }

        return $this->render('ips',[
            'data' => $data
        ]);
    }
}