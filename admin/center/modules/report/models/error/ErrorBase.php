<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2017/5/17
 * Time: 10:11
 */

namespace center\modules\report\models\error;

use yii;
use yii\db\ActiveRecord;

/**
 * 认证错误基础模型
 * Class ErrorBase
 * @package center\modules\report\models\error
 */
class ErrorBase extends ActiveRecord
{
    public $start_time;
    public $stop_time;
    public $timePoint;
    public $realModel;
    public $child = 'day';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'srun_login_log_day';
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['start_time', 'stop_time', 'timePoint',], 'safe'],
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
            'user_name' => 'User Name',
            'err_msg' => 'Err Msg',
            'number' => 'Number',
        ];
    }


    /**
     * 检查
     */
    public function validateField()
    {
        $start_time = strtotime($this->start_time); //开始时间
        $stop_time = strtotime($this->stop_time); //结束时间
        //结束时间不能大于当前的月份
        if (date('m', strtotime($this->stop_time)) > date('m')) {
            $this->addError($this->stop_time, Yii::t('app', 'end time big error'));

            return false;
        }
        //结束时间不能大于当前的时间
        if (strtotime($this->stop_time) > time()) {
            $this->addError($this->stop_time, Yii::t('app', 'end time error'));

            return false;
        }
        if ($stop_time < $start_time) {
            $this->addError($this->stop_time, Yii::t('app', 'end time error'));

            return false;
        }

        return true;
    }

    /**
     * 获取操作模型
     */
    public function getRealModel()
    {
        if ($this->timePoint) {
            $this->setTime($this->timePoint);
        } else {
            if (empty($this->start_time)) {
                $this->setTime(4);
            }
            if ($this->start_time == $this->stop_time) {
                $this->child = 'hour';
                $this->realModel = new ErrorHour();
            } else {
                $this->child = 'day';
                $this->realModel = new ErrorDay();
            }
        }
        $this->realModel->start_time = $this->start_time;
        $this->realModel->stop_time = $this->stop_time;
        $this->realModel->child = $this->child;

        return true;
    }

    /**
     * 设置时间
     * @return bool
     */
    public function setTime($point = 4)
    {
        switch ($point) {
            case 1:
                $this->child = 'hour';
                $this->start_time = date('Y-m-d');
                $this->stop_time = date('Y-m-d');
                $this->realModel = new ErrorHour();
                break;
            case 2:
                $this->child = 'hour';
                $this->start_time = date('Y-m-d', strtotime('-1 days'));
                $this->stop_time = date('Y-m-d', strtotime('-1 days'));
                $this->realModel = new ErrorHour();
                break;
            case 3:
                $this->child = 'day';
                $this->start_time = date('Y-m-d', strtotime('-7 days'));
                $this->stop_time = date('Y-m-d', strtotime('-1 days'));
                $this->realModel = new ErrorDay();
                break;
            case 4:
                $this->child = 'day';
                $this->start_time = date('Y-m-d', strtotime('-30 days'));
                $this->stop_time = date('Y-m-d', strtotime('-1 days'));
                $this->realModel = new ErrorDay();
                break;
        }

        return true;
    }


    /**
     * 获取时间轴
     * @return array
     */
    public function getDate()
    {
        $dates = [];
        if ($this->child == 'hour') {
            if ($this->stop_time == date('Y-m-d')) {
                $hour = date('G', strtotime('-1 hours'));
            } else {
                $hour = 24;
            }
            $dates = array_fill(0, $hour, 1);
            $dates = array_keys($dates);
        } else if ($this->child == 'day') {
            $sta = strtotime($this->start_time);
            $end = strtotime($this->stop_time);
            while ($sta <= $end) {
                $dates[] = $sta;
                $sta += 86400;
            }
        }

        return $dates;
    }
}