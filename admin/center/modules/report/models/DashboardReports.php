<?php

namespace center\modules\report\models;

use center\models\EfficiencyReport;
use Yii;
use center\extend\Tool;
use yii\db\Query;
use center\modules\report\models\detail\SystemStatus;
use center\modules\report\models\Efficiency;

/**
 * This is the model class for table "DashboardReports".
 *
 */
class DashboardReports extends \yii\db\ActiveRecord
{


    public function netStatusAttribute()
    {
        return [
            'recv_bytes' => Yii::t('app', 'recv_bytes'),
            'send_bytes' => Yii::t('app', 'send_bytes'),
            'recv_ptks' => Yii::t('app', 'recv_ptks'),
            'send_ptks' => Yii::t('app', 'send_ptks'),
        ];
    }


    public function systemStatusAttribute()
    {
        return [
            'cpu' => Yii::t('app', 'cpu'),
            'mem' => Yii::t('app', 'mem'),
            'mem-cahced' => Yii::t('app', 'mem-cahced'),
            'loads' => Yii::t('app', 'loads'),
            'proccess' => Yii::t('app', 'proccess'),
            'httpd' => Yii::t('app', 'httpd'),
        ];
    }

    public function netConnectStatusAttribute()
    {
        return [
            'port80' => Yii::t('app', 'port80'),
            'port8080' => Yii::t('app', 'port8080'),
            'port8081' => Yii::t('app', 'port8081'),
            'port8800' => Yii::t('app', 'port8800'),
            'port69' => Yii::t('app', 'port69'),
            'port8069' => Yii::t('app', 'port8069'),
        ];
    }

    public function devicenameAttribute()
    {
        return [
            'read_count' => Yii::t('app', 'read count'),
            'write_count' => Yii::t('app', 'write count'),
            'read_bytes' => Yii::t('app', 'read bytes'),
            'write_bytes' => Yii::t('app', 'write bytes'),
            'cpu_perncent' => Yii::t('app', 'cpu perncent'),
            'mem_percent' => Yii::t('app', 'mem percent'),
            'iowait' => Yii::t('app', 'iowait'),
        ];
    }

    /*
    ** 检测图标状态
    */

    public static function dataStatus($param, $ip)
    {
        $Efficiency = new Efficiency();
        $model = new DashboardReports();
        $larger = 100;
        $less = 70;
        switch ($param) {
            case 'httpd_process_data':
                $data = $model::system_status('httpd', $ip);
                $larger = 5000;
                $less = 2000;
                break;
            case 'system_load':
                $data = $model::system_status('loads', $ip);
                $larger = 1;
                $less = 0.7;
                break;
            case 'portal_server':
                $data = $Efficiency::CheckStatusPortal($ip);
                $larger = 80;
                $less = 60;
                break;
            case 'disk_io_status':
                $data = $model::disk_io_status($ip);
                $larger = 90;
                $less = 70;
                break;
            case 'system_status':
                $data = $model::system_status('mem', $ip);
                $larger = 90;
                $less = 70;
                break;
            case 'radisud':
                $data = $Efficiency::CheckStatusRadiusd($ip);
                $larger = 80;
                $less = 60;
                break;
            case 'hard_disk_data':
                $data = $model::hard_disk_status($ip);
                $larger = 80;
                $less = 60;
                break;
            case 'system_data':
                $data = $model::system_status('cpu', $ip);
                $larger = 90;
                $less = 60;
                break;
            case 'data_acquisition':
                $data = $Efficiency::CheckStatusAuth($ip);
                $larger = 80;
                $less = 60;
                break;
            case 'mysqld':
                $data = $model::ProcessIo_status('mysqld', $ip);
                $larger = 90;
                $less = 70;
                break;
            case 'redis_status':
                $data = $model::ProcessIo_status('redis_server', $ip);
                $larger = 90;
                $less = 70;
                break;
            default:
                $data = 0;
        }
        $result = $Efficiency::searchStatus($data, $larger, $less);
        return $result;
    }


