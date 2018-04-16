<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2017/6/8
 * Time: 13:42
 */

namespace center\modules\report\models\detail;



use Yii;
use yii\db\ActiveRecord;

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
class PartitionBase  extends ActiveRecord
{
    public static $selectFields = ['MAX(used_percent) max_used', 'partition_name', 'mount_point', 'total_bytes', 'MIN(free_bytes) min_free', 'device_ip'];

    /**
     * @inheritdoc
     */
    public static function tableName ()
    {
        return 'partitions_status';
    }

    /**
     * @inheritdoc
     */
    public function rules ()
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
    public function attributeLabels ()
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

    /**
     * @param $sta
     * @param $end
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getData ($sta, $end)
    {
        $status = PartitionsStatusHour::find ()
            ->where ('date>=:sta and date<:end', [':sta' => $sta, ':end' => $end])
            ->select (self::$selectFields)
            ->groupBy (['device_ip', 'partition_name'])
            ->asArray ()
            ->all ();

        return $status;
    }

    /**
     * 获取磁盘状态
     * @param int $type
     * @return array
     */
    public function getStatus ($type = 1)
    {
        $this->getBeginTime ($type);
        $query = new Yii\db\Query();
        $data = $query
            ->select ('device_ip, partition_name, used_percent')->addSelect ($this->indexBy)
            ->where ('date >= :sta and date <= :end', [':sta' => $this->start_time, ':end' => $this->stop_time])
            ->from ($this->table_name)
            ->groupBy (['device_ip', 'partition_name', 'date'])
            // ->indexBy($this->indexBy)
            ->all ();
        $timeArr = $this->getX ();
        $rs = $source = [];
        if (!empty($data)) {
            foreach ($data as $v) {
                $rs[$v['device_ip']][$v['partition_name']][$v[$this->child_name]] = $v['used_percent'];
            }

            $source = $this->getSource ($rs, $timeArr);
        }
        $source['xAxis'] = json_encode ($timeArr);
        //var_dump($source);exit;

        return $source;
    }
}
