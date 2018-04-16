<?php
/**
 * 批量excel处理
 */

namespace center\modules\user\models;

use center\modules\auth\models\SrunJiegou;
use center\modules\Core\models\BaseModel;
use center\modules\financial\models\Bills;
use center\modules\financial\models\Gift;
use center\modules\financial\models\PayList;
use center\modules\financial\models\RefundList;
use center\modules\financial\models\TransferBalance;
use center\modules\financial\models\WaitCheck;
use center\modules\log\models\LogWriter;
use center\modules\setting\models\ExtendsField;
use center\modules\strategy\models\IpPart;
use center\modules\strategy\models\Package;
use center\modules\strategy\models\Product;
use center\modules\strategy\models\ProductsChange;
use common\models\KernelInterface;
use common\models\Redis;
use common\models\User;
use yii;
use yii\helpers\ArrayHelper;

class BatchExcel extends BaseModel
{
    const SETTLE_ACCOUNT_LIMIT = 10000;  //最大结算数，因excel导出有限制
    const INSERT_UPDATE_USER_LIMIT = 5000; //最大开户或者编辑用户数
    const DELETE_USER_LIMIT = 5000; //销户数限制
    const EXPORT_USER_LIMIT = 30000; //导出用户限制
    const REFUND_USER_LIMIT = 5000; //退费限制
    const PAY_USER_LIMIT = 5000; //批量缴费限制
    const BUY_USER_LIMIT = 5000; //批量购买限制
    //const SETTLE_ACCOUNT_LIMIT  = 3000;
    //批量类型
    public $batchType = 1;
    //销户类型
    public $deleteType = 1;
    //已选择字段
    public $selectField = [];
    //选择导出的字段
    public $selectExportField = [];
    //选择导出的组
    public $export_group_id;
    //上传文件
    public $file;
    //excel 中的数据
    public $excelData;


    //users表的字段
    public $userField = [];
    //普通字段
    public $normalField = [];
    //需要特殊的字段
    public $specialField = [];
    //可以绑定的字段
    public $bindField = [];
    //运营商产品的字段
    public $carrierOperatorField = [];
    //可以导出的字段
    public $exportField = [];
    //结算的字段
    public $checkoutField = [];
    //设置项
    public $setting = [];

    public $showField = [];

    public $showEditField = [];
    //购买显示的字段
    public $buyField = [];

    public $product_id = 0;

    public $products = [];

    public $user_available = [];
    //用户产品信息字段
    public $productFields = [];

    public $redisFields = [];

    public $specialOperateField = [];

    //购买对象
    public $buyObject = 'package'; //默认购买套餐

    //退费时是否退未使用的套餐
    public $isRefundPackages;


    public function init()
    {
        $userModel = new Users();
        //user表的字段
        $this->userField = Yii::$app->db->getSchema()->getTableSchema('users')->columnNames;
        $attribute = $userModel->attributeLabels();
        foreach ($this->userField as $value) {
            if (array_key_exists($value, $attribute)) {
                $this->exportField[$value] = $attribute[$value];
            }
        }

        $this->productFields = [
            'product_id' => Yii::t('app', 'products id'),
            'product_name' => Yii::t('app', 'products name'),
            'product_balance' => Yii::t('app', 'products balance'),
            'user_charge' => Yii::t('app', 'user charge'),
            'checkout_date' => Yii::t('app', 'checkout date'),
        ];

        $this->redisFields = [
            'mobile_phone' => $attribute['user_available'], //用户状态
        ];
        //普通字段
        $this->normalField = [
            'user_name' => $attribute['user_name'],
            'user_password' => $attribute['user_password'],
            'user_real_name' => $attribute['user_real_name'],
            'group_id' => Yii::t('app', 'batch excel user group id'), //$attribute['group_id'],
            'user_allow_chgpass' => $attribute['user_allow_chgpass'],
            //'balance' => Yii::t('app', 'batch excel account balance'), //$attribute['balance'],
            'user_available' => $attribute['user_available'],
            'user_expire_time' => $attribute['user_expire_time'],
            'user_create_time' => $attribute['user_create_time'],
            'mgr_name_create' => $attribute['mgr_name_create'],
        ];
        //扩展字段
        foreach (ExtendsField::getAllData() as $one) {
            $this->normalField[$one['field_name']] = $one['field_desc'];
        }

        //特殊字段
        $this->specialField = [
            'balance_add' => Yii::t('app', 'batch excel pay num'), //账户余额缴费
            'products_id' => Yii::t('app', 'batch excel products id'), //绑定产品	
            'mobile_phone' => Yii::t('app', 'batch excel mobile phone'),
            'mobile_password' => Yii::t('app', 'batch excel mobile password'),
            'mac_auth' => Yii::t('app', 'batch excel mac auth'),
            'user_password_md5' => Yii::t('app', 'batch excel user password md5'),
            'max_online_num' => Yii::t('app', 'batch excel max online num'),

            //'products_id_pay' => Yii::t('app', '对产品缴费'), //对绑定的产品缴费
            //'products_id_new' => Yii::t('app', '绑定新产品'), //绑定新产品
            //'products_id_new_pay' => Yii::t('app', '对新产品缴费'), //对绑定的新产品缴费
        ];

        //可以绑定的字段
        $this->bindField = [
            'mac_auths' => Yii::t('app', 'mac_auths value'),
            'macs' => Yii::t('app', 'macs value'),
            'nas_port_ids' => Yii::t('app', 'nas_port_ids value'),
            'vlan_ids' => Yii::t('app', 'vlan_ids value'),
            'ips' => Yii::t('app', 'ips value'),
        ];

        //运营商的手机号，密码
        $this->carrierOperatorField = [
            'carrier_mobile_phone' => Yii::t('app', 'carrier operator mobile_phone'),
            'carrier_mobile_password' => Yii::t('app', 'carrier operator mobile_password'),
            'carrier_status' => Yii::t('app', 'carrier operator status'),
        ];

        //结算需要的字段
        $this->checkoutField = [
            'checkout_date' => Yii::t('app', 'checkout date')
        ];

        //特殊操作的字段
        $this->specialOperateField = [
            'next_product_id' => Yii::t('app', 'next product id'),
        ];
        //新增用户总共需要显示的字段
        $this->showField = ArrayHelper::merge($this->normalField, $this->specialField, $this->bindField, $this->carrierOperatorField);

        //修改用户总共需要显示的字段
        $this->showEditField = ArrayHelper::merge($this->normalField, $this->specialField, $this->bindField, $this->carrierOperatorField, $this->checkoutField, $this->specialOperateField);

        //导出用户总共需要显示的字段
        $this->exportField = array_merge($this->exportField, $this->redisFields, $this->productFields);

        //购买需要的字段
        $this->buyField = [
            'user_name' => Yii::t('app', 'account'),
            'product_id' => Yii::t('app', 'products id'),
            'package_id' => Yii::t('app', 'package id'),
            'balance_add' => Yii::t('app', 'batch excel pay num'),
        ];
        //可管理的产品
        $this->products = [0 => Yii::t('app', 'select product')] + $this->can_product;
        //用户状态
        $user_available = $userModel->getAttributesList()['user_available'];
        $this->user_available = ['' => Yii::t('app', 'select user_available')] + $user_available;
        parent::init();
    }

    public function getAttributesList()
    {
        return [
            'operate_product_action' => [
                'set_product' => Yii::t('app', 'Reset product'),//'重置产品',
                'add_product' => Yii::t('app', 'Cumulative products'),//'累加产品',
                'cancel_product' => Yii::t('app', 'action cancelProduct'),//'取消产品',
                'disable_product' => Yii::t('app', 'action disableProduct'),//'禁用产品',
                'open_product' => Yii::t('app', 'action enableProduct'),//'启用产品',
            ],
        ];
    }

