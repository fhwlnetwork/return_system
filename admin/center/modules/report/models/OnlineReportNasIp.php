<?php

namespace center\modules\report\models;

use Yii;

/**
 * This is the model class for table "online_report_nas_ip".
 *
 * @property integer $report_id
 * @property integer $time_point
 * @property string $nas_ip
 * @property integer $count
 * @property integer $bytes_in
 * @property integer $bytes_out
 * @property integer $time_long
 */
class OnlineReportNasIp extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'online_report_nas_ip';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['time_point', 'nas_ip', 'count', 'bytes_in', 'bytes_out', 'time_long'], 'required'],
            [['time_point', 'count', 'bytes_in', 'bytes_out', 'time_long'], 'integer'],
            [['nas_ip'], 'string', 'max' => 16]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'report_id' => 'Report ID',
            'time_point' => 'Time Point',
            'nas_ip' => 'Nas Ip',
            'count' => 'Count',
            'bytes_in' => 'Bytes In',
            'bytes_out' => 'Bytes Out',
            'time_long' => 'Time Long',
        ];
    }
}
