<?php
/**
 * 自定义用户类，重写can验证方法，超级管理员不做权限判断
 * User: ligang
 * Date: 2015/4/3
 * Time: 20:21
 */

namespace center\models;


use center\modules\auth\models\AuthItemChild;
use yii\web\User;

class CustomUser extends User
{
    private $_access = null;

    public function can($permissionName, $params = [], $allowCaching = true)
    {
        //$super = \common\models\User::isSuper();
        $root = \common\models\User::isRoot();

        //超级管理员不判断权限
        if($root){
            return true;
        }
        if($this->_access==null){
            $userPermission = AuthItemChild::getItemsByUser();
            $this->_access = $userPermission;
        }
        if(in_array($permissionName, $this->_access)){
            return true;
        }
        return false;
        //return parent::can($permissionName, $params, $allowCaching);
    }

    public static function getAuthItems()
    {
        $userPermission = AuthItemChild::getItemsByUser();

        return $userPermission;
    }
}