    /**
     * 生成模板文件
     */
    public function template()
    {
        $excelList = [];
        //导入或修改
        if ($this->batchType == 1 || $this->batchType == 2) {
            foreach ($this->selectField as $one) {
                $excelList[0][] = $this->showField[$one] ? $this->showField[$one] : $this->showEditField[$one];
            }
        } //销户、退费
        elseif ($this->batchType == 3 || $this->batchType == 5) {
            $excelList[0] = [
                Yii::t('app', 'account'),
            ];
        } //购买
        elseif ($this->batchType == 6) {
            foreach ($this->selectField as $one) {
                $excelList[0][] = $this->buyField[$one] ? $this->buyField[$one] : '';
            }
        } //结算
        elseif ($this->batchType == 7) {
            $excelList[0] = [
                Yii::t('app', 'account'),
            ];
        }

        return $excelList;
    }

    /**
     * 批量处理数据
     * @return bool
     */
    public function batch_data()
    {
        // 根据不同的选项进入不同的方法
        $type = $this->batchType;
        $return_info = false;
        $this->getMgrName();
        // 混合方式，导入，修改
        if ($type == 1) {
            $return_info = $this->insert_user();
        } else if ($type == 2) {
            $return_info = $this->update_user();
        } // 销户模式
        else if ($type == 3) {
            $return_info = $this->delete_user();
        } // 导出模式
        else if ($type == 4) {
            $return_info = $this->expert_user();
        } // 退费模式
        else if ($type == 5) {
            $return_info = $this->refund_user();
        } // 购买模式
        else if ($type == 6) {
            $return_info = $this->buy_user();
        } //结算模式
        else if ($type == 7) {
            $return_info = $this->checkout_user();
        }

        return $return_info;
    }

    /**
     * excel批量开户
     * @return bool
     * @throws yii\db\Exception
     */
    public function insert_user()
    {
        $trans = Yii::$app->db->beginTransaction();
        try {
            // 初始化失败和成功的数组
            $array_ok = [];
            $array_err = [
                '0' => [
                    Yii::t('app', 'batch excel line'),
                    Yii::t('app', 'account'),
                    Yii::t('app', 'batch excel result'),
                    Yii::t('app', 'batch excel detail'),
                ]
            ];

            //先判断是否有ip段，有再显示绑定ip列
            $isExistsIpPart = IpPart::find()->count();
            if ($isExistsIpPart) {
                $array_err[0][] = Yii::t('app', 'autoBindIp');
            }
            $model = new BatchAddEdit();
            $model->products = $this->products;
            $model->can_group = $this->can_group;
            $model->can_product = $this->can_product;

            $file_data = $this->excelData;
            unset($file_data[1]);
            $rs = $model->add_user($file_data, $this->selectField, $isExistsIpPart);

            if ($rs['code'] == 200) {
                $trans->commit();
                $array_err = array_merge($array_err, $rs['error']);
                $array_ok = array_merge($array_ok, $rs['ok']);
                // 开始整合发生错误的数组和正确的数组，并返回数组用来填充Excel供用户下载
                $array_info = array_merge($array_err, $array_ok);
                return [
                    'ok' => count($array_ok),
                    'err' => count($array_err) - 1,
                    'list' => $array_info
                ];
            } else {
                $trans->rollBack();
                Yii::$app->getSession()->setFlash('danger', $rs['msg']);
                return false;
            }
        } catch (\Exception $e) {
            $trans->rollBack();
            $this->writeMessage('excelAdd', '批量添加用户发生异常:' . $e->getMessage());

            return false;
        }
    }

