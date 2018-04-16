<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2017/7/12
 * Time: 11:35
 */

namespace center\modules\Core\models;

use yii;
use yii\helpers\Json;
use common\models\User;
use common\models\Redis;
use center\modules\user\models\Base;
use center\modules\auth\models\SrunJiegou;
use center\modules\report\models\Financial;
use center\modules\financial\models\PayList;
use center\modules\strategy\models\Product;
use center\modules\interfaces\models\SoapCenter;
use center\modules\strategy\models\ProductsChange;
use center\modules\financial\models\WaitCheck;
use center\modules\strategy\models\Package;
use center\modules\strategy\models\Recharge;
use center\modules\user\models\ExpireProducts;

/**
 *操作模型
 * Class BaseActiveRecord
 * @package center\modules\Core\models
 */
class BaseModel extends yii\base\Model
{
    public $start_time;
    public $stop_time;
    public $timePoint;
    public $operator;
    public $flag; //是否超管
    public $can_group = []; //可以管理的用户组
    public $can_product = []; //可管理的产品
    public $can_mgr = []; //可管理的管理员
    public $baseModel;
    public $products;
    public $_mgrName;

    //CDR的key
    const INTERFACE_NAME_KEY = 'key:interface_name:';
    const INTERFACE_NAME_List_KEY = 'list:users:interface_name:';
    const USERS_EXPORT_LIMIT = 30000;  //excel一次最多导出量
    const CSV_EXPORT_LIMIT = 100000; //csv一次性导出量

