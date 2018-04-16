<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2017/5/10
 * Time: 11:20
 */

namespace center\modules\report\models\base;

use yii;
use center\extend\Tool;
use yii\db\ActiveRecord;

/**
 * 终端基础类
 * Class TerminalBase
 * @package center\modules\report\models\base
 */
class TerminalBase extends ActiveRecord
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
    //验证输入时间的合理性以及时间不长的合理性
    public function validateField()
    {
        $start_At = strtotime($this->start_At); //开始时间
        $stop_At = strtotime($this->stop_At); //结束时间

        if ($stop_At === $start_At || $stop_At < $start_At) {
            $this->addError($this->stop_At, Yii::t('app', 'end time error'));
        }

        if ($this->step <= 0) {
            $this->addError($this->step, Yii::t('app', 'step error'));
        }

        return true;
    }


    /**
     * 获取基准
     * @return array
     */
    public function getBase()
    {
        $names = self::find()->select(["distinct($this->base) $this->base"])->indexBy($this->base)->asArray()->all();

        return $names ? array_keys($names) : [];
    }

    /**
     * @return array
     */
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


    public function findData($names)
    {
        $start = strtotime($this->start_At);
        if ($this->stop_At == date('Y-m-d')) {
            $stop = time() - 10*60;
        } else {
            $stop = strtotime($this->stop_At) + 86399;
        }

        $data = self::find()
            ->select([$this->base,  'time_point', $this->sql_type])
            ->where(['between', 'time_point', $start, $stop])
            ->andWhere([$this->base => $names])
            ->asArray()
            ->all();

        return ['data' => $data, 'time' => ['sta' => $start, 'stop' => $stop]];
    }
    /**
     * 获取数据
     * @return array
     */
    public function getData($names)
    {
        $this->sql_type = (!empty($this->sql_type)) ? $this->sql_type : 'count';
        $rs = [];
        if (!empty($names)) {
            $data = $this->findData($names);
            $this->flag = count($names) == 1;
            $xAxis = $this->getDate(); //X轴数据
            $rs = $this->getRsData($data['data'], $names,  $xAxis);
            $action = Yii::$app->controller->action->id;
            $name = $this->getNameByAction($action);
            if (!$this->flag) {
                $legends = json_encode($rs['legends'], JSON_UNESCAPED_UNICODE);
                $times = json_encode($xAxis, JSON_UNESCAPED_UNICODE);
                $xAxis = json_encode($rs['xAxis'], JSON_UNESCAPED_UNICODE);
                $base = json_encode($rs['base'], JSON_UNESCAPED_UNICODE);
                $series = $this->getArrSeries($rs['rs'], $name);
            } else {
                $xAxis = json_encode($rs['xAxis'], JSON_UNESCAPED_UNICODE);
                $type = self::getAttributesList()['type'][$this->sql_type];
                $legends = json_encode([$type], JSON_UNESCAPED_UNICODE);
                $series = json_encode($rs['rs']);
            }
            $products = ['' => Yii::t('app', 'Please Select')]+array_combine($names, $names);

            return [
                'data' => [
                    'legends' => $legends,
                    'xAxis' => $xAxis,
                    'times' => $times,
                    'series' => $series,
                    'base' => $base
                ],
                'table' => $data['data'],
                'products' => $products
            ];
        }

        return $rs;
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
            $rs = $this->getMultiData($data, $names, $xAxis);
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
        $rs = $table = $yAxis = $legends = $hours = [];
        $base = $this->base;
        foreach ($data as $v) {
            $day = date('Y-m-d H:i', $v['time_point']);
            $hours[] = $day;
            $rs[] = $v[$this->sql_type];
        }
        $i = 0;
        //var_dump($table);exit;

        return ['rs' => $rs, 'legends' => $legends, 'table' => $table, 'xAxis' => $hours];
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
        $rs = $table = $yAxis = $legends = $hours =  $base = [];
        $flag = count($xAxis) == 1;
        foreach ($data as $v) {
            $time = $flag ? date('H:i', $v['time_point']) : date('Y-m-d H:i', $v['time_point']);
            $products = $v[$this->base];
            $name = $names[$products];
            if (!in_array($time, $base)) {
                $base[] = $time;
            }
            if (!in_array($name, $legends)) {
                $legends[] = $name;
            }
            $rs[$time][] = ['name' => $name, 'value' => $v[$this->sql_type]];
        }
        //var_dump($table);exit;
        return ['rs' => $rs, 'legends' => $legends, 'table' => $table, 'base' => $base];
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

    public function setTime($point)
    {
        switch ($point) {
            case 'month': //本月
                $this->start_At = date('Y-m-1');
                $this->stop_At = date('Y-m-d');
                break;
            case 'last': //上轴
                $this->start_At = date('Y-m-d', mktime(0,0,0,date('m'),date('d')-date('w')+1-7,date('Y')));
                $this->stop_At = date('Y-m-d', mktime(23,59,59,date('m'),date('d')-date('w')+7-7,date('Y')));
                break;
            case 'week': //本周
                $this->start_At = date('Y-m-d', mktime(0,0,0,date('m'),date('d')-date('w')+1,date('Y')));
                $this->stop_At = date('Y-m-d');
                break;
            case 'day': //本日
            case 'Today': //本日
                $this->start_At = date('Y-m-d');
                $this->stop_At = date('Y-m-d');
                break;
        }

        return true;
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
        $object->data = array_values($data);
        $object->symbol = true;
        $object->sampling = 'average';
        $object->symbol = 'none';
        $object->areaStyle = ['normal' => []];
        $object2 =  new \stdClass();

        $result[] = $object;

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