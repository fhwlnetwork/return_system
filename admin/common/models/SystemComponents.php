<?php
namespace common\models;

use Yii;

class SystemComponents extends \yii\db\ActiveRecord
{

    /**
     * 返回操作信息 比如成功 或者是 失败
     */
    public static function backUserOperateMessage($item)
    {

        switch ($item) {
            case 'add_user_success':
                $item = "添加用户成功";
                break;
            case 'update_user_success':
                $item = '更新用户成功';
                break;
            case 'delete_user_success':
                $item = '删除用户成功';
                break;
            case 'delete_tpl_success':
                $item = '删除短信模板成功';
                break;
            case 'add_tpl_success':
                $item = '添加短信模板成功';
                break;
            case 'update_tpl_success':
                $item = '添加短信模板成功';
                break;
            case 'update_password_success':
                $item = '更新密码成功';
                break;
            case 'update_password_error':
                $item = '更新密码失败';
                break;
            case 'user_assign_success':
                $item = '用户赋予角色组成功';
                break;
            case 'auit_alluser_success':
                $item = '一键审核操作成功';
                break;
            case 'add_permission_success':
                $item = '添加权限成功';
                break;
            case 'send_message_success':
                $item = '短信发送成功';
                break;
            case 'upate_permission_success':
                $item = '权限编辑成功';
                break;
            default:
                $item = '未知';
                break;
        }

        return $item;
    }

    /**
     * 返回用户组信息.
     * @return array
     */
    /*public static function getUserGroup()
    {
        return [
            '1' => 'test组',
            '2' => '教工组',
            '3' => '学生组',
            '4' => '家属区',
            '5' => '未注册教工',
            '6' => '未注册学生',
            '7' => '无证件号教工组',
            '8' => '设备',
            '9' => '教工子帐号组',
        ];
    }*/

    public static function getUserGroupValue($group_id)
    {
        $group = static::getUserGroup();
        return $group[$group_id];
    }

    public static function getColorClass($tag, $rand)
    {
        $arr = [
            //扩展字段
            'extends-field' => [
                '0' => 'label label-default',
                '1' => 'label label-primary',
                '2' => 'label label-success',
                '3' => 'label label-info',
                '4' => 'label label-warning',
                '5' => 'label label-danger',
            ],
            'default' => [
                '0' => 'label label-default',
            ],
        ];

        switch ($tag) {
            case 'extends-field':
                return $arr['extends-field'][$rand];
                break;
            default:
                return $arr['default'];
                break;
        }
    }

    public static function smsSwitch()
    {
        return [
            '0' => '关闭',
            '1' => '正常',
        ];
    }

    /**
     * 对字符串进行混编, 返回组合后的字符串.
     * @param $filed
     * @param $data
     * @return mixed
     */
    public static function searchFiled($filed, $data)
    {
        return str_replace(strtolower($filed), '<strong><font color="red">' . strtolower($filed) . '</font></strong>', strtolower($data));
    }
}
