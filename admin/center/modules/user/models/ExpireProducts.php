<?php

namespace center\modules\user\models;

use center\modules\financial\models\WaitCheck;
use center\modules\strategy\models\Product;
use center\modules\strategy\models\ProductsChange;
use common\models\Redis;
use Yii;

/**
 * This is the model class for table "expire_products".
 *
 * @property string $id
 * @property string $user_name
 * @property integer $user_id
 * @property integer $products_id
 * @property integer $expired_at
 * @property integer $updated_at
 */
class ExpireProducts extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'expire_products';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_name', 'user_id', 'products_id', 'expired_at', 'updated_at'], 'required'],
            [['user_id', 'products_id', 'expired_at', 'updated_at'], 'integer'],
            [['user_name'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_name' => Yii::t('app', 'User Name'),
            'user_id' => Yii::t('app', 'User ID'),
            'products_id' => Yii::t('app', 'Product ID'),
            'expired_at' => Yii::t('app', 'Expire Date'),
            'updated_at' => Yii::t('app', 'Update Time'),
        ];
    }

    /**
     * 产品余额是否够下个周期上网
     * @param $user_name
     * @param $user_id
     * @param $product_id
     * @return bool
     */
    public function isEnoughNext($user_name, $user_id, $product_id){
        //先查询结算金额
        $waitModel = new WaitCheck();
        $waitData = $waitModel->checkoutParams($user_name, $product_id);
        if($waitData == false){
            return false;
        }
        $checkoutAmount = isset($waitData['checkout_amount']) ? $waitData['checkout_amount'] : 0;

        //查询下个周期产品的周期费用
        $proChangeModel = new ProductsChange();
        $product_id_new = $proChangeModel->getChangeProduct($user_id, $product_id);
        $nextProductId = $product_id_new ? $product_id_new : $product_id;
        $nextAmount = Redis::executeCommand('hget', 'hash:products:'.$nextProductId, ['checkout_amount']);
        $nextCheckoutAmount = $nextAmount ? $nextAmount : 0;

        //查询产品余额
        $balance = Redis::executeCommand('hget', 'hash:users:products:'.$user_id.':'.$product_id, ['user_balance']);

        if($balance >= $checkoutAmount+$nextCheckoutAmount){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 获取产品过期日期
     * @param $checkout_date 产品结算日期（时间戳）
     * @param $product_id 产品id
     * @param $balance 产品余额
     * @return false|float|int
     */
    public function getExpireDate($checkout_date, $product_id, $balance){
        $product_data = Redis::executeCommand('hmget', 'hash:products:'.$product_id, ['checkout_amount', 'checkout_cycle']);

        //产品结算金额为0，没有到期日期
        if(!$product_data[0]){
            return $product_data[0];
        }

        if($checkout_date){
            if($balance-$product_data[0]<=0){
                return $checkout_date;
            }
            $cycle_num = floor(($balance-$product_data[0])/$product_data[0]);
        }

        if(!$cycle_num){
            return $cycle_num;
        }

        return strtotime('+' . $cycle_num . ' '.$product_data[1].'s', $checkout_date);
    }
}
