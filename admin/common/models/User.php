<?php

namespace common\models;

use center\modules\auth\models\AuthAssignment;
use center\modules\auth\models\SrunJiegou;
use center\modules\log\models\LogWriter;
use center\modules\product\models\Major;
use center\modules\strategy\models\Product;
use common\extend\Tool;
use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;

/**
 * User model
 *
 * @property integer $id
 * @property string $username
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property string $auth_key
 * @property integer $role
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $password write-only password
 */
class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 10;
    const ROLE_USER = 10;

    public $roles;
    public $password;
    public $passwords;

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
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * Creates a new user
     *
     * @param  array $attributes the attributes given by field => value
     * @return static|null the newly created model, or null on failure
     */
    public static function create($attributes)
    {
        /** @var User $user */
        $user = new static();
        $user->setAttributes($attributes);
        $user->setPassword($attributes['password']);
        $user->generateAuthKey();
        $user->email = $attributes['email'];
        $user->mobile_phone = '';
        $user->ip_area = '';
        $user->mgr_org = $attributes['mgr_org']; // 可管理的组织结构
        $user->mgr_product = ''; // 可管理的产品
        $user->mgr_admin = ''; // 可管理的管理员
        $user->mgr_portal = ''; // portal
        $user->pid = Yii::$app->user->identity->getId(); // 父ID
        $user->path = self::getManagerPath(Yii::$app->user->identity->getId()); // 等级目录
        $user->tid = '';
        $user->mgr_admin_type = $attributes['mgr_admin_type'];
        $user->max_open_num = $attributes['max_open_num'];
        $user->expire_time = strtotime($attributes['expire_time']);
        $user->begin_time = strtotime($attributes['begin_time']);
        $user->stop_time = strtotime($attributes['stop_time']);
        $user->major_id = $attributes['major_id'];
        $major = Major::find()->where(['id' => $attributes['major_id']])->one();
        $user->major_name = $major ? $major->major_name : '';
        $user->person_name = isset($attributes['person_name']) ? $attributes['person_name'] : '';
        $user->nation = isset($attributes['nation']) ? $attributes['nation'] : '';
        $user->id_number =isset($attributes['id_number']) ? $attributes['id_number'] : '';
        $user->sex = isset($attributes['sex']) ? $attributes['sex'] : '男';
        $user->mobile_phone = isset($attributes['mobile_phone']) ? $attributes['mobile_phone'] : '男';

        if ($user->save()) {
            return $user;
        } else {
            return null;
        }
    }

    /**
     * 为新建用户生成PATH.
     * @param $uid
     * @return string
     */
    public static function getManagerPath($uid)
    {
        $data = self::findOne($uid);

        $path = $data->path . '-' . $data->id;

        return $path;
    }

    public function getChildIdAll()
    {
        $user = Yii::$app->user->identity['attributes'];

        switch ($user['mgr_admin_type']) {
            case '1':
                return $this->getTypeOne($user); //不管理其他人
                break;
            case '2':
                return $this->getTypeTwo($user); //只管理所勾选管理员
                break;
            case '3':
                return $this->getTypeThree($user); //管理勾选管理员及自己创建的管理员
                break;
            case '4':
                return $this->getTypeFore($user); //管理自己创建的管理员
                break;
            default:
                break;
        }
    }

    public function getChildIdAllTwo($id = null, $type = 1, $action = 'add')
    {
        if (!empty($id)) {
            $user = $this->findOne($id);
        } else {
            $user = Yii::$app->user->identity['attributes'];
        }

        //$user = Yii::$app->user->identity['attributes'];

        switch ($type) {
            case '1':
                return $this->getTypeOne($user); //不管理其他人
                break;
            case '2':
                return $this->getTypeTwoSecond($user, $action); //只管理所勾选管理员
                break;
            case '3':
                return $this->getTypeThreeSecond($user, $action); //管理勾选管理员及自己创建的管理员
                break;
            case '4':
                return $this->getTypeFore($user); //管理自己创建的管理员
                break;
            default:
                break;
        }
    }

    //不管理其他人 只管理自己.
    public function getTypeOne($user)
    {
        return [$user['id'] => $user['username']];
    }

    //只管理所勾选管理员
    public function getTypeTwo($user)
    {
        $data[$user['id']] = $user['username'];
        if (!empty($user['mgr_admin'])) {
            $array = explode(',', $user['mgr_admin']);

            foreach ($array as $val) {
                $model = $this->findOne($val);

                if ($model) {
                    $data[$model['id']] = $model['username'];
                }
            }
        }
        return $data;
    }

    /**
     * 只管理可勾选的
     * @param $user
     * @param string $action
     * @return array
     */
    public function getTypeTwoSecond($user, $action = 'add')
    {
        $data = [];
        if ($action != 'add') {
            $data[$user['id']] = $user['username'];
        }
        $pid = $user->pid;
        $id = $user->id;
        $path = $user->path ? $user->path : $user['path'];
        $grade = count(explode('-', $path));

        if (!empty($pid)) {
            $users = $this->find()->select('id,pid,username,path')->where('id !=' . $pid)->andWhere("pid != '{$pid}'")->andWhere('pid !=' . $id)->asArray()->all(); //除去去父元素的所有管理以及自己创建并且除去自己的上级元素
        } else {
            //创建管理员
            $users = $this->find()->select('id,username,pid,mgr_admin,path')->andWhere('pid !=' . $user['id'])->asArray()->all(); //除去去父元素的所有管理
        }


        //获取可勾选管理员
        $source = $this->getLevelData($users, $grade, $action);
        if ($action != 'add') {
            //添加新管理员，没有创建管理员
            $grade = count(explode('-', $user['path']));
            $path = "%{$user['id']}%";
            //查出子孙级元素
            $users = $this->find()->select('id,username,pid,path')->where('path like' . "'{$path}'")->asArray()->all();
            if ($users) {
                $sonData = $this->getLevelData($users, $grade, $action);
            }

        }
        //var_dump($source);exit;

        if ($action != 'add' && !empty($source) && !empty($sonData)) {
            $i = count($source);

            $source[$i] = [
                'id' => $user['id'],
                'username' => $user['username'],
                'isCreate' => true,
                'child' => $sonData,
            ];
            $data = $source;
            // var_dump($sonData,$source);exit;

        } else if (!empty($sonData)) {
            $data[] = [
                'id' => $user['id'],
                'username' => $user['username'],
                'isCreate' => true,
                'child' => $sonData,
            ];
        } else {
            $data = $source;
        }
//        echo '<pre>';
//        var_dump($data);exit;


        return $data;
    }

    /**
     * 管理勾选和自己创建的
     * @param $user
     * @return array
     */
    public function getTypeThree($user)
    {
        $source = $this->getTypeTwo($user);
        $data[$user['id']] = $user['username'];
        $like = "%" . $user['id'] . "%";
        $array = $this->find()->where("path LIKE '{$like}'")->asArray()->all();
        if ($array) {
            foreach ($array as $val) {
                $data[$val['id']] = $val['username'];
            }
        }
        if (!empty($source) && !empty($array)) {
            $data = array_unique($source + $data);
        } else if (!empty($array)) {
            $data = array_unique($data);
        } else {
            $data = array_unique($source);
        }

        return $data;
    }

    //管理勾选管理员及自己创建的管理员
    public function getTypeThreeSecond($user, $action = 'add')
    {
        $data = [];
        if ($action != 'add') {
            $data[$user['id']] = $user['username'];
        }
        $pid = $user->pid;
        $id = $user->id;
        $path = $user->path ? $user->path : $user['path'];
        $grade = count(explode('-', $path));

        if (!empty($pid)) {
            $users = $this->find()->select('id,pid,username,path')->where('id !=' . $pid)->andWhere("pid != '{$pid}'")->andWhere('pid !=' . $id)->asArray()->all(); //除去去父元素的所有管理以及自己创建并且除去自己的上级元素
        } else {
            //创建管理员
            $users = $this->find()->select('id,username,pid,mgr_admin,path')->andWhere('pid !=' . $user['id'])->asArray()->all(); //除去去父元素的所有管理
        }


        //获取可勾选管理员
        $source = $this->getLevelData($users, $grade, $action);

        return $source;
    }

    public function getTypeFore($user)
    {
        $data[$user['id']] = $user['username'];

        $array = $this->find()->where('pid = ' . $user['id'])->asArray()->all();

        if ($array) {
            foreach ($array as $val) {
                $data[$val['id']] = $val['username'];
            }
        }

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED]],

            ['role', 'default', 'value' => self::ROLE_USER],
            ['role', 'in', 'range' => [self::ROLE_USER]],

            ['username', 'filter', 'filter' => 'trim'],
            ['username', 'required'],
            ['username', 'unique'],
            ['username', 'string', 'min' => 2, 'max' => 255],

            /*['email', 'filter', 'filter' => 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'unique'],*/

        ];
    }

    public function attributeLabels()
    {
        return [
            'roles' => '角色组',
            'username' => '名称',
            'password' => '密码',
            'passwords' => '确定密码',
            'mgr_product' => '可管理产品',
            'mgr_admin_type' => '可管理类型',
            'mgr_admin' => '可管理的用户',
        ];
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        try {
            return self::findOne(['username' => $username]); // 'status' => self::STATUS_ACTIVE
        } catch (\Exception $e) {
            var_dump($e->getMessage());
            exit;
        }

    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        $parts = explode('_', $token);
        $timestamp = (int)end($parts);
        if ($timestamp + $expire < time()) {
            // token expired
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            //'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function resetPassword($password)
    {
        return Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    /**
     * 判断管理员是否可以管理
     * @param $type string org:组织，product:产品,admin:管理员
     * @param $id int 组id或产品id或管理员名称
     * @return bool
     */
    public static function canManage($type, $id)
    {
        //判断管理员是否可以管理此组
        if (self::isSuper()) {
            return true;
        }
        if ($type == 'org') {
            // ['结构ID', '结构ID', '结构ID']
            $allOrg = SrunJiegou::getAllNode();
            if (in_array($id, $allOrg)) {
                return true;
            }
        } else if ($type == 'product') {
            $productModel = new Product();
            // ['产品id'=>'产品1', '产品id'=>'产品2']
            $allProduct = $productModel->getNameOfList();
            if (array_key_exists($id, $allProduct)) {
                return true;
            }
        } else if ($type == 'admin') {
            $mgrModel = new User();
            // ['user_id'=>'管理员', 'user_id'=>'管理员']
            $allAdmin = $mgrModel->getChildIdAll();
            if (array_key_exists($id, $allAdmin)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 获取用户的权限组
     * @param null $id 默认值代表当前用户id
     * @return string
     */
    public static function getRole($id = null)
    {
        if ($id == null) {
            $id = Yii::$app->user->getId();
        }

        $result = AuthAssignment::findOne(['user_id' => $id]);

        if ($result) {
            return $result->item_name;
        }

        return null;
    }

    /**
     * 判断id是否是超级管理员
     * @param null $id 默认值代表当前用户id
     * @return bool
     */
    public static function isSuper($id = null)
    {
        $preg = SUPER_ROLE;
        return preg_match("/^$preg/", self::getRole($id));
    }

    /**
     * 判断id是否是学生
     * @param null $id 默认值代表当前用户id
     * @return bool
     */
    public static function isStudent($id = null)
    {
        $preg = "学生";
        return preg_match("/^$preg/", self::getRole($id));
    }
    public static function isRoot($id = null)
    {
        $preg = SUPER_ROLE;
        return self::getRole($id) == $preg;
    }

    public static function getMgrOrg($id)
    {
        return self::findOne($id)->mgr_org;
    }

    /**
     * 获取应用端token
     */
    public static function validateToken()
    {

        //如果没有配置 两个参数，则直接略过云端验证
        if (empty(Yii::$app->params['dbConfig']['products_key']) || empty(Yii::$app->params['dbConfig']['products_password'])) {
            return false;
        }

        $config = Yii::$app->params['srunCloudApi'];
        $url = $config['url'] . $config['version'][0] . $config['version']['login'];
        $data = [
            'username' => Yii::$app->params['dbConfig']['products_key'],
            'password' => md5(Yii::$app->params['dbConfig']['products_password']),
        ];
        $json = Tool::postApi($url, http_build_query($data));
        if (empty($json)) {
            echo '<div style="color: #ffb61c;text-align: center">' . Yii::t('app', 'login timeout by reason2') . '</div>';
            return false;
        }
        if ($json) {
            $res = json_decode($json, true);
            return $res;
        }
        return false;
    }

    //获取的token信息存入session
    public static function tokenToSession()
    {
        $res = self::validateToken();
        if ($res) {
            if ($res['code'] === 0 && isset($res['data'])) {
                $data = $res['data'];
                Yii::$app->session['access_token'] = isset($data['access_token']) && !empty($data['access_token']) ? $data['access_token'] : null;
                //应用id
                Yii::$app->session['access_uid'] = isset($data['uid']) && !empty($data['uid']) ? $data['uid'] : 0;
                //过期时间
                Yii::$app->session['access_expire_time'] = isset($data['expire_time']) && !empty($data['expire_time']) ? $data['expire_time'] : 0;
            } else {
                //把错误写入session
                Yii::$app->session['cloud_err'] = '[' . $res['code'] . ']' . $res['message'];
                //如果有强制退出标示，就提示被强制退出
                $isForceLogout = Redis::executeCommand('get', 'forced_exit', []);
                if ($isForceLogout) {
                    Yii::$app->getSession()->setFlash('error', Yii::t('app', 'login timeout by reason1', ['message' => $res['message']]));
                } else {
                    Yii::$app->session['cloud_err'] = '[' . $res['code'] . ']' . $res['message'];
                    Yii::$app->getSession()->setFlash('error', Yii::t('app', 'login timeout by reason', ['message' => '[' . $res['code'] . ']' . $res['message']]));
                    return false;
                }
                return false;
            }
        }
    }

    /**
     * 得到下级管理员
     * @param array $users
     * @param int $grade
     * @param string $action
     * @param int $pid
     * @param bool|true $flag
     * @return array
     */
    protected function getLevelData($users = [], $grade = 1, $action = 'add', $pid = 0, $flag = true)
    {
        $data = [];
        if (!empty($users)) {
            foreach ($users as &$v) {
                if ($this->isSuper($v['id'])) {
                    continue;
                }
                $grades = count(explode('-', $v['path']));
                //var_dump($grade <= $grades,$grade,$grades);
                if ($grade >= $grades) {
                    continue;
                }
                //echo $v['pid'] .'<hr/>';
                if (intval($v['pid']) == intval($pid)) {
                    $v['level'] = $grades;
                    $v['child'] = $this->getLevelData($users, $grades, 'edit', $v['id'], false);
                    $data[] = $v;
                } else {
                    //得到下一级
                    if ($action == 'add') {
                        //添加，需先获取下两级，因为同级之间不能管理
                        if ($flag && $grades - $grade == 2) {
                            $v['level'] = $grades;
                            $v['child'] = $this->getLevelData($users, $grades, 'edit', $v['id'], false);
                            $data[] = $v;
                        }
                    } else {
                        //更新管理员
                        if ($flag) {
                            if ($grades - $grade == 1) {
                                $v['level'] = $grades;
                                $v['child'] = $this->getLevelData($users, $grades, 'edit', $v['id'], false);
                                $data[] = $v;
                            }

                        }
                    }

                }
            }
        }

        return $data;
    }

    public function setLog($mgrName, $user_name)
    {
        //组装msg
        $msg = Yii::t('app', 'auth help1', [
            'mgrName' => $mgrName,
            'username' => $user_name,
            'time' => date('Y-m-d H:i')
        ]);
        //写日志开始
        $data = [
            'operator' => $mgrName,
            'target' => $user_name,
            'action' => 'onlineProduct',
            'action_type' => 'User Base',
            'content' => $msg,
            'class' => __CLASS__,
            'type' => 1, //描述日志
        ];

        LogWriter::write($data);

        return true;
    }
}
