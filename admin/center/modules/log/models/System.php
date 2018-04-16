<?php
/**
 * Created by PhpStorm.
 * User: ligang
 * Date: 2015/1/13
 * Time: 11:07
 */

namespace center\modules\log\models;

use yii;
use yii\db\ActiveRecord;

class System extends ActiveRecord
{
    private $_searchFiled = null;

    public $defaultField = ['proc', 'user_name', 'user_ip', 'nas_ip', 'my_ip', 'user_mac', 'nas_port_id', 'err_msg', 'log_time'];

    public static function tableName()
    {
        return 'srun_system_log';
    }

    /**
     * getter
     * @return array|null
     */
    public function getSearchField()
    {
        if(!is_null($this->_searchFiled)){
            return $this->_searchFiled;
        }
        $this->_searchFiled = [
            'proc' => Yii::t('app', 'proc'),
            'user_name' => Yii::t('app', 'account'),
            'user_ip' => Yii::t('app', 'user ip'),
            'nas_ip' => Yii::t('app', 'nas ip'),
            'my_ip' => Yii::t('app', 'my ip'),
            'user_mac' => Yii::t('app', 'user mac'),
            'nas_port_id' => Yii::t('app', 'nas port id'),
            'err_msg' => Yii::t('app', 'prompt msg'),
            'log_time' => Yii::t('app', 'log time'),
        ];
        return $this->_searchFiled;
    }

    /**
     * setter
     * @param $data
     */
    public function setSearchField($data){
        $this->_searchFiled = $data;
    }

    public function attributeLabels()
    {

    }

    /**
     * @param array $list
     * @return array
     */
    public function msgReplace($list = [])
    {
        if(empty($list)){
            return $list;
        }
        //引入错误消息文件
        $err_msgs = [];
        if(YII_ENV_DEV){
            require(Yii::getAlias('@common').'/config/params_8081.php');
        }else{
            require(Yii::$app->params['define8081'][\Yii::$app->language]);
        }
        foreach($list as $k => $v){
            //处理错误消息
            if(isset($v['err_msg']) && !empty($v['err_msg'])){
                $msgCode = substr($v['err_msg'], 0, 5);
                if(array_key_exists($msgCode, $err_msgs)){
                    $list[$k]['err_msg'] = $err_msgs[$msgCode];
                }
            }
        }
        return $list;
    }

    public static function getAttributesList(){
        if (YII_ENV_DEV) {
            require(Yii::getAlias('@common') . '/config/params_8081.php');
        } else {
            require(Yii::$app->params['define8081'][\Yii::$app->language]);
        }
        return [
            'system_error_message' => array_merge(['' => Yii::t('app', 'prompt msg')],$err_msgs),
        ];
    }
}