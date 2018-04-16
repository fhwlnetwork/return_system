<?php

namespace common\models;

use center\modules\setting\models\Sms;
use common\extend\Tool;

/**
 * Class SRunSms
 * @package common\models
 */
class SrunSms implements SmsInterface
{
    public $error = []; //错误信息

    /**
     * 发送短信
     *
     * @param string $phone
     * @param string $msg
     * @return bool|mixed
     */
    public function send_msg($phone, $msg)
    {
        //获取参数设置
        $setting = Sms::getSetting();
        if (!isset($setting['setting']['name']) || !isset($setting['setting']['token'])) {
            return false;
        }
        $name = trim($setting['setting']['name'], '&');
        $token = trim($setting['setting']['token'], '&');
        $sign = md5($name . $token . $phone); //签名
        $url = 'http://msg.srun.com/message/backend/web/index.php/send/message';
        $post = [
            'name' => $name,
            'phone' => $phone,
            'content' => $msg,
            'sign' => $sign,
        ];
        return Tool::postData($url, http_build_query($post));
    }
}