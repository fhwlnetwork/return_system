<?php

namespace center\modules\auth\models;

use Yii;
use yii\data\ActiveDataProvider;
use common\models\User;
use yii\helpers\Json;
use center\modules\log\models\LogWriter;
use center\modules\auth\models\SrunJiegou;
use center\modules\strategy\models\Product;

/**
 * This is the model class for table "{{%user}}".
 *
 * @property integer $id
 * @property string $username
 * @property string $auth_key
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property integer $role
 * @property integer $status
 * @property string $mgr_org
 * @property string $mgr_product
 * @property string $mgr_admin
 * @property string $tid
 * @property string $path
 * @property integer $pid
 * @property integer $mgr_admin_type
 * @property integer $created_at
 * @property integer $updated_at
 */
class UserModel extends \yii\db\ActiveRecord
{
    public $roles;
    public $password;
    public $passwords;
    private $_old = [];
    public $old_password;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%manager}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'roles', 'mgr_admin_type'], 'required', 'on' => ['create', 'update']],
            [['username'], 'filter', 'filter' => 'trim'],
            ['username', 'unique', 'targetClass' => '\common\models\User', 'message' => Yii::t('app', 'auth_user_model_font1'), 'on' => 'create'],
            ['username', 'string', 'min' => 2, 'max' => 20],
            ['email', 'email'],
            ['email', 'checkEmail',  'message' => '邮箱已经被注册.'],
            ['mobile_phone', 'match', 'pattern' => '/^1[0-9]{10}$/', 'message'=>'{attribute}必须为1开头的11位纯数字'],
            ['password', 'string', 'min' => 6],
            ['password', 'checkOldPass'],
            ['passwords', 'compare', 'compareAttribute' => 'password'],
            [['role', 'status', 'pid', 'mgr_admin_type', 'created_at', 'updated_at', 'max_open_num'], 'integer'],
            [['username', 'roles', 'password_hash', 'password_reset_token', 'email', 'path', 'mgr_org'], 'string', 'max' => 255],
            [['auth_key'], 'string', 'max' => 32],
            [['tid', 'begin_time', 'stop_time', 'major_id', 'nation', 'sex', 'id_number', 'person_name'], 'string', 'max' => 30],
            ['ip_area', 'match', 'pattern' => '/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}((\/|-)\d{1,3})?(\,\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}((\/|-)\d{1,3})?)*$/', 'message' => '{attribute}不是正确格式的ip区域'],
            ['max_open_num', 'checkMaxOpenNum'],
            ['expire_time', 'default', 'value' => 0],
            ['old_password', 'checkIsRight', 'on' => ['update']]
        ];
    }

    public function checkOldPass($attributes, $params){
        $params = Yii::$app->request->post('UserModel');
        if($params['old_password'] == ''){
            $this->addError($attributes, Yii::t('app', 'Old password empty'));
        }
    }

    public function  checkIsRight($attributes, $params)
    {
        $passwordHash = Yii::$app->security->generatePasswordHash($this->$attributes);
        $user = User::findByUsername($this->username);
        if (!empty($this->$attributes)) {
            if (!$user || !$user->validatePassword($this->$attributes)) {
                $this->addError($attributes, Yii::t('app', 'Old password error'));
            }
        }
    }

    public function checkEmail($attributes, $params)
    {
        if ($this->isNewRecord) {
            $model = User::findOne(['email'=>$this->email]);
        } else {
           $model = User::find()->where("email = '{$this->email}' and id != '{$this->id}'")->one();
        }

        if (!empty($model)) {
            $this->addError($attributes, '邮箱已经被注册.');
        }
    }

    public function checkMaxOpenNum($attributes, $params)
    {
        if(!User::isSuper()){
            $max_num = Yii::$app->user->identity->max_open_num;
            $params = Yii::$app->request->post()['UserModel'];

        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => Yii::t('app', 'Manager Name'),
            'auth_key' => Yii::t('app', 'auth key'),
            'password_hash' => Yii::t('app', 'Password Hash'),
            'password_reset_token' => Yii::t('app', 'Password Reset Token'),
            'email' => Yii::t('app', 'email'),
            'mobile_phone' => Yii::t('app', 'attr variable7'),
            'role' => Yii::t('app', 'Role'),
            'status' => Yii::t('app', 'Status'),
            'mgr_org' => Yii::t('app', 'orgnization'),
            'mgr_product' => Yii::t('app', 'manager_mgr-product'),
            'mgr_admin' => Yii::t('app', 'manager_mgr-admin'),
            'tid' => Yii::t('app', 'Tid'),
            'path' => Yii::t('app', 'path'),
            'pid' => Yii::t('app', 'pid'),
            'mgr_admin_type' => Yii::t('app', 'manager_mgr-admin-type'),
            //'mgr_portal' => Yii::t('app', 'manager_mgr-portal'),
            'created_at' => Yii::t('app', 'created_at'),
            'updated_at' => Yii::t('app', 'updated_at'),
            'roles' => Yii::t('app', 'roles'),
            'password' => Yii::t('app', 'password'),
            'passwords' => Yii::t('app', 'Confirm Password'),
            'ip_area' => Yii::t('app', 'ip area'),
            'max_open_num' => Yii::t('app', 'max_open_num'),
            'expire_time' => Yii::t('app', 'expire time'),
            'old_password' => Yii::t('app', 'Old password'),
            'begin_time' => '入学时间',
            'stop_time' => '毕业时间',
            'major_id' => '专业性质',
            'sex' => '性别',
            'nation' => '民族',
            'id_number' => '身份证',
            'person_name' => '姓名'
        ];
    }

    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function signup()
    {
        if ($this->validate()) {
            return User::create($this->attributes);
        }

        return null;
    }

    /**
     * Creates data provider instance with search query applied
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = UserModel::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'username' => $this->username,
        ]);

        $query->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'id', $this->id]);

        return $dataProvider;
    }

    public static function getAttributesList()
    {
        return[
            'mgr_admin_type' => [
                '1' => Yii::t('app', 'auth_user_model_font2'),
                '2' => Yii::t('app', 'auth_user_model_font3'),
                '3' => Yii::t('app', 'auth_user_model_font4'),
                '4' => Yii::t('app', 'auth_user_model_font5'),
            ],
            'mgr_portal' => [
                'srun_portal_pc_hotel-basic' => Yii::t('app', 'UserModel_portal-1'),
                'srun_portal_pc_airport-basic' => Yii::t('app', 'UserModel_portal-2'),
                'srun_portal_pc_school-basic' => Yii::t('app', 'UserModel_portal-3'),
                'srun_portal_mobile_hotel-basic' => Yii::t('app', 'UserModel_portal-4'),
                'srun_portal_mobile_airport-basic' => Yii::t('app', 'UserModel_portal-5'),
                'srun_portal_mobile_school-basic' => Yii::t('app', 'UserModel_portal-6'),
            ],
        ];
    }

    /**
     * 获取更新之前的数据.
     * @param $isNewRecord 检查操作行为 true 为新增 false 为编辑
     * @return array|null 如果新增数据 则返回 null 否则返回该数据编辑之前的原始数据.
     */
    public function old($isNewRecord)
    {
        if ($isNewRecord) {
            return null;
        } else {
            return $this->_old;
        }
    }

    public static function getBuildAttributes($attributes)
    {
        unset($attributes['auth_key']);
        unset($attributes['password_hash']);
        $attributes['mgr_product'] = $attributes['mgr_product'] ? static::setBuildLogValue((new Product())->getNameArr(explode(',', $attributes['mgr_product']))) : ''; // 可管理的产品
        $attributes['mgr_org'] = $attributes['mgr_org'] ? static::setBuildLogValue((new SrunJiegou())->getGroupNameArr(explode(',', $attributes['mgr_org']))) : ''; // 可管理的组织结构
        return $attributes;
    }

    /**
     * 获取一个用户信息
     * @param mixed $id condition|array
     * @return bool|null|static
     */
    public static function findOne($id)
    {
        //从数据库中查询记录
        $model = parent::findOne($id);
        //将当前记录保存在临时旧数据
        $model->_old = self::getBuildAttributes($model->attributes);
        return $model;
    }

    /**
     * 构建拼装一个供日志完美展示的数据,
     *
     * For example,
     *
     * ~~~
     * $array = ['1' => 'user1', '2' => 'user2'];
     * 日志展示成 1:user1 形式.
     * ~~~
     */
    public static function setBuildLogValue($array)
    {
        $source = '';
        if (!empty($array)) {
            foreach ($array as $key => $val) {
                $source .= $key . ':' . $val . ',';
            }

            return rtrim($source, ',');
        }
    }

    /**
     * 根据指定的ID 获取相应的字段值.
     * @param $id
     * @param $field
     * @return mixed
     */
    public static function getItemValue($id, $field)
    {
        if ($id == '' || $field == '') {
            return false;
        } else {
            $data = self::find()->where(['id' => $id])->all();
            if($data) {
                return $data[0]['attributes'][$field];
            }
            return null;
        }
    }

    public function afterSave($insert, $changedAttributes)
    {
        $dirtyArr = LogWriter::dirtyData($this->old($insert), self::getBuildAttributes($this->attributes));

        if (!empty($dirtyArr)) {
            $logData = [
                'operator' => Yii::$app->user->identity->username,
                'target' => $this->username,
                'action' => $insert ? 'add' : 'edit',
                'action_type' => 'Setting Manager',
                'content' => Json::encode($dirtyArr),
                'class' => __CLASS__,
                'type' => 0,
            ];
            LogWriter::write($logData);
        }
    }

    public function afterDelete()
    {
        parent::afterDelete();

        $dirtyArr = LogWriter::dirtyData($this->getOldAttributes(), self::getBuildAttributes($this->attributes));

        if (!empty($dirtyArr)) {
            $logData = [
                'operator' => Yii::$app->user->identity->username,
                'target' => $this->username,
                'action' => 'delete',
                'action_type' => 'Setting Manager',
                'content' => Json::encode($dirtyArr),
                'class' => __CLASS__,
                'type' => 0,
            ];
            LogWriter::write($logData);
        }
    }
}
