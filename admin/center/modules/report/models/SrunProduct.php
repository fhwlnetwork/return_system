<?php

namespace center\modules\report\models;
use center\modules\strategy\models\Product;
use center\modules\user\models\UserProducts;
use center\modules\user\models\Users;
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
class SrunProduct extends \yii\db\ActiveRecord
{
    public $start_At; //开始时间
    public $stop_At; //截止时间
    public $step; //步长
    public $unit; //时间修饰词
    public $bytes_mb = 1048576; //流量进位 MB
    public $bytes_gb = 1073741824; //流量进位 GB
    public $bytes_limit; //总流量限制
    public $bytes_in_limit; //入流量限制
    public $bytes_out_limit; //出流量限制
    public $ip_area;  //ip区域
    public $nas_ip;
    public $nas_port_start;
    public $nas_port_stop;
    public $login_time_start;
    public $login_time_stop;
    public $login_out_time_start;
    public $login_out_time_stop;
    public $products_arr;

    /**
     * 获取登录时间起始
     * */
    public function getLoginTimeStart(){
        return $this->login_time_start;
    }

    /**
     * 设置登录时间起始
     * */
    public function setLoginTimeStart($time){
        $this->login_time_start = $time;
    }

    /**
     * 获取登录时间结束
     * */
    public function getLoginTimeStop(){
        return $this->login_time_stop;
    }

    /**
     * 设置登录时间结束
     * */
    public function setLoginTimeStop($time){
        $this->login_time_stop = $time;
    }

    /**
     * 获取下线时间结束
     * */
    public function getLoginOutTimeStop(){
        return $this->login_out_time_stop;
    }

    /**
     * 设置下线时间结束
     * */
    public function setLoginOutTimeStop($time){
        $this->login_out_time_stop = $time;
    }

    /**
     * 获取下线时间开始
     * */
    public function getLoginOutTimeStart(){
        return $this->login_out_time_start;
    }

    /**
     * 设置下线时间开始
     * */
    public function setLoginOutTimeStart($time){
        $this->login_out_time_start = $time;
    }

    /**
     * 获取nas_ip
     * */
    public function getNasIp(){
        return $this->nas_ip;
    }

    /**
     * 设置nas_ip
     * */
    public function setNasIp($ip){
        $this->nas_ip = $ip;
    }

    /**
     * 获取ip
     * */
    public function getIpArea(){
        return $this->ip_area;
    }

    /**
     * 设置ip
     * */
    public function setIpArea($ipArea){
        $this->ip_area = $ipArea;
    }

    /**
     *
     * */

    /**
     * 获取nas开始端口
     * */
    public function getNasPortStart(){
        return $this->nas_port_start;
    }

    /**
     * 设置nas结束端口
     * */
    public function setNasPortStart($start){
        $this->nas_port_start = $start;
    }

    /**
     * 获取nas结束端口
     * */
    public function getNasPortStop(){
        return $this->nas_port_stop;
    }

    /**
     * 设置nas结束端口
     * */
    public function setNasPortStop($stop){
        $this->nas_port_stop = $stop;
    }


    /**
     * 获取下行流量限制
     * */
    public function getBytesInLimit(){
        return $this->bytes_in_limit;
    }

    /**
     * 设置下行流量限制
     * */
    public function setBytesInLimit($limit){
        $this->bytes_in_limit = $limit;
    }

    /**
     * 获取上行流量限制
     * */
    public function getBytesOutLimit(){
        return $this->bytes_out_limit;
    }

    /**
     * 设置上行流量限制
     * */
    public function setBytesOutLimit($limit){
        $this->bytes_out_limit = $limit;
    }

    /**
     * 获取总流量限制
     * */
    public function getBytesLimit(){
        return $this->bytes_limit;
    }

