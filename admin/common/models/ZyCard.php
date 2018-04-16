<?php
namespace common\models;

use Yii;
use soapclient;
use SoapHeader;

//header("Content-type:text/html;charset=utf-8");
class ZyCard {
    private  $conf;

    //配置信息
    public $config;
    private $sid = false;
    private $client = false;

    /**
     * @name 初始化
     */
    public function __construct(){
        $this->conf = '/srun3/etc/wlan_card.conf';
        $config = [];
        if (file_exists($this->conf)) {
            $config = parse_ini_file($this->conf);
        }
        $this->config = $config;
        $this->client = new soapclient($this->config['wsdl_url']);
        $sid = $this->client->SignIn(array('Param'=>$this->config['site_ip']));
        $this->sid = $sid->SIKey;
    }

    /**
     * 消费
     * @param $user_id
     * @param $password
     * @param $amount
     * @param int $nWalletNum
     * @param int $uIDType
     * @param bool $IsNeedPwd
     * @return string
     */
    public function Payment( $user_id, $password, $amount, $nWalletNum = 1,$uIDType = 2,$IsNeedPwd = true ){
        if(empty($user_id)){
            return $this->show_msg(101);
        }
        if(empty($amount)){
            return $this->show_msg(104);
        }
        if(empty($password)){
            return $this->show_msg(105);
        }
        if(empty($nWalletNum)){
            return $this->show_msg(102);
        }
        //第二步验证用户帐号余额
        $user_info = $this->getcardinfo($user_id,$uIDType);
        if($user_info->GetAccInfoResult == 1){
            $user_balance = $this->UserBalance($user_info->AccNum,$nWalletNum,$uIDType);
            if($user_balance->GetAccDBMoneyResult == 1){
                if($user_balance->nMoney <= 0 || $user_balance->nMoney < $amount){
                    return $this->show_msg(106);
                }
                //第三部扣费

                $param = array(
                    'uID'           =>  $user_info->AccNum,
                    'uIDType'       =>  0,
                    'nWalletNum'    =>  $nWalletNum,
                    'MonDeal'       =>  "-".$amount,
                    'IsNeedPwd'     =>  $IsNeedPwd,
                    'nPayPwd'       =>  $IsNeedPwd === false ? "" : $password
                );
                $result = $this->CallFunction("Payment",$param);
                if($result->PaymentResult != 1){
                    //支付失败
                    return $this->show_msg(111,$result->PaymentResult);
                }else{
                    return $this->show_msg(100,$result->nRecID);
                }
            }
            else{
                return $this->show_msg(108,$user_info->GetAccDBMoneyResult);
            }
        }
        else{
            return $this->show_msg(107,$user_info->GetAccInfoResult);
        }
    }

    /**
     * 交易冲正
     * @param $user_id
     * @param $MonDeal
     * @param $nFromRecID
     * @param int $uIDType
     * @param int $nWalletNum
     * @return string
     */
    public function Corrected( $user_id, $MonDeal, $nFromRecID, $uIDType = 2, $nWalletNum = 1 ){
        if(empty($user_id)){
            return $this->show_msg(101);
        }
        if(empty($nWalletNum)){
            return $this->show_msg(102);
        }
        //第一步 获取用户信息
        $user_info = $this->getcardinfo($user_id,$uIDType);

        if($user_info->GetAccInfoResult == 1){
            //冲正到用户
            $param = array(
                'uID'		=>	$user_id,
                'uIDType'	=>	$uIDType,
                'nWalletNum'=>	$nWalletNum,
                'MonDeal'	=>	$MonDeal,
                'nFromRecID'=>	$nFromRecID
            );
            return $this->CallFunction("Corrected", $param);
        }
        else{
            return $this->show_msg(107,$user_info->GetAccInfoResult);
        }
    }


    /**
     * @param $AccNum
     * @param $nEWalletNum 钱包号，主钱包为1，副钱包为2
     * @param $uIDType
     * @return string
     */
    private function UserBalance($AccNum,$nEWalletNum,$uIDType){
        if(empty($AccNum)){
            return $this->show_msg(101);
        }
        if($nEWalletNum != 1 && $nEWalletNum != 2){
            return $this->show_msg(102);
        }
        $param = array(
            'nAccNum'       =>  $AccNum,
            'nEWalletNum'   =>  $nEWalletNum
        );
        return $this->CallFunction("GetAccDBMoney",$param);
    }

