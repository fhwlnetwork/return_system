<?php

namespace center\modules\report\models;

use center\modules\strategy\models\Product;
use center\modules\user\models\Operator;
use center\modules\user\models\Users;
use common\models\User;
use Yii;
use center\extend\Tool;
use yii\db\Query;

/**
 * This is the model class for table "srun_detail_day".
 *
 * @property integer $detail_day_id
 * @property string $user_name
 * @property integer $record_day
 * @property integer $bytes_in
 * @property integer $bytes_out
 * @property integer $bytes_in6
 * @property integer $bytes_out6
 * @property integer $products_id
 * @property integer $billing_id
 * @property integer $control_id
 * @property double $user_balance
 * @property integer $total_bytes
 * @property integer $time_long
 * @property double $user_login_count
 * @property integer $user_group_id
 */
class SrunProductDetail extends \yii\db\ActiveRecord
{
    public $start_At; //开始时间
    public $stop_At; //截止时间
    public $step; //步长
    public $unit; //时间修饰词
    public $bytes_mb = 1048576; //流量进位 MB
    public $bytes_gb = 1073741824; //流量进位 GB
    public $bytes_limit; //总流量限制


    /**
     * 获取总流量限制
     * */
    public function getBytesLimit()
    {
        return $this->bytes_limit;
    }

