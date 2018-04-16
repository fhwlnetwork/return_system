<?php
namespace center\modules\user\models;

use yii;
use center\modules\auth\models\SrunJiegou;
use center\modules\financial\models\PayList;
use center\modules\financial\models\WaitCheck;
use center\modules\log\models\LogWriter;
use center\modules\strategy\models\IpPool;
use center\modules\strategy\models\Product;
use center\modules\strategy\models\ProductsChange;
use common\models\Feita;
use common\models\KernelInterface;
use common\models\Redis;
use center\modules\setting\models\ExtendsField;
use center\modules\strategy\models\Condition;
use center\modules\financial\models\PayType;
use yii\helpers\Json;

/**
 * Class Base
 * @package center\modules\user\models
 * @property string $user_name 账号
 */
class Users extends yii\db\ActiveRecord
{
    public $products = [];
    public $user_extends = [];
    //redis中存储的user id
    public $redis_uid;
    //密码
    public $user_password;
    public $user_password_md5;
    //确认密码
    public $user_password2;
    //状态，0-正常 1-禁用 2-停机保号 3-暂停 4-未开通
    //public $user_available = 0;
    //订购的产品 产品ID 数组 [1, 2..]， redis中多个产品用英文逗号隔开。
    public $products_id = [];
    //默认搜索显示的字段
    public $defaultField = ['user_id', 'user_name', 'user_real_name', 'group_id', 'user_available', 'balance'];
    //时间格式的字段
    public $dateTimeField = ['user_create_time', 'user_update_time', 'user_expire_time'];
    //保存为模板
    public $saveTem = 0;
    //模板名称
    public $temName = '';
    //是否公共模板
    public $commonTem = 0;
    //选择模板
    public $selectTemplate = 0;
    //临时旧数据
    private $_temOldAttr = [];
    public $pwd_type;
    //缴费方式
    public $payType;
    public $payModel = null;
    //手机号 手机密码
    public $mobile_phone;
    //是否用明文保存手机号码
    public $mobile_is_text = 0;
    //确认手机号
    public $mobile_phone2;
    public $mobile_password;
    //确认手机密码
    public $mobile_password2;
    //最大在线人数
    public $max_online_num;
    //免认证账号
    public $interface_name;
    //免认证状态
    public $interface_status = 1;//默认不允许
    //是否MAC认证 1是开启 0是关闭
    public $mac_auth = 1;
    //批量缴费字段
    public $balance_add;
    //确认密码
    public $user_confirm_password;
    //新密码
    public $user_new_password;
    //产品余额
    public $product_balance;
    //是否修改密码
    public $is_edit_password;
    //在线状态
    public $user_online_status;
    //自动绑定的ip
    public $bindIp;
    //具体信息
    public $message;
    public $default_type; //默认支付方式
    public $package_list; //套餐
    public $products_pay;
    public $products_package;
    public $extMsg; //excel反馈信息
    public $_searchField;
    private static $_instance = null;

    public function init()
    {
        $this->user_extends = ExtendsField::getAllData();
    }

    public static function getInstance()
    {
        if (is_null(self::$_instance) && !(self::$_instance instanceof self)) {
            self::$_instance = new self ();
        }
        return self::$_instance;
    }

    public static function tableName()
    {
        return 'users';
    }

    public $_mgrName;


    /**
     * 获取操作的管理员
     * @return mixed
     */
    public function getMgrName()
    {
        if ($this->_mgrName == '') {
            $this->setMgrName();
        }
        return $this->_mgrName;
    }

    /**
     * 设置管理员姓名
     * @param $name null|string
     * @return string
     */
    public function setMgrName($name = null)
    {
        if (is_null($name)) {
            $this->_mgrName = Yii::$app->user->identity->username;
        } else {
            $this->_mgrName = $name;
        }
    }

