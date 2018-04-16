<?php

namespace center\modules\report\models;

use Yii;
use center\extend\Tool;
use yii\data\Pagination;
use center\modules\user\models\Base;

/**
 * This is the model class for table "cloud_partitions_day".
 *
 * @property string $id
 * @property string $date
 * @property string $device_ip
 * @property string $partition_name
 * @property string $mount_point
 * @property string $total_bytes
 * @property string $free_bytes
 * @property double $used_percent
 * @property string $product_name
 */
class CloudPartitionsDay extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'cloud_partitions_day';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['date', 'total_bytes', 'free_bytes'], 'integer'],
            [['device_ip', 'partition_name', 'mount_point'], 'required'],
            [['used_percent'], 'number'],
            [['device_ip', 'partition_name', 'mount_point'], 'string', 'max' => 64],
            [['product_name'], 'string', 'max' => 50],
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
            'device_ip' => 'Device Ip',
            'partition_name' => 'Partition Name',
            'mount_point' => 'Mount Point',
            'total_bytes' => 'Total Bytes',
            'free_bytes' => 'Free Bytes',
            'used_percent' => 'Used Percent',
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
            'partition_name' => Yii::t('app', 'partition_name'),
            'mount_point' => Yii::t('app', 'mount point'),
            'total_bytes' => Yii::t('app', 'disk total'),
            'free_bytes' => Yii::t('app', 'disk min free'),
            'used_percent' => Yii::t('app', 'max used percent'),
        ], $exFields);

        return $this->_searchField;
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
                        'date'
                    ]
                );
                $query->where(['>=', 'date', $newParams[':sta']]);
                $query->andWhere(['<=', 'date', $newParams[':end']]);
                $query->andWhere(['product_name' => array_keys($products)]);
                $query->groupBy(['product_name', 'device_ip', 'mount_point', 'date']);
                $result = $query->orderBy('date asc')->asArray()->all();
                if (!empty($result)) {
                    foreach ($result as $k => $v) {
                        $user = Base::findOne(['user_name' => $v['product_name']]);
                        //获取账户的分区使用情况
                        foreach ($v as $key => $val) {
                            if (in_array($key, ['total_bytes', 'free_bytes'])) {
                                if ($v['mount_point'] == '/') {
                                    $systemData['table'][$v['product_name']][$key] = $val;
                                }
                            } else if ($key != 'used_percent') {
                                $systemData['table'][$v['product_name']][$key] = $val;
                            }
                        }
                        $free = $systemData['table'][$v['product_name']]['free_bytes'];
                        $total = $systemData['table'][$v['product_name']]['total_bytes'];
                        $used_percent = ($total - $free) > 0 ? sprintf("%1.2f", (($total - $free) / $total) * 100) : '0.00';
                        $systemData['table'][$v['product_name']]['used_percent'] = $used_percent;
                        //var_dump($v);exit;
                        $systemData['table'][$v['product_name']]['details'][$v['device_ip']][$v['mount_point']] = $v;
                        $systemData['table'][$v['product_name']]['school_name'] = $user ? $user->user_real_name : $v['product_name'];
                        $systemData['data'][$v['product_name']][$v['device_ip']][$v['mount_point']][$v['date']] = sprintf("%1.2f", $v['used_percent']);
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
}
