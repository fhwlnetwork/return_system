<?php

namespace center\modules\report\models\error;

use Yii;

/**
 * This is the model class for table "srun_login_log_month".
 *
 * @property string $id
 * @property integer $date
 * @property string $user_name
 * @property string $err_msg
 * @property string $number
 */
class ErrorMonth extends ErrorBase
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'srun_login_log_month';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['date', 'number'], 'integer'],
            [['err_msg'], 'required'],
            [['user_name'], 'string', 'max' => 32],
            [['err_msg'], 'string', 'max' => 256],
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
     * 获取认证错误数据
     * @return array
     */
    public function getData()
    {
        $sta = strtotime($this->start_time);
        $end = strtotime($this->stop_time);
        //最近三十天用户日志
        $dates = $this->getDate();
        $dateData = $this->getBase($sta, $end, 'date');
        $userData = $this->getBase($sta, $end, 'user_name');
        $errData = $this->getBase($sta, $end, 'err_msg');
        $rs = ['timeJson' => [], 'userJson' => [], 'errJson' => []];
        foreach ($dates as $time) {
            $day = date('Y-m-d', $time);
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
            ->select(['sum(number) number', $index])
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
