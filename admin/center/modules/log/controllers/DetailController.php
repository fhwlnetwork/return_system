<?php
namespace center\modules\log\controllers;

use center\modules\strategy\models\Product;
use yii;
use common\models\User;
use common\models\Redis;
use yii\data\Pagination;
use common\extend\Excel;
use center\modules\log\models\Detail;
use center\modules\user\models\Base;
use center\modules\auth\models\SrunJiegou;
use center\controllers\ValidateController;
use common\extend\Export\CsvExport;
use common\extend\Export\DataType\ArrayType;
use common\extend\Export\DataType\SqlType;

class DetailController extends ValidateController
{
    const DETAIL_EXPORT_LIMIT = 20000;
    const CSV_EXPORT_LIMIT = 600000;

    /**
     * 上网明细列表控制器 ，此处如果上网明细和主库在同一台服务器，那么支持跨库权限判断，如果不在同一台服务器，无法做权限判断
     * @return string
     */
    public function actionIndex()
    {
        //请求的参数
        $params = Yii::$app->getRequest()->queryParams;
        //如果不输入任何条件，就查询当天的明细
        if (empty($params)) {
            $params["start_add_time"] = date("Y-m-d 00:00:00");
        }

        $model = new Detail();
        //查询表名
        $res = $model::resetPartitionIndex($params);
        if(!empty($res)){
            Yii::$app->getSession()->setFlash('danger', $res);
        }

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
        $where = '1=1';

        foreach ($params as $field => $value) {
            if ($value != '') {
                switch ($field) {
                    case 'start_add_time':
                        $query->andWhere(['>=', $tableName . '.add_time', strtotime($value)]);
                        $sta = strtotime($value);
                        $where .= " AND add_time >= $sta";
                        break;
                    case 'end_add_time':
                        $query->andWhere(['<=', $tableName . '.drop_time', strtotime($value)]);
                        $end = strtotime($value);
                        $where .= " AND add_time >= $end";
                        //$query->andWhere(['<', $tableName.'.add_time', strtotime('+1 days '.$value)]);
                        break;
                    default:
                        if (array_key_exists($field, $model->searchField)) {
                            if (isset($params['fuzzy_vlan']) && !empty($params['fuzzy_vlan']) && $field=='vlan_id') {
                                $query->andWhere("$field LIKE :$field", [":$field" => "%$value%"]);
                                $val = "%$value%";
                                $where .= " AND filed LIKE ''{$val}";
                            }else{
                                $query->andWhere(['=', $tableName . '.' . $field, $value]);
                                $where .= " AND $field = '{$value}'";
                            }
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
            $proKey = array_merge([0], $proKey);
            $query->andWhere(['products_id' => $proKey]);
        }

        //排序
        if (isset($params['orderBy']) && array_key_exists($params['orderBy'], $model->searchField)) {
            $query->orderBy([$tableName . '.' . $params['orderBy'] => $params['sort'] == 'desc' ? SORT_DESC : SORT_ASC]);
        } else {
            $query->orderBy(['detail_id' => SORT_DESC]);
        }

        //生成excel
        if (isset($params['export']) && !empty($params['export'])) {
            set_time_limit(0);
            $count = $query->count();
            $export = $params['export'];
            $limit = ($export == 'csv') ? self::CSV_EXPORT_LIMIT : self::DETAIL_EXPORT_LIMIT;
            if ($export == 'csv') {
                ini_set('memory_limit', '1024M'); //设置可以导出1GB
            }

            if ($count > $limit) {
                Yii::$app->session->setFlash('error', Yii::t('app', 'batch export help1', [
                    'num' => $limit,
                ]));
            } elseif ($count == 0) {
                Yii::$app->session->setFlash('error', Yii::t('app', 'batch export help2'));
            } else {
                $title = Yii::t('app', 'Detail Log');
                $file = $title . '.xls';
                if ($export == 'csv') {
                    $times = ['add_time', 'drop_time'];

                    $fields = [];
                    $fields['detail_id'] = ['name' => $model->getSearchField()['detail_id'], 'type' => 'string'];
                    $field = 'detail_id,';
                    $groups = SrunJiegou::getAllIdNameVal();
                    $products = (new Product())->getAllNameArr();
                    foreach ($params['showField'] as $k => $v) {
                        if ($model->hasAttribute($v)) {
                            if ($v == 'detail_id') {
                                continue;
                            }
                            $field .= "$v,";
                            $query->addSelect($v);
                            if (in_array($v, $times)) {
                                $fields[$v] = ['name' => $model->getSearchField()[$v], 'type' => 'datetime'];
                            } else if ($v == 'group_id') {
                                //用户组增加关联数组
                                $fields[$v] = ['name' => $model->getSearchField()[$v], 'type' => 'associatedArray',  'associatedArray'=>array(
                                    $v =>$groups,
                                ),];
                            } else if ($v == 'products_id') {
                                //产品增加关联数组
                                $fields[$v] = ['name' => $model->getSearchField()[$v], 'type' => 'associatedArray',  'associatedArray'=>array(
                                    $v =>$products,
                                ),];
                            } else{
                                $fields[$v] = ['name' => $model->getSearchField()[$v], 'type' => 'string'];
                            }
                        }
                    }
                    if (!$oneHost) {
                        //不是一台主机
                        $lists = $query
                            ->asArray()
                            ->all();
                        $data = array_slice($lists, 1);
                        $source = new ArrayType($data, $fields);
                    } else {
                        $field = rtrim($field, ',');
                        $sql = sprintf("SELECT %s FROM %s WHERE %s", $field, $tableName, $where);
                        $source = new SqlType($sql, $fields);
                    }
                    $csv = new CsvExport();
                    $csv->export($source, $title);
                    exit;
                } else {
                    $lists = $query
                        ->asArray()
                        ->all();
                    $listAllData = $model->findAllPageData($lists, $params); // 获取指定 id 数据.
                    $list = $model->formatExcelData($listAllData, $params['showField']); // 将数据进行过滤, 获取指定列的数据.
                    Excel::header_file($list, $file, $title);
                    exit;
                }
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
            $ipZone = $model->getIpZone();
        }

        if (in_array('vlan_zone', $params['showField'])) {
            $vlanAndIp = $model->getVlanZone();
            $vlans = $vlanAndIp['vlans'];
            $ips = $vlanAndIp['ips'];
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

        //处理ajax请求
        if (isset($params['showType']) && $params['showType'] == 'ajax') {
            $header = [];
            foreach ($params['showField'] as $value) {
                if ($value == 'user_name') //用户资料中不用显示用户名
                    continue;
                $header[0][$value] = $model->searchField[$value];
            }
            //内容 去掉用户名和最后一位detail_id
            $i = 0;
            $data = [];
            if ($list) {
                foreach ($list as $value1) {
                    $data[$i] = [];
                    foreach ($header as $k => $v) {
                        if (is_array($v)) {
                            foreach ($v as $key => $val) {
                                if ($key == 'billing_id') {
                                    $data[$i][$key] = (is_array($value1) && isset($model->billings[$value1[$key]])) ? $value1[$key].'.'.$model->billings[$value1['billing_id']] : '';
                                    continue;
                                }
                                $data[$i][$key] = (is_array($value1) && isset($value1[$key])) ? $value1[$key] : '';
                            }
                        }
                        $i++;
                     }

                    }
            }

            $newList = yii\helpers\ArrayHelper::merge($header, $data);
            foreach ($newList as $k => $v) {
                //$v = $model->formattedData($v);
                $newList[$k] = array_values($v);
            }
            return yii\helpers\Json::encode($newList);
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
}