    public function rules()
    {
        //获取扩展字段的必填项
        $mustField = [];
        if ($this->user_extends) {
            foreach ($this->user_extends as $one) {
                if ($one['is_must'] == 1) {
                    $mustField[] = $one['field_name'];
                }
            }
        }

        $pwd_strong = Setting::findOne(['key' => 'pwd_strong']);
        $strong = [['user_password'], 'required', 'on' => ['add']];
        if ($pwd_strong->value == 1) {
            $strong = [['user_password'], 'string', 'min' => 6, 'on' => ['add', 'edit', 'chgPassword']];
        } elseif ($pwd_strong->value == 2) {
//            $strong = [['user_password'],'string','min' => 8,'on' => ['add','edit','chgPassword']];
            $strong = [['user_password'], 'match', 'pattern' => '/^(?![a-zA-z]+$)(?!\d+$)(?![!@#$%^&*]+$)[a-zA-Z\d!@#$%^&*]{8,20}$/', 'on' => ['add', 'edit', 'chgPassword'], 'message' => Yii::t('app', 'pwd_8_20')];
        } elseif ($pwd_strong->value == 3) {
//            $strong = [['user_password'],'string','min' => 10,'on' => ['add','edit','chgPassword']];
            $strong = [['user_password'], 'match', 'pattern' => '/^(?![a-zA-z]+$)(?!\d+$)(?![!@#$%^&*]+$)[a-zA-Z\d!@#$%^&*]{10,20}$/', 'on' => ['add', 'edit', 'chgPassword'], 'message' => Yii::t('app', 'pwd_10_20')];

        }

        $mustAdd = array_merge($mustField, ['user_name', 'user_password', 'group_id', 'products_id', 'user_confirm_password']);
        $mustEdit = $mustField;

        return [
            [$mustAdd, 'required', 'on' => ['add']],
            $strong,
            ['user_name', 'unique', 'on' => ['add']],
            ['user_name', 'trim', 'on' => ['add']],
            ['user_name', 'string', 'length' => [1, 64], 'on' => ['add']],
            ['user_name', 'match', 'pattern' => '/^[a-zA-Z0-9][a-zA-Z0-9@._-]{0,63}$/', 'on' => ['add']],
            ['group_id', 'groupMust', 'on' => ['add']],
            ['products_id', 'productsIdMust', 'on' => ['add']],
            ['user_confirm_password', 'compare', 'compareAttribute' => 'user_password', 'on' => ['add']],
            ['user_new_password', 'checkConfirmPassword', 'on' => ['edit']],
            ['user_confirm_password', 'compare', 'compareAttribute' => 'user_new_password', 'on' => ['edit']],
            [$mustEdit, 'required', 'on' => ['edit']],
            [['user_password', 'user_password2'], 'required', 'on' => ['chgPassword']],
            [['user_password', 'user_password2'], 'string', 'max' => 64, 'on' => ['chgPassword']],
            [['user_password', 'user_password2'], 'string', 'min' => 6, 'on' => ['chgPassword']],
            [['user_password2'], 'compare', 'compareAttribute' => 'user_password', 'on' => ['chgPassword']],
            ['temName', 'default', 'value' => Yii::t('app', 'user template'), 'on' => ['add']],
            [['user_expire_time'], 'default', 'value' => 0, 'on' => ['add', 'edit']],
            ['payType', 'integer'],
            [['mobile_phone', 'mobile_password'], 'string', 'on' => ['add', 'edit']],
            [['phone'], 'string', 'length' => 11, 'on' => ['add', 'edit']],
            [['cert_num', 'cert_type'], 'string', 'on' => ['add', 'edit']],
            [['cert_num'], 'checkCert', 'on' => ['add', 'edit']],
            //[['mobile_phone2'], 'compare', 'compareAttribute'=>'mobile_phone', 'on' => ['add', 'edit']],
            //[['mobile_password2'], 'compare', 'compareAttribute'=>'mobile_password', 'on' => ['add', 'edit']],
            ['max_online_num', 'string', 'on' => ['add', 'edit']],
            ['user_available', 'integer'],
            [['temName', 'group_id', 'products_id'], 'required', 'on' => ['addtem']],
        ];
    }

    public function checkCert()
    {
        if ($this->cert_type == 1) {
            if (strlen($this->cert_num) != 18) {
                $this->addError($this->cert_num, Yii::t('app', 'cert_num length error'));
            }
        }
    }

    /**
     * 场景
     * @return array
     */

    public function scenarios()
    {
        $arr1 = ['user_password', 'user_allow_chgpass', 'user_available', 'user_real_name', 'group_id', 'products_id', 'user_expire_time', 'mobile_is_text', 'interface_status'];
        //获取扩展字段的列表
        $exFields = [];
        if ($this->user_extends) {
            foreach ($this->user_extends as $one) {
                $exFields[] = $one['field_name'];
            }
        }
        //添加上附加字段
        $arrEdit = yii\helpers\ArrayHelper::merge($arr1, $exFields);
        //添加上模板部分
        $arrAdd = yii\helpers\ArrayHelper::merge($arrEdit, ['user_name', 'saveTem', 'temName', 'commonTem']);

        return yii\helpers\ArrayHelper::merge(parent::scenarios(), [
            'add' => $arrAdd,
            'edit' => $arrEdit,
            'chgPassword' => ['user_name', 'user_real_name', 'user_password', 'user_password2'],
            'addtem' => ['temName', 'products_id', 'group_id'],
        ]);
    }