    /* @inheritdoc
     */
    public static function tableName()
    {
        return 'system_status';
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
     * 类初始化
     */
    public function init()
    {
        $this->flag = User::isSuper();
        $this->can_group = SrunJiegou::canMgrGroupNameList();
        $rs = $this->getProNames();
        $this->can_mgr = $this->getMgrOpe();
        $this->can_product = $rs['names'];
        $this->products = $rs['list'];
        parent::init(); //TODO:: change some settings
    }

    /**
     * 获取产品名列表
     * @return array
     */
    public function getProNames(){
        $productList = $this->getProList();
        $proNames = [];
        foreach($productList as $v){
            $proNames[$v['products_id']]= $v['products_name'];
        }
        return ['list' => $productList, 'names' => $proNames];
    }

    /**
     * 获取产品列表
     * @return array
     */
    public function getProList(){
        $productModel = new Product();
        return $productModel->getValidList();
    }

    /**
     * 获取已经开户的用户数
     * @return mixed
     */
    public function getOpenUserNum($mgrName = null)
    {
        $mgrName = $mgrName ? $mgrName : Yii::$app->user->identity->username;

        return Base::find()->where(['mgr_name_create' => $mgrName])->count();
    }


    /**
     * 获取能管理的管理员
     * @return array
     */
    public function getMgrOpe()
    {
        //判断管理员
        $userModel = new User();
        //获取管理员能管理的
        if (!$this->flag) {
            $canMgrope = $userModel->getChildIdAll();
        } else {
            $allMgrs = User::find()->select('username')->indexBy('username')->asArray()->all();
            $canMgrope = array_keys($allMgrs);
        }

        //查询接口名称
        $center_names = SoapCenter::find()->select('center_name')->asArray()->all();
        //北向用户名称
        $north_names = PayList::find()->select('DISTINCT(mgr_name) mgr_name')->where('mgr_name like :mgr', [':mgr' => 'Api%'])->asArray()->all();

        if ($center_names) {
            foreach ($center_names as $one) {
                $canMgrope[] = $one['center_name'];
            }
        }
        if ($north_names) {
            foreach ($north_names as $one) {
                $canMgrope[] = $one['mgr_name'];
            }
        }

        return $canMgrope;
    }

    /**
     * 根据用户id或者用户名获取用户订购产品
     * @param $userId
     * @param bool|false $flag
     * @return array
     */
    public function getProductByName($userId, $flag = false)
    {
        //echo 1;exit;
        $products = [];
        if ($flag) {
            $userId = Redis::executeCommand('get', 'key:users:user_name:' . $userId);
        }
        $products_id = Redis::executeCommand('LRANGE', 'list:users:products:' . $userId, [0, -1]);
        if ($products_id) {
            foreach ($products_id as $id) {
                if (!$this->flag) {
                    if ($this->can_product && !array_keys($id, $this->can_product)) {
                        continue;
                    }
                }
                $products[$id] = $this->can_product[$id];
            }
        }

        return $products;
    }

    /**
     * 获取CDR绑定信息列表
     * @param $user_name
     * @return array|mixed
     */
    public function getCDRList($user_name)
    {
        $data = Redis::executeCommand('LRANGE', self::INTERFACE_NAME_List_KEY . $user_name, [0, -1]);
        return $data;
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
     * 获取用户产品余额
     * @param $userId
     * @param $proId
     * @return array|string
     */
    public function getProductBalance($userId, $proId)
    {
        $hash = Redis::executeCommand('hgetall', "hash:users:products:$userId:$proId");

        return $hash ? Redis::hashToArray($hash)['user_balance'] : 0;
    }

    /**
     * 获取redis中用户的基本信息
     * @param $user_name 用户名
     * @return array
     */
    public function getUserInRedis($user_name)
    {
        $user = [];
        $redis_uid = Redis::executeCommand('get', 'key:users:user_name:' . $user_name);
        $hash = Redis::executeCommand('HGETALL', 'hash:users:' . $redis_uid);
        if ($hash) {
            $user = Redis::hashToArray($hash);
        }
        return $user;
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
     * 获取用户订购的产品信息，仅返回可以管理的已订购产品
     * @return array
     */
    public function getOrderedProduct($products, $user_id)
    {
        $arr = [];

        $productModel = new Product();//产品模型
        $packageModel = new Package();//套餐模型
        //获取此产品的缴费策略
        $rechargeModel = new Recharge();
        $rechargesList = $rechargeModel->getList();
        foreach ($products  as $id) {
            //判断产品是否可以管理，如果不能管理，跳过
            if (!array_key_exists($id, $this->can_product)) {
                continue;
            }
            //获取产品的详情
            $product = $productModel->getOne($id);

            if ($product) {
                //获取此用户此产品的结算日期
                $waitCheckModel = WaitCheck::findOne(['user_id' => $user_id, 'products_id' => $id]);
                if ($waitCheckModel) {
                    $product['checkout_date'] = date('Y-m-d', $waitCheckModel->checkout_date);
                }

                //产品到期日期
                $expireModel = ExpireProducts::findOne(['user_id' => $user_id, 'products_id' => $id]);
                if ($expireModel) {
                    $product['expire_date'] = $expireModel->expired_at ? date('Y-m-d', $expireModel->expired_at) : '';
                }

                //获取此产品的下个产品
                $proChangeModel = new ProductsChange();
                $product_id_new = $proChangeModel->getChangeProduct($user_id, $id);
                if ($product_id_new === false) {
                    $product['next_product'] = $product['products_name'];
                } else {
                    $next_product = Redis::executeCommand('hget', $productModel->hashKeyPre . $product_id_new, ['products_name']);
                    $product['next_product'] = $next_product ? $next_product : $product['products_name'];
                }

                //获取此产品已经使用的数据
                if ($oneProduct = $this->getOneOrderedProduct($id, $user_id)) {
                    $oneProduct['user_balance'] = str_replace(',', '', $oneProduct['user_balance']);
                    $product['used'] = $oneProduct;
                    $product['used']['recharge_times'] = Product::getRechargeTimes($id);
                }

                //获取此产品的套餐
                $package = $packageModel->getOneByUidAndPid($user_id, $id);
                if ($package) {
                    $product['package'] = $package;
                }
                if ($rechargesList && isset($product['recharge_id']) && !empty($product['recharge_id'])) {
                    $sort1 = [];
                    foreach ($product['recharge_id'] as $rid) {
                        // 存在的元素排在最上面
                        if (array_key_exists($rid, $rechargesList)) {
                            $sort1[$rid] = $rechargesList[$rid];
                        }
                    }
                    $product['rechargesList'] = $sort1;
                }
                $arr[$id] = $product;
            }
        }

        return $arr;
    }

    /**
     * 判断用户是否能完成销户
     * @param $orderedProductList ，订购产品以及产品相关信息
     * @param $username ，用户名称
     * @return bool|string
     */
    public function getWhetherDelete($orderedProductList, $username)
    {
        foreach ($orderedProductList as $product) {
            $productId = isset($product['products_id']) ? $product['products_id'] : '';
            $productName = isset($product['products_name']) ? $product['products_name'] : '';
            $proBalance = isset($product['used']['user_balance']) ? $product['used']['user_balance'] : '';
            $productFee = isset($product['checkout_amount']) ? $product['checkout_amount'] : '';
            $proRecharge = isset($product['used']['user_charge']) ? $product['used']['user_charge'] : 0;
            $proBytes = isset($product['used']['sum_bytes']) ? $product['used']['sum_bytes'] : 0;
            $proSeconds = isset($product['used']['sum_seconds']) ? $product['used']['sum_seconds'] : 0;
            $proTimes = isset($product['used']['sum_times"']) ? $product['used']['sum_times"'] : 0;
            if ($proBalance >= 0.1 && $proBalance > $productFee) {
                return $productName;
            } else if ($proBalance > 0) {
                //写结算
                $checkoutModel = new CheckoutList();
                $checkoutModel->user_name = $username;
                $checkoutModel->spend_num = $proBalance;
                $checkoutModel->rt_spend_num = $proRecharge;
                $checkoutModel->product_id = $productId;
                $checkoutModel->flux = $proBytes;
                $checkoutModel->minutes = $proSeconds;
                $checkoutModel->sum_times = $proTimes;
                $checkoutModel->create_at = time();
                $checkoutModel->type = CheckoutList::CHECKOUT_OTH;
                $rs = $checkoutModel->save();
                if (!$rs) {
                    return false;
                }
            }
        }
        return true;
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