    public static function disk_io_status($ip)
    {
        $query = new Query();
        $nowTim = time() - 7200;
        $query->from('disk_io_counters');
        $query->select(['read_time', 'write_time']);
        $query->andWhere(['>=', 'time', $nowTim]);
        $query->andWhere(['=', 'device_ip', $ip]);
        $data = $query->All();
        $arr = array();
        if (!empty($data)) {
            foreach ($data as $x => $y) {
                $max = ($y["read_time"] > $y["write_time"]) ? $y["read_time"] : $y["write_time"];
                $arr[] = $max / 1000 / 60;
            }
            sort($arr);
            $end = array_pop($arr);
            return $end;
        }
        return 0;

    }

    public static function hard_disk_status($ip)
    {
        $nowTim = time();
        $endTim = time() - 7200;
        $query = new Query();
        $query->from('partitions_status');
        $query->select(['used_percent']);
        $query->andWhere(['=', 'device_ip', $ip]);
        $query->andWhere(['<=', 'time', $nowTim]);
        $query->andWhere(['>=', 'time', $endTim]);
        $yAxisAllData = $query->All();

        $arr = array();
        if (!empty($yAxisAllData)) {
            foreach ($yAxisAllData as $x => $y) {
                $arr[] = $y["used_percent"];
            }
            sort($arr);
            $end = array_pop($arr);
            return $end;
        }
        return 0;
    }

    public static function ProcessIo_status($param, $ip)
    {
        $nowTim = time();
        $endTim = time() - 7200;
        $query = new Query();
        $query->from('process_io_counters');
        $query->select(["mem_percent"]);
        $query->andWhere(['=', 'process_name', $param]);
        $query->andWhere(['=', 'device_ip', $ip]);
        $query->andWhere(['<=', 'time', $nowTim]);
        $query->andWhere(['>=', 'time', $endTim]);
        $query->orderBy('time desc');
        $yAxisAllData = $query->all();
        $arr = array();
        if (!empty($yAxisAllData)) {
            foreach ($yAxisAllData as $x => $y) {
                $arr[] = $y["mem_percent"];
            }
            sort($arr);
            $end = array_pop($arr);
            return $end;
        }
        return 0;
    }

    /**
     * 获取最近三小时状态
     * @param $param
     * @param $ip
     * @return int
     */
    public static function system_status($param, $ip)
    {
        $nowTim = time();
        $endTim = time() - 7200;
        $query = new Query();
        $query->from('system_status');
        $query->select(["max({$param}) {$param}"]);
        $query->andWhere(['=', 'device_ip', $ip]);
        $query->orderBy("{$param} desc");
        $query->andWhere(['<=', 'time', $nowTim]);
        $query->andWhere(['>=', 'time', $endTim]);
        $data = $query->One();
        if (!empty($data)) {
            return $data["{$param}"];
        }
        return 0;
    }

    /*
    ** 进程状态
    */
    public function ProcessIoCounters($param, $ip)
    {
        $ProcessIo = DashboardReports::ProcessIoData($param, $ip);

        return $ProcessIo;
    }

    /*
    ** 进程状态数据查询
    */
    public function ProcessIoData($param, $ip)
    {
        $rs = [];
        try {
            $nowTim = time();
            $endTim = time() - 7200;
            $query = new Query();
            $query->from('process_io_counters');
            $query->select(['process_name', 'read_count', 'write_count', 'cpu_perncent', 'mem_percent', 'iowait', 'time']);
            $query->andWhere(['=', 'process_name', $param]);
            $query->andWhere(['=', 'device_ip', $ip]);
            $query->andWhere(['<=', 'time', $nowTim]);
            $query->andWhere(['>=', 'time', $endTim]);
            $query->orderBy('time asc');
            $data = $query->all();
            if (!empty($data)) {
                $legends = [Yii::t('app', 'read_count'), Yii::t('app', 'write_count'), Yii::t('app', 'cpu_percent'), Yii::t('app', 'mem_percent'), Yii::t('app', 'iowait')];
                $series = $xAxis = [];
                foreach ($data as $key => $val) {
                    $xAxis[] = date('H:i', $val['time']);
                    $series['read_count'][] = $val['read_count'];
                    $series['write_count'][] = $val['write_count'];
                    $series['cpu_percent'][] = $val['cpu_perncent'];
                    $series['mem_percent'][] = $val['mem_percent'];
                    $series['iowait'][] = $val['iowait'];
                }
                $series = $this->getLineSeries('line', $series);
                $rs = ['code' => 200, 'legends' => $legends, 'xAxis' => $xAxis, 'series' => $series, 'type' => 'line'];
            } else {
                $rs = ['code' => 404, 'msg' => Yii::t('app', 'no record')];
            }
        } catch (\Exception $e) {
            $rs = ['code' => 500, 'msg' => '获取redis状态发生异常'];
        }

        return $rs;
    }


