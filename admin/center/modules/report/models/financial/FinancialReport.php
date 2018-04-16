<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2017/5/18
 * Time: 10:35
 */

namespace center\modules\report\models\financial;


use center\modules\auth\models\SrunJiegou;
use center\modules\financial\models\TransferBalance;
use common\extend\Excel;
use yii;
use center\modules\report\models\Financial;

/**
 * 财务报表基础模型
 * Class FinancialReport
 * @package center\modules\report\models
 */
class FinancialReport extends FinancialBase
{
    public $multi;

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
        $query = self::find()
            ->select(['sum(pay_num) nums', 'product_id'])
            ->where('create_at >= :sta and create_at <= :end', [
                ':sta' => $sta,
                ':end' => $end
            ]);
        if (!$this->flag) {
            //增加用户组权限
            $query->andWhere(['group_id' => array_keys($this->can_group)]);
        }
        $data = $query->andWhere(['mgr_name' => $this->operator])
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
        $json = json_encode([$series['series']], JSON_UNESCAPED_UNICODE);
        $legends = $series['legends'];
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
        $query = self::find()
            ->select(['sum(pay_num) nums', 'mgr_name'])
            ->where('create_at >= :sta and create_at <= :end', [
                ':sta' => $sta,
                ':end' => $end
            ]);
        if (!$this->flag) {
            $query->andWhere(['group_id' => array_keys($this->can_group)]);
        }
        $data = $query->andWhere(['mgr_name' => $this->can_mgr])
            ->indexBy('mgr_name')
            ->groupBy('mgr_name')
            ->asArray()
            ->all();

