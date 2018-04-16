<?php
namespace center\modules\user\models;

use center\modules\auth\models\SrunJiegou;
use center\modules\financial\models\PayList;
use center\modules\financial\models\PayType;
use center\modules\financial\models\WaitCheck;
use center\modules\log\models\LogWriter;
use center\modules\setting\models\ExtendsField;
use center\modules\strategy\models\Package;
use center\modules\strategy\models\Product;
use center\modules\strategy\models\ProductsChange;
use common\models\KernelInterface;
use common\models\Redis;
use common\models\User;
use yii;
use yii\helpers\Json;

/**
 * Class Base
 * @package center\modules\user\models
 * @property string $user_name 账号
 */
class Card extends yii\db\ActiveRecord
{
	//收费标准
	public $Standard;

    public static function tableName()
    {
        return 'users';
    }

    public function rules()
    {
        //获取扩展字段的必填项
        $mustField = [];
        foreach(ExtendsField::getAllData() as $one){
            if($one['is_must'] == 1){
                $mustField[] = $one['field_name'];
            }
        }

        $mustAdd = array_merge($mustField, ['user_name', 'user_password', 'group_id', 'products_id']);
        $mustEdit = $mustField;

        return [
            [$mustAdd, 'required', 'on'=>['add']],
            ['user_name', 'unique', 'on'=>['add']],
            ['user_name', 'trim', 'on'=>['add']],
            ['user_name', 'string', 'length' => [1, 64], 'on'=>['add']],
            ['user_name', 'match', 'pattern' => '/^[a-zA-Z0-9][a-zA-Z0-9@._-]{0,63}$/', 'on'=>['add']],
            ['group_id', 'groupMust', 'on'=>['add']],
            ['products_id', 'productsIdMust', 'on'=>['add']],
            [$mustEdit, 'required', 'on'=>['edit']],
            [['user_password', 'user_password2'], 'required', 'on'=>['chgPassword']],
            [['user_password', 'user_password2'], 'string', 'max' => 64, 'on'=>['chgPassword']],
            [['user_password', 'user_password2'], 'string', 'min' => 6, 'on'=>['chgPassword']],
            [['user_password2'], 'compare', 'compareAttribute'=>'user_password', 'on'=>['chgPassword']],
            ['temName', 'default' , 'value' => Yii::t('app', 'user template'), 'on'=>['add']],
            [['user_expire_time'], 'default', 'value' => 0, 'on' => ['add', 'edit']],
            ['payType', 'integer'],
            [['mobile_phone', 'mobile_password'], 'string', 'on'=>['add', 'edit']],
            //[['mobile_phone2'], 'compare', 'compareAttribute'=>'mobile_phone', 'on' => ['add', 'edit']],
            //[['mobile_password2'], 'compare', 'compareAttribute'=>'mobile_password', 'on' => ['add', 'edit']],
            ['max_online_num', 'string', 'on' => ['add', 'edit']],
        ];
    }

    /**
     * 场景
     * @return array
     */
    public function scenarios()
    {
        $arr1 = ['user_password', 'user_allow_chgpass', 'user_available', 'user_real_name', 'group_id', 'products_id', 'user_expire_time','mobile_is_text'];
        //获取扩展字段的列表
        $exFields = [];
        foreach(ExtendsField::getAllData() as $one){
            $exFields[] = $one['field_name'];
        }
        //添加上附加字段
        $arrEdit = yii\helpers\ArrayHelper::merge($arr1, $exFields);
        //添加上模板部分
        $arrAdd = yii\helpers\ArrayHelper::merge($arrEdit, ['user_name', 'saveTem', 'temName', 'commonTem']);

        return yii\helpers\ArrayHelper::merge(parent::scenarios(), [
            'add' =>  $arrAdd,
            'edit' => $arrEdit,
            'chgPassword' => ['user_name', 'user_real_name', 'user_password', 'user_password2'],
        ]);
    }




    public function transactions()
    {
        return [
            'default' => self::OP_UPDATE,
        ];
    }

    public function attributeLabels()
    {
        return [
            'user_name' => Yii::t('app', 'User Name'),
            'balance' => Yii::t('app', 'Charge standard'),
            'card_num' => Yii::t('app', 'Card number'),
            'card_owner' => Yii::t('app', 'Card Owner'),
            'email' => Yii::t('app', 'Email'),
        ];
    }

    /**
     * 获取redis中用户的基本信息
     * @param $user_name 用户名
     * @return array
     */
    public function getUserInRedis($user_name)
    {
        $user = [];
        $redis_uid = Redis::executeCommand('get', 'key:users:user_name:'.$user_name);
        $hash = Redis::executeCommand('HGETALL', 'hash:users:' . $redis_uid);
        if ($hash) {
            $user = Redis::hashToArray($hash);
        }
        return $user;
    }

    /**
     * 根据用户名获取产品
     * @param $name
     * @return mixed ['pid'=>'pName', 'pid'=>'pName']
     */
    public function getProductByName($name)
    {
        $products = [];
        $redis_uid = Redis::executeCommand('get', 'key:users:user_name:'.$name);
        $products_id = Redis::executeCommand('LRANGE', 'list:users:products:'.$redis_uid, [0, -1]);
        if($products_id){
            $productModel = new Product();
            foreach($products_id as $pid){
                $pro = $productModel->getOneName($pid);
                if($pro){
                    $products[$pid] = $pro;
                }
            }
        }
        return $products;
    }


}