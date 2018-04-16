<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2017/6/8
 * Time: 13:28
 */

namespace center\modules\report\models\detail;

use yii;

class BaseModel extends yii\db\ActiveRecord
{
    public $table_name;
    public $start_time;
    public $stop_time;
    public $child_name;
    public $indexBy;
    public $realModel;
    public $timePoint;
    public $sql_type;

    /* @inheritdoc
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
            [['start_time', 'stop_time', 'timePoint', 'device_ip', 'sql_type'], 'safe'],
        ];
    }

    /**
     * 默认获取最近30天系统机器运行状况
     */
    public function init()
    {
        $this->start_time = date('Y-m-d', strtotime("-30 days"));
        $this->stop_time = date('Y-m-d', strtotime("-1 days"));
        $this->child_name = 'day';
        $this->sql_type = 'cpu';
        parent::init(); //Todo::change some items
    }

    public function getLegends($type = 'cpu')
    {
        $legends = [];
        switch ($type) {
            case 'cpu':
                $legends = [Yii::t('app', 'cpu'), Yii::t('app', 'mem'), Yii::t('app', 'mem_cache'), Yii::t('app', 'loads'), Yii::t('app', 'process'), Yii::t('app', 'httpd')];
                break;
            case 'efficiency':
                $legends = [Yii::t('app', 'start_response_time'), Yii::t('app', 'update_response_time'), Yii::t('app', 'stop_response_time'), Yii::t('app', 'auth_response_time'), Yii::t('app', 'coa_response_time'), Yii::t('app', 'dm_response_time')];
                break;
            case 'srun':
                $legends = [Yii::t('app', 'read_count'), Yii::t('app', 'write_count'), Yii::t('app', 'cpu_percent'), Yii::t('app', 'mem_percent'), Yii::t('app', 'iowait')];
                break;
        }

        return $legends;
    }

    //验证有效性
    public function validateField($flag = false)
    {
        $start_time = strtotime($this->start_time); //开始时间
        $stop_time = strtotime($this->stop_time); //结束时间

        if ($stop_time < $start_time) {
            $this->addError('stop_time', Yii::t('app', 'end time error'));
            return false;
        }
        if ($stop_time > time()) {
            $this->addError('stop_time', Yii::t('app', 'report operate remind19'));
            return false;
        }
        if (!empty($flag)) {
            if (empty($this->device_ip)) {
                $this->addError('device_ip', Yii::t('app', 'ip地址不能为空'));
                return false;
            }
        }

        return true;
    }

    /**
     * @return int|mixed
     */
    public function getCount()
    {
        $query = self::find()
            ->select('count(distinct(device_ip)) nums')
            ->where('date>=:sta and date<=:end', [
                ':sta' => strtotime($this->start_time),
                ':end' => strtotime($this->stop_time) + 86399,
            ]);
        if (!empty($this->device_ip)) {
            $query->andWhere(['device_ip' => $this->device_ip]);
        }
        $num = $query->asArray()->one();

        return !is_null($num['nums']) ? $num['nums'] : 0;
    }

    /**
     * 设置子类
     * @return bool
     */
    public function setChildName()
    {
        if (!empty($this->timePoint)) {
            $this->setTime();
        }
        $sta = strtotime($this->start_time);
        $stop = strtotime($this->stop_time);

        if ($stop - $sta > 84000) {
            $this->child_name = 'day';
        } else {
            $this->child_name = 'hour';
        }

        return true;
    }

    /**
     * @return bool
     */
    public function getRealModel()
    {
        switch ($this->child_name) {
            case 'day':
                $this->realModel = new SystemStatusDay();
                break;
            case 'hour':
                $this->realModel = new SystemStatusHour();
                break;
            default:
                $this->realModel = new SystemStatusDay();
                break;
        }
        $this->realModel->start_time = $this->start_time;
        $this->realModel->stop_time = $this->stop_time;
        $this->realModel->child_name = $this->child_name;
        $this->realModel->device_ip = $this->device_ip;

        return true;
    }

    /**
     * 组装数据
     * @param $data
     * @return array
     */
    public function getSource($data)
    {
        $yAxis = $legends = [];
        $ips = array_keys($data);
        foreach ($ips as $v) {
            $legends[$v] = array_keys($data[$v]);
        }
        foreach ($legends as $k => $names) {
            foreach ($names as $name) {
                $yAxis[$k][$name] = $data[$k][$name];
            }
        }
        $source = [
            'legends' => $legends,
            'yAxis' => $yAxis
        ];

        return $source;
    }