    /**
     *设置总流量限制
     * */
    public function setBytesLimit($limit){
        $this->bytes_limit = $limit;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'srun_detail';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
           // [['record_day', 'bytes_in', 'bytes_out', 'bytes_in6', 'bytes_out6', 'products_id', 'billing_id', 'control_id', 'total_bytes', 'time_long'], 'integer'],
            [['login_time_start', 'login_time_stop', 'login_out_time_start','login_out_time_stop','nas_port_start','nas_port_stop','nas_ip','ip_area'], 'safe'],
            [['bytes_limit','bytes_out_limit','bytes_in_limit','nas_port_start','nas_port_stop'],'number'],
            [['ip_area'],'string'],
            [['nas_ip'],'safe'],
            [['products_arr'],'required']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_name' => 'User Name',
            'total_bytes' => 'Total Bytes',
            'time_long' => 'Time Long',
            'product_arr' => 'Product Group',
            'login_time_start' => 'Login Time Start',
            'login_time_stop' => 'Login Time Stop',
            'login_out_time_start' => 'Login Out Time Start',
            'login_out_time_stop' => 'Login Out Time Stop',
            'nas_port_start' => 'Nas Port Start',
            'nas_ip' => 'Nas Ip',
            'ip_area' => 'Ip Area',
            'bytes_limit' => 'Bytes Limit',
            'bytes_out_limit' => 'Bytes Out Limit',
            'bytes_in_limit' => 'Bytes In Limit',
        ];
    }


    public function attributeShowLabels()
    {
        return [
            'products_id' => Yii::t('app', 'products name'),
            'total_bytes' => Yii::t('app', 'total bytes'),
            'user_login_count' => Yii::t('app', 'time count'),
            'time_long' => Yii::t('app', 'time long'),
            'user_name' => Yii::t('app','User Name'),
            'total_bytes' => Yii::t('app','Total Bytes'),
            'time_long' => Yii::t('app','Time Long'),
            'product_arr' => Yii::t('app','Product Group'),
            'login_time_start' => Yii::t('app','Login Time Start'),
            'login_time_stop' => Yii::t('app','Login Time Stop'),
            'login_out_time_start' => Yii::t('app','Login Out Time Start'),
            'login_out_time_stop' => Yii::t('app','Login Out Time Stop'),
            'nas_port_start' => Yii::t('app','Nas Port Start'),
            'nas_ip' => Yii::t('app','Nas Ip'),
            'ip_area' => Yii::t('app','Ip Area'),
            'bytes_limit' => Yii::t('app','Bytes Limit'),
            'bytes_out_limit' => Yii::t('app','Bytes Out Limit'),
            'bytes_in_limit' => Yii::t('app','Bytes In Limit'),
        ];
    }



    //验证输入时间的合理性以及时间不长的合理性
    public function validateField()
    {
        if($this->login_time_start&&$this->login_time_stop){
            $loginTimeStart = strtotime($this->login_time_start);
            $loginTimeStop = strtotime($this->login_time_stop);
            if($loginTimeStart > $loginTimeStop){
                $this->addError($this->login_time_start,'Login Time Start Error 1');
                return false;
            }
        }elseif($this->login_out_time_start&&$this->login_out_time_stop){
            $loginOutTimeStart = strtotime($this->login_out_time_start);
            $loginOutTimeStop = strtotime($this->login_out_time_stop);
            if($loginOutTimeStart > $loginOutTimeStop){
                $this->addError($this->login_out_time_start,'Login Out Time Start Error 1');
                return false;
            }
        }elseif($this->nas_port_start&&$this->nas_port_stop){
            $nasPortStart = $this->nas_port_start;
            $nasPortStop = $this->nas_port_stop;
            if($nasPortStart > $nasPortStop){
                $this->addError($this->nas_port_start,'Nas Port Start Error 1');
                return false;
            }
        }
        return true;
    }

    //获取某一个产品的明细
    public function getProductDetailExcel($product_id){
        $bytesLimit = $this->bytes_limit?$this->bytes_limit:null;
        $bytesInLimit = $this->bytes_in_limit?$this->bytes_in_limit:null;
        $bytesOutLimit = $this->bytes_out_limit?$this->bytes_out_limit:null;
        $ipArea = $this->ip_area?$this->ip_area:null;
        $nasIp = $this->nas_ip?$this->nas_ip:null;
        $nasPortStart = $this->nas_port_start?$this->nas_port_start:null;
        $nasPortStop = $this->nas_port_stop?$this->nas_port_stop:null;
        $loginTimeStart = $this->login_time_start?strtotime($this->login_time_start):null;
        $loginTimeStop = $this->login_time_stop?strtotime($this->login_time_stop):null;
        $loginOutTimeStart = $this->login_out_time_start?strtotime($this->login_out_time_start):null;
        $loginOutTimeStop = $this->login_out_time_stop?strtotime($this->login_out_time_stop):null;
        $query = new Query();
        $query->select(['user_name','count(user_name) as user_login_count','sum(total_bytes) as total_bytes', 'sum(bytes_in) as bytes_in','sum(bytes_out) as bytes_out','sum(time_long) as time_long']);
        $query->from($this->tableName());
        $query->where(['products_id'=>$product_id]);
        $query->andFilterWhere(['in','nas_ip',$nasIp]);
        $query->andFilterWhere(['>=','add_time',$loginTimeStart]);
        $query->andFilterWhere(['<=','add_time',$loginTimeStop]);
        $query->andFilterWhere(['>=','drop_time',$loginOutTimeStart]);
        $query->andFilterWhere(['<=','drop_time',$loginOutTimeStop]);
        $query->andFilterWhere(['>=','total_bytes',$bytesLimit]);
        $query->andFilterWhere(['>=','bytes_in',$bytesInLimit]);
        $query->andFilterWhere(['>=','bytes_out',$bytesOutLimit]);
        $query->andFilterWhere(['like','user_ip',$ipArea]);
        $query->andFilterWhere(['>=','nas_port_id',$nasPortStart]);
        $query->andFilterWhere(['<=','nas_port_id',$nasPortStop]);
        $query->groupBy(['user_name']);
        $data = $query->all();
        $result = [];
        //获取用户的信息
        $userQuery = new Query();
        $userQuery->select(['user_name','user_real_name','cert_num']);
        $userQuery->from(Users::tableName());
        $userQuery->indexBy('user_name');
        $userResult = $userQuery->all();
        //获取运营商账号
        $userProductsTemp = UserProducts::find()
            ->select(['user_name','products_id','mobile_phone'])
            ->asArray()
            ->all();
        $userProduct = [];
        foreach ($userProductsTemp as $key => $value){
            $userProduct[$value['user_name']][$value['products_id']] = isset($value['mobile_phone'])?$value['mobile_phone']:'0000';
        }
        foreach ($data as $key => $value){
            $temp['user_name'] = $value['user_name'];
            $temp['user_real_name'] = $userResult[$value['user_name']]['user_real_name'];
            $temp['cert_num'] = $userResult[$value['user_name']]['cert_num'];
            $temp['user_login_count'] = $value['user_login_count'].Yii::t('app', 'report operate remind20');
            $temp['total_bytes'] = Tool::bytes_format($value['total_bytes']);
            $temp['bytes_in'] = Tool::bytes_format($value['bytes_in']);
            $temp['bytes_out'] = Tool::bytes_format($value['bytes_out']);
            $temp['time_long'] = $this->secondsFormat($value['time_long']);
            if(!$userProduct[$value['user_name']][$product_id]){
                $temp['mobile_phone'] = '0000';
            }else{
                $temp['mobile_phone'] = $userProduct[$value['user_name']][$product_id];
            }
            $result[] = array_values($temp);
            unset($temp);
        }
        return $result;
    }


    //查询明细
    public function getProductDetailData(){
        //获取查询条件
        $bytesLimit = $this->bytes_limit?$this->bytes_limit:null;
        $bytesInLimit = $this->bytes_in_limit?$this->bytes_in_limit:null;
        $bytesOutLimit = $this->bytes_out_limit?$this->bytes_out_limit:null;
        $ipArea = $this->ip_area?$this->ip_area:null;
        $nasIp = $this->nas_ip?$this->nas_ip:null;
        $nasPortStart = $this->nas_port_start?$this->nas_port_start:null;
        $nasPortStop = $this->nas_port_stop?$this->nas_port_stop:null;
        $loginTimeStart = $this->login_time_start?strtotime($this->login_time_start):null;
        $loginTimeStop = $this->login_time_stop?strtotime($this->login_time_stop):null;
        $loginOutTimeStart = $this->login_out_time_start?strtotime($this->login_out_time_start):null;
        $loginOutTimeStop = $this->login_out_time_stop?strtotime($this->login_out_time_stop):null;
        $productArr = $this->products_arr;
        //特殊条件处理
        $query = new Query();
        $query->select(['products_id',
            'count(distinct user_name) as user_count',
            'count(user_name) as user_login_count',
            'sum(total_bytes) as total_bytes',
            'sum(bytes_out) as bytes_out',
            'sum(bytes_in) as bytes_in',
            'sum(time_long) as time_long']);
        $query->from($this->tableName());
        $query->where(['in','products_id',$productArr]);
        $query->andFilterWhere(['in','nas_ip',$nasIp]);
        $query->andFilterWhere(['>=','add_time',$loginTimeStart]);
        $query->andFilterWhere(['<=','add_time',$loginTimeStop]);
        $query->andFilterWhere(['>=','drop_time',$loginOutTimeStart]);
        $query->andFilterWhere(['<=','drop_time',$loginOutTimeStop]);
        $query->andFilterWhere(['>=','total_bytes',$bytesLimit]);
        $query->andFilterWhere(['>=','bytes_in',$bytesInLimit]);
        $query->andFilterWhere(['>=','bytes_out',$bytesOutLimit]);
        $query->andFilterWhere(['like','user_ip',$ipArea]);
        $query->andFilterWhere(['>=','nas_port_id',$nasPortStart]);
        $query->andFilterWhere(['<=','nas_port_id',$nasPortStop]);
        $query->groupBy(['products_id']);
        $data = $query->all();
        $resultArray = array();
        $product = new Product();
        foreach($data as $key => $value){
            $productInfo = $product->getProOne($value['products_id']);
            $resultArray[$value['products_id']]['products_name'] = $productInfo['products_name'];
            $resultArray[$value['products_id']]['products_id'] = $value['products_id'];
            $resultArray[$value['products_id']]['user_login_count'] = $value['user_login_count'].Yii::t('app', 'report operate remind20');
            $resultArray[$value['products_id']]['bytes_in'] = Tool::bytes_format($value['bytes_in']);;
            $resultArray[$value['products_id']]['bytes_out'] = Tool::bytes_format($value['bytes_out']);
            $resultArray[$value['products_id']]['user_count'] = $value['user_count'];
            $resultArray[$value['products_id']]['total_bytes'] = Tool::bytes_format($value['total_bytes']);
            $resultArray[$value['products_id']]['time_long'] = $this->secondsFormat($value['time_long']);
            $resultArray[$value['products_id']]['bytes_in6'] = Tool::bytes_format($value['bytes_in6']);
            $resultArray[$value['products_id']]['bytes_out6'] = Tool::bytes_format($value['bytes_out6']);
        }
        return $resultArray;
    }





    public static function secondsFormat($second)
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
