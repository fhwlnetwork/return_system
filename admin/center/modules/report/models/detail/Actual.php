<?php

namespace center\modules\report\models\detail;

use center\modules\auth\models\SrunJiegou;
use center\modules\report\models\Financial;
use center\modules\user\models\Base;
use common\extend\Excel;
use m35\thecsv\theCsv;
use Yii;

/**
 * This is the model class for table "actual_people_num".
 *
 * @property integer $id
 * @property integer $date
 * @property integer $product_id
 * @property integer $group_id
 * @property string $actual_number
 * @property string $user_id
 */
class Actual extends \yii\db\ActiveRecord
{
    public $start_time;
    public $stop_time;
    public $timePoint;
    public $product_list;
    const  EXCEL_EXPORT_LIMIT = 50000;
    const  CSV_EXPORT_LIMIT = 100000;
    public $can_group;


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'actual_people_num';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['start_time', 'stop_time', 'timePoint', 'product_id', 'group_id'], 'safe'],
        ];
    }

    public function init()
    {
        $this->start_time = date('Y-m-d', strtotime('-30 days'));
        $this->stop_time = date('Y-m-d', strtotime('-1 days'));
        $this->product_list = (new Financial())->getProNames();
        $this->can_group = SrunJiegou::canMgrGroupNameList();

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'date' => 'Date',
            'product_id' => 'Product ID',
            'group_id' => 'Group ID',
            'actual_number' => 'Actual Number',
            'user_id' => 'User ID',
        ];
    }

    //验证有效性
    public function validateField()
    {
        $start_time = strtotime($this->start_time); //开始时间
        $stop_time = strtotime($this->stop_time); //结束时间

        if ($stop_time < $start_time) {
            $this->addError($this->stop_time, Yii::t('app', 'end time error'));
            return false;
        }
        if ($stop_time > time()) {
            $this->addError($this->stop_time, Yii::t('app', 'report operate remind19'));
            return false;
        }

        return true;
    }

    /**
     * 获取资源
     * @param $params
     * @return array
     */
    public function getData($params)
    {
        $rs = [];
        try {
            if (!empty($params)) {
                if (!($this->load($params) && $this->validateField())) {
                    $rs = ['code' => 403];

                    return $rs;
                }
                if (!empty($this->timePoint)) {
                    $this->setTime($this->timePoint);
                }
            }
            $rs = $this->getSource();
        } catch (\Exception $e) {
            $rs = ['code' => 500, 'msg' => '发生异常:' . $e->getMessage()];
        }

        return $rs;
    }

    /**
     * 导出数据
     * @param $date
     * @param $params
     * @return array|void
     */
    public function exportData($date, $params)
    {
        try {
            if (!empty($params)) {
                if (!($this->load($params) && $this->validateField())) {
                    $rs = ['code' => 403, 'msg' => '验证失败'];

                    return $rs;
                }
            }
            $query = self::find()
                ->select(['user_id', 'user_name', 'user_real_name', 'group_id', 'product_id'])
                ->where(['=', 'date', strtotime($date)]);
            if (!empty($this->product_id)) {
                $query->andWhere(['product_id' => $this->product_id]);
            }
            if (!empty($this->group_id)) {
                $groupId = (new SrunJiegou())->getNodeId($this->group_id);
                $query->andWhere(['group_id' => $groupId]);
            }
            $count = $query->count();

            if ($count < 1) {
                $rs = ['code' => 404, 'msg' => Yii::t('app', 'no record')];
            } else {
                //导出该天在网用户
                if ($count > 0) {
                    $csvLimit = self::CSV_EXPORT_LIMIT;
                    if ($count < $csvLimit) {
                        $data = $query->asArray()->all();
                        if ($count > self::EXCEL_EXPORT_LIMIT) {
                            $rs = $this->csvExport($data, $date);
                        } else {
                            $rs = $this->excelExport($data, $date);
                        }
                    } else {
                        $rs = ['code' => 408, 'msg' => Yii::t('app', 'export_msg', [
                            'limit' => $csvLimit
                        ])];
                    }
                }
            }

        } catch(\Exception $e) {
            $rs = ['code' => 500, 'msg' => '导出发生异常:'.$e->getMessage()];
        }

        return $rs;
    }

    /**
     * csv导出
     * @param $data
     * @param $date
     * @throws \yii\web\HttpException
     */
    public function csvExport($data, $date)
    {
        $excelData = $this->getExportData($data);
        $title = Yii::t('app', 'report/actual/index').'-'.$date;
        theCsv::export([
            'data' => $excelData,
            'name' => $title . '.csv',    // 自定义导出文件名称
        ]);
        exit;
    }


    /**
     * 获取导出数据
     * @param $data
     * @return array
     */
    public function getExportData($data) {
        $ids = [];
        $excelData[0] = [
            Yii::t('app', 'user id'),
            Yii::t('app', 'User Name'),
            Yii::t('app', 'realname'),
            Yii::t('app', 'user_group'),
            Yii::t('app', 'products id'),
            Yii::t('app', 'products name'),
        ];

        foreach ($data as $val) {
            // $user 指代的是用户表当中的其中一行数据
            $excelData[] = [$val['user_id'], $val['user_name'], $val['user_real_name'],
                $this->can_group[$val['group_id']], $val['product_id'], $this->product_list[$val['product_id']]];


        }

        return $excelData;
    }
    /**
     * excel导出
     * @param $data
     * @param $date
     */
    public function excelExport($data, $date)
    {
        $excelData = $this->getExportData($data);
        $title = Yii::t('app', 'report/actual/index').'-'.$date;
        $file = $title.'.xls';
        Excel::header_file($excelData, $file, 'xx');
        exit;
    }
    /**
     * 获取资源
     * @return array
     */
    public function getSource()
    {
        $query = self::find()
            ->select(['date', 'count(distinct(user_id)) number'])
            ->where(['between', 'date', strtotime($this->start_time), strtotime($this->stop_time)]);
        if (!empty($this->product_id)) {
            $query->andWhere(['product_id' => $this->product_id]);
        }
        if (!empty($this->group_id)) {
            $groupId = (new SrunJiegou())->getNodeId(explode(',', $this->group_id));
            $query->andWhere(['group_id' => $groupId]);
        }

        $table = $query->indexBy('date')->groupBy('date')->asArray()->all();
        $flag = array_keys($table)[0];
        if (!$flag) {
            $rs = ['code' => 404, 'msg' => Yii::t('app', 'no record')];
        } else {
            $x = $this->getX();
            $series = $xAxis = $tableData =  [];
            foreach ($x as $time) {
                $xAxis[] = $date = date('Y-m-d', $time);
                $series['actual_number'][] = $num = isset($table[$time]) ? $table[$time]['number'] : 0;
                $tableData[$date] = $num;
            }
            $legends = [Yii::t('app', 'actual_number')];
            $series = $this->getLineSeries('line', $series);
            $data = [
                'legend' => $legends,
                'xAxis' => $xAxis,
                'series' => $series,
                'text' => Yii::t('app', 'report/actual/index')
            ];

            $rs = ['code' => 200, 'data' => $data, 'table' => $tableData];

        }

        return $rs;
    }

    /**
     * 获取时间轴
     * @return array
     */
    protected function getX()
    {
        $startTime = strtotime($this->start_time);
        $stopTime = strtotime($this->stop_time);
        $dateArr = [];
        if ($startTime == $stopTime) {
            $dateArr[] = $startTime;
        } else {
            while (1) {
                $dateArr[] = $startTime;
                $startTime = $startTime + 86400;
                if ($startTime == $stopTime) {
                    $dateArr[] = $startTime;
                    break;
                }
            }
        }
        return $dateArr;
    }


    /**
     * @param $type
     * @param $data
     * @return array
     */
    protected function getLineSeries($type, $data)
    {
        $result = [];
        foreach ($data as $k => $v) {
            $object = new \stdClass();
            $object->type = $type;
            $object->name = \Yii::t('app', $k);
            $object2 = new \stdClass();
            $object2->normal = new \stdClass();
            $object->areaStyle = $object2;
            $object->data = $v;
            $result[] = $object;
        }

        return $result;
    }

    /**
     * 设置时间
     * @return bool
     */
    protected function setTime($point = 4)
    {
        $season = ceil((date('n')) / 3);//当月是第几季度
        switch ($point) {
            case 1: //本月
                $this->start_time = date('Y-m-01');
                $this->stop_time = date('Y-m-d', strtotime('-1 days'));
                break;
            case 2: //上月
                $this->start_time = date('Y-m-01', mktime(0, 0, 0, date("m") - 1, 1, date("Y")));
                $this->stop_time = date('Y-m-d', mktime(23, 59, 59, date("m"), 0, date("Y")));
                break;
            case 3: //本季度
                $this->start_time = date('Y-m-d', mktime(0, 0, 0, $season * 3 - 3 + 1, 1, date('Y')));
                $this->stop_time = date('Y-m-d', strtotime('-1 days'));
                break;
            case 4: //上季度
                $this->start_time = date('Y-m-01', mktime(0, 0, 0, $season * 3 - 6 + 1, 1, date('Y')));
                $this->stop_time = date('Y-m-d', mktime(23, 59, 59, $season * 3 - 3, date('t', mktime(0, 0, 0, $season * 3 - 3, 1, date("Y"))), date('Y')));
                break;
            default :
                $this->start_time = date('Y-m-01', strtotime('-30 days'));
                $this->stop_time = date('Y-m-d', strtotime('-1 days'));
                break;
        }

        return true;
    }
}