    /**
     * 获取总进程
     * @param $ip
     * @return array
     */
    public function getProcess($ip, $fields = '')
    {
        $rs = [];
        try {
            $flag = empty($fields);
            if (empty($fields)) {
                $fields = 'proccess as process, time, httpd';
            } else {
                $origin = $fields;
                $fields .= ',time';
            }
            $data = SystemStatus::find()
                ->select($fields)
                ->where(['between', 'time', time() - 7200, time()])
                ->andWhere(['device_ip' => $ip])
                ->orderBy('time asc')
                ->asArray()
                ->all();
            if (!empty($data)) {
                if ($flag) {
                    $legends = $this->getLegends('process');
                } else {
                    $legends = $this->getLegends($origin);
                }
                $xAxis = $series = [];
                foreach ($data as $key => $val) {
                    $xAxis[] = date('H:i', $val['time']);
                    if ($flag) {
                        $series['process'][] = $val['process'];
                        $series['httpd'][] = $val['httpd'];
                    } else {
                        $series[$origin][] = $val[$origin];
                    }
                }

                $series = $this->getLineSeries('line', $series);
                $rs = ['code' => 200, 'series' => $series, 'xAxis' => $xAxis, 'legends' => $legends, 'type' => 'line'];
            } else {
                $rs = ['code' => 404, 'msg' => Yii::t('app', 'no record')];
            }
        } catch (\Exception $e) {
            $rs = ['code' => 500, 'msg' => '获取进程数据发生异常' . $e->getMessage()];
        }

        return $rs;
    }

    /**
     * 获取portal_server认证时间
     * @param $ip
     * @params $fields
     * @return array
     */
    public function getPortalServer($ip, $fields = '')
    {
        $rs = [];
        try {
            $flag = empty($fields);
            if (empty($fields)) {
                $fields = 'auth_response_time, time_point';
                $proc = 'srun_portal_server';
            } else {
                $proc = 'rad_auth';
            }
            $data = EfficiencyReport::find()
                ->select($fields)
                ->where(['between', 'time_point', time() - 7200, time()])
                ->andWhere(['my_ip' => $ip])
                ->andWhere(['=', 'proc', $proc])
                ->orderBy('time_point asc')
                ->asArray()
                ->all();
            if (!empty($data)) {
                if ($flag) {
                    $legends = $this->getLegends('portal');
                }
                $xAxis = $series = [];
                foreach ($data as $key => $val) {
                    $xAxis[] = date('H:i', $val['time_point']);
                    if ($flag) {
                        $series['auth_response_time'][] = $val['auth_response_time'];
                    }
                }
                $series = $this->getLineSeries('line', $series);
                $rs = ['code' => 200, 'series' => $series, 'xAxis' => $xAxis, 'legends' => $legends, 'type' => 'line'];
            } else {
                $rs = ['code' => 404, 'msg' => Yii::t('app', 'no record')];
            }
        } catch (\Exception $e) {
            $rs = ['code' => 500, 'msg' => '获取进程数据发生异常' . $e->getMessage()];
        }

        return $rs;
    }

