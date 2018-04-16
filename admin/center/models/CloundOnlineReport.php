<?php

namespace center\models;

use Yii;
use yii\helpers\Json;
use center\modules\setting\models\ExtendsField;
use yii\data\Pagination;
use center\extend\Tool;
use center\modules\user\models\Base;

/**
 * This is the model class for table "clound_online_report".
 *
 * @property integer $time_point
 * @property integer $count
 * @property string $products_key
 */
class CloundOnlineReport extends \yii\db\ActiveRecord
{
    const  TIME_STEP = 5;  //时间间隔
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'clound_online_report';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['time_point', 'count', 'products_key'], 'required'],
            [['time_point', 'count'], 'integer'],
            [['products_key'], 'string', 'max' => 30],
            [['time_point', 'products_key'], 'unique', 'targetAttribute' => ['time_point', 'products_key'], 'message' => 'The combination of Time Point and Products Key has already been taken.']
        ];
    }
    //监控的字段
    private $field = "sum(count)/count(time_point) as user_account";

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'time_point' => 'Time Point',
            'count' => 'Count',
            'products_key' => 'Products Key',
        ];
    }
    public function getAllData($params) {
        $newParams = [];
        $newParams['start_time'] = isset($params['start_time']) ? strtotime($params['start_time']) : time()-30*60;
        $newParams['end_time'] = isset($params['end_time']) ? strtotime($params['end_time']) : time();
        $newParams['product_key'] = isset($params['products_key']) ? $params['products_key'] : '';

        if ($newParams['end_time'] < $newParams['start_time']) {
            return ['code' => 401, 'error' => Yii::t('app', 'end time error')];
        }
        if ($newParams['end_time'] - $newParams['start_time'] > 86400 * 31) {//超过10天
            return ['code' => 402, 'error' => Yii::t('app', 'time error1')];
        }
        //对输入的时间进行切分 比如 10：00 10：05 10：10 10：15 这样子.
        $tool = new Tool();
        if ($newParams['end_time'] - $newParams['start_time'] > 86400) {
            $newParams['end_time'] = date('Y-m-d H:i:s', $newParams['end_time']);
            $newParams['end_time'] = strtotime(substr($newParams['end_time'], 0, 10));
            $unit = 'days';
            $step = 1;
        } else {
            $unit = 'minutes';
            $step = 5;
        }
        $xAxis = $tool->substrTime($newParams['start_time'], $newParams['end_time'], $unit, $step);
        $query = $this->find();
        $key = $newParams['product_key'];
        $query->andWhere('products_key = :pro', [":pro" => "$key"]);
        $query->addGroupBy('products_key');
        $yAxis = [];

        for ($i = 0; $i <= count($xAxis)-1; $i++) {
            if ($unit == 'days') {
                $query->andWhere("time_point > :sta AND time_point <= :end", [':sta' => $xAxis[$i], ':end' => $xAxis[$i] + 86400]);
            } else {
                $query->andWhere("time_point > :sta AND time_point <= :end", [':sta' => $xAxis[$i], ':end' => $xAxis[$i] + self::TIME_STEP * 60]);
            }
            $data = $query->select('time_point,count,'.$this->field)->asArray()->one();
            if(count($data) > 0){
                $yAxis[] = $data;
            } else {
                $yAxis[] = [
                    'time_point' => $xAxis[$i],
                    'count' => 0,
                    'user_account' => 0
                ];
            }

        }

        $yAxisData = array();
        if(!empty($yAxis)){
            foreach ($yAxis as $key=>$value) {
                $xAxistime[]= $value['time_point'];
                $yAxisData[] = ceil($value['user_account']);
            }
        }
        $yAxisString = implode(',', $yAxisData);
        if(empty($xAxistime)){
            $xAxistime = $xAxis;
        }
        $source = [
            'xAxis' => $tool->formatTime($unit, $xAxistime),
            'yAxis' => $yAxisString,
        ];

        return $source;
    }
}
