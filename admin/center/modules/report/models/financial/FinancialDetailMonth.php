<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2017/5/18
 * Time: 10:35
 */

namespace center\modules\report\models\financial;


use center\modules\user\models\Users;
use yii;
use center\extend\Tool;
use common\models\Redis;
use center\modules\user\models\Base;
use center\modules\log\models\Detail;
use center\modules\report\models\Financial;
use center\modules\financial\models\PayList;
use center\modules\financial\models\TransferBalance;
use center\modules\report\models\SrunDetailDay;
use center\modules\financial\models\CheckoutList;
use center\modules\financial\models\RefundList;

/**
 * 财务报表基础模型
 * Class FinancialReport
 * @package center\modules\report\models
 */
class FinancialDetailMonth extends FinancialBase
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


    public static function getDb()
    {
        return Yii::$app->db_detail;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'srun_detail_month';
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
    public function getRecentlyData()
    {
        $recently = $this->getMonthData(0);
        $recently_one = $this->getMonthData(1);
        $recently_two = $this->getMonthData(2);
        $all = $this->getMonthData('all');
        //exit;
        return [
            '30' => $recently_one,
            '60' => $recently_two,
            'all' => $all,
            'this' => $recently
        ];
    }


    public function getMonthData($type = 1)
    {
        if ($type !== 'all') {
            $base = date('Y-m-01');
            if (empty($type)) {
                $time = strtotime($base);
                if (date('d') == '01') {
                    $data = Detail::find()->where('add_time >= :sta', [':sta' => $time])->select(['sum(total_bytes) bytes', 'sum(time_long) times'])->asArray()->one();
                } else {
                    $data = $this->getBaseData($time);
                }
            } else {
                $time = strtotime("$base -$type months");
                $data = $this->getBaseData($time);
            }

        } else {
            $data = self::find()->select(['sum(total_bytes) bytes', 'sum(time_long) times'])->asArray()->one();
        }
        $bytes = $this->getBytesFormat($data['bytes']);
        $times = $this->getTimesFormat($data['times']);

        return ['bytes' => $bytes, 'times' => $times];
    }

    /**
     * 获取消费情况
     * @param $time
     * @param $group
     * @return mixed|string
     */
    public function getBaseData($time, $group = '')
    {
        $query = self::find();
        $select = ['sum(total_bytes) bytes', 'sum(time_long) times'];
        $query->andWhere(['=', 'record_day', $time]);
        if (!empty($group)) {
            $query->addSelect($group);
            $query->addSelect($select);
            $query->groupBy($group);
            $data = $query->indexBy($group)->orderBy('bytes desc')->limit(30)->asArray()->all();

            return $data;
        } else {
            $data = $query->select($select)->asArray()->one();
        }

        return $data;
    }

    //获取单个用户上期结余
    public function getPrevData()
    {

    }

    /**
     * 获取半年消费
     * @return array
     */
    public function getHalfYearData()
    {
        $xAxis = $series = [];
        $end = date('Y-m-1', strtotime('-1 months'));
        $sta = date('Y-m-d', strtotime("$end -5 months"));
        $data = $this->getMonthDatas($sta, $end);
        $sta = strtotime($sta);
        $end = strtotime($end);
        while ($sta <= $end) {
            $xAxis[] = date('m', $sta) . Yii::t('app', 'months');
            $date = date('Y-m-d', $sta);
            $arr = explode('-', $date);
            $sum = $this->getMonthDaySum($arr[0], $arr[1]);
            $series['total_bytes'][] = isset($data[$sta]) ? sprintf('%.2f', $data[$sta]['bytes'] / 1024 / 1024 / 1024 / 1024) : '0.00';
            $series['time_long'][] = isset($data[$sta]) ? sprintf('%.2f', $data[$sta]['times'] / 60 / 60) : '0.00s';
            $sta = $sta + $sum * 86400;
        }

        $series = $this->getLineSeries('line', $series);
        $legend = [Yii::t('app', 'total_bytes'), Yii::t('app', 'time_long')];
        $title = [Yii::t('app', 'report help15'), Yii::t('app', 'report help16')];
        //var_dump($series);exit;

        return ['xAxis' => $xAxis, 'series' => $series, 'legend' => $legend, 'title' => $title];
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
    public function getMonthDatas($sta, $end)
    {
        $data = self::find()
            ->select(['sum(total_bytes) bytes', 'record_day', 'sum(time_long) times'])
            ->where('record_day >= :sta and record_day <= :end', [
                ':sta' => strtotime($sta),
                ':end' => strtotime($end)
            ])
            ->groupBy('record_day')
            ->indexBy('record_day')
            ->asArray()
            ->all();


        return $data;
    }

    /**
     * 最近用户组消费情况
     * @param int $type
     * @return mixed|string
     */
    public function getGroupData($type = 1)
    {
        //获取上月时间
        $times = $this->getTime($type);
        $data = $this->getBaseData($times['sta'], 'user_group_id');
        $xAxis = $series = [];

        foreach ($data as $k => $v) {
            $name = $this->can_group[$k];
            $xAxis[] = $name;
            $series['total_bytes'][] = sprintf('%.2f', $v['bytes'] / 1024 / 1024 / 1024 / 1024);
            $series['time_long'][] = sprintf('%.2f', $v['times'] / 60 / 60);
        }

        $title = [$times['month'] . Yii::t('app', 'report help2'), $times['month'] . Yii::t('app', 'report help3')];
        $series = $this->getLineSeries('line', $series);
        $legend = [Yii::t('app', 'total_bytes'), Yii::t('app', 'time_long')];

        return ['xAxis' => $xAxis, 'series' => $series, 'legend' => $legend, 'title' => $title];
    }

    /**
     * 获取产品交费情况
     * @param int $type
     * @return array
     */
    public function getProductData($type = 1)
    {
        //获取上月时间
        $times = $this->getTime($type);
        $data = $this->getBaseData($times['sta'], 'products_id');
        $names = $names = $this->baseModel->getProNames();
        $names[0] = Yii::t('app', 'other fee');
        //var_dump($legend);exit
        $xAxis = $series = $dataSeries = [];
        $count = 0;
        foreach ($data as $id => $v) {
            $xAxis[] = $names[$id] . ',id:' . $id;
            $object = new \StdClass;
            $object->name = $names[$id];
            $dataSeries['total_bytes'][] = ['name' => $names[$id] . ',id:' . $id, 'value' => sprintf('%.2f', $v['bytes'] / 1024 / 1024 / 1024 / 1024)];
            $dataSeries['time_long'][] = ['name' => $names[$id] . ',id:' . $id, 'value' => sprintf('%.2f', $v['bytes'] / 60 / 60)];
            $count++;
        }
        $title = [$times['month'] . Yii::t('app', 'report help4'), $times['month'] . Yii::t('app', 'report help5')];
        $names = [Yii::t('app', 'total_bytes'), Yii::t('app', 'time_long')];

        return ['xAxis' => $xAxis, 'series' => $dataSeries, 'legend' => $xAxis, 'title' => $title, 'count' => $count, 'names' => $names];
    }


    /**
     * 获取最近30天流量数据
     * @return array
     */
    public function getRecentlyBytesData()
    {
        $sta = strtotime(date('Y-m-d', strtotime('-30 days')));
        $end = strtotime(date('Y-m-d', strtotime('-1 days')));
        $data = SrunDetailDay::find()->select(['sum(total_bytes) bytes', 'sum(time_long) times', 'record_day'])
            ->where(['between', 'record_day', $sta, $end])
            ->groupBy('record_day')
            ->indexBy('record_day')
            ->asArray()
            ->all();
        $xAxis = $series = [];
        while ($sta <= $end) {
            $xAxis[] = date('Y-m-d', $sta);
            $series['total_bytes'][] = sprintf('%.2f', $data[$sta]['bytes'] / 1024 / 1024 / 1024 / 1024);
            $series['time_long'][] = sprintf('%.2f', $data[$sta]['times'] / 60 / 60);
            $sta = $sta + 86400;
        }

        $title = [Yii::t('app', 'report help6'), Yii::t('app', 'report help7')];
        $series = $this->getLineSeries('bar', $series);
        $names = [Yii::t('app', 'total_bytes'), Yii::t('app', 'time_long')];

        return ['xAxis' => $xAxis, 'series' => $series, 'legend' => $names, 'title' => $title, 'names' => $names];

    }

    /**
     * 获取用户详情
     * @param $user_name
     * @return array
     */
    public function getUserDetail($user_name, $params)
    {
        $rs = [];
        try {
            $user = Users::findOne(['user_name' => $user_name]);
            if (!$user) {
                $rs = ['code' => 404, 'msg' => Yii::t('app', 'The user does not exist.')];
            } else {
                if (!$this->flag) {
                    if (!in_array($user->group_id, $this->can_group)) {
                        $rs = ['code' => 403, 'msg' => Yii::t('app', 'message 401 3')];

                        return $rs;
                    }
                }
                if (!empty($user['products_id']) && !empty($user['products_id'])) {
                    $rs = ['code' => 401, 'msg' => Yii::t('app', 'batch excel help29')];
                } else {
                    //获取用户产品消息
                    //var_dump(Yii::$app->request->isAjax);exit;
                    $showType = isset($params['showType']) ? $params['showType'] : '';
                    $type = isset($params['type']) ? $params['type'] : 0;

                    if ($showType == 'ajax') {
                        $checkoutOrPayDetail = $this->getPayDetail($user, $type, $showType);
                        $rs = $this->getAjaxData($user, $checkoutOrPayDetail);

                        return $rs;
                    } else if ($showType == 'echarts') {
                        $detail = $this->getRsDetail($user);

                        return $detail;
                    }

                    $checkoutOrPayDetail = $this->getPayDetail($user, $type, $showType);
                    // var_dump($checkoutOrPayDetail);exit;

                    return ['code' => 200, 'user' => $user, 'detail' => $checkoutOrPayDetail];
                }
            }
        } catch (\Exception $e) {
            $rs = ['code' => 500, 'msg' => '发生异常： ' . $e->getMessage()];
        }

        return $rs;
    }

    public function getPayDetail($user, $type = 0, $showType = '')
    {
        $rs = [];
        $username = $user['user_name'];
        $rs['all']['checkout_num'] = $rs['all']['refund_num'] = $rs['all']['pay_num'] = $rs['all']['transfer_num_from'] = $rs['all']['transfer_num_to'] = $rs['all']['money'] = 0;
        $min = $max = 0;
        //获取转入转出详情
        $rs['checkout']['detail'] = $rs['pay']['detail'] = $rs['transfer_from']['detail'] = $rs['transfer_to']['detail'] = $rs['refund']['detail'] = [];
        //获取最大结算日期
        foreach ($user['products_id'] as $id) {
            //获取产品详情
            $products = $this->getProDetail($id);
            //需获取本期待结算
            $checkoutMode = isset($products['checkout_mode']) ? $products['checkout_mode'] : '';
            $checkoutCycle = isset($products['checkout_cycle']) ? $products['checkout_cycle'] : '';

            //var_dump($query, $checkout);exit;
            //获取本期待结算日期
            $dates = $this->getCheckoutDate($checkoutMode, $checkoutCycle, $username, $id, $type);
            $rs[$id]['code'] = $dates['code'];
            if ($dates['code'] == 200) {
                if ($min != 0) {
                    $min = min($dates['sta'], $min);
                } else {
                    $min = $dates['sta'];
                }
                $max = max($dates['end'], $max);
                //获取本期结算数据
                $data = CheckoutList::find()
                    ->select(['user_name', 'product_id', 'buy_id', 'spend_num', 'rt_spend_num', 'create_at', 'flux', 'buy_id', 'group_id', 'minutes'])
                    ->where('user_name = :user and product_id = :pro and create_at > :sta  and create_at < :end', [
                        ':user' => $user['user_name'],
                        ':pro' => $id,
                        ':sta' => strtotime($dates['sta']) + 3600,
                        ':end' => strtotime($dates['end']) + 86400
                    ])
                    ->asArray()
                    ->all();
                $rs['all']['checkout_num'] = 0;
                $rs[$id]['checkout_num'] = 0;
                $rs[$id]['pro_fee'] = 0;
                $rs[$id]['pay_num'] = 0;
                $rs[$id]['refund_num'] = 0;
                if (!empty($data)) {
                    foreach ($data as $v) {
                        $rs['all']['checkout_num'] = $rs['all']['checkout_num'] + $v['spend_num'] + $v['rt_spend_num'];
                        $rs[$id]['checkout_num'] = $v['spend_num'] + $v['rt_spend_num'];
                        $rs[$id]['pro_fee'] = $v['spend_num'] + $v['rt_spend_num'];
                        $rs[$id]['pay_num'] = 0;
                        if ($v['buy_id'] != 0) {
                            //产品月租费
                            $rs[$id]['package'][$v['buy_id']]['checkout_num'] += $v['spend_num'] + $v['rt_spend_num'];
                            $rs[$id]['package'][$v['buy_id']]['name'] = $this->getPackageName($v['buy_id']);
                            $rs[$id]['package']['checkout_num'] += $v['spend_num'] + $v['rt_spend_num'];
                        }
                        $rs['checkout']['detail'][] = $v;
                    }
                }
                $rs[$id]['dates'] = $dates;
                $rs[$id]['name'] = $products['products_name'];
                //获取产品退费金额
            } else {
                //产品无结算
                $rs[$id]['msg'] = Yii::t('app', 'product id') . ':' . $id . ';' . Yii::t('app', 'product name') . ':' . $products['products_name'] . ';msg:' . $dates['msg'];
            }
        }

        if ($min != 0) {
            $sta = strtotime($min);
            $end = strtotime($max);
            //缴费
            $payDetail = $this->getDetail($username, $sta, $end);
            if (!empty($payDetail)) {
                foreach ($payDetail as $id => $val) {
                    //计算总充值以及单个产品充值
                    $packageId = $val['package_id'];
                    $proId = $val['product_id'];
                    if ($packageId > 0) {
                        $rs[$proId]['package'][$packageId]['pay_num'] += $val['pay_num'];
                        $rs[$proId]['pay_num'] += $val['pay_num'];
                        $rs['all']['pay_num'] += $val['pay_num'];
                    } else {
                        $rs[$proId]['pro_fee'] += $val['pay_num'];
                        $rs['all']['pay_num'] += $val['pay_num'];
                    }

                    $rs['pay']['detail'][] = $val;
                }
            }
            $transferDetail = $this->getDetail($username, $sta, $end, 'transfer_balance');
            if (!empty($transferDetail)) {
                foreach ($transferDetail as $val) {
                    if ($val['user_name_from'] == $username) {
                        //转出
                        $rs['all']['transfer_num_to'] += $val['transfer_num'];
                        $rs['transfer_to']['detail'][] = $val;
                    } else {
                        //转入
                        $rs['all']['transfer_num_from'] += $val['transfer_num'];
                        $rs['transfer_from']['detail'][] = $val;
                    }
                }
            }
            $refundDetail = $transferDetail = $this->getDetail($username, $sta, $end, 'refund_list');
            if (!empty($refundDetail)) {
                foreach ($refundDetail as $value) {
                    if ($value['type'] == 1) {
                        //产品退费
                        $rs[$value['product_id']]['refund_num'] = $value['refund_num'];
                    } else if ($value['type'] == 0) {
                        $rs['all']['refund_num'] += $value['refund_num'];
                    }
                    $rs['refund']['detail'][] = $value;
                }
            }
        }

        $user['user_available'] = (new Users())->getAttributesList()['user_available'][$user['user_available']];

        return $rs;
    }

    /**
     * 获取数据库数据
     * @param $username
     * @param $sta
     * @param $end
     * @param string $type
     * @return array|yii\db\ActiveRecord[]
     */
    public function getDetail($username, $sta, $end, $type = 'pay_list')
    {
        if ($type == 'pay_list') {
            $query = PayList::find()->select(['user_name', 'product_id', 'pay_num', 'create_at', 'package_id', 'group_id', 'balance_pre', 'mgr_name operator']);
            $query->where('user_name = :user and create_at > :sta  and create_at < :end', [
                ':user' => $username,
                ':sta' => $sta,
                ':end' => $end
            ]);
        } else if ($type == 'transfer_balance') {
            $query = TransferBalance::find()->select(['user_name_from', 'user_name_to', 'product_id', 'transfer_num', 'create_at', 'package_id', 'type', 'mgr_name']);
            $query->where('(user_name_from = :user or user_name_to=:user) and create_at > :sta  and create_at < :end and user_name_from  != user_name_to', [
                ':user' => $username,
                ':sta' => $sta,
                ':end' => $end
            ]);
        } else {
            $query = RefundList::find()->select(['user_name', 'product_id', 'refund_num', 'create_at', 'type', 'product_id']);
            $query->where('user_name = :user and create_at > :sta  and create_at < :end and type=0', [
                ':user' => $username,
                ':sta' => $sta,
                ':end' => $end
            ]);
        }

        return $query->asArray()->all();

    }

    /**
     * 获取产品详情
     * @param $id
     * @return mixed
     */
    public function getProDetail($id)
    {
        $hash = Redis::executeCommand('hgetall', 'hash:products:' . $id);
        $detail = Redis::hashToArray($hash);

        return $detail;
    }

    /**
     * 获取套餐名称
     * @param $id
     * @return mixed
     */
    public function getPackageName($id)
    {
        $hash = Redis::executeCommand('hgetall', 'hash:package:' . $id);
        if (empty($hash)) {
            return $id;
        }
        $detail = Redis::hashToArray($hash);

        return $detail['package_name'];
    }

    /**
     * 获取本期待结算日期
     * @param $mode
     * @param $cycle
     * @param $username
     * @param $product_id
     * @return array
     */
    public function getCheckoutDate($mode, $cycle, $username, $product_id, $type = 0)
    {
        //获取最新结算
        if ($mode == 32) {
            //按首次登录去明细表查最近计算获取本期结算日期
            $query = CheckoutList::find()
                ->select(['user_name', 'spend_num', 'rt_spend_num', 'create_at'])
                ->where('type=0 and user_name=:user and product_id=:pro and buy_id=0', [
                    ':user' => $username,
                    ':pro' => $product_id
                ])->orderBy('create_at asc')
                ->indexBy('create_at')
                ->asArray();
            if ($type != 0) {
                $checkout = $query->limit($type + 1)->all();
                if (empty($checkout)) {
                    //无结算日期
                    return ['code' => 409, 'msg' => Yii::t('app', 'report help1')];
                }
                $times = array_keys($checkout);
                $create_at = $times[0];
            } else {
                $checkout = $query->one();

                if (is_null($checkout)) {
                    //无结算日期
                    return ['code' => 409, 'msg' => Yii::t('app', 'report help1')];
                }
                $create_at = $checkout['create_at'];
            }

            if ($cycle == 'month') {
                //按月结算
                $end = date('Y-m-d', $create_at);
                $sta = date('Y-m-d', strtotime("$end -1 months"));
            } else if ($cycle == 'day') {
                //按天
                $end = date('Y-m-d', $create_at);
                $sta = date('Y-m-d', strtotime("$end -1 days"));

            } else {
                //按年
                $end = date('Y-m-d', $create_at);
                $sta = date('Y-m-d', strtotime("$end -1 years"));
            }
        } else {
            //1-31号结算
            $base = date('Y-m') . '-' . $mode;
            $next = $type;
            $end = date('Y-m-d', strtotime("$base -$next months"));
            if ($cycle == 'month') {
                //按月结算
                $sta = date('Y-m-d', strtotime("$end -1 months"));
            }
        }
        //var_dump($sta, $end);exit;
        return ['sta' => $sta, 'end' => $end, 'code' => 200];
    }

    /**
     * 整理数据
     * @param $user
     * @param $detail
     * @return string
     */
    public function getAjaxData($user, $detail)
    {
        $header = [];
        $header['user_msg'] = $this->getHeader();
        $header['pay_detail'] = $this->getHeader('pay');
        $header['product_detail'] = $this->getHeader('product');
        $header['checkout_detail'] = $this->getHeader('checkout');
        $header['transfer_detail'] = $this->getHeader('transfer');
        $header['refund_detail'] = $this->getHeader('refund');
        $i = 0;
        $data = [];
        if ($detail) {
            try {
                foreach ($detail as $key => $value1) {
                    if ($key == 'all') {
                        $data['user_msg'] = array_combine(array_keys($header['user_msg']), [
                            $user['user_name'], $user['user_real_name'], $this->can_group[$user['group_id']], $user['user_available'], $value1['checkout_num'], $value1['refund_num'],
                            $value1['pay_num'], $value1['transfer_num_from'], $value1['transfer_num_to']
                        ]);
                    } else if ($key == 'checkout' || $key == 'pay' || $key == 'refund') {
                        //组装缴费详细和结算详细
                        $data[$key . '_detail'] = [];
                        if (!empty($value1['detail'])) {
                            foreach ($value1['detail'] as $value) {
                                if ($key == 'pay') {
                                    $data[$key . '_detail'][] = array_combine(array_keys($header[$key . '_detail']), [
                                        $user['user_name'], $user['user_real_name'], $value['pay_num'], $value['product_id'], $this->getPackageName($value['package_id']), $value['operator'], date('Y-m-d H:i', $value['create_at'])]);
                                } else if ($key == 'checkout') {
                                    $data[$key . '_detail'][] = array_combine(array_keys($header[$key . '_detail']), [
                                        $user['user_name'], $user['user_real_name'], $value['spend_num'] + $value['rt_spend_num'], $value['product_id'], $this->getPackageName($value['buy_id']), Tool::bytes_format($value['flux']), Tool::seconds_format($value['minutes']), date('Y-m-d H:i', $value['create_at'])]);
                                } else {
                                    $data[$key . '_detail'][] = array_combine(array_keys($header[$key . '_detail']), [
                                        $value['user_name'], $value['refund_num'], date('Y-m-d H:i', $value['create_at'])]);
                                }

                            }
                        }

                    } else if (preg_match('/transfer/', $key)) {
                        $data[$key . '_detail'] = [];
                        foreach ($value1['detail'] as $value) {
                            // var_dump($value1['detail']);exit;
                            if ($key == 'transfer_to') {
                                $username = $value['user_name_to'];
                            } else {
                                $username = $value['user_name_from'];
                            }

                            $data[$key . '_detail'][] = array_combine(array_keys($header['transfer_detail']), [$username, $value['transfer_num'], $value['mgr_name'], date('Y-m-d H:i', $value['create_at'])]);

                        }
                    } else if (!empty($key)) {
                        //产品详细
                        if ($value1['code'] == 200) {
                            $data['product_detail'][$key] = array_combine(array_keys($header['product_detail']), [
                                $key, $value1['name'], $value1['checkout_num'], Yii::t('app', 'product') . ':' . $value1['pro_fee'], Yii::t('app', 'product') . ':' . sprintf('%.2f', $value1['pay_num']), $value1['dates']['sta'] . '-' . $value1['dates']['end']
                            ]);
                            if (!empty($value1['package'])) {
                                foreach ($value1['package'] as $id => $value3) {
                                    if (is_numeric($id)) {
                                        $data['product_detail'][$key]['fee_detail'] .= "\r\n" . Yii::t('app', 'package id') . ':' . $id . ',' . Yii::t('app', 'package name') .
                                            ':' . $value3['name'] . ',' . Yii::t('app', 'checkout amount') . ':' . $value3['checkout_num'];
                                        $data['product_detail'][$key]['pay_fee_detail'] .= "\r\n" . Yii::t('app', 'package id') . ':' . $id . ',' . Yii::t('app', 'package name') . ':' . $value3['name']
                                            . ',' . Yii::t('app', 'payment') . ':' . $value3['pay_num'];
                                    }
                                }

                            }
                        } else {
                            $data['product_detail'][$key] = array_combine(['msg'], [
                                $value1['msg']
                            ]);
                        }

                    }
                    $i++;
                }
            } catch (\Exception $e) {
                echo $e->getMessage();
                echo $e->getLine();
                exit;
            }

        }

        return yii\helpers\Json::encode([
            'header' => $header,
            'data' => $data
        ]);

    }

    /**
     * 获取月份对应的天数
     * */
    private function getMonthDaySum($year, $month)
    {
        $arr1 = [1, 3, 5, 7, 8, 10, 12];
        $arr2 = [4, 6, 9, 11];
        if (in_array($month, $arr1)) {
            $daySum = 31;
        } else if (in_array($month, $arr2)) {
            $daySum = 30;
        } else if ($month == 2) {
            if (!($year % 4)) {
                $daySum = 29;
            } else {
                $daySum = 28;
            }
        } else {
            return false;
        }
        return $daySum;
    }


    private function getHeader($type = 'user')
    {
        $header = [];
        switch ($type) {
            case 'user':
                $header = [
                    'user_name' => Yii::t('app', 'user_name'),
                    'user_real_name' => Yii::t('app', 'user_real_name'),
                    'group_id' => Yii::t('app', 'group_id'),
                    'user_available' => Yii::t('app', 'user available'),
                    'total_fee' => Yii::t('app', 'total fee'),
                    'refund_fee' => Yii::t('app', 'refund fee'),
                    'pay_fee' => Yii::t('app', 'pay fee'),
                    'transfer_from_fee' => Yii::t('app', 'transfer from fee'),
                    'transfer_to_fee' => Yii::t('app', 'transfer to fee'),
                ];
                break;
            case 'product':
                $header = [
                    'product_id' => Yii::t('app', 'product id'),
                    'product_name' => Yii::t('app', 'product name'),
                    'total_fee' => Yii::t('app', 'total fee'),
                    'fee_detail' => Yii::t('app', 'fee detail'),
                    'pay_fee_detail' => Yii::t('app', 'pay fee detail'),
                    'date_between' => Yii::t('app', 'Settlement cycle')
                ];
                break;

            case 'pay':
                $header = [
                    'user_name' => Yii::t('app', 'user_name'),
                    'user_real_name' => Yii::t('app', 'user_real_name'),
                    'pay_num' => Yii::t('app', 'payment'),
                    'product_id' => Yii::t('app', 'product id'),
                    'package_id' => Yii::t('app', 'package name'),
                    'mgr_name' => Yii::t('app', 'mgr_name'),
                    'create_at' => Yii::t('app', 'report operate remind6'),
                ];
                break;

            case 'checkout':
                $header = [
                    'user_name' => Yii::t('app', 'user_name'),
                    'user_real_name' => Yii::t('app', 'user_real_name'),
                    'checkout_num' => Yii::t('app', 'checkout amount'),
                    'product_id' => Yii::t('app', 'product id'),
                    'package_id' => Yii::t('app', 'package name'),
                    'flux' => Yii::t('app', 'flux'),
                    'time_long' => Yii::t('app', 'time lenth'),
                    'create_at' => Yii::t('app', 'checkout time'),
                ];
                break;
            case 'transfer':
                $header = [
                    'user_name' => Yii::t('app', 'user_name'),
                    'transfer_num' => Yii::t('app', 'transfer num'),
                    'mgr_name' => Yii::t('app', 'mgr_name'),
                    'create_at' => Yii::t('app', 'transfer time'),
                ];
                break;
            case 'refund':
                $header = [
                    'user_name' => Yii::t('app', 'user_name'),
                    'refund_num' => Yii::t('app', 'refund amount'),
                    'create_at' => Yii::t('app', 'refund time'),
                ];
                break;
        }


        return $header;
    }

    public function getRsDetail($user)
    {
        $dates = $this->getMonthDate();
        $rs = $xAxis = [];
        //获取最近五个月的缴费结算等记录
        foreach ($dates as $v) {
            $pay = $this->getUserMonthData($user['user_name'], $v);
            $check = $this->getUserMonthData($user['user_name'], $v, 'checkout');
            $from = $this->getUserMonthData($user['user_name'], $v, 'transfer_from');
            $to = $this->getUserMonthData($user['user_name'], $v, 'transfer_to');
            $refund = $this->getUserMonthData($user['user_name'], $v, 'refund');
            $rs['pay'][] = $pay['num'];
            $rs['check'][] = $check['num'];
            $rs['transfer_from'][] = $from['num'];
            $rs['transfer_to'][] = $to['num'];
            $rs['refund'][] = $refund['num'];
            $xAxis[] = $pay['month'];
        }

        $series = $this->getLineSeries('line', $rs);
        $title = [Yii::t('app', 'report help13'), Yii::t('app', 'report help14'), Yii::t('app', 'report help24'), Yii::t('app', 'report help25'), Yii::t('app', 'report help26')];

        return yii\helpers\Json::encode(['title' => $title, 'xAxis' => $xAxis, 'series' => $series]);
    }

    /**
     * 获取每月数据
     * @param $username
     * @param $params
     * @param string $type
     * @return array
     */
    public function getUserMonthData($username, $params, $type = 'pay_list')
    {
        if ($type == 'pay_list') {
            $data = PayList::find()
                ->select('sum(pay_num) nums')
                ->where('user_name =:user and create_at >= :sta and create_at < :end', [
                    ':user' => $username,
                    ':sta' => strtotime($params['sta']),
                    ':end' => strtotime($params['end'])
                ])
                ->asArray()
                ->one();
        } else if ($type == 'checkout') {
            $data = CheckoutList::find()
                ->select('sum(spend_num + rt_spend_num) nums')
                ->where('user_name =:user and create_at >= :sta and create_at < :end', [
                    ':user' => $username,
                    ':sta' => strtotime($params['sta']),
                    ':end' => strtotime($params['end'])
                ])
                ->asArray()
                ->one();
        } else if (preg_match('/transfer/', $type)) {
            $query = TransferBalance::find()->select('sum(transfer_num) nums')
                ->where('user_name_from !=user_name_to and create_at >= :sta and create_at < :end', [
                    ':sta' => strtotime($params['sta']),
                    ':end' => strtotime($params['end'])
                ]);
            if ($type == 'transfer_from') {
                //转入
                $query->andWhere('user_name_to =:user', [':user' => $username]);
            } else {
                //转出
                $query->andWhere('user_name_from =:user', [':user' => $username]);
            }
            $data = $query->asArray()->one();
        } else {
            $data = RefundList::find()
                ->select('sum(refund_num) nums')
                ->where('user_name =:user and create_at >= :sta and create_at < :end and type=0', [
                    ':user' => $username,
                    ':sta' => strtotime($params['sta']),
                    ':end' => strtotime($params['end'])
                ])
                ->asArray()
                ->one();
        }
        //var_dump($data);exit;

        return [
            'month' => date('m', strtotime($params['sta'])) . Yii::t('app', 'months'),
            'num' => is_null($data['nums']) ? 0 : sprintf('%.2f', $data['nums'])
        ];
    }

}
