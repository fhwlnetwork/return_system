<?php

namespace center\modules\report\models;

use center\modules\user\models\Base;
use Yii;

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
class DetailDay extends \yii\db\ActiveRecord
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

    /**
     * 设置默认
     * @return bool
     */
    public function setDefault()
    {
        $this->start_At = date('Y-m-1');
        $this->stop_At = date('Y-m-d', strtotime('-1 days'));

        return true;
    }

    //验证输入时间的合理性以及时间不长的合理性
    public function validateField()
    {
        $start_At = strtotime($this->start_At); //开始时间
        if ($start_At > time()) {
            $this->addError('start_At', Yii::t('app', 'end time error'));
            return false;
        }
        if (!empty($this->user_name)) {
            if (!Base::findOne(['user_name' => $this->user_name])) {
                $this->addError('user_name', Yii::t('app', 'E2531'));
                return false;
            }
        }
        return true;
    }

    public function init()
    {
        parent::init(); //Todo:: change some settings
        $this->sql_type = 'total_bytes';
    }

    public static function getAttributesList()
    {
        return [
            'type' => [
                'login_count' => Yii::t('app', 'number of people'),
                'bytes_in' => Yii::t('app', 'bytes in'),
                'bytes_out' => Yii::t('app', 'bytes out'),
                'time_long' => Yii::t('app', 'user time long'),
                'total_bytes' => Yii::t('app', 'total bytes'),
            ],
            'step' => [
                '15' => '15',
                '30' => '30',
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'srun_detail_day';
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
            [['step', 'unit', 'user_group_id', 'sql_type'], 'safe'],
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
        }

        return $legends;
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
            $rs = ['日期', '用户组|消息', '流量'];
        else if ($type == 'time')
            $rs = ['用户名', '用户组', '流量', '上网时长', '登陆次数'];

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
     * @param $params
     * @param string $type
     * @return mixed
     */
    public function getControlData($params, $type = 'control_id')
    {

        if ($this->start_At == date('Y-m')) {
            $this->stop_At = date('Y-m-d', strtotime('-1 days'));
        } else {
            $this->stop_At = date('Y-m-d', strtotime("$this->start_At +1 month -1 day"));
        }
        $start_At = strtotime($this->start_At);
        $stop_At = strtotime($this->stop_At);
        $baseData = $this->getBaseData(array_keys($params), $start_At, $stop_At, $type);
        $flag = count($params) == 1;
        $dates = $this->getDates();
        if ($flag) {
            $rs = $this->getSignleControlData($params, $dates, $baseData);
        } else {
            $rs = $this->getMultiControlData($params, $dates, $baseData, $type);
        }

        return $rs;
    }

    /**
     * 多个策略条件
     * @param $params
     * @param $dates
     * @param $data
     * @param $type
     * @return array
     */
    public function getMultiControlData($params, $dates, $data, $type)
    {
        $xAxis = $series = $yAxis = [];

        $this->flag = 2;
        foreach ($data as $v) {
            $yAxis[$v['record_day']][$v[$type]] = $v['total'];
        }

        foreach ($dates as $v) {
            $xAxis[] = date('Y-m-d', $v);
            if (isset($yAxis[$v])) {
                foreach ($params as $id => $name) {
                    $key = $id . ':' . $name;
                    $series[$key][] = isset($yAxis[$v][$id]) ? $yAxis[$v][$id] : 0;
                }
            } else {
                foreach ($params as $id => $name) {
                    $key = $id . ':' . $name;
                    $series[$key][] = 0;
                }
            }
        }
        $legend = array_keys($series);


        return [
            'base' => json_encode($legend, JSON_UNESCAPED_UNICODE),
            'xAxis' => json_encode($xAxis, JSON_UNESCAPED_UNICODE),
            'series' => $series
        ];
    }

    /**
     * 获取单个策略数据
     * @param $params
     * @param $dates
     * @param $data
     * @return array
     */
    public function getSignleControlData($params, $dates, $data)
    {
        //单个
        $xAxis = $series = [];

        $this->flag = 1;
        foreach ($dates as $v) {
            $xAxis[] = date('Y-m-d', $v);
            $series[] = isset($data[$v]) ? $data[$v]['total'] : 0;
        }


        return [
            'xAxis' => json_encode($xAxis, JSON_UNESCAPED_UNICODE),
            'series' => json_encode($series, JSON_UNESCAPED_UNICODE)
        ];
    }


    /**
     * 获取数据库基础数据
     * @param $ids
     * @param string $type
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getBaseData($ids, $start_At, $stop_At, $type = 'control_id')
    {
        // var_dump($this->sql_type);exit;
        if ($this->sql_type == 'login_count') {
            $str = "count(distinct(user_name)) total";
        } else {
            $str = "sum($this->sql_type) total";
        }
        $query = self::find()->select([
            $str,
            'record_day',
            'user_name',
            $type
        ])
            ->where(['between', 'record_day', $start_At, $stop_At])
            ->andWhere([$type => $ids]);
        if (!empty($this->user_name)) {
            $group = "record_day, $type";
            $query->andWhere(['user_name' => $this->user_name]);
        } else {
            $group = "record_day, $type";
        }
        if (count($ids) == 1) {
            //一个控制策略
            $query->indexBy('record_day');
        }


        $data = $query
            ->orderBy('total_bytes desc')
            ->groupBy($group)
            ->asArray()
            ->all();

        return $data;
    }

    /**
     * 获取列表数据
     * @param $sta
     * @param $end
     * @param $type
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getTableData($sta, $end, $type)
    {
        $table = self::find()->select([
            'count(distinct(user_name)) user_numer',
            'max(total_bytes) max_bytes',
            'user_name',
            'sum(time_long) time_long',
            'sum(bytes_in) bytes_in',
            'sum(bytes_out) bytes_out',
            'sum(bytes_total) bytes_total',
            $type
        ])->where(['between', 'record_day', $sta, $end])
            ->groupBy($type)
            ->asArray()
            ->all();

        return $table;
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
                $this->start_At = date('Y-m-1');
                $this->stop_At = date('Y-m-d', strtotime('-1 days'));
                break;
            case 2: //上月
                $this->start_At = date('Y-m-1', mktime(0, 0, 0, date("m") - 1, 1, date("Y")));
                $this->stop_At = date('Y-m-d', mktime(23, 59, 59, date("m"), 0, date("Y")));
                break;
            case 3: //本季度
                $this->start_At = date('Y-m-d', mktime(0, 0, 0, $season * 3 - 3 + 1, 1, date('Y')));
                $this->stop_At = date('Y-m-d', strtotime('-1 days'));
                break;
            case 4: //上季度
                $this->start_At = date('Y-m-1', mktime(0, 0, 0, $season * 3 - 6 + 1, 1, date('Y')));
                $this->stop_At = date('Y-m-d', mktime(23, 59, 59, $season * 3 - 3, date('t', mktime(0, 0, 0, $season * 3 - 3, 1, date("Y"))), date('Y')));
                break;
            default :
                $this->start_At = date('Y-m-1');
                $this->stop_At = date('Y-m-d', strtotime('-1 days'));
                break;
        }

        return true;
    }
}
