<?php
/**
 * Created by PhpStorm.
 * User: ligang
 * Date: 2015/1/14
 * Time: 16:55
 */

namespace center\modules\user\models;

use common\models\Redis;
use Yii;
use yii\db\ActiveRecord;

class OnlineRadiusDhcp extends ActiveRecord
{
    //默认要显示的字段
    public $defaultField = ['user_name', 'ip', 'user_mac', 'nas_ip', 'nas_port', 'vlan_id'];

    public static function tableName()
    {
        return 'online_radius_dhcp';
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
            'line_type' => Yii::t('app', 'line type'),
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
            'nas_ip' => [
                'label' => Yii::t('app', 'nas ip'),
            ],
            'nas_port' => [
                'label' => Yii::t('app', 'nas port'),
            ],
            'vlan_id' => [
                'label' => Yii::t('app', 'vlan id'),
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
        if($field){
            $value = Redis::executeCommand('HGET', 'hash:dhcp:'.$rad_online_id, [$field], 'redis_online');
            return $value;
        }else{
            $res = Redis::executeCommand('HGETALL', 'hash:dhcp:'.$rad_online_id, [], 'redis_online');
            return Redis::hashToArray($res);
        }

    }

}