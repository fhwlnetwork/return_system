<?php

namespace center\modules\report\models\error;

use Yii;

/**
 * This is the model class for table "srun_login_log_hour".
 *
 * @property string $id
 * @property integer $date
 * @property integer $hour
 * @property string $user_name
 * @property string $err_msg
 * @property string $number
 */
class ErrorHour extends ErrorBase
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'srun_login_log_hour';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['date', 'hour', 'number'], 'integer'],
            [['user_name', 'err_msg'], 'string', 'max' => 32],
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
            'hour' => 'Hour',
            'user_name' => 'User Name',
            'number' => 'Number',
            'err_msg' => 'err msg'
        ];
    }

    /**
     * 获取认证错误数据
     * @return array
     */
    public function getData()
    {
        $sta = strtotime($this->start_time);
        $end = ($this->start_time == date('Y-m-d')) ? strtotime('-1 hours') :  strtotime($this->stop_time)+86400;
        //最近三十天用户日志
        $dates = $this->getDate();
        $dateData = $this->getBase($sta, $end, 'hour');
        $userData = $this->getBase($sta, $end, 'user_name');
        $errData = $this->getBase($sta, $end, 'err_msg');
        $rs = ['timeJson' => [], 'userJson' => [], 'errJson' => []];
        foreach ($dates as $time) {
            $day = $time.Yii::t('app', 'hours');
            $rs['timeJson'][$day] = isset($dateData[$time]) ? $dateData[$time]['number'] : 0;
        }
        foreach ($userData as $user => $v) {
            $rs['userJson'][$user] = $v['number'];
        }
        foreach ($errData as  $msg => $v) {
            $msg = preg_match('/E/', $msg) ? Yii::t('app', $msg) : $msg;
            $rs['errJson'][$msg] = $v['number'];
        }

        return $rs;
    }

    /**
     * 获取数据
     * @param $sta
     * @param $end
     * @param $index
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getBase($sta, $end, $index)
    {
        $query = self::find()
            ->select(['sum(number) number', $index, 'date'])
            ->where(['between', 'date', $sta, $end])
            ->indexBy($index)
            ->groupBy($index);
        if ($index == 'user_name') {
            //查看top30
            $query->orderBy('number desc')->limit(30);
        }

        $data = $query->asArray()->all();

        return $data;
    }
}
