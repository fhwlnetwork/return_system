<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2017/6/9
 * Time: 13:28
 */

namespace center\modules\report\models\detail;

use yii;
use common\models\Redis;

class EfficiencyBase extends yii\db\ActiveRecord
{
    public $table_name;
    public $start_time;
    public $stop_time;
    public $child_name;
    public $indexBy;
    public $realModel;
    public $process = [];
    public $ip = [];
    public $process_ip_key = 'process_ip_inner';
    public $process_default;
    public $inner;
    public $timePoint;
    public $ip_process;


    public $selectFields = ['my_ip', 'date','proc', 'start_response_time', 'update_response_time', 'stop_response_time',
        'auth_response_time', 'coa_response_time', 'dm_response_time', 'start_count', 'update_count', 'stop_count',
        'auth_count', 'coa_count', 'dm_count'
    ];
    /* @inheritdoc
     */
    public static function tableName ()
    {
        return 'efficiency_report_day';
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['start_time', 'stop_time', 'timePoint', 'my_ip', 'process_default'], 'safe'],
        ];
    }
    //验证有效性
    public function validateField()
    {
        $start_At = strtotime($this->start_time); //开始时间
        $stop_At = strtotime($this->stop_time); //结束时间

        if ($stop_At < $start_At) {
            $this->addError($this->stop_At, Yii::t('app', 'end time error'));
            return false;
        }
        if ($stop_At > time()) {
            $this->addError($this->stop_At, Yii::t('app', 'report operate remind19'));
            return false;
        }

        return true;
    }
    /**
     * 默认获取最近30天系统机器运行状况
     */
    public function init()
    {
        //获取相关redis注册信息
        if (Redis::executeCommand('exists', $this->process_ip_key)) {
            $rs = json_decode(Redis::executeCommand('get', $this->process_ip_key), true);
            if (!empty($rs)) {
                foreach ($rs as $proc => $val) {
                    $this->process[$proc] = $proc;
                    foreach ($val as $ip) {
                        if (!in_array($ip, $this->ip)) {
                            $this->ip[] = $ip;
                        }
                        $this->ip_process[$ip][] = $proc;
                    }
                }
            }
        } else {
            $lists = Redis::executeCommand('lrange', 'list:efficiency', [0 , -1], 'redis_debug');
            $rs = [];
            if (!empty($lists)) {
                foreach ($lists as $val) {
                    $arr = explode(':', $val);
                    if (!in_array($arr[1], $this->ip)) {
                        $this->ip[$arr[1]] = $arr[1];
                    }
                    $this->ip[] = $arr[1];
                    if (!in_array($arr[0], $this->process)) {
                        $this->process[$arr[0]] = $arr[0];
                    }
                    $this->ip_process[$arr[1]][] = $arr[0];
                    $rs[$arr[0]][] = $arr[1];
                }
                $json = json_encode($rs);
                Redis::executeCommand('set', $this->process_ip_key, [$json]);
                Redis::executeCommand('expire', $this->process_ip_key, [86400]); //保存一天
            }
        }
        $this->inner = $rs;

        $this->start_time = date('Y-m-d', strtotime("-30 days"));
        $this->stop_time = date('Y-m-d', strtotime("-1 days"));
        $this->child_name = 'day';
        $this->process_default = 'radiusd';
        parent::init(); //Todo::change some items
    }

    public function getLegends()
    {
        return [Yii::t('app', 'start_response_time'), Yii::t('app', 'update_response_time'), Yii::t('app', 'stop_response_time'),  Yii::t('app', 'auth_response_time'),Yii::t('app', 'coa_response_time'), Yii::t('app', 'dm_response_time')];
    }

    /**
     * @return int|mixed
     */
    public function getCount($type = 0)
    {
        if ($type == 0) {
           $count = count($this->ip);
        } else {
            if (!empty($this->my_ip)) {
                if (in_array($this->my_ip, $this->inner[$this->process_default])) {
                    return 1;
                } else {
                    return 0;
                }
            }
            $count = isset($this->inner[$this->process_default]) ? count($this->inner[$this->process_default]) : 0;
        }


        return $count;
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
            $this->child_name =  'hour';
        }

        return true;
     }
    /**
     * @return bool
     */
    public function getRealModel()
    {
        switch($this->child_name) {
            case 'day':
                $this->realModel = new EfficiencyReportDay();
                break;
            case 'hour':
                $this->realModel = new EfficiencyReportHour();
                break;
            default:
                $this->realModel = new EfficiencyReportDay();
                break;
        }
        $this->realModel->start_time = $this->start_time;
        $this->realModel->stop_time = $this->stop_time;
        $this->realModel->child_name = $this->child_name;
        $this->realModel->process_default = $this->process_default;
        $this->realModel->process = $this->process;
        $this->realModel->inner = $this->inner;
        $this->realModel->my_ip = $this->my_ip;

        return true;
    }
    /**
     * 组装数据
     * @param $data
     * @return array
     */
    public function getSource ($data)
    {
        $yAxis = $legends = [];
        $ips = array_keys ($data);
        foreach ($ips as $v) {
            $legends[$v] = array_keys ($data[$v]);
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

    public function getBeginTime ($type)
    {
        switch ($type) {
            case 1:
                $this->table_name = 'partitions_status_hour';
                $this->start_time = time()- 86400;
                $this->stop_time = time();
                $this->child_name = 'hour';
                $this->indexBy = 'hour';
                break;
            case 2:
                $this->table_name = 'partitions_status_hour';
                $this->start_time = strtotime (date ('Y-m-d', strtotime ('-1 days')));
                $this->stop_time = strtotime (date ('Y-m-d'));
                $this->child_name = 'hour';
                $this->indexBy = 'hour';
                break;
            case 3:
                $this->table_name = 'partitions_status_date';
                $this->start_time = strtotime (date ('Y-m-1'));
                $this->stop_time = strtotime (date ('Y-m-d'));
                $this->child_name = 'day';
                $this->indexBy = 'date';
                break;
            case 4:
                $this->table_name = 'partitions_status_date';
                $this->start_time = strtotime (date ('Y-m-1', strtotime ('-1 months')));
                $this->stop_time = strtotime (date ('Y-m-1'));
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
    public function getX ()
    {
        if (strpos ($this->child_name, 'hour') !== false) { //如果是小时返回小时
            $arr = array_fill (0, 24, 1);
            return array_keys ($arr);
        }
        if (strpos ($this->child_name, 'day') !== false) {  //如果是天返回天
            $startTime = strtotime ($this->start_time);
            $stopTime = strtotime ($this->stop_time);
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
            $base[] = $object;
        }
        foreach ($data as $k => $v) {
            $text = Yii::t('app', 'proc').':'.$this->process_default;
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
        switch($type) {
            case 'system':
                $header = [
                    'device_ip' => Yii::t('app', 'my ip'),
                    'date' => Yii::t('app', 'user time'),
                    'start_response_time' => Yii::t('app', 'start_response_time'),
                    'update_response_time' => Yii::t('app', 'update_response_time'),
                    'stop_response_time' => Yii::t('app', 'stop_response_time'),
                    'auth_response_time' => Yii::t('app', 'auth_response_time'),
                    'coa_response_time' => Yii::t('app', 'coa_response_time'),
                    'dm_response_time' => Yii::t('app', 'dm_response_time')
                ];
                break;
            case 'multi':
                $header = [
                    'date' => Yii::t('app', 'user time'),
                    'start_response_time' => Yii::t('app', 'start_response_time'),
                    'update_response_time' => Yii::t('app', 'update_response_time'),
                    'stop_response_time' => Yii::t('app', 'stop_response_time'),
                    'auth_response_time' => Yii::t('app', 'auth_response_time'),
                    'coa_response_time' => Yii::t('app', 'coa_response_time'),
                    'dm_response_time' => Yii::t('app', 'dm_response_time')
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
            default :
                $this->start_time = date('Y-m-01', strtotime('-30 days'));
                $this->stop_time = date('Y-m-d', strtotime('-1 days'));
                break;
        }

        return true;
    }

}