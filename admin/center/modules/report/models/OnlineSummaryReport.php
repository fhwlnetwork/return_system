<?php

namespace center\modules\report\models;

use common\extend\Tool;
use Yii;

/**
 * This is the model class for table "online_summary_report".
 *
 * @property integer $time
 * @property integer $online_number
 */
class OnlineSummaryReport extends \yii\db\ActiveRecord
{
    public $start_At; //开始时间
    public $stop_At; //截止时间
    public $step; //步长
    public $unit; //时间修饰词
    private static $_instance;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'online_summary_report';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['start_At', 'stop_At'], 'required'],
            [['time', 'online_number'], 'integer'],
            ['step', 'integer', 'min' => 1],
            [['start_At', 'stop_At', 'unit'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'time' => 'Time',
            'online_number' => 'Online Number',
            'start_At' => Yii::t('app', 'start time'),
            'stop_At' => Yii::t('app', 'end time'),
            'step' => Yii::t('app', 'OnlineSummaryReport_model-step'),
            'unit' => Yii::t('app', 'OnlineSummaryReport_model-unit')
        ];
    }

    //单例方法,用于访问实例的公共的静态方法
    public static function getInstance()
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    public function getAttributesList()
    {
        return [
            'step' => [
                1 => '1',
                2 => '2',
                3 => '3',
                4 => '4',
                5 => '5',
                6 => '6',
                7 => '7',
                8 => '8',
                9 => '9',
                10 => '10',
                15 => '15',
                20 => '20',
                25 => '25',
            ],
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
        $tmpDate = date("Y-m-d H:i:00", strtotime("+1 hours")); //时间取整
        if ($stop_At > strtotime($tmpDate)) {
            $this->addError($this->stop_At, Yii::t('app', 'report operate remind19'));
            return false;
        }

        if (($stop_At - $start_At) < ($this->step * Tool::getTimeDate($this->unit))) {
            $this->addError($this->unit, Yii::t('app', 'report operate remind16'));
            return false;
        }

        return true;
    }

    //在线人数报表统计
    public function getOnline($params)
    {
        $unit = $params->unit; //单位  比如 分钟 小时  天  月 年
        $step = $params->step; //步长  比如一分钟  一小时 一天 一月 一年
        $start_At = strtotime($params->start_At);
        $stop_At = strtotime($params->stop_At);

        //对输入的时间进行切分 比如 10：00 10：05 10：10 10：15 这样子.
        $tool = new Tool();
        $xAxis = $tool->substrTime($start_At, $stop_At, $unit, $step);

        //为图表 x 轴数据做准备
        foreach ($xAxis as $val) {
            $xAxisData[] = "'" . date('H:i', $val) . "'";
        }
        $xAxisString = implode(',', $xAxisData);

        /*for ($i = 0; $i < count($xAxis); $i++) {
            $yAxis[] = $this->findAll(['time' => $xAxis[$i]]);
        }

        if (!empty($yAxis)) {
            foreach ($yAxis as $val) {
                if (empty($val)) {
                    $yAxisData[] = 0;
                } else {
                    $yAxisData[] = $val[0]->online_number;
                }
            }
            $yAxisString = implode(',', $yAxisData);
        } else {
            $yAxisString = '';
        }*/

        for ($i = 0; $i < count($xAxis); $i++) {
            $yAxis[] = $this->find()->select('online_number')->where(['time' => $xAxis[$i]])->asArray()->all();
        }

        if (!empty($yAxis)) {
            foreach ($yAxis as $val) {
                if(empty($val)) {
                    $yAxisData[] = 0;
                } else {
                    $yAxisData[] = $val[0]['online_number'];
                }
            }
            $yAxisString = implode(',', $yAxisData);
        } else {
            $yAxisString = '';
        }

        $source = [
            'xAxis' => $xAxisString,
            'yAxis' => $yAxisString,
            'desc' => $params->start_At . '---' . $params->stop_At
        ];
        return $source;
    }
}
