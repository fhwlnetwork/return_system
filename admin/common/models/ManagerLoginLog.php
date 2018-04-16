<?php

namespace common\models;

use yii;
use center\modules\log\models\LogWriter;

/**
 * This is the model class for table "manager_login_log".
 *
 * @property string $id
 * @property integer $user_id
 * @property string $manager_name
 * @property string $ip
 * @property string $login_time
 * @property string $logout_time
 */
class ManagerLoginLog extends \yii\db\ActiveRecord
{
    public $end_time;
    public $start_time;

    //默认搜索显示的字段
    public $defaultField = [ 'id','manager_name', 'ip', 'login_time'];
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'manager_login_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id', 'user_id', 'login_time', 'logout_time'], 'integer'],
            [['manager_name', 'ip'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'manager_name' => 'Manager Name',
            'ip' => 'Ip',
            'login_time' => 'Login Time',
            'logout_time' => 'Logout Time',
        ];
    }
    /**
     * 要搜索的字段
     * @return array
     */
    public function getSearchInput()
    {
        //扩展字段加入搜索
        $exField = [];
        return yii\helpers\ArrayHelper::merge([
            'manager_name' => [
                'label' => Yii::t('app', 'operate type User Base')
            ],
            'start_time' => [
                'label' => Yii::t('app', 'start time')
            ],
            'end_time' => [
                'label' => Yii::t('app', 'end time')
            ],
        ], $exField);
    }
    //搜索字段
    private $_searchField = null;

    public function getSearchField()
    {
        if(!is_null($this->_searchField)){
            return $this->_searchField;
        }
        $this->_searchField = yii\helpers\ArrayHelper::merge([
            'id' => 'ID',
            'user_id' => Yii::t('app', 'user id'),
            'manager_name' => Yii::t('app', 'manager name'),
            'ip' => Yii::t('app', 'ip'),
            'login_time' => Yii::t('app', 'login time'),
            'logout_time' => Yii::t('app', 'logout time'),
        ], []);

        return $this->_searchField;
    }
    public function batchWriteLog($logContent){
        //写日志开始
        if ($logContent) {
            $logData = [
                'operator' => Yii::$app->user->identity->username,
                'action' => 'batch delete login',
                'content' => $logContent,
                'class' => __CLASS__,
                'type' => 1,
            ];
            LogWriter::write($logData);
        }
    }
}
