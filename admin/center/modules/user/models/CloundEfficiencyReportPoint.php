<?php

namespace center\modules\user\models;

use Yii;

/**
 * This is the model class for table "clound_efficiency_report_point".
 *
 * @property integer $time_point
 * @property string $products_key
 */
class CloundEfficiencyReportPoint extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'clound_efficiency_report_point';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['time_point', 'products_key'], 'required'],
            [['time_point'], 'integer'],
            [['products_key'], 'string', 'max' => 30],
            [['time_point', 'products_key'], 'unique', 'targetAttribute' => ['time_point', 'products_key'], 'message' => 'The combination of Time Point and Products Key has already been taken.']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'time_point' => 'Time Point',
            'products_key' => 'Products Key',
        ];
    }
}
