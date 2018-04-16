<?php
/**
 * Created by PhpStorm.
 * User: ligang
 * Date: 2015/1/14
 * Time: 16:55
 */

namespace center\modules\user\models;

use common\models\KernelInterface;
use common\models\Redis;
use yii;
use yii\db\ActiveRecord;

class OnlineRadius extends ActiveRecord
{
    //默认要显示的字段
    public $defaultField = ['user_name', 'ip', 'user_mac', 'bytes_in', 'bytes_out', 'add_time'];

    public static function tableName()
    {
        return 'online_radius';
    }

    //拖拽显示并排序的字段
    private $_searchFiled = null;

    public function getSearchField()
    {
        if(!is_null($this->_searchFiled)){
            return $this->_searchFiled;
        }
        $this->_searchFiled = [
            'session_id' => Yii::t('app', 'session id'),
            'user_name' => Yii::t('app', 'account'),
            'domain' => Yii::t('app', 'domain'),
            'uid' => Yii::t('app', 'user id'),
            'ip' => Yii::t('app', 'user ip'),
            'ip6' => Yii::t('app', 'user ip6'),
            'user_mac' => Yii::t('app', 'user mac'),
            'nas_ip' => Yii::t('app', 'nas ip'),
            'nas_ip1' => Yii::t('app', 'nas ip1'),
            'nas_port' => Yii::t('app', 'nas port'),
            'nas_port_id' => Yii::t('app', 'nas port id'),
            'station_id' => Yii::t('app', 'station id'),
            'filter_id' => Yii::t('app', 'filter id'),
            'pbhk' => Yii::t('app', 'pbhk'),
            'vlan_id1' => Yii::t('app', 'vlan id1'),
            'vlan_id2' => Yii::t('app', 'vlan id2'),
            'vlan_id' => Yii::t('app', 'vlan id'),
            'vlan_zone' => Yii::t('app', 'vlan_zone'),
            'ip_zone' => Yii::t('app', 'ip zone'),
            'line_type' => Yii::t('app', 'line type'),
            'login_mode' => Yii::t('app', 'login mode'),
            'nas_type' => Yii::t('app', 'nas type'),
            'products_id' => Yii::t('app', 'products id'),
            'control_id' => Yii::t('app', 'control id'),
            'billing_id' => Yii::t('app', 'billing id'),
            //'condition_mode' => Yii::t('app', 'condition mode'),
            'condition' => Yii::t('app', 'billing condition'),
            'control_condition' => Yii::t('app', 'control condition'),
            'bytes_in' => Yii::t('app', 'bytes in'),
            'bytes_out' => Yii::t('app', 'bytes out'),
            //'bytes_in1' => Yii::t('app', 'bytes in1'),
            //'bytes_out1' => Yii::t('app', 'bytes out1'),
            'bytes_in6' => Yii::t('app', 'bytes in6'),
            'bytes_out6' => Yii::t('app', 'bytes out6'),
            'add_time' => Yii::t('app', 'add time'),
            //'drop_time' => Yii::t('app', 'drop time'),
            'update_time' => Yii::t('app', 'update time'),
            'session_time' => Yii::t('app', 'session time'),
            'keepalive_time' => Yii::t('app', 'keepalive time'),
            //'drop_cause' => Yii::t('app', 'drop cause'),
            'my_ip' => Yii::t('app', 'my ip'),
            'sum_bytes' => Yii::t('app', 'sum bytes'),
            'sum_seconds' => Yii::t('app', 'sum seconds'),
            'sum_times' => Yii::t('app', 'sum times'),
            'remain_bytes' => Yii::t('app', 'remain bytes'),
            'remain_seconds' => Yii::t('app', 'remain seconds'),
            'traffic_down_ratio' => Yii::t('app', 'downlink flow ratio(%)'),
            'traffic_up_ratio' => Yii::t('app', 'uplink flow ratio(%)'),
            'billing_rate' => Yii::t('app', 'billing rate'),
            'billing_units' => Yii::t('app', 'billing units'),
            'billing_mode' => Yii::t('app', 'billing mode'),
            'billing_top' => Yii::t('app', 'billing top'),
            'user_balance' => Yii::t('app', 'user balance'),
            'user_charge' => Yii::t('app', 'user charge'),
            'bandwidth_up' => Yii::t('app', 'bandwidth up'),
            'bandwidth_down' => Yii::t('app', 'bandwidth down'),
            'os_name' => Yii::t('app', 'os name'),
            'class_name' => Yii::t('app', 'class name'),
            'client_type' => Yii::t('app', 'client type'),
            'allow_arrears' => Yii::t('app', 'allow arrears'),
            'change_mode_control' => Yii::t('app', 'change mode control'),
            'change_mode_billing' => Yii::t('app', 'change mode billing'),
            'group_id' => Yii::t('app', 'User Groups'),

        ];
        return $this->_searchFiled;
    }