    public function getSearchField()
    {
        if (!is_null($this->_searchField)) {
            return $this->_searchField;
        }
        //将扩展字段加入搜索项
        $exFields = [];
        if ($this->user_extends) {
            foreach ($this->user_extends as $one) {
                $exFields[$one['field_name']] = $one['field_desc'];
            }
        }
        $this->_searchField = yii\helpers\ArrayHelper::merge([
            'user_id' => Yii::t('app', 'user id'),
            'user_name' => Yii::t('app', 'account'),
            'user_real_name' => Yii::t('app', 'name'),
            'group_id' => Yii::t('app', 'group id'),
            'products_id' => Yii::t('app', 'user products id'),
            'user_balance' => Yii::t('app', 'products balance'),
            'user_online_status' => Yii::t('app', 'user online status'),
            'user_available' => Yii::t('app', 'user available'),
            'balance' => Yii::t('app', 'electronic purse'),
            'user_allow_chgpass' => Yii::t('app', 'user allow chgpass'),
            'user_expire_time' => Yii::t('app', 'user expire time'),
            'user_create_time' => Yii::t('app', 'user create time'),
            'mgr_name_create' => Yii::t('app', 'mgr name create'),
            'user_update_time' => Yii::t('app', 'user update time'),
            'mgr_name_update' => Yii::t('app', 'mgr name update'),
            'temName' => Yii::t('app', 'template name'),
        ], $exFields);

        return $this->_searchField;
    }

    public function attributeLabels()
    {
        $labels = $this->getSearchField();

        //扩展属性
        if ($this->user_extends) {
            foreach ($this->user_extends as $one) {
                $labels[$one['field_name']] = $one['field_desc'];
            }
        }

        //产品名称
        $labels['products_name'] = Yii::t('app', 'products name');

        //设置属性
        $labels['user_password'] = Yii::t('app', 'password');
        $labels['pwd_type'] = Yii::t('app', 'pwd_type');
        $labels['user_password2'] = Yii::t('app', 'Confirm Password');
        $labels['selectTemplate'] = Yii::t('app', 'select template');
        //绑定信息
        $labels['no_mac_auth'] = Yii::t('app', 'no_mac_auth');
        $labels['mac_auths'] = Yii::t('app', 'mac_auths');
        $labels['macs'] = Yii::t('app', 'macs');
        $labels['nas_port_ids'] = Yii::t('app', 'nas_port_ids');
        $labels['vlan_ids'] = Yii::t('app', 'vlan_ids');
        $labels['ips'] = Yii::t('app', 'ips');

        //开停机时间
        $labels['user_start_time'] = Yii::t('app', 'user start time');
        $labels['user_stop_time'] = Yii::t('app', 'user stop time');
        //缴费方式
        $labels['payType'] = Yii::t('app', 'pay type');
        //手机号、手机密码
        $labels['mobile_phone'] = Yii::t('app', 'mobile phone');
        $labels['mobile_is_text'] = Yii::t('app', 'mobile is text');
        $labels['mobile_phone2'] = Yii::t('app', 'confirm phone');
        $labels['mobile_password'] = Yii::t('app', 'mobile password');
        $labels['mobile_password2'] = Yii::t('app', 'confirm mobile password');
        //最大在线人数
        $labels['max_online_num'] = Yii::t('app', 'max online num');
        //免认证账号和状态
        $labels['interface_name'] = Yii::t('app', 'interface name');
        $labels['interface_status'] = Yii::t('app', 'interface status');
        $labels['user_confirm_password'] = Yii::t('app', 'Confirm Password');

        return $labels;
    }

    /**
     * 自定义rules：必须选择用户组
     * @param $attribute
     * @param $params
     */
    public function groupMust($attribute, $params)
    {
        $params = Yii::$app->request->post()['Base'];
        if ($params['group_id'] < 1) {
            $this->addError($attribute, Yii::t('app', 'user base help20'));
        }
    }

    public function checkConfirmPassword($attribute, $params)
    {
        $params = Yii::$app->request->post()['Base'];
        if (!empty($params['user_new_password'])) {
            if (empty($params['user_confirm_password'])) {
                $this->addError('user_confirm_password', Yii::t('app', 'confirm password not blank'));
                Yii::$app->getSession()->setFlash('error', Yii::t('app', 'confirm password not blank'));
            }
        }
    }

