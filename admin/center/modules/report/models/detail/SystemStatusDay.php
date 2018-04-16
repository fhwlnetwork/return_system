<?php

namespace center\modules\report\models\detail;

use Yii;

/**
 * This is the model class for table "system_status_day".
 *
 * @property integer $id
 * @property string $device_ip
 * @property integer $date
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
class SystemStatusDay extends SystemStatus
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'system_status_day';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['device_ip', 'date'], 'required'],
            [['date'], 'integer'],
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
     * 获取表格数据
     * @return array
     */
    public function getTableData()
    {
        $rs = [];
        try {
            $count = $this->getCount();
            if ($count < 1) {
                $rs = ['code' => 404, 'msg' => '没有数据'];
            } else {
                $query = self::find()
                    ->select(['device_ip', 'cpu', 'mem', 'mem-cahced mem_cache', 'loads', 'proccess process', 'httpd', 'date'])
                    ->where('date>=:sta and date<=:end', [
                        ':sta' => strtotime($this->start_time),
                        ':end' => strtotime($this->stop_time),
                    ])
                    ->groupBy('device_ip, date');
                $xAxis = $this->getX();
                if ($count == 1) {
                    //只采集了一台机器状态
                    $data = $query->indexBy('date')->asArray()->all();
                    $rs = $this->getSingleTableData($xAxis, $data);
                } else {
                    $data = $query->asArray()->all();
                    $rs = $this->getMultiTableData($xAxis, $data);
                }
            }
            //var_dump($rs);exit;
        } catch (\Exception $e) {
            $rs = ['code' => 500, 'msg' => '获取cpu状态发生异常:' . $e->getMessage()];
        }

        return $rs;
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
            if ($count < 1) {
                $rs = ['code' => 404, 'msg' => '没有数据'];
            } else {
                $query = self::find()
                    ->select(['device_ip', 'cpu', 'mem', 'mem-cahced mem_cache', 'loads', 'proccess process', 'httpd', 'date'])
                    ->where('date>=:sta and date<=:end', [
                        ':sta' => strtotime($this->start_time),
                        ':end' => strtotime($this->stop_time),
                    ]);
                if (!empty($this->device_ip)) {
                    $query->andWhere(['device_ip' => $this->device_ip]);
                }

                $query->groupBy('device_ip, date');
                $xAxis = $this->getX();
                if ($count == 1) {
                    //只采集了一台机器状态
                    $data = $query->indexBy('date')->asArray()->all();
                    $rs = $this->getSingleData($xAxis, $data);
                } else {
                    $data = $query->asArray()->all();
                    $rs = $this->getMultiData($xAxis, $data);
                }
            }
            //var_dump($rs);exit;
        } catch (\Exception $e) {
            $rs = ['code' => 500, 'msg' => '获取cpu状态发生异常:' . $e->getMessage()];
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
        $table['top_header'] = $this->getHeader('multi');
        $table['detail_header'] = $this->getHeader();
        foreach ($data as $v) {
            if (!in_array($v['device_ip'], $base)) {
                $base[] = $v['device_ip'];
            }
            $date = date('Y-m-d', $v['date']);
            $yAxis[$v['date']][$v['device_ip']]['cpu'] =  $cpu_rate = sprintf('%.2f', $v['cpu']);
            $yAxis[$v['date']][$v['device_ip']]['mem'] = $mem_rate = sprintf('%.2f', $v['mem']);
            $yAxis[$v['date']][$v['device_ip']]['mem_cache'] = $mem_cache_rate = sprintf('%.2f', $v['mem_cache']);
            $yAxis[$v['date']][$v['device_ip']]['loads'] = $load = $v['loads'];
            $yAxis[$v['date']][$v['device_ip']]['process'] = $process = $v['process'];
            $yAxis[$v['date']][$v['device_ip']]['httpd'] = $httpd = $v['httpd'];
            $ip = $v['device_ip'];
            $table['data'][$date]['data'] = [$date, $cpu_rate . '%', $mem_rate . '%', $mem_cache_rate . '%', $load, $process, $httpd];
            $table['data'][$date]['detail'][] = [$ip, $date, $cpu_rate . '%', $mem_rate . '%', $mem_cache_rate . '%', $load, $process, $httpd];
        }

        foreach ($times as $time) {
            $xAxis[] = date('Y-m-d', $time);
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
            'table' => $table,
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
        $ip = '';
        $legends = $this->getLegends();
        $table['header'] = $this->getHeader();
        foreach ($times as $time) {
            $xAxis[] = $date = date('Y-m-d', $time);
            if (isset($data[$time])) {
                $arr = $data[$time];
                $series['cpu'][] = $cpu_rate = sprintf('%.2f', $arr['cpu']);
                $series['mem'][] = $mem_rate = sprintf('%.2f', $arr['mem']);
                $series['mem_cache'][] = $mem_cache_rate = sprintf('%.2f', $arr['mem_cache']);
                $series['loads'][] = $load = $arr['loads'];
                $series['process'][] = $process = $arr['process'];
                $series['httpd'][] = $httpd = $arr['httpd'];
                $ip = $arr['device_ip'];
                $table['data'][] = [$ip, $date, $cpu_rate . '%', $mem_rate . '%', $mem_cache_rate . '%', $load, $process, $httpd];
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
            'text' => Yii::t('app', 'system monitor'),
            'legends' => $legends,
            'xAxis' => $xAxis,
            'table' => $table
        ];
    }

    /**
     * 多台机器cpu监控
     * @param $times
     * @param $data
     * @return array
     */
    public function getMultiTableData($times, $data)
    {
        $rs = $base = $yAxis = $table = [];
        $table['top_header'] = $this->getHeader('multi');
        $table['detail_header'] = $this->getHeader();
        foreach ($data as $arr) {
            if (!in_array($arr['device_ip'], $base)) {
                $base[] = $arr['device_ip'];
            }
            $date = date('Y-m-d', $arr['date']);
            $cpu_rate = sprintf('%.2f', $arr['cpu']);
            $mem_rate = sprintf('%.2f', $arr['mem']);
            $mem_cache_rate = sprintf('%.2f', $arr['mem_cache']);
            $load = $arr['loads'];
            $process = $arr['process'];
            $httpd = $arr['httpd'];
            $ip = $arr['device_ip'];
            $table['data'][$date]['data'] = [$date, $cpu_rate . '%', $mem_rate . '%', $mem_cache_rate . '%', $load, $process, $httpd];
            $table['data'][$date]['detail'][] = [$ip, $date, $cpu_rate . '%', $mem_rate . '%', $mem_cache_rate . '%', $load, $process, $httpd];
        }
        // var_dump($table);exit;

        return [
            'table' => $table,
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
    public function getSingleTableData($times, $data)
    {
        $table = [];
        $ip = '';
        $table['header'] = $this->getHeader();
        foreach ($times as $time) {
            $xAxis[] = $date = date('Y-m-d', $time);
            if (isset($data[$time])) {
                $arr = $data[$time];
                $cpu_rate = sprintf('%.2f', $arr['cpu']);
                $mem_rate = sprintf('%.2f', $arr['mem']);
                $mem_cache_rate = sprintf('%.2f', $arr['mem_cache']);
                $load = $arr['loads'];
                $process = $arr['process'];
                $httpd = $arr['httpd'];
                $ip = $arr['device_ip'];
                $table['data'][] = [$ip, $date, $cpu_rate . '%', $mem_rate . '%', $mem_cache_rate . '%', $load, $process, $httpd];
            }
        }

        return [
            'code' => 200,
            'single' => true,
            'table' => $table
        ];
    }
}
