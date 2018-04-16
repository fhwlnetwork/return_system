<?php

namespace center\models;

use Yii;
use yii\db\Query;

/**
 * This is the model class for table "efficiency_report".
 *
 * @property string $report_id
 * @property integer $time_point
 * @property string $my_ip
 * @property string $proc
 * @property string $start_count
 * @property double $start_response_time
 * @property string $update_count
 * @property double $update_response_time
 * @property string $stop_count
 * @property double $stop_response_time
 * @property string $auth_count
 * @property double $auth_response_time
 * @property string $coa_count
 * @property double $coa_response_time
 * @property string $dm_count
 * @property double $dm_response_time
 */
class EfficiencyReport extends \yii\db\ActiveRecord
{
    public $selectFields = ['my_ip', 'proc', 'max(start_response_time) start_response_time', 'max(update_response_time) update_response_time',
        'max(stop_response_time) stop_response_time', 'max(auth_response_time) auth_response_time', 'max(coa_response_time) coa_response_time',
        'max(dm_response_time) dm_response_time', 'sum(start_count) start_count', 'sum(update_count) update_count', 'sum(stop_count) stop_count',
        'sum(auth_count) auth_count', 'sum(coa_count) coa_count', 'sum(dm_count) dm_count'
    ];
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'efficiency_report';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['time_point', 'my_ip', 'proc', 'start_count', 'start_response_time', 'update_count', 'update_response_time', 'stop_count', 'stop_response_time', 'auth_count', 'auth_response_time', 'coa_count', 'coa_response_time', 'dm_count', 'dm_response_time'], 'required'],
            [['time_point', 'start_count', 'update_count', 'stop_count', 'auth_count', 'coa_count', 'dm_count'], 'integer'],
            [['start_response_time', 'update_response_time', 'stop_response_time', 'auth_response_time', 'coa_response_time', 'dm_response_time'], 'number'],
            [['my_ip'], 'string', 'max' => 16],
            [['proc'], 'string', 'max' => 32]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'report_id' => 'Report ID',
            'time_point' => 'Time Point',
            'my_ip' => 'My Ip',
            'proc' => 'Proc',
            'start_count' => 'Start Count',
            'start_response_time' => 'Start Response Time',
            'update_count' => 'Update Count',
            'update_response_time' => 'Update Response Time',
            'stop_count' => 'Stop Count',
            'stop_response_time' => 'Stop Response Time',
            'auth_count' => 'Auth Count',
            'auth_response_time' => 'Auth Response Time',
            'coa_count' => 'Coa Count',
            'coa_response_time' => 'Coa Response Time',
            'dm_count' => 'Dm Count',
            'dm_response_time' => 'Dm Response Time',
        ];
    }

    /**
     * 获取数据
     * @param $beginTime
     * @param $endTime
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getData($beginTime, $endTime)
    {
        $data = self::find()
            ->select($this->selectFields)
            ->where('time_point >= :sta and time_point < :end', [
                ':sta' => $beginTime,
                ':end' => $endTime
            ])
            ->groupBy('my_ip, proc')
            ->asArray()
            ->all();

        return $data;
    }

    /**
     * 获取小时表数据
     * @param $beginTime
     * @param $endTime
     * @return array
     */
    public function getHourData($beginTime, $endTime)
    {
        $data = (new Query())
            ->select($this->selectFields)
            ->where('date >= :sta and date < :end', [
                ':sta' => $beginTime,
                ':end' => $endTime
            ])
            ->groupBy('my_ip, proc')
            ->from('efficiency_report_hour')
            ->all();

        return $data;
    }
}