    public function getAttributesList()
    {
        //获取扩展字段的列表字段
        $exField = ExtendsField::getList();
        $conditon = Condition::getNameList();
        $pay_type = PayType::getTypesWithoutBalance();
        $max_online_num_selection = [];
        for ($i = 0; $i <= 100; $i++) {
            $max_online_num_selection[] = $i;
        }
        return yii\helpers\ArrayHelper::merge($exField, [
            //缴费方式
            'payType' => $pay_type,
            //用户状态
            'user_available' => [
                '0' => Yii::t('app', 'user available0'),
                '1' => Yii::t('app', 'user available1'),
                '2' => Yii::t('app', 'user available3'),
                '3' => Yii::t('app', 'user available4'),
                '4' => Yii::t('app', 'user available5'),
            ],
            //用户在线状态
            'user_online_status' => [
                '0' => Yii::t('app', 'user online status0'),
                '1' => Yii::t('app', 'user online status1'),
            ],
            //是否允许修改密码
            'user_allow_chgpass' => [
                '1' => Yii::t('app', 'allow'),
                '0' => Yii::t('app', 'deny'),
            ],
            //绑定类型
            'bindType' => [
                '1' => Yii::t('app', 'mac_auths'),
                '2' => Yii::t('app', 'macs'),
                '3' => Yii::t('app', 'nas_port_ids'),
                '4' => Yii::t('app', 'vlan_ids'),
                '5' => Yii::t('app', 'ips'),
            ],
            'bindCDRType' => [
                'ip' => Yii::t('app', 'ip'),
                'mac' => Yii::t('app', 'mac'),
                'terminal' => Yii::t('app', 'terminal'),
            ],
            //用户过期时间
            'user_expire_time' => [
                '0' => Yii::t('app', 'user expire time2'),//永不过期
            ],
            //停机保号类型
            'stopType' => [
                'days' => Yii::t('app', 'days'),
                'months' => Yii::t('app', 'months'),
                'years' => Yii::t('app', 'years'),
            ],
            //停机保号状态选择
            'stopTypeStatus' => [
                '2' => Yii::t('app', 'user available3'),
                '0' => Yii::t('app', 'user available0'),
            ],
            //立即转移产品扣费模式
            'dedFeeType' => [
                'allfee' => Yii::t('app', 'ded fee by allfee'),
                'halffee' => Yii::t('app', 'ded fee by halffee'),
                'byday' => Yii::t('app', 'ded fee by day'),
                'nonded' => Yii::t('app', 'non deduction'),
            ],
            //是否结算
            'isCheckout' => [
                1 => Yii::t('app', 'checkout'),
                0 => Yii::t('app', 'checkout type0'),
            ],
            //是否允许明文保存手机号
            'mobile_is_text' => [
                '1' => Yii::t('app', 'allow'),
                '0' => Yii::t('app', 'deny'),
            ],
            'interface_status' => [
                '0' => Yii::t('app', 'allow'),
                '1' => Yii::t('app', 'deny'),
            ],
            'condition' => array_merge(['' => Yii::t('app', 'select condition')], $conditon),
            'max_online_num_selection' => $max_online_num_selection,
        ]);
    }

    /**
     * 获取一个用户信息
     * @param mixed $id condition|array
     * 如果参数传用户id，就可以直接传id,
     * 如果是精确查找其他字段，那么传数组，如['user_name'=>'test'],
     * 如果直接传用户名，那么会模糊查询用户名user_name
     * @return center\modules\user\models\Base
     */
    public static function findOne($id)
    {
        //从数据库中查询记录
        if (is_array($id)) {
            $model = parent::findOne($id);
        } else {
            $model = parent::findOne($id);
            if (empty($model)) {
                $model = parent::find()->filterWhere(['like', 'user_name', $id])->one();
            }
        }

        if (!$model) {
            return false;
        }
        //处理时间
        $model->user_expire_time = $model->user_expire_time > 0 ? date('Y-m-d H:i', $model->user_expire_time) : '';

        //从redis中获取数据
        $model->redis_uid = Redis::executeCommand('get', 'key:users:user_name:' . $model->user_name);
        $user = Redis::executeCommand('HGETALL', 'hash:users:' . $model->redis_uid);
        if ($user) {
            $user = Redis::hashToArray($user);
            //$model->user_available = $user['user_available'];
            // 查询订购的产品
            $model->products_id = Redis::executeCommand('LRANGE', 'list:users:products:' . $model->redis_uid, [0, -1]);
            //手机号，手机密码
            $model->mobile_phone = isset($user['mobile_phone']) ? $user['mobile_phone'] : '';
            $model->mobile_password = isset($user['mobile_password']) ? $user['mobile_password'] : '';
            //最大在线人数
            $model->max_online_num = isset($user['max_online_num']) ? $user['max_online_num'] : '';
            //是否mac认证
            $model->mac_auth = isset($user['no_mac_auth']) && $user['no_mac_auth'] == 0 ? 1 : 0;
            //是否允许绑定cdr
            $model->interface_status = isset($user['interface_status']) ? $user['interface_status'] : $model->interface_status;
        }

        //将当前记录保存在临时旧数据
        $model->_temOldAttr = $model->getCurrentData();

        return $model;
    }
    /**
     * 自定义rules：必须选择产品
     * @param $attribute
     * @param $params
     */
    public function productsIdMust($attribute, $params)
    {
        $params = Yii::$app->request->post()['Base'];
        $is_products = false;
        if (is_array($params['products_id'])) {
            foreach ($params['products_id'] as $pro) {
                if (isset($pro['open']) && $pro['open'] == '1') {
                    $is_products = true;
                }
            }
        }
        if (!$is_products) {
            $this->addError($attribute, Yii::t('app', 'user base help23'));
        }
    }

