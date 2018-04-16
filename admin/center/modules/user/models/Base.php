<?php
namespace center\modules\user\models;

use center\models\Pagination;
use center\modules\auth\models\SrunJiegou;
use center\modules\Core\models\BaseActiveRecord;
use center\modules\Core\models\UserBase;
use center\modules\financial\models\Bills;
use center\modules\financial\models\CheckoutList;
use center\modules\financial\models\Pause;
use center\modules\financial\models\PayList;
use center\modules\financial\models\PayType;
use center\modules\financial\models\WaitCheck;
use center\modules\log\models\LogWriter;
use center\modules\setting\models\ExtendsField;
use center\modules\strategy\models\Condition;
use center\modules\strategy\models\IpPool;
use center\modules\strategy\models\Package;
use center\modules\strategy\models\Product;
use center\modules\strategy\models\ProductsChange;
use center\modules\strategy\models\Recharge;
use common\extend\Excel;
use common\models\Feita;
use common\models\FileOperate;
use common\models\KernelInterface;
use common\models\Redis;
use common\models\User;
use center\modules\financial\models\TransferBalance;
use yii;
use yii\helpers\Json;
use yii\helpers\Url;
use common\extend\Export\CsvExport;
use common\extend\Export\DataType\ArrayType;
use center\modules\report\models\SrunDetailDay;

/**
 * Class Base
 * @package center\modules\user\models
 * @property string $user_name 账号
 */
class Base extends UserBase
{
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

    /**
     * 初始化
     */
    public function init()
    {
        $this->default_type = $this->payType = PayType::getDefaultType();
        $this->package_list = (new Package())->getList();

        parent::init();
    }


    public static function tableName()
    {
        return 'users';
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

    //搜索字段
    private $_searchField = null;

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

    public function setSearchField($data)
    {
        $this->_searchField = $data;
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
            $changeModel = new ProductsChange();
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
     * @return bool
     */
    public function saveUser($runValidation = true, $attributeNames = null, $money = 0)
    {
        $isNew = $this->getIsNewRecord();
        $action = $isNew ? 'add' : 'edit';
        $trans = Yii::$app->db->beginTransaction();
        $this->payModel = new PayList();
        try {
            $this->getUserProduct($isNew); //获取产品
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

            if (!empty($this->balance) || $money) {
                $this->payModel->userModel = $this;
                if ($isNew) {
                    $this->payModel->payToBalance($this->balance, $isNew);
                } else {
                    $this->payModel->payToBalance($money, $isNew);
                }

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
            }

            //订购套餐
            if ($this->products_package) {
                if (!$this->payModel) {
                    $this->payModel = new PayList();
                }
                $buyPackageItem = [
                    'buyPackage' => $this->products_package
                ];
                $this->payModel->payByUser($this->user_name, $buyPackageItem, $this->payType);
            }

            //添加用户自动分配ip池的ip
            if ($isNew) {
                $ip = (new IpPool())->getIp($this->group_id, $this->products_id);
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
                        $this->message[] = Yii::t('app', 'user base help42', ['ip' => $ip]);
                    }
                }
            }

            $trans->commit();
        } catch (\Exception $e) {
            $trans->rollBack();
            //写入异常信息表定期查看
            $msg = "ERROR:" . ($isNew ? '开户发生异常: ' . $e->getMessage() : '编辑用户发生异常:' . $e->getMessage());
            $this->writeMessage($action, $msg);
            return false;
        }

        return true;
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
        //如果用户的状态更改为暂停
        /*if($this->user_available==3){
            $this->setPause($this->user_id, $this->products_id);
        }*/
    }

    /**
     * 检查开启正常是 原来是否暂停，暂停的话延迟增加待结算记录
     * @param $user_id
     * @param array $products_id
     * @return bool
     */
    public function checkPause($user_id, $products_id = [])
    {
        if (!$user_id || empty($products_id)) {
            return;
        }
        $wait = new WaitCheck();
        foreach ($products_id as $pid) {
            $pause = Pause::findOne(['user_id' => $user_id, 'products_id' => $pid]);
            if ($pause) {
                $day = time() - $pause->create_time;
                if (($day / 3600) < 1) {
                    $day = 0;//暂停一小时之内 不延迟结算时间
                }
                $data = $pause->toArray();
                $data['checkout_date'] += $day;
                $data['balance_start'] += $day;
                if ($wait->addWaitCheck($data)) {
                    $pause->delete();
                }
            }
        }
        return true;
    }

