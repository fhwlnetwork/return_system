<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2017/7/7
 * Time: 11:35
 */

namespace center\modules\Core\models;

use yii;
use common\models\User;
use common\models\Redis;
use yii\db\ActiveRecord;
use center\modules\user\models\Base;
use center\modules\auth\models\SrunJiegou;
use center\modules\report\models\Financial;
use center\modules\financial\models\PayList;
use center\modules\strategy\models\Product;
use center\modules\strategy\models\Package;
use center\modules\financial\models\WaitCheck;
use center\modules\strategy\models\Recharge;
use center\modules\user\models\ExpireProducts;
use center\modules\strategy\models\ProductsChange;
use center\modules\financial\models\CheckoutList;
use center\modules\interfaces\models\SoapCenter;

/**
 * 数据库操作基类
 * Class BaseActiveRecord
 * @package center\modules\Core\models
 */
class BaseActiveRecord extends ActiveRecord
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
    public $_mgrName;
    public $products;

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
        $this->can_mgr = $this->getMgrOpe();
        $rs = $this->getProNames();
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
    public function getProductByName($userId, $flag = true)
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
                    if ($this->can_product && !array_key_exists($id, $this->can_product)) {
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