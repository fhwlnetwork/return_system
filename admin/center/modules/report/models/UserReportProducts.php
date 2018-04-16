<?php

namespace center\modules\report\models;

use Yii;
use center\modules\strategy\models\Product;
use common\models\Redis;
use center\modules\user\models\Base;
use center\modules\auth\models\SrunJiegou;
use center\modules\financial\models\PayList;
use yii\db\Query;

/**
 * This is the model class for table "online_report_products".
 *
 * @property integer $report_id
 * @property integer $time_point
 * @property integer $products_id
 * @property integer $count
 * @property integer $bytes_in
 * @property integer $bytes_out
 * @property integer $time_long
 */
class UserReportProducts extends \yii\db\ActiveRecord
{
    public $start_At; //开始时间
    public $stop_At; //截止时间
    public $groupid; //用户组

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'users';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'user_name', 'user_create_time', 'groupid'], 'safe'],
            [['start_At', 'stop_At'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'User Id',
            'user_name' => 'User Name',
            'user_create_time' => 'Create Time',
        ];
    }

    //验证输入时间的合理性以及时间不长的合理性
    public function validateField()
    {
        $start_At = strtotime($this->start_At); //开始时间
        $stop_At = strtotime($this->stop_At); //结束时间

        if ($stop_At === $start_At || $stop_At < $start_At) {
            $this->addError($this->stop_At, Yii::t('app', 'end time error'));
        }

        return true;
    }

    static public function getGroupList()
    {
        $dataArray['groupid'] = array('1' => Yii::t('app', 'report_userReport_font1'), '2' => Yii::t('app', 'report_userReport_font2'), '3' => Yii::t('app', 'report_userReport_font3'));
        return $dataArray;
    }

    /**
     * 用户组产品消费情况
     * @param string $params
     * @param array $fieldArray
     * @param bool|true $flag
     * @return array
     */
    public function getGroupData($son, $fieldArray = array(), $flag = true)
    {
        $fieldArray = $son+$fieldArray;
        $start_At = strtotime($this->start_At);
        $stop_At = strtotime($this->stop_At)+86399;
        $product = new Product();
        $list = $product->getList();

        $names = array('0' => Yii::t('app', 'report operate remind21'));
        foreach ($list as $key => $value) {
            $names[$key] = $value['products_name'];
        }
        $productIds = array_keys($names);

        $data = PayList::find()
            ->select('sum(pay_num) num, a.user_id, a.group_id, product_id')
            ->where(['between', 'create_at', $start_At, $stop_At])
            ->andWhere(['a.group_id' => array_keys($fieldArray)])
            ->andWhere(['product_id' => $productIds])
            ->leftJoin('users a', 'a.user_name = pay_list.user_name')
            ->groupBy('group_id, product_id')
            ->asArray()
            ->all();

        $rs = $this->getRsData($data, $names, $fieldArray);
        $legends = json_encode($rs['legends'], JSON_UNESCAPED_UNICODE);
        $xAxis = json_encode(array_values($fieldArray), JSON_UNESCAPED_UNICODE);
        //var_dump($rs, $legends, $data, $xAxis);exit;
        $series = $this->getPieSeries($rs['rs'], $names);

        return [
            'data' => [
                'legends' => $legends,
                'xAxis' => $xAxis,
                'series' => $series
            ],
            'table' => $rs['table'],
            'products' => $names
        ];
    }

    public function getPayData($groupid, $productid, $start_At, $stop_At, $flag)
    {

        $model = new PayList();
        $query = $model::find();
        $query->select(['sum(pay_list.pay_num) as sum']);
        //$query->select(['users.user_name', 'pay_list.pay_num','pay_list.product_id']);
        if ($flag) {
            $query->andWhere(['>=', 'pay_list.create_at', $start_At]);
            $query->andWhere(['<=', 'pay_list.create_at', $stop_At]);
        }
        $query->leftJoin('users', 'users.user_name=pay_list.user_name');
        $query->andWhere(['users.group_id' => $groupid]);
        $query->andWhere(['pay_list.product_id' => $productid]);
        $data = $query->asArray()->all();
        $sum = $data['0']['sum'];
        if (!empty($sum)) {
            return $sum;
        }
        return '0';
    }


    //获取指定查询时间的数据
    public function getData($params = "", $fieldArray, $flag = true)
    {
        if (!empty($params)) {
            $start_At = strtotime($params->start_At);
            $stop_At = strtotime($params->stop_At);
        } else {
            $start_At = strtotime(date('Y-m-01'));
            $stop_At = strtotime(date('Y-m-d'));
        }

        $stop_Ats = $stop_At + 60 * 60 * 24;
        $grouid = array_keys($fieldArray);
        //先判断表是否存在
        $sqldrop = "show table status like '%tmp_query%'";
        $commandDrop = Yii::$app->db->createCommand($sqldrop);
        $res = $commandDrop->queryAll();
        if (!empty($res)) {
            //先删除表
            $sqldrop = "DROP TABLE `tmp_query`";
            $commandDrop = Yii::$app->db->createCommand($sqldrop);
            $commandDrop->execute();
        }

        //创建临时表
        $sql = "create table tmp_query(id int(11) NOT NULL auto_increment PRIMARY KEY,user_id int(11) default NULL,products_id int(11) default NULL,group_id int(11) default NULL,user_available int(11) default NULL)ENGINE=MEMORY DEFAULT CHARSET=utf8;";
        $command = Yii::$app->db->createCommand($sql);
        $command->execute();

        //从user表中取出所有符合时间的数据
        if ($flag) {
            if (!empty($grouid)) {
                $group = implode(',', $grouid);
                $wheregroup = " and group_id in(" . $group . ")";
            }
            $sql2 = "select user_id,group_id,user_name from users where user_create_time >= '" . $start_At . "' and user_create_time < '" . $stop_Ats . "'" . $wheregroup . "";
        } else {
            if (!empty($grouid)) {
                $group = implode(',', $grouid);
                $wheregroup = " where group_id in(" . $group . ")";
            }
            $sql2 = "select user_id,group_id,user_name from users" . $wheregroup . "";
        }
        $command2 = Yii::$app->db->createCommand($sql2);
        $UserResult = $command2->queryAll();

        // 向表中插入数据
        $Base = new Base();
        if (!empty($UserResult)) {
            foreach ($UserResult as $key => $value) {
                $userMessage = $Base->getUserInRedis($value['user_name']);
                $user_id = $value['user_id'];
                $products_id = $userMessage['products_id'];
                $group_id = $value['group_id'];
                $user_available = $userMessage['user_available'];
                $sql3 = "INSERT INTO tmp_query (user_id,products_id,group_id,user_available) VALUES('" . $user_id . "','" . $products_id . "','" . $group_id . "','" . $user_available . "')";
                $command3 = Yii::$app->db->createCommand($sql3);
                $command3->execute();
            }
        }

        //查询出所有的产品
        $product = new Product();
        $list = $product->getList();
        $systemProduct = array();
        $string = "";
        $maxdata = array();
        foreach ($list as $key => $value) {
            $sql4 = "select count(products_id) as count from tmp_query where products_id = '" . $value['products_id'] . "'";
            $command4 = Yii::$app->db->createCommand($sql4);
            $result = $command4->queryAll();
            $data = $result[0]['count'];

            if ($data > 0) {
                $systemProduct[] = "'" . $value['products_name'] . "'";
                $string = $string . "{value:" . $data . ",name:'" . $value['products_name'] . "'},";
                $maxdata[] = $data;
            }
        }

        $xAxisString = trim($string, ',');
        $yAxisString = implode(',', $systemProduct);
        $max = 0;
        if (!empty($maxdata)) {
            $max = max($maxdata);
        }

        $source = [
            'xAxis' => $xAxisString,
            'yAxis' => $yAxisString,
            'desc' => $model->start_At . '---' . $model->stop_At,
            'max' => $max,
        ];
        return $source;

    }

    //获取指定查询时间的数据
    public function getStatusData($params = "", $fieldArray, $flag = true)
    {
        if (!empty($params)) {
            $start_At = strtotime($params->start_At);
            $stop_At = strtotime($params->stop_At);
        } else {
            $start_At = strtotime(date('Y-m-01'));
            $stop_At = strtotime(date('Y-m-d'));
        }

        $stop_Ats = $stop_At + 60 * 60 * 24;
        $grouid = array_keys($fieldArray);
        $wheregroup = '';

        //先判断表是否存在
        $sqldrop = "show table status like '%tmp_query%'";
        $commandDrop = Yii::$app->db->createCommand($sqldrop);
        $res = $commandDrop->queryAll();
        if (!empty($res)) {
            //先删除表
            $sqldrop = "DROP TABLE `tmp_query`";
            $commandDrop = Yii::$app->db->createCommand($sqldrop);
            $commandDrop->execute();
        }

        //创建临时表
        $sql = "create table tmp_query(id int(11) NOT NULL auto_increment PRIMARY KEY,user_id int(11) default NULL,products_id int(11) default NULL,group_id int(11) default NULL,user_available int(11) default NULL)ENGINE=MEMORY DEFAULT CHARSET=utf8;";
        $command = Yii::$app->db->createCommand($sql);
        $command->execute();

        //从user表中取出所有符合时间的数据
        if ($flag) {
            if (!empty($grouid)) {
                $group = implode(',', $grouid);
                $wheregroup = " and group_id in(" . $group . ")";
            }
            $sql2 = "select user_id,group_id,user_name from users where user_create_time >= '" . $start_At . "' and user_create_time < '" . $stop_Ats . "'" . $wheregroup . "";
        } else {
            if (!empty($grouid)) {
                $group = implode(',', $grouid);
                $wheregroup = "group_id in(" . $group . ")";
            }
            $sql2 = "select user_id,group_id,user_name from users where " . $wheregroup . "";
        }
        $command2 = Yii::$app->db->createCommand($sql2);
        $UserResult = $command2->queryAll();

        // 向表中插入数据
        $Base = new Base();
        if (!empty($UserResult)) {
            foreach ($UserResult as $key => $value) {
                $userMessage = $Base->getUserInRedis($value['user_name']);
                $user_id = $value['user_id'];
                $products_id = $userMessage['products_id'];
                $group_id = $value['group_id'];
                $user_available = $userMessage['user_available'];
                $sql3 = "INSERT INTO tmp_query (user_id,products_id,group_id,user_available) VALUES('" . $user_id . "','" . $products_id . "','" . $group_id . "','" . $user_available . "')";
                $command3 = Yii::$app->db->createCommand($sql3);
                $command3->execute();
            }
        }


        //查询出所有的产品
        $attributes = $Base->getAttributesList();
        $userStatus = $attributes['user_available'];
        $Status = array();
        $string = "";
        $maxdata = array();
        //用户状态
        foreach ($userStatus as $n => $m) {
            $sql4 = "select count(user_id) as count from tmp_query where user_available = '" . $n . "'";
            $command4 = Yii::$app->db->createCommand($sql4);
            $result = $command4->queryAll();
            $data = $result[0]['count'];

            if ($data > 0) {
                $Status[] = "'" . $m . "'";
                $string = $string . "{value:" . $data . ",name:'" . $m . "'},";
                $maxdata[] = $data;
            }

        }

        $xAxisString = trim($string, ',');
        $yAxisString = implode(',', $Status);
        $max = 0;
        if (!empty($maxdata)) {
            $max = max($maxdata);
        }

        $source = [
            'xAxis' => $xAxisString,
            'yAxis' => $yAxisString,
            'desc' => $model->start_At . '---' . $model->stop_At,
            'max' => $max,
        ];
        return $source;
    }

    /**
     * 根据用户名获取产品
     * @param $name
     * @return mixed ['pid'=>'pName', 'pid'=>'pName']
     */
    public function getProductByName($name)
    {
        $products = "";
        $redis_uid = Redis::executeCommand('get', 'key:users:user_name:' . $name);
        $products_id = Redis::executeCommand('LRANGE', 'list:users:products:' . $redis_uid, [0, -1]);
        if ($products_id) {
            $productModel = new Product();
            foreach ($products_id as $pid) {
                $pro = $productModel->getOneName($pid);
                if ($pro) {
                    $products = $pro;
                }
            }
        }
        return $products;
    }

    /**
     * 整理数据
     * @param $data
     * @param array $names
     * @param array $groups
     * @return array
     */
    public function getRsData($data, $names = [], $groups = [])
    {
        $rs = $table = $yAxis = $legends = [];
        foreach ($data as $v) {
            $group = $v['group_id'];
            $product = $v['product_id'];
            $table[$groups[$group]]['data'] = isset($table[$groups[$group]]['data']) ? $table[$groups[$group]]['data'] + $v['num'] : $v['num'];
            $table[$groups[$group]]['detail'][] = $v;
            $yAxis[$product][$group] = isset($yAxis[$product][$group]) ? $yAxis[$product][$group] + $v['num'] : $v['num'];
        }
        foreach ($names as $pro => $name) {
            $legends[] = $pro . ":" . $name;
            foreach ($groups as $group => $groupName) {
                $rs[$groupName]['pro'][] = $pro;
                if (isset($yAxis[$pro][$group])) {
                    $rs[$groupName]['data'][] = $yAxis[$pro][$group];
                } else {
                    $rs[$groupName]['data'][] = 0;
                }
            }
            //var_dump($pro);exit;
        }
        //var_dump($table);exit;

        return ['rs' => $rs, 'legends' => $legends, 'table' => $table];

    }

    /**
     * 打包数据
     * @param $data
     * @return array
     */
    public function getSeries($data)
    {
        $result = [];
        foreach ($data as $key => $value) {
            //循环构造结果集数据
            $object = new \stdClass();
            $object->type = 'line';
            $object->name = \Yii::t('app', $key);
            $object->data = $value;
            $result[] = $object;
        }

        return $result;
    }

    /**
     * 打包数据
     * @param $data
     * @return array
     */
    public function getPieSeries($data, $names)
    {
        $result = [];
        $i = 0;
        foreach ($data as $key => $value) {
            //循环构造结果集数据
            foreach ($value['data'] as $k => $v) {
                $result[$key][] = [
                    'name' => $value['pro'][$k] . ":" . $names[$value['pro'][$k]],
                    'value' => $v
                ];;
            }
            $i++;
        }

        return $result;
    }
}