    public function getBeginTime($type)
    {
        switch ($type) {
            case 1:
                $this->table_name = 'partitions_status_hour';
                $this->start_time = time() - 86400;
                $this->stop_time = time();
                $this->child_name = 'hour';
                $this->indexBy = 'hour';
                break;
            case 2:
                $this->table_name = 'partitions_status_hour';
                $this->start_time = strtotime(date('Y-m-d', strtotime('-1 days')));
                $this->stop_time = strtotime(date('Y-m-d'));
                $this->child_name = 'hour';
                $this->indexBy = 'hour';
                break;
            case 3:
                $this->table_name = 'partitions_status_date';
                $this->start_time = strtotime(date('Y-m-1'));
                $this->stop_time = strtotime(date('Y-m-d'));
                $this->child_name = 'day';
                $this->indexBy = 'date';
                break;
            case 4:
                $this->table_name = 'partitions_status_date';
                $this->start_time = strtotime(date('Y-m-1', strtotime('-1 months')));
                $this->stop_time = strtotime(date('Y-m-1'));
                $this->child_name = 'day';
                $this->indexBy = 'date';
                break;
        }

        return true;
    }

    /**
     * 根据子类的类名的不同获取各自的x横轴坐标
     *
     * */
    public function getX()
    {
        if (strpos($this->child_name, 'hour') !== false) { //如果是小时返回小时
            $arr = array_fill(0, 24, 1);
            return array_keys($arr);
        }
        if (strpos($this->child_name, 'day') !== false) {  //如果是天返回天
            $startTime = strtotime($this->start_time);
            $stopTime = strtotime($this->stop_time);
            $dateArr = [];
            if ($startTime == $stopTime) {
                $dateArr[] = $startTime;
            } else {
                while (1) {
                    $dateArr[] = $startTime;
                    $startTime = $startTime + 86400;
                    if ($startTime == $stopTime) {
                        $dateArr[] = $startTime;
                        break;
                    }
                }
            }
            return $dateArr;
        }
    }

    public static function getAllSqlType()
    {
        return [
            'cpu' => 'cpu,mem等状态',
            'efficiency' => '各进程响应状态',
            'redis-server' => 'redis状态',
            'mysql' => 'mysql状态',
            'srun_intf_main' => 'srun核心',
            'httpd' => 'Apache状态',
            'nginx' => 'nginx状态',
            'srun_weixin' => '微信监控'
        ];
    }

    /**
     * 获取监控数据
     * @return array
     */
    public function getSourceByType()
    {
        $source = [];
        $sta = strtotime($this->start_time);
        $end = $this->start_time == $this->stop_time ? strtotime($this->stop_time) + 86399 : strtotime($this->stop_time);

        try {
            if (in_array($this->sql_type, ['redis-server', 'mysql', 'srun_intf_main', 'httpd', 'nginx', 'srun_weixin'])) {
                $source = $this->getProcessSource($sta, $end, $this->sql_type);
            } else {
                switch ($this->sql_type) {
                    case 'cpu':
                        $source = $this->getCpuSource($sta, $end);
                        break;
                    case 'efficiency':
                        $source = $this->getEfficiencySource($sta, $end);
                        break;
                    default :
                        $source = $this->getCpuSource($sta, $end);
                        break;
                }
            }

        } catch (\Exception $e) {
            $source = ['code' => 500, 'msg' => '获取监控数据发生异常:' . $e->getMessage()];
        }

        return $source;
    }

    public function getFields()
    {
        $fields = ['read_count', 'write_count', 'cpu_percent', 'mem_percent', 'io_wait'];

        return $fields;
    }

    /**
     * 获取进程监控数据
     * @param $sta
     * @param $end
     * @param string $type
     * @return array
     */
    public function getProcessSource($sta, $end, $type = 'redis-server')
    {
        $fields = $this->getFields();
        $data = (new yii\db\Query())
            ->select($fields)
            ->addSelect('date')
            ->where(['between', 'date', $sta, $end])
            ->andWhere(['device_ip' => $this->device_ip])
            ->andWhere(['process_name' => $type])
            ->from('process_io_counters_hour')
            ->indexBy('date')
            ->all();
        if (empty($data)) {
            $rs = ['code' => 404, 'msg' => Yii::t('app', 'no record')];
        } else {
            $x = $this->getXaxis($sta, $end);
            $xAxis = $legends = [];
            $table['header'] = $this->getHeader('srun');
            foreach ($x as $time) {
                $date = date('Y-m-d G a', $time);
                $xAxis[] = $date;
                if (isset($data[$time])) {
                    $arr = $data[$time];
                    foreach ($fields as $key) {
                        $series[$key][] = sprintf('%.2f', $arr[$key]);
                    }
                    $table['data'][] = [$this->device_ip, $date, $type, $arr['read_count'], $arr['write_count'], $arr['cpu_percent'] . '%', $arr['mem_percent'] . '%', $arr['io_wait'] . '%'];
                } else {
                    foreach ($fields as $key) {
                        $series[$key][] = 0.00;
                    }
                }
            }
            $legends = $this->getLegends('srun');
            $series = $this->getLineSeries('line', $series);
            $rs = [
                'code' => 200,
                'legends' => $legends,
                'xAxis' => $xAxis,
                'series' => $series,
                'table' => $table,
                'subtext' => 'ip:' . $this->device_ip,
                'text' => Yii::t('app', 'proc').':'.$type,
                'single' => true
            ];
        }

        return $rs;
    }

