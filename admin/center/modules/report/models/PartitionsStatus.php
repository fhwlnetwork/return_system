<?php

namespace center\modules\report\models;

use Yii;

/**
 * This is the model class for table "partitions_status".
 *
 * @property integer $id
 * @property string $device_ip
 * @property string $partition_name
 * @property string $mount_point
 * @property string $total_bytes
 * @property string $free_bytes
 * @property double $used_percent
 * @property string $time
 */
class PartitionsStatus extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'partitions_status';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['device_ip', 'partition_name', 'mount_point', 'time'], 'required'],
            [['total_bytes', 'free_bytes', 'time'], 'integer'],
            [['used_percent'], 'number'],
            [['device_ip', 'partition_name', 'mount_point'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'device_ip' => 'Device Ip',
            'partition_name' => 'Partition Name',
            'mount_point' => 'Mount Point',
            'total_bytes' => 'Total Bytes',
            'free_bytes' => 'Free Bytes',
            'used_percent' => 'Used Percent',
            'time' => 'Time',
        ];
    }

    /**
     * 获取当前系统磁盘状态
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getCurrentStatus ()
    {
        $sta = time () - 7200;
        $status = self::find ()
            ->where ('time >= :sta', [':sta' => $sta])
            ->select (self::$selectFields)
            ->groupBy (['device_ip', 'partition_name'])
            ->asArray ()
            ->all ();

        return $status;
    }

}
