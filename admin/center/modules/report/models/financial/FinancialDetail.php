<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2017/5/18
 * Time: 10:35
 */

namespace center\modules\report\models\financial;

use center\modules\financial\models\TransferBalance;
use yii;
use common\models\User;
use yii\db\ActiveRecord;
use center\modules\report\models\Financial;
use center\modules\auth\models\SrunJiegou;
use center\modules\financial\models\PayList;
use center\modules\financial\models\CheckoutList;

/**
 * 财务报表基础模型
 * Class FinancialReport
 * @package center\modules\report\models
 */
class FinancialDetail extends FinancialBase
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

    /**
     * @return array
     */
    public function getPayOrCheckoutData($type = 'pay_list')
    {
        $recently = $this->getMonthData(0, $type);
        $recently_one = $this->getMonthData(1, $type);
        $recently_two = $this->getMonthData(2, $type);
        $all = $this->getMonthData('all', $type);
        //exit;
        return [
            '30' => $recently_one,
            '60' => $recently_two,
            'all' => $all,
            'this' => $recently
        ];
    }

    public function getMonthData($type = 1, $payType)
    {
        if ($type !== 'all') {
            $base = date('Y-m-01');
            if (empty($type)) {
                $sta = strtotime($base);
                $end = time();
            } else {
                $next = $type - 1;
                $sta = strtotime("$base -$type months");
                $end = strtotime("$base -$next months");
            }
            $num = $this->getBaseData($sta, $end, $payType);

        } else {
            if ($payType == 'pay_list') {
                $num = self::find()->select('sum(pay_num) nums')->asArray()->one();
            } else {
                $num = CheckoutList::find()->select('sum(spend_num+rt_spend_num) nums')->asArray()->one();
            }

            $num = $num['nums'];
        }

        return $num;
    }

    /**
     * 获取消费情况
     * @param $sta
     * @param $end
     * @param string $type
     * @return mixed|string
     */
    public function getBaseData($sta, $end, $type = 'pay_list', $group = '')
    {
        if ($type == 'pay_list') {
            $query = self::find();
            $select = 'SUM(pay_num) nums';
        } else if ($type == 'transfer') {
            $query = TransferBalance::find()->where('type=0 and user_name_from = user_name_to and product_id != 0');
            $select = 'SUM(transfer_num) nums';
        } else {
            $query = CheckoutList::find();
            $select = 'SUM(spend_num+rt_spend_num) nums';
        }
        $query->andWhere(['>=', 'create_at', $sta]);
        $query->andWhere(['<=', 'create_at', $end]);
        if (!empty($group)) {
            $query->addSelect($group);
            $query->addSelect($select);
            $query->groupBy($group);
            $data = $query->indexBy($group)->orderBy('nums desc')->limit(30)->asArray()->all();

            return $data;
        } else {
            $data = $query->select($select)->asArray()->one();
        }

        return $data ? $data['nums'] : '';
    }


    /**
     * 获取半年消费
     * @return array
     */
    public function getHalfYearData($type)
    {
        $monthDate = $this->getMonthDate();
        $xAxis = $series = [];
        foreach ($monthDate as $v) {
            $data = $this->getMonthDatas($v, $type);
            $xAxis[] = $data['month'];
            $series[] = $data['num'];
        }
        $name = $type == 'pay_list' ? Yii::t('app', 'batch excel pay num') : Yii::t('app', 'checkout amount');
        $title = $type == 'pay_list' ? Yii::t('app', 'report help13') :Yii::t('app', 'report help14');
        $series = $this->getBarSeries('line', $name, $series);

        return ['xAxis' => $xAxis, 'series' => $series, 'legend' => [$name], 'title' => $title];
    }

    public function getBarSeries($type, $name, $data)
    {
        $object = new \stdClass();
        $object->type = $type;
        $object->name = \Yii::t('app', $name);
        $object->data = $data;
        $result[] = $object;

        return $result;
    }

    /**
     * 获取某月数据
     * @param $params
     * @return array
     */
    public function getMonthDatas($params, $type)
    {
        if ($type == 'pay_list') {
            $data = self::find()
                ->select('sum(pay_num) nums')
                ->where('create_at >= :sta and create_at < :end', [
                    ':sta' => strtotime($params['sta']),
                    ':end' => strtotime($params['end'])
                ])
                ->asArray()
                ->one();
        } else {
            $data = CheckoutList::find()
                ->select('sum(spend_num + rt_spend_num) nums')
                ->where('create_at >= :sta and create_at < :end', [
                    ':sta' => strtotime($params['sta']),
                    ':end' => strtotime($params['end'])
                ])
                ->asArray()
                ->one();
        }


        return [
            'month' => date('m', strtotime($params['sta'])) . Yii::t('app', 'months'),
            'num' => is_null($data['nums']) ? 0 : sprintf('%.2f', $data['nums'])
        ];
    }

    /**
     * 最近用户组消费情况
     * @param int $type
     * @return mixed|string
     */
    public function getGroupData($type = 1, $pay)
    {
        //获取上月时间
        $times = $this->getTime($type);
        $data = $this->getBaseData($times['sta'], $times['end'], $pay, 'group_id');
        $xAxis = $series = [];

        foreach ($data as $k => $v) {
            $name = $this->can_group[$k];
            $xAxis[] = $name;
            $series[] = sprintf('%.2f', $v['nums']);
        }

        $name = $pay == 'pay_list' ? Yii::t('app', 'batch excel pay num') : Yii::t('app', 'checkout amount');
        $title = $pay == 'pay_list' ? $times['month'] . Yii::t('app', 'report help8') : $times['month'] . Yii::t('app', 'report help9');
        $series = $this->getBarSeries('line', $name, $series);

        return ['xAxis' => $xAxis, 'series' => $series, 'legend' => [$name], 'title' => $title];
    }

    /**
     * 获取产品交费情况
     * @param int $type
     * @return array
     */
    public function getProductData($type = 1, $pay)
    {
        //获取上月时间
        $times = $this->getTime($type);
        $data = $this->getBaseData($times['sta'], $times['end'], $pay, 'product_id');
        $names = $this->baseModel->getProNames();
        //var_dump($legend);exit
        $xAxis = $series = $dataSeries = [];
        $count = 0;
        foreach ($data as $id => $v) {
            $xAxis[] = $names[$id];
            $object = new \StdClass;
            $object->name = $names[$id];
            $object->value = sprintf('%.2f', $v['nums']);
            $dataSeries[] = $object;
            $count++;
        }
        $title = $pay == 'transfer' ? $times['month'] . Yii::t('app', 'report help10') : $times['month'] . Yii::t('app', 'report help11');
        $name = $pay == 'transfer' ? Yii::t('app', 'batch excel pay num') : Yii::t('app', 'checkout amount');

        return ['xAxis' => $xAxis, 'series' => $dataSeries, 'legend' => $xAxis, 'title' => $title, 'count' => $count, 'name' => $name];
    }

    /**
     * 获取按缴费方式
     * @param int $type
     * @return array
     */
    public function getPayTypeData($type = 1)
    {
        //获取上月时间
        $times = $this->getTime($type);
        $data = $this->getBaseData($times['sta'], $times['end'], 'pay_list', 'pay_type_id');
        //var_dump($legend);exit
        $xAxis = $series = $dataSeries = [];
        $count = 0;
        $names = $this->getPayTypeUsed();
        foreach ($data as $id => $v) {
            $xAxis[] = $names[$id];
            $object = new \StdClass;
            $object->name = $names[$id];
            $object->value = sprintf('%.2f', $v['nums']);
            $dataSeries[] = $object;
            $count++;
        }


        return ['xAxis' => $xAxis, 'series' => $dataSeries, 'legend' => $xAxis, 'title' => $times['month'] . Yii::t('app', 'report help12'), 'count' => $count, 'name' => Yii::t('app', 'batch excel pay num')];
    }
}