    /**
     * 获取cpu资源
     * @param $sta
     * @param $end
     * @return array
     */
    public function getCpuSource($sta, $end)
    {
        $data = SystemStatusHour::find()
            ->select(['device_ip', 'cpu', 'mem', 'mem-cahced mem_cache', 'loads', 'proccess process', 'httpd', 'date'])
            ->where(['between', 'date', $sta, $end])
            ->andWhere(['device_ip' => $this->device_ip])
            ->indexBy('date')
            ->asArray()
            ->all();
        if (empty($data)) {
            $rs = ['code' => 404, 'msg' => Yii::t('app', 'no record')];
        } else {
            $x = $this->getXaxis($sta, $end);
            $xAxis = $legends = [];
            $table['header'] = $this->getHeader();
            foreach ($x as $time) {
                $date = date('Y-m-d G a', $time);
                $xAxis[] = $date;
                if (isset($data[$time])) {
                    $arr = $data[$time];
                    $series['cpu'][] = $cpu_rate = sprintf('%.2f', $arr['cpu']);
                    $series['mem'][] = $mem_rate = sprintf('%.2f', $arr['mem']);
                    $series['mem_cache'][] = $mem_cache_rate = sprintf('%.2f', $arr['mem_cache']);
                    $series['loads'][] = $load = $arr['loads'];
                    $series['process'][] = $process = $arr['process'];
                    $series['httpd'][] = $httpd = $arr['httpd'];
                    $table['data'][] = [$this->device_ip, $date, $cpu_rate . '%', $mem_rate . '%', $mem_cache_rate . '%', $load, $process, $httpd];
                } else {
                    $series['cpu'][] = 0.00;
                    $series['mem'][] = 0.00;
                    $series['mem_cache'][] = 0.00;
                    $series['loads'][] = 0;
                    $series['process'][] = 0;
                    $series['httpd'][] = 0;
                }
            }
            $legends = $this->getLegends();
            $series = $this->getLineSeries('line', $series);
            $rs = [
                'code' => 200,
                'legends' => $legends,
                'xAxis' => $xAxis,
                'series' => $series,
                'table' => $table,
                'subtext' => 'ip:' . $this->device_ip,
                'text' => '监控信息',
                'single' => true
            ];
        }

        return $rs;
    }

    public function getEfficiencySource($sta, $end)
    {
        try {
            $model = new EfficiencyReportHour();
            $baseArr = $model->ip_process;
            //var_dump($baseArr);exit;
            $count = isset($baseArr[$this->device_ip]) ? count($baseArr[$this->device_ip]) : 0;
            if ($count == 0) {
                $rs = ['code' => 403, 'msg' => Yii::t('app', '未注册监控进程')];
            } else {
                Yii::$app->session->set('procs', $baseArr[$this->device_ip]);
                $rs = $this->getMultiEfficiencySource($sta, $end, $baseArr[$this->device_ip]);
            }

        } catch (\Exception $e) {
            echo $e->getLine();
            $rs = ['code' => 500, 'msg' => ' 获取各大进程监控数据发生异常:' . $e->getMessage()];
        }


        return $rs;
    }