    public function setSearchField($data){
        $this->_searchFiled = $data;
    }

    //要搜索的字段
    public function getSearchInput()
    {
        return [
            'user_name' => [
                'label' => Yii::t('app', 'account')
            ],
            'ip' => [
                'label' => Yii::t('app', 'user ip')
            ],
            'user_mac' => [
                'label' => Yii::t('app', 'user mac')
            ],
            'start_add_time' => [
                'label' => Yii::t('app', 'start add time'),
                'type' => 'dateTime',
            ],
            'end_add_time' => [
                'label' => Yii::t('app', 'end add time'),
                'type' => 'dateTime',
            ],
            'group_id' => [
                    'label' => Yii::t('app', 'group id'),
            ],
            'products_id' => [
                    'label' => Yii::t('app', 'user products id'),
            ],
            'vlan_id1' => [
                'label' => Yii::t('app', 'vlan id1'),
            ],
            'vlan_id' => [
                'label' => Yii::t('app', 'vlan id'),
            ],
            'nas_ip' => [
                'label' => Yii::t('app', 'attr variable8'),
            ],
        ];
    }

    /**
     * 根据rad_online_id和字段名获取此字段的值
     * @param $rad_online_id
     * @param $field
     * @return mixed
     */
    public function getValueInRedis($rad_online_id, $field = null)
    {
        //获取单个字段
        if($field){
            $value = Redis::executeCommand('HGET', 'hash:rad_online:'.$rad_online_id, [$field], 'redis_online');
            //v4入流量
            if($field == 'bytes_in'){
                $value -= Redis::executeCommand('HGET', 'hash:rad_online:'.$rad_online_id, ['bytes_in1'], 'redis_online');
            }
            //v4出流量
            else if($field == 'bytes_out'){
                $value -= Redis::executeCommand('HGET', 'hash:rad_online:'.$rad_online_id, ['bytes_out1'], 'redis_online');
            }
            return $value;
        }
        //获取所有字段
        else{
            $res = Redis::executeCommand('HGETALL', 'hash:rad_online:'.$rad_online_id, [], 'redis_online');
            return Redis::hashToArray($res);
        }

    }

    /**
     * 获取redis中的名称
     * @param $type string  类型，products_id：产品策略，billing_id：计费策略，control_id：控制策略
     * @param $value int 查找的值id
     * @return mixed 返回对应的名称
     */
    public function getNameInRedis($type, $value)
    {
        $name = '';
        //产品策略
        if($type == 'products_id'){
            $name = Redis::executeCommand('HGET', 'hash:products:'.$value, ['products_name']);
        }
        //计费策略
        else if($type == 'billing_id'){
            $name = Redis::executeCommand('HGET', 'hash:billing:'.$value, ['billing_name']);
        }
        //控制策略
        else if($type == 'control_id'){
            $name = Redis::executeCommand('HGET', 'hash:control:'.$value, ['control_name']);
        }
        return $name;
    }

    /**
     * 根据用户名 dm下线
     * @param $user_name
     * @return bool|string
     */
    public function radiusDrop($user_name){
        if(!empty($user_name)){
            $data = self::findAll(['user_name'=>$user_name]);
            if($data){
                foreach($data as $v){
                    if(isset($v['rad_online_id']) && !empty($v['rad_online_id'])){
                        $array = ['online_id' => $v['rad_online_id']];
                        KernelInterface::userDrop('radius',$array);
                    }
                }
            }
        }
        return false;
    }

}