    /**
     * 新增或者更新用户
     */
    public function update_user()
    {
        $trans = Yii::$app->db->beginTransaction();
        try {
            $language_failed = Yii::t('app', 'failed');
            // 初始化失败和成功的数组
            $array_ok = [];
            $array_err = [
                '0' => [
                    Yii::t('app', 'batch excel line'),
                    Yii::t('app', 'account'),
                    Yii::t('app', 'batch excel result'),
                    Yii::t('app', 'batch excel detail'),
                ]
            ];

            //先判断是否有ip段，有再显示绑定ip列
            $isExistsIpPart = IpPart::find()->count();
            if ($isExistsIpPart) {
                $array_err[0][] = Yii::t('app', 'autoBindIp');
            }

            $changeModel = new ProductsChange();
            $file_data = $this->excelData;
            $batchType = $this->batchType;
            $productsIds = array_keys($this->can_product);
            //用户组
            $groups = array_keys($this->can_group);
            //待结算model
            $wait_checkout = new WaitCheck();
            $payList = new PayList();
            $userModel = Users::getInstance();

            // 表中有效的字段，剔出第一行
            unset($file_data[1]);
            $flag = false;
            if (count($file_data) > 100) {
                $userBase = Users::find()->select('user_name')->indexBy('user_name')->asArray()->all();
                $flag = true;
            }
            $i = 0;
            foreach ($file_data as $line => $data) {
                $payList->message_err = [];
                $payList->message_ok = [];
                $payList->payTotalNum = 0;
                // 初始化普通字段和特殊字段数组
                $normal = $special = $bind = $checkout = [];
                $userModel->oldAttributes = null;
                $userModel->user_id = null;
                // 把数据组合成如下形式：array('user_login_name'=>'test1','user_password_ori'=>'111')
                foreach ($this->selectField as $k => $field) {
                    //如果是批量修改且是空，那么不更新用户数据。
                    if ($batchType == 2 && $special[$field] == '') {
                        unset($special[$field]);
                    }
                    // 分开 用户普遍字段和需要特殊处理的字段
                    if (array_key_exists($field, $this->specialField)) {
                        //产品
                        $special[$field] = $data[$k] == null ? '' : trim($data[$k]);
                    } else if (array_key_exists($field, $this->normalField)) {
                        //数据库
                        $normal[$field] = $data[$k] == null ? '' : trim($data[$k]);
                    } else if (array_key_exists($field, $this->bindField)) {
                        //mac,valn|ip等bind
                        $bind[$field] = $data[$k] == null ? '' : trim($data[$k]);
                    } else if (array_key_exists($field, $this->carrierOperatorField)) {
                        //运营商
                        $carrier[$field] = $data[$k] == null ? '' : trim($data[$k]);
                    } else if (array_key_exists($field, $this->checkoutField)) {
                        $checkout[$field] = $data[$k] == null ? '' : trim($data[$k]);
                    } else if (array_key_exists($field, $this->specialOperateField)) {
                        $specialOperate[$field] = $data[$k] == null ? '' : trim($data[$k]);
                    }
                }

                // 组合失败
                if (empty($normal) && empty($special)) {
                    $array_err[] = [$line, '', $language_failed, Yii::t('app', 'batch excel help13')];
                    continue;
                }

                // 如果没有用户名
                if ($normal['user_name'] == '') {
                    $array_err[] = [$line, '', $language_failed, Yii::t('app', 'batch excel help12')];
                    continue;
                }
                $user_name = $normal['user_name'];

                //判断密码
                if ($batchType == 1 && $normal['user_password'] == '' && $special['user_password_md5'] == '') {
                    $array_err[] = [$line, $user_name, $language_failed, Yii::t('app', 'batch excel help41')];
                    continue;
                }

                //判断组织结构
                if (isset($normal['group_id'])) {
                    if (!in_array($normal['group_id'], $groups)) {
                        $array_err[] = [$line, '', $language_failed, Yii::t('app', 'message 401 1')];
                        continue;
                    }
                }
                //判断产品
                if (isset($special['products_id']) && !empty($special['products_id'])) {
                    $products_id = explode(',', $special['products_id']);
                    $pidErr = $pidNotExist = false;
                    foreach ($products_id as $pid) {
                        if (!in_array($pid, $productsIds)) {
                            $pidNotExist = $pid;
                            break;
                        }
                    }
                    if ($pidNotExist !== false) {
                        $array_err[] = [$line, $user_name, $language_failed, Yii::t('app', 'message 401 5', ['product_id' => $pidNotExist])];
                        continue;
                    }
                    if ($pidErr) {
                        $array_err[] = [$line, '', $language_failed, Yii::t('app', 'message 401 4')];
                        continue;
                    }
                }

                if ($flag) {
                    //编辑用户不存在
                    if (!in_array($userModel->group_id, $groups)) {
                        // 对存在的用户判断组织结构
                        $array_err[] = [$line, '', $language_failed, Yii::t('app', 'message 401 1')];
                        continue;
                    }
                    if (!array_key_exists($user_name, $userBase)) {
                        $array_err[] = [$line, $user_name, $language_failed, Yii::t('app', 'batch excel help15')];
                        continue;
                    }
                } else {
                    // 查询数据库中用户的信息
                    $userModel = Users::findOne(['user_name' => $user_name]);
                    // 先做一次基本判定
                    // 如果是更新模式,用户不存在
                    if (!in_array($userModel->group_id, $groups)) {
                        // 对存在的用户判断组织结构
                        $array_err[] = [$line, '', $language_failed, Yii::t('app', 'message 401 1')];
                        continue;
                    }
                    $array_err[] = [$line, $user_name, $language_failed, Yii::t('app', 'batch excel help15')];
                    continue;
                }
                //下个周期产品
                if ($specialOperate) {
                    //如果多个产品，不让转移
                    if ((!isset($special['products_id']) || empty($special['products_id'])) && count($userModel->products_id) > 1) {
                        $array_err[] = [$line, $user_name, $language_failed, Yii::t('app', 'batch excel message1')];
                        continue;
                    }
                    $current_product_id = (!isset($special['products_id']) || empty($special['products_id'])) ? $userModel->products_id[0] : explode(',', $special['products_id'])[0]; //explode(',', $special['products_id'])[0];
                    //判断新产品是否在下个产品日志里，如果存在，则不能再转
                    if ($changeModel->isExistNextProduct($userModel->user_id, $specialOperate['next_product_id'])) {
                        $product_name = $this->can_product($specialOperate['next_product_id']);
                        $product_desc = empty($product_name) ? 'id=' . $specialOperate['next_product_id'] : $specialOperate['next_product_id'] . ':[' . $product_name . ']';
                        $array_err[] = [$line, $user_name, $language_failed, Yii::t('app', 'change product help3', ['product_desc' => $product_desc])];
                    } else {
                        $data = [
                            'user_id' => $userModel->user_id,
                            'products_id_from' => $current_product_id,
                            'products_id_to' => $specialOperate['next_product_id'],
                        ];
                        $res = $changeModel->changeProductNext($data);
                        if ($res) {
                            $changeModel->addLogNext($user_name, $data['products_id_from'], $data['products_id_to']);
                            $array_ok[] = array($line, $userModel->user_name, Yii::t('app', 'success'), '');
                        }
                    }
                    continue;
                }

                //处理特殊字段
                foreach ($special as $field => $value) {
                    if ($field !== 'products_id') {
                        $userModel->$field = $value;
                    }
                }

                //产品处理  必须没有勾选结算日期、和缴费金额
                if ((isset($special['products_id']) && !empty($special['products_id'])) && empty($checkout) && (!isset($special['balance_add']) || empty($special['balance_add']))) {
                    $pro = $userModel->products_id; //用户绑定的产品
                    $proNew = explode(',', $special['products_id']); //新的产品
                    //修改用户产品时，把之前的产品需要结算掉，剩下的余额转到账户余额
                    if ($batchType == 2) {
                        if ($this->setting['operate_product_action'] == 'set_product') {//重置产品
                            if (!empty($pro)) {
                                $cancel_pids = array_diff($pro, $proNew);
                                $add_pids = array_diff($proNew, $pro);
                                //如果是正好相差一个产品，那么就进行立即转移产品操作，否则进行 取消+修改产品
                                if (count($cancel_pids) == 1 && count($add_pids) == 1) {
                                    $checkout_num = $wait_checkout->checkoutParams($user_name, $cancel_pids[0])['checkout_amount'];
                                    $change_res = $changeModel->changeProductNow($user_name, $userModel->user_id, $cancel_pids[0], $add_pids[0], 1, $checkout_num);
                                    if (!$change_res) {
                                        $array_err[] = [$line, $user_name, $language_failed, Yii::t('app', 'batch excel help32', ['aciton' => $this->getAttributesList()['operate_product_action'][$this->setting['operate_product_action']], 'pro_id' => $cancel_pids[0]])];
                                    }
                                } else {
                                    if ($cancel_pids) {
                                        foreach ($cancel_pids as $v) {
                                            $cancelProRes = $userModel->cancelProduct($v);
                                            if (!$cancelProRes) {
                                                $array_err[] = [$line, $user_name, $language_failed, Yii::t('app', 'batch excel help32', ['aciton' => $this->getAttributesList()['operate_product_action'][$this->setting['operate_product_action']], 'pro_id' => $v])];
                                                continue;
                                            }
                                        }
                                    }
                                }
                            }
                        } elseif ($this->setting['operate_product_action'] == 'add_product') {//累加产品
                            $proNew = array_unique(array_merge($pro, $proNew));
                        } elseif ($this->setting['operate_product_action'] == 'cancel_product') {//取消产品
                            //原产品必须多余要取消的产品才能取消
                            if (count($pro) > count($proNew)) {
                                foreach ($proNew as $pid) {
                                    if (in_array($pid, $pro)) {
                                        $cancelProRes = 1;
                                        $userModel->cancelProduct($pid);
                                        if (!$cancelProRes) {
                                            $array_err[] = [$line, $user_name, $language_failed, Yii::t('app', 'batch excel help32', ['aciton' => $this->getAttributesList()['operate_product_action'][$this->setting['operate_product_action']], 'pro_id' => $pid])];
                                            continue;
                                        }
                                    } else {
                                        $array_err[] = [$line, $user_name, $language_failed, Yii::t('app', 'batch excel help59', ['product_id' => $pid])];
                                        continue;
                                    }
                                }
                                $proNew = array_diff($pro, $proNew);
                            } else {
                                $array_err[] = [$line, $user_name, $language_failed, Yii::t('app', 'batch excel help60', ['pro_pre' => implode(',', $pro)])];
                                continue;
                            }
                        } elseif ($this->setting['operate_product_action'] == 'disable_product') {//禁用产品
                            foreach ($proNew as $pid) {
                                if (in_array($pid, $pro)) {
                                    $res = $userModel->disableProduct($pid);
                                    if (!$res) {
                                        $array_err[] = [$line, $user_name, $language_failed, Yii::t('app', 'batch excel help32', ['aciton' => $this->getAttributesList()['operate_product_action'][$this->setting['operate_product_action']], 'pro_id' => $pid])];
                                        continue;
                                    }
                                } else {
                                    $array_err[] = [$line, $user_name, $language_failed, Yii::t('app', 'batch excel help59', ['product_id' => $pid])];
                                    continue;
                                }
                            }
                            $proNew = $pro;
                        } elseif ($this->setting['operate_product_action'] == 'open_product') {//启用产品
                            foreach ($proNew as $pid) {
                                if (in_array($pid, $pro)) {
                                    $res = $userModel->enableProduct($pid);
                                    if (!$res) {
                                        $array_err[] = [$line, $user_name, $language_failed, Yii::t('app', 'batch excel help32', ['aciton' => $this->getAttributesList()['operate_product_action'][$this->setting['operate_product_action']], 'pro_id' => $pid])];
                                        continue;
                                    }
                                } else {
                                    $array_err[] = [$line, $user_name, $language_failed, Yii::t('app', 'batch excel help59', ['product_id' => $pid])];
                                    continue;
                                }
                            }
                            $proNew = $pro;
                        } else {
                            $array_err[] = [$line, $user_name, $language_failed, Yii::t('app', 'batch excel help61')];
                            continue;
                        }
                    }
                    //判断订购的新产品是否在下个产品日志里，如果存在，则不能订购
                    foreach ($proNew as $onePid) {
                        if ($changeModel->isExistNextProduct($userModel->user_id, $onePid)) {
                            $product_name = $this->can_product($onePid);
                            $product_desc = empty($product_name) ? 'id=' . $onePid : $onePid . ':' . $product_name;
                            $array_err[] = [$line, $user_name, $language_failed, Yii::t('app', 'change product help2', ['product_desc' => $product_desc])];
                            unset($proNew[array_search($onePid, $proNew)]);
                            continue;
                        }
                    }
                    $userModel->products_id = $proNew;
                }

                //保存用户数据
                //$userModel->setAttributes($normal, false);
                foreach ($normal as $field => $value) {
                    $userModel->$field = trim($value);
                }

                //判断用户的必须条件
                //没有产品

                if (empty($userModel->products_id)) {
                    $array_err[] = [$line, $user_name, $language_failed, Yii::t('app', 'batch excel help29')];
                    continue;
                }
                //没有组
                if (empty($userModel->group_id)) {
                    $array_err[] = [$line, $user_name, $language_failed, Yii::t('app', 'batch excel help30')];
                    continue;
                }

                $userModel->saveUser(false);

                //绑定字段处理
                $bindFields = $userModel->getBindType();
                foreach ($bind as $field => $value) {
                    if (array_key_exists($field, $bindFields)) {
                        $vals = explode(',', $value);
                        if ($vals) {
                            foreach ($vals as $val) {
                                $res = KernelInterface::userBind(['operation' => 1, 'user_name' => $user_name, 'value' => $val, 'type' => $userModel->getBindType()[$field]]);
                                if (!$res) {
                                    $array_err[] = [$line, $user_name, $language_failed, Yii::t('app', 'batch excel help37', ['field' => $field . '(' . $val . ')'])];
                                }
                                continue;
                            }
                        }
                    }
                }

                //运营商字段处理
                if ($carrier) {
                    if (empty($special['products_id'])) {
                        $array_err[] = [$line, $user_name, $language_failed, Yii::t('app', 'batch excel help38')];
                    }
                    $operatorModel = new Operator();
                    $proObj = $userModel->getOneProductObj($userModel->user_id, $special['products_id'][0]);
                    $carrier_mobile_phone = isset($carrier['carrier_mobile_phone']) ? $carrier['carrier_mobile_phone'] : (isset($proObj['mobile_phone']) ? $proObj['mobile_phone'] : '');
                    $carrier_mobile_password = isset($carrier['carrier_mobile_password']) ? $carrier['carrier_mobile_password'] : (isset($proObj['mobile_password']) ? $proObj['mobile_password'] : '');
                    if (isset($carrier['carrier_status']) && !in_array($value, [0, 1])) {
                        $array_err[] = [$line, $user_name, $language_failed, Yii::t('app', 'batch excel help39')];
                    }
                    $carrier_status = isset($carrier['carrier_status']) ? $carrier['carrier_status'] : (isset($proObj['user_available']) ? $proObj['user_available'] : $operatorModel::STATUS_S);
                    $operatorModel->updateProObj($user_name, $special['products_id'][0], $carrier_mobile_phone, $carrier_mobile_password, $carrier_status);
                }

                //附加消息处理
                $extMsg = '';

                //充值
                $payItem = [];
                if (isset($special['balance_add']) && $special['balance_add'] != 0) {
                    //根据缴费设置，缴到不同的地方
                    if (isset($this->setting['pay_where'])) {
                        //缴到第一个产品，并且存在第一个产品
                        if ($this->setting['pay_where'] == 1) {
                            //如果想给多个产品缴费，那么产品id和缴费金额都用，隔开
                            if ($batchType == 2 && !empty($special['products_id'])) {
                                $proNew = explode(',', $special['products_id']);
                                $payNum = explode(',', $special['balance_add']);
                                foreach ($proNew as $key => $id) {
                                    if (in_array($id, $userModel->products_id) && isset($payNum[$key]) && $payNum[$key] > 0) {
                                        $payItem['productPay'][$id] = $payNum[$key];
                                    } else {
                                        $array_err[] = [$line, $user_name, $language_failed, Yii::t('app', 'batch excel help59', ['product_id' => $id])];
                                        continue;
                                    }
                                }
                            } else {
                                if ($special['balance_add'] > 0) {
                                    $payItem['productPay'][$userModel->products_id[0]] = $special['balance_add'];
                                }
                            }

                        } //缴到账户余额
                        else if ($this->setting['pay_where'] == 2) {
                            $payItem['balance'] = $special['balance_add'];
                        }
                    }
                }
                //开始缴费
                if ($payItem) {
                    $payList->payByUser($userModel->user_name, $payItem);
                    $extMsg .= $payList->getPayMessage();
                }

                //结算日期
                if ($checkout && $batchType == 2) {
                    if (!$special['products_id']) {
                        $array_err[] = [$line, $user_name, $language_failed, Yii::t('app', 'batch excel help40')];
                        continue;
                    }

                    foreach ($products_id as $pid) {
                        $wait_checkout_data = $wait_checkout::findOne(['user_id' => $userModel->user_id, 'products_id' => $pid]);
                        $usedHash = Redis::executeCommand('HGETALL', 'hash:users:products:' . $userModel->user_id . ":" . $pid, []);
                        if ($usedHash) {
                            $checkout_date = strstr($checkout['checkout_date'], '-') == false && strstr($checkout['checkout_date'], '/') == false && strstr($checkout['checkout_date'], ':') == false && strlen($checkout['checkout_date']) == 10 && intval($checkout['checkout_date']) > 0 ? intval($checkout['checkout_date']) : strtotime($checkout['checkout_date']);
                            if (!$wait_checkout_data) {
                                $wait_checkout->user_id = $userModel->user_id;
                                $wait_checkout->products_id = $pid;
                                $wait_checkout->checkout_date = $checkout_date;
                                $wait_checkout->save(false);
                            } else {
                                $wait_checkout_data->checkout_date = $checkout_date;
                                $wait_checkout_data->save(false);
                            }
                        }

                    }
                }

                $resMsg = '';
                if ($this->batchType == 1) {
                    $resMsg = Yii::t('app', 'batch excel help16'); //开户成功
                } else if ($this->batchType == 2) {
                    $resMsg = Yii::t('app', 'batch excel help17'); //修改成功
                }

                $msg = !empty($normal) ? json_encode($normal) : '';
                $msg .= !empty($special) ? json_encode($special) : '';
                $msg .= !empty($bind) ? json_encode($bind) : '';
                $msg .= !empty($carrier) ? json_encode($carrier) : '';
                $msg .= $extMsg;
                $ok = [$line, $userModel->user_name, $resMsg, $msg];
                //如果有自动绑定ip
                if ($isExistsIpPart && $userModel->bindIp) {
                    $ok[] = $userModel->bindIp;
                }
                $array_ok[] = $ok;
                $i++;
            }
            // 所有Excel文件中的数据已经处理完毕
            $trans->commit();
            // 开始整合发生错误的数组和正确的数组，并返回数组用来填充Excel供用户下载
            $array_info = array_merge($array_err, $array_ok);
            return [
                'ok' => count($array_ok),
                'err' => count($array_err) - 1,
                'list' => $array_info
            ];
        } catch (\Exception $e) {
            $trans->rollBack();
            $msg = ($this->batchType == 1) ? 'Error: 批量excel开户发生异常:' . $e->getMessage() : 'Error: 批量excel编辑用户发生异常:' . $e->getMessage();
            $action = ($this->batchType == 1) ? 'excelAdd' : 'excelEdit';
            $this->writeMessage($action, $msg);

            return false;
        }
    }

