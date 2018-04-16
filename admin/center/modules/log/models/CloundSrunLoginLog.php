<?php

namespace center\modules\log\models;

use Yii;

/**
 * This is the model class for table "clound_srun_login_log".
 *
 * @property string $id
 * @property string $product_key
 * @property string $error_count
 * @property string $error_count1
 * @property string $error_count2
 * @property string $error_count3
 * @property string $error_count4
 * @property string $error_type
 * @property integer $statistics_time
 */
class CloundSrunLoginLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'clound_srun_login_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_key', 'statistics_time'], 'required'],
            [['error_count', 'error_count1', 'error_count2', 'error_count3', 'error_count4', 'statistics_time'], 'integer'],
            [['error_type'], 'string'],
            [['product_key'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'product_key' => 'Product Key',
            'error_count' => 'Error Count',
            'error_count1' => 'Error Count1',
            'error_count2' => 'Error Count2',
            'error_count3' => 'Error Count3',
            'error_count4' => 'Error Count4',
            'error_type' => 'Error Type',
            'statistics_time' => 'Statistics Time',
        ];
    }
}
