<?php

namespace center\modules\report\models\detail;

use Yii;

/**
 * This is the model class for table "system_status".
 *
 * @property integer $id
 * @property string $device_ip
 * @property string $time
 * @property double $cpu
 * @property double $mem
 * @property double $mem-cahced
 * @property double $loads
 * @property double $proccess
 * @property double $httpd
 * @property double $stat2
 * @property double $stat3
 * @property double $stat4
 * @property double $stat5
 * @property double $stat6
 * @property double $stat7
 * @property double $stat8
 */
class SystemStatus extends BaseModel
{
    public static $selectFields = ['device_ip', 'cpu', 'mem', 'mem-cahced', 'loads', 'proccess', 'httpd'];
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'system_status';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['device_ip', 'time'], 'required'],
            [['time'], 'integer'],
            [['cpu', 'mem', 'mem-cahced', 'loads', 'proccess', 'httpd', 'stat2', 'stat3', 'stat4', 'stat5', 'stat6', 'stat7', 'stat8'], 'number'],
            [['device_ip'], 'string', 'max' => 64]
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
            'time' => 'Time',
            'cpu' => 'Cpu',
            'mem' => 'Mem',
            'mem-cahced' => 'Mem Cahced',
            'loads' => 'Loads',
            'proccess' => 'Proccess',
            'httpd' => 'Httpd',
            'stat2' => 'Stat2',
            'stat3' => 'Stat3',
            'stat4' => 'Stat4',
            'stat5' => 'Stat5',
            'stat6' => 'Stat6',
            'stat7' => 'Stat7',
            'stat8' => 'Stat8',
        ];
    }

    public static function getCurrentStatus($fields = '')
    {
        $fields = (empty($fields)) ? self::$selectFields : $fields;
        $sta = time() - 7200;
        $status  = self::find()
            ->where('time >= :sta', [':sta' => $sta])
            ->select($fields)
            ->groupBy(['device_ip'])
            ->asArray()
            ->all();


        return $status;
    }

    /**
     * @param $sta
     * @param $end
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getData ($sta, $end, $fields = '*')
    {
        $status = SystemStatusHour::find ()
            ->select($fields)
            ->where ('date>=:sta and date<:end', [':sta' => $sta, ':end' => $end])
            ->groupBy (['device_ip'])
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
        $rs = $times = [];
        for ($i = 0; $i <= 24; $i++) {
            $stop = ($this->start_time) + $i * 3600;
            $query = SystemStatus::find();
            $data = $query
                ->select ('device_ip, max(`mem`) `mem`, max(`mem-cahced`) as `mem_cached`, max(cpu) cpu, time')
                ->where ('time >= :sta and time <= :end', [':sta' => $this->start_time, ':end' => $stop])
                ->groupBy (['device_ip'])
                ->asArray()
                ->all();
            $times[] = date('m/d H时', $stop);
            foreach ($data as $v) {
                $rs[$v['device_ip']]['mem'][] = $v['mem'];
                $rs[$v['device_ip']]['cpu'][] = $v['cpu'];
                $rs[$v['device_ip']]['mem-cached'][] = $v['mem_cached'];
            }
        }

        $source = $this->getSource($rs);
        $source['xAxis'] = json_encode ($times);
        //var_dump($source);exit;

        return $source;
    }
}