        $xAxis = $series = $rs = $legends = [];
        $xAxis = array_keys($data);
        if (!empty($data)) {
            foreach ($data as $k => $v) {
                $mgrName = $v['mgr_name'];
                $rs[] = sprintf('%.2f', $v['nums']);
            }
        }
        //$json = $this->getChartSeries($series);
        return [
            'data' => [
                'xAxis' => json_encode($xAxis, JSON_UNESCAPED_UNICODE),
                'legends' => json_encode($legends, JSON_UNESCAPED_UNICODE),
                'series' => json_encode($rs, JSON_UNESCAPED_UNICODE)
            ],
            'table' => $data
        ];
    }

    /**
     * 获取每天缴费情况
     * @param $names
     * @return array
     */
    public function getDataByTime($names)
    {
        $this->multi = ($this->start_time == $this->stop_time);
        $this->sql_type = 'product_id';
        $rs = $this->getRsData($names);

        return $rs;
    }

    /**
     *
     * @return array
     */
    public function getRsData($names)
    {
        $rs = [];
        $sta = strtotime($this->start_time);
        $end = strtotime($this->stop_time);
        $rs = $this->getOneDayData($sta, $end, $names);
        //var_dump($rs);exit;

        return $rs;
    }

    /**
     * 获取多天缴费情况
     * @param $sta
     * @param $end
     * @param $names
     * @return array
     */
    public function getMultiTimeData($sta, $end, $names)
    {
        $ids = array_keys($names);
        $legends = $table = $xAxis = $series = [];
        $i = 0;
        while ($sta <= $end) {
            $day = date('Y-m-d', $sta);
            $xAxis[] = $day;
            $from = $this->getOneDay($sta, $end, $ids); //产品转入
            $to = $this->getOneDay($sta, $end, $ids, 1); //产品转出
            if (!empty($data)) {
                $table[$day] = $data;
            }
            $rs = $this->getSeries($data, $names, (bool)$i);
            if ($i == 0) {
                $legends = $rs['legends'];
                $series[$day] = $rs['data'];
            } else {
                $series[$day] = $rs;
            }
            $i++;
            $sta += 86400;
        }

        return [
            'data' => [
                'xAxis' => json_encode($xAxis, JSON_UNESCAPED_UNICODE),
                'legends' => json_encode($legends, JSON_UNESCAPED_UNICODE),
                'series' => $series
            ],
            'table' => $table
        ];
    }

    /**
     * 组装单天数据
     * @param $sta
     * @param $end
     * @param $names
     * @return array
     */
    public function getOneDayData($sta, $end, $names)
    {
        $ids = array_keys($names);
        $from = $this->getOneDay($sta, $end, $ids); //产品转入
        $to = $this->getOneDay($sta, $end, $ids, 1); //产品转出, 相当于产品退费
        //var_dump($data);exit;
        $series = $rs = $legends = $dataSeries = $table = [];
        if (!empty($from) || !empty($to)) {
            foreach ($from as $k => $v) {
                $rs['from'][$k] = sprintf('%.2f', $v['nums']);
                $table[$k]['from'] = sprintf('%.2f', $v['nums']);
            }
            foreach ($to as $k => $v) {
                $rs['to'][$k] = sprintf('%.2f', $v['nums']);
                $table[$k]['to'] = sprintf('%.2f', $v['nums']);
            }
        }

        if ($this->sql_type == 'product_id') {
            $name = Yii::t("app", "report/financial/product");
        } else {
            $name = Yii::t("app", "report/financial/usergroup");
        }
        $series = $this->getPieSeries($name, $names, $rs, ['55%', '65%']);
        $json = json_encode($series['series'], JSON_UNESCAPED_UNICODE);
        $legends = $series['legends'];

        return [
            'data' => [
                'legends' => json_encode($legends, JSON_UNESCAPED_UNICODE),
                'series' => $json
            ],
            'table' => $table
        ];

    }

    /**
     * 获取单天数据
     * @param $sta
     * @param $end
     * @param $ids
     * @param integer $type
     *
     * @return array|yii\db\ActiveRecord[]
     */
    public function getOneDay($sta, $end, $ids, $type = 0)
    {
        $end = $end + 86399;
        //钱包转入
        $query = TransferBalance::find()
            ->select(['sum(transfer_num) nums', $this->sql_type])
            ->where(['between', 'create_at', $sta, $end])
            ->andWhere(['type' => $type])
            ->andWhere([$this->sql_type => $ids]);
        if (!$this->flag) {
            if ($this->sql_type == 'product_id') {
                $query->andWhere(['group_id' => array_keys($this->can_group)]);
            } else {
                $names = $this->baseModel->getProNames();
                $names[0] = Yii::t('app', 'other fee');
                $query->andWhere(['product_id' => $names]);
            }
        }

        //钱包转出

        $data = $query->indexBy($this->sql_type)
            ->groupBy($this->sql_type)
            ->orderBy('nums desc')
            ->asArray()
            ->all();

        return $data;
    }

    /**
     * 获取用户组数据
     * @return array
     */
    public function getDataByGroup()
    {
        $this->sql_type = 'group_id';
        $sta = strtotime($this->start_time);
        $end = strtotime($this->stop_time);
        $rs = $this->getPayData($sta, $end);

        return $rs;
    }

    /**
     * 获取用户组支付情况
     * @param $sta
     * @param $end
     * @return array
     */
    public function getPayData($sta, $end)
    {
        $rs = [];
        try {
            $end = $end + 86399;
            $query = self::find()->select([$this->sql_type, 'sum(pay_num) nums'])->where(['between', 'create_at', $sta, $end]);
            if (!empty($this->group_id)) {
                $ids = explode(',', $this->group_id);
                $groups = SrunJiegou::getNodeId($ids);
                $query->andWhere(['group_id' => $groups]);
                if (!$this->flag) {
                    $query->andWhere(['mgr_name' => $this->can_mgr]); //可管理管理员
                }
            } else {
                if (!$this->flag && !array_key_exists('1', $this->can_group)) {
                    $query->andWhere(['group_id' => array_keys($this->can_group)]); //可管理用户组
                    $query->andWhere(['mgr_name' => $this->can_mgr]); //可管理管理员
                }
            }
            if (!empty($this->data_source)) {
                if ($this->data_source == 'users') {
                    //获取用户缴费
                    $query->andWhere('user_name = mgr_name');
                    $query->andWhere(['not in', 'mgr_name', $this->can_mgr]);
                } else if ($this->data_source == 'system') {
                    //获取管理员缴费
                    $query->andWhere(['in', 'mgr_name', $this->can_mgr]);
                }
            }
            $data = $query->groupBy('group_id')->indexBy('group_id')->asArray()->all();
            if (empty($data)) {
                $rs = ['code' => 404, 'msg' => Yii::t('app', 'no record')];
            } else {
                $res = $names = [];
                foreach ($data as $id => $val) {

                    $names[$id] = $this->can_group[$id];
                    $res[$id] = sprintf('%.2f', $val['nums']);
                }

                $name = Yii::t("app", "report/financial/usergroup");
                $series = Parent::getPieSeries($name, $names, $res, ['55%', '65%']);
                $json = json_encode($series['series'], JSON_UNESCAPED_UNICODE);
                $legends = $series['legends'];

                return [
                    'data' => [
                        'legends' => json_encode($legends, JSON_UNESCAPED_UNICODE),
                        'series' => $json
                    ],
                    'table' => $data
                ];
            }
        } catch (\Exception $e) {
            $rs = ['code' => 500, 'msg' => '获取用户组支付情况bug'];
        }

        return $rs;

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
        $fromSerires = $toSeries = [];
        foreach ($names as $id => $val) {
            $nameses = $id . ":" . $val;

            $from_num = isset($data['from'][$id]) ? $data['from'][$id] : 0;
            // var_dump(isset($data['from'][$id]), $data['from'][$id]);
            $to_num = isset($data['to'][$id]) ? $data['to'][$id] : 0;
            $legends[] = $nameses;
            $from_obj = new \stdClass();
            $from_obj->name = $nameses;
            $to_obj = new \stdClass();
            $to_obj->name = $nameses;
            $from_obj->value = $from_num;
            $to_obj->value = $to_num;

            $fromSerires[] = $from_obj;
            $toSeries[] = $to_obj;
        }
        // var_dump($fromSerires, $toSeries);exit;

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
        $series = [];
        //总的外围包装器
        $series[0] = new \stdClass();
        $series[0]->name = $name;
        $series[0]->type = 'pie';
        $series[0]->center = ['25%', '50%'];
        $series[0]->radius = [30, 100];
        $series[0]->roseType = 'roseType';
        $series[0]->data = $fromSerires;
        $series[0]->label = $label;
        //总的外围包装器
        $series[1] = new \stdClass();
        $series[1]->name = '产品转出统计';
        $series[1]->type = 'pie';
        $series[1]->roseType = 'area';
        $series[1]->radius = [30, 100];
        $series[1]->center = ['75%', '50%'];
        $series[1]->data = $toSeries;
        $series[1]->label = $label;
        //var_dump($series);exit;
        return ['series' => $series, 'legends' => $legends];
    }

    /**
     * 导出费用明细
     * @param type
     * @return array
     */
    public function exportData($type)
    {
        $rs = [];
        if ($type == 'group') {
            $name = $this->can_group[$this->product_id];
            return  $this->exportGroupData($name);
        } else {
            $name = $this->getProName($this->product_id);
        }

        try {
            $sta = strtotime($this->start_time);
            $end = strtotime($this->stop_time) + 86399;
            $data = TransferBalance::find()
                ->select('*')
                ->where(['type' => [0, 1]])
                ->andWhere(['between', 'create_at', $sta, $end])
                ->andWhere(['product_id' => $this->product_id])
                ->asArray()
                ->all();
            if (empty($data)) {
                $rs = ['code' => 404, 'msg' => Yii::t('app', 'no data')];
            } else {
                $excelData = [];
                $excelData[0] = [
                    Yii::t('app', 'user name from'), Yii::t('app', 'account'), Yii::t('app', 'amount'), Yii::t('app', 'transfer'), Yii::t('app', 'user products id'), Yii::t('app', 'user time')
                ];
                $from = $to = 0;
                foreach ($data as $k => $v) {
                    $do = $v['type'] == 0 ? Yii::t('app', 'transfer from') : Yii::t('app', 'transfer to');
                    if ($v['type'] == 0) {
                        $from += $v['transfer_num'];
                    } else {
                        $to += $v['transfer_num'];
                    }
                    $excelData[] = [
                        $v['user_name_from'], $v['user_name_to'], $v['transfer_num'], $name, $do, date('Y-m-d H:i', $v['create_at'])
                    ];
                }
                $excelData[] = [Yii::t('app', 'total report', Yii::t('app', 'transfer from')) . ':' . $from, Yii::t('app', 'transfer to') . ':' . $to, $name, '', $this->start_time . '-' . $this->stop_time];
                $title = Yii::t('app', 'Export expense breakdown');
                Excel::header_file($excelData, $title . '.xls', $title);
                exit;
            }
        } catch (\Exception $e) {
            $rs = ['code' => '500', 'msg' => '批量导出费用明细异常：' . $e->getMessage()];
        }

        return $rs;
    }

    /**
     * 导出用户组明细
     * @param $name
     * @return array
     */
    public function exportGroupData($name)
    {
        $rs = [];
        try {
            $sta = strtotime($this->start_time);
            $end = strtotime($this->stop_time) + 86399;
            $data = self::find()
                ->select('*')
                ->andWhere(['between', 'create_at', $sta, $end])
                ->andWhere(['group_id' => $this->product_id])
                ->asArray()
                ->all();
            if (empty($data)) {
                $rs = ['code' => 404, 'msg' => Yii::t('app', 'no data')];
            } else {
                $excelData = [];
                $excelData[0] = [
                   Yii::t('app', 'account'), Yii::t('app', 'amount'), Yii::t('app', 'group id'), Yii::t('app', 'user time')
                ];
                $total = $to = 0;
                foreach ($data as $k => $v) {
                    $total += $v['pay_num'];
                    $excelData[] = [
                        $v['user_name'], $v['pay_num'], $name, date('Y-m-d H:i', $v['create_at'])
                    ];
                }
                $excelData[] = [Yii::t('app', 'total report', Yii::t('app', 'total amount')) . ':' . $total,$name, $this->start_time . '-' . $this->stop_time];
                $title = Yii::t('app', 'Export expense breakdown');
                Excel::header_file($excelData, $title .'-'.$name. '.xls', $title);
                exit;
            }
        } catch (\Exception $e) {
            $rs = ['code' => '500', 'msg' => '批量导出费用明细异常：' . $e->getMessage()];
        }

        return $rs;
    }

}