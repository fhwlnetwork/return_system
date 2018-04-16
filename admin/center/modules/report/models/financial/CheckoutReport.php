<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2017/5/18
 * Time: 16:47
 */

namespace center\modules\report\models\financial;

use center\modules\auth\models\SrunJiegou;
use yii;
use common\extend\Excel;
use center\extend\Tool;
use m35\thecsv\theCsv;
use center\models\Pagination;
use common\models\FileOperate;
use center\modules\user\models\Base;
use center\modules\strategy\models\Product;
use center\modules\setting\models\EmailForm;

/**
 * 结算报表统计
 * Class CheckoutReport
 * @package center\modules\report\models\financial
 */
class CheckoutReport extends FinancialBase
{
    public $names;
    public $query;
    CONST EXCEL_EXPORT_LIMIT = 10000;
    CONST CSV_EXPORT_LIMIT = 100000;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%checkout_list}}';
    }

    public function init()
    {
        $productModel = new Product();
        $product = $productModel->getNameOfList();
        $this->names = $product;

        parent::init(); //TODO:: change some settings
    }

    /**
     * @return int|string
     */
    public function getTotal()
    {
        $total = $this->query->select(['sum(spend_num+rt_spend_num) num'])->asArray()->one();

        return !is_null($total['num']) ? sprintf('%.2f', $total['num']) : 0;
    }

    /**
     * 获取结算数据
     * @return array|void
     */
    public function getCheckoutData($params)
    {
        try {
            $this->proIds = array_keys($this->names);
            $sta = strtotime($this->start_time);
            $end = strtotime($this->stop_time) + 86400;
            $this->generateQuery($sta, $end, $params);
            $spend = $this->getTotal();
            $rs = $this->getBase();
            $rs['spend'] = $spend;
        } catch (\Exception $e) {
            $rs = ['code' => 500, 'msg' => '发生异常:' . $e->getMessage()];
        }

        return $rs;
    }

    /**
     * 获取数据|总数
     * @param $sta
     * @param $end
     * @param array $params
     * @param string $type
     * @return array
     */
    public function getBase($type = 'data')
    {
        $count = $this->query->count();
        if ($type == 'count') {
            return $count;
        } else {
            $pagesSize = 10; // 每页条数
            $pagination = new Pagination(['totalCount' => $count, 'pageSize' => $pagesSize]);
            $list = $this->query->select(['user_name', 'group_id', 'spend_num', 'rt_spend_num', 'product_id', 'flux', 'minutes', 'create_at',])
                ->offset($pagination->offset)
                ->limit($pagination->limit)
                ->asArray()
                ->all();

            return [
                'pages' => $pagination,
                'count' => $count,
                'list' => $list
            ];
        }
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
            if (!isset($params['CheckoutReport']['start_time']) && empty($params['CheckoutReport']['start_time'])) {
                $this->setDefault();
            } else {
                $this->load($params);
                $this->setDate($params);
            }
            $sta = strtotime($this->start_time);
            $end = strtotime($this->stop_time);
            $this->generateQuery($sta, $end, $params);
            $count = $this->query->count();
            $type = $params['action'];
            $limit = ($type == 'excel') ? self::EXCEL_EXPORT_LIMIT : self::CSV_EXPORT_LIMIT;
            if ($count > $limit) {
                $msg = Yii::t('app', 'group msg6', [
                    'mgr' => Yii::$app->user->identity->username,
                    'limit' => $limit
                ]);
                $rs = ['code' => 400, 'msg' => $msg];
            } else {
                set_time_limit(0);
                ini_set('memory_limit', '1024M'); //设置可以导出1GB

                $title = Yii::t('app', 'report/financial/checkout');
                $excelData = $this->getExcelData();
                if ($type == 'excel') {
                    //生成excel
                    $file = $title . '.xls';
                    Excel::header_file($excelData, $file, $title);
                    exit;
                } else {
                    theCsv::export([
                        'data' => $excelData,
                        'name' => $title . '.csv',    // 自定义导出文件名称
                    ]);

                    exit;
                }
            }
        } catch (\Exception $e) {
            $rs = ['code' => 500, 'msg' => '导出数据发生异常' . $e->getMessage()];
        }

        return $rs;
    }

    /**
     * 发送邮件
     * @param array $params
     * @return array
     */
    public function sendEmail($params = [], $mgrs = [])
    {
        //结算数据发送email
        $rs = [];
        try {
            if (!isset($params['CheckoutReport']['start_time']) && empty($params['CheckoutReport']['start_time'])) {
                $this->setDefault();
            } else {
                $this->load($params);
                $this->setDate($params);
            }
            $sta = strtotime($this->start_time);
            $end = strtotime($this->stop_time);
            $this->generateQuery($sta, $end, $params);
            $count = $this->query->count();
            $limit = self::CSV_EXPORT_LIMIT;
            if ($count > $limit) {
                $msg = Yii::t('app', 'group msg6', [
                    'mgr' => Yii::$app->user->identity->username,
                    'limit' => $limit
                ]);
                $rs = ['code' => 400, 'msg' => $msg];
            } else {
                set_time_limit(0);
                ini_set('memory_limit', '1024M');
                $excelData = $this->getExcelData();
                $subject = Yii::t('app', 'checkout email subject', ['date' => $this->start_time]);
                $file = FileOperate::dir('report') . '/checkout_' . date('YmdHis') . '.xls';
                Excel::arrayToExcel($excelData, $file, $subject);
                if ($mgrs) {
                    $content = '';
                    foreach ($mgrs as $one) {
                        if (!empty($one['email'])) {
                            EmailForm::sendEmail($one['email'], $subject, $content, 'text', $file);
                        }
                    }
                }
                $rs = ['code' => 200, 'msg' => Yii::t('app', 'operate success.')];
            }
        } catch (\Exception $e) {
            $rs = ['code' => 500, 'msg' => '发送邮件发生异常' . $e->getMessage()];
        }

        return $rs;
    }

    /**
     * 生成查询query
     * @param $sta
     * @param $end
     * @param $params
     * @return bool
     */
    protected function generateQuery($sta, $end, $params)
    {
        $query = self::find()->where(['between', 'create_at', $sta, $end]);

        if (isset($params['product_id']) && !empty($params['product_id'])) {
            $query->andWhere(['product_id' => $params['product_id']]);
        } else {
            if (!$this->flag) {
                //非超管
                $query->andWhere(['product_id' => $this->proIds]);
            }
        }
        if (isset($params['group_id']) && !empty($params['group_id'])) {
            $groupIds = SrunJiegou::getNodeId(explode(',', $params['group_id']));
            $query->andWhere(['group_id' => $groupIds]);
        } else {
            if (!$this->flag) {
                //非超管
                $query->andWhere(['group_id' => array_keys($this->can_group)]);
            }
        }

        if (!empty($this->user_name)) {
            $query->andWhere(['user_name' => $this->user_name]);
        }
        $this->query = $query;

        return true;
    }

    /**
     * 获取excel数据
     * @return array
     */
    public function getExcelData()
    {
        $data = $this->query->select(['user_name', 'group_id', 'spend_num', 'rt_spend_num', 'product_id', 'flux', 'minutes', 'create_at'])->asArray()->all();
        $spend = $this->getTotal();
        $names = Base::find()->select(['user_name', 'user_real_name'])->indexBy('user_name')->asArray()->all();
        $excelData = [];
        $fields = [
            'user_name' => Yii::t('app', 'account'),
            'user_real_name' => Yii::t('app', 'name'),
            'num' => Yii::t('app', 'checkout amount'),
            'group_id' => Yii::t('app', 'group id'),
            'product_id' => Yii::t('app', 'product'),
            'flux' => Yii::t('app', 'flux'),
            'minutes' => Yii::t('app', 'time lenth'),
            'create_at' => Yii::t('app', 'checkout time'),
        ];
        $excelData[0] = array_values($fields);
        if ($data) {
            foreach ($data as $one) {
                $byte = Tool::bytes_format($one['flux']);
                $time = Tool::seconds_format($one['minutes']);
                $real_name = $names[$one['user_name']]['user_real_name'];
                $num = sprintf('%.2f', $one['spend_num'] + $one['rt_spend_num']);
                $product = $this->names[$one['product_id']];
                $group = isset($this->can_group[$one['group_id']]) ? $this->can_group[$one['group_id']] : $one['group_id'];
                $date = date('Y-m-d H:i', $one['create_at']);
                $excelData[] = [$one['user_name'], $real_name, $num, $group, $product, $byte, $time, $date];
            }
        }
        $excelData[] = [
            Yii::t('app', 'total report'),
            $spend . Yii::t('app', 'currency'),
        ];

        return $excelData;
    }

}