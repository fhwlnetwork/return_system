<?php

namespace center\modules\report\models\detail;

use Yii;

/**
 * This is the model class for table "system_status_hour".
 *
 * @property integer $id
 * @property string $device_ip
 * @property integer $date
 * @property integer $hour
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
class SystemStatusHour extends SystemStatus
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'system_status_hour';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['device_ip', 'date', 'hour'], 'required'],
            [['date', 'hour'], 'integer'],
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
            'date' => 'Date',
            'hour' => 'Hour',
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
    /**
     * 获取磁盘状态
     * @param $type
     * @return array
     */
    public function getSystemStatus($type)
    {
        $rs = [];
        try {
            $count = $this->getCount();
            $query = self::find()
                ->select(['device_ip', 'cpu', 'mem', 'mem-cahced mem_cache', 'loads', 'proccess process', 'httpd', 'hour'])
                ->where('date>=:sta and date<=:end', [
                    ':sta' => strtotime($this->start_time),
                    ':end' => strtotime($this->stop_time)+86399,
                ])
                ->groupBy('device_ip, hour');
            if (!empty($this->device_ip)) {
                $query->andWhere(['device_ip' => $this->device_ip]);
            }
            if ($count < 1) {
                $rs = ['code' => 404, 'msg' => '没有数据'];
            } else {
                $xAxis = $this->getX();

                if ($count == 1) {
                    //只采集了一台机器状态
                    $data = $query->indexBy('hour')->asArray()->all();
                    $rs = $this->getSingleData($xAxis, $data);
                } else {
                    $data = $query->asArray()->all();
                    $rs = $this->getMultiData($xAxis, $data);
                }
            }
            //var_dump($rs);exit;
        } catch(\Exception $e) {
            $rs = ['code' => 500, 'msg' => '获取cpu状态发生异常:'. $e->getMessage()];
        }


        return $rs;
    }

    /**
     * 多台机器cpu监控
     * @param $times
     * @param $data
     * @return array
     */
    public function getMultiData($times, $data)
    {
        $rs = $base = $yAxis = $xAxis = $series = [];
        $legends = $this->getLegends();
        foreach ($data as $v) {
            if (!in_array($v['device_ip'], $base)) {
                $base[] = $v['device_ip'];
            }
            $yAxis[$v['hour']][$v['device_ip']]['cpu'] = sprintf('%.2f', $v['cpu']);
            $yAxis[$v['hour']][$v['device_ip']]['mem'] = sprintf('%.2f', $v['mem']);
            $yAxis[$v['hour']][$v['device_ip']]['mem_cache'] = sprintf('%.2f', $v['mem_cache']);
            $yAxis[$v['hour']][$v['device_ip']]['loads'] = $v['loads'];
            $yAxis[$v['hour']][$v['device_ip']]['process'] = $v['process'];
            $yAxis[$v['hour']][$v['device_ip']]['httpd'] = $v['httpd'];
        }

        foreach ($times as $time) {
            $xAxis[] = $time.Yii::t('app', 'hours');
            if (isset($yAxis[$time])) {
                foreach ($base as $val) {
                    $series[$val]['cpu'][] = isset($yAxis[$time][$val]) ? $yAxis[$time][$val]['cpu'] : 0.00;
                    $series[$val]['mem'][] = isset($yAxis[$time][$val]) ? $yAxis[$time][$val]['mem'] : 0.00;
                    $series[$val]['mem_cache'][] = isset($yAxis[$time][$val]) ? $yAxis[$time][$val]['mem_cache'] : 0.00;
                    $series[$val]['loads'][] = isset($yAxis[$time][$val]) ? $yAxis[$time][$val]['loads'] : 0.00;
                    $series[$val]['process'][] = isset($yAxis[$time][$val]) ? $yAxis[$time][$val]['process'] : 0.00;
                    $series[$val]['httpd'][] = isset($yAxis[$time][$val]) ? $yAxis[$time][$val]['httpd'] : 0.00;
                }
            } else {
                foreach ($base as $val) {
                    $series[$val]['cpu'][] = 0.00;
                    $series[$val]['mem'][] = 0.00;
                    $series[$val]['mem_cache'][] = 0.00;
                    $series[$val]['loads'][] = 0.00;
                    $series[$val]['process'][] = 0.00;
                    $series[$val]['httpd'][] = 0.00;
                }
            }
        }
        $series = $this->getArrSeries($series, $legends);

        return [
            'base' => [
                'base' => $base,
                'xAxis' => $xAxis,
                'series' => $series['base'],
                'legends' => $legends
            ],
            'options_data' => $series['option'],
            'multi' => true,
            'code' => 200
        ];
    }

    /**
     * 获取单台机器的
     * @param $times
     * @param $data
     * @return array
     */
    public function getSingleData($times, $data)
    {
        $rs = $xAxis = $legends = $series = $table = [];
        $ip  = '';
        $legends = $this->getLegends();
        $table['header'] = $this->getHeader();
        foreach ($times as $time)
        {
            $xAxis[] = $hour =  $time.Yii::t('app', 'hours');
            if (isset($data[$time])) {
                $arr = $data[$time];
                $series['cpu'][] = $cpu_rate = sprintf('%.2f', $arr['cpu']);
                $series['mem'][] = $mem_rate = sprintf('%.2f', $arr['mem']);
                $series['mem_cache'][] = $mem_cache_rate = sprintf('%.2f', $arr['mem_cache']);
                $series['loads'][] = $load = $arr['loads'];
                $series['process'][] = $process = $arr['process'];
                $series['httpd'][] = $httpd =  $arr['httpd'];
                $ip = $arr['device_ip'];
                $table['data'][] = [$ip, $hour, $cpu_rate.'%', $mem_rate.'%', $mem_cache_rate.'%', $load, $process, $httpd];
            } else {
                $series['cpu'][] = 0.00;
                $series['mem'][] = 0.00;
                $series['mem_cache'][] = 0.00;
                $series['loads'][] = 0;
                $series['process'][] = 0;
                $series['httpd'][] = 0;
            }
        }
        $series = $this->getLineSeries('line', $series);


        return [
            'code' => 200,
            'single' => true,
            'series' => $series,
            'subtext' => $ip,
            'text' => $this->start_time.'监控信息',
            'legends' => $legends,
            'xAxis' => $xAxis,
            'table' => $table
        ];
    }
}
