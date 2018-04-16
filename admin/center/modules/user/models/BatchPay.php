<?php
/**
 * 批量购买
 * Created by PhpStorm.
 * User: wjh
 * Date: 2017/7/19
 * Time: 15:16
 */

namespace center\modules\user\models;

use center\extend\Tool;
use center\modules\financial\models\Bills;
use center\modules\financial\models\PayType;
use center\modules\strategy\models\Product;
use common\models\KernelInterface;
use common\models\Redis;
use center\modules\log\models\LogWriter;
use center\modules\log\models\Operate;
use yii;

class BatchPay extends yii\db\ActiveRecord
{
    //const SETTLE_ACCOUNT_LIMIT  = 3000;
    //购买对象
    public $buyObject = 'package'; //默认购买套餐
    public $pay_type;

    //退费时是否退未使用的套餐
    public $isRefundPackages;
    public $needPayTotalNum;
    public $userModel;
    public $message_ok;
    public $message_err;
    public $useBalance;
    public $transfer_data;
    public $pay_data;
    public $useTotalBalance;
    public $payTotalNum;
    public $payListId;
    public $pay_type_name;
    public static $write_log;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%refund_list}}';
    }

    public static function getWriteLogModel()
    {
        if (self::$write_log == '') {
            self::$write_log = new Operate();
        }

        return self::$write_log;
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
     * 购买产品
     * @param $data
     * @return bool
     */
    public function payProduct($data)
    {
        if (empty($data)) {
            return false;
        }
        $this->payTotalNum = 0;
        foreach ($data as $pid => $num) {
            if (!is_numeric($num)) {
                $this->message_err[] = ['缴费金额不为数字'];

                return false;
            }
            $rs = $this->payOneProduct($pid, $num);
        }
        $this->writeLog();

        return $rs;
    }

    /**
     * 购买单个产品
     * @param $id
     * @param $num
     * @return bool
     */
    public function payOneProduct($id, $num)
    {
        $pay_num = $num;
        $transfer_num = 0; //需要用电子钱包的金额
        $balance = $this->userModel->balance; //用户新余额
        //产品名称
        $products = $this->userModel->products;
        $products_name = $products[$id]['products_name'];
        $user_name = $this->userModel->user_name;
        $user_real_name = $this->userModel->user_real_name ? $this->userModel->user_real_name : '';
        $group_id = $this->userModel->group_id;
        $time = time();
        if ($this->useBalance) {
            if ($balance >= $num) {
                $transfer_num = $num;
            } else {//电子钱包余额不足 不许缴费
                $this->message_err[] = Yii::t('app', 'pay help13', ['pay_num' => $num, 'balance_num' => $balance]);

                return false;
            }
        } else {//不用电子钱包充值，那就先充进电子钱包（不进账，只写记录）再转账
            //写缴费记录
            $data = [
                'user_name' => $user_name,
                'user_real_name' => $user_real_name,
                'pay_num' => $num,
                'group_id' => $group_id,
                'product_id' => $id,
                'package_id' => 0,
                'pay_type_id' => $this->pay_type,
                'mgr_name' => $this->_mgrName,
                'order_no' => '',
                'balance_pre' => $this->userModel->balance,
                'bill_number' => date('YmdHis') . str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT),
                'create_at' => $time
            ];
            $this->pay_data[] = $data;
        }

        //转账
        $this->transfer_data[] = [
            'user_name_from' => $user_name,
            'user_name_to' => $user_name,
            'product_id' => $id,
            'package_id' => 0,
            'transfer_num' => $num,
            'mgr_name' => $this->_mgrName,
            'extra_pay_id' => 0,
            'type' => 0,
            'group_id' => $group_id,
            'group_id_from' => $group_id,
            'create_at' => $time
        ];

        $this->payTotalNum += $num - $transfer_num; //需要缴纳的金额

        //如果用户余额变了，
        if ($transfer_num > 0) {
            $this->useTotalBalance += $transfer_num; //加到使用的总余额上
            //更新用户
            $this->userModel->balance -= $transfer_num;
            $this->userModel->save(false);
        }

        //产品缴费后更新欠费日志，如果产品新余额>产品月费，更改状态
        $this->changeArrearsLog($id);

        //根据是否使用余额写不同的日志
        if ($transfer_num > 0) {
            //使用了余额后的日志
            $this->message_ok[] = Yii::t('app', 'pay product message ok use balance', [
                'product_name' => $id . ':' . $products_name,
                'num' => $num,
                'useBalanceNum' => $transfer_num,
                'pay_num' => $pay_num,
            ]);
        } else {
            $this->message_ok[] = Yii::t('app', 'pay product message ok', [
                'product_name' => $id . ':' . $products_name,
                'num' => $num,
            ]);
        }
        Product::addRechargeTimes($id);

        //消息触发通知用户
        $pay_type = $this->pay_type_name;
        $detail = $this->getOneOrderedProduct($id, $this->userModel->user_id);
        $pro_balance = isset($detail['user_balance']) ? $detail['user_balance'] + $num : $num;
        $this->payProductNotice($this->userModel->user_name, $products_name, $pay_type, $num, $pro_balance, $balance);

        //写余额流水
        $billsData = [
            'user_name' => $this->userModel->user_name,
            'target_id' => $id,
            'change_amount' => $num,
            'before_amount' => Product::getProductBalance($this->userModel->user_id, $id),
            'before_balance' => $balance,
        ];

        $this->on(Bills::PRODUCT_RECHARGE, ['center\modules\financial\models\Bills', 'billsRecord'], $billsData);
        $this->trigger(Bills::PRODUCT_RECHARGE);
        $this->off(Bills::PRODUCT_RECHARGE);

        return true;
    }


    /**
     * 购买套餐
     * @param $data
     * @param $packages
     * @return bool
     */
    public function payPackage($data, $packages)
    {
        if (empty($data)) {
            return false;
        }
        $this->payTotalNum = 0;
        foreach ($data as $pid => $package) {
            foreach ($package as $package_id) {
                //双层循环购买
                $res = $this->payPackageOne($pid, $package_id, $packages[$package_id]);
                $this->payTotalNum += $packages[$package_id]['amount'];
            }
        }
        $this->writeLog();

        return true;
    }

    /**
     * 购买单个套餐
     * @param $pid
     * @param $package_id
     * @param $package
     * @param $extends
     * @return bool
     */
    public function payPackageOne($pid, $package_id, $package, $extends = [])
    {
        //获取套餐的信息
        //产品名称
        $product_name = $this->userModel->products[$pid]['products_name'];
        $num = $package['amount']; //此套餐的金额
        $pay_num = $num; //需要充值的金额
        $balance = $this->userModel->balance; //用户新余额
        $transfer_num = 0;//用电子钱包金额
        $user_name = $this->userModel->user_name;
        $user_real_name = $this->userModel->user_real_name ? $this->userModel->user_real_name : '';
        $group_id = $this->userModel->group_id;
        $time = time();
        //如果是用电子钱包来充值产品，那么只写转账记录，否则写缴费记录和转账记录
        if ($this->useBalance) {
            if ($balance >= $num) {
                $transfer_num = $num;
                $pay_num = 0;
            } else {//电子钱包余额不足 不许缴费
                $this->message_err[] = Yii::t('app', 'pay help13', ['pay_num' => $num, 'balance_num' => $balance]);
                return false;
            }
        } else {//不用电子钱包充值，那就先充进电子钱包（不进账，只写记录）再转账
            //写缴费记录
            $data = [
                'user_name' => $user_name,
                'user_real_name' => $user_real_name,
                'pay_num' => $num,
                'group_id' => $group_id,
                'product_id' => $pid,
                'package_id' => $package_id,
                'pay_type_id' => $this->pay_type,
                'mgr_name' => $this->_mgrName,
                'order_no' => '',
                'balance_pre' => $this->userModel->balance,
                'bill_number' => date('YmdHis') . str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT),
                'create_at' => $time
            ];
            $this->pay_data[] = $data;
        }
        $this->transfer_data[] = [
            'user_name_from' => $user_name,
            'user_name_to' => $user_name,
            'product_id' => $pid,
            'package_id' => $package_id,
            'transfer_num' => $num,
            'mgr_name' => $this->_mgrName,
            'extra_pay_id' => 0,
            'type' => 0,
            'group_id' => $group_id,
            'group_id_from' => $group_id,
            'create_at' => $time
        ];
        //如果用户余额变了，
        if ($transfer_num > 0) {
            $this->useTotalBalance += $transfer_num; //加到使用的总余额上
            //更新用户
            $this->userModel->balance -= $transfer_num;
            $this->userModel->save();
        }
        //加到需要缴纳的金额
        $this->needPayTotalNum += $num - $transfer_num;
        //购买套餐成功
        if ($transfer_num > 0) {
            $this->message_ok[] = Yii::t('app', 'pay package message ok use balance', [
                'product_name' => $pid . ':' . $product_name,
                'package_name' => $package_id . ':' . $package['package_name'],
                'num' => $num,
                'useBalanceNum' => $transfer_num,
                'pay_num' => $pay_num,
            ]);
        } else {
            $this->message_ok[] = Yii::t('app', 'pay package message ok', [
                'product_name' => $pid . ':' . $product_name,
                'package_name' => $package_id . ':' . $package['package_name'],
                'num' => $num,
            ]);
        }

        //写余额流水
        $billsData = [
            'user_name' => $this->userModel->user_name,
            'target_id' => $pid,
            'change_amount' => $num,
            'before_amount' => 0,
            'before_balance' => $balance,
        ];
        $this->on(Bills::PACKAGE_BUY, ['center\modules\financial\models\Bills', 'billsRecord'], $billsData);
        $this->trigger(Bills::PACKAGE_BUY);
        $this->off(Bills::PACKAGE_BUY);

        return true;
    }

    /**
     * 用户绑定套餐的接口
     * @param $id int 产品id
     * @param $pid int 套餐id
     * @param $user_name
     * @param $checkout_id
     * @return mixed
     */
    private function iBuyPackage($id, $pid, $user_name, $checkout_id)
    {
        //用户绑定套餐
        $data = [
            'action' => 5,
            'serial_code' => time() . rand(111111, 999999),
            'time' => time(),
            'proc' => 'admin',
            'user_name' => $user_name,
            'products_id' => $id,
            'package_id' => $pid,
            'checkout_time' => $checkout_id
        ];
        $json = json_encode($data);

        return Redis::executeCommand('RPUSH', 'list:interface', [$json]);
    }

    /**
     * 批量写入转账表|缴费表
     * @param array $packages
     * @param string $type
     * @param bool $flag
     * @return bool
     * @throws yii\db\Exception
     */
    public function batchInsert($packages = [], $type = 'pay_list', $flag = false)
    {
        //批量写入
        $fields = $this->getFields($type); //写入字段
        $table = $this->getTableName($type); //写入table
        $data = $type == 'pay_list' ? $this->pay_data : $this->transfer_data;
        $db =  Yii::$app->db;
        //  var_dump($data, $fields);exit;
        if (!empty($data)) {
            $rs = $db->createCommand()->batchInsert($table, $fields, $data)->execute();
        }
        if ($type == 'transfer') {
            if ($rs) {
                if ($flag) {
                    //写产品队列
                    foreach ($data as $v) {
                        $this->iPayProduct($v['product_id'], $v['product_id'], $v['user_name_from']);
                    }
                } else {
                    //默认购买套餐
                    foreach ($data as $v) {
                        $package = $packages[$v['package_id']];
                        //将费用发到结算接口
                        $checkout_list = [
                            'user_name' => $v['user_name_from'],
                            'product_id' => $v['product_id'],
                            'buy_id' => $v['package_id'],
                            'spend_num' => $v['transfer_num'],
                            'flux' => $package['kbytes'] * Tool::TRAFFIC_CARRY,
                            'minutes' => $package['minutes'] * 60,
                            'sum_times' => 0,
                            'type' => 1, //消费
                            'create_at' => $v['create_at'],
                            'group_id' => $v['group_id']
                        ];
                        $db->createCommand()->insert('checkout_list', $checkout_list)->execute();
                        $id = $db->getLastInsertID();
                        $this->iBuyPackage($v['product_id'], $v['package_id'], $v['user_name_from'], $id);
                       // KernelInterface::addCheckoutedList($checkout_list);
                    }
                }

                return true;
            }

        }
        return false;
    }

    /**
     * 获取写入字段
     * @param $type
     * @return array
     */
    public function getFields($type)
    {
        $fields = [];
        if ($type == 'pay_list') {
            $fields = ['user_name', 'user_real_name', 'pay_num', 'group_id', 'product_id', 'package_id', 'pay_type_id', 'mgr_name', 'order_no', 'balance_pre', 'bill_number', 'create_at'];
        } else if ($type == 'transfer') {
            $fields = ['user_name_from', 'user_name_to', 'product_id', 'package_id', 'transfer_num', 'mgr_name', 'extra_pay_id', 'type', 'group_id', 'group_id_from', 'create_at'];
        }

        return $fields;
    }

    /**
     * 获取表名
     * @param $type
     * @return string
     */
    public function getTableName($type)
    {
        return $type == 'pay_list' ? 'pay_list' : 'transfer_balance';
    }

    /**
     * 产品续费的接口
     * @param $pay_num number 续费金额
     * @param $product_id int 产品id
     * @param $user_name 用户名
     * @return mixed
     */
    private function iPayProduct($pay_num, $product_id, $user_name)
    {
        $data = [
            'action' => 3,
            'serial_code' => time() . rand(111111, 999999),
            'time' => time(),
            'proc' => 'admin',
            'user_name' => $user_name,
            'amount' => $pay_num,
            'products_id' => $product_id,
        ];
        $json = json_encode($data);

        return Redis::executeCommand('RPUSH', 'list:interface', [$json]);
    }

    /**
     * 写缴费日志
     * @return string
     */
    private function writeLog()
    {
        $logWrite = self::getWriteLogModel();
        $logWrite->oldAttributes = null;
        $logWrite->id = null;
        //日志内容
        $logContent = $this->getPayMessage('<br />');

        //写日志
        $data = [
            'operator' => $this->_mgrName,
            'target' => $this->userModel->user_name,
            'action' => 'pay',
            'action_type' => 'Financial Pay',
            'content' => $logContent,
            'class' => get_class($this),
            'type' => 1
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

    /**
     * 获取缴费的日志
     * @param $wrap string 日志组合的连接符
     * @return string
     */
    public function getPayMessage($wrap = '')
    {
        $msg = '';
        //成功的消息
        $msg .= $this->message_ok ? implode($wrap, $this->message_ok) : '';
        //失败的消息
        $msg .= $this->message_err ? implode($wrap, $this->message_err) : '';
        //合计消息
        $msg .= $wrap;
        $msg .= $this->useTotalBalance == 0 ? Yii::t('app', 'pay total num', ['payTotalNum' => $this->payTotalNum]) :
            Yii::t('app', 'pay total num use balance', [
                'needPayTotalNum' => $this->needPayTotalNum,
                'useTotalBalance' => $this->useTotalBalance,
                'payTotalNum' => $this->payTotalNum
            ]);

        //处理完毕，进行消息处理和总金额计算
        $logContent = Yii::t('app', 'pay log content', [
            'mgr' => $this->_mgrName,
            'target' => $this->userModel->user_name,
            'payDetail' => $msg
        ]);

        return $logContent;
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
     * 产品缴费后更新欠费日志，如果产品新余额>产品月费，更改状态
     * @param $product_id int 产品id
     */
    private function changeArrearsLog($product_id)
    {
        //获取此产品的新余额
        $orderedProduct = $this->userModel->getOneOrderedProduct($product_id, $this->userModel->user_id);
        if ($orderedProduct) {
            //获取此产品的月费
            $checkout_amount = Redis::executeCommand('HGET', 'hash:products:' . $product_id, ['checkout_amount']);
            //如果产品新余额大于产品月费，那么更改状态
            if (isset($orderedProduct['user_balance']) && $orderedProduct['user_balance'] >= $checkout_amount) {
                Yii::$app->db->createCommand()->update('srun_arrears_log', ['log_status' => 1], [
                    'user_name' => $this->userModel->user_name, 'products_id' => $product_id
                ])->execute();
            }
        }
    }


    /**
     * 产品充值触发消息策略
     * @param $user_name
     * @param $product_name
     * @param $pay_type
     * @param $pay_num
     * @param $new_balance
     * @param $user_balance
     * @return bool
     */
    public function payProductNotice($user_name, $product_name, $pay_type, $pay_num, $new_balance, $user_balance)
    {
        $data = [
            'event_source' => SRUN_MGR,
            'event_type' => 'product_recharge',
            '{ACCOUNT}' => $user_name,
            '{PRODUCT}' => $product_name,
            '{TYPE}' => $pay_type,
            '{NUM}' => $pay_num,
            '{NEW_BALANCE}' => $new_balance,
            '{USER_BALANCE}' => $user_balance
        ];
        $data = json_encode($data);
        return Redis::executeCommand('RPUSH', 'list:message:main:events', [$data], 'redis_online');
    }

}
