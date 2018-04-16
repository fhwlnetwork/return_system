<?php
/**
 * Created by PhpStorm.
 * User: cyc
 * Date: 16-8-29
 * Time: 上午11:32
 */

namespace center\modules\user\controllers;

use common\extend\Excel;
use common\models\Redis;
use yii\web\Controller;


class UsersController extends Controller{
    public function actionViewPackageByPid(){
        $fields = [
            0 => [
                '用户名', '套餐id', '套餐购买时间', '套餐过期时间'
            ],
        ];
        $product_id = 8;
        $package_id = 10;
        $billing_id = Redis::executeCommand('HGET', 'hash:package:'.$package_id, ['billing_id']);
        $users = Redis::executeCommand('lrange', 'list:products:'.$product_id, [0, -1]);
        foreach($users as $uid){
            $user_name = Redis::executeCommand('hget', 'hash:users:'.$uid, ['user_name']);
            $userPacList = Redis::executeCommand('LRANGE', 'list:users:products:package:' . $uid . ":" . $product_id, [0, -1]);
            if ($userPacList) {
                foreach ($userPacList as $pacIds) {
                    //$pacIds 新版本的套餐ID中带有:号，后面跟着实例ID
                    //套餐id
                    $pacId = explode(":", $pacIds)[0];
                    $objId = explode(":", $pacIds)[1];
                    if($objId){
                        $buy_time = Redis::executeCommand('HGET', 'hash:users:products:package:' . $uid . ':' . $product_id . ':' . $billing_id . ':' . $objId, ['add_time']);
                        $expire_time = Redis::executeCommand('HGET', 'hash:users:products:package:' . $uid . ':' . $product_id . ':' . $billing_id . ':' . $objId, ['expire_time']);
                        $fields[] = [
                            $user_name, $package_id, date('Y-m-d H:i:s', $buy_time), date('Y-m-d H:i:s', $expire_time)
                        ];
                    }
                }
            }
        }
        //生成excel
        $title = '用户套餐的购买时间';
        $file = $title . '.xls';
        Excel::header_file($fields, $file, $title);exit;
    }
} 