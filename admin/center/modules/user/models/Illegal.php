<?php
/**
 * Created by PhpStorm.
 * User: qk
 * Date: 15-9-1
 * Time: 下午5:09
 */

namespace center\modules\user\models;


use center\extend\Tool;
use center\modules\auth\models\SrunJiegou;
use common\models\Redis;
use yii\base\Model;

class Illegal extends Model
{
    const MACAUTHKEY = 'key:users:mac_auth:';

    function getIllegalUsers($list)
    {
        $data = [];
        if ($list) {
            foreach ($list as $v) {
                if($v['user_mac']){
                    $user_name = Redis::executeCommand('GET', self::MACAUTHKEY . strtolower(Tool::format_mac($v['user_mac'])), []);
                    if (!empty($user_name) && $user_name == 'IllegalUser') {
                        $user = Base::findOne(['user_name' => $v['user_name']]);
                        $group = SrunJiegou::getOwnParent($user['group_id']);
                        $data[] = [
                            'user_name' => $v['user_name'],
                            'ip' => $v['user_name'],
                            'mac' => $v['user_mac'],
                            'user_real_name' => $user['user_real_name'],
                            'user_group' => $group,
                            'err_msg' => $v['err_msg'],
                        ];
                    }
                }
            }
        }
        return $data;
    }

    function deleteIllegalUsers($mac)
    {
        if (empty($mac)) {
            return false;
        }
        $data = [
            'action' => 6,
            'serial_code' => time() . rand(111111, 999999), //唯一的流水号
            'time' => time(),
            'user_name' => 'IllegalUser',
            'type' => 1,
            'value' => $mac,
            'operation' => 2,
            'proc' => 'admin',
        ];
        $json = json_encode($data);
        return Redis::executeCommand('RPUSH', "list:interface", [$json]);
    }
}