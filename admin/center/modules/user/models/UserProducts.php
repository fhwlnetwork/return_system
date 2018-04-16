<?php

namespace center\modules\user\models;

use Yii;

/**
 * This is the model class for table "user_products".
 *
 * @property string $user_products_id
 * @property string $user_name
 * @property string $user_id
 * @property string $products_id
 * @property string $mobile_phone
 * @property integer $user_available
 * @property double $user_balance
 */
class UserProducts extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_products';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_name', 'mobile_phone'], 'required'],
            [['user_id', 'products_id', 'user_available'], 'integer'],
            [['user_balance'], 'number'],
            [['user_name', 'mobile_phone'], 'string', 'max' => 64],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_products_id' => 'User Products ID',
            'user_name' => 'User Name',
            'user_id' => 'User ID',
            'products_id' => 'Products ID',
            'mobile_phone' => 'Mobile Phone',
            'user_available' => 'User Available',
            'user_balance' => 'User Balance',
        ];
    }
}
