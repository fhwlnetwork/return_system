<?php

namespace center\modules\report\models;

use center\modules\auth\models\SrunJiegou;
use common\extend\Excel;
use Yii;
use center\extend\Tool;
use yii\db\Query;
use yii\db\Migration;

/**
 * This is the model class for table "srun_detail_day".
 *
 * @property integer $detail_day_id
 * @property string $user_name
 * @property integer $record_day
 * @property integer $bytes_in
 * @property integer $bytes_out
 * @property integer $bytes_in6
 * @property integer $bytes_out6
 * @property integer $products_id
 * @property integer $billing_id
 * @property integer $control_id
 * @property double $user_balance
 * @property integer $total_bytes
 * @property integer $time_long
 * @property double $user_login_count
 * @property integer $user_group_id
 */
class SrunDetailDay extends \yii\db\ActiveRecord
{
    public $btn_chooses;
    public $start_At; //开始时间
    public $stop_At; //截止时间
    public $step; //步长
    public $unit; //时间修饰词
    public $bytes_mb = 1048576; //流量进位 MB
    public $bytes_gb = 1073741824; //流量进位 GB
    public $base;
    public $sql_type;
    public $number;
    public $flag;
    public $section;

    public function setDefault()
    {
        $this->start_At = date('Y-m-d', strtotime('-30 days'));
        $this->stop_At = date('Y-m-d', strtotime('-1 days'));
        if ($this->sql_type == 'time_long') {
            $this->step = '5H';
        } else if ($this->sql_type == 'bytes') {
            $this->step = '500M';
        } else {
            $this->step = '10';
        }

        $this->unit = 5;

        return true;
    }

    private static $table = 'srun_detail_day';

