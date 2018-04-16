<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2017/5/18
 * Time: 10:35
 */

namespace center\modules\report\models\financial;


use common\models\Redis;
use yii;
use common\models\User;
use yii\db\ActiveRecord;
use center\modules\financial\models\PayType;
use center\modules\report\models\Financial;
use center\modules\auth\models\SrunJiegou;
use center\modules\financial\models\PayList;

/**
 * 财务报表基础模型
 * Class FinancialReport
 * @package center\modules\report\models
 */
class FinancialBase extends ActiveRecord
{
    public $start_time;
    public $stop_time;
    public $timePoint;
    public $operator;
    public $baseModel;
    public $proIds; //产品id
    public $flag;
    public $can_mgr;
    public $can_group;
    public $payOperators = [];//收费员
    public $data_source;//数据来源
    public $realModel;
    public $sql_type;
    public $user_name;
    public $statistical_cycle;


    public function init()
    {
        $this->baseModel = new Financial();
        $this->flag = User::isSuper();
        if (!$this->flag) {
            $this->can_mgr = $this->baseModel->getMgrOpe();
            $this->can_group = SrunJiegou::canMgrGroupNameList();
        } else {
            $this->can_group = SrunJiegou::canMgrGroupNameList();
            $this->can_mgr = (new PayList())->getFinancialMgr();
        }
        parent::init(); //TODO:: change some settings
    }

    /**
     * 获取单个产品名称
     * @param $id
     * @return string
     */
    public function getProName($id)
    {
        $name = '';
        if (Redis::executeCommand('exists', 'hash:products:' . $id)) {
            $name = Redis::executeCommand('hget', 'hash:products:' . $id, ['products_name']);
        }

        return $id . ':' . $name;
    }

    /**
     * 获取真实操作模型
     * @return bool
     */
    public function getRealModel()
    {
        $action = Yii::$app->controller->action->id;
        switch ($action) {
            case 'paytype':
                $this->realModel = new FinancialReport();
                break;
            case 'checkout':
                $this->realModel = new CheckoutReport();
                break;
            default :
                $this->realModel = new FinancialReport();
                break;
        }

        return true;
    }

