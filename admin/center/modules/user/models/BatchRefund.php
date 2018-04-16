<?php
/**
 * 批量excel处理
 */

namespace center\modules\user\models;

use center\extend\Tool;
use center\modules\financial\models\Bills;
use center\modules\financial\models\CheckoutList;
use center\modules\financial\models\CheckoutSetting;
use center\modules\financial\models\Gift;
use center\modules\financial\models\PayType;
use center\modules\financial\models\RefundList;
use center\modules\financial\models\TransferBalance;
use center\modules\log\models\LogWriter;
use center\modules\log\models\Operate;
use center\modules\strategy\models\Package;
use center\modules\strategy\models\ProductsChange;
use common\models\KernelInterface;
use common\models\Redis;
use yii;

class BatchRefund extends yii\db\ActiveRecord
{
    //const SETTLE_ACCOUNT_LIMIT  = 3000;
    //购买对象
    public $buyObject = 'package'; //默认购买套餐
    public $pay_type;
    public $select_fields;
    public $data;

    //退费时是否退未使用的套餐
    public $isRefundPackages;
    public $needPayTotalNum;
    public $userModel;
    public $message_ok;
    public $message_err;
    public $useBalance;
    public $pay_data;
    public $useTotalBalance;
    public $payTotalNum;
    public $payListId;
    public $pay_type_name;
    public $can_group;
    public $can_product;
    public $products;
    public $package_mode;
    public static $radiusModel;
    public static $change_model;
    public static $package_model;
    public static $giftModel;
    public static $write_log;


