<?php
/**
 * Created by PhpStorm.
 * User: ligang
 * Date: 2015/1/14
 * Time: 12:02
 */

namespace center\modules\user\models;

use yii;
use center\modules\log\models\LogWriter;
use common\models\KernelInterface;
use common\models\Redis;
use yii\base\Model;


class Online extends Model
{
    public static $model;

    /**
     * 在线类定义
     * @return array
     */
    public static function corePanels()
    {
        return [
            'radius' => ['class' => 'center\modules\user\models\OnlineRadius'],
            'proxy' => ['class' => 'center\modules\user\models\OnlineRadiusProxy'],
            'dhcp' => ['class' => 'center\modules\user\models\OnlineRadiusDhcp'],
            'portal' => ['class' => 'center\modules\user\models\OnlinePortal'],
        ];
    }

    /**
     * 在线类型的描述
     * @param $type string 在线类型
     * @return mixed 描述
     */
    public static function getOnlineType($type)
    {
        $types = [
            'radius' => Yii::t('app', 'online type1'),
            'proxy' => Yii::t('app', 'online type2'),
            'dhcp' => Yii::t('app', 'online type3'),
            'portal' => Yii::t('app', 'online type4'),
        ];
        return isset($types[$type]) ? $types[$type] : $types['radius'];
    }

    /**
     * 获取用户的在线数据
     * @param $user_name
     * @return array
     */
    public static function getOnlineByName($user_name)
    {
        //获取radius在线
        $radiusOnline = OnlineRadius::find()->where(['user_name'=>$user_name])->asArray()->all();
        //获取代理在线
        $proxyOnline = OnlineRadiusProxy::find()->where(['user_name'=>$user_name])->asArray()->all();

        return $radiusOnline+$proxyOnline;
    }
    
    /**
     * 从REDIS中直接获取用户的在线数据
     * @param $user_name
     * @return array
     */
    public static function getOnlineByNameRedis($user_name)
    {
    	$radiusOnline = [];
    	$proxyOnline = [];
    	
    	$array = Redis::executeCommand('SMEMBERS', 
									 'set:rad_online:user_name:'.$user_name, 
									 [], 
									 'redis_online');
    	if($array)
    	{
    		foreach($array as $rad_online_id)
    		{
    			$hash = Redis::executeCommand('HGETALL', 
									 'hash:rad_online:'.$rad_online_id, 
									 [], 
									 'redis_online');
    			$radiusOnline[] = Redis::hashToArray($hash);
    		}
    	}
    	
    	$array = Redis::executeCommand('SMEMBERS',
						    			'set:proxy:user_name:'.$user_name,
						    			[],
						    			'redis_online');
    	if($array)
    	{
    		foreach($array as $rad_online_id)
    		{
    			$hash = Redis::executeCommand('HGETALL',
    					'hash:proxy:'.$rad_online_id,
    					[],
    					'redis_online');
    			$proxyOnline[] = Redis::hashToArray($hash);
    		}
    	}
    
    	return $radiusOnline+$proxyOnline;
    }

    /**
     * 根据在线类型和id获取数据对象
     * @param $type string 在线类型
     * @param $id integer id
     */
    public static function getModel($type, $id)
    {
        $corePanels = self::corePanels();
        //类名称
        $modelClass = $corePanels[$type]['class'];
        //表名称
        $modelTable = $modelClass::tableName();

        $query = $modelClass::find();
        $query->addSelect($modelTable.'.user_name');
        if($type == 'portal'){
            $query->addSelect($modelTable.'.user_ip')
                ->andWhere([$modelTable.'.auth_id' => $id]);
        }else{
            $query->addSelect([$modelTable.'.rad_online_id', $modelTable.'.ip'])
                ->andWhere([$modelTable.'.rad_online_id' => $id]);
        }

        self::$model = $query->one();
    }

    /**
     * 其他模块调用下线
     * @param $type string 类型
     * @param $id integer 下线的id
     * @return bool|string
     */
    public static function dropOnlineById($type, $id)
    {
        if(!array_key_exists($type, self::corePanels())){
            return false;
        }

        self::getModel($type, $id);

        if(!self::$model){
            return false;
        }

        return self::dropOnline($type);
    }

    /**
     * 用户下线
     * @param $type
     * @return string
     */
    public static function dropOnline($type)
    {
        if($type == 'portal'){
            $data = ['user_ip' => Online::$model->user_ip];
        }else{
            $data = ['online_id' => Online::$model->rad_online_id];
        }
        $res = KernelInterface::userDrop($type, $data);
        if($res){
            //写日志
            $content = Yii::t('app', 'user online help4', [
                'mgr' => Yii::$app->user->identity->username,
                'online_type' => self::getOnlineType($type),
                'target' => self::$model->user_name,
                'ip' => $type=='portal' ? self::$model->user_ip : self::$model->ip,
            ]);
            LogWriter::write([
                'operator' => Yii::$app->user->identity->username,
                'target' => self::$model->user_name,
                'action' => 'drop',
                'action_type' => 'User Online',
                'content' => $content,
                'class' => __CLASS__,
                'type' => 1,
            ]);
        }
        return $res;
    }


}