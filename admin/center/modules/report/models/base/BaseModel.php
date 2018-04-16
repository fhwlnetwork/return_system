<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2017/5/9
 * Time: 15:07
 */

namespace center\modules\report\models\base;

use yii;
use center\extend\Tool;
use yii\db\ActiveRecord;

/**
 * 基础模型
 * Class BaseModel
 * @package center\modules\report\models\base
 */
class BaseModel extends ActiveRecord
{
    public $sql_type;
    public $start_At; //开始时间
    public $stop_At; //截止时间
    public $type; //查询类型
    public $step; //步长
    public $unit; //时间修饰词
    public $base;
    public $flag;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'online_report_billing';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['start_At', 'stop_At'], 'required'],
            [['start_At', 'stop_At', 'unit', 'type'], 'string'],
            ['step', 'integer', 'min' => 1],
            //[['time_point', 'billing_id', 'count', 'bytes_in', 'bytes_out', 'time_long'], 'required'],
            [['time_point', 'billing_id', 'count', 'bytes_in', 'bytes_out', 'time_long'], 'integer']
        ];
    }

    /**
     * 获取标题
     * @param string $action
     * @return string
     */
    public function getNameByAction($action = 'product')
    {
        return Yii::t('app', 'report/online/'.$action);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'report_id' => 'Report ID',
            'time_point' => 'Time Point',
            'billing_id' => 'Billing ID',
            'count' => 'Count',
            'bytes_in' => 'Bytes In',
            'bytes_out' => 'Bytes Out',
            'time_long' => 'Time Long',
            'start_At' => Yii::t('app', 'start time'),
            'stop_At' => Yii::t('app', 'end time')
        ];
    }

    public static function getAttributesList()
    {
        return [
            'type' => [
                'count' => Yii::t('app', 'number of people'),
                'bytes_in' => Yii::t('app', 'bytes in'),
                'bytes_out' => Yii::t('app', 'bytes out'),
                'time_long' => Yii::t('app', 'user time long')
            ],
            'step' => [
                '15' => '15',
                '30' => '30',
            ]
        ];
    }

    //验证输入时间的合理性以及时间不长的合理性
    public function validateField($type = '')
    {
        $start_At = strtotime($this->start_At); //开始时间
        $stop_At = strtotime($this->stop_At); //结束时间

        if ($stop_At < $start_At) {
            $this->addError($this->stop_At, Yii::t('app', 'end time error'));

            return false;
        }

        if (!empty($type)) {
            if ($stop_At - $start_At >= 86400) {
                $this->addError($this->stop_At, Yii::t('app', 'Time had better not more than one day'));

                return false;
            }
        }
        return true;
    }

    /**
     * 在线产品使用人数分布
     * @param $searchField
     * @return array
     */
    public function peoples($searchField)
    {
        $this->sql_type = (empty($this->type)) ? 'count' : $this->type;
        $data = $this->findData($searchField);
        $this->flag = count($searchField) == 1;

        $xAxis = $this->getDate(); //X轴数据
        $action = Yii::$app->controller->action->id;
        $name = $this->getNameByAction($action);

        $rs = $this->getRsData($data['data'], $searchField, $xAxis);
        if (!$this->flag) {
            $legends = json_encode($rs['legends'], JSON_UNESCAPED_UNICODE);
            $times = json_encode($xAxis, JSON_UNESCAPED_UNICODE);
            $xAxis = json_encode($rs['xAxis'], JSON_UNESCAPED_UNICODE);
            $base = json_encode($rs['base'], JSON_UNESCAPED_UNICODE);
            $series = $this->getArrSeries($rs['rs'], $name);
          //  var_dump($series);exit;
        } else {
            $xAxis = json_encode($rs['xAxis'], JSON_UNESCAPED_UNICODE);
            $type = self::getAttributesList()['type'][$this->sql_type];
            $legends = json_encode([$type], JSON_UNESCAPED_UNICODE);
            $series = json_encode($rs['rs'], JSON_UNESCAPED_UNICODE);
        }
        //var_dump($rs, $legends, $data, $xAxis);exit;
        return [
            'data' => [
                'legends' => $legends,
                'xAxis' => $xAxis,
                'times' => $times,
                'series' => $series,
                'base' => $base
            ],
            'table' => $data,
            'products' => $searchField,
        ];
    }


    //返回查询数据.
    public function findData($productArray)
    {
        //默认就查今天的
        if ($this->stop_At == date('Y-m-d')) {
            $stop = time();
        } else {
            $stop = strtotime($this->stop_At) + 86399;
        }
        $start = strtotime($this->start_At);
        $ids = array_keys($productArray);
        $data = self::find()
            ->select([$this->sql_type, 'time_point', $this->base])
            ->where(['between', 'time_point', $start, $stop])
            ->andWhere([$this->base => $ids])
            ->asArray()
            ->all();

        return [
            'data' => $data,
            'time' => [
                'sta' => $start,
                'stop' => $stop
            ]
        ];
    }

    /**
     * @param array $times
     * @return mixed
     */
    public function formatTime($times = [])
    {
        $start_At = $times['sta'];
        $stop_At = $times['stop'];
        $this->step = (!empty($this->step)) ? $this->step : 10;

        $unit = 'minutes';
        //对输入的时间进行切分 比如 10：00 10：05 10：10 10：15 这样子.
        $tool = new Tool();
        $xAxis = $tool->substrTime($start_At, $stop_At, $unit, $this->step);

        return $xAxis;
    }

    /**
     * @param array $times
     * @return mixed
     */
    public function formatTimes($times = [])
    {
        $start_At = strtotime($times[0]);
        $stop_At = strtotime(end($times)) + 86399;
        $this->step = (!empty($this->step)) ? $this->step : 10;

        $unit = 'minutes';
        //对输入的时间进行切分 比如 10：00 10：05 10：10 10：15 这样子.
        $tool = new Tool();
        $xAxis = $tool->substrTime($start_At, $stop_At, $unit, $this->step);

        return $xAxis;
    }


    /**
     * 整理数据
     * @param $data
     * @param array $names
     * @param array $xAxis
     * @return array
     */
    public function getRsData($data, $names = [], $xAxis = [])
    {
        if ($this->flag) {
            $rs = $this->getSignleData($data, $names, $xAxis);
        } else {
            $rs = $this->getProData($data, $names, $xAxis);
        }

        return $rs;
    }

    /**
     * 单天数据
     * @param $data
     * @param $names
     * @param $xAxis
     * @return array
     */
    public function getSignleData($data, $names, $xAxis)
    {
        $rs = $table = $yAxis = $legends = $hours = $times =  [];
        foreach ($data as $v) {
            $products = $v[$this->base];
            $yAxis[] = $v[$this->sql_type];
            $times[] = date('Y-m-d H:i', $v['time_point']);
        }
        $i = 0;
        //var_dump($table);exit;

        return ['rs' => $yAxis, 'legends' => $legends, 'table' => $table, 'xAxis' => $times];
    }

    /**
     * 多天
     * @param $data
     * @param $names
     * @param $xAxis
     * @return array
     */
    public function getProData($data, $names, $xAxis)
    {
        $rs = $table = $yAxis = $legends = $hours = $base = [];
        $flag = count($xAxis) == 1;
        foreach ($data as $v) {
            $time = $flag ? date('H:i', $v['time_point']) : date('Y-m-d H:i', $v['time_point']);
            $products = $v[$this->base];
            $name = $products . ":" . $names[$products];
            if (!in_array($time, $base)) {
                $base[] = $time;
            }
            if (!in_array($name, $legends)) {
                $legends[] = $name;
            }
            $rs[$time][] = ['name' => $name, 'value' => $v[$this->sql_type]];
        }


        return ['rs' => $rs, 'legends' => $legends, 'table' => $table, 'base' => $base];
    }

    /**
     * 多天
     * @param $data
     * @param $names
     * @param $xAxis
     * @return array
     */
    public function getMultiData($data, $names, $xAxis)
    {
        $rs = $table = $yAxis = $legends = $hours = [];
        foreach ($data as $v) {
            $day = date('Y-m-d', $v['time_point']);
            $time = date('Y-m-d H:i', $v['time_point']);
            $products = $v[$this->base];
            $name = $products . ":" . $names[$products];
            $table[$day]['data'] = isset($table[$day]['data']) ? max($table[$day]['data'], $v[$this->sql_type]) : $v[$this->sql_type];
            $table[$day]['detail'][] = $v;
            $rs[$time] = $v[$this->sql_type];
        }


        return ['rs' => $rs, 'legends' => $legends, 'table' => $table, 'xAxis' => $hours];
    }

    /**
     * 获取位置
     * @param $time
     * @param $arr
     * @return int|string
     */
    public function getTime($time, $arr)
    {
        foreach ($arr as $k => $v) {
            if ($time >= $v && $time < $v + $this->step * 60) {
                return $v;
            }
        }

        return $v;
    }


    /**
     * 打包数据
     * @param $data
     * @return array
     */
    public function getPieSeries($data, $names)
    {
        $result = [];
        $i = 0;
        foreach ($data as $key => $value) {
            //循环构造结果集数据
            foreach ($value['data'] as $k => $v) {
                $result[$key][] = [
                    'name' => $value['pro'][$k] . ":" . $names[$value['pro'][$k]],
                    'value' => $v
                ];;
            }
            $i++;
        }

        return $result;
    }

    /**
     * 获取x轴
     * @return array
     */
    public function getDate()
    {
        $dates = [];
        $sta = strtotime($this->start_At);
        while ($sta <= strtotime($this->stop_At)) {
            $dates[] = date('Y-m-d', $sta);
            $sta += 86400;
        }

        return $dates;
    }

    /**
     * 打包数据
     * @param $key
     * @param $data
     * @return array
     */
    public function getSeries($key, $data)
    {
        $result = [];
        $object = new \stdClass();
        $object->type = 'line';
        $object->name = $key;
        $object->data = $data;
        $object->symbol = true;
        $object->sampling = 'average';
        $object->symbol = 'none';
        $object->areaStyle = ['normal' => []];
        $result = $object;

        return $result;
    }

    /**
     * 组装多个option
     * @param $data
     * @param $title
     * @return array
     */
    public function getArrSeries($data, $title)
    {

        $option = $base = [];
        $object = new \stdClass();
        $object->type = 'pie';
        $object->name = $title;
        $base[] = $object;
        foreach ($data as $k => $v) {
            $subtext = Yii::t('app', 'user time').':'.$k;
            $object = new \stdClass();
            $object2 = new \StdClass();
            $object2->text = $title;
            $object2->subtext = $subtext;
            $object->title = $object2;
            $series = [];

            foreach  ($v as $name => $val) {
                $object3 = new \StdClass();
                $object3->name = $val['name'];
                $object3->value = $val['value'];
                $series[] = $object3;
            }
            $object4 = new \StdClass();
            $object4->data = $series;
            $object->series[] = $object4;
            $option[] = $object;
        }

        return [
            'base' => $base,
            'option' => $option
        ];
    }

}