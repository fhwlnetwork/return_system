<?php
namespace center\modules\log\models;

use yii;
use center\extend\Tool;
use center\modules\strategy\models\Billing;
use center\modules\strategy\models\Control;
use center\modules\strategy\models\Product;
use center\modules\strategy\models\IpArea;


class Detail extends yii\db\ActiveRecord
{
    private $_searchFiled = null;

    public $defaultField = ['user_name', 'add_time', 'drop_time', 'user_ip', 'total_bytes', 'time_long', 'user_charge'];

    private static $table = 'srun_detail';

    /**
     * 重置明细表名
     * @param $params
     * @return null
     */
    public static function resetPartitionIndex($params) {
        if((!isset($params['start_add_time']) || empty($params['start_add_time'])) && (!isset($params['end_add_time']) || empty($params['end_add_time']))){
            return Yii::t('app', 'log detail help3');
        }
        $tableName = \common\extend\Tool::getPartitionTable($params['start_add_time'], $params['end_add_time']);
        if($tableName){
            if($tableName !== 'srun_detail'){
                $is_exists = Detail::getDb()->createCommand('show tables like "'.$tableName.'"')->queryAll();
                if(empty($is_exists)) {
                    //表不存在
                    return Yii::t('app', 'log detail help4', ['table_name' => $tableName]);
                }
            }
            self::$table = $tableName;
        }else{
            //上线时间和下线时间必须同时在一个自然月内才可搜索
            return Yii::t('app', 'log detail help3');;
        }
    }

    public static function getDb()
    {
        return Yii::$app->db_detail;
    }


    public static function tableName()
    {
        return self::$table;
    }

    public function getSearchField() //这里添加新的字段
    {
        if (!is_null($this->_searchFiled)) {
            return $this->_searchFiled;
        }
        $this->_searchFiled = [
            'detail_id' => Yii::t('app', '明细ID'),
            'user_name' => Yii::t('app', 'account'),
            'add_time' => Yii::t('app', 'add time'),
            'drop_time' => Yii::t('app', 'drop time'),
            'group_id' => Yii::t('app', 'group id'),
            'bytes_in' => Yii::t('app', 'bytes in'),
            'bytes_out' => Yii::t('app', 'bytes out'),
            'bytes_in6' => Yii::t('app', 'bytes in6'),
            'bytes_out6' => Yii::t('app', 'bytes out6'),
            'user_mac' => Yii::t('app', 'user mac'),
            'user_ip' => Yii::t('app', 'user ip'),
            'user_ip6' => Yii::t('app', 'user ip6'),
            'nas_ip' => Yii::t('app', 'nas ip'),
            'nas_port_id' => Yii::t('app', 'nas port id'),
            'vlan_id' => Yii::t('app', 'vlan id'),
            'vlan_zone' => Yii::t('app', 'vlan_zone'),
            'ip_zone' => Yii::t('app', 'ip zone'),
            'line_type' => Yii::t('app', 'line type'),
            'login_mode' => Yii::t('app', 'login mode'),
            'nas_type' => Yii::t('app', 'nas type'),
            'ip_type' => Yii::t('app', 'in type'),
            'user_id' => Yii::t('app', 'user id'),
            'products_id' => Yii::t('app', 'products id'),
            'billing_id' => Yii::t('app', 'billing id'),
            'control_id' => Yii::t('app', 'control id'),
            'traffic_down_ratio' => Yii::t('app', 'downlink flow ratio(%)'),
            'traffic_up_ratio' => Yii::t('app', 'uplink flow ratio(%)'),
            'billing_rate' => Yii::t('app', 'billing rate'),
            'billing_units' => Yii::t('app', 'billing units'),
            'billing_mode' => Yii::t('app', 'billing mode'),
            'user_balance' => Yii::t('app', 'user balance'),
            'total_bytes' => Yii::t('app', 'total bytes'),
            'time_long' => Yii::t('app', 'time long'),
            'user_charge' => Yii::t('app', 'user charge'),
            'drop_reason' => Yii::t('app', 'drop reason'),
			'class_name' => Yii::t('app', 'class name'),
			'os_name' => Yii::t('app', 'os name'),
			'pc_num' => Yii::t('app', 'pc num'),
			'mobile_num' => Yii::t('app', 'mobile num'),
			'sensitivity' => Yii::t('app', 'sensitivity'),
			'proxy_desc' => Yii::t('app', 'proxy desc'),
        ];
        return $this->_searchFiled;
    }

    public function setSearchField($data)
    {
        $this->_searchFiled = $data;
    }

    /**
     * 格式化数据
     * @param $one
     * @return mixed
     */
    public function formattedData($one)
    {
        //流量字段
        $bytesArr = ['total_bytes', 'bytes_in', 'bytes_out', 'bytes_in6', 'bytes_out6'];
        if (isset($one['add_time']) && $one['add_time'] > 0) {
            $one['add_time'] = date('Y-m-d H:i:s', $one['add_time']);
        }
        if (isset($one['drop_time']) && $one['drop_time'] > 0) {
            $one['drop_time'] = date('Y-m-d H:i:s', $one['drop_time']);
        }

        //处理流量字段
        foreach ($bytesArr as $field) {
            if (isset($one[$field]) && !empty($one[$field])) {
                $one[$field] = Tool::bytes_format($one[$field]);
            }
        }
        //处理时长
        if (isset($one['time_long']) && $one['time_long'] > 0) {
            $one['time_long'] = Tool::seconds_format($one['time_long']);
        }
        //处理下线原因drop_reasons
        if (isset($one['drop_reason']) && $one['drop_reason'] != '') {
            //开发模式下读取本地文件
            if (YII_ENV_DEV) {
                require(Yii::getAlias('@common') . '/config/params_8081.php');
            } else {
                require(Yii::$app->params['define8081'][\Yii::$app->language]);
            }
            $one['drop_reason'] = isset($drop_reasons[$one['drop_reason']]) ? $drop_reasons[$one['drop_reason']] : $one['drop_reason'];
        }
        return $one;
    }