    /**
     *设置总流量限制
     * */
    public function setBytesLimit($limit)
    {
        $this->bytes_limit = $limit;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'srun_detail_day';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['start_At'], 'required'],
            [['record_day', 'bytes_in', 'bytes_out', 'bytes_in6', 'bytes_out6', 'products_id', 'billing_id', 'control_id', 'total_bytes', 'time_long'], 'integer'],
            [['user_balance', 'user_login_count'], 'number'],
            [['step', 'unit', 'user_group_id', 'bytes_limit'], 'safe'],
            [['user_name', 'step', 'start_At', 'stop_At', 'unit'], 'string', 'max' => 64],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_name' => 'User Name',
            'record_day' => 'Record Day',
            'total_bytes' => 'Total Bytes',
            'time_long' => 'Time Long',
            'start_At' => Yii::t('app', 'start time'),
            'stop_At' => Yii::t('app', 'end time')
        ];
    }


    public function attributeShowLabels()
    {
        return [
            'products_id' => Yii::t('app', 'products name'),
            'usercount' => Yii::t('app', 'use count'),
            'total_bytes' => Yii::t('app', 'total bytes'),
            'user_login_count' => Yii::t('app', 'time count'),
            'time_long' => Yii::t('app', 'time long'),
        ];
    }


    //验证输入时间的合理性以及时间不长的合理性
    public function validateField()
    {
        $start_At = strtotime($this->start_At); //开始时间
        if ($start_At > time()) {
            $this->addError($this->start_At, Yii::t('app', 'end time error'));
            return false;
        }
        return true;
    }

    //查询明细
    public function getDetailData($pid, $start_At, $bytesLimit)
    {
        $start_At = strtotime($start_At);
        $BeginDate = date('Y-m-02', $start_At);  //每月第一天
        $BeginDate2 = date('Y-m-01', $start_At);  //每月第一天
        $EndingDate = date('Y-m-d 23:59:59', strtotime("$BeginDate2 +1 month"));    //每月最后一天
        $bytesLimit = $bytesLimit * 1024 * 1024;
        $Begin = strtotime($BeginDate);
        $Ending = strtotime($EndingDate);

        $query = new Query();
        $query->from($this->tableName());
        $query->select(['products_id', 'user_name', 'sum(user_login_count) as user_login_count', 'sum(total_bytes) as total_bytes', 'sum(time_long) as time_long']);
        $query->where(['>=', 'record_day', $Begin]);
        $query->andWhere(['<=', 'record_day', $Ending]);
        $query->andWhere(['=', 'products_id', $pid]);
        $query->andWhere(['>=', 'total_bytes', $bytesLimit]);
        $query->groupBy('user_name');
        $query->orderby('detail_day_id');
        $yAxisAllData = $query->all();
        //从user表中获取用户的信息
        $userQuery = new Query();
        $userQuery->select(['user_name', 'user_real_name', 'cert_num']);
        $userQuery->from(Users::tableName());
        $userQuery->indexBy('user_name');
        $userResult = $userQuery->all();
        $resultArray = array();
        $product = new Product();
        $product = $product->getProOne($pid);
        if (!empty($product)) {
            foreach ($yAxisAllData as $key => $value) {
                $temp['products_name'] = $product['products_name'];
                $temp['user_name'] = $value['user_name'];
                $temp['user_real_name'] = $userResult[$value['user_name']]['user_real_name'];
                $temp['cert_num'] = $userResult[$value['user_name']]['cert_num'];
                $temp['total_bytes'] = Tool::bytes_format($value['total_bytes']);
                $temp['user_login_count'] = $value['user_login_count'] . Yii::t('app', 'report operate remind20');
                $temp['time_long'] = $this->seconds_format($value['time_long']);
                $temp = array_values($temp);
                $resultArray[] = $temp;
                unset($temp);
            }
        }
        return $resultArray;
    }

    //统计数据
    public function getCountData($model, $fieldArray)
    {
        $start_At = strtotime($model->start_At);
        $begin = date('Y-m-01', $start_At);  //每月第一天
        $end = date('Y-m-d', strtotime("$begin +1 month -1 day"));    //每月最后一天
        $bytesLimit = $this->getBytesLimit();

        $result = array();

        $product_ids = array_keys($fieldArray);
        $bytesLimit = $bytesLimit * 1024 * 1024;
        $arr = array();
        $sta = strtotime($begin);
        $endTime = strtotime($end);
        $data = $this->getData($product_ids, $sta, $endTime, $bytesLimit, 'products_id');
        if (!empty($data)) {
            foreach ($fieldArray as $k => $v) {
                if (isset($data[$k])) {
                    $value['products_name'] = $v;
                    $value['usercount'] = $data[$k]['user_count'];
                    $value['total_bytes'] = Tool::bytes_format($data[$k]['totals']);
                    $value['user_login_count'] = $data[$k]['user_login_count'] . Yii::t('app', 'report operate remind20');
                    $value['time_long'] = $this->seconds_format($data[$k]['time_long']);
                } else {
                    $value['products_name'] = $v;
                    $value['usercount'] = 0;
                    $value['total_bytes'] = 0;
                    $value['user_login_count'] = 0;
                    $value['time_long'] = 0;
                }
                $value['products_id'] = $k;
                $arr[] = $value;
            }
        }

        $result['BeginDate'] = $begin;
        $result['EndingDate'] = $end;
        $result['arr'] = $arr;
        return $result;

    }


    /**
     * 获取产品明细
     * @param $ids
     * @param $sta
     * @param $end
     * @param $bytes
     * @param $group
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getData($ids, $sta, $end, $bytes, $group)
    {
        $data = self::find()
            ->select(['COUNT(distinct user_name) as user_count', 'products_id', 'user_name', 'SUM(total_bytes) as totals', 'SUM(user_login_count) as user_login_count', 'SUM(time_long) as time_long'])
            ->where(['between', 'record_day', $sta, $end])
            ->andWhere([$group => $ids])
            ->indexBy($group)
            ->groupBy($group)
            ->having(['>', 'SUM(total_bytes)', $bytes])
            ->asArray()
            ->all();

        return $data;
    }

    //查询数据
    public function finddata($v, $Begin, $Ending, $bytes)
    {
        $query = new Query();
        $query->from($this->tableName());
        $query->select(['products_id', 'user_name', 'SUM(total_bytes) as total_bytes', 'SUM(user_login_count) as user_login_count', 'SUM(time_long) as time_long']);
        $query->where(['>=', 'record_day', $Begin]);
        $query->andWhere(['<=', 'record_day', $Ending]);
        $query->andWhere(['=', 'products_id', $v]);
        $query->having(['>', 'SUM(total_bytes)', $bytes]); //每个人的流量限制
        $query->groupBy(['user_name']);
        $faterQuery = new \yii\db\Query();
        $faterQuery->select(['products_id', 'COUNT(distinct user_name) as user_count', 'SUM(total_bytes) as total_bytes', 'SUM(user_login_count) as  user_login_count', 'SUM(time_long) as time_long']);
        $faterQuery->from(['a' => $query]);
        $yAxisAllData = $faterQuery->all();
        $resultArray = array();
        $product = new Product();
        $product = $product->getProOne($v);
        if (!empty($product)) {
            $productname = $product['products_name'];
            foreach ($yAxisAllData as $key => $value) {
                if ($value['user_count'] > 0) {
                    $value['products_name'] = $productname;
                    $value['usercount'] = $value['user_count'];
                    $value['total_bytes'] = Tool::bytes_format($value['total_bytes']);
                    $value['user_login_count'] = $value['user_login_count'] . Yii::t('app', 'report operate remind20');
                    $value['time_long'] = $this->seconds_format($value['time_long']);
                    $resultArray[] = $value;
                }
            }
        }
        return $resultArray;
    }


    //统计每个产品下的用户人数
    public function count_User($productid, $Begin, $Ending)
    {
        $query = new Query();
        $query->from($this->tableName());
        $query->select('user_name')->distinct();
        $query->where(['>=', 'record_day', $Begin]);
        $query->andWhere(['<=', 'record_day', $Ending]);
        $query->andWhere(['=', 'products_id', $productid]);
        $yAxisAllData = $query->count();
        return $yAxisAllData;
    }

    public static function seconds_format($second)
    {
        $h = floor($second / 3600);
        $m = floor(($second % 3600) / 60);
        $s = floor(($second % 3600) % 60);
        $out = "";
        if ($h > 0) {
            if ($m > 0) {
                $min = $m . Yii::t('app', 'minutes');
            } else {
                $min = '';
            }
            if ($s > 0) {
                $sec = $s . Yii::t('app', 'seconds');
            } else {
                $sec = '';
            }
            $out = number_format($h, 0) . Yii::t('app', 'hours') . $min . $sec;
        } else if ($m > 0) {
            if ($s > 0) {
                $sec = $s . Yii::t('app', 'seconds');
            } else {
                $sec = '';
            }
            $out = $m . Yii::t('app', 'minutes') . $sec;
        } else {
            $out = $s . Yii::t('app', 'seconds');
        }
        return $out;
    }


}