    public function transactions()
    {
        return [
            'default' => self::OP_UPDATE,
        ];
    }

    public function beforeSave($insert)
    {
        //过期时间
        if ($this->user_expire_time) {
            $this->user_expire_time = strtotime($this->user_expire_time);
        } else {
            $this->user_expire_time = 0;
        }

        if ($insert) {
            //余额
            if (empty($this->balance)) {
                $this->balance = 0;
            }
            $this->user_create_time = $this->user_create_time ? strtotime($this->user_create_time) : time();
            $this->user_update_time = $this->user_update_time ? strtotime($this->user_update_time) : time();
            $this->mgr_name_create = $this->mgr_name_create ? $this->mgr_name_create : $this->getMgrName();
            $this->mgr_name_update = $this->getMgrName();
        } else {
            $this->user_update_time = time();
            $this->mgr_name_update = $this->getMgrName();
        }

        return true;
    }

    /**
     * 组装开户或者编辑用户产品
     * @param $isNew
     * @return bool
     */
    public function getUserProduct($isNew)
    {
        //组装开户或者编辑用户产品
        if ($this->products_id) {
            if (!$isNew) {
                $changeModel = new ProductsChange();
            }
            foreach ($this->products_id as $key => $value) {
                //如果value是数组，那么是
                if (is_array($value)) {
                    //如果本产品勾选了
                    if (isset($value['open']) && $value['open'] == 1) {
                        //判断订购的新产品是否在下个产品日志里，如果存在，则不能订购
                        if (!$isNew && $changeModel->isExistNextProduct($this->user_id, $key)) {
                            $product_desc = !isset($this->can_product[$key]) ? 'id=' . $key : $key . ':' . $this->can_product[$key];
                            Yii::$app->getSession()->setFlash('error', Yii::t('app', 'change product help2', ['product_desc' => $product_desc]));
                            continue;
                        }
                        $products_id[] = $key;
                        //如果金额正常，那么放到缴费的数组
                        if (isset($value['num']) && is_numeric($value['num'])) {
                            $this->products_pay[$key] = $value['num'];
                        }
                        //如果订购套餐
                        if (isset($value['packages']) && !empty($value['packages'])) {
                            $this->products_package[$key] = $value['packages'];
                        }
                    }
                } else {
                    $products_id[] = $value;
                }
            }
            $this->products_id = $products_id;
        }

        return true;
    }

    /**
     * 设置密码
     * @return bool
     */
    public function setPassword()
    {
        //将核心数据发送到接口
        //导入数据有可能没有明文，可以按密文字段直接保存密文
        if (!$this->user_password) {
            if (strlen($this->user_password_md5) == 32)//标准的MD5是32位的
            {
                $this->user_password = "{MD5_HEX}" . $this->user_password_md5;
            } else if (strlen($this->user_password_md5) == 16)//3K中的MD5是16位的
            {
                $this->user_password = "{MD5_SRUN}" . $this->user_password_md5;
            }
        }

        if (!empty($this->mobile_phone) && $this->mobile_is_text)//使用明文保存
        {
            $this->mobile_phone = "{T}" . $this->mobile_phone;
        }

        return true;
    }

