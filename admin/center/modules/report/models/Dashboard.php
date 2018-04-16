<?php
/**
 * Created by PhpStorm.
 * User: ligang
 * Date: 2015/4/1
 * Time: 17:22
 */

namespace center\modules\report\models;


use yii;
use yii\base\Model;
use yii\db\Query;

class Dashboard extends Model
{

    public function getData()
    {
        /*** 权限控制*/
        $canViewPay = Yii::$app->user->can('financial/pay/list');
        $canViewRefund = Yii::$app->user->can('financial/refund/list');
        $canViewCheck = Yii::$app->user->can('financial/checkout/list');
        //今天开始的时间戳
        $todayUnix = strtotime(date('Y-m-d'));
        //明天的时间戳
        $tomorrowUnix = strtotime(date('Y-m-d').' +1 day');
        //本月开始的时间戳
        $monthUnix = strtotime(date('Y-m').'-1');

        //获取新增用户
        $newUser = (new Query())->from('users')->where(['>=', 'user_create_time', $todayUnix])->count();
        //获取总用户数
        $allUser = (new Query())->from('users')->count();
        //获取过期用户
        $expireUser = (new Query())->from('users')->where(['>', 'user_expire_time', 0])->andWhere(['<=', 'user_expire_time', $todayUnix])->count();
        //已销户
        $deleteUser = (new Query())->from('users_open_log')->where(['=', 'type', 1])->count();
        //今日缴费
        $payToday = $canViewPay ? (new Query())
            ->from('pay_list')
            ->where(['>=', 'create_at', $todayUnix])
            ->sum('pay_num'): [];
        //今日消费
        $checkoutToday = $canViewCheck ? (new Query())
            ->from('checkout_list')
            ->where(['>=', 'create_at', $todayUnix])
            ->sum('spend_num') : [];
        //今日退费
        $refundToday = $canViewRefund ? (new Query())
            ->from('refund_list')
            ->where(['>=', 'create_at', $todayUnix])
            ->andWhere(['type'=>0])
            ->sum('refund_num') :[];
        //本月缴费
        $payMonth = $canViewPay ? (new Query())
            ->from('pay_list')
            ->where(['>=', 'create_at', $monthUnix])
            ->sum('pay_num') : [];;
        //本月消费
        $checkoutMonth = $canViewCheck ? (new Query())
            ->from('checkout_list')
            ->where(['>=', 'create_at', $monthUnix])
            ->sum('spend_num+rt_spend_num') : [];
        //本月退费
        $refundMonth = $canViewRefund ? (new Query())
            ->from('refund_list')
            ->where(['>=', 'create_at', $monthUnix])
            ->andWhere(['type'=>0])
            ->sum('refund_num') : [];
        //当前在线
        $radius = (new Query())->from('online_radius')->count();
        $proxy = (new Query())->from('online_radius_proxy')->count();
        $onlineNum = $radius + $proxy;

        $data = [
            'newUsers' => $newUser,//新用户
            'allUser' => $allUser,//全部
            'expireUser' => $expireUser, //过期
            'deleteUser' => $deleteUser, //销户
            'payToday' => $payToday,//今天缴费
            'checkoutToday' => $checkoutToday, //今日消费
            'refundToday' => $refundToday, //今日退费
            'payMonth' => $payMonth, //本月缴费
            'checkoutMonth' => $checkoutMonth, //本月结算
            'refundMonth' => $refundMonth, //本月退费]
        ];
        /*//今日入流量
        $byteInToday = (new Query())->from('srun_detail')->where(['>=', 'add_time', $todayUnix])->sum('bytes_in', \Yii::$app->db_detail);
        //今日时长
        $longToday = (new Query())->from('srun_detail')->where(['>=', 'add_time', $todayUnix])->sum('time_long', \Yii::$app->db_detail);
        //本月流量
        $byteInMonth = (new Query())->from('srun_detail')->where(['>=', 'add_time', $monthUnix])->sum('bytes_in', \Yii::$app->db_detail);
        //本月时长
        $longMonth = (new Query())->from('srun_detail')->where(['>=', 'add_time', $monthUnix])->sum('time_long', \Yii::$app->db_detail);
        //今日待结算
        $waitCheckout = (new Query())->from('waiting_checkout')->where(['=', 'checkout_date', $tomorrowUnix])->count();*/
        //返回数据
        return [
            'newUsers' => $newUser,//新用户
            'allUser' => $allUser,//全部
            'expireUser' => $expireUser, //过期
            'deleteUser' => $deleteUser, //销户
            'payToday' => $payToday,//今天缴费
            'checkoutToday' => $checkoutToday, //今日消费
            'refundToday' => $refundToday, //今日退费
            'payMonth' => $payMonth, //本月缴费
            'checkoutMonth' => $checkoutMonth, //本月结算
            'refundMonth' => $refundMonth, //本月退费
            /*'onlineNum' => $onlineNum,//在线用户
            'byteInToday' => $byteInToday,//今日入流量
            'longToday' => $longToday, //今日时长
            'byteInMonth' => $byteInMonth, //本月流量
            'longMonth' => $longMonth, //本月时长
            'waitCheckout' => $waitCheckout, //待结算*/
        ];
    }
}