    /**
     * 导出用户
     */
    public function expert_user()
    {
        try {
            $userModel = new Users();
            $limit = self::EXPORT_USER_LIMIT;
            $query = Users::find();
            $list = []; //excel列表
            $fields = []; //字段
            $attributes = $userModel->getAttributesList();
            //获取全部组织结构的数组[1=>'google', 2=>'qq']
            $groups = $this->can_group;

            foreach ($this->selectExportField as $value) {
                if (array_key_exists($value, $this->exportField)) {
                    $fields[] = $value;
                    $list[0][] = $this->exportField[$value];
                }
            }
            //添加字段
            if (in_array('user_id', $this->selectExportField)) {
                $select_fields = array_diff($fields, array_keys($this->redisFields), array_keys($this->productFields));
            } else {
                $select_fields = array_merge(['user_id'], array_diff($fields, array_keys($this->redisFields), array_keys($this->productFields)));
            }
            //echo '<pre>';
            //var_dump($select_fields, $_POST,$this->selectExportField, $this->productFields, $sameProduct, $redisSame);exit;
            $query->addSelect($select_fields);

            //搜索的条件
            if (!empty($this->export_group_id)) {
                //获取可以导出的id
                $group_id = SrunJiegou::getNodeId($this->export_group_id);
                $query->where(['group_id' => $group_id]);
            }

            //如果选择产品
            if ($this->product_id) {
                $users_id = Redis::executeCommand('LRANGE', 'list:products:' . $this->product_id, [0, -1]);
                $query->andWhere(['user_id' => $users_id]);
            }

            $query->asArray();
            $count = $query->count();
            if ($count > $limit) {
                $msg = Yii::t('app', 'batch help1', [
                    'mgr' => $this->_mgrName,
                    'count' => $count,
                    'limit' => $limit
                ]);
                Yii::$app->getSession()->setFlash('danger', $msg);

                return false;
            }

            //var_dump($query->all(), $group_id);
            $dateTimeField = ['user_create_time', 'user_update_time', 'user_expire_time']; //去redis查询产品信息
            $intersect_red = array_intersect($this->selectExportField, array_keys($this->productFields)); //去redis查询相关产品信息
            $diff = array_diff($intersect_red, ['product_id', 'product_name']);
            $ifOnlyProduct = $diff ? false : true;
            foreach ($query->each(100) as $one) {
                if ($this->product_id) {
                    $list[] = $this->getExportOneUser($attributes, $groups, $one, $this->product_id, $dateTimeField, $ifOnlyProduct);
                } else {
                    if ($intersect_red) {
                        $ids = $this->getProductByName($one['user_id']);
                        foreach ($ids as $id => $name) {
                            $list[] = $this->getExportOneUser($attributes, $groups, $one, $id, $dateTimeField, $ifOnlyProduct);
                        }
                    } else {
                        $list[] = $this->getExportOneUser($attributes, $groups, $one, '', $dateTimeField);
                    }

                }
            }

            //如果选择了用户状态，那么把数据过滤一遍，通过查询redis里的状态来筛选出数据
            if ($list && !is_array($this->user_available) && $this->user_available !== '') {
                $data[0] = $list[0];
                unset($list[0]);
                foreach ($list as $one) {
                    $uid = Redis::executeCommand('get', 'key:users:user_name:' . $one[array_search('user_name', $this->selectExportField)]);
                    $abailable = Redis::executeCommand('HGET', 'hash:users:' . $uid, ['user_available']);
                    if (!is_null($abailable) && $this->user_available == $abailable) {
                        $data[] = $one;
                    }
                }
                $list = $data;
            }
            $logContent = Yii::t('app', 'batch excel help28', [
                'mgr' => Yii::$app->user->identity->username,
                'ok_num' => count($list) - 1,
            ]);
            $logData = [
                'operator' => Yii::$app->user->identity->username,
                'target' => 'users',
                'action' => 'export',
                'action_type' => 'User Batch',
                'content' => $logContent,
                'class' => get_class($this),
                'type' => 1
            ];
            LogWriter::write($logData);
            //日志结束
            return $list;
        } catch (\Exception $e) {
            $msg = "Error: 批量导出用户发生异常" . $e->getMessage();
            $this->writeMessage('batchExportUsers', $msg);

            return false;
        }
    }