    public $transfer_data; //转账记录
    public $checkout_data; //结算记录
    public $refund_data; //退费记录
    public $package_data; //需要发送的套餐数据
    public $product_data; //需要发送的产品数据
    public $del_packages; //需要删除的套餐
    public $del_checkout; //需要删除的结算数据


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%refund_list}}';
    }

    /**
     * @return OnlineRadius
     */
    public static function getRadiusModel()
    {
        if (empty(self::$radiusModel)) {
            self::$radiusModel = new OnlineRadius();
        }

        return self::$radiusModel;
    }

    /**
     * 套餐模块,单例模式
     * @return Package
     */
    public static function getPackageModel()
    {
        if (empty(self::$package_model_model)) {
            self::$package_model = new Package();
        }

        return self::$package_model;
    }

    public static function getGiftModel()
    {
        if (empty(self::$giftModel)) {
            self::$giftModel = new Gift();
        }

        return self::$giftModel;
    }
    public static function getWriteLogModel()
    {
        if (self::$write_log == '') {
            self::$write_log = new Operate();
        }

        return self::$write_log;
    }

    /**
     *
     * @return ProductsChange
     */
    public static function getChangeModel()
    {
        if (empty(self::$change_model)) {
            self::$change_model = new ProductsChange();
        }

        return self::$change_model;
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
            $this->pay_type = PayType::getDefaultType();
            $this->pay_type_name = PayType::findOne($this->pay_type)['type_name'];
        }
        return $this->_mgrName;
    }

    /**
     * 退费到电子钱包
     * @param $userData  需要处理的用户
     * @param $fields 选中的字段
     * @param $is_refund_product 是否退产品
     * @param $is_refund_package
     * @param $getType
     *
     * @return bool|array
     */
    public function refundToBalance($userData, $fields, $is_refund_product, $is_refund_package, $getType)
    {
        if (empty($userData)) {
            return false;
        }
        $language_failed = Yii::t('app', 'failed');
        $array_err = $array_ok = [];
        $this->getMgrName();
        $checkoutSettings = $transferData = [];
        $time = time();
        if ($is_refund_product) {
            //获取各产品结算情况
            $checkoutSettings = $this->getCheckoutSettings($this->products);
        }
        $flag = ($getType == 1) ? true : false;
        foreach ($userData as $key => $data) {

            // 把数据组合成如下形式：array('user_login_name'=>'test1','user_password_ori'=>'111')
            if ($getType == 1) {
                $line = $key + 1;
                foreach ($fields as $k => $field) {
                    $user[$field] = trim($data[$k]);
                }
                $user_name = $user['user_name'];
                if (empty($user_name)) {
                    $array_err[] = [$line, '', '', '', $language_failed, Yii::t('app', 'batch excel help12')];
                    continue;
                }
                $line = $key + 1;
                foreach ($fields as $k => $field) {
                    $user[$field] = trim($data[$k]);
                }
                $user_name = $user['user_name'];
                if (!empty($user_name)) {
                    $userModel = Users::findOne(['user_name' => $user_name]);
                    if (!$userModel) {//用户不存在
                        $array_err[] = [$line, $user_name, '', '', $language_failed, Yii::t('app', 'batch excel help15')];
                        continue;
                    }
                } else { //用户名为空
                    $array_err[] = [$line, '', '', '', $language_failed, Yii::t('app', 'batch excel help12')];
                    continue;
                }
                //判断组织结构和产品是否可用
                //判断组织结构
                if (!array_key_exists($userModel->group_id, $this->can_group)) {
                    $array_err[] = [$line, $user_name, $userModel->user_real_name, $userModel->cert_num, $language_failed, Yii::t('app', 'message 401 3')];
                    continue;
                }
            } else {
                $user_name = $data['user_name'];
                $line = $key - 1;
                $userModel = Users::findOne(['user_name' => $user_name]);
            }
            //判断产品
            $checkout_num = $pro_ref_num = $package_ref_num = 0;
            $pro_ref_detail = $package_total = '';
            $balance_pre = $userModel->balance;

            if ($userModel->products_id) {
                foreach ($userModel->products_id as $pid) {
                    $proName = $pid . ':' . $this->products[$pid]['products_name'];
                    if (!array_key_exists($pid, $this->can_product)) {
                        $array_err[] = [$line, $user_name, $userModel->user_real_name, $userModel->cert_num, $language_failed, $proName . Yii::t('app', 'message 401 4')];
                    } else {
                        $rs = $this->refundOneProduct($userModel, $pid, $is_refund_product, $checkoutSettings[$pid]);
                        if ($rs['code'] != 200) {
                            $array_err[] = [$line, $user_name, $userModel->user_real_name, $userModel->cert_num, $language_failed, $proName . $rs['msg']];
                        } else {
                            //钱包套餐转换金额，组装转换数据以及退费数据
                            $pro_ref_num += $rs['pro_num'];
                            $checkout_num += $rs['checkout_num'];
                            $pro_ref_detail .= $proName . ':' . $rs['pro_num'] . Yii::t('app', 'currency') . " ";
                            //记录日志，单个产品退费记录
                            $this->product_data[] = [
                                'action' => 3,
                                'serial_code' => time() . rand(111111, 999999),
                                'time' => time(),
                                'proc' => 'admin',
                                'user_name' => $user_name,
                                'amount' => floatval(-$rs['pro_num']),
                                'products_id' => $pid,

                            ];
                            $logContent = Yii::t('app', 'batch help3', [
                                'mgr' => $this->_mgrName,
                                'name' => $proName,
                                'num' => $rs['pro_num']
                            ]); //转账金额
                            $this->writeLog($user_name, $logContent, 'refund');
                            //产品转账
                            $this->transfer_data[] = [
                                $rs['pro_num'], $user_name, $user_name, TransferBalance::TypeProToBal, $pid, 0, $userModel->group_id, $userModel->group_id, $time, $this->_mgrName
                            ];

                            //写产品流水
                            $billsData = [
                                'user_name' => $user_name,
                                'target_id' => $pid,
                                'change_amount' => $rs['pro_num'],
                                'before_amount' => $rs['pro_balance'],
                                'before_balance' => $balance_pre,
                            ];
                            $this->on(Bills::PRODUCT_REFUND, ['center\modules\financial\models\Bills', 'billsRecord'], $billsData);
                            $this->trigger(Bills::PRODUCT_REFUND);
                            $this->off(Bills::PRODUCT_REFUND);
                        }
                    }
                    if ($is_refund_package) {
                        //获取该产品能退的套餐
                        $canRefundPackage = $this->getUserCanDeletePackage($userModel->user_id, $pid, $user_name, $userModel->group_id, $balance_pre);

                        if ($canRefundPackage != 200) {
                            //这表示有退套餐记录
                            if (!empty($canRefundPackage['msg'])) {
                                $package_ref_num += $canRefundPackage['package_num'];
                                $package_total .= $canRefundPackage['desc'];
                                $logContent = Yii::t('app', 'batch help4', [
                                    'mgr' => $this->_mgrName,
                                    'name' => $proName,
                                    'package' => $canRefundPackage['package'],
                                    'num' => $canRefundPackage['package_num']
                                ]); //转账金额
                                $this->writeLog($user_name, $logContent, 'refund');
                            }

                        } else {
                            $array_err[] = [$line, $user_name, $userModel->user_real_name, $userModel->cert_num, $language_failed, $canRefundPackage['msg']];
                        }

                    }
                }
                $refund_num = $pro_ref_num + $balance_pre + $package_ref_num;
                //账户余额
                if ($balance_pre || $pro_ref_num || $package_ref_num) {
                    $this->refund_data[] = [
                        $user_name, $refund_num, 0, 0, 0, 0, 0, $time, $this->_mgrName, Yii::t('app', 'user/batch/excel') . '->' . Yii::t('app', 'refund'), $userModel->group_id
                    ];
                    if ($balance_pre) {
                        $balType = 'subBal';
                        $this->updateBalance($userModel, $balance_pre, $balType);
                    }
                    //写退费日志
                    $logContent = Yii::t('app', 'refund log content', [
                        'mgr' => Yii::$app->user->identity->username,
                        'target' => $user_name,
                        'refund_type' => Yii::t('app', 'batch') . '[' . Yii::t('app', 'batch excel refund_where1') . ']',
                        'refundDetail' => Yii::t('app', 'batch excel help36', ['pro_ref_redail' => $pro_ref_detail, 'package_detail' => $package_total, 'user_balance' => $balance_pre, 'balance' => $pro_ref_num + $package_ref_num + $balance_pre])
                    ]);
                    $this->writeLog($user_name, $logContent);
                    //写电子钱包流水
                    $billsData = [
                        'user_name' => $user_name,
                        'target_id' => $userModel->user_id,
                        'change_amount' => $refund_num,
                        'before_amount' => 0,
                        'before_balance' => $balance_pre,
                    ];
                    $this->on(Bills::WALLET_REFUND, ['center\modules\financial\models\Bills', 'billsRecord'], $billsData);
                    $this->trigger(Bills::WALLET_REFUND);
                    $this->off(Bills::WALLET_REFUND);
                }
                $array_ok[] = [$line, $user_name, $userModel->user_real_name, $userModel->cert_num, Yii::t('app', 'success'), '', $pro_ref_detail, $checkout_num, $package_total, $balance_pre, $pro_ref_num + $balance_pre + $package_ref_num];
            } else {
                $array_err[] = [$line, $user_name, $userModel->user_real_name, $userModel->cert_num, $language_failed, Yii::t('app', 'batch excel help29')];
                continue;
            }
        }

        $this->dbExec(); //处理数据

        return ['code' => 200, 'arr_err' => $array_err, 'arr_ok' => $array_ok];
    }


    /**
     * @param $userModel
     * @param $pid
     * @param $is_refund_product
     * @param $checkout
     *
     * @return array
     */
    public function refundOneProduct($userModel, $pid, $is_refund_product, $checkout = [])
    {
        $rs = [];
        try {
            $gift = self::getGiftModel();
            $oneDetail = $this->getOneOrderedProduct($pid, $userModel->user_id);
            $user_name = $userModel->user_name;
            if (empty($oneDetail)) {
                $rs = ['code' => 404, 'msg' => '产品实例不存在'];
            } else {
                $proBalance = isset($oneDetail['user_balance']) ? $oneDetail['user_balance'] : '0';
                if (empty($proBalance)) {
                    $rs = ['code' => 403, 'msg' => '没有可退余额'];
                } else {
                    if ($proBalance < 0) {
                        //产品欠费
                        $rs = ['code' => 408, 'msg' => '产品已欠费' . $proBalance . Yii::t('app', '$')];
                    } else {
                        $gift_num = $gift->getGiftedNum($user_name, $pid);
                        $can_refund_num = $proBalance * 10000 / 10000 - $gift_num;
                        if ($can_refund_num > 0.1) {
                            $res = $this->getTransferNum($userModel, $pid, $can_refund_num, $is_refund_product, $oneDetail, $checkout, $gift_num);
                            if (!$res) {
                                $rs = ['code' => 409, 'msg' => $this->message_err];
                            } else {
                                $transfer_num = $res['transfer_num'];
                                $checkout_num = $res['checkout_num'];
                                $rs = ['code' => 200, 'pro_num' => $transfer_num, 'checkout_num' => $checkout_num, 'pro_balance' => $proBalance, 'gift' => $gift_num, 'detail' => $oneDetail];
                            }
                        } else {
                            $rs = ['code' => 405, 'msg' => '余额均为赠送不可退'];
                        }
                    }
                }
            }

        } catch (\Exception $e) {
            $rs = ['code' => 500, 'msg' => '结算产品发生异常:' . $e->getMessage()];
        }

        return $rs;

    }

    /**
     * 系统结算
     * @param $userData
     * @param $fields
     * @param $getType
     *
     * @return array
     */
    public function refundToSystem($userData, $fields, $getType)
    {
        try {
            $this->getMgrName();
            $language_failed = Yii::t('app', 'failed');
            $array_err = $array_ok = [];
            foreach ($userData as $key => $data) {
                // 把数据组合成如下形式：array('user_login_name'=>'test1','user_password_ori'=>'111')
                if ($getType == 1) {
                    $line = $key + 1;
                    foreach ($fields as $k => $field) {
                        $user[$field] = trim($data[$k]);
                    }
                    $user_name = $user['user_name'];
                    if (!empty($user_name)) {
                        $userModel = Users::findOne(['user_name' => $user_name]);
                        if (!$userModel) {//用户不存在
                            $array_err[] = [$line, $user_name, '', '', $language_failed, Yii::t('app', 'batch excel help15')];
                            continue;
                        }
                    } else { //用户名为空
                        $array_err[] = [$line, '', '', '', $language_failed, Yii::t('app', 'batch excel help12')];
                        continue;
                    }
                    //判断组织结构和产品是否可用
                    //判断组织结构
                    if (!array_key_exists($userModel->group_id, $this->can_group)) {
                        $array_err[] = [$line, $user_name, $userModel->user_real_name, $userModel->cert_num, $language_failed, Yii::t('app', 'message 401 3')];
                        continue;
                    }
                } else {
                    $user_name = $data['user_name'];
                    $line = $key - 1;
                    $userModel = Users::findOne(['user_name' => $user_name]);
                }
                //判断产品
                $checkout_num = $pro_ref_num = $package_ref_num = 0;
                $pro_ref_detail = $package_total = '';
                $balance_pre = $userModel->balance;
                $time = time();
                if ($userModel->products_id) {
                    foreach ($userModel->products_id as $pid) {
                        $proName = $pid . ':' . $this->products[$pid]['products_name'];
                        if (!array_key_exists($pid, $this->can_product)) {
                            $array_err[] = [$line, $user_name, $userModel->user_real_name, $userModel->cert_num, $language_failed, $proName . Yii::t('app', 'message 401 4')];
                        } else {
                            $rs = $this->refundOneProduct($userModel, $pid, 0);
                            if ($rs['code'] != 200) {
                                $array_err[] = [$line, $user_name, $userModel->user_real_name, $userModel->cert_num, $language_failed, $proName . $rs['msg']];
                            } else {
                                $detail = $rs['detail'];
                                $bytes = isset($detail['bytes']) ? $detail['bytes'] : 0;
                                $times = isset($detail['sum_seconds']) ? $detail['sum_seconds'] : 0;
                                $used = isset($detail['sum_times']) ? $detail['sum_times'] : 0;
                                $checkout_num += $rs['pro_num'];

                                //如果用户在线,进行虚拟下线后把流量时长写入结算表
                                $onlineInfo = KernelInterface::onlineInfo($user_name, 1);
                                $onlineInfo = json_decode($onlineInfo, true);
                                if (!empty($onlineInfo)) {
                                    $bytes += $onlineInfo['sum_bytes'];
                                    $times += $onlineInfo['sum_seconds'];
                                    $used += $onlineInfo['sum_times'];
                                }

                                $this->checkout_data[] = [
                                    $user_name, $pid, 0, $rs['pro_num'], 0, $bytes, $times, $used, 0, $userModel->group_id, $time, $userModel->user_id
                                ];
                                $pro_ref_detail .= $proName . ':' . $rs['pro_num'] . Yii::t('app', 'currency') . " ";
                                //记录日志，单个产品退费记录
                                $this->product_data[] = [
                                    'action' => 3,
                                    'serial_code' => time() . rand(111111, 999999),
                                    'time' => time(),
                                    'proc' => 'admin',
                                    'user_name' => $user_name,
                                    'amount' => floatval(-$rs['pro_num']),
                                    'products_id' => $pid,

                                ];
                                //记录产品结算
                                $logContent = Yii::t('app', 'batch help2', [
                                    'mgr' => $this->_mgrName,
                                    'proName' => $proName,
                                    'gift' => $rs['gift'],
                                    'pro_balance' => $rs['pro_balance'],
                                    'num' => $rs['pro_num'],
                                    'new' => $rs['pro_balance'] - $rs['pro_num'] + $rs['gift']
                                ]);
                                $this->writeLog($user_name, $logContent, 'checkout');
                                // KernelInterface::updateproductBal($user_name, $pid, -
                            }
                        }
                    }
                    //账户余额
                    if ($balance_pre) {
                        $this->refund_data[] = [
                            $user_name, $balance_pre, 0, 0, 0, 0, 0, $time, $this->_mgrName, Yii::t('app', 'user/batch/excel') . '->' . Yii::t('app', 'refund'), $userModel->group_id
                        ];
                        if ($balance_pre) {
                            $balType = 'subBal';
                            $this->updateBalance($userModel, $balance_pre, $balType);
                        }
                        //写电子钱包流水
                        $billsData = [
                            'user_name' => $user_name,
                            'target_id' => $userModel->user_id,
                            'change_amount' => $balance_pre,
                            'before_amount' => 0,
                            'before_balance' => $balance_pre,
                        ];
                        $this->on(Bills::WALLET_REFUND, ['center\modules\financial\models\Bills', 'billsRecord'], $billsData);
                        $this->trigger(Bills::WALLET_REFUND);
                        $this->off(Bills::WALLET_REFUND);
                    }
                    //写退费日志
                    $logContent = Yii::t('app', 'refund log content', [
                        'mgr' => Yii::$app->user->identity->username,
                        'target' => $user_name,
                        'refund_type' => Yii::t('app', 'batch') . '[' . Yii::t('app', 'batch excel refund_where2') . ']',
                        'refundDetail' => Yii::t('app', 'batch excel help36', ['pro_ref_redail' => $pro_ref_detail, 'package_detail' => $package_total, 'user_balance' => $balance_pre
                        ])
                    ]);
                    $this->writeLog($user_name, $logContent, 'refund');
                    $array_ok[] = [$line, $user_name, $userModel->user_real_name, $userModel->cert_num, Yii::t('app', 'success'), '', $pro_ref_detail, $checkout_num, $package_total, $balance_pre, $balance_pre];
                } else {
                    $array_err[] = [$line, $user_name, $userModel->user_real_name, $userModel->cert_num, $language_failed, Yii::t('app', 'batch excel help29')];
                    continue;
                }
            }
            $this->dbExec();

            $rs = ['code' => 200, 'arr_err' => $array_err, 'arr_ok' => $array_ok];

        } catch (\Exception $e) {
            $rs = ['code' => 500, 'msg' => '批量结算发生异常'];
        }

        return $rs;
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

    /**
     * 获取各产品结算设置
     * @param $products
     * @return array
     */
    public function getCheckoutSettings($products)
    {
        if (empty($products)) {
            return [];
        } else {
            $checkoutSetModel = new CheckoutSetting();
            $checkout = [];
            $all = $checkoutSetModel->getOne('all');
            foreach ($products as $id => $product) {
                $one = $checkoutSetModel->getOne($id);
                $checkout[$id] = empty($one) ? $all : $one;
            }

            return $checkout;
        }
    }

    /**
     * 获取用户单个产品结算参数
     * @param $temp 产品模板
     * @param $product 产品实例
     * @param $checkout 结算设置
     * @param $user_name 用户名
     * @param $user_available 用户状态
     * @param integer $discount 折扣
     *
     * @return bool|array
     */
    public function getOneProductCheckoutParams($temp, $product, $checkout, $user_name, $user_available, $discount = 1)
    {
        //结算金额  已用流量  已用时长  上线次数
        $checkout_amount = $sum_bytes = $sum_seconds = $sum_times = $refund_amount = 0;
        $user_balance = isset($product['user_balance']) ? $product['user_balance'] : 0;
        $user_charge = isset($product['user_charge']) ? $product['user_charge'] : 0;
        $temp_checkout = isset($temp['checkout_amount']) && $temp['checkout_amount'] > 0 ? $temp['checkout_amount'] : 0; //模板结算余额
        $checkout_amount += $temp_checkout;
        //产品实例的阶梯收费
        $checkout_amount += isset($products['checkout_amount']) && $products['checkout_amount'] > 0 ? $products['checkout_amount'] : 0;
        //流量
        $sum_bytes += $product['sum_bytes'];
        //时长
        $sum_seconds += $product['sum_seconds'];
        //上线次数
        $sum_times += $product['sum_times'];
        //如果用户在线,进行虚拟下线后把流量时长写入结算表
        $onlineInfo = KernelInterface::onlineInfo($user_name, 1);
        $onlineInfo = json_decode($onlineInfo, true);
        if (!empty($onlineInfo)) {
            $sum_bytes += $onlineInfo['sum_bytes'];
            $sum_seconds += $onlineInfo['sum_seconds'];
            $sum_times += $onlineInfo['sum_times'];
        }


        //根据结算设置来计算结算金额
        if (!empty($checkout)) {
            //使用流量和 使用时长 必须同时在 免费值内 才可以 结算金额为0
            if ((isset($checkout['flux_limit']) && ($checkout['flux_limit'] * Tool::TRAFFIC_CARRY * Tool::TRAFFIC_CARRY) > $sum_bytes) && (isset($checkout['time_limit_value']) && $checkout['time_limit_value'] > $sum_seconds)) {
                $checkout_amount = 0;
                self::addLog('4', $user_name, 'E7009', $this->_mgrName);
                self::addLog('4', $user_name, 'E7010', $this->_mgrName);
            }

            //本周期未上网过的禁用的用户是否扣费
            if ($user_available == 1 && $sum_bytes == 0 && $sum_seconds == 0 && isset($checkout['user_available_limit_noauth']) && $checkout['user_available_limit_noauth'] == 1) {
                $checkout_amount = 0;
                self::addLog('4', $user_name, 'E7022', $this->_mgrName);
            } //本周期上网过的禁用的用户是否扣费
            elseif ($user_available == 1 && ($sum_bytes > 0 || $sum_seconds > 0) && isset($checkout['user_available_limit']) && $checkout['user_available_limit'] == 1) {
                //还要考虑免费的流量 和时长
                if (($checkout['flux_limit'] * Tool::TRAFFIC_CARRY * Tool::TRAFFIC_CARRY > $sum_bytes) || ($checkout['time_limit_value'] > $sum_seconds)) {
                    $checkout_amount = 0;
                    self::addLog('4', $user_name, 'E7019', $this->_mgrName);
                }
            } //本周期未上网过的暂停的用户是否扣费
            elseif ($user_available == 3 && $sum_bytes == 0 && $sum_seconds == 0 && isset($checkout['user_available_3_noauth']) && $checkout['user_available_3_noauth'] == 1) {
                $checkout_amount = 0;
                self::addLog('4', $user_name, 'E7024', $this->_mgrName);
            } //本周期上网过的暂停的用户是否扣费
            elseif ($user_available == 3 && ($sum_bytes > 0 || $sum_seconds > 0) && isset($checkout['user_available_3_auth']) && $checkout['user_available_3_auth'] == 1) {
                //还要考虑免费的流量 和时长
                if (($checkout['flux_limit'] * Tool::TRAFFIC_CARRY * Tool::TRAFFIC_CARRY > $sum_bytes) || ($checkout['time_limit_value'] > $sum_seconds)) {
                    $checkout_amount = 0;
                    self::addLog('4', $user_name, 'E7023', $this->_mgrName);
                }
            } else {
                //余额不足的话不扣费
                if (isset($checkout['status_limit']) && $checkout['status_limit'] == 1 && $checkout_amount > $user_balance) {
                    $checkout_amount = 0;
                    self::addLog('4', $user_name, 'E7011');
                }
                //余额不足的话把余额扣为0
                if (isset($checkout['status_limit']) && $checkout['status_limit'] == 2 && $checkout_amount > $user_balance) {
                    if ($discount < 1) {
                        $checkout_amount = $checkout_amount * $discount <= $user_balance ? $checkout_amount * $discount : $user_balance;
                        $discount = 1;
                    } else {
                        $checkout_amount = $user_balance;
                    }
                    self::addLog('4', $user_name, 'E7012', $this->_mgrName);
                }
                //余额不足的话 直接扣(可以扣为负值)
                if (isset($checkout['status_limit']) && $checkout['status_limit'] == 0 && $checkout_amount > $user_balance) {
                    self::addLog('4', $user_name, 'E7013', $this->_mgrName);
                }

                //默认 停机保号，未开通用户不扣费
                if ($user_available == 2 || $user_available == 4) {
                    $checkout_amount = 0;
                    self::addLog('4', $user_name, 'E7025', $this->_mgrName);
                }
            }

        } else {
            //如果没有进行结算设置，那么默认 停机保号，未开通 不扣费，未使用的 禁用和暂停用户不扣费，扣费金额最多扣为0
            if ($user_available == 2 || $user_available == 4 || ($sum_bytes == 0 && $sum_seconds == 0 && ($user_available == 1 || $user_available == 3))) {
                $checkout_amount = 0;
                self::addLog('4', $user_name, 'E7025', $this->_mgrName);
            }
        }
        $checkout_amount = $checkout_amount * $discount;//结算金额乘以折扣

        return [
            'checkout_amount' => floor($checkout_amount * 10000) / 10000,
            'sum_bytes' => $sum_bytes,
            'sum_seconds' => $sum_seconds,
            'sum_times' => $sum_times,
            'checkout_mode' => $temp['checkout_mode'],
            'pro_user_balance' => floor($user_balance * 10000) / 10000,
            'pro_user_charge' => floor($user_charge * 10000) / 10000,
            'checkout_setting' => $checkout
        ];
    }

    /**
     * 根据用户id获取该用户可退费的套餐
     * @param $user_id //用户id
     * @param $pid //产品id
     * @param $user_name //用户名
     * @param $group_id
     * @param $balance_pre
     *
     * @return array 删除的套餐id，以及套餐结算金额，需要删除的结算数据
     */
    public function getUserCanDeletePackage($user_id, $pid, $user_name, $group_id, $balance_pre)
    {
        $rs = [];
        try {
            $packageModel = self::getPackageModel();
            $packages = $packageModel->getOneByUidAndPid($user_id, $pid);
            $amount = 0;
            $msg = '';
            //var_dump($packages);exit;
            if ($packages) {
                $detail = $packages['detail'];
                foreach ($detail as $key => $one) {
                    if (((isset($one['expire_time']) && $one['expire_time'] > time()) || $one['expire_time'] == 0) && $one['usage_rate'] == 1) {
                        $this->del_packages[] = [
                            'action' => 10, //10表示删除套餐实例
                            'serial_code' => time() . rand(111111, 999999), //唯一的流水号
                            'time' => time(),
                            'user_name' => $user_name,
                            'products_id' => $pid, //产品id
                            'proc' => 'admin',
                            'user_name' => $user_name,
                            'user_package_id' => $one['user_package_id'],
                            'package_id' => $one['package_id'],
                        ];
                        $logContent = Yii::t('app', 'batch help9', [
                            'mgr' => $this->_mgrName,
                            'package' => $one['package_id'].':'.$one['package_name'],
                            'static' => $one['user_package_id'],
                            'num' => $one['amount']
                        ]);
                        $this->writeLog($user_name, $logContent, 'refund');
                        $this->del_checkout[] = $one['checkout_time'];
                        $amount += $one['amount'];
                        //['transfer_num', 'user_name_from', 'user_name_to', 'type', 'product_id', 'package_id', 'group_id', 'group_id_from', 'create_at', 'mgr_name'];
                        $this->transfer_data[] = [
                            $one['amount'], $user_name, $user_name, TransferBalance::TypeProToBal, $pid, $one['package_id'], $group_id, $group_id, time(), $this->_mgrName
                        ]; //套餐转钱包
                        $msg .= $one['package_name'] . ':' . $one['amount'] . Yii::t('app', 'currency') . ";";

                        //写取消套餐流水
                        $billsData = [
                            'user_name' => $user_name,
                            'target_id' => $pid,
                            'change_amount' => $one['amount'],
                            'before_amount' => 0,
                            'before_balance' => $balance_pre,
                        ];
                        $this->on(Bills::PACKAGE_CANCEL, ['center\modules\financial\models\Bills', 'billsRecord'], $billsData);
                        $this->trigger(Bills::PACKAGE_CANCEL);
                        $this->off(Bills::PACKAGE_CANCEL);
                    }
                }
            }
            $rs = ['code' => 200, 'msg' => 'ok', 'package_num' => $amount, 'desc' => $msg, 'package' => $one['package_id'].':'.$one['package_name']];

        } catch (\Exception $e) {
            $rs = ['code' => 500, 'msg' => '获取可结算套餐异常'];
        }

        return $rs;
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

    /**
     * 结算某个产品
     * @param $user_id
     * @param $products_id
     * @param $checkoutParams
     * @param $user
     * @param $gift
     * @return bool
     * @throws yii\db\Exception
     */
    public function checkoutOne($user_id, $products_id, $checkoutParams, $user, $gift)
    {
        if ($checkoutParams) {
            $checkout_amount = $checkoutParams['checkout_amount'];
            $sum_bytes = $checkoutParams['sum_bytes'];
            $sum_seconds = $checkoutParams['sum_seconds'];
            $sum_times = $checkoutParams['sum_times'];
            $pro_user_balance = $checkoutParams['pro_user_balance'];
            $pro_user_charge = $checkoutParams['pro_user_charge'];
            if ($checkoutParams['checkout_mode'] >= 1 && $checkoutParams['checkout_mode'] <= 34) {
                //根据结算设置 判断是否 清除套餐
                try {
                    $time = time();
                    //通过接口对产品进行结算
                    $this->product_data[] = [
                        'action' => 8,
                        'serial_code' => time() . rand(111111, 999999),
                        'time' => $time,
                        'proc' => 'admin',
                        'user_name' => $user['user_name'],
                        'amount' => -$checkout_amount,
                        'products_id' => $products_id,
                    ];
                    //写记录
                    $logContent = Yii::t('app', 'batch help2', [
                        'mgr' => $this->_mgrName,
                        'proName' => $products_id.':'.$this->products[$products_id]['products_name'],
                        'gift' => $gift,
                        'pro_balance' => $pro_user_balance,
                        'num' => $checkout_amount,
                        'new' => $pro_user_balance - $checkout_amount + $gift
                    ]);
                    $this->writeLog($user->user_name, $logContent, 'checkout');
                    //记录产品结算

                    $this->checkout_data[] = [
                        'user_name' => $user['user_name'],
                        'product_id' => $products_id,
                        'buy_id' => 0,
                        'spend_num' => $checkout_amount,
                        'rt_spend_num' => $pro_user_charge,
                        'flux' => $sum_bytes,
                        'minutes' => $sum_seconds,
                        'sum_times' => $sum_times,
                        'type' => 0, //消费
                        'group_id' => $user['group_id'],
                        'create_at' => $time,
                        'user_id' => $user['user_id']
                    ];
                    //写余额流水
                    $billsData = [
                        'user_name' => $user['user_name'],
                        'target_id' => $products_id,
                        'change_amount' => $checkout_amount,
                        'before_amount' => $pro_user_balance,
                        'before_balance' => $user['balance'],
                    ];
                    $this->on(Bills::PRODUCT_SETTLEMENT, ['center\modules\financial\models\Bills', 'billsRecord'], $billsData);
                    $this->trigger(Bills::PRODUCT_SETTLEMENT);
                    $this->off(Bills::PRODUCT_SETTLEMENT);
                    self::addLog('4', $user['user_name'], 'E7003', $this->_mgrName);

                    return true;
                } catch (\Exception $e) {

                    return false;
                }
            } else {
                self::addLog('4', $user_id, 'E7018', $this->_mgrName);
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 获取转账金额跟结算金额
     * @param $userModel
     * @param $pid
     * @param $can_refund_num
     * @param $is_refund_product
     * @param $oneDetail
     * @param $checkout
     * @param $gift_num
     * @return array|bool
     */
    public function getTransferNum($userModel, $pid, $can_refund_num, $is_refund_product, $oneDetail, $checkout, $gift_num = 0)
    {
        if ($is_refund_product) {
            $products = $this->products[$pid];
            $user_name = $userModel->user_name;
            $user_avail = $userModel->user_available;
            $checkParams = $this->getOneProductCheckoutParams($products, $oneDetail, $checkout, $user_name, $user_avail);
            $checkParams['pro_user_balance'] = $can_refund_num;
            //结算单个产品
            $checkRes = $this->checkoutOne($userModel->user_id, $pid, $checkParams, $userModel, $gift_num);
            if (!$checkRes) {
                $this->message_err = Yii::t('app', 'checkout') . Yii::t('app', 'failed');

                return false;
            }
            $transfer_num = $can_refund_num - $checkParams['checkout_amount'];
            $checkout_num = $checkParams['checkout_amount'];
        } else {
            $transfer_num = $can_refund_num; //写转账记录
        }

        return ['transfer_num' => $transfer_num, 'checkout_num' => $checkout_num];
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

    /**
     * @param $proc 模块
     * @param $user_name
     * @param $err_msg
     * $param $mgr_name
     * @return mixed
     */
    public static function addLog($proc, $user_name, $err_msg, $mgr_name)
    {
        $data_list = [
            'proc' => $proc,
            'log_time' => time(),
            'user_name' => $user_name,
            'user_ip' => '',
            'my_ip' => '',
            'user_mac' => '',
            'nas_port_id' => '',
            'err_msg' => $err_msg,
            'sysmgr_name' => $mgr_name,
        ];
        $json = json_encode($data_list);
        return Redis::executeCommand('RPUSH', 'list:log', [$json], 'redis_log');
    }

    /**
     * 处理产品套餐实例，如果套餐实例过期就删除掉，有自动购买套餐则购买
     * @param $uid
     * @param $user_name
     * @param $product_id_old
     * @param $product_id_new
     */
    private function operatePackage($uid, $user_name, $product_id_old, $product_id_new)
    {
        $model = self::getPackageModel();
        $time = time();
        $list = $model->getPackageObjList($uid, $product_id_old);
        if ($list) {
            $packages_id = [];
            foreach ($list as $one) {
                if ($one) {
                    //获取套餐实例
                    $hash = $model->getOnePackageObj($uid, $product_id_old, $one);
                    if (isset($hash['expire_time']) && $hash['expire_time'] > 0 && $hash['expire_time'] < $time) {//如果过期则删除
                        $del_res = $model->delPackageObj($user_name, $product_id_old, $hash['package_id'], $hash['user_package_id']);
                        if ($del_res) {
                            self::addLog('4', $user_name, 'E7014', $this->_mgrName);
                        } else {
                            self::addLog('4', $user_name, 'E7015', $this->_mgrName);
                        }
                        continue;//有过期的不能再自动购买
                    }
                    if (isset($hash['auto_buy']) && $hash['auto_buy'] == 1) {//如果有自动购买套餐则订购
                        //如果没有购买此套餐，才能购买，否则会买重复
                        if (!in_array($hash['package_id'], $packages_id)) {
                            $buy_res = $model->buyPackage($user_name, $product_id_new, $hash['package_id']);
                            if ($buy_res) {
                                //购买成功后 记录一下package_id
                                $packages_id[] = $hash['package_id'];
                                self::addLog('4', $user_name, 'E7016', $this->_mgrName);
                            } else {
                                self::addLog('4', $user_name, 'E7017', $this->_mgrName);
                            }
                        }
                    }
                    //套餐用完的要删除，包括流量，时长，次数
                    if ($hash['remain_bytes'] <= 0 && $hash['remain_seconds'] <= 0 && (!isset($hash['remain_times']) || (isset($hash['remain_times']) && $hash['remain_times'] <= 0))) {
                        $del_res = $model->delPackageObj($user_name, $product_id_old, $hash['package_id'], $hash['user_package_id']);
                        if ($del_res) {
                            self::addLog('4', $user_name, 'E7020');
                        } else {
                            self::addLog('4', $user_name, 'E7021');
                        }
                    }
                }
            }
        }
    }

    /**
     * 进行数据处理
     * @return bool
     * @throws yii\db\Exception
     */
    public function dbExec()
    {
        $db = Yii::$app->db;
        $trans = $db->beginTransaction();
        try {
            //先处理mysql，在推送消息队列
            if (!empty($this->checkout_data)) {
                $db->createCommand()->batchInsert(
                    CheckoutList::tableName(),
                    ['user_name', 'product_id', 'buy_id', 'spend_num', 'rt_spend_num', 'flux', 'minutes', 'sum_times', 'type', 'group_id', 'create_at', 'user_id'],
                    $this->checkout_data
                )->execute();
            }
            if (!empty($this->refund_data)) {
                $db->createCommand()->batchInsert(
                    RefundList::tableName(),
                    ['user_name', 'refund_num', 'type', 'product_id', 'is_refund_fee', 'extra_id', 'package_id', 'create_at', 'mgr_name', 'remarks', 'group_id'],
                    $this->refund_data
                )->execute();
            }
            if (!empty($this->product_data)) {
                foreach ($this->product_data as $one) {
                    $json = [json_encode($one)];
                    Redis::executeCommand('RPUSH', 'list:interface', $json);
                }
            }
            if (!empty($this->transfer_data)) {
                // var_dump($this->transfer_data, $this->refund_data);exit;
                $db->createCommand()->batchInsert(
                    TransferBalance::tableName(),
                    ['transfer_num', 'user_name_from', 'user_name_to', 'type', 'product_id', 'package_id', 'group_id', 'group_id_from', 'create_at', 'mgr_name'],
                    $this->transfer_data
                )->execute();

                if (!empty($this->del_packages)) {
                    //删除结算记录
                    CheckoutList::deleteAll(['id' => $this->del_checkout]);
                    //删除套餐实例
                    foreach ($this->del_packages as $one) {
                        $json = [json_encode($one)];
                        Redis::executeCommand('RPUSH', 'list:interface', $json);
                    }
                }
            }

            $trans->commit();

            return true;
        } catch (\Exception $e) {
            echo $e->getMessage();
            echo $e->getLine();
            $trans->rollBack();

            return false;
        }

    }

    /**
     * 记录日志
     * @param $user_name
     * @param $logContent
     * @param string $type
     * @return bool
     */
    public function writeLog($user_name, $logContent, $type = 'refund')
    {
        $logWrite = self::getWriteLogModel();
        $logWrite->oldAttributes = null;
        $logWrite->id = null;
        if ($type == 'refund') {
            $action_type = 'Financial RefundList';
        } else if ($type == 'checkout') {
            $action_type = 'Financial WaitCheck';
        } else if ($type == 'trans') {
            $action_type = 'Financial Trans';
        }
        $data = [
            'operator' => $this->_mgrName,
            'target' => $user_name,
            'action' => $type,
            'action_type' => $action_type,
            'content' => $logContent,
            'class' => __CLASS__,
            'type' => 1,
        ];
        $ip = method_exists(Yii::$app->getRequest(), 'getUserIP') ? Yii::$app->getRequest()->getUserIP() : isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
        if(!isset($data['opt_ip'])) $data['opt_ip'] = $ip;
        if(!isset($data['opt_time'])) $data['opt_time'] = time();
        foreach ($data as $var => $value) {
            if ($logWrite->hasAttribute($var)) {
                $logWrite->$var = $value;
            }
        }
        if ($logWrite->save(false)) {
            return true;
        } else {
            return false;
        }

        return true;
    }
}
