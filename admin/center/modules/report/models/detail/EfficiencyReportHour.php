<?php

namespace center\modules\report\models\detail;

use Yii;

/**
 * This is the model class for table "efficiency_report_hour".
 *
 * @property integer $id
 * @property integer $date
 * @property integer $hour
 * @property string $my_ip
 * @property string $proc
 * @property string $start_count
 * @property double $start_response_time
 * @property string $update_count
 * @property double $update_response_time
 * @property string $stop_count
 * @property double $stop_response_time
 * @property string $auth_count
 * @property double $auth_response_time
 * @property string $coa_count
 * @property double $coa_response_time
 * @property string $dm_count
 * @property double $dm_response_time
 */
class EfficiencyReportHour extends EfficiencyBase
{
    public $selectFields = ['my_ip', 'date', 'proc', 'start_response_time', 'update_response_time', 'stop_response_time',
        'auth_response_time', 'coa_response_time', 'dm_response_time', 'start_count', 'update_count', 'stop_count',
        'auth_count', 'coa_count', 'dm_count', 'hour'
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'efficiency_report_hour';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['date', 'hour', 'my_ip', 'proc', 'start_count', 'start_response_time', 'update_count', 'update_response_time', 'stop_count', 'stop_response_time', 'auth_count', 'auth_response_time', 'coa_count', 'coa_response_time', 'dm_count', 'dm_response_time'], 'required'],
            [['date', 'hour', 'start_count', 'update_count', 'stop_count', 'auth_count', 'coa_count', 'dm_count'], 'integer'],
            [['start_response_time', 'update_response_time', 'stop_response_time', 'auth_response_time', 'coa_response_time', 'dm_response_time'], 'number'],
            [['my_ip'], 'string', 'max' => 16],
            [['proc'], 'string', 'max' => 32],
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
            'my_ip' => 'My Ip',
            'proc' => 'Proc',
            'start_count' => 'Start Count',
            'start_response_time' => 'Start Response Time',
            'update_count' => 'Update Count',
            'update_response_time' => 'Update Response Time',
            'stop_count' => 'Stop Count',
            'stop_response_time' => 'Stop Response Time',
            'auth_count' => 'Auth Count',
            'auth_response_time' => 'Auth Response Time',
            'coa_count' => 'Coa Count',
            'coa_response_time' => 'Coa Response Time',
            'dm_count' => 'Dm Count',
            'dm_response_time' => 'Dm Response Time',
        ];
    }

    /**
     * 获取所有进程最近30天
     * @return array|void
     */
    public function getEfficiencyStatus($type)
    {
        try {
            $xAxis = $this->getX();

            $count = $this->getCount($type);
            $query = self::find()
                ->select($this->selectFields)
                ->where('date>=:sta and date<=:end and proc=:proc', [
                    ':sta' => strtotime($this->start_time),
                    ':end' => strtotime($this->stop_time) + 86399,
                    ':proc' => $this->process_default
                ]);
            if (!empty($this->my_ip)) {
                $query->andWhere(['my_ip' => $this->my_ip]);
            }
            $query->groupBy('my_ip, hour');
            if ($count == 1) {
                //只采集了一台机器状态
                $data = $query->indexBy('hour')->asArray()->all();
                if (empty($data)) {
                    $rs = ['code' => 404, 'msg' => '没有数据'];
                } else {
                    $rs = $this->getSingleData($xAxis, $data);
                }
            } else {
                $data = $query->asArray()->all();
                if (empty($data)) {
                    $rs = ['code' => 404, 'msg' => '没有数据'];
                } else {
                    $rs = $this->getMultiData($xAxis, $data);
                }
            }
        } catch (\Exception $e) {
            $rs = ['code' => 500, 'msg' => '获取cpu状态发生异常:' . $e->getMessage()];
        }

        return $rs;
    }

    /**
     * @param $times
     * @param $data
     * @return array
     */
    public function getMultiData($times, $data)
    {
        $rs = $base = $yAxis = $xAxis = $series = $table = [];
        $base = $this->inner[$this->process_default];
        $table['top_header'] = $this->getHeader('multi');
        $table['detail_header'] = $this->getHeader();
        $legends = $this->getLegends();
        foreach ($data as $v) {
            $date = date('Y-m-d G a', $v['date']);
            $yAxis[$v['hour']][$v['my_ip']]['start_response_time'] = $start = sprintf('%.2f', $v['start_response_time']);
            $yAxis[$v['hour']][$v['my_ip']]['update_response_time'] = $update = sprintf('%.2f', $v['update_response_time']);
            $yAxis[$v['hour']][$v['my_ip']]['auth_response_time'] = $auth = sprintf('%.2f', $v['auth_response_time']);
            $yAxis[$v['hour']][$v['my_ip']]['stop_response_time'] = $stop = sprintf('%.2f', $v['stop_response_time']);
            $yAxis[$v['hour']][$v['my_ip']]['coa_response_time'] = $coa = $v['coa_response_time'];
            $yAxis[$v['hour']][$v['my_ip']]['dm_response_time'] = $dm = $v['dm_response_time'];
            $table['data'][$date]['data'] = [$date, $start . 'ms', $stop . 'ms', $auth . 'ms', $stop . 'ms', $coa . 'ms', $dm . 'ms'];
            $table['data'][$date]['detail'][] = [$v['my_ip'], $date, $start . 'ms', $stop . 'ms', $auth . 'ms', $stop . 'ms', $coa . 'ms', $dm . 'ms'];
        }
        foreach ($times as $time) {
            $xAxis[] = $time.Yii::t('app', 'hours');
            if (isset($yAxis[$time])) {
                foreach ($base as $val) {
                    $series[$val]['start_response_time'][] = isset($yAxis[$time][$val]) ? $yAxis[$time][$val]['start_response_time'] : 0.00;
                    $series[$val]['update_response_time'][] = isset($yAxis[$time][$val]) ? $yAxis[$time][$val]['update_response_time'] : 0.00;
                    $series[$val]['stop_response_time'][] = isset($yAxis[$time][$val]) ? $yAxis[$time][$val]['stop_response_time'] : 0.00;
                    $series[$val]['auth_response_time'][] = isset($yAxis[$time][$val]) ? $yAxis[$time][$val]['auth_response_time'] : 0.00;
                    $series[$val]['coa_response_time'][] = isset($yAxis[$time][$val]) ? $yAxis[$time][$val]['coa_response_time'] : 0.00;
                    $series[$val]['dm_response_time'][] = isset($yAxis[$time][$val]) ? $yAxis[$time][$val]['dm_response_time'] : 0.00;
                }
            } else {
                foreach ($base as $val) {
                    $series[$val]['start_response_time'][] = 0.00;
                    $series[$val]['update_response_time'][] = 0.00;
                    $series[$val]['stop_response_time'][] = 0.00;
                    $series[$val]['auth_response_time'][] = 0.00;
                    $series[$val]['coa_response_time'][] = 0.00;
                    $series[$val]['dm_response_time'][] = 0.00;
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

    public function getSingleData($times, $data)
    {
        $rs = $xAxis = $legends = $series = $table = [];
        $ip = '';
        $legends = $this->getLegends();
        $table['header'] = $this->getHeader();
        foreach ($times as $time) {
            $xAxis[] = $date = date('Y-m-d G a', strtotime("$this->start_time +$time hours"));
            if (isset($data[$time])) {
                $arr = $data[$time];
                $series['start_response_time'][] = $start = sprintf('%.2f', $arr['start_response_time']);
                $series['update_response_time'][] = $update = sprintf('%.2f', $arr['update_response_time']);
                $series['stop_response_time'][] = $stop = sprintf('%.2f', $arr['stop_response_time']);
                $series['auth_response_time'][] = $auth = sprintf('%.2f', $arr['auth_response_time']);
                $series['coa_response_time'][] = $coa = $arr['coa_response_time'];
                $series['dm_response_time'][] = $dm = $arr['dm_response_time'];
                $ip = $arr['my_ip'];
                $table['data'][]  = [$ip, $date, $start . 'ms', $update.'ms',$stop . 'ms', $auth . 'ms',  $coa . 'ms', $dm . 'ms'];
            } else {
                $series['start_response_time'][] = 0.00;
                $series['update_response_time'][] = 0.00;
                $series['stop_response_time'][] = 0.00;
                $series['auth_response_time'][] = 0;
                $series['dm_response_time'][] = 0;
                $series['httpd'][] = 0;
            }
        }
        $series = $this->getLineSeries('line', $series);

        return [
            'code' => 200,
            'single' => true,
            'series' => $series,
            'subtext' => $ip,
            'text' => Yii::t('app', 'proc') . ':' . $this->process_default,
            'legends' => $legends,
            'xAxis' => $xAxis,
            'table' => $table
        ];
    }
}