    /**
     * 导出一个用户的详细处理
     * @param $attrs
     * @param $groups 用户组数组
     * @param $one 每个用户的数组
     * @param $pid 产品id
     * @param array $timeField 时间字段
     * @param bool $ifOnlyProduct 只选择了产品跟产品名称
     * @return array
     */
    public function getExportOneUser($attrs, $groups, $one, $pid, $timeField, $ifOnlyProduct = false)
    {
        $arr = [];
        foreach ($this->selectExportField as $field) {
            if (array_key_exists($field, $this->productFields)) {
                if ($ifOnlyProduct) {
                    $proObj = $this->getOneOrderedProduct($one['user_id'], $pid);
                } else {
                    $proObj = [];
                }

                if ($field == 'product_id') {
                    $arr[] = $pid;
                }
                if ($field == 'product_name') {
                    $arr[] = $this->can_product[$pid];
                }
                if ($field == 'product_balance') {
                    $arr[] = $proObj ? sprintf('%.2f', $proObj['user_balance']) : 0;
                }
                if ($field == 'user_charge') {
                    $arr[] = $proObj ? sprintf('%.2f', $proObj['user_charge']) : 0;
                }
                if ($field == 'checkout_date') {
                    //获取此用户此产品的结算日期
                    $waitCheckModel = WaitCheck::findOne(['user_id' => $one['user_id'], 'products_id' => $pid]);
                    $arr[] = $proObj && $waitCheckModel ? date('Y-m-d', $waitCheckModel->checkout_date) : '';
                }
            } elseif (array_key_exists($field, $this->redisFields)) {
                $user = Redis::executeCommand('HGETALL', 'hash:users:' . $one['user_id']);
                if ($user) {
                    $user = Redis::hashToArray($user);
                    $arr[] = isset($attrs[$field][$user[$field]]) ? $attrs[$field][$user[$field]] : $user[$field];
                } else {
                    $arr[] = '';
                }
            } else {
                //时间格式
                if (array_key_exists($field, $one) && !empty($timeField) && in_array($field, $timeField)) {
                    $arr[] = date('Y-m-d H:i:s', $one[$field]);
                } //选项列表格式
                else if (isset($attributes[$field][$one[$field]])) {
                    $arr[] = $attributes[$field][$one[$field]];
                } //组织结构名称
                else if ($field == 'group_id' && isset($groups[$one[$field]])) {
                    $arr[] = $groups[$one[$field]];
                } //产品id
                else if ($field == 'user_available') {
                    $arr[] = isset($attrs[$field][$one[$field]]) ? $attrs[$field][$one[$field]] : $one[$field];
                } else {
                    $arr[] = $one[$field];
                }
            }
        }
        // var_dump($arr, $this->selectExportField, $one);exit;
        return $arr;
    }