    /**
     * 获取radius数据
     * @param $ip
     * @param string $type
     * @return array
     */
    public function getRadiusdTime($ip, $type = 'auth_response_time')
    {

        $rs = [];
        try {
            $data = EfficiencyReport::find()
                ->select(['time_point', $type])
                ->where(['between', 'time_point', time() - 7200, time()])
                ->andWhere(['my_ip' => $ip])
                ->andWhere(['=', 'proc', 'radiusd'])
                ->orderBy('time_point asc')
                ->asArray()
                ->all();
            if (!empty($data)) {
                $legends = $this->getLegends('portal');
                $xAxis = $series = [];
                foreach ($data as $key => $val) {
                    $xAxis[] = date('H:i', $val['time_point']);
                    $series['auth_response_time'][] = $val['auth_response_time'];
                }
                $series = $this->getLineSeries('line', $series);
                $rs = ['code' => 200, 'series' => $series, 'xAxis' => $xAxis, 'legends' => $legends, 'type' => 'line'];
            } else {
                $rs = ['code' => 404, 'msg' => Yii::t('app', 'no record')];
            }
        } catch (\Exception $e) {
            $rs = ['code' => 500, 'msg' => '获取进程数据发生异常' . $e->getMessage()];
        }

        return $rs;
    }

    /**
     * @param $ip
     * @return array
     */
    public function getAuthDetail($ip)
    {
        $rs = [];
        try {
            $data = EfficiencyReport::find()
                ->select(['time_point', 'start_response_time', 'update_response_time', 'stop_response_time'])
                ->where(['between', 'time_point', time() - 7200, time()])
                ->andWhere(['my_ip' => $ip])
                ->andWhere(['=', 'proc', 'rad_auth'])
                ->orderBy('time_point asc')
                ->asArray()
                ->all();
            if (!empty($data)) {
                $legends = $this->getLegends('disk');
                $xAxis = $series = [];
                foreach ($data as $key => $val) {
                    $xAxis[] = date('H:i', $val['time_point']);
                    $series['start_response_time'][] = $val['start_response_time'];
                    $series['update_response_time'][] = $val['update_response_time'];
                    $series['stop_response_time'][] = $val['stop_response_time'];
                }
                $series = $this->getLineSeries('line', $series);
                $rs = ['code' => 200, 'series' => $series, 'xAxis' => $xAxis, 'legends' => $legends, 'type' => 'line'];
            } else {
                $rs = ['code' => 404, 'msg' => Yii::t('app', 'no record')];
            }
        } catch (\Exception $e) {
            $rs = ['code' => 500, 'msg' => '获取进程数据发生异常' . $e->getMessage()];
        }

        return $rs;
    }

    /*
    ** 磁盘使用
    */
    public function DiskIoCounters($ip)
    {
        $rs = [];
        try {
            $query = new Query();
            $nowTim = time() - 7200;
            $query->from('disk_io_counters');
            $query->select(['device_name', 'read_count', 'write_count', 'read_bytes', 'write_bytes', 'time']);
            $query->andWhere(['>=', 'time', $nowTim]);
            $query->andWhere(['=', 'device_ip', $ip]);
            $data = $query->All();
            if (!empty($data)) {
                $base = $legends = $series = $xAxis = [];
                $legends = [
                    Yii::t('app', 'read count'),
                    Yii::t('app', 'write count'),
                    Yii::t('app', 'read bytes'),
                    Yii::t('app', 'write bytes')
                ];
                foreach ($data as $val) {
                    if (!in_array($val['device_name'], $base)) {
                        $base[] = $val['device_name'];
                    }
                    $min = date('H:i', $val['time']);
                    if (!in_array($min, $xAxis)) {
                        $xAxis[] = $min;
                    }
                    $series[$val['device_name']]['read_count'][] = $val['read_count'];
                    $series[$val['device_name']]['write_count'][] = $val['write_count'];
                    $series[$val['device_name']]['read_bytes'][] = sprintf('%.2f', $val['read_bytes'] / 1024);
                    $series[$val['device_name']]['write_bytes'][] = sprintf('%.2f', $val['write_bytes'] / 1024);
                }
                $single = count($base) == 1;
                if ($single) {
                    $series = $this->getLineSeries('line', $series);
                } else {
                    $series = $this->getArrSeries($series, $legends);
                }
                $rs = ['code' => 200, 'msg' => 'ok', 'base' => $base, 'series' => $series, 'legends' => $legends, 'single' => $single, 'type' => 'bar', 'title' => Yii::t('app', 'disk_io_status'), 'xAxis' => $xAxis];
            } else {
                $rs = ['code' => 404, 'msg' => Yii::t('app', 'no record')];
            }
        } catch (\Exception $e) {
            $rs = ['code' => 500, 'msg' => '获取磁盘使用状态异常:' . $e->getMessage()];
        }

        return $rs;
    }

