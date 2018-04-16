<?php
/**
 * 写日志类
 * User: ligang
 * Date: 2015/1/23
 * Time: 17:48
 */
namespace center\modules\log\models;

use yii\db\ActiveRecord;
use yii;

class LogWriter extends ActiveRecord
{
    /**
     * 写日志
     * @param $data
     * @return bool
     */
    public static function write($data)
    {
        $operate = new Operate();
        //请求ip
        $ip = method_exists(Yii::$app->getRequest(), 'getUserIP') ? Yii::$app->getRequest()->getUserIP() : isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
        if(!isset($data['opt_ip'])) $data['opt_ip'] = $ip;
        if(!isset($data['opt_time'])) $data['opt_time'] = time();
        foreach ($data as $var => $value) {
            if ($operate->hasAttribute($var)) {
                $operate->$var = $value;
            }
        }
        if ($operate->save()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取脏数组数组
     * @param $oldData array 原始数据数组
     * @param $newData array 新数据数组
     * @return array 脏数组数据：新数据的格式['字段名1'=>'值', '字段名2'=>'值'], 修改数据的格式['字段名1'=>['原值', '新值'], '字段名2'=>['原值', '新值'], ]
     */
    public static function dirtyData($oldData, $newData)
    {
        if(empty($oldData)){
            //return $newData;
            $newList = [];
            if($newData){
                foreach ($newData as $k => $v) {
                    if(!is_null($v) && $v!=''){
                        $newList[$k] = $v;
                    }
                }
            }
            return $newList;
        }
        $dirtyArr = [];
        if($newData){
            foreach($newData as $key => $value) {
                $oldValue = isset($oldData[$key]) ? $oldData[$key] : '';
                if ($value != $oldValue) {
                    $dirtyArr[$key] = [$oldValue, $value];
                }
            }
        }

        return $dirtyArr;
    }
}