    /**
     * 保存用户需保存一致性，续费等等，任何一部失败都需要回滚
     * @param bool|true $runValidation
     * @param null $attributeNames
     * @param integer $money
     * @param bool $isNew
     * @return bool
     */
    public function saveUser($runValidation = true, $attributeNames = null, $money = 0, $isNew = true)
    {
        //更新到数据库
        $res = parent::save($runValidation, $attributeNames);
        if (!$res) {
            return false;
        }
        $this->setPassword(); //设置密码
        $array = [
            "user_name" => $this->user_name,
            "serial_code" => time() . rand(111111, 999999), //唯一的流水号
            "time" => time(),
            'proc' => 'admin',
            "user_password" => $this->user_password,
            "user_available" => $this->user_available, //状态
            "products_id" => $this->products_id ? implode(',', $this->products_id) : 0,//订购的产品
            "mobile_phone" => $this->mobile_phone ? $this->mobile_phone : '',
            "mobile_password" => $this->mobile_password ? $this->mobile_password : '',
            "max_online_num" => $this->max_online_num,//最大在线人数
            "mac_auth" => $this->mac_auth,
            "group_id" => $this->group_id,
            'interface_status' => $this->interface_status,
        ];

        //新增数据
        if ($isNew) {
            $array['action'] = 1; //接口队列中添加用户的标识符
            $array['user_id'] = $this->user_id;
        } //编辑数据
        else {
            $array['action'] = 2; //接口队列中编辑用户的标识符
            if (empty($this->user_password)) {
                unset($array['user_password']);
            }
            if (empty($this->mobile_phone))//手机号为空时，清空手机密码
            {
                $this->mobile_password = "";
            }
        }
        $json = Json::encode($array);
        //电子钱包
        if (!empty($this->balance)) {
            $this->payModel->userModel = $this;
            if ($isNew) {
                $this->payModel->payToBalance($this->balance, $isNew);
            } else {
                $this->payModel->payToBalance($money, $isNew);
            }
            $this->extMsg .= $this->payModel->getPayMessage();
            $this->payModel->message_err = [];
            $this->payModel->message_ok = [];
        }
        Redis::executeCommand('RPUSH', "list:interface", [$json]);

        //添加cdr
        if ($this->interface_name) {
            KernelInterface::CDRBind($this->user_name, $this->interface_name);
        }
        //产品缴费
        if ($this->products_pay && $this->payType) {

            $payItem = [
                'productPay' => $this->products_pay
            ];

            $this->payModel->payByUser($this->user_name, $payItem, $this->payType);
            $this->extMsg .= $this->payModel->getPayMessage();
            $this->payModel->message_err = [];
            $this->payModel->message_ok = [];
        }

        //订购套餐
        if ($this->products_package) {
            $buyPackageItem = [
                'buyPackage' => $this->products_package
            ];
            $this->payModel->payByUser($this->user_name, $buyPackageItem, $this->payType);
            $this->extMsg .= $this->payModel->getPayMessage();
        }

        return true;
    }


    /**
     * 批量开户
     * @param $users
     * @param bool|true $isSame
     * @param bool|false $isPart
     * @return array
     */
    public function batchSave($users, $isSame = true, $isPart = false)
    {
        $rs = $user_names = $excel_err = $excel_ok = [];
        $flag = true;
        // var_dump(1111,$users);exit;
        if (count($users) > 100) {
            //查询出所有用户name
            $flag = false;
            $userBases = self::find()->select('user_name')->indexBy('user_name')->asArray()->all();
            $user_names = array_keys($userBases);
        }
        $this->scenario = 'add';
        $nameArr = [];
        $this->payModel = new PayList();
        $i = 0;
        foreach ($users as $name => $val) {
            if ($flag) {
                if (self::find()->where(['user_name' => $name])->count() > 0) {
                    $excel_err[] = array($name, Yii::t('app', 'batch add help2'));
                    continue;
                }

            } else {
                if (in_array($name, $user_names)) {
                    $excel_err[] = array($name, Yii::t('app', 'batch add help2'));
                    continue;
                }

            }
            //开始处理单个用户
            $params['Users'] = $val;
            $this->payModel->message_err = [];
            $this->payModel->message_ok = [];
            $this->payModel->payTotalNum = 0;
            $this->oldAttributes = null;
            $this->user_id = null;
            if ($this->load($params)) {
                $this->extMsg = '';
                $isNew = $this->getIsNewRecord();
                $this->balance = (isset($val['balance']) && !empty($val['balance'])) ? $val['balance'] : 0;
                $this->user_password = $val['user_pass_value'];
                $this->getUserProduct($isNew); //获取产品
                if ($isSame) {
                    if (empty($nameArr)) {
                        $nameArr = $this->getName($this->products_id, $val['can_product']);
                        if ($isPart) {
                            $ip = (new IpPool())->getIp($this->group_id, $this->products_id);
                        }
                    }
                } else {

                }

                if ($i != 0) {
                    $this->user_create_time = $this->user_create_time ? strtotime($this->user_create_time) : time();
                    $this->user_update_time = $this->user_update_time ? strtotime($this->user_update_time) : time();
                }

                if ($ip) {
                    //绑定ip
                    $data = [
                        'operation' => 1,
                        'user_name' => $this->user_name,
                        'value' => $ip,
                        'type' => 5
                    ];
                    $res = KernelInterface::userBind($data);
                    if ($res) {
                        $this->bindIp = $ip;
                    }
                }
                $this->saveUser(false, null, $this->balance, $isNew);
                $ok = [
                    $this->user_id,
                    $this->user_name,
                    $this->user_password,
                    implode(',', $nameArr),
                    $this->balance,
                    date('Y-m-d H:i:s'),
                    $this->user_expire_time != 0 ? date('Y-m-d', $this->user_expire_time) : Yii::t('app', 'user expire time2'),
                    $this->attributesList['user_available'][$this->user_available],
                    Yii::$app->user->identity->username,
                ];
                if ($isPart && $this->bindIp) {
                    $ok[] = $this->bindIp;
                }
                $ok[] = $this->extMsg;
                $excel_ok[] = $ok;
            }
            $i++;
        }

        return ['excel_ok' => $excel_ok, 'excel_err' => $excel_err];

    }

