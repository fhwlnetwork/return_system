<?php

namespace center\modules\report\models;


use Yii;
use center\modules\report\models\base\TerminalBase;

/**
 * This is the model class for table "online_report_class_name".
 *
 * @property integer $report_id
 * @property integer $time_point
 * @property string $class_name
 * @property integer $count
 * @property integer $bytes_in
 * @property integer $bytes_out
 * @property integer $time_long
 */
class OnlineReportClassName extends TerminalBase
{
   public $base = 'class_name';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'online_report_class_name';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['start_At'], 'required'],
            [['stop_At'], 'required'],
            [['start_At', 'stop_At', 'unit', 'type'], 'string'],
            ['step', 'integer', 'min' => 1],
            //[['time_point', 'class_name', 'count', 'bytes_in', 'bytes_out', 'time_long'], 'required'],
            [['time_point', 'count', 'bytes_in', 'bytes_out', 'time_long'], 'integer'],
            [['class_name'], 'string', 'max' => 64]
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
            'class_name' => 'Class Name',
            'count' => 'Count',
            'bytes_in' => 'Bytes In',
            'bytes_out' => 'Bytes Out',
            'time_long' => 'Time Long',
            'start_At' => Yii::t('app', 'start time'),
            'stop_At' => Yii::t('app', 'end time')
        ];
    }
}
