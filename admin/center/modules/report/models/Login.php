<?php
/**
 * Created by PhpStorm.
 * User: qk
 * Date: 15-9-2
 * Time: 下午4:18
 */

namespace center\modules\report\models;


use yii\base\Model;
use center\modules\log\models\Login as Logins;
use yii;

class Login extends Model
{
    public $start_At;
    public $stop_At;
    public $err_msg;
    public $sql_type;

    const LIMIT_SEARCH_DAY = 30;

    public function setDefault()
    {
        $this->start_At = date('Y-m-d');
        $this->stop_At = date('Y-m-d');

    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['start_At'], 'required'],
            [['start_At', 'stop_At'], 'string'],
        ];
    }

    //验证输入时间的合理性以及时间不长的合理性
    public function validateField()
    {
        $start_At = strtotime($this->start_At); //开始时间
        $stop_At = strtotime($this->stop_At); //结束时间

        if (!empty($stop_At) && ($stop_At < $start_At)) {
            $this->addError($this->stop_At, Yii::t('app', 'end time error'));
            return false;
        }


        return true;
    }

    /**
     * 获取错误名称
     * @return array
     */
    public static function getLegend()
    {
        $legend = Logins::getAttributesList()['error_message'];
        unset($legend['']);
        return array_values($legend);
    }

    public function getData()
    {
        $errorMsg = Logins::getAttributesList()['error_message'];
        unset($errorMsg['']);
        if ($this->start_At == $this->stop_At) {
            $this->sql_type = 'hour';
        } else {
            $this->sql_type = 'date';
        }
        $start = strtotime($this->start_At);
        $stop = strtotime($this->stop_At) + 86399;

        $data = Logins::find()
            ->select(['err_msg', 'user_name', 'log_time'])
            ->where(['between', 'log_time', $start, $stop])
            ->asArray()
            ->all();

        $rs = ['date' => [], 'msg' => [], 'detail' => [], 'table' => []];
        foreach ($data as $val) {
            $time = ($this->sql_type == 'date') ? date('Y-m-d', $val['log_time']) : date('Y-m-d G a',  $val['log_time']);
            $user = $val['user_name'];
            $err = explode(':', $val['err_msg'])[0];
            $rs['msg'][$err] = isset($rs['msg'][$err]) ? $rs['msg'][$err] + 1 : 1;
            $rs['detail'][$user] = isset($rs['detail'][$user]) ? $rs['detail'][$user] + 1 : 1;
            $rs['date'][$time] = isset($rs['date'][$time]) ? $rs['date'][$time] + 1 : 1;
            $rs['all'] = isset($rs['all']) ? $rs['all'] + 1 : 1;
            $rs['table'][$time][] = $val;
        }
        $date = $this->getDates($start, $stop);
        $dateData = $this->getRsData($rs['date'], $date);
        //var_dump($dateData, $this->sql_type);exit;
        $errDate = $this->getRsData($rs['msg'], $errorMsg, 'msg');
        $userData = $rs['detail'];
        $timeJson = $dateData;
        $userJson = $userData;
        $errJson = $errDate;

        //var_dump($rs, $date, $dateData, $errDate, $userData);
        return [
            'data' => [
                'errJson' => $errJson,
                'timeJson' => $timeJson,
                'userJson' => $userJson,
                'all' => $rs['all']
            ],
            'date' => $date,
            'table' => $rs['date'],
            'detail' => $rs['table'],
            'error' => $errorMsg
        ];
    }


    public function getUsersByErr($params)
    {
        $query = Logins::find()->select(['user_name', 'count(id) as num']);
        if (isset($params['start_At']) && !empty($params['start_At'])) {
            $query->andWhere(['>=', 'log_time', strtotime($params['start_At'])]);
        }
        if (isset($params['stop_At']) && !empty($params['stop_At'])) {
            $query->andWhere(['<=', 'log_time', strtotime($params['stop_At'] . '23:59:59')]);
        }
        if (isset($params['err_msg']) && !empty($params['err_msg'])) {
            $query->andWhere('err_msg like "' . $params['err_msg'] . '%"');
        }
        $query->groupBy('err_msg');
        //分页
        $pagesSize = 10; // 每页条数
        $pages = new yii\data\Pagination([
            'totalCount' => $query->count(),
            'pageSize' => $pagesSize
        ]);

        $data = $query->offset($pages->offset)
            ->limit($pages->limit)
            ->asArray()
            ->all();

        return ['data' => $data, 'page' => $pages];
    }

    /**
     * 获取日期数组
     * @param $sta
     * @param $end
     * @return array
     */
    public function getDates($sta, $end)
    {
        $date = [];
        while ($sta <= $end) {
            $date[] = $sta;

            $sta = ($this->sql_type == 'date') ? $sta + 86400 : $sta + 3600;
        }

        return $date;
    }

    /**
     * 整理数据
     * @param $data
     * @param $dates
     * @param string $type
     * @return array
     */
    public function getRsData($data, $dates, $type = 'date')
    {
        $yAxis = [];
        foreach ($dates as $key => $v) {
            if ($type == 'date') {
                $date = ($this->sql_type == 'date') ? date('Y-m-d', $v) : date('Y-m-d G a', $v);
            } else {
                $date = ($type == 'date') ? date('Y-m-d', $v) : $key;
            }
            $yAxis[Yii::t('app', $date)] = isset($data[$date]) ? $data[$date] : 0;
        }
        return $yAxis;
    }


    /**
     * 打包数据
     * @param $data
     * @return array
     */
    public function getSeriesJson($data)
    {
        $result = [];
    }
} 