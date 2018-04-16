<?php
/**
 * 短信接口
 * User: ligang
 * Date: 2015/5/11
 * Time: 12:29
 */

namespace common\models;


interface SmsInterface
{
    /**
     * 发送短信接口
     * @param $phone string 手机号码
     * @param $msg string 短信内容
     * @return mixed
     */
    public function send_msg($phone, $msg);
}