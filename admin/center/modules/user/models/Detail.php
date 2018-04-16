<?php
/**
 * Created by PhpStorm.
 * User: ligang
 * Date: 2015/3/28
 * Time: 18:50
 */

namespace center\modules\user\models;

use Yii;
use yii\db\ActiveRecord;

class Detail extends ActiveRecord
{
    public $user_real_name = '';
    public static function tableName()
    {
        return 'users_open_log';
    }

    public function attributeLabels()
    {
        return [
            'user_name' => Yii::t('app', 'User Name'),
            'user_real_name' => Yii::t('app', 'realname'),
            'type' => Yii::t('app', 'action type'),
            'operate_time' => Yii::t('app', 'operate time'),
            'operate_ip' => Yii::t('app', 'operate ip'),
            'mgr_name' => Yii::t('app', 'operate operator'),
            'detail' => Yii::t('app', 'data detail'),
        ];
    }

    public function getAttributesList()
    {
        return [
            'type' => [
                '0' => Yii::t('app', 'user_detail_font1'),
                '1' => Yii::t('app', 'user_detail_font2')
            ],
        ];
    }

    public function beforeSave($insert)
    {
        $this->operate_time = time();
        $this->operate_ip = $this->operate_ip ? $this->operate_ip : Yii::$app->request->userIP;
        $this->mgr_name = $this->mgr_name ? $this->mgr_name : Yii::$app->user->identity->username;
        return true;
    }

    public function afterFind()
    {
        $this->operate_time = date('Y-m-d H:i:s', $this->operate_time);
        parent::afterFind();
    }

    public function formatExcelData($list){
        $arrayTitle = $this->attributeLabels();
        unset($arrayTitle['detail']);
        $title = array();
        $title[0] = array_values($arrayTitle);
        $result = array();
        if($list){
            foreach ($list as $one) {
                $arr = array();
                foreach ($arrayTitle as $key => $value){
                    if($key == 'operate_time'){
                        $arr[] = date('Y-m-d H:i:s',$one[$key]);
                    }elseif ($key == 'type'){
                        $arr[] = $this->attributesList['type'][$one[$key]];
                    }else{
                        $arr[] = $one[$key];
                    }
                }
                array_push($result,$arr);
            }
        }
        $data = array_merge($title,$result);
        return $data;
    }


}