    /**
     * 获取产品列表
     * @return array
     */
    public function getProducts()
    {
        $productModel = new Product();
        $product = $productModel->getNameOfList();
        return $product;
    }

    /**
     * 获取计费策略列表
     * @return array
     */
    public function getBillings()
    {
        $billingtModel = new Billing();
        $billing = $billingtModel->getAllBillNameList();
        return $billing;
    }

    /**
     * 获取控制策略列表
     * @return array
     */
    public function getControls()
    {
        $controltModel = new Control();
        $control = $controltModel->getNameOfList();
        return $control;
    }

    public function findAllPageData($data, $params)
    {
        if (!empty($data)) {
            // = array_column($data, 'detail_id');
            $column_key = 'detail_id';
            $index_key = null;
            if (function_exists('array_column ')) {
                $value = array_column($data, $column_key);
            }
            $result = [];
            foreach ($data as $arr) {
                if (!is_array($arr)) continue;

                if (is_null($column_key)) {
                    $value = $arr;
                } else {
                    $value = $arr[$column_key];
                }

                if (!is_null($index_key)) {
                    $key = $arr[$index_key];
                    $result[$key] = $value;
                } else {
                    $result[] = $value;
                }
            }

            $value = $result;
            //对数据进行排序
            if(empty($params['orderBy']) || empty($params['sort'])) {
                $resArr = self::find()->where(['in', 'detail_id', $value])->orderBy(['detail_id' => SORT_DESC])->asArray()->all();
            } else {
                if($params['sort'] === 'asc') {
                    $resArr = self::find()->where(['in', 'detail_id', $value])->orderBy([$params['orderBy'] => SORT_ASC])->asArray()->all();
                } else {
                    $resArr = self::find()->where(['in', 'detail_id', $value])->orderBy([$params['orderBy'] => SORT_DESC])->asArray()->all();
                }

            }
        } else {
            $resArr = [];
        }

        return $resArr;
    }

    public function searchData($params, $query)
    {
        //统计流量总数 消费总额等数据
        if (!empty($params['start_add_time']) && !empty($params['end_add_time']) && (!empty($params['user_ip']) || !empty($params['user_mac']) || !empty($params['user_name']))) {
            $searchField = ['total_bytes', 'bytes_in', 'bytes_out', 'time_long', 'user_charge']; // 需要统计的列值
            $data = $query->asArray()->all();
            $data = $this->findAllPageData($data, $params);
            $resArr = \common\extend\Tool::getCount($searchField, $data);
        } else {
            $resArr = [];
        }

        return $resArr;
    }

    /**
     * 将查询出来的所有数据进行过滤，只显示查询的字段.
     * @param $data array 数据.
     * @param $params array 要显示的列.
     * @return array|bool 过滤之后的 二维数组.
     */
    public function buildSearchField($data, $params)
    {
        if (empty($data) || empty($params) || !is_array($params)) {
            return false;
        }

        $result = [];
        foreach ($data as $key => $one) {

            foreach ($params as $field) {
                if(array_key_exists($field, $one)) {
                    $result[$key][$field] = $one[$field];
                } else {
                    null;
                }
            }
        }

        return $result;
    }

    /**
     * @param $data
     * @param $params
     * @return bool
     */
    public function formatExcelData($data,$params){
        if (empty($data) || empty($params) || !is_array($params)) {
            return false;
        }
        $arrayTitle = array();
        if($data){
            foreach ($params as $field){
                if(array_key_exists($field,$data[0])){
                    $arrayTitle[0][] = $this->searchField[$field];
                }
            }
        }
        $result = [];
        foreach ($data as $key => $one) {

            foreach ($params as $field) {
                if(array_key_exists($field, $one)) {
                    $result[$key][$field] = $one[$field];
                } else {
                    null;
                }
            }
            $result[$key] = $this->formattedData($result[$key]);
            $result[$key] = array_values($result[$key]);
        }
        $data = array_merge($arrayTitle,$result);
        return $data;
    }

    /**
     * 流量系数（为了特殊场合给流量加倍显示的需求)
     * @return array
     */
    public function getFlowRatioByMgr(){
        $data = [];
        $ratio = Yii::$app->user->identity->flow_ratio;
        if($ratio){
            $ratio = explode("\n", trim($ratio, "\n"));
            foreach($ratio as $one){
                $data[explode('|', $one)[0]] = [
                    'group_ids' => explode(',', explode('|', $one)[1]),
                    'ratio' => floatval(trim(explode('|', $one)[2])),
                ];
            }
        }
        return $data;
    }

    /**
     * 获取ip区域
     * @return array
     */
    public function getIpZone()
    {
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

        return $ipZone;
    }

    /**
     * 获取vlan区域
     * @return array
     */

    public function getVlanZone()
    {
        $vlan_id = $this->findBySql("SELECT userarea_id,vlan_id,sub_ip FROM rad_user_area")->asArray()->all();
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

        return [
            'vlans' => $vlans,
            'ips' => $ips,
        ];
    }
}