<?php

namespace center\modules\auth\models;

use common\models\User;
use Yii;
use yii\helpers\Json;
use center\modules\log\models\LogWriter;

/**
 * This is the model class for table "{{%auth_item_child}}".
 *
 * @property string $parent
 * @property string $child
 * @property AuthItem $parent0
 * @property AuthItem $child0
 */
class AuthItemChild extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%auth_item_child}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['parent', 'child'], 'required'],
            [['parent', 'child'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'parent' => Yii::t('app', 'operate type Setting Roles'),
            'child' => Yii::t('app', 'auth_power'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParent0()
    {
        return $this->hasOne(AuthItem::className(), ['name' => 'parent']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChild0()
    {
        return $this->hasOne(AuthItem::className(), ['name' => 'child']);
    }

    public static function getUserAssignMent($parent)
    {
        $roles = yii::$app->session['EmpowermenParent'];
        $data = self::findOne(['parent' => $roles, 'child' => $parent]);
        (isset($data)) ? ($msg = 'checked') : ($msg = '');

        return $msg;
    }

    /**
     * 将权限数据添加到 session 中.
     * @param $parent
     */
    public static function pullSessionChildData($parent)
    {
        $itemchild = self::findAll(['parent' => $parent]);
        if ($itemchild) {
            foreach ($itemchild as $val) {
                $type = AuthItem::findOne(['name' => $val['child']])->type;
                if ($type == '2') {
                    $empower_child_data[] = $val['child'];
                }
            }
            Yii::$app->session['empower_child_data'] = $empower_child_data;
        } else {
            Yii::$app->session['empower_child_data'] = '';
        }
    }

    /**
     * 验证角色许可是否已经存在
     */
    public static function validated($name, $description)
    {
        $model = self::findOne(['parent' => $name, 'child' => $description]);
        return $model;
    }

    /**
     * 创建授权
     */
    static public function createEmpowerment($name, $description)
    {
        $auth = Yii::$app->authManager;

        $parent = $auth->createRole($name);
        $child = $auth->createPermission($description);

        $auth->addChild($parent, $child);
    }

    /**
     * 权限选择项是否显示. 作用在 角色组的编辑和添加功能中.
     * @param $permission 权限名称
     * @return bool|string 返回值
     */
    public static function checkPermission($permission)
    {
        if(User::isSuper() == 'root'){
            return true;
        }

        $source = Yii::$app->user->can($permission);

        if ($source === true) {
            return true;
        } else {
            return 'style="display:none";';
        }
    }

    /**
     * 根据管理员id获取权限列表，无参数则获取当前登录管理员的权限列表，如果是超级管理员则不需要获取
     * @param null $id 如果id为null，则默认获取当前登录的管理员
     * @return array ['user/base/add', 'user/base/edit', 'user/base/delete']
     */
    public static function getItemsByUser($id=null)
    {
        $list = [];
        //获取管理员的角色名
        $role = User::getRole($id);
        if($role){
            $list = self::getItemsByRole($role);
        }
        return $list;
    }

    /**
     * 根据角色组id获取此组的所有权限
     * @param $role
     * @return array ['user/base/add', 'user/base/edit', 'user/base/delete']
     */
    public static function getItemsByRole($role)
    {
        $list = [];
        $all = self::findAll(['parent'=>$role]);
        if($all){
            foreach($all as $one){
                $list[] = $one->child;
            }
        }
        return $list;
    }

    /**
     * 判断当前管理员是否可以管理$role的角色
     * @param $role string 角色名称
     * @return bool
     */
    public static function canManageRole($role)
    {
        //判断当前的角色
        if(User::isSuper()){
            return true;
        }
        //根据角色获取拥有的权限
        $roleItem = self::getItemsByRole($role);
        //获取当前登录管理员所拥有的权限列表
        $selfItem = self::getItemsByUser();
        //遍历角色组的权限，判断每个权限是否都在当前登录管理员范围内，如果有不在的，那么返回false
        if($roleItem){
            foreach($roleItem as $item){
                if(!in_array($item, $selfItem)){
                    return false;
                }
            }
        }
        return true;
    }

    public static function deleteLog($oldAttributes, $attributes)
    {
        $dirtyArr = LogWriter::dirtyData($oldAttributes, $attributes);

        if (!empty($dirtyArr)) {
            $logData = [
                'operator' => Yii::$app->user->identity->username,
                'target' =>  $attributes['parent'],
                'action' => 'delete',
                'action_type' => 'Setting Roles',
                'content' => Json::encode($dirtyArr),
                'class' => __CLASS__,
                'type' => 0,
            ];
            LogWriter::write($logData);
        }
    }
}
