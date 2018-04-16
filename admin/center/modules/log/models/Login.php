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

class Login extends ActiveRecord
{
    private $_searchFiled = null;

    public $defaultField = ['user_name', 'user_ip', 'nas_ip', 'user_mac', 'nas_port_id', 'err_msg', 'log_time'];

    private static $table = 'srun_login_log';

    /**
     * 重置表名
     * @param $params
     * @return null
     */
    public static function resetPartitionIndex($params) {
        $table_name = 'srun_login_log';
        if((!isset($params['start_log_time']) || empty($params['start_log_time'])) && (!isset($params['end_log_time']) || empty($params['end_log_time']))){
            return Yii::t('app', 'log detail help3');
        }
        $tableName = \common\extend\Tool::getPartitionTable($params['start_log_time'], $params['end_log_time'], $table_name);
        if($tableName){
            if($tableName !== $table_name){
                $is_exists = Yii::$app->db->createCommand('show tables like "'.$tableName.'"')->queryAll();
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

    public static function tableName()
    {
        return self::$table;
    }

    /**
     * getter
     * @return array|null
     */
    public function getSearchField()
    {
        if (!is_null($this->_searchFiled)) {
            return $this->_searchFiled;
        }
        $this->_searchFiled = [
            'user_name' => Yii::t('app', 'account'),
            'user_ip' => Yii::t('app', 'user ip'),
            'nas_ip' => Yii::t('app', 'nas ip'),
            'user_mac' => Yii::t('app', 'user mac'),
            'nas_port_id' => Yii::t('app', 'nas port id'),
            'err_msg' => Yii::t('app', 'err msg'),
            'log_time' => Yii::t('app', 'log time'),
        ];
        return $this->_searchFiled;
    }

    /**
     * setter
     * @param $data
     */
    public function setSearchField($data)
    {
        $this->_searchFiled = $data;
    }

    /**
     * @param array $list
     * @return array
     */
    public function msgReplace($list = [])
    {
        if (empty($list)) {
            return $list;
        }
        //引入错误消息文件
        $err_msgs = [];
        if(YII_ENV_DEV){
            require(Yii::getAlias('@common').'/config/params_8081.php');
        }else{
            $define_conf = Yii::$app->language != 'zh-CN' ? '/srun3/www/include/define_en.php' : '/srun3/www/include/define.php';
            require($define_conf);
        }
        foreach ($list as $k => $v) {
            //处理错误消息
            if (isset($v['err_msg']) && !empty($v['err_msg'])) {
                $msgCode = substr($v['err_msg'], 0, 5);
                if (array_key_exists($msgCode, $err_msgs)) {
                    $list[$k]['err_msg'] = $err_msgs[$msgCode];
                }else{
                    if(strpos($v['err_msg'], 'E2901') !== false){
                        $list[$k]['err_msg'] = Yii::t('app', 'E2901');
                    }
                }
            }
        }
        return $list;
    }

    /**
     * 格式化单个数据
     * @param $one
     * @return mixed
     */
    public function formattedData($one)
    {
        if (isset($one['log_time']) && $one['log_time'] > 0) {
            $one['log_time'] = date('Y-m-d H:i:s', $one['log_time']);
        }
        return $one;
    }

    public static function getAttributesList()
    {
        return [
            'error_message' => [
                '' => Yii::t('app', 'err msg'),
                'E2531' => Yii::t('app', 'E2531'),
                'E2532' => Yii::t('app', 'E2532'),
                'E2533' => Yii::t('app', 'E2533'),
                'E2553' => Yii::t('app', 'E2553'),
                'E2601' => Yii::t('app', 'E2601'),
                'E2606' => Yii::t('app', 'E2606'),
                'E2611' => Yii::t('app', 'E2611'),
                'E2613' => Yii::t('app', 'E2613'),
                'E2614' => Yii::t('app', 'E2614'),
                'E2616' => Yii::t('app', 'E2616'),
                'E2620' => Yii::t('app', 'E2620'),
                'E2621' => Yii::t('app', 'E2621'),
                'E2806' => Yii::t('app', 'E2806'),
                'E2807' => Yii::t('app', 'E2807'),
                'E2808' => Yii::t('app', 'E2808'),
                'E2833' => Yii::t('app', 'E2833'),
                'E2901' => Yii::t('app', 'E2901'),
            ],
        ];
    }

    /**
     * @param $list
     * @return mixed
     */
    public function formatExcelData($list,$showFields = null){
        $expireList = $this->msgReplace($list);
        $ArrayTitle = array();
        if($showFields){
            foreach ($showFields as $key) {
                $ArrayTitle[0][] = $this->searchField[$key];
            }
        }

        $dataArray = array();
        if($expireList){
            foreach ($expireList as $value){
                $arr = array();
                foreach($showFields as $k){
                    if($k == 'log_time'){
                        if(isset($value[$k]) && $value[$k] > 0){
                            $arr[] = date('Y-m-d H:i:s',$value[$k]);
                        }
                    }else{
                        $arr[] = $value[$k];
                    }
                }
                array_push($dataArray,$arr);
            }
        }
        $data = array_merge($ArrayTitle,$dataArray);
        return $data;
    }
}