    /**
     * 曲线描述
     * @param string $type
     * @return array
     */
    public static function getLegends($type = 'device')
    {
        $legends = [];
        switch ($type) {
            case 'device':
                $legends = [Yii::t('app', 'disk free total'), Yii::t('app', 'other')];
                break;
            case 'process':
                $legends = [Yii::t('app', 'process'), Yii::t('app', 'httpd')];
                break;
            case 'portal':
                $legends = [Yii::t('app', 'auth_response_time')];
                break;
            case 'loads':
                $legends = [Yii::t('app', 'loads')];
                break;
            case 'disk':
                $legends = [Yii::t('app', 'start_response_time'), Yii::t('app', 'update_response_time'), Yii::t('app', 'stop_response_time')];
                break;
            case 'mem':
                $legends = [Yii::t('app', 'mem')];
                break;
            case 'cpu':
                $legends = [Yii::t('app', 'cpu')];
                break;
        }

        return $legends;
    }

    /**
     * 获取分区状态
     * @param $ip
     * @return array
     */
    public function PartitionStatus($ip)
    {
        try {
            $data = PartitionsStatus::find()
                ->select(['partition_name', 'total_bytes', 'free_bytes'])
                ->where('device_ip=:device', [
                    ':device' => $ip
                ])
                ->groupBy('partition_name')
                ->indexBy('partition_name')
                ->asArray()
                ->all();
            $base = $series = $rows = [];
            if (!empty($data)) {
                $base = array_keys($data);
                $single = count($base) == 1;
                $legends = self::getLegends('device');
                foreach ($data as $key => $val) {
                    if ($single) {
                        $rows[] = ['name' => [Yii::t('app', 'disk free total'), 'value' => $val['free_bytes']]];
                        $rows[] = ['name' => Yii::t('app', 'other'), 'value' => $val['total_bytes'] - $val['free_bytes']];
                    } else {
                        $rows[$key][] = ['name' => Yii::t('app', 'disk free total'), 'value' => $val['free_bytes']];
                        $rows[$key][] = ['name' => Yii::t('app', 'other'), 'value' => $val['total_bytes'] - $val['free_bytes']];
                    }
                }

                if ($single) {
                    $series = $this->getPieSeries(Yii::t('app', 'hard_disk_data'), $rows);
                } else {
                    $series = $this->getArrPieSeries($rows);
                }
                $rs = ['code' => 200, 'msg' => 'ok', 'base' => $base, 'series' => $series, 'legends' => $legends, 'single' => $single, 'title' => Yii::t('app', 'hard_disk_data'), 'type' => 'pie'];
            } else {
                $rs = ['code' => 404, 'msg' => Yii::t('app', 'no record')];
            }
        } catch (\Exception $e) {
            $rs = ['code' => 500, 'msg' => '获取分区状态发生异常' . $e->getMessage()];
        }

        return $rs;
    }
    /*
   **
   */
    public function indexSystemStatus()
    {
        $model = new DashboardReports();
        $nowTim = time();
        $endTim = time()-7200;
        $data = SystemStatus::find()
            ->where(['between', 'time', $endTim, $nowTim])
            ->indexBy('device_ip')
            ->orderBy('cpu desc')
            ->asArray()
            ->all();

        return $data;
    }

