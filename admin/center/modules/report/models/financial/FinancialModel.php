<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2017/5/18
 * Time: 10:35
 */

namespace center\modules\report\models\financial;

use yii;
use m35\thecsv\theCsv;
use common\models\User;
use common\extend\Excel;
use yii\db\ActiveRecord;
use center\models\Pagination;
use center\modules\report\models\Financial;
use center\modules\auth\models\SrunJiegou;
use center\modules\financial\models\PayList;
use center\modules\financial\models\PayType;
use center\modules\financial\models\RefundList;
use center\modules\financial\models\TransferBalance;

/**
 * 财务报表基础模型
 * Class FinancialReport
 * @package center\modules\report\models
 */
class FinancialModel extends ActiveRecord
{
    public $start_time;
    public $stop_time;
    public $timePoint;
    public $operator;
    public $baseModel;
    public $proIds; //产品id
    public $flag;
    public $can_mgr;
    public $can_group;
    public $payOperators = [];//收费员
    public $sql_type;
    public $user_name;
    public $can_pro;
    public $type;
    public $pay_type;
    public $payQuery;
    public $refundQuery;

    public function init()
    {
        $this->baseModel = new Financial();
        $this->flag = User::isSuper();
        if (!$this->flag) {
            $this->can_mgr = $this->baseModel->getMgrOpe(); //产品
            $this->can_pro = $this->baseModel->getProNames();  //非超管可以管理的产品
            $this->can_group = SrunJiegou::canMgrGroupNameList(); //用户组
        } else {
            $this->can_group = SrunJiegou::canMgrGroupNameList();
            $this->can_mgr = (new PayList())->getFinancialMgr();
        }
        $this->pay_type = [
            '0' => Yii::t('app', 'users balance'),
            '1' => Yii::t('app', 'product'),
            '2' => Yii::t('app', 'Financial ExtraPay'),
            '3' => Yii::t('app', 'buy_buy'),
        ];

        parent::init(); //TODO:: change some settings
    }


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%pay_list}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['start_time', 'stop_time', 'timePoint', 'operator', 'data_source', 'user_name'], 'safe'],
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
            $this->addError('stop_name', Yii::t('app', 'end time big error'));

            return false;
        }
        //结束时间不能大于当前的时间
        if (strtotime($this->stop_time) > time()) {
            $this->addError('stop_name', Yii::t('app', 'end time error'));

            return false;
        }
        if ($stop_time < $start_time) {
            $this->addError('stop_name', Yii::t('app', 'end time error'));

            return false;
        }

        if (!empty($this->operator)) {
            if (!in_array($this->operator, $this->can_mgr)) {
                $this->addError('operator', '该管理员不存在或者不在可管理的管理员之内');

                return false;
            }
        }

        return true;
    }

    /**
     * 获取管理员
     * @return array
     */
    public function getMgrs()
    {
        $pay_mgr = self::find()
            ->select(['mgr_name'])
            ->andWhere(['mgr_name' => $this->can_mgr])
            ->asArray()
            ->all();
        foreach ($pay_mgr as $v) {
            $pay_mgrs[] = $v['mgr_name'];
        }
        $refund_mgr = RefundList::find()
            ->select(['mgr_name'])
            ->andWhere(['mgr_name' => $this->can_mgr])
            ->indexBy('mgr_name')
            ->asArray()
            ->all();
        $refund_mgr = array_keys($refund_mgr);

        if (!empty($refund_mgr)) {
            return array_unique(array_merge($pay_mgrs, $refund_mgr));
        } else {
            return $pay_mgrs ? $pay_mgrs: [];
        }
    }

    /**
     * 设置时间
     * @return bool
     */
    public function setTime($point = 4)
    {
        switch ($point) {
            case 1:
                $this->start_time = date('Y-m-d');
                $this->stop_time = date('Y-m-d');
                break;
            case 2:
                $this->start_time = date('Y-m-d', strtotime('-1 days'));
                $this->stop_time = date('Y-m-d', strtotime('-1 days'));
                break;
            case 3:
                $this->start_time = date('Y-m-d', strtotime('-7 days'));
                $this->stop_time = date('Y-m-d', strtotime('-1 days'));
                break;
            case 4:
                $this->start_time = date('Y-m-d', strtotime('-30 days'));
                $this->stop_time = date('Y-m-d', strtotime('-1 days'));
                break;
        }

        return true;
    }


    /**
     * 设置默认时间
     * @return bool
     */
    public function setDefault()
    {
        $this->start_time = date('Y-m-d', strtotime('-30 days'));
        $this->stop_time = date('Y-m-d');

        return true;
    }

    /**
     * 获取数据
     * @param $params
     * @return array
     */
    public function getData($params)
    {
        if ($this->type == 'methods') {
            if (empty($this->operator)) {
                $rs = $this->getMultiData($params);
            } else {
                $rs = $this->getMultiData($params, [$this->operator]);
            }
        } else {
            if (!empty($this->operator)) {
                $rs = $this->getSingleData($params);
            } else {
                $rs = $this->getSingleData($params);
            }
        }


        return $rs;
    }


    /**
     * 获取单个管理员缴费记录
     * @param $params
     * @param array $mgrs
     * @return array
     */
    public function getSingleData($params, $mgrs = [])
    {
        $this->generateQuery($params);
        if (empty($mgrs)) {
            $mgrs = $this->getMgrs();
        }
        $pagination = new Pagination(['totalCount' => count($mgrs), 'pageSize' => 10]);
        //取10个管理员
        $list = [];
        $usedMgr = array_slice($mgrs, $pagination->offset, $pagination->limit);
        $rs = $this->getPayOrRefundData($usedMgr, 'signle');
        $pay_list = $rs['pay_list'];
        $refund_list = $rs['refund_list'];
        foreach ($pay_list as $v) {
            $list[$v['mgr_name']]['pay_num'] = $v['nums'];
            $list[$v['mgr_name']]['money_summary'] = $v['nums'];
            $list[$v['mgr_name']]['count'] = $v['count'];
            $list[$v['mgr_name']][0] = $v['nums'];
        }
        //var_dump($list);exit;
        foreach ($this->pay_type as $id => $val) {
            if ($id == 0) {
                //电子钱包缴费
                continue;
            }
            $query = TransferBalance::find();
            $query->select(['SUM(transfer_num) as type_pay_sum', 'mgr_name', 'count(id) count']);
            $query->where(['mgr_name' => $usedMgr]);
            if (!empty($this->start_time)) {
                $query->andWhere(['>=', 'create_at', strtotime($this->start_time)]);
            }
            if (!empty($this->start_time)) {
                $query->andWhere(['<=', 'create_at', strtotime($this->stop_time) + 86399]);
            }
            //按照缴费项目查询每个收费员的缴费信息
            if ($id == 3) {//购买套餐
                $query->andWhere(['>', 'package_id', 0]);
            } elseif ($id == 1) {//查询产品续费
                $query->andWhere(['>', 'product_id', 0]);
                $query->andWhere(['package_id' => 0]);
            } elseif ($id == 2) {//查询附加费用
                $query->andWhere(['>', 'extra_pay_id', 0]);
            } else {
                $query->andWhere(['type' => 0]);
            }

            if (isset($params['group_id']) && !empty($params['group_id'])) {
                $groups = explode(',', $params['group_id']);
                $ids = SrunJiegou::getNodeId($groups);
                $query->andWhere(['group_id' => $ids]);
            }
            $payData = $query->groupBy('mgr_name')->indexBy('mgr_name')->asArray()->all();

            //var_dump($payData);
            foreach ($payData as $name => $value) {
                $list[$name][$id] = $value['type_pay_sum'];
                $list[$name][0] -= $value['type_pay_sum'];
            }
        }
        foreach ($refund_list as $mgrName => $v) {
            $list[$mgrName]['count'] += $v['count'];
            $list[$mgrName]['money_summary'] -= $v['nums'];
            $list[$mgrName]['refund_num'] += $v['nums'];
        }
        // var_dump($list);exit;
        $totalPayNum = $this->getTotal($mgrs, $params,'pay_list');
        $refundNum = $this->getTotal($mgrs, $params,'refund_list');
        return [
            'pagination' => $pagination,
            'list' => $list,
            'mgrs' => $usedMgr,
            'refund_list' => $refund_list,
            'types' => $this->pay_type,
            'totalMoney' => $totalPayNum - $refundNum,
            'refundMoney' => $refundNum,
            'payNum' => $totalPayNum
        ];
    }

    /**
     * 管理员按方式缴费
     * @param $sta
     * @param $end
     * @param array $mgrs
     * @return array
     */
    public function getMultiData($params, $mgrs = [])
    {
        $this->generateQuery($params);
        $pagesSize = 10; // 每页条数
        if (empty($mgrs)) {
            $mgrs = $this->getMgrs();
        }
        $pagination = new Pagination(['totalCount' => count($mgrs), 'pageSize' => $pagesSize]);
        //取10个管理员
        $list = [];
        $usedMgr = array_slice($mgrs, $pagination->offset, $pagination->limit);
        $types = $this->getPayTypeUsed();
        //缴费金额

        $list = [];
        $rs = $this->getPayOrRefundData($usedMgr);
        $pay_list = $rs['pay_list'];
        $refund_list = $rs['refund_list'];
        $totalPayNum = $this->getTotal($mgrs, $params, 'pay_list');
        $refundNum = $this->getTotal($mgrs, $params, 'refund_list');
        foreach ($pay_list as $v) {
            $list[$v['mgr_name']][$v['pay_type_id']] = $v['nums'];
            $list[$v['mgr_name']]['pay_num'] += $v['nums'];
            $list[$v['mgr_name']]['money_summary'] += $v['nums'];
            $list[$v['mgr_name']]['count'] += $v['count'];
        }
        foreach ($refund_list as $mgrName => $v) {
            $list[$mgrName]['count'] += $v['count'];
            $list[$mgrName]['money_summary'] -= $v['nums'];
            $list[$mgrName]['refund_num'] += $v['nums'];
        }

        // var_dump($pay_list);exit;

        return [
            'pagination' => $pagination,
            'list' => $list,
            'mgrs' => $usedMgr,
            'refund_list' => $refund_list,
            'types' => $types,
            'totalMoney' => $totalPayNum - $refundNum,
            'refundMoney' => $refundNum,
            'payNum' => $totalPayNum
        ];
    }

    /**
     * 获取支付详情
     * @param $usedMgr
     * @return array
     */
    public function getPayOrRefundData($usedMgr, $type = 'multi')
    {
        if ($type == 'multi') {
            $select = ['sum(pay_num) nums', 'mgr_name', 'pay_type_id', 'count(id) count'];
            $group = 'mgr_name, pay_type_id';
        } else {
            $select = ['sum(pay_num) nums', 'mgr_name', 'count(id) count'];
            $group = 'mgr_name';
        }
        $pay_list = $this->payQuery
            ->select($select)
            ->andWhere(['mgr_name' => $usedMgr])
            ->groupBy($group)
            ->orderBy('nums desc')
            ->asArray()
            ->all();
        //var_dump($pay_list, $usedMgr, $this->payQuery);exit;
        //退费金额
        $refund_list = $this->refundQuery
            ->select(['sum(refund_num) nums', 'mgr_name', 'count(id) count'])
            ->andWhere(['mgr_name' => $usedMgr])
            ->indexBy('mgr_name')
            ->groupBy('mgr_name')
            ->orderBy('nums desc')
            ->asArray()
            ->all();

        return ['pay_list' => $pay_list, 'refund_list' => $refund_list];
    }
    /**
     * @param $params
     * @return bool
     */
    public function setDate($params)
    {
        $timePoint = isset($params['timePoint']) ? $params['timePoint'] : '';
        if (!empty($timePoint)) {
            $season = ceil((date('n')) / 3);//当月是第几季度
            switch ($timePoint) {
                case 3:
                    $this->start_time = date('Y-m-1');
                    $this->stop_time = date('Y-m-d');
                    break;
                case 5:
                    //本季度
                    $this->start_time = date('Y-m-d', mktime(0, 0, 0, $season * 3 - 3 + 1, 1, date('Y')));
                    $this->stop_time = date('Y-m-d');
                    break;
                case 7:
                    //本年
                    $this->start_time = date('Y-01-01');
                    $this->stop_time = date('Y-m-d');
                    break;
                default:
                    $this->start_time = date('Y-m-1');
                    $this->stop_time = date('Y-m-d');
                    break;
            }
        }

        return true;
    }

    /**
     * 获取缴费方式
     * @return array
     */
    public function getPayTypeUsed()
    {
        $list = PayType::find()->select('id, type_name')->indexBy('id')->asArray()->all();
        $types = [];

        foreach ($list as $id => $val) {
            $types[$id] = $val['type_name'];
        }

        return $types;
    }

    /**
     * 获取总费用
     * @param $mgrs
     * @param $type
     * @return int|string
     */
    public function getTotal($mgrs, $params, $type)
    {
        if ($type == 'pay_list') {
            $query = self::find()->select('sum(pay_num) nums');
        } else {
            $query = RefundList::find()->select('sum(refund_num) nums')->where(['type' => 0]);
        }

        if (!empty($this->start_time)) {
            $query->andWhere(['>=', 'create_at', strtotime($this->start_time)]);
        }
        if (!empty($this->start_time)) {
            $query->andWhere(['<=', 'create_at', strtotime($this->stop_time) + 86399]);
        }

        if (isset($params['group_id']) && !empty($params['group_id'])) {
            $groups = explode(',', $params['group_id']);
            $ids = SrunJiegou::getNodeId($groups);
            $query->andWhere(['group_id' => $ids]);
        }
        $total = $query->andWhere(['mgr_name' => $mgrs])->asArray()->one();

        return !is_null($total['nums']) ? sprintf('%.2f', $total['nums']) : 0;
    }


    /**
     * 导出数据
     * @param array $params
     * @return array
     */
    public function exportData($params = [])
    {
        try {
            //导出数据
            if (isset($params['FinancialModel']['start_time']) && !empty($params['FinancialModel']['start_time'])) {
                if (!empty($params['timePoint'])) {
                    $this->setDate($params);
                }
                $this->load($params);
            }
            set_time_limit(0);
            ini_set('memory_limit', '1024M'); //设置可以导出1GB

            if ($this->type == 'methods') {
                $fileName = Yii::t('app', 'pay methods');
                $excelData = $this->getExcelDataByMethods();
            } else {
                $excelData = $this->getExcelDataByType();
                $fileName = Yii::t('app', 'pay type');
            }
            $title = $fileName . $this->start_time . '-' . $this->stop_time;
            $file = $title . '.xls';
            Excel::header_file($excelData, $file, $title);
            exit;
        } catch (\Exception $e) {
            $rs = ['code' => 500, 'msg' => '导出数据发生异常' . $e->getMessage()];
        }

        return $rs;
    }


    /**
     * 获取excel数据
     * @return mixed
     */
    public function getExcelDataByMethods()
    {
        $this->generateQuery();
        if ($this->operator) {
            $mgrs = [$this->operator];
        } else {
            $mgrs = $this->getMgrs();
        }
        $types = $this->getPayTypeUsed();
        //缴费金额
        $rs = $this->getPayOrRefundData($mgrs);
        $pay_list = $rs['pay_list'];
        $refund_list = $rs['refund_list'];
        $list = [];
        $count = $pay_num = $money = 0;
        foreach ($pay_list as $v) {
            $list[$v['mgr_name']]['types'][$v['pay_type_id']] = $v['nums'];
            $list[$v['mgr_name']]['pay_num'] += $v['nums'];
            $list[$v['mgr_name']]['money_summary'] += $v['nums'];
            $list[$v['mgr_name']]['count'] += $v['count'];
            $list['all']['types'][$v['pay_type_id']] += $v['nums'];
            $list['all']['count'] += $v['count'];
            $list['all']['pay_num'] += $v['nums'];
        }
        foreach ($refund_list as $mgrName => $v) {
            $list[$mgrName]['count'] += $v['count'];
            $list[$mgrName]['money_summary'] -= $v['nums'];
            $list[$mgrName]['refund_num'] += $v['nums'];
            $list['all']['refund_num'] += $v['nums'];
        }
        $list['all']['money_summary'] = $list['all']['pay_num'] - $list['all']['refund_num'];

        $array = $this->getExcelData($list, $types);

        return $array;
    }

    /**
     * 缴费类型
     * @return mixed
     */
    public function getExcelDataByType()
    {
        $this->generateQuery();
        if ($this->operator) {
            $mgrs = [$this->operator];
        } else {
            $mgrs = $this->getMgrs();
        }
        $types = $this->pay_type;
        $rs = $this->getPayOrRefundData($mgrs, 'signele');
        $pay_list = $rs['pay_list'];
        $refund_list = $rs['refund_list'];
        //缴费金额
        $list = [];
        foreach ($pay_list as $v) {
            $list[$v['mgr_name']]['pay_num'] = $v['nums'];
            $list[$v['mgr_name']]['money_summary'] = $v['nums'];
            $list[$v['mgr_name']]['count'] = $v['count'];
            $list[$v['mgr_name']][0] = $v['nums'];
            $list['all']['types'][0] += $v['nums'];
            $list['all']['count'] += $v['count'];
            $list['all']['pay_num'] += $v['nums'];
        }
        foreach ($types as $id => $val) {
            if ($id == 0) {
                continue;
            }
            $query = TransferBalance::find();
            $query->select(['SUM(transfer_num) as type_pay_sum', 'mgr_name', 'count(id) count']);
            $query->where(['mgr_name' => $mgrs]);
            if (!empty($this->start_time)) {
                $query->andWhere(['>=', 'create_at', strtotime($this->start_time)]);
            }
            if (!empty($this->start_time)) {
                $query->andWhere(['<=', 'create_at', strtotime($this->stop_time) + 86399]);
            }
            //按照缴费项目查询每个收费员的缴费信息
            if ($id == 3) {//购买套餐
                $query->andWhere(['>', 'package_id', 0]);
            } elseif ($id == 1) {//查询产品续费
                $query->andWhere(['>', 'product_id', 0]);
                $query->andWhere(['package_id' => 0]);
            } elseif ($id == 2) {//查询附加费用
                $query->andWhere(['>', 'extra_pay_id', 0]);
            }

            if (isset($params['group_id']) && !empty($params['group_id'])) {
                $groups = explode(',', $params['group_id']);
                $ids = SrunJiegou::getNodeId($groups);
                $query->andWhere(['group_id' => $ids]);
            }
            $payData = $query->groupBy('mgr_name')->indexBy('mgr_name')->asArray()->all();

            //var_dump($payData);
            foreach ($payData as $name => $value) {
                $list[$name]['types'][$id] = $value['type_pay_sum'];
                $list[$name]['types'][0] -= $value['type_pay_sum'];
                $list['all']['types'][$id] += $value['type_pay_sum'];
                $list['all']['types'][0] -= $value['type_pay_sum'];
            }
        }

        // var_dump($refund_list, $this->refundQuery);
        foreach ($refund_list as $mgrName => $v) {
            $list[$mgrName]['count'] += $v['count'];
            $list[$mgrName]['money_summary'] -= $v['nums'];
            $list[$mgrName]['refund_num'] += $v['nums'];
            $list['all']['refund_num'] += $v['nums'];
        }
        //var_dump($list);exit;
        $array = $this->getExcelData($list, $types);

        return $array;
    }

    /**
     * @param $list
     * @param $types
     * @return mixed
     */
    public function getExcelData($list, $types)
    {
        $array[0] = [
            '0' => Yii::t('app', 'toll taker'),
            '1' => Yii::t('app', 'pay count'),
            '2' => Yii::t('app', 'money summary'),
            '3' => Yii::t('app', 'total revenue'),
        ];
        foreach ($types as $type_id => $type_name) {
            $array[0][] = $type_name;
        }
        $array[0][] = Yii::t('app', 'refund amount');
        $totalNum = $total = $payTotal = $refundTotal = 0;
        if (!empty($list)) {
            $s = 1;
            foreach ($list as $k => $v) {
                if ($k != 'all') {
                    $array[$s][] = $k;
                } else {
                    $pos = $s;
                    $array[$s][] = '总计';
                }
                $array[$s][] = $v['count'];
                $array[$s][] = sprintf("%.2f", $v['money_summary']);
                $array[$s][] = sprintf("%.2f", $v['pay_num']);
                foreach ($types as $id => $name) {
                    $array[$s][] = isset($v['types'][$id]) ? sprintf('%.2f', $v['types'][$id]) : '0.00';
                }
                $array[$s][] = sprintf("%.2f", $v['refund_num']);
                $s++;
            }
            //总计

        }
        $arr = $array[$pos];
        unset($array[$pos]);
        $array = array_values($array);
        $array[] = $arr;

        return $array;
    }

    /**
     * 生成查询query
     */
    public function generateQuery($params = [])
    {
        $this->payQuery = self::find();
        $this->refundQuery = RefundList::find()->where(['type' => 0]);
        if (!empty($this->start_time)) {
            $this->payQuery->andWhere(['>=', 'create_at', strtotime($this->start_time)]);
            $this->refundQuery->andWhere(['>=', 'create_at', strtotime($this->start_time)]);
        }
        if (!empty($this->stop_time)) {
            $this->payQuery->andWhere(['<=', 'create_at', strtotime($this->stop_time) + 86399]);
            $this->refundQuery->andWhere(['<=', 'create_at', strtotime($this->stop_time) + 86399]);
        }

        if (isset($params['group_id']) && !empty($params['group_id'])) {
            $groups = explode(',', $params['group_id']);
            $ids = SrunJiegou::getNodeId($groups);
            $this->payQuery->andWhere(['group_id' => $ids]);
            $this->refundQuery->andWhere(['group_id' => $ids]);
        }

        return true;
    }
}
