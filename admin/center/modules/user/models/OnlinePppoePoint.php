<?php

namespace center\modules\user\models;

use common\extend\Tool;
use Yii;

/**
 * This is the model class for table "online_pppoe_point".
 *
 * @property integer $time_point
 * @property integer $count
 * @property string $service_name
 */
class OnlinePppoePoint extends \yii\db\ActiveRecord
{

    public $start_At;
    public $stop_At;
    public $step;
    public $unit;
    private static $_instance;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'online_pppoe_point';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['time_point', 'count', 'service_name'], 'required'],
            [['time_point', 'count'], 'integer'],
            [['service_name'], 'string', 'max' => 64],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'time_point' => 'Time Point',
            'count' => 'Count',
            'service_name' => 'Service Name',
        ];
    }

    public static function getInstance()
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    /**
     * 获取运营商在线数
     */
    public function getOnlineByService($params){
        $unit = $params->unit;
        $step = $params->step;
        $start_At = strtotime($params->start_At);
        $stop_At = strtotime($params->stop_At);
        $service_name = $params->service_name;

        if(empty($start_At)){
            $unit = 'hours';
            $step = 1;
            $start_At = strtotime(date('Y-m-d 00:00'));
            $stop_At = strtotime(date('Y-m-d H:00',strtotime('+1 hours')));
        }

        $tool = new Tool();
        $xAxis = $tool->substrTime($start_At, $stop_At, $unit, $step);

        $xAxistime = $yAxisData = [];
        for ($i = 0; $i < count($xAxis)-1; $i++) {
            $data = self::find()->select(['time_point', 'max(count) as count'])->where(['service_name'=>$service_name])->andWhere(['>', 'time_point', $xAxis[$i]])->andWhere(['<=', 'time_point', $xAxis[$i+1]])->asArray()->one();
            if(!is_null($data['time_point']) && !is_null($data['count'])){
                $xAxistime[] = $data['time_point'] ? $data['time_point'] : 0;
                $yAxisData[] = $data['count'];
            }else{
                $xAxistime[] = $xAxis[$i];
                $yAxisData[] = 0;
            }
        }

        $yAxisString = implode(',', $yAxisData);

        $source = [
            'xAxis' => $tool->formatTime($unit, $xAxistime),
            'yAxis' => $yAxisString,
        ];
        return $source;
    }
}
