<?php

namespace center\models;

use Yii;

/**
 * This is the model class for table "efficiency_report_point".
 *
 * @property integer $time_point
 */
class EfficiencyReportPoint extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'efficiency_report_point';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['time_point'], 'required'],
            [['time_point'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'time_point' => 'Time Point',
        ];
    }
}
