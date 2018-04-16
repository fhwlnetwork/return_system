<?php

namespace center\modules\report\models\detail;

use Yii;

/**
 * This is the model class for table "partitions_status_hour".
 *
 * @property integer $id
 * @property integer $date
 * @property integer $hour
 * @property string $device_ip
 * @property string $partition_name
 * @property string $mount_point
 * @property string $total_bytes
 * @property string $min_free_bytes
 * @property double $max_used_percent
 */
class PartitionsStatusHour extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'partitions_status_hour';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['date', 'hour', 'total_bytes', 'min_free_bytes'], 'integer'],
            [['device_ip', 'partition_name', 'mount_point'], 'required'],
            [['max_used_percent'], 'number'],
            [['device_ip', 'partition_name', 'mount_point'], 'string', 'max' => 64],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'date' => 'Date',
            'hour' => 'Hour',
            'device_ip' => 'Device Ip',
            'partition_name' => 'Partition Name',
            'mount_point' => 'Mount Point',
            'total_bytes' => 'Total Bytes',
            'aver_free_bytes' => 'Aver Free Bytes',
            'min_free_bytes' => 'Min Free Bytes',
            'aver_used_percent' => 'Aver Used Percent',
            'max_used_percent' => 'Max Used Percent',
        ];
    }
}
