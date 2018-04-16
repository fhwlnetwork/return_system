<?php

namespace center\modules\report\models;


use Yii;
use yii\db\Query;
use center\extend\Tool;
use center\modules\strategy\models\Control;
use center\modules\report\models\base\BaseModel;

/**
 * This is the model class for table "online_report_control".
 *
 * @property integer $report_id
 * @property integer $time_point
 * @property integer $control_id
 * @property integer $count
 * @property integer $bytes_in
 * @property integer $bytes_out
 * @property integer $time_long
 */
class OnlineReportControl extends BaseModel
{
    public $base = 'control_id';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'online_report_control';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['start_At', 'stop_At'], 'required'],
            [['start_At', 'stop_At', 'unit', 'type'], 'string'],
            ['step', 'integer', 'min' => 1],
            //[['time_point', 'control_id', 'count', 'bytes_in', 'bytes_out', 'time_long'], 'required'],
            [['time_point', 'control_id', 'count', 'bytes_in', 'bytes_out', 'time_long'], 'integer']
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
            'control_id' => 'Control ID',
            'count' => 'Count',
            'bytes_in' => 'Bytes In',
            'bytes_out' => 'Bytes Out',
            'time_long' => 'Time Long',
            'start_At' => Yii::t('app', 'start time'),
            'stop_At' => Yii::t('app', 'end time')
        ];
    }
}