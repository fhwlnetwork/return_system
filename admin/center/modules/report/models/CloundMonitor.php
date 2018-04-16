<?php

namespace center\modules\report\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\helpers\Json;
use center\modules\setting\models\ExtendsField;
use yii\data\Pagination;
use center\extend\Tool;
use center\models\CloundOnlineReport;
use center\modules\user\models\Base;

/**
 * This is the model class for table "clound_monitor".
 *
 * @property string $report_id
 * @property integer $time_point
 * @property string $user_info
 * @property string $my_ip
 * @property string $proc
 * @property string $start_count
 * @property double $start_response_time
 * @property string $update_count
 * @property double $update_response_time
 * @property string $stop_count
 * @property double $stop_response_time
 * @property string $auth_count
 * @property double $auth_response_time
 * @property string $coa_count
 * @property double $coa_response_time
 * @property string $dm_count
 * @property double $dm_response_time
 */
class CloundMonitor extends \yii\db\ActiveRecord
{
    public $start_time;
    public $end_time;
    const  TIME_STEP = 15;  //时间间隔
    public $flag;
    //需要监控的进程
    public $procs = ['radiusd', 'rad_auth', 'rad_dm', 'srun_portal_server', 'third_auth', 'proxy_3p'];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'clound_monitor';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['time_point', 'user_info', 'my_ip', 'proc', 'start_count', 'start_response_time', 'update_count', 'update_response_time', 'stop_count', 'stop_response_time', 'auth_count', 'auth_response_time', 'coa_count', 'coa_response_time', 'dm_count', 'dm_response_time'], 'required'],
            [['start_count', 'update_count', 'stop_count', 'auth_count', 'coa_count', 'dm_count'], 'integer'],
            [['start_response_time', 'update_response_time', 'stop_response_time', 'auth_response_time', 'coa_response_time', 'dm_response_time'], 'number'],
            [['my_ip'], 'string', 'max' => 16],
            [['proc'], 'string', 'max' => 32]
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
            'user_info' => 'User Info',
            'my_ip' => 'My Ip',
            'proc' => 'Proc',
            'start_count' => 'Start Count',
            'start_response_time' => 'Start Response Time',
            'update_count' => 'Update Count',
            'update_response_time' => 'Update Response Time',
            'stop_count' => 'Stop Count',
            'stop_response_time' => 'Stop Response Time',
            'auth_count' => 'Auth Count',
            'auth_response_time' => 'Auth Response Time',
            'coa_count' => 'Coa Count',
            'coa_response_time' => 'Coa Response Time',
            'dm_count' => 'Dm Count',
            'dm_response_time' => 'Dm Response Time',
        ];
    }

    //搜索字段
    private $_searchField = null;
    //监控的字段
    private $field = "sum(start_response_time)/sum(start_count) as start_response,
                      sum(auth_response_time)/sum(auth_count) as auth_response,
                      sum(dm_response_time)/sum(dm_count) as dm_response,
                      sum(coa_response_time)/sum(coa_count) as coa_response,
                      sum(update_response_time)/sum(update_count) as update_response,
                      sum(stop_response_time)/sum(stop_count) as stop_response,
                      proc
                      ";

    /**
     * 显示字段
     * @return array|null
     */
    public function getSearchField()
    {
        if (!is_null($this->_searchField)) {
            return $this->_searchField;
        }
        //将扩展字段加入搜索项
        $exFields = [];
        foreach (ExtendsField::getAllData() as $one) {
            $exFields[$one['field_name']] = $one['field_desc'];
        }

        $this->_searchField = \yii\helpers\ArrayHelper::merge([
            'startRes' => Yii::t('app', 'startRes'),
            'authRes' => Yii::t('app', 'authRes'),
            'dmRes' => Yii::t('app', 'dmRes'),
            'coaRes' => Yii::t('app', 'coaRes'),
            'updateRes' => Yii::t('app', 'updateRes'),
            //'user_status' => Yii::t('app', 'user status'),
            'stopRes' => Yii::t('app', 'stopRes'),
            'user_account' => Yii::t('app', 'user online'),
            'school_name' => Yii::t('app', 'school name'),
        ], $exFields);

        return $this->_searchField;
    }

    /**
     * 得到该用户这段时间各进程状况
     * @param $params
     * @return string
     */

    public function getData($params)
    {
        $newParams = [];
        $newParams[':sta'] = isset($params['start_time']) ? strtotime($params['start_time']) : '';
        $newParams[':end'] = isset($params['end_time']) ? strtotime($params['end_time']) : '';
        $newParams[':prod'] = isset($params['products_key']) ? $params['products_key'] : '';
        $where = " 1=1";
        if ($newParams[':end'] < $newParams[':sta']) {
            return ['code' => 401, 'msg' => Yii::t('app', 'end time error')];
        }
        if ($newParams[':end'] - $newParams[':sta'] > 86400 * 10) {//超过10天
            return ['code' => 402, 'msg' => Yii::t('app', 'time error')];
        }


        try {

            $where .= " AND products_key = :prod AND time_point >= :sta AND time_point <= :end";
            $proc = $this->procs;
            $seriesData = $startRes = $stopRes = $authRes = $updateRes = $dmRes = $coaRes = [];
            foreach ($proc as $v) {
                $where .= " AND proc= :pro";
                $newParams[':pro'] = $v;
                $sql = " SELECT $this->field FROM clound_monitor WHERE $where GROUP BY proc";
                $data = $this->findBySql($sql, $newParams)->asArray()->one();

                if (!empty($data)) {
                    foreach ($data as &$val) {

                        if (is_null($val)) {
                            $val = 0;
                        } else {
                            $val = sprintf("%.2f", $val);
                        }
                    }
                    $startRes[] = $data['start_response'];
                    $authRes[] = $data['auth_response'];
                    $updateRes[] = $data['update_response'];
                    $dmRes[] = $data['dm_response'];
                    $coaRes[] = $data['coa_response'];
                    $stopRes[] = $data['stop_response'];
                    $seriesData[$v][] = array_values($data);

                } else {
                    $startRes[] = 0;
                    $authRes[] = 0;
                    $updateRes[] = 0;
                    $dmRes[] = 0;
                    $coaRes[] = 0;
                    $stopRes[] = 0;
                    $seriesData[$v][] = array_fill(0, count($proc), 0);
                }
            }

            return array('code' => 200, 'msg' => 'ok',
                'proc' => $proc,
                'startRes' => $startRes,
                'stopRes' => $stopRes,
                'authRes' => $authRes,
                'updateRes' => $updateRes,
                'coaRes' => $coaRes,
                'dmRes' => $dmRes,
                'seriesData' => $seriesData,
            );

        } catch (\Exception $e) {
            return array('code' => 500, 'msg' => $e->getMessage());
        }

    }


    /**
     * 得到所有用户
     * @return string
     */
    public function getProductsKey()
    {
        try {
            $productsKeys = $this->findBySql("SELECT products_key FROM clound_monitor  GROUP by products_key")->asArray()->all();
            if (!empty($productsKeys)) {
                return Json::encode(array('code' => 200, 'msg' => 'ok', 'rows' => $productsKeys));
            } else {
                return Json::encode(array('code' => 401, 'msg' => Yii::t('app', 'no record')));
            }
        } catch (\Exception $e) {
            return Json::encode(array('code' => 500, 'msg' => $e->getMessage()));
        }
    }

    /**
     * 获取所有用户监控数据
     * @param $params
     * @return string
     */
    public function getAllData($params)
    {
        $newParams = [];
        $newParams[':sta'] = isset($params['start_time']) ? strtotime($params['start_time']) : time() - 30 * 60;
        $newParams[':end'] = isset($params['end_time']) ? strtotime($params['end_time']) : time();
        $newParams[':prod'] = isset($params['products_key']) ? $params['products_key'] : '';
        if ($newParams[':end'] < $newParams[':sta']) {
            return ['code' => 401, 'error' => Yii::t('app', 'end time error')];
        }
        if ($newParams[':end'] - $newParams[':sta'] > 86400 * 31) {//超过一个月
            return ['code' => 402, 'error' => Yii::t('app', 'time error1')];
        }
        if (!empty($params['products_key'])) {
            //判断是否存在
            $isExists = self::findOne(['products_key' => $params['products_key']]);
            if (!$isExists) {
                return ['code' => 404, 'error' => Yii::t('app', '云端账户不存在')];
            }
        }

        try {

            //分页
            $this->flag = isset($params['products_key']) && !empty($params['products_key']);
            $data = $this->getRsData($newParams);
            if ($this->flag) {
                $product = $params['products_key'];
                $rs['rows'][$product] = $data['all'];
                $rs['rows'][$product]['source'] = $data['source'];
                $rs['productData'][$product] = $data['detail'];
                $rs[$params['products_key']] = $data;

            } else {
                $rs = $data;
            }

            $productsKeys = array_keys($rs['rows']);

            if (!empty($productsKeys)) {
                $productsData = $rs['productData'];


                return [
                    'code' => 200,
                    'msg' => 'ok',
                    'productsData' => $productsData,
                    'rows' => $rs['rows'],
                    'productKeys' => $productsKeys,
                    'pagination' => $rs['pagination']
                ];
            } else {
                return ['code' => 403, 'error' => Yii::t('app', 'no record')];
            }

        } catch (\Exception $e) {
            return ['code' => '500', 'error' => $e->getMessage()];
        }
    }

    /**
     * @param $params
     * @return array
     */
    public function getRsData($params)
    {
        $rs = [];
        if ($this->flag) {
            $rs = $this->getSignleData($params);
        } else {
            $rs = $this->getMultiData($params);
        }

        return $rs;
    }

    /**
     * 获取多个云端账户在线数据
     * @param $params
     * @return mixed
     */
    public function getMultiData($params)
    {
        $query = $this->find();
        $query->select('distinct(products_key) products_key');
        $offset = isset($params['offset']) && $params['offset'] > 0 ? $params['offset'] : 10;
        //var_dump($query,$query->count());exit;
        $pagination = new Pagination([
            'defaultPageSize' => $offset,
            'totalCount' => $query->count(),
        ]);
        $productsKeys = self::find()->select(['distinct(products_key) products_key'])
            ->indexBy('products_key')
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->asArray()
            ->all();
        $rs['pagination'] = $pagination;
        if ($productsKeys) {
            foreach ($productsKeys as $product => $val) {
                $params[':prod'] = $product;
                $res = $this->getSignleData($params);
                $rs['rows'][$product] = $res['all'];
                $rs['rows'][$product]['source'] = $res['source'];
                $rs['productData'][$product] = $res['detail'];
            }
        }

        return $rs;
    }

    /**
     * 获取单个云端账户数据
     * @param $params
     * @return array
     */
    public function getSignleData($params)
    {
        $data = self::find()
            ->select($this->field)
            ->where('time_point >= :sta and time_point <= :end and products_key = :prod', $params)
            ->groupBy('proc')
            ->indexBy('proc')
            ->asArray()
            ->all();
        $table = [];
        $data_key = ['start_response', 'auth_response', 'dm_response', 'coa_response', 'update_response', 'stop_response'];
        $all = array_combine($data_key, array_fill(0, count($data_key), 0));
        foreach ($this->procs as $proc) {
            if (isset($data[$proc])) {
                $value = $data[$proc];
                foreach ($value as $type => $val) {
                    if ($type != 'proc') {
                        if (is_null($val)) {
                            $value[$type] = 0;
                        } else {
                            $value[$type] = sprintf("%.2f", $val);
                        }
                        $all[$type] = isset($all[$type]) ? $all[$type] + $value[$type] : $value[$type];
                    }
                }
                $table[$proc][] = $value;
            } else {
                $table[$proc][] = array_combine($data_key, array_fill(0, count($data_key), 0));
            }
        }

        $data = CloundOnlineReport::find()
            ->where('time_point >= :sta and time_point <= :end and products_key = :prod', $params)
            ->indexBy('time_point')
            ->orderBy('time_point asc')
            ->asArray()
            ->all();

        if (empty($data)) {
            $tool = new Tool();
            $xAxisTime = $tool->substrTime($params[':sta'], $params[':end'], 'minutes', '5');
            $yAxis = array_fill(0, count($xAxisTime), 0);
            foreach ($xAxisTime as $time) {
                $time = date('H:i', $time);
                $xAxis[] = $time;
            }
            $all['user_max_account'] = 0;
        } else {
            $xAxisTime = array_keys($data);
           // var_dump($xAxisTime);
            foreach ($xAxisTime as $time) {
                $format = date('m/d H:i', $time);
                $xAxis[] = $format;
                $yAxis[] = $data[$time]['count'];
            }
            $all['user_max_account'] = max($yAxis);
        }

        return ['source' => ['xAxis' => json_encode($xAxis), 'yAxis' => json_encode($yAxis)], 'all' => $all, 'detail' => $table];

    }

    public function getMonitorHistoryData($params)
    {
        $newParams = [];
        $newParams['start_time'] = isset($params['start_time']) ? strtotime($params['start_time']) : '';
        $newParams['end_time'] = isset($params['end_time']) ? strtotime($params['end_time']) : '';
        $newParams['product_key'] = isset($params['product_key']) ? $params['product_key'] : '';
        $newParams['proc'] = isset($params['proc']) ? $this->procs[$params['proc']] : '';

        if ($newParams['end_time'] < $newParams['start_time']) {
            return ['code' => 401, 'error' => Yii::t('app', 'end time error')];
        }
        if ($newParams['end_time'] - $newParams['start_time'] > 86400 * 10) {//超过10天
            return ['code' => 402, 'error' => Yii::t('app', 'time error')];
        }
        //对输入的时间进行切分 比如 10：00 10：05 10：10 10：15 这样子.
        $tool = new Tool();
        if ($newParams['end_time'] - $newParams['start_time'] > 86400) {
            $newParams['end_time'] = date('Y-m-d H:i:s', $newParams['end_time']);
            $newParams['end_time'] = strtotime(substr($newParams['end_time'], 0, 10));
            $unit = 'days';
            $step = 1;
        } else {
            $unit = 'minutes';
            $step = 15;
        }
        $xAxis = $tool->substrTime($newParams['start_time'], $newParams['end_time'], $unit, $step);
        $query = $this->find();
        $key = $newParams['product_key'];
        $query->andWhere('products_key = :pro', [":pro" => "$key"]);
        $query->andWhere('proc = :proc', [':proc' => $newParams['proc']]);
        $query->addGroupBy('products_key');
        $startRes = $stopRes = $authRes = $updateRes = $dmRes = $coaRes = [];
        for ($i = 0, $len = count($xAxis); $i < $len; $i++) {
            if ($unit == 'days') {
                $query->andWhere("time_point > :sta AND time_point <= :end", [':sta' => $xAxis[$i], ':end' => $xAxis[$i] + 86400]);
            } else {
                $query->andWhere("time_point > :sta AND time_point <= :end", [':sta' => $xAxis[$i], ':end' => $xAxis[$i] + self::TIME_STEP * 60]);
            }

            $data = $query->select($this->field)->asArray()->one();

            if (count($data) > 0) {
                foreach ($data as &$val) {
                    if (is_null($val)) {
                        $val = 0;
                    } else {
                        $val = sprintf('%.2f', $val);
                    }
                }
            } else {
                $data['start_response'] = $data['auth_response'] = $data['stop_response'] =
                $data['coa_response'] = $data['dm_response'] = $data['update_response'] = 0;
            }
            $startRes[] = $data['start_response'];
            $authRes[] = $data['auth_response'];
            $stopRes[] = $data['stop_response'];
            $coaRes[] = $data['coa_response'];
            $dmRes[] = $data['dm_response'];
            $updateRes[] = $data['update_response'];

        }

        $timeLine = $tool->formatTime($unit, $xAxis);
        $source = [
            'xAxis' => explode(',', $timeLine),
            'startRes' => $startRes,
            'authRes' => $authRes,
            'coaRes' => $coaRes,
            'updateRes' => $updateRes,
            'stopRes' => $stopRes,
            'dmRes' => $dmRes
        ];

        return [
            'code' => 200,
            'source' => $source
        ];
    }
}