    /**
     * 销户
     */
    public function delete_user()
    {
        $trans = Yii::$app->db->beginTransaction();
        try {

            $language_failed = Yii::t('app', 'failed');
            // 初始化失败和成功的数组
            $array_ok = array();
            $array_err = [
                '0' => [
                    Yii::t('app', 'batch excel line'),
                    Yii::t('app', 'account'),
                    Yii::t('app', 'batch excel result'),
                    Yii::t('app', 'batch excel detail'),
                ],
            ];

            $file_data = $this->excelData;
            $groups = $this->can_group;
            $products = $this->can_product;

            // 表中有效的字段
            unset($file_data[1]);
            foreach ($file_data as $line => $data) {
                $user = $special = [];
                // 初始化
                // 把数据组合成如下形式：array('user_login_name'=>'test1','user_password_ori'=>'111')
                foreach ($this->selectField as $k => $field) {
                    $user[$field] = trim($data[$k]);
                }

                // 如果没有用户名
                if ($user['user_name'] == '') {
                    $array_err[] = [$line, '', $language_failed, Yii::t('app', 'batch excel help12')];
                    continue;
                }
                $user_name = $user['user_name'];
                $userModel = Users::findOne(['user_name' => $user_name]);
                // 先做一次基本判定
                // 如果用户不存在
                if (!$userModel) {
                    $array_err[] = [$line, $user_name, $language_failed, Yii::t('app', 'batch excel help15')];
                    continue;
                }
                //判断组织结构和产品是否可用
                //判断组织结构
                if (!array_key_exists($userModel->group_id, $groups)) {
                    $array_err[] = [$line, $user_name, $language_failed, Yii::t('app', 'message 401 3')];
                    continue;
                }
                //判断产品
                if ($userModel->products_id) {
                    $pidErr = false;
                    foreach ($userModel->products_id as $pid) {
                        if (!array_key_exists($pid, $products)) {
                            $pidErr = true;
                            break;
                        }
                    }
                    if ($pidErr) {
                        $array_err[] = [$line, $user_name, $language_failed, Yii::t('app', 'message 401 4')];
                        continue;
                    }
                }
                if ($userModel->balance < 0.1) {
                    if ($userModel->products_id) {
                        $orderedProductList = $this->getOrderedProductDetail($userModel->products_id, $userModel->user_id);
                        $whether = $this->getWhetherDelete($orderedProductList, $userModel->user_name);
                        $isBreak = false;
                        if (is_bool($whether)) {
                            //销户
                            if (!$whether) {
                                $isBreak = true;
                                $msg = Yii::t('app', 'disable user error3');
                            }
                        } else {
                            $isBreak = true;
                            $msg = Yii::t('app', 'disable user error2', [
                                'user_name' => $userModel->user_name,
                                'proName' => $whether,
                            ]);
                        }
                        if ($isBreak) {
                            $array_err[] = [$line, $user_name, $language_failed, $msg];
                            continue;
                        }
                    }

                } else {
                    $array_err[] = [$line, $user_name, $language_failed, Yii::t('app', 'disable error1')];
                    continue;
                }

                $userModel->delete();
                $array_ok[] = [$line, $user_name, Yii::t('app', 'batch excel help18')];
            }
            // 所有Excel文件中的数据已经处理完毕
            // 开始整合发生错误的数组和正确的数组，并返回数组用来填充Excel供用户下载
            $array_info = array_merge($array_err, $array_ok);
            $trans->commit(); //提交
            return [
                'ok' => count($array_ok),
                'err' => count($array_err) - 1,
                'list' => $array_info
            ];
        } catch (\Exception $e) {
            $msg = "ERROR: 批量销户发生异常:" . $e->getMessage() . ',行数：' . $e->getLine();
            $trans->rollBack();
            $this->writeMessage('batchDeleteUsers', $msg);

            return false;
        }

    }

    /**
     * 退费
     * @return array
     */
    public function refund_user()
    {
        $trans = Yii::$app->db->beginTransaction();
        try {
            $language_failed = Yii::t('app', 'failed');
            // 初始化失败和成功的数组
            $array_ok = array();
            $array_err = [
                '0' => [
                    Yii::t('app', 'batch excel line'),
                    Yii::t('app', 'account'),
                    Yii::t('app', 'user real name'),
                    Yii::t('app', 'ID NO'),
                    Yii::t('app', 'batch excel result'),
                    Yii::t('app', 'batch excel detail'),
                    Yii::t('app', 'products balance') . '(' . Yii::t('app', 'currency') . ')',
                    Yii::t('app', 'checkout amount'),
                    Yii::t('app', 'package') . '(' . Yii::t('app', 'currency') . ')',
                    Yii::t('app', 'account balance') . '(' . Yii::t('app', 'currency') . ')',
                    Yii::t('app', 'total amount') . '(' . Yii::t('app', 'currency') . ')',
                ],
            ];
            //设置项
            //默认退费给用户
            $model = new BatchRefund();
            $refund_type = isset($this->setting['refund_where']) ? $this->setting['refund_where'] : 1;
            //退费前是否结算产品
            $refund_is_checkout = isset($this->setting['refund_is_checkout']) ? $this->setting['refund_is_checkout'] : 1;
            //操作类型
            $get_data_type = isset($this->setting['get_data_type']) ? $this->setting['get_data_type'] : 1;
            //是否退未使用的套餐
            $isRefundPackages = $this->setting['isRefundPackages'];
            //用户组id
            $group_id = $this->batchType == 5 ? $this->export_group_id : '';
            if ($group_id !== '') {
                $group_data = Users::find()->select('user_name')->where(['group_id' => $group_id])->all();
            }
            $userData = $get_data_type == 1 && !empty($this->excelData) ? $this->excelData : $group_data;
            $model->can_product = $this->can_product;
            $model->can_group = $this->can_group;
            $model->products = $this->products;
            // 表中有效的字段
            if ($get_data_type == 1 && !empty($this->excelData)) {
                unset($userData[1]);
            }
            if ($refund_type == 1) {
                $rs = $model->refundToBalance($userData, $this->selectField, $refund_is_checkout, $isRefundPackages, $get_data_type);
            } else {
                $rs = $model->refundToSystem($userData, $this->selectField, $get_data_type);
            }
            if ($rs['code'] != 200) {
                return false;
            }
            $array_err = $array_err + $rs['arr_err'];
            $array_ok = $rs['arr_ok'];

            // 所有Excel文件中的数据已经处理完毕
            // 开始整合发生错误的数组和正确的数组，并返回数组用来填充Excel供用户下载
            $array_info = array_merge($array_err, $array_ok);
            $trans->commit();
            return [
                'ok' => count($array_ok),
                'err' => count($array_err) - 1,
                'list' => $array_info
            ];
        } catch (\Exception $e) {
            $trans->rollBack();
            $msg = "Error: 批量退费发生异常:" . $e->getMessage();
            $this->writeMessage('batchRefund', $msg);

            return false;
        }
    }

    /**
     * 结算
     * @return array
     */
    public function checkout_user()
    {
        $trans = Yii::$app->db->beginTransaction();
        try {
            $language_failed = Yii::t('app', 'failed');
            // 初始化失败和成功的数组
            $array_ok = array();
            $array_err = [
                '0' => [
                    Yii::t('app', 'account'),
                    Yii::t('app', 'user real name'),
                    Yii::t('app', 'batch excel result'),
                    Yii::t('app', 'products id'),
                    Yii::t('app', 'batch excel detail'),
                ],
            ];
            //用户组id
            $group_id = $this->batchType == 7 ? $this->export_group_id : '';
            //根据选择用户组得到的用户数据
            $group_data = [];
            if ($group_id !== '') {
                $arr = SrunJiegou::getNodeId($group_id);
                $group_data = Users::find()->select('user_name')->where(['group_id' => $arr])->all();
            }
            $count = count($group_data);
            if ($count < 1 && !empty($group_id)) {
                //没有结算的用户
                Yii::$app->getSession()->setFlash('error', Yii::t('app', 'settle account error'));

                return false;
            }
            $this->getMgrName();
            if ($count > self::SETTLE_ACCOUNT_LIMIT) {
                $msg = Yii::t('app', 'batch help1', [
                    'mgr' => $this->_mgrName,
                    'count' => $count,
                    'limit' => self::SETTLE_ACCOUNT_LIMIT
                ]);
                Yii::$app->getSession()->setFlash('danger', $msg);

                return false;
            }
            $userData = !empty($this->excelData) ? $this->excelData : $group_data;

            // 表中有效的字段
            if (!empty($this->excelData)) {
                unset($userData[1]);
            }

            //创建对象
            $check_model = new WaitCheck();
            $change_model = new ProductsChange();
            if ($userData) {
                foreach ($userData as $line => $data) {
                    //导入excel用户来结算
                    if (!empty($this->excelData)) {
                        $user = [];
                        // 初始化
                        // 把数据组合成如下形式：array('user_login_name'=>'test1','user_password_ori'=>'111')
                        foreach ($this->selectField as $k => $field) {
                            $user[$field] = trim($data[$k]);
                        }
                        // 如果没有用户名
                        if ($user['user_name'] == '') {
                            $array_err[] = ['', '', $language_failed, '', Yii::t('app', 'batch excel help12')];
                            continue;
                        }
                        $user_name = $user['user_name'];
                        $userModel = Users::findOne(['user_name' => $user_name]);
                        // 先做一次基本判定
                        // 如果用户不存在
                        if (!$userModel) {
                            $array_err[] = [$user_name, '', $language_failed, '', Yii::t('app', 'batch excel help15')];
                            continue;
                        }
                        //判断组织结构和产品是否可用
                        //判断组织结构
                        if (!array_key_exists($userModel->group_id, $this->can_group)) {
                            $array_err[] = [$user_name, '', $language_failed, '', Yii::t('app', 'message 401 3')];
                            continue;
                        }
                        //判断产品
                        if ($userModel->products_id) {
                            $pidErr = false;
                            foreach ($userModel->products_id as $pid) {
                                if (!array_key_exists($pid, $this->can_product)) {
                                    $pidErr = true;
                                    break;
                                }
                            }

                            if ($pidErr) {
                                $array_err[] = [$user_name, '', $language_failed, '', Yii::t('app', 'message 401 4')];
                                continue;
                            }
                        }
                    } else { //选择用户组来结算
                        $user_name = $data['user_name'];
                        $userModel = Users::findOne(['user_name' => $user_name]);
                    }
                    //结算
                    if (!empty($userModel->products_id)) {
                        foreach ($userModel->products_id as $pid) {
                            //判断该用户产品是否在待结算表中
                            $rs = $check_model->checkoutOnce($change_model, $userModel->user_id, $pid, $userModel->attributes);
                            if ($rs) {
                                $array_ok[] = [$user_name, $userModel->user_real_name, Yii::t('app', 'success'), $pid, ''];
                            } else {
                                $array_err[] = [$user_name, $userModel->user_real_name, Yii::t('app', 'failed'), $pid];
                            }
                        }
                    } else {
                        $array_err[] = [$user_name, $userModel->user_real_name, Yii::t('app', 'failed'), '', Yii::t('app', 'product error')];

                    }
                }
            }
            // 所有Excel文件中的数据已经处理完毕
            // 开始整合发生错误的数组和正确的数组，并返回数组用来填充Excel供用户下载
            $trans->commit();
            $array_info = array_merge($array_err, $array_ok);
            return [
                'ok' => count($array_ok),
                'err' => count($array_err) - 1,
                'list' => $array_info
            ];
        } catch (\Exception $e) {
            $trans->rollBack();
            $msg = 'Error: 用户批量结算发生异常:' . $e->getMessage();
            $this->writeMessage('batchCheckout', $msg);

            return false;
        }

    }