    public function getName($products, $names)
    {
        $proNames = [];
        foreach ($products as $id) {
            $proNames[] = isset($names[$id]) ? $names[$id] : $id;
        }

        return $proNames;
    }

    /**
     * 改变用户的状态
     */
    private function changeAvailable()
    {
        //如果用户的状态更改为正常
        if ($this->user_available == 0) {
            if ($this->user_start_time != 0) {
                //如果开机时间大于当前时间，说明用户还处于停机保号的时间段内，需要处理结算日期
                if ($this->user_start_time > time()) {
                    /**
                     * 如果强制开启，那么需要计算用户的停用时间：user_start_time-今天 = 6天，这是用户还需要停用的天数，
                     * 将结算日期减去6天，则宿舍区产品的结算日期=2015-05-05 办公区=2015-05-24，
                     * 然后把用户的状态更改为正常，把user_start_time和user_stop_time置为0
                     */
                    $diffTimes = $this->user_start_time - time();
                    //修改用户产品的结算日期
                    $waitChecks = WaitCheck::findAll(['user_id' => $this->user_id]);
                    if ($waitChecks) {
                        foreach ($waitChecks as $one) {
                            $one->checkout_date -= $diffTimes;
                            $one->save();
                        }
                    }
                }
                $this->user_start_time = 0;
                $this->user_stop_time = 0;
                $this->save();
            } else {
                /**
                 * 如果不是停机保号的状态来开启，就去查找暂停表，暂停表如果有数据，把结算的时间往后延 暂停期间的这段时间
                 * 然后删除暂停记录，增加待结算记录
                 */
                //$this->checkPause($this->user_id, $this->products_id);
            }
        }
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        $this->changeAvailable();

        //写日志开始 获取脏数据
        $dirtyArr = LogWriter::dirtyData($this->_temOldAttr, $this->getCurrentData());
        if (!empty($dirtyArr)) {
            $logData = [
                'operator' => $this->getMgrName(),
                'target' => $this->user_name,
                'action' => $insert ? 'add' : 'edit',
                'action_type' => 'User Base',
                'content' => Json::encode($dirtyArr),
                'class' => __CLASS__,
                'type' => 0,
            ];
            LogWriter::write($logData);
        }
        //写日志结束

        //如果是新增记录
        if ($insert) {
            //开户明细开始
            $detailModel = new Detail();
            $detailModel->user_name = $this->user_name;
            $detailModel->user_real_name = $this->user_real_name ? $this->user_real_name : '';
            $detailModel->type = 0;
            $detailModel->detail = Json::encode($dirtyArr);
            $detailModel->mgr_name = $this->getMgrName();
            $detailModel->save();
            //开户明细结束
        }

        //如果更改状态就写消息通知用户
        if ($this->_temOldAttr['user_available'] != $this->user_available && !$insert) {
            $statusArr = $this->getAttributesList()['user_available'];
            $old_status = isset($statusArr[$this->_temOldAttr['user_available']]) ? $statusArr[$this->_temOldAttr['user_available']] : '';
            $new_status = isset($statusArr[$this->user_available]) ? $statusArr[$this->user_available] : '';
            $this->updateAvailNotice($this->user_name, $old_status, $new_status);
        }
        //如果开户就写消息通知用户
        if ($insert) {
            $this->addUserNotice($this->user_name, $this->user_password);
        }
        //添加用户后添加防火墙的对象
        if ($insert && $this->interface_name) {
            $feitaClass = new Feita();
            $feita_config = Yii::$app->params['feita_config'];
            //获取token
            list($token, $cookie) = $feitaClass->getToken($feita_config['token_url'], $feita_config['username'], $feita_config['secretKey']);
            $feitaClass->addObject($this->user_name, $this->interface_name, $feita_config['add_object_url'], $token, $cookie);
        }
    }

