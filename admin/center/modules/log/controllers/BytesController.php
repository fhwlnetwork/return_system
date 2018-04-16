<?php

namespace center\modules\log\controllers;


use center\modules\auth\models\SrunJiegou;
use center\modules\log\models\Detail;
use center\modules\strategy\models\IpArea;
use center\modules\user\models\Base;
use common\extend\Excel;
use common\models\Redis;
use common\models\User;
use center\controllers\ValidateController;
use yii\data\Pagination;
use yii;
class BytesController extends ValidateController{
    public function actionIndex(){
        $params = Yii::$app->request->queryParams;
        //如果不输入任何条件，就查询当天的明细
        if (empty($params)) {
            $params["start_add_time"] = date("Y-m-d");
        }
        $model = new Detail();
        $query = Detail::find();

        //无论如何要搜索 detail_id 字段
        $query->select('detail_id');

        $tableName = Detail::tableName();
        $userTable = Base::tableName();

        //判断上网明细和主库是否同一个服务器
        //如果是同一台服务器,则做权限判断，如果没在同一台，则不做权限判断
        if (Yii::$app->params['dbConfig']['hostname'] == Yii::$app->params['dbConfig']['detail_hostname']) {
            $oneHost = true; //同一台服务器
            $tableName = Yii::$app->params['dbConfig']['detail_dbname'] . '.' . $tableName;
            $userTable = Yii::$app->params['dbConfig']['dbname'] . '.' . $userTable;
        } else {
            $oneHost = false;
        }

        // 从redis中获取用户默认的在线菜单
        $paramKey = 'key:log:detail:search:params';
        $paramRedis = Redis::executeCommand('get', $paramKey, [], 'redis_manage');

        //整理要查询数据库的字段
        if (empty($params['showField'])) {
            // 从redis中获取此管理员之前勾选过的字段
            $defaultField = $paramRedis ? yii\helpers\Json::decode($paramRedis) : false;
            $params['showField'] = is_array($defaultField) ? $defaultField : $model->defaultField;
        }
        $sortField = [];
        //处理要显示的列
        foreach ($params['showField'] as $val) {
            if (array_key_exists($val, $model->searchField)) {
                //将搜索字段压入新数组
                $sortField[$val] = $model->searchField[$val];
            }
        }
        Redis::executeCommand('set', $paramKey, [yii\helpers\Json::encode($params['showField'])], 'redis_manage'); //将记录保存在redis中

        $model->searchField = $sortField + $model->searchField; //重新排序searchField

        //过滤查询条件字段
        foreach ($params as $field => $value) {
            if ($value != '') {

                switch ($field) {
                    case 'start_add_time':
                        $query->andWhere(['>=', $tableName . '.add_time', strtotime($value)]);
                        break;
                    case 'end_add_time':
                        $query->andWhere(['<', $tableName.'.drop_time', strtotime('+1 days '.$value)]);
                        break;
                    case 'user_ip_start':
                        if(preg_match('/^((25[0-5]|2[0-4]\d|[01]?\d\d?)\.){3}(25[0-5]|2[0-4]\d|[01]?\d\d?)$/', $value)){
                            $query->andWhere(['>=', $tableName.'.user_ip_int', bindec(decbin(ip2long($value)))]);
                        }
                        break;
                    case 'user_ip_end':
                        if(preg_match('/^((25[0-5]|2[0-4]\d|[01]?\d\d?)\.){3}(25[0-5]|2[0-4]\d|[01]?\d\d?)$/', $value)){
                            $query->andWhere(['<=', $tableName.'.user_ip_int', bindec(decbin(ip2long($value)))]);
                        }
                        break;
                    case 'user_ip6_start':
                        if(preg_match('/^([\da-fA-F]{1,4}:){7}[\da-fA-F]{1,4}$/', $value)){
                            $query->andWhere(['>=', $tableName.'.user_ip6_int', $this->ipv62long($value)]);
                        }
                        break;
                    case 'user_ip6_end':
                        if(preg_match('/^([\da-fA-F]{1,4}:){7}[\da-fA-F]{1,4}$/', $value)){
                            $query->andWhere(['<=', $tableName.'.user_ip6_int', $this->ipv62long($value)]);
                        }
                        break;
                    default:
                        if (array_key_exists($field, $model->searchField)) {
                            $query->andWhere(['=', $tableName . '.' . $field, $value]);
                        }
                        break;
                }
            }
        }

        //如果是同一台服务器上and非超管，用组织结构过滤
        if ($oneHost && !User::isSuper()) {
            //所有可以管理的组
            $canMgrOrg = SrunJiegou::getAllNode();
            $canMgrOrg = array_merge([0], $canMgrOrg);
            $query->andWhere(['group_id' => $canMgrOrg]);
            //判断产品
            //所有可以管理的产品
            $proKey = array_keys($model->products);
            $query->andWhere(['products_id' => $proKey]);
        }

        //排序
        if (isset($params['orderBy']) && array_key_exists($params['orderBy'], $model->searchField)) {
            $query->orderBy([$tableName . '.' . $params['orderBy'] => $params['sort'] == 'desc' ? SORT_DESC : SORT_ASC]);
        } else {
            $query->orderBy(['detail_id' => SORT_DESC]);
        }

        //生成excel
        if (isset($params['action']) && !empty($params['action'])) {

            $lists = $query
                ->asArray()
                ->all();
            if (count($lists) > self::DETAIL_EXPORT_LIMIT) {
                Yii::$app->session->setFlash('error', Yii::t('app', 'batch export help1', [
                    'num' => self::DETAIL_EXPORT_LIMIT,
                ]));
            } elseif (count($lists) == 0) {
                Yii::$app->session->setFlash('error', Yii::t('app', 'batch export help2'));
            } else {
                $listAllData = $model->findAllPageData($lists, $params); // 获取指定 id 数据.
                $list = $model->formatExcelData($listAllData, $params['showField']); // 将数据进行过滤, 获取指定列的数据.

                $title = Yii::t('app', 'Detail Log');
                $file = $title . '.xls';
                Excel::header_file($list, $file, $title);
                exit;
            }

        }

        //分页
        $pagination = new Pagination([
            'defaultPageSize' => $offset = !isset($params["offset"]) || empty($params["offset"]) ? 20 : $params["offset"],
            'totalCount' => $query->count('detail_id'),
        ]);

        $resArr = $model->searchData($params, $query); //对搜索的数据进行统计总和处理  比如总流量 总时长

        //列表
        $lists = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->asArray()
            ->all();

        $listAllData = $model->findAllPageData($lists, $params); // 获取指定 id 数据.
        $list = $model->buildSearchField($listAllData, $params['showField']); // 将数据进行过滤, 获取指定列的数据.

        if (in_array('ip_zone', $params['showField'])) {
            $ipModel = new IpArea();
            $query = $ipModel->find();
            $query->select('area_id, area_name, area_ip_start, area_ip_end');
            $ipData = $query->asArray()->all();
            $ipZone = [];
            if (!empty($ipData)) {
                foreach ($ipData as $ip) {
                    for ($lip = $ip['area_ip_start'],$end = $ip['area_ip_end']; $lip <= $end; $lip++) {
                        $lipAddr = long2ip($lip);
                        $ipZone[$ip['area_id']][] = $lipAddr;
                    }
                    $ipZone[$ip['area_id']][] = $ip['area_name'];
                }
            }
        }

        if (in_array('vlan_zone', $params['showField'])) {
            $vlan_id = $model->findBySql("SELECT userarea_id,vlan_id,sub_ip FROM rad_user_area")->asArray()->all();
            $vlans = [];
            $ips = [];
            foreach ($vlan_id as &$v) {
                $vlans[$v['userarea_id']] = [];
                $data = explode(',', $v['vlan_id']);
                $ip = explode(',', $v['sub_ip']);
                if (count($ip) > 1) {
                    foreach ($ip as $ip_address) {
                        $flag1 = strpos($ip_address, '-');
                        $flag2 = strpos($ip_address, '/');
                        if ($flag1) {
                            $ip_area_zone = explode('-', $ip_address);
                            $pos = strrpos($ip_area_zone[0], '.');
                            $prev = trim(substr($ip_area_zone[0], 0, $pos));
                            $start = trim(substr($ip_area_zone[0], $pos + 1));
                            $end = trim(substr($ip_area_zone[1], $pos + 1));
                            for ($i = $start; $i <= $end; $i++) {
                                $ips[$v['userarea_id']][] = $prev . '.' . $i;
                            }
                        }
                        if ($flag2) {
                            if (preg_match("/^([\d]{1,3}.[\d]{1,3}.[\d]{1,3}.[\d]{1,3})\/([\d]{1,2})$/", $ip_address, $arr)) {
                                $ip_test = $arr[1];
                                $mask = $arr[2];
                                $ip_mask = (0xffffffff << (32 - $mask)) & 0xffffffff;
                                $lip = ip2long($ip_test);
                                $ip_start = ($lip & $ip_mask) + 1;
                                $ip_end = (~($lip ^ $ip_mask));
                            }
                            for ($lip = $ip_start; $lip <= $ip_end; $lip++) {
                                $ip_addr = long2ip($lip);
                                $ips[$v['userarea_id']][] = $ip_addr;
                            }
                        }
                        if (!$flag1 && !$flag2) {
                            if (!empty($ips[$v['userarea_id']])) {
                                $ips[$v['userarea_id']][] = $ip_address;
                            } else {
                                $ips[$v['userarea_id']][] = [$ip_address];
                            }

                        }
                    }
                } else {
                    $flag1 = strpos($ip[0], '-');
                    $flag2 = strpos($ip[0], '/');
                    if ($flag1) {
                        $ip_area_zone = explode('-', $ip[0]);
                        $pos = strrpos($ip_area_zone[0], '.');
                        $prev = trim(substr($ip_area_zone[0], 0, $pos));
                        $start = trim(substr($ip_area_zone[0], $pos + 1));
                        $end = trim(substr($ip_area_zone[1], $pos + 1));
                        for ($i = $start; $i <= $end; $i++) {
                            $ips[$v['userarea_id']][] = $prev . '.' . $i;
                        }
                    }
                    if ($flag2) {
                        if (preg_match("/^([\d]{1,3}.[\d]{1,3}.[\d]{1,3}.[\d]{1,3})\/([\d]{1,2})$/", $ip[0], $arr)) {
                            $ip = $arr[1];
                            $mask = $arr[2];
                            $ip_mask = (0xffffffff << (32 - $mask)) & 0xffffffff;
                            $lip = ip2long($ip);
                            $ip_start = ($lip & $ip_mask) + 1;
                            $ip_end = (~($lip ^ $ip_mask));
                        }
                        for ($lip = $ip_start; $lip <= $ip_end; $lip++) {
                            $ip_addr = long2ip($lip);
                            $ips[$v['userarea_id']][] = $ip_addr;
                        }
                    }
                    if (!$flag1 && !$flag2) {
                        $ips[$v['userarea_id']] = [$ip[0]];
                    }
                }
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

        //格式化列表数据，比如流量，时长，其他的信息
        if ($list) {
            foreach ($list as $k => $v) {
                //  获取vlan区域
                if (in_array('vlan_zone', $params['showField'])) {
                    if (empty($v['vlan_id'])) {
                        foreach ($ips as $key1 => $ip) {
                            if (in_array($v['user_ip'], $ip)) {
                                $vans = $model->findBySql("SELECT user_area FROM rad_user_area WHERE userarea_id=" . $key1)->asArray()->one();
                                $v['vlan_zone'] = $vans['user_area'];
                                continue;
                            }

                        }
                    }
                    if (!empty($v['vlan_id'])) {
                        foreach ($vlans as $key => $vlan) {
                            if (in_array($v['vlan_id'], $vlan)) {
                                $vans = $model->findBySql("SELECT user_area FROM rad_user_area WHERE userarea_id=" . $key)->asArray()->one();
                                $v['vlan_zone'] = $vans['user_area'];
                            }
                        }
                    }
                    $v['vlan_zone'] = isset($v['vlan_zone']) ? $v['vlan_zone'] : $v['vlan_id'];
                }
                if (in_array('ip_zone', $params['showField'])) {
                    if (!empty($ipZone)) {
                        foreach ($ipZone  as $i => $ip) {
                            if (in_array($v['user_ip'], $ip)) {
                                $v['ip_zone'] = array_pop($ip);
                                continue;
                            }
                            $v['ip_zone'] = isset($v['ip_zone']) ? $v['ip_zone'] : '';
                        }
                    }
                }
                $v = $model->formattedData($v);
                $list[$k] = $v;
            }
        }

        ksort($params['showField']);
        return $this->render('index', [
            'model' => $model,
            'list' => $list,
            'pagination' => $pagination,
            'params' => $params,
            'resArr' => $resArr,
        ]);
    }

    public function ipv62long($ip){
    $ip_n = inet_pton($ip);
    $bits = 15; // 16 x 8 bit = 128bit (ipv6)
    while ($bits >= 0){
        $bin = sprintf("%08b",(ord($ip_n[$bits])));
        $ipbin = $bin.$ipbin;
        $bits--;
    }
    return $ipbin;
}
} 