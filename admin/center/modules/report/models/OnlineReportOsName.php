<?php

namespace center\modules\report\models;


use Yii;
use center\extend\Tool;
use center\modules\report\models\base\TerminalBase;

/**
 * This is the model class for table "online_report_os_name".
 *
 * @property integer $report_id
 * @property integer $time_point
 * @property string $os_name
 * @property integer $count
 * @property integer $bytes_in
 * @property integer $bytes_out
 * @property integer $time_long
 */
class OnlineReportOsName extends TerminalBase
{
    public $base = 'os_name';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'online_report_os_name';
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
            //[['time_point', 'os_name', 'count', 'bytes_in', 'bytes_out', 'time_long'], 'required'],
            [['time_point', 'count', 'bytes_in', 'bytes_out', 'time_long'], 'integer'],
            [['os_name'], 'string', 'max' => 64]
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
            'os_name' => 'Class Name',
            'count' => 'Count',
            'bytes_in' => 'Bytes In',
            'bytes_out' => 'Bytes Out',
            'time_long' => 'Time Long',
            'start_At' => Yii::t('app', 'start time'),
            'stop_At' => Yii::t('app', 'end time')
        ];
    }

    //验证输入时间的合理性以及时间不长的合理性
    public function validateField()
    {
        $start_At = strtotime($this->start_At); //开始时间
        $stop_At = strtotime($this->stop_At); //结束时间

        if ($stop_At === $start_At || $stop_At < $start_At) {
            $this->addError($this->stop_At, Yii::t('app', 'end time error'));
        }

        if ($this->step <= 0) {
            $this->addError($this->step, Yii::t('app', 'step error'));
        }

        if (($stop_At - $start_At) < ($this->step * Tool::getTimeDate($this->unit))) {
            $this->addError($this->unit, Yii::t('app', 'report operate remind16'));
            return false;
        }

        return true;
    }
}
