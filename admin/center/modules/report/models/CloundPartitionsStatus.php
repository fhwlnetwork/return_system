<?php

namespace center\modules\report\models;

use center\extend\Tool;
use center\modules\user\models\Base;
use Yii;
use yii\data\Pagination;

/**
 * This is the model class for table "clound_partitions_status".
 *
 * @property string $id
 * @property string $device_ip
 * @property string $partition_name
 * @property string $mount_point
 * @property string $total_bytes
 * @property string $free_bytes
 * @property double $used_percent
 * @property string $product_name
 * @property string $time
 */
class CloundPartitionsStatus extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'clound_partitions_status';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['device_ip', 'partition_name', 'mount_point', 'time'], 'required'],
            [['total_bytes', 'free_bytes', 'time'], 'integer'],
            [['used_percent'], 'number'],
            [['device_ip', 'partition_name', 'mount_point'], 'string', 'max' => 64],
            [['product_name'], 'string', 'max' => 50],
            [['time', 'product_name'], 'unique', 'targetAttribute' => ['time', 'product_name'], 'message' => 'The combination of Product Name and Time has already been taken.']
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
            'partition_name' => 'Partition Name',
            'mount_point' => 'Mount Point',
            'total_bytes' => 'Total Bytes',
            'free_bytes' => 'Free Bytes',
            'used_percent' => 'Used Percent',
            'product_name' => 'Product Name',
            'time' => 'Time',
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
            'partition_name' => Yii::t('app', 'partition_name'),
            'mount_point' => Yii::t('app', 'mount point'),
            'total_bytes' => Yii::t('app', 'disk total'),
            'min_free_bytes' => Yii::t('app', 'disk min free'),
            'aver_free_bytes' => Yii::t('app', 'disk aver min free'),
            'used_max_percent' => Yii::t('app', 'max used percent'),
        ], $exFields);

        return $this->_searchField;
    }

    /**
     * 获取实时状态的磁盘使用状态
     * @param $params
     * @return array
     */
    public function getTimeData($params)
    {
        try {
            $offset = isset($params['offset']) && $params['offset'] > 0 ? $params['offset'] : 10;
            //获取云端账户
            $productsQuery = $this->find();
            if (isset($newParams[':prod']) && !empty($newParams[':prod'])) {
                $key = $params['products_key'];
                $productsQuery->andWhere('product_name LIKE :pro', [":pro" => "%$key%"]);
            }
            $productsQuery->groupBy('product_name');
            $count = $productsQuery->count();
            $pagination = new Pagination([
                'defaultPageSize' => $offset,
                'totalCount' => $count,
            ]);
            $products = $productsQuery
                ->select('product_name,device_ip')
                ->offset($pagination->offset)
                ->limit($pagination->limit)
                ->asArray()
                ->all();
            $systemData = [];
            if (!empty($products)) {
                foreach ($products as $k => $v) {
                    $user = Base::findOne(['user_name' => $v['product_name']]);
                    //获取账户的分区使用情况
                    $systemData['table'][$k] = $v;
                    $status = $this->getPartitions($v['product_name'], $params);
                    $systemData['table'][$k]['school_name'] = $user ? $user->user_real_name : $v['product_name'];
                    if ($status !== false) {
                        //var_dump($status);exit;
                        $systemData[$k]['series'] = $status['series'];
                        $systemData['table'][$k]['total_bytes'] = $status['status']['totalBytes'];
                        $systemData['table'][$k]['free_bytes'] =sprintf("%1.2f",$status['status']['free_bytes']);
                        $systemData['table'][$k]['used_percent'] = sprintf("%1.2f", (($status['status']['totalBytes'] - $status['status']['free_bytes'])/ $status['status']['totalBytes'])*100). '%';
                        $systemData[$k]['height'] = $status['status']['height'];
						$systemData[$k]['legend'] = $status['status']['legend'];
                    } else {
                        $systemData[$k]['series'] = [];
                    }
                }
                //var_dump($systemData);exit;
                //exit;

                return ['code' => 200, 'data' => $systemData, 'products_key' => $products, 'count' => $count];
            } else {

                return ['code' => 403, 'error' => Yii::t('app', 'no record')];
            }
        } catch (\Exception $e) {
            return ['code' => '500', 'error' => $e->getMessage()];
        }

    }

    /**
     *  获取当前云端用户的系统分区使用状态
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
            $productsQuery->groupBy('product_name');
            $count = $productsQuery->count();
            $pagination = new Pagination([
                'defaultPageSize' => $offset,
                'totalCount' => $count,
            ]);
            $products = $productsQuery
                ->select('distinct(product_name) product_name')
                ->offset($pagination->offset)
                ->limit($pagination->limit)
                ->indexBy('product_name')
                ->asArray()
                ->all();
            $systemData = [];
            if (!empty($products)) {
                $query = self::find();
                $query->select(
                    [
                        'device_ip',
                        'product_name',
                        'device_ip',
                        'mount_point',
                        'partition_name',
                        'total_bytes',
                        'max(free_bytes) as free_bytes',
                        'max(used_percent) as used_percent',
                        'time'
                    ]
                );
                $query->where(['>=', 'time', $newParams[':sta']]);
                $query->andWhere(['<=', 'time', $newParams[':end']]);
                $query->andWhere(['product_name' => array_keys($products)]);
                $query->groupBy(['product_name', 'device_ip', 'mount_point', 'time']);
                $result = $query->orderBy('time asc')->asArray()->all();

                //exit;

                return ['code' => 200, 'data' => $systemData, 'products_key' => $products, 'count' => $count];
            } else {

                return ['code' => 403, 'error' => Yii::t('app', 'no record')];
            }
        } catch (\Exception $e) {
            return ['code' => '500', 'error' => $e->getMessage()];
        }
    }

    /**
     *  获取单个用户的所有分区使用情况
     * @param $productKey
     * @param $params
     * @return array|bool
     */
    public function getPartitions($productKey, $params)
    {
        if (isset($params['action'])) {
            $timeSta = time() -3600; //过去一天
            $timeEnd = time();
        } else {
            $timeSta = $params[':sta'];
            $timeEnd = $params[':end'];
        }

        $query = $this->find()
            ->where("product_name = '{$productKey}'")
             ->andwhere("time <= '{$timeEnd}' AND time >= '{$timeSta}'")
            ->groupBy('device_ip,partition_name')
            ->select('partition_name,device_ip,mount_point,total_bytes,min(free_bytes) as min_free_bytes,max(used_percent) as max_used_percent,sum(free_bytes)/count(id) aver_free_bytes');


        $partitions = $query->asArray()->all();

        if (!isset($params['action'])) {
            //对输入的时间进行切分 比如 10：00 10：05 10：10 10：15 这样子.
            $tool = new Tool();
            if ($timeEnd - $timeSta > 86400) {
                $newParams['end_time'] = date('Y-m-d H:i:s', $timeEnd);
                $newParams['end_time'] = strtotime(substr($timeEnd, 0, 10));
                $unit = 'days';
                $step = 1;
            } else {
                $unit = 'minutes';
                $step = 5;
            }
            $xAxis = $tool->substrTime($timeSta, $timeEnd, $unit, $step);
            $totalBytes = $freeBytes = 0;
            if (!empty($partitions)) {
                foreach ($partitions as $k => $v) {
                    $yAxis = $xAxistime = [];
                    $totalBytes += $v['total_bytes'];
                    $freeBytes += $v['aver_free_bytes'];
                    $partitions[$k]['used_max_bytes'] = $v['total_bytes'] - $v['min_free_bytes'];
                    $partitions[$k]['max_used_percent'] = sprintf("%1.2f",   $partitions[$k]['used_max_bytes']/$v['total_bytes'])*100 .'%';
                    $query = $this->find();
                    for ($i = 0; $i <= count($xAxis) - 1; $i++) {
                        if ($unit == 'days') {
                            $query->andWhere("time >= :sta AND time <= :end AND partition_name =:par AND device_ip=:dev", [':sta' => $xAxis[$i], ':end' => $xAxis[$i] + 86400, ':par'=>$v['partition_name'], ':dev'=>$v['device_ip']]);
                        } else {
                            $query->andWhere("time >= :sta AND time <= :end AND partition_name =:par AND device_ip=:dev", [':sta' => $xAxis[$i], ':end' => $xAxis[$i] + 5 * 60, ':par'=>$v['partition_name'],  ':dev'=>$v['device_ip']]);
                        }
                        $rs = [];
                        $data = $query->select('time,total_bytes,free_bytes')->asArray()->one();
                        if (count($data) > 0) {
                            $yAxis[] = [
                                'time' => $data['time'],
                                'used_percent' => (($data['total_bytes']-$data['free_bytes'])/$data['total_bytes']) * 100,
                            ];
                        } else {
                            $yAxis[] = [
                                'time' => $xAxis[$i],
                                'used_percent' => 0,
                            ];
                        }

                    }

                    $yAxisData = array();
                    if (!empty($yAxis)) {
                        foreach ($yAxis as $key => $value) {
                            $xAxistime[] = $value['time'];
                            $yAxisData[] = sprintf("%1.2f", $value['used_percent']/ 100);
                        }
                    }
                    $yAxisString = implode(',', $yAxisData);
                    if (empty($xAxistime)) {
                        $xAxistime = $xAxis;
                    }

                    $source = [
                        'xAxis' => $tool->formatTime($unit, $xAxistime),
                        'yAxis' => $yAxisString,
                    ];

                    $partitions[$k]['source'] = $source; //每个分区各个时间段的使用情况
                }
                return ['partitions' => $partitions, 'status' => ['totalBytes' => $totalBytes, 'free_bytes' => $freeBytes]];
            } else {
                return false;
            }
        } else {
            if (!empty($partitions)) {
                $totalBytes = $freeBytes = 0;
                $height = 250;
                $series = $legend = [];
				$total = count($partitions);
                foreach ($partitions as $k => $partition) {
                    if ($total <= 2) {
                        $x = 0.5;
                        $xStep = 0.25;
                        $y = 0.5;
                        $yStep = 0;
                    } else if($total == 3) {
                        $x = 0.4;
                        $xStep = 0.25;
                        $y = 0.5;
                        $yStep = 0;
                    } else if ($total >= 4 && $total <= 8) {
                        $x = 0.4;
                        $y = 0.35;
                        $xPos = 0;
                        $xStep = 0.15;
                        $yStep = 0.4;
                    } else if($total > 8) {
                        $x = 0.4;
                        $y = 0.25;
                        $xPos = 0;
                        $xStep = 0.15;
                        $yStep = 0.3;
                    }


                    if ($k != 0 && $k % 4 ==0) {
                        $height += 250;
                        $x = 0.4;
                        $y += 0.3;
                    }
                    $totalBytes += $partition['total_bytes'];
                    $freeBytes += $partition['aver_free_bytes'];
                    $partitions[$k]['used_max_bytes'] = $partition['total_bytes'] - $partition['min_free_bytes'];
                    $partitions[$k]['max_used_percent'] = sprintf("%1.2f",   $partitions[$k]['used_max_bytes']/$partition['total_bytes'])*100 .'%';
                  $query = $this->find();
                  $query->andWhere("device_ip =:dev and partition_name = :par and time <= :end and time >= :sta", [
                        ':dev' => $partition['device_ip'],
                        ':par' => $partition['partition_name'],
                        ':end' => $timeEnd,
                        ':sta' => $timeSta,
                    ]);
                    $data = $query->select('time,total_bytes,free_bytes')->asArray()->one();
                    $dis = intval($k % 4);

					$yDis = floor($k / 4);
					if ($k != 0 && $k % 4 ==0) {
						$yDis = $yDis-1;
					}
                    $x = ($x+(($dis) * $xStep)) * 100 . '%';
                    $y = (($y+(($yDis) * $yStep))* 100).'%';
                    $xPos = (($dis) * 0.25 * 100) . '%';
                    if (!is_null($data)) {
						$legend[] = '机器'.$partition['device_ip'].'之分区'.$partition['partition_name'].Yii::t('app', 'max used percent');
                        $series[] = [
                            'type' => 'pie',
                            'center' => [$x, $y],
                            'x' => $xPos,
                            'data' => [
										[
											'name' => '空闲空间',

											'value' => $data['free_bytes']
										],
										[
											'name' => '机器' . $partition['device_ip'] . '之分区' . $partition['partition_name'] . Yii::t('app', 'max used percent'),

											'value' => $data['total_bytes']-$data['free_bytes']
										],


                            ],
                        ];
                    }
                }

                return ['series' => $series, 'status' => ['totalBytes' => $totalBytes, 'free_bytes' => $freeBytes, 'height'=>$height, 'legend'=>$legend]];

            } else {
                return false;
            }
        }
    }
}