    /**
     * 需退费多少 查看excel
     * @return array
     */
    public function refund_num_excel()
    {
        $language_failed = Yii::t('app', 'failed');
        // 初始化失败和成功的数组
        $array_ok = array();
        $array_err = [
            '0' => [
                Yii::t('app', 'batch excel line'),
                Yii::t('app', 'account'),
                Yii::t('app', 'batch excel result'),
                Yii::t('app', 'batch excel detail'),
                Yii::t('app', 'products balance') . '(' . Yii::t('app', 'currency') . ')',
                Yii::t('app', 'account balance') . '(' . Yii::t('app', 'currency') . ')',
                Yii::t('app', 'tatal amount') . '(' . Yii::t('app', 'currency') . ')',
            ],
        ];
        //设置项
        //默认退费给用户
        $refund_type = isset($this->setting['refund_where']) ? $this->setting['refund_where'] : 1;
        //操作类型
        $get_data_type = isset($this->setting['get_data_type']) ? $this->setting['get_data_type'] : 1;
        //用户组id
        $group_id = $this->batchType == 5 ? $this->export_group_id : '';
        //根据选择用户组得到的用户数据
        $group_data = [];
        if ($group_id !== '') {
            $group_data = Users::find()->select('user_name')->where(['group_id' => $group_id])->all();
        }
        $userData = $get_data_type == 1 && !empty($this->excelData) ? $this->excelData : $group_data;
        $refund_model = new RefundList();

        // 表中有效的字段
        if ($get_data_type == 1 && !empty($this->excelData)) {
            unset($userData[1]);
        }

        if ($userData) {
            foreach ($userData as $line => $data) {
                //导入excel用户来退费
                if (!empty($this->excelData)) {
                    $line -= 1;
                    $user = [];
                    // 初始化
                    // 把数据组合成如下形式：array('user_login_name'=>'test1','user_password_ori'=>'111')
                    foreach ($this->selectField as $k => $field) {
                        $user[$field] = trim($data[$k]);
                    }
                    // 如果没有用户名
                    if ($user['user_name'] == '') {
                        $array_err[] = [$line, '', $language_failed, Yii::t('app', 'batch excel help12')];
                        continue;
                    }
                    $user_name = $user['user_name'];
                    $userModel = Base::findOne($user_name);
                    // 先做一次基本判定
                    // 如果用户不存在
                    if (!$userModel) {
                        $array_err[] = [$line, $user_name, $language_failed, Yii::t('app', 'batch excel help15')];
                        continue;
                    }
                    //判断组织结构和产品是否可用
                    //判断组织结构
                    if (!array_key_exists($userModel->group_id, $this->can_group)) {
                        $array_err[] = [$line, $user_name, $language_failed, Yii::t('app', 'message 401 3')];
                        continue;
                    }
                    //判断产品
                    if ($userModel->products_id) {
                        $pidErr = false;
                        foreach ($userModel->products_id as $pid) {
                            if (!array_key_exists($pid, $this->can_product)) {
                                $pidErr = true;
                                break;
                            }
                        }
                        if ($pidErr) {
                            $array_err[] = [$line, $user_name, $language_failed, Yii::t('app', 'message 401 4')];
                            continue;
                        }
                    }
                } else { //选择用户组来退费
                    $line += 1;
                    $user_name = $data['user_name'];
                    $userModel = Users::findOne(['user_name' => $user_name]);
                }
                //退费
                $pro_ref_detail = ''; //产品退费详情
                $balance_pre = $userModel->balance; //退费之前账户余额
                $pro_ref_num = 0; //产品退费总余额
                if ($refund_type == 1) {
                    foreach ($userModel->products_id as $pid) {
                        $availableAll = $refund_model->getProBal($pid, $userModel->user_id);
                        $availArr = json_decode($availableAll, true);
                        $available = $availArr['balance'] - $availArr['fee'];
                        $balType = 'addBal';
                        if ($available > 0) {
                            //扣除产品余额
                            KernelInterface::updateproductBal($user_name, $pid, -$available);
                            //写退费记录
                            $this->addRefundList($refund_model, $user_name, $available, $type = 1, $pid, ['group_id' => $userModel->group_id]);
                            $pro_ref_num += $available;
                            $proInfo = $this->products[$pid];
                            $pro_name = isset($proInfo['products_name']) ? $proInfo['products_name'] : $pid;
                            $pro_ref_detail .= $pro_name . ':' . $available . Yii::t('app', 'currency') . " ";
                        }
                        $pro_ref_detail = $pro_ref_detail == '' ? '0' : $pro_ref_detail;
                    }
                    //账户余额
                    $user_balance = $balance_pre + $pro_ref_num;
                    if ($user_balance) {
                        $balType = 'subBal';
                        $res = $this->updateBalance($userModel, $refund_model, $user_balance, 0, $user_name, '0', $balType);
                        //写退费记录
                        $this->addRefundList($refund_model, $user_name, $available, $type = 0, '', ['group_id' => $userModel->group_id]);
                    }
                }
                //结算
                if ($refund_type == '2') {
                    echo '正在开发中...';
                    exit;
                }

                $pro_ref_detail = empty($pro_ref_detail) ? '0' : $pro_ref_detail;
                $array_ok[] = [$line, $user_name, $userModel->user_real_name, Yii::t('app', 'success'), '', $pro_ref_detail, $balance_pre, $pro_ref_num + $balance_pre];
            }
        }

        // 开始整合发生错误的数组和正确的数组，并返回数组用来填充Excel供用户下载
        $array_info = array_merge($array_err, $array_ok);
        return [
            'ok' => count($array_ok),
            'err' => count($array_err) - 1,
            'list' => $array_info
        ];
    }

    /**
     * 更新用户账户余额
     * @param $baseModel
     * @param $refund_num
     * @param string $typeBal addBal 增加余额 subBal 减少余额
     * @return mixed
     */
    public function updateBalance($baseModel, $refund_num, $typeBal = 'subBal')
    {

        if ($typeBal == 'addBal') {
            $baseModel->balance += $refund_num;
        } else {
            $baseModel->balance -= $refund_num;
        }
        return $baseModel->save(false);
    }