    /**
     * 获取性能监控资源
     * @param $sta
     * @param $end
     * @param $procs
     * @return array
     */
    public function getMultiEfficiencySource($sta, $end, $procs)
    {
        $times = $this->getXaxis($sta, $end);
        $legends = $this->getLegends('efficiency');
        $table['header'] = $this->getHeader('efficiency');
        $data = EfficiencyReportHour::find()
            ->select(['my_ip', 'date', 'proc', 'start_response_time', 'update_response_time', 'stop_response_time',
                'auth_response_time', 'coa_response_time', 'dm_response_time', 'start_count', 'update_count', 'stop_count',
                'auth_count', 'coa_count', 'dm_count', 'hour'
            ])
            ->where('date>=:sta and date<=:end and my_ip = :device', [
                ':sta' => $sta,
                ':end' => $end,
                ':device' => $this->device_ip
            ])
            ->andWhere(['proc' => $procs])
            ->asArray()
            ->all();
        if (!empty($data)) {
            $table['data'] = $series = $yAxis = [];
            foreach ($data as $val) {
                $yAxis[$val['date']][$val['proc']] = $val;
            }

            foreach ($times as $time) {
                $xAxis[] = $date = date('Y-m-d G a', $time);
                if (isset($yAxis[$time])) {
                    foreach ($procs as $proc) {
                        $arr = isset($yAxis[$time][$proc]) ? $yAxis[$time][$proc] : [];
                        $series[$proc]['start_response_time'][] = $start = sprintf('%.2f', $arr['start_response_time']);
                        $series[$proc]['update_response_time'][] = $update = sprintf('%.2f', $arr['update_response_time']);
                        $series[$proc]['stop_response_time'][] = $stop = sprintf('%.2f', $arr['stop_response_time']);
                        $series[$proc]['auth_response_time'][] = $auth = sprintf('%.2f', $arr['auth_response_time']);
                        $series[$proc]['coa_response_time'][] = $coa = sprintf('%.2f', $arr['coa_response_time']);
                        $series[$proc]['dm_response_time'][] = $dm = sprintf('%.2f', $arr['dm_response_time']);
                        $table['data'][$proc][] = [$date, $proc, $start . 'ms', $update . 'ms', $auth . 'ms', $stop . 'ms', $coa . 'ms', $dm . 'ms'];
                    }
                } else {
                    foreach ($procs as $proc) {
                        $series[$proc]['start_response_time'][] = $start = 0;
                        $series[$proc]['update_response_time'][] = $update = 0;
                        $series[$proc]['stop_response_time'][] = $stop = 0;
                        $series[$proc]['auth_response_time'][] = $auth = 0;
                        $series[$proc]['coa_response_time'][] = $coa = 0;
                        $series[$proc]['dm_response_time'][] = $dm = 0;
                    }
                }
            }
            $ySeries = [];
            foreach ($procs as $proc) {
                $ySeries[$proc] = $this->getLineSeries('line', $series[$proc], true);
            }

            return [
                'code' => 200,
                'base' => $procs,
                'series' => $ySeries,
                'xAxis' => $xAxis,
                'table' => $table,
                'legends' => $legends,
                'multi' => true
            ];
        } else {
            $rs = ['code' => 404, 'msg' => Yii::t('app', 'no record')];
        }

        return $rs;

    }

    /**
     * 获取时间x轴
     * @param $sta
     * @param $end
     * @return array
     */
    public function getXaxis($sta, $end)
    {
        $xAxis = [];
        while ($sta < $end) {
            $xAxis[] = $sta;
            $sta += 3600;
        }

        return $xAxis;
    }

    /**
     * @param $type
     * @param $data
     * @return array
     */
    public function getLineSeries($type, $data, $flag = false)
    {
        $result = [];
        foreach ($data as $k => $v) {
            $object = new \stdClass();
            $object->type = $type;
            $object->name = \Yii::t('app', $k);
            if (!$flag) {
                if (in_array($k, ['cpu', 'mem', 'mem_cache'])) {
                    $object2 = new \StdClass();
                    $object2->fomatter = '{value} %';
                    $object->axisLabel = $object2;
                    $object->yAxisIndex = 1;
                } else {
                    $object->yAxisIndex = 0;
                }
            }

            $object->data = $v;
            $result[] = $object;
        }

        return $result;
    }

    /**
     * 组装多个option
     * @param $data
     * @param $legends
     * @return array
     */
    public function getArrSeries($data, $legends)
    {
        $option = $base = [];
        foreach ($legends as $v) {
            $object = new \stdClass();
            $object->type = 'line';
            $object->name = \Yii::t('app', $v);
            if (in_array($v, [Yii::t('app', 'cpu'), Yii::t('app', 'mem'), Yii::t('app', 'mem_cache')])) {
                $object2 = new \StdClass();
                $object2->fomatter = '{value} %';
                $object->axisLabel = $object2;
                $object->yAxisIndex = 0;
            } else {
                $object->yAxisIndex = 1;
            }
            $base[] = $object;
        }
        foreach ($data as $k => $v) {
            $text = Yii::t('app', 'system monitor');
            $subtext = $k;
            $object = new \stdClass();
            $object2 = new \StdClass();
            $object2->text = $text;
            $object2->subtext = $subtext;
            $object->title = $object2;
            $series = [];
            foreach ($v as $one) {
                $object3 = new \StdClass();
                $object3->data = $one;
                $series[] = $object3;
            }
            $object->series = $series;
            $option[] = $object;
        }

        return [
            'base' => $base,
            'option' => $option
        ];
    }

