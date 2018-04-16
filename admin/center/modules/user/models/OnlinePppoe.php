<?php

namespace center\modules\user\models;

use Yii;

/**
 * This is the model class for table "online_pppoe".
 *
 * @property integer $rad_online_id
 * @property integer $add_time
 * @property string $session_id
 * @property string $user_name
 * @property string $service_name
 * @property integer $uid
 * @property string $ip
 * @property string $ip6
 * @property string $user_mac
 */
class OnlinePppoe extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'online_pppoe';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['add_time', 'session_id', 'user_name', 'service_name', 'uid', 'ip', 'ip6', 'user_mac'], 'required'],
            [['add_time', 'uid'], 'integer'],
            [['session_id', 'user_name', 'service_name', 'ip', 'ip6', 'user_mac'], 'string', 'max' => 64],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'rad_online_id' => 'Rad Online ID',
            'add_time' => 'Add Time',
            'session_id' => 'Session ID',
            'user_name' => 'User Name',
            'service_name' => 'Service Name',
            'uid' => 'Uid',
            'ip' => 'Ip',
            'ip6' => 'Ip6',
            'user_mac' => 'User Mac',
        ];
    }

    /**
     * 根据pppoe表 获取服务商列表
     * @return array
     */
    public function getServices(){
        $data = [];
        $services = self::find()->select('service_name')->groupBy('service_name')->asArray()->all();
        if($services){
            foreach($services as $one){
                if($one['service_name']){
                    $data[] = $one['service_name'];
                }
            }
        }
        return $data;
    }

    /**
     * 获取服务号的当前在线数
     * @param $service_name
     * @return bool|int|string
     */
    public function getOnlineNum($service_name){
        if(empty($service_name)){
            return false;
        }
        return self::find()->where(['service_name'=>$service_name])->count();
    }
}