    /*
   ** 内存状态数据查询
   */
    public static function SystemStatusData($param,$ip,$flag){
        //取最新数据
        $model = new DashboardReports();
        $nowTim = time();
        $endTim = time()-7200;
        if(!$flag){
            $query1 = new Query();
            $query1->from('system_status');
            $query1->select(['device_ip','cpu','mem','mem-cahced','loads','proccess','httpd','time']);
            $query1->andWhere(['<=', 'time', $nowTim]);
            $query1->andWhere(['>=', 'time', $endTim]);
            $query1->andWhere(['=', 'device_ip', $ip]);
            $query1->orderBy('time desc');
            $systemStatusNew = $query1->One();
            return $systemStatusNew;
        }

        $legend = array();
        $timedata = array();
        $query = new Query();
        $query->from('system_status');
        $query->select(["{$param}",'time']);
        $query->andWhere(['<=', 'time', $nowTim]);
        $query->andWhere(['>=', 'time', $endTim]);
        $query->andWhere(['=', 'device_ip', $ip]);
        $query->orderBy('time asc');
        $yAxisAllData = $query->All();
        $StatusLables = $model->systemStatusAttribute();
        $dataArray = array();
        $dataString = array();
        $i = 0;
        $timedata = array();
        foreach($yAxisAllData as $key=>$value){
            $timedata[] = "'".date('H:i',$value['time'])."'";
            $dataArray["{$param}"][$i] = sprintf("%.2f",$value["{$param}"]);
            $i++;
        }
        foreach($dataArray as $key=>$value){
            $data = implode(',',$value);
            $keyname = $StatusLables[$key];
            $legend[]= "'".$keyname."'";
            $dataString[] = "{name:'".$keyname."',
                            type:'line',
                            smooth:true,
                            itemStyle: {normal: {areaStyle: {type: 'default'}}},
                            data:[".$data."],
                            markPoint : {
                                data : [{type : 'max', name: '".Yii::t('app', 'max')."'}]
                            },
                        }";
        }
        $legend = implode(',',$legend);
        $xAxis = implode(',',$timedata);
        $series = implode(',',$dataString);
        $source = [
            'xAxis' => $xAxis,
            'legend' => $legend,
            'dataString' => $series,
        ];
        return $source;
    }



    /**
     * @param $type
     * @param $data
     * @return array
     */
    public function getLineSeries($type, $data)
    {
        $result = [];
        foreach ($data as $k => $v) {
            $object = new \stdClass();
            $object->type = $type;
            $object->name = \Yii::t('app', $k);
            $object2 = new \stdClass();
            $object2->normal = new \stdClass();
            $object->areaStyle = $object2;
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
            $object4 = new \StdClass();
            $object4->noraml = new \stdClass();
            $object->areaStyle = $object4;
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
     * 组装饼图
     * @param $name
     * @param $data
     * @return \stdClass
     */
    public function getPieSeries($name, $data)
    {

        //设置饼状图样式
        $emphasis = new \stdClass();
        $emphasis->shadowBlur = 10;
        $emphasis->shadowOffsetX = 0;
        $emphasis->shadowColor = 'rgba(0, 0, 0, 0.5)';

        $label = new \stdClass();
        $label->normal =  new \stdClass();
        $label->normal->show = false;
        $itemStyle = new \stdClass();
        $label->emphasis = $emphasis;
        //总的外围包装器
        $series = new \stdClass();
        $series->name = $name;
        $series->type = 'pie';
        $series->radius = '65%';
        $series->center = ['50%', '60%'];
        $series->data = $data;
        $series->label = $label;

        return $series;
    }

    /**
     * 组装多个option
     * @param $data
     * @param $legends
     * @return array
     */
    public function getArrPieSeries($data)
    {
        $option = $base = [];
        foreach ($data as $k => $v) {
            $text = Yii::t('app', 'system monitor');
            $subtext = $k;
            $object = new \stdClass();
            $object2 = new \StdClass();
            $object2->text = $text;
            $object2->subtext = $subtext;
            $object->title = $object2;
            $series = [];
            $object3 = new \StdClass();
            $object3->data = $v;
            $object->series = $object3;
            $option[] = $object;
        }

        return [
            'base' => $base,
            'option' => $option
        ];
    }
}
