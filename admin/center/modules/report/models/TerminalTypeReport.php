<?php

namespace center\modules\report\models;

use Yii;
use common\extend\Tool;

/**
 * This is the model class for table "terminal_type_report".
 *
 * @property integer $id
 * @property integer $time
 * @property string $contect
 */
class TerminalTypeReport extends \yii\db\ActiveRecord
{
    public $start_At; //开始时间
    public $stop_At; //截止时间
    public $step; //步长
    public $unit; //时间修饰词

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'terminal_type_report';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['start_At', 'stop_At'], 'required'],
            [['time'], 'integer'],
            ['step', 'integer', 'min' => 1],
            [['start_At', 'stop_At', 'unit'], 'string'],
        ];

        /*return [
            [['time', 'contect'], 'required'],
            [['time'], 'integer'],
            [['contect'], 'string', 'max' => 100]
        ];*/
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'time' => 'Time',
            //'online_number' => 'Online Number',
            'start_At' => Yii::t('app', 'start time'),
            'stop_At' => Yii::t('app', 'stop time'),
            'step' => Yii::t('app', 'OnlineSummaryReport_model-step'),
            'unit' => Yii::t('app', 'OnlineSummaryReport_model-unit')
        ];
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

    public function terminal($params)
    {
        //单位和步长都不为空
        if (empty($params->unit) || empty($params->step)) {
            $msg = ['code' => 0, 'msg' => Yii::t('app', 'report_terminaltype_font1')];
            return $msg;
        }

        $unit = $params->unit; //单位  比如 分钟 小时  天  月 年
        $step = $params->step; //步长  比如一分钟  一小时 一天 一月 一年
        $start_At = strtotime($params->start_At);
        $stop_At = strtotime($params->stop_At);

        //对输入的时间进行处理  结束时间小于开始时间返回错误.
        if (($start_At == $stop_At) || ($start_At > $stop_At)) {
            $msg = ['code' => 0, 'msg' => Yii::t('app', 'report_terminaltype_font2')];
            return $msg;
        }

        //对输入的时间进行切分 比如 10：00 10：05 10：10 10：15 这样子.
        $tool = new Tool();
        $xAxis = $tool->substrTime($start_At, $stop_At, $unit, $step);
        $unitDate = $tool->getUnitDate($unit);

        //为图表 x y轴数据做准备
        foreach ($xAxis as $val) {
            $xAxisData[] = "'" . date('n-d', $val) . "'";
            $yAxis[] = $this->find()->select('contect')->where(['>=', 'time', $val])->andWhere(['<', 'time', $val+$unitDate])->asArray()->all();
        }
        array_pop($xAxisData);
        $xAxisString = implode(',', $xAxisData);
        //return $xAxisString;
        if (!empty($yAxis)) {
            foreach ($yAxis as $key=>$value) {
                if (is_array($value)) {
                    foreach ($value as $val) {
                        $dataArr = (array)json_decode($val['contect']);

                        //对每个终端按着输入的时间单位进行统计
                        $yAxisData['pcArr'][$key][] = $dataArr['pc'];
                        $yAxisData['mobileArr'][$key][] = $dataArr['mobile'];
                        $yAxisData['otherArr'][$key][] = $dataArr['other'];

                        //统计总数
                        $yAxisData['pc'][] = $dataArr['pc'];
                        $yAxisData['mobile'][] = $dataArr['mobile'];
                        $yAxisData['other'][] = $dataArr['other'];
                    }
                }
            }
        } else {
            $yAxisData = '';
        }

        //return $yAxisData;


        //平均值
        $unit_average = $unitDate / 60;

        $source = [
            'xAxis' => $xAxisString,
            /*'yAxis' => [
                'pc' => ,
                'mobile' => ,
                'other' => ,
            ceil(array_sum($yAxisData['pc']) / $unit_average)
            ],*/
            'totle' => [
                'pc' => ceil(array_sum($yAxisData['pc']) / $unit_average),
                'mobile' => ceil(array_sum($yAxisData['mobile']) / $unit_average),
                'other' => ceil(array_sum($yAxisData['other']) / $unit_average),
            ],
        ];
        return $source;
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
            $this->addError($this->unit,  Yii::t('app', 'report operate remind16'));
            return false;
        }

        return true;
    }
}
