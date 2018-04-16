<?php
namespace center\controllers;

use center\modules\financial\models\CheckoutList;
use center\modules\financial\models\PayList;
use center\modules\financial\models\WaitCheck;
use center\modules\user\models\Base;
use center\modules\user\models\OnlineRadius;
use center\modules\user\models\Users;
use common\extend\Excel;
use common\models\FileOperate;
use common\models\KernelInterface;
use Yii;
use yii\rest\ActiveController;

class LogicController extends ActiveController
{
    public $modelClass = '';

    // 批量停机保号
    // 更改用户状态和停机时间和开机时间，更改结算日期，写入结算队列，然后给用户下线
    /**
     * @param array $user_ids
     * @param integer $num
     * @param string $type
     * @param number $money
     * @return bool
     */
    public static function batchStopByUserId($user_ids,$num,$type,$money){
        set_time_limit(0);
        // 至少要有一个人
        if(empty($user_ids)){
            return false;
        }
        //判断类型是否正确
        if (!in_array($type, ['days', 'months', 'years'])) {
            return false;
        }

        $fail = Yii::t('app', 'failed');
        $success = Yii::t('app', 'success');
        $excel_data = [
            0 => [
                Yii::t('app', 'account'),
                Yii::t('app', 'batch excel result'),
                Yii::t('app', 'batch excel detail'),
            ]
        ];
        foreach ($user_ids as $user_id) {
            $users = Users::findOne(['user_id' => $user_id]);
            if($users){
                // 第一步 更改users字段信息
                $users->user_available = 2;
                $users->user_stop_time = $users->user_stop_time > 0 ? $users->user_stop_time : time();
                if($users->user_start_time){
                    $users->user_start_time = strtotime(date('Y-m-d H:i:s', $users->user_start_time) . ' +' . $num . ' ' . $type);
                }else{
                    $users->user_start_time = strtotime('+' . $num . ' ' . $type);
                }
                $rs1 = $users->save();
                // 第二步 写结算队列
                $checkout_list = [
                    'user_name' => $users->user_name,
                    'products_id' => 0,
                    'checkout_amount' => $money,
                    'user_balance' => $users->balance,
                    'sum_bytes' => 0,
                    'sum_seconds' => 0,
                    'sum_bytes6' => 0,
                    'sum_seconds6' => 0,
                    'type' => CheckoutList::CHECKOUT_OTH,
                ];
                $rs2 = KernelInterface::addCheckoutedList($checkout_list);
                // 第三步 修改用户产品的结算日期
                $wait_checks = WaitCheck::findAll(['user_id' => $users->user_id]);
                $rs3 = [];
                if($wait_checks){
                    foreach ($wait_checks as $wait_check) {
                        $wait_check->checkout_date = strtotime(date('Y-m-d H:i:s', $wait_check->checkout_date) . ' +' . $num . ' ' . $type);
                        $rs3[] = $wait_check->save();
                    }
                }
                // 第四步 dm下线
                $radius_model = new OnlineRadius();
                $rs4 = $radius_model->radiusDrop($users->user_name);
                // 第五步 写pay_list
                $pay_list = new PayList();
                $pay_list->user_name = $users->user_name;
                $pay_list->user_real_name = $users->user_real_name;
                $pay_list->pay_num = $money;
                $pay_list->balance_pre = $users->balance;
                $pay_list->pay_type_id = 1;
                $pay_list->create_at = time();
                $pay_list->mgr_name = Yii::$app->user->identity->username;
                $pay_list->group_id = $users->group_id;
                $rs5 = $pay_list->save();

                // 操作结果输出
                $res = [
                    'first_step' => $rs1,
                    'second_step' => $rs2,
                    'third_step' => $rs3,
                    'fourth_step' => $rs4,
                    'fifth_step' => $rs5,
                    'data_wait_checks' => $wait_checks,
                    'data_users' => $users->toArray(),
                ];
                L(json_encode($res),'batch_stop');
                if(!$rs1 || !$rs2 || !$rs3 || !$rs5){
                    L(json_encode($res),'batch_stop_error');
                }
                // 写入Excel
                $msg = Yii::$app->user->identity->username . '--停机保号 缴费' . $money . '元' . '停机时长:' . $num . $type;
                if(!$rs1 || !$rs2 || !$rs3 || !$rs5){
                    $excel_data[] = [
                        $users->user_name, $fail, $msg
                    ];
                }else{
                    $excel_data[] = [
                        $users->user_name, $success, $msg
                    ];
                }
                unset($users);
                unset($wait_checks);
                unset($res);
            }
        }
        $file =  FileOperate::dir('account') . 'pro_batch_renew' . '_' . date( 'YmdHis' ).'.xls';
        $title = Yii::t('app', 'batch renew');
        //将内容写入excel文件
        Excel::arrayToExcel( $excel_data, $file, $title );
        //设置下载文件session
        Yii::$app->session->set('batch_excel_download_file', $file);
        return true;
    }

    // 将销户用户转移到销户表存储
    // 表结构与users表一致
    // 取消user_name唯一索引限制
    /**
     * @param int $id 用户id
     * @return bool
     */
    public static function transferUserById($id){
        $tr = Yii::$app->db->beginTransaction();
        $sql = "insert into users_history (SELECT * FROM users WHERE users.user_id = $id)";
        $rs1 = Yii::$app->db->createCommand($sql)->execute();
        if ($rs1){
            $users = Base::findOne($id);
            $rs2 = $users->delete();
            if ($rs2){
                $tr->commit();

                return true;
            }else{
                $tr->rollBack();

                return false;
            }
        }else{
            $tr->rollBack();

            return false;
        }
    }
}