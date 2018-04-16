<?php
namespace center\modules\setting\models;

use common\models\Redis;
use yii;
use yii\db\ActiveRecord;
use yii\helpers\Json;
use center\modules\log\models\LogWriter;
use center\modules\auth\models\UserModel;

class Server extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'setting_server';
    }
	
    public function rules()
    {
        return [
            [['ip', 'devicename', 'type'], 'required'],
            ['ip', 'match', 'pattern' => '/^(((1?[0-9]?[0-9])|(2(([0-4][0-9])|(5[0-5]))))(.((1?[0-9]?[0-9])|(2(([0-4][0-9])|(5[0-5]))))){3})$/'],
			[['id', 'ip', 'devicename','type','admin','region','fault','configure'], 'safe']		
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'id'),
            'ip' => Yii::t('app', 'ip'),
            'devicename' => Yii::t('app', 'devicename'),
            'type' => Yii::t('app', 'type'),
            'admin' => Yii::t('app', 'admin'),
            'region' => Yii::t('app', 'region'),
            'fault' => Yii::t('app', 'fault'),
            'configure' => Yii::t('app', 'configure')
        ];
    }
	
    public function getAttributesList()
    {

        return [
            'id' => Yii::t('app', 'id'),
            'ip' => Yii::t('app', 'ip'),
            'devicename' => Yii::t('app', 'devicename'),
            'type' => Yii::t('app', 'type'),
            'admin' => Yii::t('app', 'admin'),
            'region' => Yii::t('app', 'region'),
            'fault' => Yii::t('app', 'fault'),
            'configure' => Yii::t('app', 'configure')
        ];
    }

	public function findAdminName ($id){
		$userMes = UserModel::findOne($id);
		if(!empty($userMes)){
			return $userMes->username;
		}
		return $id;	
	}
	
    public function getAttributesType(){
		$querydata = UserModel::find()->all();
		$userData = array();
		if(!empty($querydata)){
			foreach($querydata as $key=>$value){
				$userData[$value['id']] = $value['username'];
			}
		}
		return [
            'type' => [
                'Portal Server' => Yii::t('app','Portal Server'),
                'AAA' => Yii::t('app','AAA'),
                'Mysql' => Yii::t('app','Mysql'),
                'Redis' => Yii::t('app','Redis'),
                'Redis从' => Yii::t('app','Redis从'),
                'Radiusd' => Yii::t('app','Radiusd'),				
                'Interface' => Yii::t('app','Interface'),
            ],
			'admin'=>$userData,
            'devicename' => [
                'devicename_portal' => Yii::t('app','devicename_portal'),
                'devicename_billing' => Yii::t('app','devicename_billing'),
                'devicename_message' => Yii::t('app','devicename_message'),
                'devicename_data' => Yii::t('app','devicename_data'),
                'devicename_backups' => Yii::t('app','devicename_backups')
            ],			
			
        ];
    }

    public function log($oldData, $newData, $action)
    {
        $newArr = LogWriter::dirtyData($oldData, $newData);

        if ($newArr) {
            $logData = [
                'operator' => Yii::$app->user->identity->username,
                'target' => '',
                'action' => $action,
                'action_type' => 'Message ' . substr(get_class($this), strripos(__CLASS__, '\\') + 1),
                'content' => Json::encode($newArr),
                'class' => __CLASS__,
            ];
            LogWriter::write($logData);
        }
    }
	
    public static function getOne($id)
    {
        //从数据库中查询记录
        $model = parent::findOne($id);
        //将当前记录保存在临时旧数据
        return $model;
    }	
	
    public function deleteOne($id)
    {
		$model = parent::findOne($id);
		$model->delete();
        return true;
    }


}