    /**
     * 重置表名
     * @param $params
     * @return null
     */
    public static function resetPartitionIndex($params)
    {
        $table_name = 'srun_detail_day';
        if ((!isset($params['start_date']) || empty($params['start_date'])) && (!isset($params['end_date']) || empty($params['end_date']))) {
            return Yii::t('app', 'log detail help3');
        }
        $tableName = \common\extend\Tool::getPartitionTable($params['start_date'], $params['end_date'], $table_name);
        if ($tableName) {
            if ($tableName !== $table_name) {
                $is_exists = Yii::$app->db->createCommand('show tables like "' . $tableName . '"')->queryAll();
                if (empty($is_exists)) {
                    //表不存在
                    return Yii::t('app', 'log detail help4', ['table_name' => $tableName]);
                }
            }
            self::$table = $tableName;
        } else {
            //上线时间和下线时间必须同时在一个自然月内才可搜索
            return Yii::t('app', 'log detail help3');;
        }
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return self::$table;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['start_At', 'stop_At'], 'required'],
            [['record_day', 'bytes_in', 'bytes_out', 'bytes_in6', 'bytes_out6', 'products_id', 'billing_id', 'control_id', 'total_bytes', 'time_long', 'btn_chooses'], 'integer'],
            [['user_balance', 'user_login_count'], 'number'],
            [['step', 'unit', 'user_group_id'], 'safe'],
            [['user_name', 'step', 'start_At', 'stop_At', 'unit'], 'string', 'max' => 64],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_name' => 'User Name',
            'record_day' => 'Record Day',
            'total_bytes' => 'Total Bytes',
            'time_long' => 'Time Long',
            'start_At' => Yii::t('app', 'start time'),
            'stop_At' => Yii::t('app', 'end time')
        ];
    }

    /**
     * @param string $type
     * @return array
     */
    public function getLegends($type = 'bytes')
    {
        $legends = [];
        switch ($type) {
            case "bytes":
                $legends = [Yii::t('app', 'sum_bytes'), Yii::t('app', 'max_bytes'), Yii::t('app', 'aver_bytes'), Yii::t('app', 'user_number')];
                break;
            case 'detail':
                $legends = [Yii::t('app', 'sum_bytes'), Yii::t('app', 'max_bytes'), Yii::t('app', 'aver_bytes'), Yii::t('app', 'user_number')];
                break;
            case 'signle':
                $legends = [Yii::t('app', 'sum_bytes')];
                break;
            case 'user':
                $legends = [Yii::t('app', 'total_bytes'), Yii::t('app', 'time_long')];
                break;
        }

        return $legends;
    }

    public static function getAttributesList()
    {
        $arr = array();
        for ($i = 1; $i <= 10; $i++) {
            $arr[$i] = $i;
        }
        return [
            'step' => [
                10 => '10' . Yii::t('app', 'report operate remind20'),
                20 => '20' . Yii::t('app', 'report operate remind20'),
                50 => '50' . Yii::t('app', 'report operate remind20'),
                100 => '100' . Yii::t('app', 'report operate remind20'),
                200 => '200' . Yii::t('app', 'report operate remind20'),
                500 => '500' . Yii::t('app', 'report operate remind20'),
                1000 => '1000' . Yii::t('app', 'report operate remind20'),
                2000 => '2000' . Yii::t('app', 'report operate remind20'),
                5000 => '5000' . Yii::t('app', 'report operate remind20'),
                10000 => '10000' . Yii::t('app', 'report operate remind20'),
            ],
            'unit' => $arr
        ];
    }

    /**
     * 获取excel表头
     * @param string $type
     * @return array
     */
    public function getTableHeader($type = 'bytes')
    {
        $rs = [];
        if ($type == 'bytes')
            $rs = [Yii::t('app', 'date'), Yii::t('app', 'user_group') . '|' . Yii::t('app', 'devicename_message'), Yii::t('app', 'flux')];
        else if ($type == 'time')
            $rs = [Yii::t('app', 'user_name'), Yii::t('app', 'user_group'), Yii::t('app', 'flux'), Yii::t('app', 'time_long'), Yii::t('app', 'login_count')];

        return $rs;
    }

    public static function getbytestype()
    {
        $arr = array();
        for ($i = 1; $i <= 10; $i++) {
            $arr[$i] = $i;
        }
        return [
            'step' => [
                '50M' => '50M',
                '100M' => '100M',
                '200M' => '200M',
                '500M' => '500M',
                '1G' => '1G',
                '2G' => '2G',
                '5G' => '5G',
                '10G' => '10G',
                '20G' => '20G',
                '50G' => '50G',
                '100G' => '100G',
            ],
            'unit' => $arr
        ];
    }

    public function getbytesdata($bytes = "")
    {
        $arraydata = [
            '50M' => 50 * $this->bytes_mb, //100M
            '100M' => 100 * $this->bytes_mb, //100M
            '200M' => 200 * $this->bytes_mb, //200M
            '500M' => 500 * $this->bytes_mb, //500M
            '1G' => 1 * $this->bytes_gb, //1G
            '2G' => 2 * $this->bytes_gb, //2G
            '5G' => 5 * $this->bytes_gb, //5G
            '10G' => 10 * $this->bytes_gb, //10G
            '20G' => 20 * $this->bytes_gb, //20G
            '50G' => 50 * $this->bytes_gb, //50G
            '100G' => 100 * $this->bytes_gb //100G
        ];
        if (!empty($bytes)) {
            return $arraydata[$bytes];
        }
        return $arraydata;
    }

    /**设定时长 时间段**/
    public static function getTimLong()
    {
        $arr = array();
        for ($i = 1; $i <= 10; $i++) {
            $arr[$i] = $i;
        }
        return [
            'step' => [
                '1H' => '1H',
                '2H' => '2H',
                '5H' => '5H',
                '10H' => '10H',
                '20H' => '20H',
                '50H' => '50H',
                '100H' => '100H',
                '200H' => '200H',
                '500H' => '500H',
                '1000H' => '1000H'
            ],
            'unit' => $arr
        ];
    }

    //验证输入时间的合理性以及时间不长的合理性
    public function validateField($params)
    {
        $start_At = strtotime($this->start_At); //开始时间
        $stop_At = strtotime($this->stop_At); //结束时间
        $base = date('Y-m-01');
        //var_dump($this->stop_At, $base, $this->start_At, $this->start_At == $base);exit;
        if ($this->start_At == $base && $start_At >= $stop_At) {
            $this->addError('stop_At', Yii::t('app', '本月1号刚开始，暂无统计数据'));
            return false;
        }

        if ($stop_At < $start_At) {
            $this->addError($this->stop_At, Yii::t('app', 'end time error'));
            return false;
        }
        if ($stop_At > time()) {
            $this->addError($this->stop_At, Yii::t('app', 'report operate remind19'));
            return false;
        }
        if (count(explode(',', $params['group_id'])) > 10) {
            $this->addError('user_group_id', Yii::t('app', 'report operate remind27'));
            return false;
        }
        if (!empty($this->user_name)) {
            if ($stop_At == $start_At && empty($this->btn_chooses)) {
                $this->addError($this->stop_At, Yii::t('app', 'end time error'));
                return false;
            }
        }
        return true;
    }


    /**
     * 折线图的流量统计(根据用户组)
     * @param $fieldArray
     * @return string
     */
    public function flowReportLine($son, $fieldArray)
    {
        $fieldArray = $son + $fieldArray;
        $start_At = strtotime($this->start_At);
        $stop_At = strtotime($this->stop_At);
        $groupNames = array_values($fieldArray);
        $groupIds = array_keys($fieldArray);
        $tool = new Tool();
        $this->flag = count($fieldArray) == 1;
        //var_dump($fieldArray);exit;
        $date = $tool->substrTime($start_At, $stop_At, 'days', 1);
        if ($date) {
            foreach ($date as $one) {
                $x_data[] = date('m-d', $one);
            }
        }


        $rs = self::find()
            ->select('max(total_bytes) as total_bytes')
            ->where(['>=', 'record_day', $start_At])
            ->andWhere(['<', 'srun_detail_day.record_day', $stop_At + 86400])
            ->andWhere(['user_group_id' => $groupIds])
            ->groupBy('record_day')
            ->orderBy('total_bytes desc')
            ->asArray()
            ->one();

        $this->setUnits($rs['total_bytes']);
        //var_dump($this->unit, $rs, $this->start_At, $this->stop_At);exit;
        $data = self::find()
            ->select(["sum(total_bytes)/$this->base as total_bytes", 'user_group_id', 'record_day'])
            ->where(['>=', 'record_day', $start_At])
            ->andWhere(['<=', 'srun_detail_day.record_day', $stop_At])
            ->andWhere(['user_group_id' => $groupIds])
            ->groupBy('user_group_id, record_day')
            ->asArray()
            ->all();


        if ($this->flag) {
            $this->sql_type = 'bytes';
            $rs = $this->getRsData($data, $date, $fieldArray);
            $legends = json_encode($groupNames, JSON_UNESCAPED_UNICODE);
            $xAxis = json_encode($rs['xAxis'], JSON_UNESCAPED_UNICODE);
            //var_dump($rs, $legends, $data, $xAxis);exit;
            $series = json_encode($this->getSeries($rs['rs']), JSON_UNESCAPED_UNICODE);

            return [
                'data' => [
                    'legends' => $legends,
                    'xAxis' => $xAxis,
                    'series' => $series
                ],
                'table' => array_reverse($rs['times'])
            ];
        } else {
            $rs = $this->getBaseData($date, $fieldArray, $data);

            return $rs;
        }


    }

    /**
     * 查询数据库
     * @param $groupIds
     * @param $flag
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getData($groupIds, $flag)
    {
        $start_At = strtotime($this->start_At);
        $stop_At = strtotime($this->stop_At);
        $query = self::find()->select(["sum(srun_detail_day.user_login_count) as login_count", 'sum(time_long) time_long', 'sum(total_bytes) bytes', 'user_group_id', 'user_name'])
            ->where(['between', 'record_day', $start_At, $stop_At]);

        if (!$flag) {
            $query->andWhere(['user_group_id' => $groupIds]);
        }

        $data = $query
            ->indexBy('user_name')
            ->groupBy('user_name')
            ->asArray()
            ->all();

        return $data;
    }

    /**
     * 上网时长统计
     * @param $fieldArray
     * @param $flag
     * @return array
     */
    public function getTimelong($fieldArray, $flag = false)
    {
        $grouid = $fieldArray;
        //var_dump($stop_At,  $this->unit);exit;
        $step = $this->step;
        $unit = $this->unit;
        $hour = Yii::t('app', 'hours');
        $data = $this->getData($grouid, $flag);
        $base = 3600;
        $times = [];
        if ($this->sql_type == 'bytes') {
            if (strpos($step, 'G')) {
                $hour = Yii::t('app', 'Gb');
                $base = $this->bytes_gb;
            } else {
                $hour = Yii::t('app', 'Mb');
                $base = $this->bytes_mb;
            }
            $step = $this->getbytesdata($step);
        } else if ($this->sql_type == 'login_count') {
            $hour = Yii::t('app', 'report operate remind20');
            $base = 1;
        }
        for ($i = 1; $i <= $unit; $i++) {
            if ($this->sql_type == 'time_long') {
                $sta = ($i - 1) * $step * 3600;
                $end = $i * $step * 3600;
            } else {
                $sta = ($i - 1) * $step;
                $end = $i * $step;
            }

            // $count = $this->getCount($start_At, $stop_At, $sta, $end, 'time_long');
            if ($i == $unit) {
                $desc = Yii::t('app', '>') . ($end / $base) . $hour;
            } else {
                $desc = ($sta / $base) . $hour . '-' . ($end / $base) . $hour;
            }
            $legends[] = $desc;
            //var_dump($times, $keys);exit;
            $rs[$desc] = 0;
            foreach ($data as $one) {
                $time = $one[$this->sql_type];
                if ($i == $unit) {
                    if ($time > $end) {
                        $rs[$desc]++;
                    }
                } else if ($time >= $sta && $time < $end) {
                    $rs[$desc]++;
                }
            }
        }
        foreach ($rs as $name => $val) {
            $series[] = ['name' => $name, 'value' => $val];
        }


        return [
            'data' => [
                'legends' => json_encode($legends),
                'series' => json_encode($series)
            ],
            'table' => $rs
        ];
    }

    /**
     * 设置时间
     * @return bool
     */
    public function getTime()
    {
        $season = ceil((date('n')) / 3);//当月是第几季度
        switch ($this->btn_chooses) {
            case 1: //本月
                $this->start_At = date('Y-m-01');
                $this->stop_At = date('Y-m-d', strtotime('-1 days'));
                break;
            case 2: //上月
                $this->start_At = date('Y-m-01', mktime(0, 0, 0, date("m") - 1, 1, date("Y")));
                $this->stop_At = date('Y-m-d', mktime(23, 59, 59, date("m"), 0, date("Y")));
                break;
            case 3: //本季度
                $this->start_At = date('Y-m-d', mktime(0, 0, 0, $season * 3 - 3 + 1, 1, date('Y')));
                $this->stop_At = date('Y-m-d', strtotime('-1 days'));
                break;
            case 4: //上季度
                $this->start_At = date('Y-m-01', mktime(0, 0, 0, $season * 3 - 6 + 1, 1, date('Y')));
                $this->stop_At = date('Y-m-d', mktime(23, 59, 59, $season * 3 - 3, date('t', mktime(0, 0, 0, $season * 3 - 3, 1, date("Y"))), date('Y')));
                break;
            default :
                $this->start_At = date('Y-m-01', strtotime('-30 days'));
                $this->stop_At = date('Y-m-d', strtotime('-1 days'));
                break;
        }

        return true;
    }

    /**
     * 打包数据
     * @param $data
     * @return array
     */
    public function getSeries($data)
    {
        $result = [];
        foreach ($data as $key => $value) {
            //循环构造结果集数据
            $object = new \stdClass();
            $object->type = 'line';
            $object->name = \Yii::t('app', $key);
            $object->data = $value;

            if (in_array($this->btn_chooses, ['4', '3'])) {
                $object->symbol = true;
                $object->sampling = 'average';
                $object->symbol = 'none';
                $object->areaStyle = ['normal' => []];
            }
            $result[] = $object;
        }

        return $result;
    }

    /**
     * 打包数据
     * @param $data
     * @return array
     */
    public function getPieSeries($data)
    {
        $result = [];

        foreach ($data as $key => $value) {
            //循环构造结果集数据
            $result[] = [
                'name' => $key,
                'value' => $value
            ];
        }

        //var_dump($result);exit;

        return $result;
    }


    /**
     * @param $bytes
     * @return bool
     */
    public function setUnits($bytes)
    {
        if ($bytes > 1024 * 1024 * 1024) {
            $this->unit = 'G';
            $this->base = 1024 * 1024 * 1024;
        } elseif ($bytes > 1024 * 1024) {
            $this->unit = 'M';
            $this->base = 1024 * 1024;
        } elseif ($bytes > 1024) {
            $this->unit = "K";
            $this->base = 1024;
        } else {
            $this->unit = "B";
            $this->base = 1;
        }

        return true;
    }

    /**
     * @param $data
     * @param $date
     * @param $field
     * @return array
     */
    public function getRsData($data, $date = [], $field = [])
    {
        $rs = [];
        if ($this->sql_type == 'bytes') {
            //获取流量
            $rs = $this->getBytesDetail($data, $date, $field);
        } else if ($this->sql_type == 'time_long') {
            $rs = $this->getTimeDetail($data, [], $field);
        } else if ($this->sql_type == 'login_count') {
            $rs = $this->getTimeDetail($data, [], $field);
        }

        return $rs;
    }


    /**
     * 整理数据
     * @param $data
     * @param $date
     * @param $field
     * @return array
     */
    public function getBytesDetail($data, $date, $field)
    {
        $rs = $xAxis = $yAxis = $times = [];
        foreach ($data as $val) {
            $yAxis[$val['record_day']][$val['user_group_id']] = $val['total_bytes'];
        }
        $groupId = array_keys($field)[0];

        foreach ($date as $k => $v) {
            $time = date('Y-m-d', $v);
            $xAxis[] = $time;
            $times[$time]['total'] = 0;
            $times[$time]['group_id'] = $groupId;
            foreach ($field as $id => $name) {
                if (!empty($yAxis)) {
                    if (isset($yAxis[$v][$id])) {
                        $bytes = $yAxis[$v][$id];
                        $rs[$name][] = $bytes;
                        $times[$time]['total'] = isset($times[$time]['total']) ? $times[$time]['total'] + $bytes : 0;
                    } else {
                        $rs[$name][] = 0;
                    }
                } else {
                    $rs[$name][] = 0;
                }
            }
        }

        return ['rs' => $rs, 'xAxis' => $xAxis, 'times' => $times];
    }

    /**
     * 流量详细统计
     *
     * @return string
     */
    public function getBytesDetails()
    {
        $start_At = strtotime($this->start_At);
        $stop_At = strtotime($this->stop_At);
        if (!empty($this->user_name)) {
            $rs = $this->getUserDetail($start_At, $stop_At);
        } else {
            $this->flag = $start_At == $stop_At ? 1 : 0;

            if ($this->flag) {
                $rs = $this->getSignleData($start_At);
            } else {
                $rs = $this->getMultiData($start_At, $stop_At);
            }
        }


        return $rs;
    }

    /**
     * 获取用户上网流量以及上网时长
     */
    public function getUserDetail($start_At, $stop_At)
    {
        //var_dump($this->unit, $rs, $this->start_At, $this->stop_At);exit;
        $data = self::find()
            ->select([
                "user_name",
                'sum(total_bytes) total_bytes',
                'sum(time_long) time_long',
                'record_day'
            ])
            ->where(['between', 'record_day', $start_At, $stop_At])
            ->andWhere('user_name = :user', [':user' => $this->user_name])
            ->groupBy('record_day')
            ->orderBy('record_day asc')
            ->indexBy('record_day')
            ->asArray()
            ->all();
        $this->unit = 'G';
        $xAxis = $legends = $series = [];
        $this->flag = 2;
        if (!empty($data)) {
            $dates = $this->getDates();
            $legends = $this->getLegends('user');
            foreach ($dates as $time) {
                $xAxis[] = date('Y-m-d', $time);
                $series['total_bytes'][] = isset($data[$time]) ? sprintf('%.2f', $data[$time]['total_bytes'] / 1024 / 1024 / 1024) : '0.00';
                $series['time_long'][] = isset($data[$time]) ? sprintf('%.2f', $data[$time]['time_long'] / 3600) : '0.00';
            }
            $legends = json_encode($legends, JSON_UNESCAPED_UNICODE);
            $xAxis = json_encode($xAxis, JSON_UNESCAPED_UNICODE);
            $series = json_encode($this->getLineSeries('line', $series), JSON_UNESCAPED_UNICODE);
        }
        krsort($data);
        return [
            'data' => [
                'legends' => $legends,
                'xAxis' => $xAxis,
                'series' => $series
            ],
            'table' => $data,
            'dates' => $xAxis
        ];
    }

    /**
     * 单天top40使用流量
     * @param $start_At
     * @return array
     */
    public function getSignleData($start_At)
    {
        //var_dump($this->unit, $rs, $this->start_At, $this->stop_At);exit;
        $data = self::find()
            ->select([
                "user_name",
                'total_bytes total',
                'record_day'
            ])
            ->where(['=', 'record_day', $start_At])
            ->orderBy('total_bytes desc')
            ->indexBy('user_name')
            ->limit(40)
            ->asArray()
            ->all();
        $rs = ['sum_bytes' => []];
        $xAxis = [];
        $i = 0;
        foreach ($data as $name => $v) {
            $xAxis[] = $name;
            if ($i == 0) {
                $max = $v['total'];
                $this->setUnits($max);
            }
            $rs['sum_bytes'][] = sprintf('%.2f', $v['total'] / $this->base);
            $i++;
        }

        $legends = json_encode($this->getLegends('signle'), JSON_UNESCAPED_UNICODE);
        $xAxis = json_encode($xAxis, JSON_UNESCAPED_UNICODE);
        $series = json_encode($this->getArrSeries($rs), JSON_UNESCAPED_UNICODE);

        return [
            'data' => [
                'legends' => $legends,
                'xAxis' => $xAxis,
                'series' => $series
            ],
            'table' => $data,
            'dates' => $xAxis
        ];
    }

    /**
     * 获取多天明细
     * @param $start_At
     * @param $stop_At
     * @return array
     */
    public function getMultiData($start_At, $stop_At)
    {
        //var_dump($this->unit, $rs, $this->start_At, $this->stop_At);exit;
        $data = self::find()
            ->select([
                "sum(total_bytes) total",
                'record_day',
                'count(distinct(user_name)) user_number',
                'max(total_bytes) max_bytes',
            ])
            ->where(['between', 'record_day', $start_At, $stop_At])
            ->orderBy('record_day asc')
            ->groupBy('record_day')
            ->indexBy('record_day')
            ->asArray()
            ->all();

        $dates = $this->getDates();

        $rs = ['sum_bytes' => [], 'max_bytes' => [], 'aver_bytes' => [], 'user_number' => [],];
        $xAxis = [];
        foreach ($dates as $v) {
            $xAxis[] = date('Y-m-d', $v);
            if (isset($data[$v])) {
                $rs['aver_bytes'][] = sprintf('%.2f', $data[$v]['total'] / $data[$v]['user_number'] / 1024 / 1024 / 1024);
                $rs['sum_bytes'][] = sprintf('%.2f', $data[$v]['total'] / 1024 / 1024 / 1024);
                $rs['user_number'][] = $data[$v]['user_number'];
                $rs['max_bytes'][] = sprintf('%.2f', $data[$v]['max_bytes'] / 1024 / 1024 / 1024);
            } else {
                $rs['aver_bytes'][] = 0;
                $rs['sum_bytes'][] = 0;
                $rs['user_number'][] = 0;
                $rs['max_bytes'][] = 0;
            }
        }
        $this->unit = 'G';

        $legends = json_encode($this->getLegends('detail'), JSON_UNESCAPED_UNICODE);
        $xAxis = json_encode($xAxis, JSON_UNESCAPED_UNICODE);
        $series = json_encode($this->getArrSeries($rs), JSON_UNESCAPED_UNICODE);
        krsort($data);
        return [
            'data' => [
                'legends' => $legends,
                'xAxis' => $xAxis,
                'series' => $series
            ],
            'table' => $data,
            'dates' => $xAxis
        ];
    }

    /**
     * 获取平均值
     * @param $data
     * @return array
     */
    public function getAverage($data)
    {
        $averages = [];
        foreach ($data['sum'] as $time => $v) {
            $averages[$time] = ($data['user'][$time] > 0) ? sprintf('%.2f', $v / $data['user'][$time]) : '0.00';

        }

        return $averages;

    }

    /**
     * 打包数据
     * @param $series
     * @return array
     */
    public function getArrSeries($series)
    {
        $result = [];
        foreach ($series as $key => $data) {
            //循环构造结果集数据
            $object = new \stdClass();
            if ($key == 'user_number') {
                $object->type = 'line';
            } else {
                $object->type = 'bar';
            }

            $object->name = \Yii::t('app', $key);
            $object->data = $data;
            $result[] = $object;
        }
        // var_dump($result);exit;

        return $result;
    }

    /**
     * @return array
     */
    public function getDates()
    {
        $sta = strtotime($this->start_At);
        $stop = strtotime($this->stop_At);
        $dates = [];
        while ($sta <= $stop) {
            $dates[] = $sta;
            $sta += 86400;
        }

        return $dates;
    }

    /**
     * @param $date
     * @param $groups
     * @param $data
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getBaseData($date, $groups, $data)
    {
        $legend = $base = $xAxis = $yAxis = $table = $series = [];
        $ids = array_keys($groups);
        $special = end($ids);
        //var_dump($special);exit;

        foreach ($data as $v) {
            $groupId = $v['user_group_id'];
            $time = $v['record_day'];
            $day = date('Y-m-d', $time);
            $yAxis[$time][$groupId] = $v['total_bytes'];
            $table[$day]['detail'][$groupId] = $v['total_bytes'];
            $table[$day]['group_id'] = $special;
            if ($groupId != $special) {
                $yAxis[$time][$special] += $v['total_bytes'];
            }

            $table[$day]['total'] += $v['total_bytes'];
        }
        //var_dump($yAxis);exit;

        foreach ($date as $time) {
            $xAxis[] = date('Y-m-d', $time);
            if (isset($yAxis[$time])) {
                foreach ($groups as $id => $name) {
                    $series[$name][] = isset($yAxis[$time][$id]) ? $yAxis[$time][$id] : 0;
                }

            } else {
                foreach ($groups as $id => $name) {
                    $series[$name][] = 0;
                }
            }
        }
        $base = array_values($groups);

        return [
            'data' => [
                'base' => json_encode($base, JSON_UNESCAPED_UNICODE),
                'xAxis' => json_encode($xAxis, JSON_UNESCAPED_UNICODE),
                'series' => $series
            ],
            'table' => array_reverse($table)
        ];
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
            if ($k == 'total_bytes') {
                $object->yAxisIndex = 0;
            } else {
                $object->yAxisIndex = 1;
            }
            $object->data = $v;
            $result[] = $object;
        }

        return $result;
    }

    /**
     * 导出用户明细数据
     * @return array
     */
    public function exportData()
    {
        $rs = [];
        try {
            if (empty($this->user_group_id)) {
                $flag = true;
            } else {
                $groupId = explode(',', $this->user_group_id);
                if (in_array(1, $groupId)) {
                    $flag = true;
                } else {
                    $groupIds = SrunJiegou::getNodeId($groupId);
                    $flag = false;
                }
            }
            $base = $this->getExportSectionData($groupIds, $flag); //获取某个段的基准数据
            if (empty($base)) {
                $rs = ['code' => 404, 'msg' => Yii::t('app', 'no record')];
            } else {
                $groups = SrunJiegou::getAllIdNameVal();
                $excelData = [];
                $excelData[0] = $this->getTableHeader('time');
                $remind =  Yii::t('app', 'report operate remind20');
                foreach ($base as $val) {
                    $excelData[] = [
                        $val['user_name'], $groups[$val['user_group_id']], Tool::bytes_format($val['bytes']), Tool::seconds_format($val['time_long']), $val['login_count'].$remind
                    ];
                }
                $title = Yii::t('app', 'Flow Detail');
                Excel::header_file($excelData, $title . '.xls', $title);
                exit;
            }
        } catch (\Exception $e) {
            $rs = ['code' => 500, 'msg' => '导出明细发生异常:' . $e->getMessage()];
        }

        return $rs;
    }

    /**
     * 获取基准数据
     * @param $groups
     * @param $flag
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getExportSectionData($groups, $flag)
    {
        $sectionArr = explode('-', $this->section);
        $unit = preg_replace('/[0-9]+/', '', $sectionArr[0]);
        $small = $end = 0;
        $last = count($sectionArr) == 1; //是否为最后一项
        $hour = Yii::t('app', 'hours');
        if ($last) {
            preg_match_all('/\d+/', $sectionArr[0], $arr);
            $small = $arr[0][0];
        }
       // var_dump($small, $arr);exit;
        if (preg_match('/mb/i', $unit)) {
            if ($last) {
                //last
                $small = $small * $this->bytes_mb;
            } else {
                $small = $sectionArr[0] * $this->bytes_mb;
                $end = $sectionArr[1] * $this->bytes_mb;
            }

        } else if (preg_match('/gb/i', $unit)) {
            if ($last) {
                //last
                $small = $small * $this->bytes_gb;
            } else {
                $small = $sectionArr[0] * $this->bytes_gb;
                $end = $sectionArr[1] * $this->bytes_gb;
            }
        } else if (preg_match("/$hour/", $unit)) {
            if ($last) {
                $small = $small*3600;
            } else {
                $small = $sectionArr[0] * 3600;
                $end = $sectionArr[1] * 3600;
            }
        } else {
            if (!$last) {
                $small = $sectionArr[0];
                $end = $sectionArr[1];
            }
        }
        $start_At = strtotime($this->start_At);
        $stop_At = strtotime($this->stop_At);
        $query = self::find()->select(["sum(srun_detail_day.user_login_count) as login_count", 'sum(time_long) time_long', 'sum(total_bytes) bytes', 'user_group_id', 'user_name'])
            ->where(['between', 'record_day', $start_At, $stop_At]);

        if (!$flag) {
            $query->andWhere(['user_group_id' => $groups]);
        }
        $query->indexBy('user_name')->groupBy('user_name');

        if ($last) {
            $query->having("$this->sql_type >= :small", [
                ':small' => $small,
            ]);
        } else {
            $query->having("$this->sql_type >= :small and $this->sql_type < :end", [
                ':small' => $small,
                ':end' => $end
            ]);
        }
        $data = $query->asArray()->all();

        return $data;
    }

}