    /**
     * 获取当前的日志需要记录的值
     * @return array
     */
    public function getCurrentData()
    {
        //获取扩展字段
        $extField = [];
        if ($this->user_extends) {
            foreach ($this->user_extends as $one) {
                $extField[] = $one['field_name'];
            }
        }

        /**
         * 要记录日志的普通字段，数据表字段以及扩展字段都在监控字段内。
         * 并非所有字段都需要记录，比如更新时间、创建人不需要记录，操作日志的作用是便于管理员排错，记录必要的信息即可。
         */
        $normalField = yii\helpers\ArrayHelper::merge([
            'user_id', 'user_name', 'user_real_name', 'group_id', 'user_allow_chgpass', 'balance', 'user_available',
        ], $extField);

        /**
         * 记录在日志中需要特殊处理的字段，比如有需要记录的日期（过期时间），密码，产品id和名称
         */
        //记录用户组的形式为：id：路径名称，比如：102：百度 / 知道
        $group_str = $this->group_id ? $this->group_id . ': ' . SrunJiegou::getOwnParent($this->group_id) : '';
        //记录产品的形式为：产品id: 产品名称,产品id: 产品名称...，比如： 1: 产品1,2: 产品2,...
        $product_desc = [];
        if ($this->products_id) {
            $productModel = new Product();
            foreach ($this->products_id as $pid) {
                $product_desc[] = $pid . ': ' . $productModel->getOneName($pid);
            }
        }
        $specialField = [
            'user_available' => $this->user_available,
            'user_start_time' => $this->user_start_time > 0 ? date('Y-m-d H:i:s', $this->user_start_time) : 0,
            'user_stop_time' => $this->user_stop_time > 0 ? date('Y-m-d H:i:s', $this->user_stop_time) : 0,
            'user_expire_time' => empty($this->user_expire_time) ? '0' : (is_int($this->user_expire_time) ? date('Y-m-d H:i', $this->user_expire_time) : $this->user_expire_time),
            'user_password' => $this->user_password ? '******' : '',
            'group_id' => $group_str,
            'products_id' => $product_desc ? implode(', ', $product_desc) : '',
        ];
        //var_dump($this->hasAttribute('user_available'));
        $list = [];
        //给普通字段赋值
        foreach ($normalField as $field) {
            if ($this->hasAttribute($field)) {
                $list[$field] = $this->$field;
            }
        }
        //返回所有需要记录的字段值
        return yii\helpers\ArrayHelper::merge($list, $specialField);
    }

    /**
     * 开户发送消息
     * @param $user_name
     * @param $password
     * @return mixed
     */
    public function addUserNotice($user_name, $password)
    {
        $data = [
            'event_source' => SRUN_MGR,
            'event_type' => 'user_regist',
            '{ACCOUNT}' => $user_name,
            '{PASSWORD}' => $password,
        ];
        $data = json_encode($data);

        return Redis::executeCommand('RPUSH', 'list:message:main:events', [$data], 'redis_online');
    }

    /**
     * 绑定的类型
     * @return array
     */
    public function getBindType()
    {
        return [
            'mac_auths' => 1,
            'macs' => 2,
            'nas_port_ids' => 3,
            'vlan_ids' => 4,
            'ips' => 5,
        ];
    }

    public function batchLog($target, $logContent)
    {
        //写日志
        $logData = [
            'operator' => $this->getMgrName(),
            'target' => $target,
            'action' => 'delete',
            'action_type' => 'User Base',
            'content' => $logContent,
            'class' => get_class($this),
            'type' => 1
        ];
        return LogWriter::write($logData);
    }

    /**
     * 用户是否存在
     * @param $id
     * @return null|static
     */
    public function userIsExist($id)
    {
        $model = parent::findOne($id);
        return $model;
    }

    /**
     *  获取用户订购的产品信息，仅返回可以管理的已订购产品
     * @param $products
     * @param $user_id
     * @return array
     */
    public function getOrderedProductDetail($products, $user_id)
    {
        $arr = [];
        foreach ($products  as $id) {
            //判断产品是否可以管理，如果不能管理，跳过
            if (!array_key_exists($id, $this->products)) {
                continue;
            }

            //获取产品的详情
            $product = $this->products[$id];

            if ($product) {
                //获取此用户此产品的结算日期
                $waitCheckModel = WaitCheck::findOne(['user_id' => $user_id, 'products_id' => $id]);
                if ($waitCheckModel) {
                    $product['checkout_date'] = date('Y-m-d H:i:s', $waitCheckModel->checkout_date);
                }

                //获取此产品已经使用的数据
                if ($oneProduct = $this->getOneOrderedProduct($id, $user_id)) {
                    //var_dump($oneProduct);exit;
                    $product['used'] = $oneProduct;
                }
                $arr[$id] = $product;
            }
        }

        return $arr;
    }

    /**
     * 获取一个订购的产品的数据
     * @param $id
     * @param $user_id
     * @return array
     */
    public function getOneOrderedProduct($id, $user_id)
    {
        $product = [];
        //获取此产品已经使用的数据
        $usedHash = Redis::executeCommand('HGETALL', 'hash:users:products:' . $user_id . ":" . $id, []);
        if ($usedHash) {
            $product = Redis::hashToArray($usedHash);
            $product['user_balance'] = number_format(isset($product['user_balance']) ? floor($product['user_balance'] * 10000) / 10000 : 0, 2);
        }
        return $product;
    }

}