    /**
     * 获取头部
     * @param string $type
     * @return array
     */
    public function getHeader($type = 'system')
    {
        $header = [];
        switch ($type) {
            case 'system':
                $header = [
                    'device_ip' => Yii::t('app', 'device ip'),
                    'date' => Yii::t('app', 'user time'),
                    'cpu' => Yii::t('app', 'cpu'),
                    'mem' => Yii::t('app', 'mem'),
                    'mem_cache' => Yii::t('app', 'mem cache'),
                    'loads' => Yii::t('app', 'loads'),
                    'process' => Yii::t('app', 'process'),
                    'httpd' => Yii::t('app', 'httpd')
                ];
                break;
            case 'multi':
                $header = [
                    'date' => Yii::t('app', 'user time'),
                    'cpu' => Yii::t('app', 'cpu'),
                    'mem' => Yii::t('app', 'mem'),
                    'mem_cache' => Yii::t('app', 'mem cache'),
                    'loads' => Yii::t('app', 'loads'),
                    'process' => Yii::t('app', 'process'),
                    'httpd' => Yii::t('app', 'httpd')
                ];
                break;
            case 'efficiency':
                $header = [
                    'date' => Yii::t('app', 'user time'),
                    'proc' => Yii::t('app', 'proc'),
                    'start_response_time' => Yii::t('app', 'start_response_time'),
                    'update_response_time' => Yii::t('app', 'update_response_time'),
                    'stop_response_time' => Yii::t('app', 'stop_response_time'),
                    'auth_response_time' => Yii::t('app', 'auth_response_time'),
                    'coa_response_time' => Yii::t('app', 'coa_response_time'),
                    'dm_response_time' => Yii::t('app', 'dm_response_time')
                ];
                break;
            case 'srun':
                $header = [
                    'device_ip' => Yii::t('app', 'device ip'),
                    'date' => Yii::t('app', 'user time'),
                    'proc' => Yii::t('app', 'process name'),
                    'start_response_time' => Yii::t('app', 'read_count'),
                    'update_response_time' => Yii::t('app', 'write_count'),
                    'stop_response_time' => Yii::t('app', 'cpu_percent'),
                    'auth_response_time' => Yii::t('app', 'mem_percent'),
                    'coa_response_time' => Yii::t('app', 'iowait'),
                ];
                break;
        }

        return $header;
    }

    /**
     * 设置时间
     * @return bool
     */
    public function setTime()
    {
        $season = ceil((date('n')) / 3);//当月是第几季度
        switch ($this->timePoint) {
            case 1: //本月
                $this->start_time = date('Y-m-01');
                $this->stop_time = date('Y-m-d', strtotime('-1 days'));
                break;
            case 2: //上月
                $this->start_time = date('Y-m-01', mktime(0, 0, 0, date("m") - 1, 1, date("Y")));
                $this->stop_time = date('Y-m-d', mktime(23, 59, 59, date("m"), 0, date("Y")));
                break;
            case 3: //本季度
                $this->start_time = date('Y-m-d', mktime(0, 0, 0, $season * 3 - 3 + 1, 1, date('Y')));
                $this->stop_time = date('Y-m-d', strtotime('-1 days'));
                break;
            case 4: //上季度
                $this->start_time = date('Y-m-01', mktime(0, 0, 0, $season * 3 - 6 + 1, 1, date('Y')));
                $this->stop_time = date('Y-m-d', mktime(23, 59, 59, $season * 3 - 3, date('t', mktime(0, 0, 0, $season * 3 - 3, 1, date("Y"))), date('Y')));
                break;
            case 5: //最近24小时
                $stop = date('H');
                $stop = strtotime(date('Y-m-d')) + ($stop - 1) * 3600;
                $start = $stop - 86400;
                $this->start_time = date('Y-m-d H:00:00', $start);
                $this->stop_time = date('Y-m-d H:00:00', $stop);
                break;
            case 6: //昨天
                $this->start_time = date('Y-m-d', strtotime('-1 days'));
                $this->stop_time = date('Y-m-d');
                break;
            case 7: //最近7天
                $this->start_time = date('Y-m-d', strtotime('-7 days'));
                $this->stop_time = date('Y-m-d', strtotime('-1 days'));
                break;
            default :
                $this->start_time = date('Y-m-01', strtotime('-30 days'));
                $this->stop_time = date('Y-m-d', strtotime('-1 days'));
                break;
        }

        return true;
    }

}