    /**
     * 增加退费记录
     * @param $refund_model
     * @param $user_name
     * @param $refund_num
     * @param string $type
     * @param $pid
     * @param array $params
     * @return mixed
     */
    public function addRefundList($refund_model, $user_name, $refund_num, $type = '0', $pid, $params = [])
    {
        $refund_model->oldAttributes = null;
        $refund_model->id = null;
        $refund_model->attributes = null;
        $refund_model->refund_num = $refund_num;
        $refund_model->type = $type;
        $refund_model->product_id = $pid;
        $refund_model->is_refund_fee = 0;
        $refund_model->user_name = $user_name;
        $refund_model->group_id = isset($params['group_id']) && $params['group_id'] ? $params['group_id'] : 0;
        return $refund_model->save(false);
    }

    public function buy_user()
    {
        $trans = Yii::$app->db->beginTransaction();
        try {
            //根据提交的excel数据来循环,把每行的数据和已选择的字段对应上，根据字段来操作
            //给每个用户的产品购买套餐，然后写日志，导出处理结果
            $language_failed = Yii::t('app', 'batch excel help50');
            if ($this->buyObject == 1) {
                //购买套餐
                $packages = (new Package())->getList();
            }
            // 初始化失败和成功的数组
            $array_ok = [];
            $array_err = [
                '0' => [
                    Yii::t('app', 'batch excel line'),
                    Yii::t('app', 'account'),
                    Yii::t('app', 'batch excel result'),
                    Yii::t('app', 'batch excel detail'),
                ]
            ];

            $file_data = $this->excelData;

            //产品名列表
            $products_name = $this->can_product;
            $payModel = new BatchPay();
            $payModel->getMgrName();
            // 表中有效的字段，剔出第一行
            unset($file_data[1]);
            foreach ($file_data as $line => $data) {
                //给每行的所有字段赋值
                $payModel->message_ok = [];
                $payModel->message_err = [];
                $payModel->needPayTotalNum = 0;
                foreach ($this->selectField as $k => $field) {
                    if (array_key_exists($field, $this->buyField)) {
                        $one[$field] = isset($data[$k]) ? trim($data[$k]) : '';
                    }
                }
                //如果没有用户名
                if ($one['user_name'] == '') {
                    $array_err[] = [$line, '', $language_failed, Yii::t('app', 'batch excel help12')];
                    continue;
                }
                //如果没有产品id
                if (empty($one['product_id'])) {
                    $array_err[] = [$line, '', $language_failed, Yii::t('app', 'batch excel help45')];
                    continue;
                }
                //判断产品权限
                if (!array_key_exists($one['product_id'], $this->can_product)) {
                    $array_err[] = [$line, '', $language_failed, Yii::t('app', 'message 401 4')];
                    continue;
                }
                //如果没有套餐id
                if (empty($one['package_id']) && $this->buyObject == 1) {
                    $array_err[] = [$line, '', $language_failed, Yii::t('app', 'batch excel help46')];
                    continue;
                }
                //如果没有缴费金额
                if (empty($one['balance_add']) && $this->buyObject == 2) {
                    $array_err[] = [$line, '', $language_failed, Yii::t('app', '缴费金额为空')];
                    continue;
                } elseif ($this->buyObject == 1) {
                    //可能多个套餐
                    $package_ids = explode(',', $one['package_id']);
                }

                // 查询数据库中用户的信息
                $userModel = Users::findOne(['user_name' => $one['user_name']]);

                // 先做一次基本判定
                // 如果用户存在
                if ($userModel) {
                    // 对存在的用户判断组织结构
                    if (!array_key_exists($userModel->group_id, $this->can_group)) {
                        $array_err[] = [$line, '', $language_failed, Yii::t('app', 'message 401 1')];
                        continue;
                    }
                } // 如果用户不存在
                else {
                    $array_err[] = [$line, $one['user_name'], $language_failed, Yii::t('app', 'batch excel help15')];
                    continue;
                }
                // 判断产品是否已订购
                if (!in_array($one['product_id'], $userModel->products_id)) {
                    $array_err[] = [$line, $one['user_name'], $language_failed, Yii::t('app', 'batch excel help47')];
                    continue;
                } else {
                    $product_name = isset($products_name[$one['product_id']]) ? $products_name[$one['product_id']] : '';
                }

                if ($this->buyObject == 1) {
                    //判断套餐是否存在
                    $package_name = [];
                    $isBreak = false;
                    foreach ($package_ids as $package_id) {
                        $packageInfo = array_key_exists($package_id, $packages);
                        if (!$packageInfo) {
                            $isBreak = true;
                        } else {
                            $package_name[] = $packages[$package_id]['package_name'];
                        }
                    }
                    if ($isBreak) {
                        $array_err[] = [$line, $one['user_name'], $language_failed, Yii::t('app', 'batch excel help48')];
                        continue;
                    }
                    $packages_name = implode(',', $package_name);
                    //购买套餐
                    $payData = [
                        $one['product_id'] => $package_ids
                    ];
                    $userModel->products = $this->products;
                    $payModel->userModel = $userModel;
                    $res = $payModel->payPackage($payData, $packages);

                } else {
                    $payData = [
                        $one['product_id'] => $one['balance_add'],
                    ];
                    $payModel->useBalance = true;
                    $userModel->products = $this->products;
                    $payModel->userModel = $userModel;
                    $res = $payModel->payProduct($payData);
                }
                if ($res) {
                    $resMsg = Yii::t('app', 'batch excel help49'); //
                    if ($this->buyObject == 1) {
                        $msg = Yii::t('app', 'batch excel help51', [
                            'product_name' => $product_name,
                            'package_name' => $packages_name,
                            'num' => $payModel->needPayTotalNum]);
                    } else {
                        $msg = Yii::t('app', 'batch excel help55', [
                            'product_name' => $product_name,
                            'num' => $one['balance_add']]);
                    }
                    // 所有Excel文件中的数据已经处理完毕
                    // 开始整合发生错误的数组和正确的数组，并返回数组用来填充Excel供用户下载

                    $array_ok[] = array($line, $userModel->user_name, $resMsg, $msg);
                } else {
                    $array_err[] = [$line, $userModel->user_name, $payModel->getPayMessage('<br/>')];
                }
            }
            $exec = $this->buyObject == 1 ? false : true;
            $payModel->batchInsert($packages, 'pay_list', $exec);
            $payModel->batchInsert($packages, 'transfer', $exec);
            $trans->commit(); //提交
            $array_info = array_merge($array_err, $array_ok);
            return [
                'ok' => count($array_ok),
                'err' => count($array_err) - 1,
                'list' => $array_info
            ];
        } catch (\Exception $e) {
            $trans->rollBack();
            $msg = "Error: 批量购买发生异常:" . $e->getMessage();
            $this->writeMessage('batchPay', $msg);

            return false;
        }

    }

    /**
     * 批量根据excel导出用户当前产品id
     */
    public function excelExport($file_data)
    {
        $array = [
            '0' => [
                '用户名',
                '电子钱包',
                '产品',
                '产品余额'
            ]
        ];
        $products = (new Product())->getAllNameArr();
        // 表中有效的字段，剔出第一行
        unset($file_data[1]);
        foreach ($file_data as $line => $data) {
            if (empty($data['0'])) {
                continue;
            }
            $one = Base::findOne(['user_name' => $data['0']]);
            if ($one) {
                $uid = $one['user_id'];
                $products_name = $products_balance = [];
                $products_id = Redis::executeCommand('LRANGE', 'list:users:products:' . $one['user_id'], [0, -1]);
                foreach ($products_id as $pid) {
                    $products_name[] = isset($products[$pid]) ? $products[$pid] : $pid;
                    $products_balance[] = Redis::executeCommand('HGET', 'hash:users:products:' . $uid . ':' . $pid, ['user_balance']);
                }
                $array[] = [$data['0'], $one['balance'], implode(',', $products_name), implode(',', $products_balance)];
            }
        }
        return $array;
    }
}