    public static function getAttributesList()
    {
        return [
            'data_source' => [
                'all' => Yii::t('app', 'all'),
                'users' => Yii::t('app', 'user recharge'),
                'system' => Yii::t('app', 'system pay'),
            ],
            'statistical_cycle' => [
                'day' => Yii::t('app', 'report by day'),
                'week' => Yii::t('app', 'report by week'),
                'year' => Yii::t('app', 'report by year'),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%pay_list}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['start_time', 'stop_time', 'timePoint', 'operator', 'data_source', 'user_name'], 'safe'],
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
            'user_name' => 'User Name',
            'err_msg' => 'Err Msg',
            'number' => 'Number',
        ];
    }


    public function getLegend()
    {
        $names = $this->baseModel->getProNames();
        //var_dump($legend);exit;
        ksort($names);
        $this->realModel->proIds = $this->proIds = array_keys($names);

        return $names;
    }

    /**
     * 检查
     */
    public function validateField()
    {
        $start_time = strtotime($this->start_time); //开始时间
        $stop_time = strtotime($this->stop_time); //结束时间
        //结束时间不能大于当前的月份
        if (date('m', strtotime($this->stop_time)) > date('m')) {
            $this->addError('stop_name', Yii::t('app', 'end time big error'));

            return false;
        }
        //结束时间不能大于当前的时间
        if (strtotime($this->stop_time) > time()) {
            $this->addError('stop_name', Yii::t('app', 'end time error'));

            return false;
        }
        if ($stop_time < $start_time) {
            $this->addError('stop_name', Yii::t('app', 'end time error'));

            return false;
        }

        if (!empty($this->operator)) {
            if (!in_array($this->operator, $this->can_mgr)) {
                $this->addError('operator', '该管理员不存在或者不在可管理的管理员之内');

                return false;
            }
        }

        return true;
    }


    /**
     * 设置时间
     * @return bool
     */
    public function setTime($point = 4)
    {
        switch ($point) {
            case 1:
                $this->start_time = date('Y-m-d');
                $this->stop_time = date('Y-m-d');
                break;
            case 2:
                $this->start_time = date('Y-m-d', strtotime('-1 days'));
                $this->stop_time = date('Y-m-d', strtotime('-1 days'));
                break;
            case 3:
                $this->start_time = date('Y-m-d', strtotime('-7 days'));
                $this->stop_time = date('Y-m-d', strtotime('-1 days'));
                break;
            case 4:
                $this->start_time = date('Y-m-d', strtotime('-30 days'));
                $this->stop_time = date('Y-m-d', strtotime('-1 days'));
                break;
        }

        return true;
    }


    /**
     * 获取时间轴
     * @return array
     */
    public function getDate()
    {
        $dates = [];
        if ($this->child == 'hour') {
            if ($this->stop_time == date('Y-m-d')) {
                $hour = date('G', strtotime('-1 hours'));
            } else {
                $hour = 24;
            }
            $dates = array_fill(0, $hour, 1);
            $dates = array_keys($dates);
        } else if ($this->child == 'day') {
            $sta = strtotime($this->start_time);
            $end = strtotime($this->stop_time);
            while ($sta <= $end) {
                $dates[] = $sta;
                $sta += 86400;
            }
        }

        return $dates;
    }

    /**
     * 设置默认时间
     * @return bool
     */
    public function setDefault()
    {
        $action = Yii::$app->controller->action->id;
        switch ($action) {
            case 'paytype':
                $this->start_time = date('Y-m-1');
                $this->stop_time = date('Y-m-d');
                break;
            case 'checkout':
                $this->start_time = date('Y-m-1');
                $this->stop_time = date('Y-m-d');
                break;
            default :
                $this->start_time = date('Y-m-d');
                $this->stop_time = date('Y-m-d');
                break;
        }

        return true;
    }

    /**
     * 获取数据
     * @param $names
     * @return array
     */
    public function getData($names)
    {
        $sta = strtotime($this->start_time);
        $end = strtotime($this->stop_time) + 86399;

        if (!empty($this->operator)) {
            $rs = $this->getSingleData($sta, $end, $names);
        } else {
            $rs = $this->getMultiData($sta, $end, $names);
        }

        return $rs;
    }


    /**
     * 获取单个管理员缴费记录
     * @param $sta
     * @param $end
     * @param $names
     * @return array
     */
    public function getSingleData($sta, $end, $names)
    {
        $ids = array_keys($names);
        $sames = array_intersect($ids, $this->proIds);
        $data = self::find()
            ->select(['sum(pay_num) nums', 'product_id'])
            ->where('create_at >= :sta and create_at <= :end', [
                ':sta' => $sta,
                ':end' => $end
            ])
            ->andWhere(['mgr_name' => $this->operator])
            ->andWhere(['product_id' => $sames])
            ->groupBy('mgr_name, product_id')
            ->indexBy('product_id')
            ->asArray()
            ->all();

        //var_dump($data);exit;
        $series = $rs = $legends = $dataSeries = [];
        if (!empty($data)) {
            foreach ($data as $k => $v) {
                $rs[$k] = sprintf('%.2f', $v['nums']);
            }
        }

        $series = $this->getPieSeries(Yii::t("app", "statistics by pay type"), $names, $rs, ['50%', '60%']);
        $json = json_encode([$series], JSON_UNESCAPED_UNICODE);
        // var_dump($series, json_encode($series, JSON_UNESCAPED_UNICODE));exit;


        return [
            'data' => [
                'legends' => json_encode($legends, JSON_UNESCAPED_UNICODE),
                'series' => $json
            ],
            'table' => $data
        ];
    }

    /**
     * 获取多个管理员缴费数据
     * @param $sta
     * @param $end
     * @param $names
     * @return array
     */
    public function getMultiData($sta, $end, $names)
    {
        $ids = array_keys($names);
        $sames = array_intersect($ids, $this->proIds);
        $data = self::find()
            ->select(['sum(pay_num) nums', 'mgr_name', 'type', 'product_id'])
            ->where('create_at >= :sta and create_at <= :end', [
                ':sta' => $sta,
                ':end' => $end
            ])
            ->andWhere(['mgr_name' => $this->can_mgr])
            ->andWhere(['product_id' => $sames])
            ->groupBy('mgr_name, product_id')
            ->asArray()
            ->all();


        //var_dump($data);exit;
        $xAxis = $series = $rs = $legends = [];
        if (!empty($data)) {
            foreach ($data as $k => $v) {
                $mgrName = $v['mgr_name'];
                if (!in_array($mgrName, $xAxis)) {
                    $xAxis[] = $mgrName;
                }
                $rs[$mgrName][$v['product_id']] = sprintf('%.2f', $v['nums']);
            }
        }
        foreach ($names as $id => $name) {
            $nameses = $id . ":" . $name;
            $legends[] = $nameses;
            foreach ($rs as $mgr => $v) {
                $series[$nameses][] = isset($v[$id]) ? $v[$id] : 0;
            }
        }
        $json = $this->getChartSeries($series);


        return [
            'data' => [
                'xAxis' => json_encode($xAxis, JSON_UNESCAPED_UNICODE),
                'legends' => json_encode($legends, JSON_UNESCAPED_UNICODE),
                'series' => $json
            ],
            'table' => $data
        ];
    }

    /**
     * 打包柱状图数据
     * @param $data
     * @return string
     */
    public function getChartSeries($data)
    {
        $result = [];
        foreach ($data as $k => $v) {
            $object = new \StdClass;
            $object->name = $k;
            $object->type = 'bar';
            $object2 = new \StdClass();
            $object3 = new \StdClass();
            $object3->position = 'top';
            $object3->show = true;
            $object2->normal = $object3;
            $object->label = $object2;
            $object->data = $v;
            $result[] = $object;
        }

        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 获取柱状图
     * @param $name
     * @param $names
     * @param $data
     * @param $position
     * @return \stdClass
     */
    public function getPieSeries($name, $names, $data, $position = [])
    {
        foreach ($names as $id => $val) {
            $nameses = $id . ":" . $val;
            $num = isset($data[$id]) ? $data[$id] : 0;
            $legends[] = $nameses;
            $obj = new \stdClass();
            $obj->name = $nameses;
            $obj->value = $num;
            $dataSeries[] = $obj;
        }

        //设置饼状图样式
        $emphasis = new \stdClass();
        $emphasis->shadowBlur = 10;
        $emphasis->shadowOffsetX = 0;
        $emphasis->shadowColor = 'rgba(0, 0, 0, 0.5)';

        $label = new \stdClass();
        $label->normal = new \stdClass();
        $label->normal->show = false;
        $itemStyle = new \stdClass();
        $label->emphasis = $emphasis;
        //总的外围包装器
        $series = new \stdClass();
        $series->name = $name;
        $series->type = 'pie';
        $series->radius = '65%';
        $series->center = $position;
        $series->data = $dataSeries;
        $series->label = $label;

        return ['series' => $series, 'legends' => $legends];
    }

    /**
     * 打包多天缴费数据
     * @param $data
     * @param $names
     * @return array
     */
    public function getSeries($data, $names, $flag)
    {
        $rs = $legends = [];
        foreach ($names as $id => $v) {
            $name = $id . ":" . $v;
            $num = isset($data[$id]) ? $data[$id]['nums'] : 0;
            $rs[] = ['name' => $name, 'value' => $num];
            if (!$flag) {
                $legends[] = $name;
            }
        }
        if ($flag) {
            return $rs;
        } else {
            return ['data' => $rs, 'legends' => $legends];
        }
    }

    /**
     * @param $params
     * @return bool
     */
    public function setDate($params)
    {
        $timePoint = isset($params['timePoint']) ? $params['timePoint'] : '';
        if (!empty($timePoint)) {
            $season = ceil((date('n')) / 3);//当月是第几季度
            switch ($timePoint) {
                case 3:
                    $this->start_time = date('Y-m-1');
                    $this->stop_time = date('Y-m-d');
                    break;
                case 5:
                    //本季度
                    $this->start_time = date('Y-m-d', mktime(0, 0, 0, $season * 3 - 3 + 1, 1, date('Y')));
                    $this->stop_time = date('Y-m-d');
                    break;
                case 7:
                    //本年
                    $this->start_time = date('Y-01-01');
                    $this->stop_time = date('Y-m-d');
                    break;
                default:
                    $this->start_time = date('Y-m-1');
                    $this->stop_time = date('Y-m-d');
                    break;
            }
        }

        return true;
    }

    /**
     * 获取缴费方式
     * @return array
     */
    public function getPayTypeUsed()
    {
        $list = PayType::find()->select('id, type_name')->indexBy('id')->asArray()->all();
        $types = [];

        foreach ($list as $id => $val) {
            $types[$id] = $val['type_name'];
        }

        return $types;
    }

    public function getMonthDate()
    {
        $base = date('Y-m-1');

        return [
            '0' => [
                'sta' => date('Y-m-d', strtotime("$base -6 months")),
                'end' => date('Y-m-d', strtotime("$base -5 months")),
            ],
            '1' => [
                'sta' => date('Y-m-d', strtotime("$base -5 months")),
                'end' => date('Y-m-d', strtotime("$base -4 months")),
            ],
            '2' => [
                'sta' => date('Y-m-d', strtotime("$base -4 months")),
                'end' => date('Y-m-d', strtotime("$base -3 months")),
            ],
            '3' => [
                'sta' => date('Y-m-d', strtotime("$base -3 months")),
                'end' => date('Y-m-d', strtotime("$base -2 months")),
            ],
            '4' => [
                'sta' => date('Y-m-d', strtotime("$base -2 months")),
                'end' => date('Y-m-d', strtotime("$base -1 months")),
            ],
            '5' => [
                'sta' => date('Y-m-d', strtotime("$base -1 months")),
                'end' => date('Y-m-d', strtotime("$base")),
            ],
        ];
    }

    /**
     * 获取时间
     * @param int $type
     * @return array
     */
    public function getTime($type = 1)
    {
        $base = date('Y-m-01');
        //获取上月时间
        $next = $type - 1;
        $sta = strtotime("$base -$type months");
        $month = date('n', $sta);
        $end = strtotime("$base -$next months");

        return ['sta' => $sta, 'end' => $end, 'month' => $month];
    }

    /**
     * 格式化流量
     * @param $bytes
     * @return bool|float|string
     */
    public function getBytesFormat($bytes)
    {
        if (empty($bytes)) {
            return '0b';
        } else {
            $i = 0;
            $base = $bytes;
            while ($bytes / 1024 > 1) {
                if ($i >= 4) {
                    break;
                }
                $i++;
                $bytes = $bytes / 1024;
            }
            $unit = $this->getUnit($i - 1);
            $bytes = sprintf('%.2f', $base / pow(1024, $i)) . $unit;

            return $bytes;
        }
    }

    /**
     * 格式化时间
     * @param $time
     * @return bool|float|string
     */
    public function getTimesFormat($time)
    {
        if (empty($time)) {
            return '0s';
        } else {
            $base = $time;
            $i = 0;
            while ($time / 60 > 1) {
                if ($i >= 2) {
                    break;
                }
                $i++;
                $time = $time / 60;
            }
            $unit = $this->getUnit($i, 'times');
            $time = sprintf('%.2f', $base / pow(60, $i)) . $unit;

            return $time;
        }
    }

    public static $unit = [
        'bytes' => ['b', 'Mb', 'Gb', 'Tb'],
        'times' => ['s', 'min', 'h']
    ];

    /**
     * @param $i
     * @param string $type
     * @return mixed
     */
    public function getUnit($i, $type = 'bytes')
    {
        return self::$unit[$type][$i];
    }

}