    /**
     * 暂停的话要删除 待结算记录 然后把待结算记录的数据 写入暂停表 然后记录下当前时间
     * @param $user_id
     * @param array $products
     * @return bool
     */
    public function setPause($user_id, $products = [])
    {
        if (!$user_id || empty($products)) {
            return;
        }
        $pause = new Pause();
        foreach ($products as $pid) {
            $wait = WaitCheck::findOne(['user_id' => $user_id, 'products_id' => $pid]);
            if (!$wait) {
                continue;
            }
            $data = $wait->toArray();
            if ($pause->addPauseCheck($data)) {
                $wait->delete();
            }
        }
        return true;
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

        //保存开户模板
        if ($insert && $this->saveTem == 1) {
            $temModel = new Template();
            $temModel->name = $this->temName;
            $temModel->create = $this->getMgrName();
            $temModel->type = Yii::$app->user->can('user/base/_addCommonTemplate') ? $this->commonTem : 0;
            //获取模板内容
            //将列表形式的数据保存下来
            $arr = [];
            $attributes = $this->getAttributesList();
            foreach ($attributes as $k => $v) {
                if ($this->hasAttribute($k)) {
                    $arr[$k] = $this->$k;
                }
            }
            $temModel->content = yii\helpers\ArrayHelper::merge([
                'user_allow_chgpass' => $this->user_allow_chgpass,
                'user_available' => $this->user_available,
                'user_expire_time' => $this->user_expire_time,
                'products_id' => $this->products_id,
                'group_id' => $this->group_id,
            ], $arr);
            $temModel->save(null, null);
        }

        //如果更改状态就写消息通知用户
        if ($this->_temOldAttr['user_available'] != $this->user_available) {
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
            //'products_id' => $this->products_id ? implode(',', $this->products_id) : '',
            //'products_name' => $this->products_id ? implode(',', (new Product())->getNameArr($this->products_id)) : '',
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

    public function afterDelete()
    {
        parent::afterDelete();

        //写日志开始
        $dirtyArr = LogWriter::dirtyData(null, $this->_temOldAttr);
        $data = [
            'operator' => $this->getMgrName(),
            'target' => $this->user_name,
            'action' => 'delete',
            'action_type' => 'User Base',
            'content' => Json::encode($dirtyArr),
            'class' => __CLASS__,
        ];
        LogWriter::write($data);
        //写日志结束

        //销户明细开始
        $detailModel = new Detail();
        if ($this->getMgrName() == 'SYSTEM-CRON') {
            $detailModel->operate_ip = '127.0.0.1';
        }
        $detailModel->user_name = $this->user_name;
        $detailModel->user_real_name = $this->user_real_name ? $this->user_real_name : '';
        $detailModel->type = 1;
        $detailModel->detail = Json::encode($this->_temOldAttr);
        $detailModel->mgr_name = $this->getMgrName();
        $detailModel->save();
        //销户明细结束
        //销户后更新 用户列表list:users  产品使用人数list:products:产品id
        $uid = $this->_temOldAttr['user_id'];
        $products = explode(',', $this->_temOldAttr['products_id']);
        Redis::executeCommand('lrem', 'list:users', [0, $uid]);
        if ($products) {
            foreach ($products as $one) {
                $pid = explode(':', $one)[0];
                Redis::executeCommand('lrem', 'list:products:' . $pid, [0, $uid]);
            }
        }

        //销户后ip池回收ip
        $ips = Redis::executeCommand('LRANGE', 'list:users:ip:' . $this->user_name, [0, -1]);
        if ($ips) {
            foreach ($ips as $ip) {
                $data = [
                    'operation' => 2,
                    'user_name' => $this->user_name,
                    'value' => $ip,
                    'type' => 5
                ];
                KernelInterface::userBind($data);
            }
        }
    }

    /**
     * 要搜索的字段
     * @return array
     */
    public function getSearchInput()
    {
        //扩展字段加入搜索
        $exField = [];
        $attributes = $this->getAttributesList();
        if ($this->user_extends) {
            foreach ($this->user_extends as $one) {
                if ($one['can_search'] == 1) {
                    $list = [];
                    if ($one['type'] == 1) {
                        $list = isset($attributes[$one['field_name']]) ? $attributes[$one['field_name']] : [];
                        if (!empty($list)) {
                            $arr1 = ['' => $one['field_desc']];
                            $list = $arr1 + $list;
                        }
                    }

                    $exField[$one['field_name']] = [
                        'label' => $one['field_desc'],
                        'list' => $list,
                    ];
                }
            }
        }
        $productList = [Yii::t('app', 'select product')] + $this->can_product;

        return yii\helpers\ArrayHelper::merge([
            'user_name' => [
                'label' => Yii::t('app', 'account')
            ],
            'user_real_name' => [
                'label' => Yii::t('app', 'name')
            ],
            'products_id' => [
                'label' => Yii::t('app', 'product'),
                'list' => $productList,
            ],
            'user_available' => [
                'label' => Yii::t('app', 'user available'),
                'list' => [
                    '' => Yii::t('app', 'user available'),
                    '0' => Yii::t('app', 'user available0'),
                    '1' => Yii::t('app', 'user available1'),
                    '2' => Yii::t('app', 'user available3'),
                    '3' => Yii::t('app', 'user available4'),
                    '4' => Yii::t('app', 'user available5'),
                ]

            ],
            'user_create_time_start' => [
                'label' => Yii::t('app', 'user_create_time_start'),
                'class' => ' inputDate',
            ],
            'user_create_time_end' => [
                'label' => Yii::t('app', 'user_create_time_end'),
                'class' => ' inputDate',
            ],
            'user_expire_time_start' => [
                'label' => Yii::t('app', 'user_expire_time_start'),
                'class' => ' inputDate',
            ],
            'user_expire_time_end' => [
                'label' => Yii::t('app', 'user_expire_time_end'),
                'class' => ' inputDate',
            ],
            'mac' => [
                'label' => Yii::t('app', 'mac_auths'),
            ],
        ], $exField);
    }

    /**
     * 停机保号功能 @todo 定时脚本 @todo 将写入结算表的搞成一个进程
     * @param $type string 类型：days,months,years
     * @param $num integer 停机时长数量
     * @param $money number 金额
     * @return bool
     */
    public function stopToProtect($type, $num, $money)
    {
        //用户状态本身就是禁用 是否可以继续停用？应该是可以，用户需要继续多停机一段时间
        /*if($this->user_available){
            return false;
        }*/
        //余额不足以停机的金额
        if ($this->balance < $money) {
            return false;
        }
        //判断类型是否正确
        if (!in_array($type, ['days', 'months', 'years'])) {
            return false;
        }

        //处理停机保号逻辑
        $this->user_available = 2;
        //如果停机时间不为0，那么停机时间保留，否则从今天算起
        $this->user_stop_time = $this->user_stop_time > 0 ? $this->user_stop_time : time();
        //如果开始时间不为0，那么在现user_start_time的基础上加
        if ($this->user_start_time) {
            $this->user_start_time = strtotime(date('Y-m-d H:i:s', $this->user_start_time) . ' +' . $num . ' ' . $type);
        } //如果开始时间=0，那么从今天算起
        else {
            $this->user_start_time = strtotime('+' . $num . ' ' . $type);
        }
        $this->balance -= $money;
        $this->save();

        //如果金额大于0，那么写入结算表
        if ($money > 0) {
            //写结算队列
            $checkout_list = [
                'user_name' => $this->user_name,
                'products_id' => 0,
                'checkout_amount' => $money,
                'user_balance' => $this->balance,
                'sum_bytes' => 0,
                'sum_seconds' => 0,
                'sum_bytes6' => 0,
                'sum_seconds6' => 0,
                'type' => CheckoutList::CHECKOUT_OTH, //停机保号
            ];
            KernelInterface::addCheckoutedList($checkout_list);
        }

        //修改用户产品的结算日期
        $waitChecks = WaitCheck::findAll(['user_id' => $this->user_id]);
        if ($waitChecks) {
            foreach ($waitChecks as $one) {
                $one->checkout_date = strtotime(date('Y-m-d H:i:s', $one->checkout_date) . ' +' . $num . ' ' . $type);
                $one->save();
            }
        }

        //dm下线
        $radius_model = new OnlineRadius();
        $radius_model->radiusDrop($this->user_name);

        return true;
    }

    /**
     * 取消产品
     * @param $id
     * @return bool
     */
    public function cancelProduct($id)
    {
        $product = isset($this->products[$id]) ? $this->products[$id] : '';
        if (!$product) {
            return false;
        } else {
            //获取此产品的数据
            $productUsed = $this->getOneOrderedProduct($id, $this->user_id);

            if ($productUsed) {
                //1,结算用户产品
                $waitModel = new WaitCheck();
                //用户当前余额，产品消费额，产品余额
                $user_balance = $this->balance;
                $product_balance = isset($productUsed['user_balance']) ? $productUsed['user_balance'] : 0;
                $checkout_info = $waitModel->checkoutParams($this->user_name, $id);

                $product_charge = $checkout_info['checkout_amount'];
                //新余额= 当前用户余额 + 产品余额 - 产品消费额
                $newUserBalance = $this->balance + $product_balance - $product_charge;


                //写结算队列
                $checkout_list = [
                    'user_name' => $this->user_name,
                    'products_id' => $id,
                    'checkout_amount' => $product_charge,
                    'user_balance' => $newUserBalance,
                    'sum_bytes' => $productUsed['sum_bytes'],
                    'sum_seconds' => $productUsed['sum_seconds'],
                    'sum_bytes6' => 0,
                    'sum_seconds6' => 0,
                    'group_id' => $this->group_id,
                ];
                $checkout_res = KernelInterface::addCheckoutedList($checkout_list);
                if (!$checkout_res) {
                    return false;
                }

                //2,把产品剩余费用返回到用户余额
                if ($this->balance != $newUserBalance) {
                    $this->balance = $newUserBalance;
                    $this->save();
                }

                //3,给接口发送取消产品的命令
                //KernelInterface::cancelProduct($this->user_name, $id);//修改用户的产品来进行取消产品
                $key = array_search($id, $this->products_id);
                if ($key !== false) {
                    unset($this->products_id[$key]);
                    $this->save(false);
                }

                //4,dm下线
                $radius_model = new OnlineRadius();
                $radius_model->radiusDrop($this->user_name);

                //写入转账记录表
                $transfer_balance = new TransferBalance();
                $transferData = [
                    'transfer_num' => $product_balance - $product_charge,
                    'user_name_from' => $this->user_name,
                    'user_name_to' => $this->user_name,
                    'type' => 1,
                    'product_id' => $id,
                    'create_at' => time(),
                    'mgr_name' => Yii::$app->user->identity->username,
                    'group_id' => $this->group_id,
                    'group_id_from' => $this->group_id,
                ];

                if (!$transfer_balance->insertData($transferData)) {
                    return false;
                }
            } else {
                //产品实例不存在，那么把list里的记录删除
                Redis::executeCommand('lrem', 'list:users:products:' . $this->user_id, [0, $id]);
                $user_balance = $this->balance;
                $product_balance = 0; //产品原余额
                $product_charge = 0; //产品消费额
                $newUserBalance = $this->balance; //用户新余额
            }
        }

        //5，写日志
        //admin 取消了 用户[user1]的产品[1:包月30元]，当前账户余额20元，产品余额50元，产品消费额30元，产品剩余余额20元已返回到用户余额，用户新余额40元。
        $logContent = Yii::t('app', 'user base help15', [
            'mgr' => $this->getMgrName(), //管理员
            'user_name' => $this->user_name, //用户名
            'balance' => $user_balance, //取消前余额
            'product' => $id . ':' . (isset($product['products_name']) ? $product['products_name'] : ''), //产品名称
            'product_balance' => $product_balance, //产品原余额
            'product_charge' => $product_charge, //产品消费额
            'new_balance' => $newUserBalance, //用户新余额
            'new_product_balance' => $product_balance - $product_charge, //产品新余额
        ]);
        //写日志开始
        $data = [
            'operator' => $this->getMgrName(),
            'target' => $this->user_name,
            'action' => 'cancelProduct',
            'action_type' => 'User Base',
            'content' => $logContent,
            'class' => __CLASS__,
            'type' => 1, //描述日志
        ];
        LogWriter::write($data);
        //写日志结束

        //6.写消息来通知用户
        if (isset($product['products_name']) && !empty($product['products_name'])) {
            ProductsChange::changeProNotice($this->user_name, $product['products_name'], Yii::t('app', 'action cancelProduct'), Yii::t('app', 'effect now'));
        }

        //写产品余额流水
        $billsData = [
            'user_name' => $this->user_name,
            'target_id' => $id,
            'change_amount' => $product_balance,
            'before_amount' => $product_balance,
            'before_balance' => $user_balance,
        ];
        $this->on(Bills::PRODUCT_CANCEL, ['center\modules\financial\models\Bills', 'billsRecord'], $billsData);
        $this->trigger(Bills::PRODUCT_CANCEL);
        $this->off(Bills::PRODUCT_CANCEL);

        //写电子钱包余额流水
        $billsData = [
            'user_name' => $this->user_name,
            'target_id' => $this->user_id,
            'change_amount' => $product_balance - $product_charge,
            'before_amount' => $user_balance,
            'before_balance' => $user_balance,
            'remark' => Bills::PRODUCT_CANCEL
        ];
        $this->on(Bills::WALLET_RECHARGE, ['center\modules\financial\models\Bills', 'billsRecord'], $billsData);
        $this->trigger(Bills::WALLET_RECHARGE);
        $this->off(Bills::WALLET_RECHARGE);

        return true;
    }

    /**
     * 组装用户搜索页数据
     * @param $list
     * @param $productShow
     * @param $searchProduct
     * @param bool|false $statusPos
     * @param bool|false $pos
     * @return mixed
     */
    public function getList($list, $productShow, $searchProduct, $statusPos = false, $pos = false)
    {

        if ($list) {
            foreach ($list as $key => $value) {
                //用户订购的产品列表
                if ($pos || $searchProduct || $productShow) {
                    //查询产品或者查询产品余额
                    $products = $this->getProductByName($value['user_id'], false);
                    //如果搜索产品
                    if ($searchProduct) {
                        if ($productShow) {
                            $list[$key]['products_id'] = [];
                            $list[$key]['products_id'][] = isset($products[$value['products_id']]) ? $products[$value['products_id']] : '';
                        }
                        if ($pos !== false) {
                            $list[$key]['user_balance'] = [];
                            $list[$key]['user_balance'][] = $this->getProductBalance($value['user_id'], $value['products_id']);
                        }
                    } else {
                        if ($products) {
                            $list[$key]['products_id'] = [];

                            //可以管理的并且是已订购的产品列表
                            $list[$key]['user_balance'] = [];
                            foreach ($products as $k => $v) {
                                $detail = $this->getOneOrderedProduct($k, $value['user_id']);
                                $balance = $detail['user_balance'];
                                $list[$key]['products_id'][] = $v;
                                $list[$key]['user_balance'][] = $balance;
                            }
                        }
                    }
                }

                if ($statusPos) {
                    //显示用户在线状态
                    $k = "set:rad_online:user_name:" . $value['user_name'];
                    $flag = Redis::executeCommand('exists', $k, [], 'redis_online');
                    $list[$key]['user_online_status'] = $flag;
                }
            }
        }

        return $list;
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

    /**
     * 获取绑定的数组
     * @return array
     */
    public function getBind()
    {
        //是否mac认证,1关闭 0开启
        $no_mac_auth = Redis::executeCommand('HGET', 'hash:users:' . $this->redis_uid, ['no_mac_auth']);
        //mac认证
        $mac_auths = Redis::executeCommand('LRANGE', 'list:users:mac_auth:' . $this->user_name, [0, -1]);
        if ($mac_auths) {
            foreach ($mac_auths as $key => $mac) {
                if (!empty($mac)) {
                    $os_name_json = Redis::executeCommand('get', 'key:os_name:' . $mac, [], 'redis_manage');
                    $os_name_info = $os_name_json ? json_decode($os_name_json, true) : '';
                    $mac_auths[$key] = isset($os_name_info['os_name']) ? $mac . '(' . $os_name_info['os_name'] . ')' : $mac;
                } else {
                    Redis::executeCommand('LREM', 'list:users:mac:' . $this->user_name, [0, '']);
                    unset($mac_auths[$key]);
                }
            }
        }
        //mac绑定
        $macs = Redis::executeCommand('LRANGE', 'list:users:mac:' . $this->user_name, [0, -1]);
        if ($macs) {
            foreach ($macs as $key => $mac) {
                if (!empty($mac)) {
                    $os_name_json = Redis::executeCommand('get', 'key:os_name:' . $mac, [], 'redis_manage');
                    $os_name_info = $os_name_json ? json_decode($os_name_json, true) : '';
                    $macs[$key] = isset($os_name_info['os_name']) ? $mac . '(' . $os_name_info['os_name'] . ')' : $mac;
                } else {
                    Redis::executeCommand('LREM', 'list:users:mac:' . $this->user_name, [0, '']);
                    unset($macs[$key]);
                }
            }
        }

        //NasPortID绑定
        $nas_port_ids = Redis::executeCommand('LRANGE', 'list:users:nas_port_id:' . $this->user_name, [0, -1]);
        //VlanID绑定
        $vlan_ids = Redis::executeCommand('LRANGE', 'list:users:vlan_id:' . $this->user_name, [0, -1]);
        //IPV4绑定
        $ips = Redis::executeCommand('LRANGE', 'list:users:ip:' . $this->user_name, [0, -1]);

        return [
            'no_mac_auth' => $no_mac_auth,
            'mac_auths' => $mac_auths,
            'macs' => $macs,
            'nas_port_ids' => $nas_port_ids,
            'vlan_ids' => $vlan_ids,
            'ips' => $ips,
        ];
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
     * 获取用户没有定义的产品列表
     * @return array
     */
    public function getUnOrderedProduct()
    {
        $productModel = new Product();
        $productList = $productModel->getValidList();
        if ($this->products_id) {
            foreach ($this->products_id as $pid) {
                if (array_key_exists($pid, $productList)) {
                    unset($productList[$pid]);
                }
            }
        }
        return $productList;
    }

    /**
     * 取用户第一个产品的包月费，如果不是包月产品则为0
     * @param $orderProducts
     * @param $user_name
     * @return int
     */
    public function fistProFee($orderProducts, $user_name)
    {
        $pid = $this->getFirstProductId($orderProducts);
        if (empty($pid)) {
            return false;
        }
        //默认折扣为1，不虚拟下线
        $checkout = new WaitCheck();
        $parses = $checkout->checkoutParams($user_name, $pid, 1, 0);
        $fee = $parses['checkout_mode'] ? $parses['checkout_amount'] : 0;

        return $fee ? $fee : 0;
    }

    /**
     * 获取第一个订购产品的产品id
     * @param $orderProducts
     * @return int|string
     */
    public function getFirstProductId($orderProducts)
    {
        if ($orderProducts && is_array($orderProducts)) {
            foreach ($orderProducts as $pid => $v) {
                if ($pid) {
                    return $pid;
                }
            }
        }
    }

    /**
     * 获取一个订购的产品的数据(产品实例)
     * @param $uid
     * @param $id
     * @return array
     */
    public function getOneProductObj($uid, $id)
    {
        $product = [];
        //获取此产品已经使用的数据
        $usedHash = Redis::executeCommand('HGETALL', 'hash:users:products:' . $uid . ":" . $id, []);
        if ($usedHash) {
            $product = Redis::hashToArray($usedHash);
            $product['user_balance'] = isset($product['user_balance']) ? floor($product['user_balance'] * 10000) / 10000 : 0;
        }
        return $product;
    }


    /**
     * 更新用户状态写消息通知用户
     * @param $user_name
     * @param $old_status
     * @param $new_status
     */
    public function updateAvailNotice($user_name, $old_status, $new_status)
    {
        $data = [
            'event_source' => SRUN_MGR,
            'event_type' => 'update_status',
            '{ACCOUNT}' => $user_name,
            '{OLD_STATUS}' => $old_status,
            '{NEW_STATUS}' => $new_status,
        ];
        $data = json_encode($data);
        Redis::executeCommand('RPUSH', 'list:message:main:events', [$data], 'redis_online');
    }

    /**
     * 取消产品不结算，不转余额，光取消
     * @param $id
     * @return bool
     */
    public function cancelProductOnly($id)
    {

        //获取此产品的数据
        $productUsed = $this->getOneOrderedProduct($id, $this->user_id);
        if (!$productUsed) {
            return false;
        }

        //获取一个产品的详细信息
        $product = isset($this->products[$id]) ? $this->products[$id] : '';
        if (!$product) {
            return false;
        }
        $waitModel = new WaitCheck();
        //用户当前余额，产品消费额，产品余额
        $user_balance = $this->balance;
        $product_balance = $productUsed['user_balance'];
        $checkout_info = $waitModel->checkoutParams($this->user_name, $id);
        $product_charge = $checkout_info['checkout_amount'];
        $newUserBalance = $this->balance;


        //1,给接口发送取消产品的命令
        //KernelInterface::cancelProduct($this->user_name, $id);
        $key = array_search($id, $this->products_id);
        if ($key !== false) {
            unset($this->products_id[$key]);
            $this->save(false);
        }

        //2,dm下线
        $radius_model = new OnlineRadius();
        $radius_model->radiusDrop($this->user_name);

        //3，写日志
        //admin 取消了 用户[user1]的产品[1:包月30元]，当前账户余额20元，产品余额50元，产品消费额30元，产品剩余余额20元已返回到用户余额，用户新余额40元。
        $logContent = Yii::t('app', 'user base help15', [
            'mgr' => $this->getMgrName(), //管理员
            'user_name' => $this->user_name, //用户名
            'balance' => $user_balance, //取消前余额
            'product' => $id . ':' . (isset($product['products_name']) ? $product['products_name'] : ''), //产品名称
            'product_balance' => $product_balance, //产品原余额
            'product_charge' => $product_charge, //产品消费额
            'new_balance' => $newUserBalance, //用户新余额
            'new_product_balance' => 0, //产品新余额
        ]);
        //写日志开始
        $data = [
            'operator' => $this->getMgrName(),
            'target' => $this->user_name,
            'action' => 'cancelProduct',
            'action_type' => 'User Base',
            'content' => $logContent,
            'class' => __CLASS__,
            'type' => 1, //描述日志
        ];
        LogWriter::write($data);
        //写日志结束

        //4.写消息来通知用户
        if (isset($product['products_name']) && !empty($product['products_name'])) {
            ProductsChange::changeProNotice($this->user_name, $product['products_name'], Yii::t('app', 'action cancelProduct'), Yii::t('app', 'effect now'));
        }

        return true;
    }

    /**
     * 根据产品id获取用户
     * @param $product_id
     * @return array|yii\db\ActiveRecord[]
     */
    public function getUsersByPid($product_id)
    {
        $users = [];
        $productModel = new Product();
        $product = $productModel->getNameOfList();
        $proKey = array_keys($product);
        if (in_array($product_id, $proKey)) {
            $uids = Redis::executeCommand('LRANGE', $productModel->usedKey . $product_id, [0, -1]);
            if (!$uids) {
                return [];
            }
            $query = $this->find()->select(['user_id', 'user_name']);
            //如果非超级管理员，则需要去判断
            if (!$this->flag) {
                //判断组
                //所有可以管理的组
                $query->andWhere(['group_id' => array_keys($this->can_group)]);
            }
            $query->andWhere(['user_id' => $uids]);
            $users = $query->asArray()->all();
        }
        return $users;
    }

    /**
     * 批量销户
     * @param $users
     * @return string
     */
    public function batchDelete($users)
    {
        if ($users && is_array($users)) {
            $fail = Yii::t('app', 'failed');
            $success = Yii::t('app', 'success');
            $excelData = [
                0 => [
                    Yii::t('app', 'account'),
                    Yii::t('app', 'batch excel result'),
                    Yii::t('app', 'batch excel detail'),
                ]
            ];
            foreach ($users as $one) {
                $model = Users::findOne($one['user_id']);
                if (!$model) {
                    $excelData[] = [
                        $one['user_name'], $fail, Yii::t('app', 'batch excel help15')
                    ];
                    continue;
                }
                //判断组织结构
                if (!array_keys($model->group_id, $this->can_group)) {
                    $excelData[] = [
                        $one['user_name'], $fail, Yii::t('app', 'message 401 3')
                    ];
                    continue;
                }
                $balance = $model->balance;

                if ($balance == 0) {
                    //电子钱包为0再搜索产品余额是否大于周期费用
                    //可以管理的并且是已订购的产品列表
                    $orderedProductList = $model->getOrderedProductDetail($model->products_id, $model->user_id);

                    $whether = $this->getWhetherDelete($orderedProductList, $model->user_name);
                    if (is_bool($whether)) {
                        //销户
                        if ($whether) {
                            $res = $model->delete();
                            if (!empty($res)) {
                                $excelData[] = [$one['user_name'], $success, ''];
                            } else {
                                $excelData[] = [$one['user_name'], $fail, Yii::t('app', 'operate failed.')];
                            }
                        } else {
                            $excelData[] = [$one['user_name'], $fail, Yii::t('app', 'disable user error3')];
                        }
                    } else {
                        $excelData[] = [$one['user_name'], $fail, Yii::t('app', 'disable user error2', [
                            'proName' => $whether,
                            'user_name' => $model->user_name
                        ])];
                    }


                } else {
                    $excelData[] = [$one['user_name'], $fail, Yii::t('app', 'disable user error1', [
                        'user_name' => $model->user_name,
                    ])];
                    continue;
                }
            }


            $file = FileOperate::dir('account') . 'batch_delete' . '_' . date('YmdHis') . '.xls';
            $title = Yii::t('app', 'batch delete');
            //将内容写入excel文件
            Excel::arrayToExcel($excelData, $file, $title);
            //设置下载文件session
            Yii::$app->session->set('batch_excel_download_file', $file);
            return true;
        }
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
     * 批量启用/禁用
     * @param $users
     * @param $action
     * @return string
     */
    public function batchEnable($users, $action)
    {
        if ($users && is_array($users)) {
            $fail = Yii::t('app', 'failed');
            $success = Yii::t('app', 'success');
            $excelData = [
                0 => [
                    Yii::t('app', 'account'),
                    Yii::t('app', 'batch excel result'),
                    Yii::t('app', 'batch excel detail'),
                ]
            ];
            foreach ($users as $one) {
                $model = self::findOne($one['user_id']);
                if (!$model) {
                    $excelData[] = [
                        $one['user_name'], $fail, Yii::t('app', 'batch excel help15')
                    ];
                    continue;
                }
                //判断组织结构
                if (!array_key_exists($model->group_id, $model->can_group)) {
                    $excelData[] = [
                        $one['user_name'], $fail, Yii::t('app', 'message 401 3')
                    ];
                    continue;
                }
                //启用
                if ($action == 'batch enable') {
                    $model->user_available = 0;
                }
                //禁用
                if ($action == 'batch disable') {
                    $model->user_available = 1;
                }

                //停机保号
                if ($action == 'batch stop') {
                    $res = $model->stopToProtect('years', 3, 0);
                } else {
                    $res = $model->save();
                }


                if ($res) {
                    $excelData[] = [$one['user_name'], $success, ''];
                } else {
                    $excelData[] = [$one['user_name'], $fail, $model->getErrors()];
                }
            }


            $file = FileOperate::dir('account') . 'batch' . '_' . date('YmdHis') . '.xls';
            $title = Yii::t('app', $action);
            //将内容写入excel文件
            Excel::arrayToExcel($excelData, $file, $title);
            //设置下载文件session
            Yii::$app->session->set('batch_excel_download_file', $file);

            return true;
        }
    }

    /**
     * 批量开启/关闭Mac认证
     * @param $users
     * @param $action
     * @return string
     */
    public function batchMacOpen($users, $action)
    {
        if ($users && is_array($users)) {
            $fail = Yii::t('app', 'failed');
            $success = Yii::t('app', 'success');
            $excelData = [
                0 => [
                    Yii::t('app', 'account'),
                    Yii::t('app', 'batch excel result'),
                    Yii::t('app', 'batch excel detail'),
                ]
            ];
            foreach ($users as $one) {
                $model = self::findOne($one['user_id']);
                if (!$model) {
                    $excelData[] = [
                        $one['user_name'], $fail, Yii::t('app', 'batch excel help15')
                    ];
                    continue;
                }
                //判断组织结构
                if (!array_key_exists($model->group_id, $model->can_group)) {
                    $excelData[] = [
                        $one['user_name'], $fail, Yii::t('app', 'message 401 3')
                    ];
                    continue;
                }

                //启用mac认证
                if ($action == 'open') {
                    $res = KernelInterface::setMacAuth(['user_name' => $one['user_name'], 'value' => 1]);
                } else {
                    $res = KernelInterface::setMacAuth(['user_name' => $one['user_name'], 'value' => 0]);
                }

                if ($res) {
                    $excelData[] = [$one['user_name'], $success, ''];
                } else {
                    $excelData[] = [$one['user_name'], $fail, Yii::t('app', 'operate failed.')];
                }
            }


            $file = FileOperate::dir('account') . 'batch' . '_' . date('YmdHis') . '.xls';
            $title = Yii::t('app', $action);
            //将内容写入excel文件
            Excel::arrayToExcel($excelData, $file, $title);
            //设置下载文件session
            Yii::$app->session->set('batch_excel_download_file', $file);

            return true;
        }

    }

    /**
     * 批量清除绑定信息
     * @param $users
     * @param $action
     * @return bool
     */
    public function batchClearBind($users, $action)
    {
        if (!in_array($action, ['mac_auth', 'mac', 'nas_port_id', 'vlan_id', 'ip'])) {
            return false;
        }
        if ($users && is_array($users)) {
            $fail = Yii::t('app', 'failed');
            $success = Yii::t('app', 'success');
            $excelData = [
                0 => [
                    Yii::t('app', 'account'),
                    Yii::t('app', 'batch excel result'),
                    Yii::t('app', 'batch excel detail'),
                ]
            ];
            foreach ($users as $one) {
                $model = self::findOne($one['user_id']);
                if (!$model) {
                    $excelData[] = [
                        $one['user_name'], $fail, Yii::t('app', 'batch excel help15')
                    ];
                    continue;
                }
                //判断组织结构
                if (!array_key_exists($model->group_id, $model->can_group)) {
                    $excelData[] = [
                        $one['user_name'], $fail, Yii::t('app', 'message 401 3')
                    ];
                    continue;
                }
                if ($action == 'mac_auth') {
                    $bindInfo = Redis::executeCommand('LRANGE', 'list:users:mac_auth:' . $one['user_name'], [0, -1]);
                    $res = KernelInterface::clearBindInfo($one['user_name'], $action == 'mac_auth' ? 'mac_auth' : $action);
                } else {
                    $bindInfo = Redis::executeCommand('LRANGE', 'list:users:' . $action . ':' . $one['user_name'], [0, -1]);
                    $res = KernelInterface::clearBindInfo($one['user_name'], $action);
                }

                //清除绑定
                $res = KernelInterface::clearBindInfo($one['user_name'], $action == 'mac_auth' ? 'mac_auth' : $action);

                if ($res) {
                    $excelData[] = [$one['user_name'], $success, json_encode($bindInfo)];
                } else {
                    $excelData[] = [$one['user_name'], $fail, Yii::t('app', 'operate failed.')];
                }
            }


            $file = FileOperate::dir('account') . 'batch' . '_' . date('YmdHis') . '.xls';
            $title = Yii::t('app', $action);
            //将内容写入excel文件
            Excel::arrayToExcel($excelData, $file, $title);
            //设置下载文件session
            Yii::$app->session->set('batch_excel_download_file', $file);

            return true;
        }
    }

    /**
     * 同步是用到的字段
     * @return array
     */
    public function getSynFields()
    {
        $labels = [
            'user_name' => $this->attributeLabels()['user_name'],
            'user_real_name' => $this->attributeLabels()['user_real_name'],
            'user_password' => $this->attributeLabels()['user_password'],
        ];
        //扩展属性
        if ($this->user_extends) {
            foreach ($this->user_extends as $one) {
                $labels[$one['field_name']] = $one['field_desc'];
            }
        }

        unset($labels['user_type']);

        foreach ($labels as $field => $name) {
            $labels[$field] = $field . '(' . $name . ')';
        }
        return $labels;
    }


    /**
     * 产品免认证上线（飞塔）
     * @param $user_name
     * @param $id
     * @param bool $write_online
     * @return array
     */
    public function onlineProduct($user_name, $id, $write_online = true)
    {
        //获取此产品的数据
        if (!$this->redis_uid) {
            $this->redis_uid = Redis::executeCommand('get', 'key:users:user_name:' . $user_name);
        }
        $productUsed = $this->getOneOrderedProduct($id, $this->redis_uid);
        if (!$productUsed) {
            return ['code' => 'fail', 'msg' => '该产品不存在'];
        }
        $product = isset($this->products[$id]) ? $this->products[$id] : '';

        $feitaClass = new Feita();
        //上线此产品
        if ($write_online) {
            //写在线表
            $domain = $feitaClass->get_domain($product['condition']);
            $data = [
                'action' => 1,
                'user_name' => $user_name,
                'domain' => $domain
            ];
            $feitaClass->udp_send(json_encode($data));
        }

        //通知防火墙开启
        $feita_config = Yii::$app->params['feita_config'];
        //获取token
        list($token, $cookie) = $feitaClass->getToken($feita_config['token_url'], $feita_config['username'], $feita_config['secretKey']);

        //开启防火墙
        $user_id = Redis::executeCommand('get', 'key:users:user_name:' . $user_name);
        if (!$user_id) {
            return ['code' => 'fail', 'msg' => '该用户不存在'];
        }

        $res_open = $feitaClass->routeApiOpen($user_id, $user_name, $feita_config['open_url'], $token, $cookie);
        if ($res_open['msg'] != 'success') {
            return ['code' => 'fail', 'msg' => '开启防火墙操作失败'];
        }
        //写日志
        $logContent = Yii::t('app', 'user base help31', [
            'mgr' => $this->_mgrName == '' ? 'system' : $this->getMgrName(), //管理员
            'user_name' => $user_name, //用户名
            'product' => $id . ':' . (isset($product['products_name']) ? $product['products_name'] : ''), //产品名称
        ]);
        //写日志开始
        $data = [
            'operator' => $this->_mgrName == '' ? 'system' : $this->getMgrName(),
            'target' => $user_name,
            'action' => 'onlineProduct',
            'action_type' => 'User Base',
            'content' => $logContent,
            'class' => __CLASS__,
            'type' => 1, //描述日志
        ];
        LogWriter::write($data);

        return ['code' => 'success', 'msg' => '操作成功'];
    }

    /**
     * 控制第三方下线（飞塔）
     * @param $user_name
     * @param $id
     * @return bool
     */
    public function offlineProduct($user_name, $id)
    {
        //获取此产品的数据
        if (!$this->redis_uid) {
            $this->redis_uid = Redis::executeCommand('get', 'key:users:user_name:' . $user_name);
        }
        //获取此产品的数据
        $productUsed = $this->getOneOrderedProduct($id, $this->redis_uid);
        if (!$productUsed) {
            return ['code' => 'fail', 'msg' => '该产品不存在'];
        }
        $product = isset($this->products[$id]) ? $this->products[$id] : '';
        $feitaClass = new Feita();
        //下线此产品
        $feita_config = Yii::$app->params['feita_config'];
        //获取token
        list($token, $cookie) = $feitaClass->getToken($feita_config['token_url'], $feita_config['username'], $feita_config['secretKey']);

        $user_id = Redis::executeCommand('get', 'key:users:user_name:' . $user_name);
        if (!$user_id) {
            return ['code' => 'fail', 'msg' => '该用户不存在'];
        }
        $policyid = 1000 + intval($user_id);
        $url = $feita_config['close_url'];
        eval("\$close_url=\"$url\";");
        $res_close = $feitaClass->routeApiClose($close_url, $token, $cookie);
        if ($res_close['msg'] != 'success') {
            return ['code' => 'fail', 'msg' => '关闭防火墙操作失败'];
        }

        //写日志
        $logContent = Yii::t('app', 'user base help33', [
            'mgr' => $this->getMgrName(), //管理员
            'user_name' => $this->user_name, //用户名
            'product' => $id . ':' . (isset($product['products_name']) ? $product['products_name'] : ''), //产品名称
        ]);
        //写日志开始
        $data = [
            'operator' => $this->getMgrName(),
            'target' => $this->user_name,
            'action' => 'onlineProduct',
            'action_type' => 'User Base',
            'content' => $logContent,
            'class' => __CLASS__,
            'type' => 1, //描述日志
        ];
        LogWriter::write($data);
        return ['code' => 'success', 'msg' => '操作成功'];
    }


    /**
     * 批量操作
     * @ params $params
     */
    public function batchOperate($params)
    {
        $action = isset($params['action']) ? $params['action'] : '';
        $userIds = isset($params['user_id']) ? $params['user_id'] : '';
        $logContent = '';
        if (empty($action)) {
            return json_encode(array('code' => 401, 'error' => Yii::t('app', 'user base help36')));
        }
        if (empty($userIds)) {
            return json_encode(array('code' => 402, 'error' => Yii::t('app', 'user base help34')));
        }

        $query = $this->find()->select(['user_id', 'user_name']);
        $query->andWhere(['user_id' => $userIds]);
        //非超级管理员可以管理的组织结构
        if (!$this->flag) {
            $query->andWhere(['group_id' => array_keys($this->can_group)]);
        }
        $users = $query->asArray()->all();
        if (empty($users)) {
            return json_encode(array('code' => 403, 'error' => Yii::t('app', 'No results found.')));
        }
        $this->payModel = new PayList();

        if (preg_match('/del/', $action)) {
            //批量删除
            $logContent = Yii::t('app', 'group msg5', [
                'mgr' => Yii::$app->user->identity->username,
                'success_num' => count($users),
                'action' => Yii::t('app', 'batch delete')
            ]);
            $this->batchLog('', $logContent);
            $rs = $this->batchDelete($users);
        } else if (preg_match('/buy/', $action)) {
            //批量交费
            $item = [
                $params['products_id'] => $params['buy_num'],
                'package_num' => $params['packages_num'],
                'pay_type_id' => $params['pay_type_id'],
            ];

            $rs = $this->payModel->batchBuyPackage($users, $item);

        } else if (preg_match('/renew/i', $action)) {
            //批量续费
            $extends = [];
            if (!empty($params['extend_fields'])) {
                foreach ($params['extend_fields'] as $k => $v) {
                    $extends[$v] = $params['extend_values'][$k];
                }
            }
            $rs = $this->payModel->batchPayList($users, $params['products_id'], $params['renew_num'], $extends, null, $params['pay_type_id']);

        } else if (preg_match('/enable/i', $action)) {
            //批量启用
            $logContent = Yii::t('app', 'group msg5', [
                'mgr' => Yii::$app->user->identity->username,
                'success_num' => count($users),
                'action' => Yii::t('app', 'batch enable')
            ]);
            $this->batchLog('', $logContent);
            $rs = $this->batchEnable($users, 'batch enable');
        } else if (preg_match('/disable/i', $action)) {
            //批量禁用
            $logContent = Yii::t('app', 'group msg5', [
                'mgr' => Yii::$app->user->identity->username,
                'success_num' => count($users),
                'action' => Yii::t('app', 'batch disable')
            ]);
            $this->batchLog('', $logContent);
            $rs = $this->batchEnable($users, 'batch disable');
        } else if (preg_match('/open/i', $action)) {
            //批量开启mac认证
            $logContent = Yii::t('app', 'group msg5', [
                'mgr' => Yii::$app->user->identity->username,
                'success_num' => count($users),
                'action' => Yii::t('app', 'batch mac auth open')
            ]);
            $this->batchLog('', $logContent);
            $rs = $this->batchMacOpen($users, 'open');

        } else if (preg_match('/close/i', $action)) {
            //批量关闭mac认证
            $logContent = Yii::t('app', 'group msg5', [
                'mgr' => Yii::$app->user->identity->username,
                'success_num' => count($users),
                'action' => Yii::t('app', 'batch mac auth close')
            ]);
            $this->batchLog('', $logContent);
            $rs = $this->batchMacOpen($users, 'close');
        } else if (preg_match('/batchClear/i', $action)) {
            $actionPrase = [
                'batchClearMacAuths' => 'mac_auth',
                'batchClearMacs' => 'mac',
                'batchClearNasPortIds' => 'nas_port_id',
                'batchClearVlanIds' => 'vlan_id',
                'batchClearIPV4s' => 'ip',
            ];
            $rs = $this->batchClearBind($users, $actionPrase[$action]);
            if ($rs) {
                $logContent = Yii::t('app', 'group msg5', [
                    'mgr' => Yii::$app->user->identity->username,
                    'success_num' => count($users),
                    'action' => Yii::t('app', 'batch clear bind', ['bindType' => Yii::t('app', $actionPrase[$action] . 's')])
                ]);
                $this->batchLog('', $logContent);
            }
        }
        Yii::$app->getSession()->setFlash('success', $logContent . Yii::t('app', 'down info', ['download_url' => Url::to(['/user/group/down-load'])]));

        return $rs;
    }


    /**
     * 记录用户绑定于删除绑定的操作日志
     * @param $data
     */
    public function bindLog($data)
    {
        if (!empty($data['user_name']) && !empty($data['operation']) && !empty($data['type'])) {
            $operation = $data['operation'] == 1 ? Yii::t('app', 'add') : Yii::t('app', 'delete');
            $type = $this->getAttributesList()['bindType'][$data['type']];
            $value = $data['value'];
            $logContent = Yii::t('app', 'bindLog', ['mgr' => $this->getMgrName(), 'user_name' => $data['user_name'], 'action' => $operation, 'type' => $type, 'value' => $value]);
            $logData = [
                'operator' => $this->getMgrName(),
                'target' => $this->user_name,
                'action' => $data['operation'] == 1 ? 'add' : 'delete',
                'action_type' => 'User Base',
                'content' => $logContent,
                'class' => __CLASS__,
                'type' => 1,
            ];
            LogWriter::write($logData);
        }
    }

    public function getData($params, $model)
    {
        $rs = [];
        try {
            $query = self::find();
            $export = isset($params['export']) ? $params['export'] : false;
            //是否显示产品
            $productShow = in_array('products_id', $params['showField']) ? true : false;
            //是否显示产品余额
            $pos = array_search('user_balance', $params['showField']);
            //是否显示在线状态
            $statusPos = array_search('user_online_status', $params['showField']);

            //如果显示用户在线状态，那么先unset该元素，处理完搜索后再加上
            if ($statusPos !== false) {
                unset($params['showField'][$statusPos]);
            }

            //无论如何要搜索user_id和user_name字段
            $query->addSelect(['users.user_id', 'users.user_name']);
            //不能单独查询用户表的字段
            $no_field_users = ['products_id', 'user_balance'];
            //连查表中都存在的字段
            $all_have_fields = ['user_id', 'user_name', 'user_available'];
            //不用like的字段
            $no_like_filed = ['products_id', 'group_id', 'user_available', 'mac', 'cert_type'];

            //如果查询产品或者产品余额
            $searchProduct = false;
            if (isset($params['products_id']) && !empty($params['products_id'])) {
                $searchProduct = true;
                $query->leftJoin('user_products as products', 'products.user_id=users.user_id');
            }

            //查询字段处理
            $sortField = [];
            foreach ($params['showField'] as $val) {
                if (array_key_exists($val, $model->searchField)) {
                    //将搜索字段压入新数组
                    $sortField[$val] = $model->searchField[$val];

                    //如果搜索的字段属于表中都存在的字段，加字段识别
                    if (in_array($val, $all_have_fields)) {
                        $val = '`users`.' . $val;
                    }

                    //如果不搜索产品，那么不查询用户表不存在的字段（比如，产品id和产品余额，遍历单独查询，不然查询出的数据重复）
                    if (!$searchProduct && in_array($val, $no_field_users)) {
                        continue;
                    }
                    $query->addSelect($val);
                }
            }
            //一页多少条
            $offset = isset($params['offset']) && $params['offset'] > 0 ? $params['offset'] : 10;

            if (isset($params['user_available']) && !empty($params['user_available']) || preg_match('/^0$/', $params['user_available'])) {
                $available = $params['user_available'];
                $query->andWhere(['users.user_available' => $available]);
            }

            //重新排序searchField
            $this->searchField = $sortField + $model->searchField;

            //过滤查询条件字段
            //如果精确搜索账号，那么不再模板查询user_name
            if (isset($params['exact_tag']) && !empty($params['exact_tag'])) {
                $query->andWhere(['`users`.user_name' => $params['user_name']]);
                $no_like_filed[] = 'user_name';
            }
            foreach ($params as $field => $value) {
                //开户日期搜索
                if ($field == 'user_create_time_start' && !empty($value)) {
                    $query->andWhere(['>=', 'user_create_time', strtotime($value)]);
                }
                if ($field == 'user_create_time_end' && !empty($value)) {
                    $query->andWhere(['<', 'user_create_time', strtotime('+1 day', strtotime($value))]);
                }
                //过期时间搜索
                if ($field == 'user_expire_time_start' && !empty($value)) {
                    $query->andWhere(['>=', 'user_expire_time', strtotime($value)]);
                }
                if ($field == 'user_expire_time_end' && !empty($value)) {
                    $query->andWhere(['<=', 'user_expire_time', strtotime($value)]);
                }
                //其他字段搜索
                if (array_key_exists($field, $model->searchField) && $value != '' && !in_array($field, $no_like_filed)) {
                    //如果字段在表中都存在，那么加字段识别
                    if (in_array($field, $all_have_fields)) {
                        $query->andWhere("`users`.$field LIKE :users_$field", [":users_$field" => "%$value%"]);
                    } else {
                        $query->andWhere("$field LIKE :$field", [":$field" => "%$value%"]);
                    }
                }
            }

            //组织结构
            if (isset($params['group_id']) && !empty($params['group_id'])) {
                $group_id = explode(',', $params['group_id']);
                $ids = SrunJiegou::getNodeId($group_id);
                $query->andWhere(['group_id' => $ids]);
            } else {
                if (!$this->flag) {
                    $query->andWhere(['group_id' => array_keys($this->can_group)]);
                }
            }

            //搜索产品
            if (!empty($params['products_id'])) {
                $query->andWhere(['products.products_id' => $params['products_id']]);
            } else {
                if (!$this->flag) {
                    $query->leftJoin('user_products as products', 'products.user_id=users.user_id');
                    $query->andWhere(['products_id' => array_keys($this->can_product)]);
                }
            }

            //排序
            if (isset($params['orderBy']) && array_key_exists($params['orderBy'], $model->searchField)) {
                $query->orderBy([$params['orderBy'] => $params['sort'] == 'desc' ? SORT_DESC : SORT_ASC]);
            } else {
                $query->orderBy(['users.user_id' => SORT_DESC]);
            }
            $pagination = new Pagination([
                'defaultPageSize' => $offset,
                'totalCount' => $query->count('distinct(users.user_id)'),
            ]);
            //列表
            if ($export) {
                set_time_limit(0);
                //导出数据
                $this->exportData($query, $model, $export, $params, $productShow, $pos, $searchProduct, $statusPos);
            } else {
                $list = $query->offset($pagination->offset)
                    ->limit($pagination->limit)
                    ->asArray()
                    ->all();
            }
            //组织结构数组
            $list = $this->getList($list, $productShow, $searchProduct, $statusPos, $pos);
            // 如果进行mac认证搜索,过滤掉$param['user_name']
            if ($params['mac']) {
                $params['user_name'] = '';
            }
            //产品列表
            $productList = [Yii::t('app', 'select product')] + $model->can_product;
            //套餐列表
            $packageList = (new Package())->getList();
            $extendFields = ExtendsField::getFieldsData('pay_list');
            //缴费方式列表
            $pay_types = PayType::getNameOfList();
            $default_type = PayType::getDefaultType();
            if ($statusPos !== false) {
                $params['showField'][$statusPos] = 'user_online_status';
            }
            ksort($params['showField']);
            //将记录保存在redis中
            $paramKey = 'key:user:base:search:params';
            Redis::executeCommand('set', $paramKey, [yii\helpers\Json::encode($params['showField'])], 'redis_manage');
            $rs = [
                'code' => 200,
                'list' => $list,
                'pagination' => $pagination,
                'params' => $params,
                'packages' => $packageList,
                'default' => $default_type,
                'products' => $productList,
                'pay_types' => $pay_types,
                'extendFields' => $extendFields
            ];
        } catch (\Exception $e) {
            echo $e->getMessage();
            $rs = ['code' => 500, 'msg' => '获取用户发生异常，请查看性能监控，看下有什么问题', 'params' => $params];
        }


        return $rs;
    }

    /**
     * 导出数据
     * @return mixed
     */
    public function exportData($query, $model, $export, $params, $productShow, $pos, $searchProduct, $statusPos)
    {
        $autoRedirect = false;
        $availableShow = in_array('user_available', $params['showField']) ? true : false;
        //导出
        try {
            $count = $query->count();
            if ($export == 'csv') {
                $limit = self::CSV_EXPORT_LIMIT;
            } else {
                $limit = self::USERS_EXPORT_LIMIT;
            }

            if ($count < 1) {
                $msg = Yii::t('error', '没有要导出的用户');
                $autoRedirect = true;
            } else if ($count > $limit) {
                $msg = Yii::t('app', 'group msg6', [
                    'mgr' => Yii::$app->user->identity->username,
                    'limit' => self::USERS_EXPORT_LIMIT
                ]);
                $autoRedirect = false;
            } else {
                $excelData = [];
                $list = $query
                    ->asArray()
                    ->all();

                //显示时间字段,转变为日期格式
                $times = array_intersect($params['showField'], ['user_create_time', 'user_update_time']); //求交集
                $i = 1;
                $excelData[0] = [];

                if ($list) {
                    $userStatus = [
                        '0' => Yii::t('app', 'user available0'),
                        '1' => Yii::t('app', 'user available1'),
                        '2' => Yii::t('app', 'user available3'),
                        '3' => Yii::t('app', 'user available4'),
                    ];
                    if ($statusPos !== false) {
                        $params['showField'][$statusPos] = 'user_online_status';
                    }
                    ksort($params['showField']);
                    foreach ($list as $key => $value) {
                        if (!in_array('user_id', $params['showField'])) {
                            unset($list[$key]['user_id']);
                        }
                        if ($productShow || $searchProduct || $pos) {

                            $products = $this->getProductByName($value['user_id'], false);
                            //如果搜索产品
                            if ($searchProduct) {
                                if ($productShow) {
                                    $list[$key]['products_id'] = isset($products[$value['products_id']]) ? $products[$value['products_id']] : '';
                                }
                                if ($pos !== false) {
                                    $list[$key]['user_balance'] = $value['user_balance'];
                                }
                            } else {
                                if ($productShow || $pos !== false) {
                                    if ($products) {
                                        $list[$key]['products_id'] = implode(",", array_values($products));
                                    } else {
                                        $list[$key]['products_id'] = Yii::t('app', 'product error1');
                                    }
                                    if ($pos !== false) {
                                        foreach ($products as $k => $v) {
                                            $detail = $this->getOneOrderedProduct($k, $value['user_id']);
                                            $balance = $detail['user_balance'];
                                            $list[$key]['user_balance'][] = $balance;
                                        }
                                        $list[$key]['user_balance'] = !empty($list[$key]['user_balance']) ? implode(',', $list[$key]['user_balance']) : '0';
                                    }
                                }

                            }
                        }

                        if (in_array('group_id', $params['showField'])) {
                            $groupId = SrunJiegou::getOwnParent($value['group_id']);
                            $list[$key]['group_id'] = $groupId;
                        }
                        //显示状态
                        if ($availableShow) {
                            $list[$key]['user_available'] = isset($userStatus[$value['user_available']])
                                ? $userStatus[$value['user_available']] : '';

                        }
                        if (!empty($times)) {
                            foreach ($times as $v) {
                                $list[$key][$v] = date('Y-m-d H:i:s', $value[$v]); //转换成日期格式再导出
                            }
                        }
                        if ($statusPos !== false) {
                            $k = "set:rad_online:user_name:" . $value['user_name'];
                            $flag = Redis::executeCommand('exists', $k, [], 'redis_online');
                            $list[$key]['user_online_status'] = ($flag == 1) ? Yii::t('app', 'user online status1') : Yii::t('app', 'user online status0');
                        }
                        if (empty($excelData[0])) {
                            foreach ($list[$key] as $field => $v) {
                                if ($export == 'csv') {
                                    $excelData[0][$field] = $model->searchField[$field];
                                } else {
                                    $excelData[0][] = $model->searchField[$field];
                                }

                            }
                        }
                        if ($export == 'csv') {
                            $excelData[$i] = $list[$key];
                        } else {
                            $excelData[$i] = array_values($list[$key]);
                        }

                        $i++;
                    }
                }

                if (count($params['showField']) != count($list[0])) {
                    //判断导出的字段与页面显示的是否一致
                    $msg = $logContent = Yii::t('app', 'group msg7', [
                        'mgr' => Yii::$app->user->identity->username,
                    ]);

                    $autoRedirect = true;
                } else {
                    if ($export == 'csv') {
                        $fileName = Yii::t('app', 'user/base/index');
                        $header = $excelData[0]; //头部
                        $fields = [];
                        foreach ($header as $k => $v) {
                            if (in_array($k, $times) || $k == 'products_id') {
                                $options = ['width' => 500];
                            } else {
                                $options = [];
                            }
                            $fields[$k] = [
                                'name' => $v,
                                'type' => 'string',
                                'options' => $options,
                            ];
                        }
                        $data = array_slice($excelData, 1); //导出数据
                        //we create the CSV into memory
                        try {
                            $export = new CsvExport();
                            $source = new ArrayType($data, $fields);
                            $export->export($source, $fileName);
                            exit;
                        } catch (\Exception $e) {
                            echo $e->getMessage();
                            exit;
                        }
                    } else {
                        $file = Yii::t('app', 'user/base/index') . '.xls';
                        $title = Yii::t('app', 'batch export');
                        //将内容写入excel文件
                        Excel::header_file($excelData, $file, $title);
                        exit;
                    }
                }
            }
        } catch (\Exception $e) {
            //判断导出的字段与页面显示的是否一致
            $msg = Yii::t('app', 'group msg8', [
                'mgr' => Yii::$app->user->identity->username,
                'message' => $e->getMessage(),
            ]);
            $autoRedirect = true;
        }
        if ($autoRedirect) {
            //说明发生异常,跳转页面
            unset($params['export']);
            $url = Yii::$app->urlManager->createUrl(array_merge(['user/base/index'], $params));

            $rs = ['code' => 403, 'url' => $url, 'msg' => $msg];

            return $rs;
        }
    }
}