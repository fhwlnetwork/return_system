<?php
namespace center\models;

use Yii;
use yii\base\Model;
use common\models\User;
use yii\helpers\Json;
use center\modules\log\models\LogWriter;
use center\modules\auth\models\SrunJiegou;
use center\modules\strategy\models\Product;


/**
 * Signup form
 */
class SignupForm extends Model
{
    public $username;
    public $email;
    public $mobile_phone;
    public $ip_area;
    public $max_open_num = 0;
    public $expire_time;
    public $password;
    public $passwords;
    public $roles;
    public $begin_time;
    public $stop_time;
    public $major_id;
    public $major_name;
    public $isNewRecord = true;
    public $id_number;
    public $person_name;
    public $sex;
    public $nation;

    //自定义字段
    public $mgr_org;//管理的组织结构节点
    public $mgr_product;//可以管理的产品
    public $mgr_admin;//可以管理的管理员
    public $mgr_admin_type = 1; //管理类型
    public $mgr_portal; //管理portal

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'password', 'passwords', 'roles', 'mgr_admin_type'], 'required'],
            [['username'], 'filter', 'filter' => 'trim'],
            ['username', 'unique', 'targetClass' => '\common\models\User', 'message' => '用户名已经被使用，换一个吧'],
            ['username', 'string', 'min' => 2, 'max' => 20],
            ['email', 'email'],
            ['email', 'unique', 'targetClass' => '\common\models\User', 'message' => '邮箱已经被注册.'],
            ['mobile_phone', 'match', 'pattern' => '/^1[0-9]{10}$/', 'message' => '{attribute}必须为1开头的11位纯数字'],
            ['password', 'string', 'min' => 6],
            ['passwords', 'compare', 'compareAttribute' => 'password'],
            ['ip_area', 'match', 'pattern' => '/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}((\/|-)(\d{1,3}))?(\,\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}((\/|-)(\d{1,3}))?)*$/', 'message' => '{attribute}不是正确格式的ip区域'],
            [['max_open_num'], 'integer'],
            [['max_open_num', 'expire_time'], 'default', 'value' => 0],
        ];
    }

    public function scenarios()
    {
        return [
            'default' => ['username', 'email', 'mobile_phone', 'password', 'passwords', 'roles', 'mgr_org', 'mgr_product', 'mgr_admin', 'mgr_admin_type', 'mgr_portal', 'ip_area', 'max_open_num', 'expire_time', 'begin_time', 'stop_time', 'major_id','id_number',
                'sex','person_name','nation'
            ],
        ];
    }


    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'username' => Yii::t('app', 'account name'),
            'alias' => Yii::t('app', 'alias'),
            'password' => Yii::t('app', 'password'),
            'passwords' => Yii::t('app', 'Confirm Password'),
            'email' => Yii::t('app', 'email'),
            'mobile_phone' => Yii::t('app', 'attr variable7'),
            'ip_area' => Yii::t('app', 'ip area'),
            'roles' => Yii::t('app', 'roles group'),
            'mgr_product' => Yii::t('app', 'product'),
            'mgr_admin' => Yii::t('app', 'manager'),
            'mgr_org' => Yii::t('app', 'orgnization'),
            'mgr_admin_type' => Yii::t('app', 'manager type'),
            'mgr_portal' => Yii::t('app', 'manager_mgr-portal'),
            'max_open_num' => Yii::t('app', 'max_open_num'),
            'expire_time' => Yii::t('app', 'expire time'),
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
        } else {
            var_dump($this->getErrors());exit;
        }

        return null;
    }

    public function sendNoticeEmail($user, $password)
    {
        return \Yii::$app->mail->compose('sendNoticeEmail', ['user' => $user, 'password' => $password])
            ->setFrom(\Yii::$app->params['supportEmail'])
            ->setTo($user->email)
            ->setSubject('管理员邮件通知')
            ->send();
    }

    public function log($oldAttributes, $attributes, $insert)
    {
        $dirtyArr = LogWriter::dirtyData($oldAttributes, self::getBuildAttributes($attributes));

        if (!empty($dirtyArr)) {
            $logData = [
                'operator' => Yii::$app->user->identity->username,
                'target' => $attributes['username'],
                'action' => $insert ? 'add' : 'edit',
                'action_type' => 'Assign Manager',
                'content' => Json::encode($dirtyArr),
                'class' => 'center\modules\auth\models\UserModel',
                'type' => 0,
            ];
            LogWriter::write($logData);
        }
    }

    public static function getBuildAttributes($attributes)
    {
        //var_dump($attributes);exit;
        unset($attributes['isNewRecord']);
        unset($attributes['password']);
        unset($attributes['passwords']);
        $attributes['mgr_product'] = $attributes['mgr_product'] ? static::setBuildLogValue((new Product())->getNameArr(explode(',', $attributes['mgr_product']))) : ''; // 可管理的产品
        $attributes['mgr_org'] = $attributes['mgr_org'] ? static::setBuildLogValue((new SrunJiegou())->getGroupNameArr(explode(',', $attributes['mgr_org']))) : ''; // 可管理的组织结构
        return $attributes;
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
}