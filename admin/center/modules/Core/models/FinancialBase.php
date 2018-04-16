<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2017/7/10
 * Time: 8:35
 */

namespace center\modules\Core\models;

use yii;
use common\models\Redis;
use center\modules\strategy\models\Product;
use center\modules\strategy\models\Package;
use center\modules\financial\models\WaitCheck;
use center\modules\setting\models\ExtendsField;
use center\modules\report\models\Financial;
use center\modules\strategy\models\Recharge;
use center\modules\user\models\ExpireProducts;
use center\modules\strategy\models\ProductsChange;
use center\modules\financial\models\CheckoutList;
use center\modules\report\models\SrunDetailDay;
use yii\db\ActiveRecord;

/**
 * Class FinancialBase
 * @package center\modules\Core\models
 */
class FinancialBase extends ActiveRecord
{
    const PAY_BALANCE = 0; //类型：余额
    const PAY_PRODUCT = 1; //类型：产品
    const PAY_EXTRA = 2; //类型：附加费用
    const PAY_BALANCE_PRODUCT = 3; //类型：余额+产品
    const PAY_ALL = 4; //类型：余额+产品+附加费用

    public $userModel = null; //用户模型
    public $payType = 0; //缴费方式
    public $useBalance = false; //是否使用余额
    public $message_ok; //缴费结果成功消息
    public $message_err; //缴费结果失败消息
    public $needPayTotalNum = 0; //总计需要缴费金额
    public $useTotalBalance = 0; //总计使用余额数
    public $payTotalNum = 0; //总计实际缴费金额
    public $payListId = 0; //缴费记录id
    public $_mgrName;
    public $financialField = '';
    public $flag = true;
    public $can_group = [];
    public $can_mgr = [];

    public $pay_type_d; //缴费方式默认项
    public $baseshowField = [
        'user_name',
        'user_real_name',
        'pay_num',
        'gift',
        'pay_type_id',
        'create_at',
        'order_no',
        'mgr_name',
        'bill_number',
        'is_refund',
        'operate',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%pay_list}}';
    }

    public function getProNames()
    {
        $productList = (new Product())->getValidList();
        $proNames = [];
        foreach($productList as $v){
            $proNames[$v['products_id']]= $v['products_name'];
        }
        return $proNames;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['start_time', 'stop_time', 'timePoint', 'operator'], 'safe'],
        ];
    }

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


    /**
     * 根据list的key值 是否在ids中对list排序
     * @param array $list
     * @return array
     */
    public function keySort($list)
    {
        $sort1 = [];
        if (!empty($this->can_product)) {
            foreach ($list as $id) {
                // 存在的元素排在最上面
                if (array_key_exists($id, $this->can_product)) {
                    $sort1[$id] = $list[$id];
                }
            }
        }

        return $sort1;
    }

    /**
     * 产品缴费
     *
     * @param $user_name
     * @param $pay_num
     * @param $product_id
     * @return mixed
     */
    public function ProductPay($user_name, $pay_num, $product_id)
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
     * 将异常错误信息写入表统计
     * @param $action
     * @param $msg
     * @return int
     * @throws yii\db\Exception
     */
    public function writeMessage($action, $msg)
    {
        $db = Yii::$app->db;
        $time = time();
        $ip_addr = Yii::$app->request->userIP;
        return $db->createCommand("INSERT INTO `operate_exception`(exception_time, action_type, err_msg, ip_addr) VALUES('{$time}', '{$action}', '{$msg}', '{$ip_addr}')")->execute();
    }
}