<?php
/**
 * Created by PhpStorm.
 * User: ligang
 * Date: 2015/1/14
 * Time: 16:55
 */

namespace center\modules\user\models;

use common\models\Redis;
use yii;
use yii\db\ActiveRecord;

class OnlinePortal extends ActiveRecord
{

    //默认要显示的字段
    public $defaultField = ['user_name', 'user_ip', 'user_mac', 'bytes_in', 'bytes_out', 'add_time'];

    public static function tableName()
    {
        return 'online_portal';
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
            'user_password' => Yii::t('app', 'password'),
            //'domain' => Yii::t('app', 'domain'),
            'user_ip' => Yii::t('app', 'user ip'),
            'user_ip6' => Yii::t('app', 'user ip6'),
            'user_mac' => Yii::t('app', 'user mac'),
            'nas_ip' => Yii::t('app', 'nas ip'),
            'ac_id' => Yii::t('app', 'ac id'),
            'ac_type' => Yii::t('app', 'ac type'),
            'add_time' => Yii::t('app', 'add time'),
            'update_time' => Yii::t('app', 'update time'),
            'bandwidth_up' => Yii::t('app', 'bandwidth up'),
            'bandwidth_down' => Yii::t('app', 'bandwidth down'),
            'vrf_id' => Yii::t('app', 'vrf id'),
            'bytes_in' => Yii::t('app', 'bytes in'),
            'bytes_out' => Yii::t('app', 'bytes out'),
            'bytes_in6' => Yii::t('app', 'bytes in6'),
            'bytes_out6' => Yii::t('app', 'bytes out6'),
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
            'user_ip' => [
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
            $value = Redis::executeCommand('HGET', 'hash:auth:'.$rad_online_id, [$field], 'redis_auth');
            return $value;
        }else{
            $res = Redis::executeCommand('HGETALL', 'hash:auth:'.$rad_online_id, [], 'redis_auth');
            return Redis::hashToArray($res);
        }

    }

}