    /**
     * @name 获取用户账户信息
     * @param $user_id
     * @param int $uIDType
     * @return string
     */
    private function getcardinfo( $user_id, $uIDType ){
        if(!$user_id){
            return $this->show_msg(101);
        }

        if($uIDType != 1 && $uIDType != 2 && $uIDType != 0){
            return $this->show_msg(103);
        }
        $param = array(
            'uID'       =>  $user_id,
            'uIDType'   =>  $uIDType
        );

        return $this->CallFunction("GetAccInfo",$param);
    }

    /**
     * @name 获取用户交易流水信息
     * @param $user_id  string 用户帐号/卡号/编号
     * @param $uIDType  int    用户类型
     * @param $sTime    date   开始时间
     * @param $eTime    date   结束时间
     * @return array
     */
    /*public function _GetUserBooks( $user_id, $uIDType = 2, $sTime, $eTime ){
        if(empty($user_id)){
            return $this->show_msg(101);
        }
        if($uIDType != 1 && $uIDType != 2 && $uIDType != 0){
            return $this->show_msg(103);
        }
        if(empty($sTime)){
            return $this->show_msg(109);
        }
        if(empty($eTime)){
            return $this->show_msg(110);
        }
        $user_info = $this->getcardinfo($user_id,$uIDType);
        $param = array(
            'nAccNum'       =>  $user_info->AccNum,
            'sTime'         =>  $sTime,
            'eTime'         =>  $eTime
        );
        $result = $this->CallFunction("GetPaymentBooks",$param);
        return $result;
    }*/

    /**
     * @name 自定义调用方法
     * @param $action 调用的方法名
     * @param $param  参数
     */
    private function CallFunction($action,$param){
        $header = new SoapHeader('http://192.168.7.374/zytk_webService',
            'SecurityHeader',
            array('SID'=>$this->sid));
        $this->client->__setSoapHeaders($header);
        return $this->client->$action($param);
    }

    /**
     * 返回错误
     * @param $id
     * @param string $msg
     * @return string
     */
    private function show_msg($id,$msg = ''){
        switch($id){
            case 100:
                $msg = empty($msg) ? "success" : $msg;
                break;
            case 101:
                $msg = empty($msg) ? "用户帐号不能为空！" : $msg;
                break;
            case 102:
                $msg = empty($msg) ? "钱包号错误！" : $msg;
                break;
            case 103:
                $msg = empty($msg) ? "用户类型错误！" : $msg;
                break;
            case 104:
                $msg = empty($msg) ? "充值金额错误！" : $msg;
                break;
            case 105:
                $msg = empty($msg) ? "交易密码不能为空！" : $msg;
                break;
            case 106:
                $msg = empty($msg) ? "用户卡户余额不足！" : $msg;
                break;
            case 107:
                $msg = empty($msg) ? "查询卡户信息出错" : $msg;
                break;
            case 108:
                $msg = empty($msg) ? "查询卡户余额出错" : $msg;
                break;
            case 109:
                $msg = empty($msg) ? "查询条件：‘开始时间’不能为空！" : $msg;
                break;
            case 110:
                $msg = empty($msg) ? "查询条件：‘结束时间’不能为空！" : $msg;
                break;
        }
        return json_encode ( array (
            'id' => $id,
            'msg' => $msg
        ) );
    }

    // 递归转为UTF-8编码,也可UTF-8转为别的编码
    public function get_utf8($array, $type = 'utf-8') {
        if (! empty ( $array )) {
            if (is_array ( $array )) {
                foreach ( $array as $k => $v ) {
                    if (is_array) {
                        $arr [$k] = self::get_utf8 ( $v, $type );
                    } else {
                        if ($type == 'utf-8') {
                            $arr [$k] = iconv ( 'gb2312', 'utf-8', $v );
                        } else {
                            $arr [$k] = iconv ( 'utf-8', $type, $v );
                        }
                    }
                }
            } else {
                if ($type == 'utf-8') {
                    $arr = iconv ( 'gb2312', 'utf-8', $array );
                } else {
                    $arr = iconv ( 'utf-8', $type, $array );
                }
            }
        }
        return $arr;
    }



}