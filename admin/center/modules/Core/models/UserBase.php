<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2017/7/10
 * Time: 8:35
 */

namespace center\modules\Core\models;


use center\modules\log\models\LogWriter;
use center\modules\user\models\OnlineRadius;
use common\models\KernelInterface;
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
use yii\helpers\Json;

/**
 * 用户操作基类
 * Class userBase
 * @package center\modules\Core\models
 */
class UserBase extends BaseActiveRecord
{
    public $user_extends; //用户表扩展字段

    /* @inheritdoc
     */
    public static function tableName()
    {
        return 'users';
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
        $this->user_extends = ExtendsField::getAllData();

        parent::init(); //TODO:: change some settings
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
                    //var_dump($oneProduct);exit;
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
            $product['user_balance'] = number_format(isset($product['user_balance']) ? floor($product['user_balance'] * 100) / 100 : 0, 2);
            $product['user_charge'] = number_format(isset($product['user_charge']) ? ceil($product['user_charge'] * 100) / 100 : 0, 2);
        }
        return $product;
    }

    /**
     * 根据list的key值 是否在ids中对list排序
     * @param array $ids
     * @return array
     */
    public function keySort($ids)
    {
        $sort1 = [];
        foreach ($ids as $id) {
            // 存在的元素排在最上面
            if (array_key_exists($id, $this->can_product)) {
                $sort1[$id] = $this->can_product[$id];
                unset($this->can_product[$id]);
            }
        }

        return $sort1 + $this->can_product;
    }


    /**
     * 销户
     * @return bool
     * @throws \Exception
     */
    public function delete()
    {
        $res = parent::delete();
        if (!$res) {
            return false;
        }

        //将核心数据发送到接口
        $array = [
            "action" => 4,
            "user_name" => $this->user_name,
            "serial_code" => time() . rand(111111, 999999), //唯一的流水号
            "time" => time(),
            'proc' => 'admin',
        ];
        $json = Json::encode($array);
        $res = Redis::executeCommand('RPUSH', "list:interface", [$json]);
        if (!$res) {
            return false;
        }

        return true;
    }

    /**
     * 禁用|启用 产品
     * @param $id
     * @param int $type ，0启用1禁用
     * @return bool
     */
    public function enableOrDisableProduct($id, $type = 0)
    {
        //获取此产品的数据
        $productUsed = $this->getOneOrderedProduct($id, $this->user_id);
        if (!$productUsed) {
            return false;
        }

        //获取一个产品的详细信息
        $product = $this->products[$id];

        //禁用产品
        KernelInterface::setProduct($this->user_name, $id, ['user_available' => $type]);
        //dm下线
        $radius_model = new OnlineRadius();
        $radius_model->radiusDrop($this->user_name);

        //写日志
        $logType = $type == 0 ? 'user base help27' : 'user base help26';
        $mgrName = $this->getMgrName();
        $logContent = \Yii::t('app', $logType, [
            'mgr' => $mgrName, //管理员
            'user_name' => $this->user_name, //用户名
            'product' => $id . ':' . (isset($product['products_name']) ? $product['products_name'] : ''), //产品名称
        ]);
        //写日志开始
        $msg = $type == 0 ? 'enableProduct' : 'cancelProduct';
        $data = [
            'operator' => $mgrName,
            'target' => $this->user_name,
            'action' => $msg,
            'action_type' => 'User Base',
            'content' => $logContent,
            'class' => __CLASS__,
            'type' => 1, //描述日志
        ];
        LogWriter::write($data);
        //写日志结束

        return true;
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
     * 根据日期查找日统计表上网的用户
     * @param $where
     * @return array
     */
    public function getOnlineUsers($where)
    {
        $query = SrunDetailDay::find()->select('user_name');
        if (isset($where['start_date'])) {
            $query->where(['>=', 'record_day', strtotime($where['start_date'])]);
        }
        if (isset($where['end_date'])) {
            $query->andWhere(['<', 'record_day', strtotime($where['end_date']) + 86400]);
        }
        if (!$this->flag) {
            //判断组
            $query->andWhere(['user_group_id' => array_keys($this->can_group)]);
            $query->andWhere(['products_id' => array_keys($this->can_product)]);
        }
        $detailUsers = $query->groupBy('user_name')->all();
        foreach ($detailUsers as $one) {
            $users[] = $one['user_name'];
        }
        return $users;
    }

    /**
     * 导出潜水用户
     * @param $list
     * @return array
     */
    public function corpse_excel($list)
    {
        //导出文件标题
        $show_files = [
            'user_name' => Yii::t('app', 'account'),
            'user_real_name' => Yii::t('app', 'name'),
            'product_name' => Yii::t('app', 'user products id'),
            'product_balance' => Yii::t('app', 'products balance'),
            'user_available' => Yii::t('app', 'user available'),
            'group_id' => Yii::t('app', 'group id'),
            'balance' => Yii::t('app', 'users balance'),
        ];
        $ArrayTitle = [
            0 => array_values($show_files),
        ];

        $add_datas = $dataArray = [];
        foreach ($list as $key => $value) {
            //显示产品
            $products = $this->getProductByName($value['user_name'], true);
            if ($products) {
                $i = 0;
                foreach ($products as $pid => $product_name) {
                    $product_balance = Redis::executeCommand('hget', 'hash:users:products:' . $value['user_id'] . ':' . $pid, ['user_balance']);
                    if ($i == 0) {
                        $list[$key]['product_name'] = $products[$pid];
                        $list[$key]['product_balance'] = $product_balance;
                    } else {
                        $add_datas[] = $value + ['product_name' => $product_name, 'product_balance' => $product_balance];
                    }
                    $i++;
                }
            }

            //显示状态
            $userRedis = $this->getUserInRedis($value['user_name']);
            if ($userRedis) {
                $list[$key]['user_available'] = $userRedis['user_available'];
            }
        }

        $list = array_merge($list, $add_datas);

        foreach ($list as $value) {
            $arr = [];
            foreach (array_keys($show_files) as $k) {
                $arr[] = $value[$k];
            }
            $dataArray[] = $arr;
        }

        return array_merge($ArrayTitle, $dataArray);
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
     * 判断是否可以继续开户
     * @param $open_num
     * @param $max_open_num
     * @return bool
     */
    public function checkIsOpen($open_num, $max_open_num)
    {
        //判断是否还有开户的权限
        if ($max_open_num - $open_num <= 0 && !$this->flag) {
            Yii::$app->getSession()->setFlash('error', Yii::t('app', 'open_num_error'));

            return false;
        }

        return true;
    }

    /**
     * 用户管理组判断
     * @return bool
     */
    public function getCanOrg()
    {
        if (empty($this->can_group) || !in_array($this->group_id, array_keys($this->can_group))) {
            return false;
        }
        return true;
    }

    /**
     * 用户操作产品判断
     * @return bool
     */
    public function getCanProduct()
    {
        foreach ($this->products_id as $pid => $value) {
            if (empty($this->can_product) || !in_array($pid, array_keys($this->can_product))) {
                return false;
            }
        }

        return true;
    }


    /**
     * 修改密码写消息通知用户
     * @param $user_name
     * @param $password
     */
    public function updatePassNotice($user_name, $password)
    {
        $data = [
            'event_source' => SRUN_MGR,
            'event_type' => 'mofify_password',
            '{ACCOUNT}' => $user_name,
            '{NEW_PASSWORD}' => $password,
        ];
        $data = json_encode($data);
        Redis::executeCommand('RPUSH', 'list:message:main:events', [$data], 'redis_online');
    }
}