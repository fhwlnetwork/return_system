<?php
/**
 * Created by PhpStorm.
 * User: cyc
 * Date: 15-7-24
 * Time: 上午10:48
 */

namespace center\modules\report\models;

use center\extend\Tool;
use center\modules\auth\models\SrunJiegou;
use center\modules\auth\models\UserModel;
use center\modules\Core\models\FinancialBase;
use center\modules\financial\models\CheckoutList;
use center\modules\financial\models\PayList;
use center\modules\interfaces\models\SoapCenter;
use common\models\User;
use yii;
use center\modules\strategy\models\Product;

class Financial extends FinancialBase
{
    public $payOperators = [];//收费员
    public $data_source = [];//数据来源
    public $statistical_cycle = [];//统计周期
    public $start_time_day;
    public $start_time_year;
    public $start_time;
    public $end_time;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['data_source', 'statistical_cycle', 'start_time', 'end_time'], 'string'],
        ];
    }

    public static function getAttributesList()
    {
        return [
            'data_source' => [
                'all' => Yii::t('app','all'),
                'users' => Yii::t('app','user recharge'),
                'system' => Yii::t('app','system pay'),
            ],
            'statistical_cycle' => [
                'day' => Yii::t('app','report by day'),
                'week' => Yii::t('app','report by week'),
                'year' => Yii::t('app','report by year'),
            ],
        ];
    }


    public function getLegend(){
        $legend = [
            Yii::t('app','users balance'),
            Yii::t('app', 'Financial ExtraPay'),
        ];
        $legend = array_merge($this->getProNames(),$legend);
        return json_encode($legend);
    }

    public function getPayMgr($pay_type_id, $type, $operator, $start_time, $end_time){
        if(empty($this->payOperators)){
            $payModel = new PayList();
            $data = $payModel->payMgr($pay_type_id, $type, $operator, $start_time, $end_time);
            $this->payOperators = $data ? $data : [];
        }else{
            $this->payOperators = [$operator];
        }
        return $this->payOperators;
    }

    public function getPayMgrStr($pay_type_id, $type, $operator, $start_time, $end_time){
        if(empty($this->operator)){
            $mgr = $this->getPayMgr($pay_type_id, $type, $operator, $start_time, $end_time);
            return json_encode($mgr);
        }else{
            return json_encode([$operator]);
        }
    }

    public function getSeries_bak($pay_type_id, $type, $operator, $start_time, $end_time){
        $payModel = new PayList();
        $mgr = $this->getPayMgr($pay_type_id, $type, $operator, $start_time, $end_time);

        //图例的属性
        $legend = [];
        //产品
        foreach($mgr as $v){
            $legend[] = [
                'type' => self::PAY_PRODUCT,
                'product_id' => $v['products_id'],
                'name' => Yii::t('app','product:').$v['products_name'],
            ];
        }
        //账户余额
        $legend[] = [
            'type' => self::PAY_BALANCE,
            'name' => Yii::t('app','users balance'),
        ];
        //附加费用
        $legend[] = [
            'type' => self::PAY_EXTRA,
            'name' => Yii::t('app', 'Financial ExtraPay'),
        ];

        foreach($legend as $k => $v){
            $data = [];
            foreach($mgr as $m){
                $pid = isset($v['product_id']) && $v['type'] == self::PAY_PRODUCT ? $v['product_id'] : 0;
                $payData = $payModel->payMgrReport($m, '', '', $v['type'], $pid);
                $data[] = isset($payData[0]['pay_num']) ? intval($payData[0]['pay_num']) : 0;
            }
            $series[] = [
                'name' => $v['name'],
                'type' => 'bar',
                'data' => $data,
                'markPoint' => [
                    'data' => [
                        [
                            'type' => 'max',
                            'name' => Yii::t('app','max'),
                        ],
                        [
                            'type' => 'min',
                            'name' => Yii::t('app','min'),
                        ],
                    ]
                ],
                'markLine' => [
                    'data' => [
                        'type' => 'average',
                        'name' => Yii::t('app','average'),
                    ]
                ],
            ];
        }
        //var_dump($series);exit;
        return json_encode($series);

    }

    public function getSeries($start_time, $end_time, $selected_projects){
        $payModel = new PayList();
        $mgr = $this->payOperators;
        $proList = $this->getProList();
        //图例的属性
        $legend = [];
        //产品
        foreach($proList as $v){
            $legend[] = [
                'type' => PayList::PAY_PRODUCT,
                'product_id' => $v['products_id'],
                'name' => $v['products_name'],
            ];
        }
        //账户余额
        $legend[] = [
            'type' => PayList::PAY_BALANCE,
            'name' => Yii::t('app','users balance'),
        ];
        //附加费用
        $legend[] = [
            'type' => PayList::PAY_EXTRA,
            'name' => Yii::t('app', 'Financial ExtraPay'),
        ];

        foreach($legend as $k => $one){
            if(!is_null($selected_projects) && !in_array($one['name'], $selected_projects)){
                unset($legend[$k]);
            }
        }
        foreach($legend as $v){
            $data = [];
            if(is_array($mgr)){
                foreach($mgr as $m){
                    $pid = isset($v['product_id']) && $v['type'] == PayList::PAY_PRODUCT ? $v['product_id'] : 0;
                    $payData = $payModel->payMgrReport($m, $start_time, $end_time, $v['type'], $pid);
                    $data[] = !empty($payData) && isset($payData[0]['pay_num']) ? intval($payData[0]['pay_num']) : 0;
                }
            }
            $series[] = [
                'name' => $v['name'],
                'type' => 'bar',
                'data' => $data,
                'itemStyle' => [
                    'normal' => [
                        'label' => [
                            'show' => true,
                            'position' => 'top',
                        ]
                    ]
                ],
            ];
        }
        return json_encode($series);

    }

    //获取所有缴费方式的字符串
    public function getMethods($operator, $start_time, $end_time){
        $str = '';
        $methods = (new PayList())->getTypesUsed($operator, $start_time, $end_time);
        if(!empty($methods)){
            foreach($methods as $v){
                $str[]= '"'.$v.'"';
            }
            $str = implode(',', $str);
        }
        return $str;
    }

    public function getSeriesType($operator, $start_time, $end_time){
        $payModel = new PayList();
        $query = $payModel::find()->select(['sum(a.pay_num) as pay_num,pay_type_id'])->from($payModel->tableName() . 'a');
        if (!empty($operator)) {
            $query->andWhere(['=', 'a.mgr_name', $operator]);
        }
        if (!empty($start_time)) {
            $query->andWhere(['>=', 'a.create_at', strtotime($start_time)]);
        }
        if (!empty($end_time)) {
            $query->andWhere(['<=', 'a.create_at', strtotime($end_time)]);
        }
        //验证权限
        if (!User::isSuper()) {
            //所有可以管理的组
            $canMgrOrg = SrunJiegou::getAllNode();
            $query->leftJoin('users b', 'a.user_name=b.user_name');
            $query->andWhere(['b.group_id'=>$canMgrOrg]);
            //判断产品
            //所有可以管理的产品
            $product = (new Product())->getNameOfList();
            $proKey = array_keys($product);
            $query->orWhere(['and',
                             ['!=','a.product_id', 0],
                             ['a.product_id' => $proKey]
            ]);
            //判断管理员
            $userModel = new User();
            $canMgrope = $userModel->getChildIdAll();
            $query->andWhere(['a.mgr_name'=>$canMgrope]);
        }
        $pay_data = $query->groupBy('a.pay_type_id')
            ->all();
        $money = '';
        if(!empty($pay_data)){
            $moneyArr = $moneyData = [];
            $methods = (new PayList())->getTypesUsed($operator, $start_time, $end_time);

            foreach($pay_data as $v){
                $moneyData[$v['pay_type_id']] = $v['pay_num'];
            }
            foreach($methods as $id => $name){
                if(array_key_exists($id,$moneyData)){
                    $moneyArr[$id] = $moneyData[$id];
                }else{
                    $moneyArr[$id] = 0;
                }
            }
            $money = implode(',',$moneyArr);
        }
        return $money;
    }

    /**
     * 根据数据来源生成查询条件
     * @param $data_source
     * @param $query
     * @return mixed
     */
    public function dataSourceWhere($data_source, $query){
        $newQuery = $query;
        if($data_source !== 'all'){
            //如果是查询用户自己充值的费用，首先要把字段mgr_name里的管理员和接口的名称排除出去。
            //查询管理员和接口名称
            $mgrs = UserModel::find()->select('username')->asArray()->all();
            $mgrs_data = [];
            foreach($mgrs as $v){
                $mgrs_data[] = $v['username'];
            }
            $center_names = SoapCenter::find()->select('center_name')->asArray()->all();
            if($center_names){
                foreach($center_names as $one){
                    $mgrs_data[] = $one['center_name'];
                }
            }
            if($data_source == 'users'){
                $newQuery = $query->andWhere(['not in', 'mgr_name', $mgrs_data]);
            }
            if($data_source == 'system'){
                $newQuery = $query->andWhere(['mgr_name' => $mgrs_data]);
            }
        }
        return $newQuery;
    }

    /**
     * 根据某天统计产品收入金额
     * @param $data
     * @param $products
     * @return string
     */
    public function getProductsIncomeByDay($data, $products){
        $value = $xAxisData = [];

        if(!empty($products)){
            $xAxisData = array_values($products);
            $products_ids = array_keys($products);
            //查询缴费清单的金额，根据数据来源，日期，查询都充值了多少
            $query = PayList::find()->select(['sum(pay_num) as pay_num','product_id']);

            //如果是根据数据来源统计 用户或者管理端（管理员和接口）
            $query = $this->dataSourceWhere($data['data_source'], $query);

            //根据日期统计
            if(!empty($data['start_time_day'])){
                $query->andWhere(['and', ['>=', 'create_at', strtotime($data['start_time_day'])],['<', 'create_at', strtotime($data['start_time_day'])+86400]]);
            }
            $res = $query->andWhere(['product_id'=>$products_ids])->groupBy(['product_id'])->asArray()->all();

            $pro_payed = [];//充值过的产品
            foreach($res as $v){
                $pro_payed[$v['product_id']] = sprintf("%.2f", floatval($v['pay_num']));
            }
            //按照产品拼产品充值过的金额数组
            foreach($products_ids as $pid){
                $value[] = array_key_exists($pid, $pro_payed) ? $pro_payed[$pid] : 0;
            }
        }

        //返回echarts
        $text = Yii::t('app','report/financial/product');
        $seriesData = $value;
        $option = $this->getBar14($text, $xAxisData, $seriesData);
        return $option;
    }
    public function getIncomeByDay($data, $products){
        $value = $xAxisData = [];

        if(!empty($products)){
            $xAxisData = array_values($products);
//            $xAxisData = $products;
            $products_ids = array_keys($products);
            foreach ($xAxisData as $kk => &$vv) {
                $vv = $products_ids[$kk] . ':' . $vv;
            }
            //查询缴费清单的金额，根据数据来源，日期，查询都充值了多少
            $query = PayList::find()->select(['sum(pay_num) as pay_num','product_id']);

            //如果是根据数据来源统计 用户或者管理端（管理员和接口）
            $query = $this->dataSourceWhere($data['data_source'], $query);

            //根据日期统计
            if(!empty($data['start_time_day'])){
                $query->andWhere(['and', ['>=', 'create_at', strtotime($data['start_time_day'])],['<', 'create_at', strtotime($data['start_time_day'])+86400]]);
            }
            $res = $query->andWhere(['product_id'=>$products_ids])->groupBy(['product_id'])->asArray()->all();

            $pro_payed = [];//充值过的产品
            foreach($res as $v){
                $pro_payed[$v['product_id']] = sprintf("%.2f", floatval($v['pay_num']));
            }
            //按照产品拼产品充值过的金额数组
            foreach($products_ids as $pid){
                $value[] = array_key_exists($pid, $pro_payed) ? $pro_payed[$pid] : 0;
            }
        }

        //返回echarts
        $text = Yii::t('app','report/financial/product');
        $seriesData = $value;

        $option['title_text'] = $text;
        $option['xaxis_data'] = $xAxisData;
        $option['series_data'] = $seriesData;

        return $option;
    }

    /**
     * 统计某一个产品一段时间内的收入
     * @param $product_id
     * @param mixed $time
     * @return array|yii\db\ActiveRecord[]
     */
    public function getIncomeByDefault($product_id,$time = 'today'){
        if($time === 'today'){
            $start_time = strtotime(date('Y-m-d'));
            $end_time = time();
        }else{
            $start_time = $time;
            $end_time = $time + 24 * 3600;
        }

        $query = PayList::find()->select(['sum(pay_num) as pay_num','product_id']);
        //根据日期统计
        $query->where(['between','create_at',$start_time,$end_time]);
        $rs = $query->andWhere(['product_id'=>$product_id])->asArray()->one();

        return $rs;
    }

    /**
     * 根据周统计产品收入金额
     * @param $data
     * @param $products
     * @return string
     */
    public function getProductsIncomeByWeek($data, $products){
        $value = $legendData = $legend = $series = [];

        if($data['start_time'] && $data['end_time'] && !empty($products)){
            $legendData = array_values($products);
            $products_ids = array_keys($products);
            $week = [
                Yii::t('app', 'Sunday'),
                Yii::t('app', 'Monday'),
                Yii::t('app', 'Tuesday'),
                Yii::t('app', 'Wednesday'),
                Yii::t('app', 'Thursday'),
                Yii::t('app', 'Friday'),
                Yii::t('app', 'Saturday'),
            ];
            for($i = strtotime($data['start_time']); $i <= strtotime($data['end_time']);$i += 86400){
                //把日期变为周几
                $legend[] = $week[date('w',$i)];
                //根据某天查出每个产品的缴费总金额
                //SELECT sum(pay_num) as pay_num,product_id from pay_list where date_format(from_unixtime(create_at),'%Y-%m-%d')='2015-11-01' GROUP BY date_format(from_unixtime(create_at),'%d'),product_id;
                $query = PayList::find()->select(['sum(pay_num) as pay_num', 'product_id']);
                //如果是根据数据来源统计 用户或者管理端（管理员和接口）
                $query = $this->dataSourceWhere($data['data_source'], $query);
                $oneDay = $query->andWhere(["date_format(from_unixtime(create_at),'%Y-%m-%d')" => date('Y-m-d', $i)])->groupBy(["date_format(from_unixtime(create_at),'%d')", 'product_id'])->all();
                if($oneDay){
                    foreach($oneDay as $v){
                        $value[$i][$v['product_id']] = sprintf("%.2f", floatval($v['pay_num']));
                    }
                }
            }
            //拼数据
            foreach($products_ids as $pid){
                $data_pro = [];
                for($i = strtotime($data['start_time']); $i <= strtotime($data['end_time']);$i += 86400){
                    $data_pro[] = isset($value[$i][$pid]) ? $value[$i][$pid] : 0;
                }
                $series[] = [
                    'name' => $products[$pid],
                    'type' => 'bar',
                    'stack' => Yii::t('app', 'network_trouble_font3'),
                    'itemStyle' => [
                        'normal' =>[
                            'label' => [
                                'show' => true,
                                'position' => 'insideRight'
                            ]
                        ]
                    ],
                    'data' => $data_pro,
                ];
            }
        }
        $option = $this->getBar4($legendData, $legend, $series);
        return $option;
    }

    /**
     * @param array $data post 参数
     * @param array $products 对应产品和 id
     * @return mixed
     */
    public function getIncomeByWeek($data, $products){
        $value = $legendData = $legend = $series = [];

        if($data['start_time'] && $data['end_time'] && !empty($products)){
            $legendData = array_values($products);
            $products_ids = array_keys($products);
            foreach ($legendData as $k => &$v) {
                $v = $products_ids[$k] . ':' . $v;
            }
            $week = [
                Yii::t('app', 'Sunday'),
                Yii::t('app', 'Monday'),
                Yii::t('app', 'Tuesday'),
                Yii::t('app', 'Wednesday'),
                Yii::t('app', 'Thursday'),
                Yii::t('app', 'Friday'),
                Yii::t('app', 'Saturday'),
            ];
            for($i = strtotime($data['start_time']); $i <= strtotime($data['end_time']);$i += 86400){
                //把日期变为周几
                $legend[] = $week[date('w',$i)] . '(' . date('Y-m-d',$i) . ')';
                //根据某天查出每个产品的缴费总金额
                //SELECT sum(pay_num) as pay_num,product_id from pay_list where date_format(from_unixtime(create_at),'%Y-%m-%d')='2015-11-01' GROUP BY date_format(from_unixtime(create_at),'%d'),product_id;
                $query = PayList::find()->select(['sum(pay_num) as pay_num', 'product_id']);
                //如果是根据数据来源统计 用户或者管理端（管理员和接口）
                $query = $this->dataSourceWhere($data['data_source'], $query);
                $oneDay = $query->andWhere(["date_format(from_unixtime(create_at),'%Y-%m-%d')" => date('Y-m-d', $i)])->groupBy(["date_format(from_unixtime(create_at),'%d')", 'product_id'])->all();
                if($oneDay){
                    foreach($oneDay as $v){
                        $value[$i][$v['product_id']] = sprintf("%.2f", floatval($v['pay_num']));
                    }
                }
            }
            //拼数据
            foreach($products_ids as $pid){
                $data_pro = [];
                for($i = strtotime($data['start_time']); $i <= strtotime($data['end_time']);$i += 86400){
                    $data_pro[] = isset($value[$i][$pid]) ? $value[$i][$pid] : 0;
                }
                $series[] = [
                    'name' => $pid . ':' . $products[$pid],
                    'type' => 'bar',
                    'stack' => Yii::t('app', 'network_trouble_font3'),
                    'itemStyle' => [
                        'normal' =>[
                            'label' => [
                                'show' => true,
                                'position' => 'insideRight'
                            ]
                        ]
                    ],
                    'data' => $data_pro,
                ];
            }
        }
        $option['legend_data'] = $legendData;
        $option['xaxis_data'] = $legend;
        $option['series'] = $series;

        return $option;
    }

    /**
     * 根据年统计产品收入金额
     * @param $data
     * @param $products
     * @return string
     */
    public function getProductsIncomeByYear($data, $products){
        $value = $legendData = $series = $months = [];

        if($data['start_time_year'] && !empty($products)){
            $legendData = array_values($products);
            $products_ids = array_keys($products);

            for($i = 1; $i <= ($data['start_time_year'] == date('Y') ? intval(ltrim(date('m'),'0')) : 12); $i++){
                $months[sprintf('%02d', $i)] = sprintf('%02d', $i).Yii::t('app', 'months');
            }
            foreach($months as $m => $m_name){
                //根据某月查出每个产品的缴费总金额
                //SELECT sum(pay_num) as pay_num,product_id from pay_list where date_format(from_unixtime(create_at),'%Y-%m-%d')='2015-11-01' GROUP BY date_format(from_unixtime(create_at),'%d'),product_id;
                $query = PayList::find()->select(['sum(pay_num) as pay_num', 'product_id']);
                //如果是根据数据来源统计 用户或者管理端（管理员和接口）
                $query = $this->dataSourceWhere($data['data_source'], $query);
                $oneMonth = $query->andWhere(["date_format(from_unixtime(create_at),'%Y-%m')" => $data['start_time_year'].'-'.$m])->groupBy(["date_format(from_unixtime(create_at),'%m')", 'product_id'])->all();
                if($oneMonth){
                    foreach($oneMonth as $v){
                        $value[$m][$v['product_id']] = floatval(sprintf("%.2f", floatval($v['pay_num'])));
                    }
                }
            }
            //拼数据
            foreach($products_ids as $pid){
                $data_pro = [];
                foreach($months as $m => $m_name){
                    $data_pro[] = isset($value[$m][$pid]) ? $value[$m][$pid] : 0;
                }
                $series[] = [
                    'name' => $products[$pid],
                    'type' => 'bar',
                    'stack' => Yii::t('app', 'network_trouble_font3'),
                    'itemStyle' => [
                        'normal' =>[
                            'label' => [
                                'show' => true,
                                'position' => 'insideRight'
                            ]
                        ]
                    ],
                    'data' => $data_pro,
                ];
            }
        }
        $option = $this->getBar4($legendData, array_values($months), $series);
        return $option;
    }
    public function getIncomeByYear($data, $products){
        $value = $legendData = $series = $months = [];

        if($data['start_time_year'] && !empty($products)){
            $legendData = array_values($products);
            $products_ids = array_keys($products);
            foreach ($legendData as $k => &$v) {
                $v = $products_ids[$k] . ':' . $v;
            }
            for($i = 1; $i <= ($data['start_time_year'] == date('Y') ? intval(ltrim(date('m'),'0')) : 12); $i++){
                $months[sprintf('%02d', $i)] = sprintf('%02d', $i).Yii::t('app', 'months');
            }
            foreach($months as $m => $m_name){
                //根据某月查出每个产品的缴费总金额
                //SELECT sum(pay_num) as pay_num,product_id from pay_list where date_format(from_unixtime(create_at),'%Y-%m-%d')='2015-11-01' GROUP BY date_format(from_unixtime(create_at),'%d'),product_id;
                $query = PayList::find()->select(['sum(pay_num) as pay_num', 'product_id']);
                //如果是根据数据来源统计 用户或者管理端（管理员和接口）
                $query = $this->dataSourceWhere($data['data_source'], $query);
                $oneMonth = $query->andWhere(["date_format(from_unixtime(create_at),'%Y-%m')" => $data['start_time_year'].'-'.$m])->groupBy(["date_format(from_unixtime(create_at),'%m')", 'product_id'])->all();
                if($oneMonth){
                    foreach($oneMonth as $v){
                        $value[$m][$v['product_id']] = floatval(sprintf("%.2f", floatval($v['pay_num'])));
                    }
                }
            }
            //拼数据
            foreach($products_ids as $pid){
                $data_pro = [];
                foreach($months as $m => $m_name){
                    $data_pro[] = isset($value[$m][$pid]) ? $value[$m][$pid] : 0;
                }
                $series[] = [
                    'name' => $pid . ':' . $products[$pid],
                    'type' => 'bar',
//                    'stack' => Yii::t('app', 'network_trouble_font3'),
//                    'itemStyle' => [
//                        'normal' =>[
//                            'label' => [
//                                'show' => true,
//                                'position' => 'insideRight'
//                            ]
//                        ]
//                    ],
                    'data' => $data_pro,
                ];
            }
        }
        $option['legend_data'] = $legendData;
        $option['xaxis_data'] = array_values($months);
        $option['series'] = $series;

        return $option;
    }

    /**
     * 根据某天统计用户组收入金额
     * @param $data
     * @param $groups
     * @return string
     */
    public function getGroupsIncomeByDay($data, $groups){
        $value = $xAxisData = [];
        if(!empty($groups)){
            $xAxisData = array_values($groups);
            $groups_ids = array_keys($groups);
            //查询缴费清单的金额，根据数据来源，日期，查询都充值了多少
            $query = PayList::find()->select(['sum(pay_num) as pay_num','group_id'])->leftJoin('users','pay_list.user_name=users.user_name');

            //如果是根据数据来源统计 用户或者管理端（管理员和接口）
            $query = $this->dataSourceWhere($data['data_source'], $query);

            //根据日期统计
            if(!empty($data['start_time_day'])){
                $query->andWhere(['and', ['>=', 'create_at', strtotime($data['start_time_day'])],['<', 'create_at', strtotime($data['start_time_day'])+86400]]);
            }
            $res = $query->andWhere(['group_id'=>$groups_ids])->groupBy(['group_id'])->asArray()->all();

            $groups_payed = [];//充值过的产品
            foreach($res as $v){
                $groups_payed[$v['group_id']] = sprintf("%.2f", floatval($v['pay_num']));
            }
            //按照用户组拼用户组充值过的金额数组
            foreach($groups_ids as $gid){
                $value[] = array_key_exists($gid, $groups_payed) ? $groups_payed[$gid] : 0;
            }
        }

        //返回echarts
        $text = Yii::t('app','report/financial/usergroup');
        $seriesData = $value;
        $option = $this->getBar14($text, $xAxisData, $seriesData);
        return $option;
    }

    /**
     * 根据周统计用户组收入金额
     * @param $data
     * @param $groups
     * @return string
     */
    public function getGroupsIncomeByWeek($data, $groups){
        $value = $legendData = $legend = $series = [];

        if($data['start_time'] && $data['end_time'] && !empty($groups)){
            $legendData = array_values($groups);
            $groups_ids = array_keys($groups);
            $week = [
                Yii::t('app', 'Sunday'),
                Yii::t('app', 'Monday'),
                Yii::t('app', 'Tuesday'),
                Yii::t('app', 'Wednesday'),
                Yii::t('app', 'Thursday'),
                Yii::t('app', 'Friday'),
                Yii::t('app', 'Saturday'),
            ];
            for($i = strtotime($data['start_time']); $i <= strtotime($data['end_time']);$i += 86400){
                //把日期变为周几
                $legend[] = $week[date('w',$i)];
                //根据某天查出每个用户组的缴费总金额
                $query = PayList::find()->select(['sum(pay_num) as pay_num','group_id'])->leftJoin('users','pay_list.user_name=users.user_name');
                //如果是根据数据来源统计 用户或者管理端（管理员和接口）
                $query = $this->dataSourceWhere($data['data_source'], $query);
                $oneDay = $query->andWhere(["date_format(from_unixtime(create_at),'%Y-%m-%d')" => date('Y-m-d', $i)])->groupBy(["date_format(from_unixtime(create_at),'%d')", 'group_id'])->asArray()->all();

                if($oneDay){
                    foreach($oneDay as $v){
                        $value[$i][$v['group_id']] = sprintf("%.2f", floatval($v['pay_num']));
                    }
                }
            }
            //拼数据
            foreach($groups_ids as $gid){
                $data_group = [];
                for($i = strtotime($data['start_time']); $i <= strtotime($data['end_time']);$i += 86400){
                    $data_group[] = isset($value[$i][$gid]) ? $value[$i][$gid] : 0;
                }
                $series[] = [
                    'name' => $groups[$gid],
                    'type' => 'bar',
                    'stack' => Yii::t('app', 'network_trouble_font3'),
                    'itemStyle' => [
                        'normal' =>[
                            'label' => [
                                'show' => true,
                                'position' => 'insideRight'
                            ]
                        ]
                    ],
                    'data' => $data_group,
                ];
            }
        }
        $option = $this->getBar4($legendData, $legend, $series);
        return $option;
    }

    /**
     * 根据年统计用户组收入金额
     * @param $data
     * @param $groups
     * @return string
     */
    public function getGroupsIncomeByYear($data, $groups){
        $value = $legendData = $series = $months = [];

        if($data['start_time_year'] && !empty($groups)){
            $legendData = array_values($groups);
            $groups_ids = array_keys($groups);

            for($i = 1; $i <= ($data['start_time_year'] == date('Y') ? intval(ltrim(date('m'),'0')) : 12); $i++){
                $months[sprintf('%02d', $i)] = sprintf('%02d', $i).Yii::t('app', 'months');
            }
            foreach($months as $m => $m_name){
                //根据某月查出每个用户组的缴费总金额
                $query = PayList::find()->select(['sum(pay_num) as pay_num','group_id'])->leftJoin('users','pay_list.user_name=users.user_name');
                //如果是根据数据来源统计 用户或者管理端（管理员和接口）
                $query = $this->dataSourceWhere($data['data_source'], $query);
                $oneMonth = $query->andWhere(["date_format(from_unixtime(create_at),'%Y-%m')" => $data['start_time_year'].'-'.$m])->groupBy(["date_format(from_unixtime(create_at),'%m')", 'group_id'])->asArray()->all();
                if($oneMonth){
                    foreach($oneMonth as $v){
                        $value[$m][$v['group_id']] = sprintf("%.2f", floatval($v['pay_num']));
                    }
                }
            }
            //拼数据
            foreach($groups_ids as $gid){
                $data_group = [];
                foreach($months as $m => $m_name){
                    $data_group[] = isset($value[$m][$gid]) ? $value[$m][$gid] : 0;
                }
                $series[] = [
                    'name' => $groups[$gid],
                    'type' => 'bar',
                    'stack' => Yii::t('app', 'network_trouble_font3'),
                    'itemStyle' => [
                        'normal' =>[
                            'label' => [
                                'show' => true,
                                'position' => 'insideRight'
                            ]
                        ]
                    ],
                    'data' => $data_group,
                ];
            }
        }
        $option = $this->getBar4($legendData, array_values($months), $series);
        return $option;
    }

    /**
     * 生成柱状图bar4
     * @param string $legendData
     * @param array $yAxisData
     * @param array $series
     * @return string
     */
    public function getBar4($legendData, $yAxisData, $series){
        $str = "option = {
                    tooltip : {
                        trigger: 'axis',
                        axisPointer : {
                                    type : 'shadow'
                        }
                    },
                    legend: {
                        data:". json_encode($legendData) ."
                    },
                    toolbox: {
                        show : true,
						showTitle:false,
                        y: 'bottom',
                        feature : {
                                    magicType : {show: true, type: ['line', 'bar']},
                                    restore : {show: true},
                                    saveAsImage : {show: true}
                        }
                    },
                    calculable : true,
                    xAxis : [
                        {
                            type : 'value'
                        }
                    ],
                    yAxis : [
                        {
                            type : 'category',
                            data : ". json_encode($yAxisData) ."
                        }
                    ],
                    series : ". json_encode($series).",
                }";
        return $str;
    }

    /**
     * 生成柱状图bar14
     * @param string $text
     * @param array $xAxisData
     * @param array $seriesData
     * @return string
     */
    public function getBar14($text, $xAxisData, $seriesData){
        $str = "
            option = {
                title: {
                    x: 'center',
                    text: '". $text ."',
                    subtext: '',
                    link: ''
                },
                        tooltip: {
                            trigger: 'item'
                },
                        toolbox: {
                            show: true,
							showTitle:false,
                    feature: {
                                saveAsImage: {show: true}
                            }
                },
                        calculable: true,
                grid: {
                            borderWidth: 0,
                    y: 80,
                    y2: 60
                },
                xAxis: [
                    {
                        type: 'category',
                        show: false,
                        data: ". json_encode($xAxisData) ."
                    }
                ],
                yAxis: [
                    {
                        type: 'value',
                        show: false
                    }
                ],
                series: [
                    {
                        name: '". $text ."',
                        type: 'bar',
                        itemStyle: {
                            normal: {
                                color: function(params) {
                                    // build a color map as your need.
                                    var colorList = [
                                        '#C1232B','#B5C334','#FCCE10','#E87C25','#27727B',
                                        '#FE8463','#9BCA63','#FAD860','#F3A43B','#60C0DD',
                                        '#D7504B','#C6E579','#F4E001','#F0805A','#26C0C0'
                                    ];
                                    return colorList[params.dataIndex]
                                    },
                                label: {
                                    show: true,
                                        position: 'top',
                                        formatter: '{b}\\n{c}'
                                    }
                            }
                        },
                        data: ". json_encode($seriesData) .",
                    }
                ]
            }";
        return $str;
    }

    public function getMonthCheckoutList($params){
        $model = new CheckoutList();
        $total = 0;
        //产品
        $productModel = new Product();
        $product = $productModel->getNameOfList();
        //用户组
        $groups = SrunJiegou::getAllIdNameVal();

        // 获取记录数，偏移量及记录数
        $query = $model::find()->select(['checkout_list.*','users.group_id','user_real_name','sum(spend_num+rt_spend_num) as num']);
        //用户组
        $query->leftJoin('users', 'users.user_name=checkout_list.user_name');
        //如果非超级管理员，则需要去判断
        if(!User::isSuper()){
            //判断组
            //所有可以管理的组
            if(isset($params['group_id']) && !empty($params['group_id'])){
                $canMgrOrg = $params['group_id'];
            }else{
                $canMgrOrg = SrunJiegou::getAllNode();
            }
            $query->andWhere(['users.group_id'=>$canMgrOrg]);

            //判断产品
            //所有可以管理的产品
            $query->andWhere(['checkout_list.product_id'=>array_keys($product)]);
        }elseif(isset($params['group_id']) && !empty($params['group_id'])){
            //用户组下的所有成员
            $group_id = explode(',', $params['group_id']);
            $ids = SrunJiegou::getNodeId($group_id);
            $query->andWhere(array('in', 'users.group_id', $ids));
        }

        foreach ($params as $k => $v) {
            if ($v != '') {
                if($k == 'user_name'){
                    $query->andWhere(['checkout_list.user_name'=>$v]);
                }
                if($k == 'statis_start_time' && !empty($v)){
                    $query->andWhere(['>=', "checkout_list.create_at", strtotime($v)]);
                }
                if($k == 'statis_end_time' && !empty($v)){
                    $query->andWhere(['<', "checkout_list.create_at", strtotime('+1 month', strtotime($v))]);
                }
                if($k == 'product_id'){
                    $query->andWhere(["checkout_list.".$k => $v]);
                }
                if($k == 'group_id'){
                    $query->andWhere(['=', 'users.group_id', $v]);
                }
            }
        }

        $query->groupBy(["date_format(from_unixtime(create_at),'%m')", 'checkout_list.user_name']);
        //排序
        $query->orderBy(['id' => SORT_DESC]);
        $data = $query->asArray()->all();

        if($data){
            foreach($data as $k => $one){
                if($one['count']>1){
                    $data[$k]['create_at'] = date('Y-m',$one['create_at']);
                }else{
                    $data[$k]['create_at'] = date('Y-m-d H:i:s',$one['create_at']);
                }
                //结算金额
                $data[$k]['num'] = sprintf("%.2f", floatval($one['num']));
                //流量
                $data[$k]['flux'] = Tool::bytes_format($one['flux']);
                //时长
                $data[$k]['minutes'] = Tool::seconds_format($one['minutes']);
                //产品名
                $data[$k]['product_id'] = empty($one['product_id']) ? '' : (!empty($product[$one['product_id']]) ? $product[$one['product_id']] : '');
                //用户组名称
                $data[$k]['group_id'] = $one['group_id'] ? (isset($groups[$one['group_id']]) ? $groups[$one['group_id']] : '') : '';
                $total += $one['num'];
            }
        }
        return [$data,sprintf("%.2f", floatval($total))];
    }

    public function checkoutExport($list,  $total_num){
        $fields = [
            'user_name' => Yii::t('app','account'),
            'user_real_name' => Yii::t('app', 'name'),
            'num' => Yii::t('app','checkout amount'),
            'group_id' => Yii::t('app','group id'),
            'product_id' => Yii::t('app','product'),
            'flux' => Yii::t('app','flux'),
            'minutes' => Yii::t('app','time lenth'),
            'create_at' => Yii::t('app','checkout time'),
        ];
        $data[0] = array_values($fields);
        if($list){
            foreach($list as $one){
                $one_data = [];
                foreach($fields as $k => $name){
                    $one_data[] = isset($one[$k])?$one[$k]:'';
                }
                $data[] = $one_data;
            }
        }
        $data[] = [
            Yii::t('app', 'total report'),
            $total_num.Yii::t('app', 'currency'),
        ];
        return $data;
    }

    function getMonthNum( $date1, $date2, $tags='-' ){
        $date1 = explode($tags,$date1);
        $date2 = explode($tags,$date2);
        return abs($date1[0] - $date2[0]) * 12 + abs($date1[1] - $date2[1]) +1;
    }

    function prMonths($start, $end){
        $month_data = [];
        $dt_start = strtotime($start);
        $dt_end = strtotime($end);
        while ($dt_start<=$dt_end){
            $month_data[] = date('Y-m', $dt_start);
            $dt_start = strtotime('+1 month',$dt_start);
        }
        return $month_data;
    }

    /**
     * 获取能管理的管理员
     * @return array
     */
    public function getMgrOpe()
    {
        //判断管理员
        $userModel = new User();
        //获取管理员能管理的
        if (!User::isSuper()) {
            $canMgrope = $userModel->getChildIdAll();
        } else {
            $allMgrs = User::find()->select('username')->indexBy('username')->asArray()->all();
            $canMgrope = array_keys($allMgrs);
        }

        //查询接口名称
        $center_names = SoapCenter::find()->select('center_name')->asArray()->all();
        //北向用户名称
        $north_names = PayList::find()->select('DISTINCT(mgr_name) mgr_name')->where('mgr_name like :mgr', [':mgr' => 'Api%'])->asArray()->all();

        if($center_names){
            foreach($center_names as $one){
                $canMgrope[] = $one['center_name'];
            }
        }
        if($north_names){
            foreach($north_names as $one){
                $canMgrope[] = $one['mgr_name'];
            }
        }

        return $canMgrope;
    }
} 