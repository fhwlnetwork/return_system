<?php

namespace center\modules\report\models;

use center\extend\Tool;
use center\modules\user\models\Base;
use Yii;
use yii\data\Pagination;

/**
 * This is the model class for table "clound_memory_status".
 *
 * @property string $id
 * @property string $device_ip
 * @property string $time
 * @property double $cpu
 * @property double $mem
 * @property double $mem-cahced
 * @property double $loads
 * @property double $proccess
 * @property double $httpd
 * @property string $product_name
 */
class CloundMemoryStatus extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'clound_memory_status';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['device_ip', 'time'], 'required'],
            [['time'], 'integer'],
            [['cpu', 'mem', 'mem-cahced', 'loads', 'proccess', 'httpd'], 'number'],
            [['device_ip'], 'string', 'max' => 64],
            [['product_name'], 'string', 'max' => 50],
            [['time', 'device_ip', 'product_name'], 'unique', 'targetAttribute' => ['time', 'device_ip', 'product_name'], 'message' => 'The combination of Device Ip, Time and Product Name has already been taken.']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'device_ip' => 'Device Ip',
            'time' => 'Time',
            'cpu' => 'Cpu',
            'mem' => 'Mem',
            'mem-cahced' => 'Mem Cahced',
            'loads' => 'Loads',
            'proccess' => 'Proccess',
            'httpd' => 'Httpd',
            'product_name' => 'Product Name',
        ];
    }
    //搜索字段
    private $_searchField = null;
    /**
     * 显示字段
     * @return array|null
     */
    public function getSearchField()
    {
        if (!is_null($this->_searchField)) {
            return $this->_searchField;
        }
        //将扩展字段加入搜索项
        $exFields = [];

        $this->_searchField = \yii\helpers\ArrayHelper::merge([
            'device_ip' => Yii::t('app', 'device ip'),
            'cpu_max' => Yii::t('app', 'cpu max'),
            'mem_max' => Yii::t('app', 'mem max'),
            'mem_cached_max' => Yii::t('app', 'mem-cached max'),
            'loads_max' => Yii::t('app', 'loads max'),
            'process_max' => Yii::t('app', 'process max'),
            'httpd_max' => Yii::t('app', 'httpd max'),
        ], $exFields);

        return $this->_searchField;
    }
    /**
     *  获取当前云端用户的系统使用状态
     * @param $params
     * @return array|string
     */
    public function getAllData($params)
    {
        $newParams = [];
        $newParams[':sta'] = (!empty($params) && !empty($params['start_time'])) ? strtotime($params['start_time']) : time() - 5 * 60;
        $newParams[':end'] = (!empty($params) && !empty($params['end_time'])) ? strtotime($params['end_time']) : time();
        $newParams[':prod'] = (!empty($params) && isset($params['products_key'])) ? $params['products_key'] : '';
        $where = " 1=1";
        if ($newParams[':end'] < $newParams[':sta']) {
            return json_encode(['code' => 401, 'error' => Yii::t('app', 'end time error')]);
        }
        if ($newParams[':end'] - $newParams[':sta'] > 86400 * 31) {//超过一个月
            return json_encode(['code' => 402, 'error' => Yii::t('app', 'time error1')]);
        }

        try {
            $offset = isset($params['offset']) && $params['offset'] > 0 ? $params['offset'] : 10;
            //获取云端账户
            $productsQuery = $this->find();
            if (isset($newParams[':prod']) && !empty($newParams[':prod'])) {
                $key = $params['products_key'];
                $productsQuery->andWhere('product_name LIKE :pro', [":pro" => "%$key%"]);
            }
            if (isset($newParams[':sta']) && !empty($newParams[':sta'])) {
                $sta = $newParams[':sta'];
                $productsQuery->andWhere('time >= :sta', [":sta" => $sta]);
            }
            if (isset($newParams[':end']) && !empty($newParams[':end'])) {
                $end = $newParams[':end'];
                $productsQuery->andWhere('time <= :end', [":end" => $end]);
            }
            $productsQuery->groupBy('product_name');
            $count = $productsQuery->count();
            $pagination = new Pagination([
                'defaultPageSize' => $offset,
                'totalCount' => $count,
            ]);
            $products = $productsQuery
                ->select('max(cpu) cpu_max, max(mem) mem_max, max(`mem-cahced`) mem_cached_max, max(loads) loads_max, max(proccess) process_max, max(httpd) httpd_max,product_name')
                ->offset($pagination->offset)
                ->limit($pagination->limit)
                ->asArray()
                ->all();

            $systemData = [];
            if (!empty($products)) {
                foreach ($products as $k => $v) {
                    $user = Base::findOne(['user_name' => $v['product_name']]);
                    //获取账户的分区使用情况
                    $systemData[$k] = $v;
                    $status = $this->getPartitions($v['product_name'], $newParams);
                    $systemData[$k]['school_name'] = $user ? $user->user_real_name : $v['product_name'];
                    if ($status !== false) {
                        $systemData[$k][$v['product_name']]['partitions'] = $status;
                    } else {
                        $systemData[$k][$v['product_name']]['partitions'] = [];
                    }
                }


                return ['code' => 200, 'data' => $systemData, 'products_key' => $products, 'count' => $count];
            } else {

                return ['code' => 403, 'error' => Yii::t('app', 'no record')];
            }
        } catch (\Exception $e) {
            return ['code' => '500', 'error' => $e->getMessage()];
        }
    }

    /**
     *  获取单个用户的所有服务器使用情况
     * @param $productKey
     * @param $params
     * @return array|bool
     */
    public function getPartitions($productKey, $params)
    {
        $timeSta = $params[':sta'];
        $timeEnd = $params[':end'];
        $partitions = $this->find()
            ->where("product_name = '{$productKey}'")
            ->groupBy('device_ip')
            ->select('product_name,device_ip,max(cpu) cpu_max, max(mem) mem_max, max(`mem-cahced`) mem_cached_max, max(loads) loads_max, max(proccess) process_max, max(httpd) httpd_max')
            ->andwhere("time <= '{$timeEnd}' AND time >= '{$timeSta}'")
            ->asArray()
            ->all();

        //对输入的时间进行切分 比如 10：00 10：05 10：10 10：15 这样子.
        $tool = new Tool();
        if ($timeEnd - $timeSta > 86400) {
            $newParams['end_time'] = date('Y-m-d H:i:s', $timeEnd);
            $newParams['end_time'] = strtotime(substr($timeEnd, 0, 10));
            $unit = 'days';
            $step = 1;
        } elseif ($timeEnd - $timeSta > 3600) {
            $unit = 'minutes';
            $step = 60;
        } else {
            $unit = 'minutes';
            $step = 5;
        }
        $xAxis = $tool->substrTime($timeSta, $timeEnd, $unit, $step);
        $totalBytes = $freeBytes = 0;
        if (!empty($partitions)) {
            foreach ($partitions as $k => $v) {
                $cpuStatus = $memStatus = $memCachedStatus = $xAxistime = $yAxis =[];
                $query = $this->find();
                $query->andWhere('device_ip = :dev', [':dev' => $v['device_ip']]);
                for ($i = 0; $i <= count($xAxis) - 1; $i++) {
                    if ($step == '1') {
                        $query->andWhere("time >= :sta AND time <= :end", [':sta' => $xAxis[$i], ':end' => $xAxis[$i] + 86400,]);
                    } else if($step == 60){
                        $query->andWhere("time >= :sta AND time <= :end ", [':sta' => $xAxis[$i], ':end' => $xAxis[$i] + $step * 60, ]);
                    } else {
                        $query->andWhere("time >= :sta AND time <= :end", [':sta' => $xAxis[$i], ':end' => $xAxis[$i] + 5 * 60, ]);
                    }
                    $rs = [];
                    $data = $query->select('time,max(cpu) cpu_max, max(mem) mem_max, max(`mem-cahced`) mem_cached_max')->asArray()->one();

                    if (count($data) > 0 && !is_null($data['cpu_max'])) {
                        foreach ($data as $m => &$v) {
                            if ($m != 'time') {
                                $v =  sprintf("%1.2f", $v);
                            }
                        }
                        $yAxis[] = $data;

                    } else {
                        $yAxis[] = [
                            'time' => $xAxis[$i],
                        ];
                    }
                    $cpuStatus[] = (count($data) > 0 && !is_null($data['cpu_max'])) ? $data['cpu_max'] : 0.00;
                    $memStatus[] = (count($data) > 0 && !is_null($data['cpu_max'])) ? $data['mem_max'] : 0.00;
                    $memCachedStatus[] = (count($data) > 0 && !is_null($data['cpu_max'])) ? $data['mem_cached_max'] : 0.00;
                }

                $yAxisData = array();
                if (!empty($yAxis)) {
                    foreach ($yAxis as $key => $value) {
                        $xAxistime[] = $value['time'];
                    }
                    $yAxisData = [
                        'cpu' => $cpuStatus,
                        'mem' => $memStatus,
                        'mem_cached' => $memCachedStatus,
                    ];
                }

                if (empty($xAxistime)) {
                    $xAxistime = $xAxis;
                }

                $source = [
                    'xAxis' => $tool->formatTime($unit, $xAxistime),
                    'yAxis' => $yAxisData,
                ];

                $partitions[$k]['source'] = $source; //每个分区各个时间段的使用情况
            }

            return $partitions;
        } else {
            return false;
        }
    }
}
