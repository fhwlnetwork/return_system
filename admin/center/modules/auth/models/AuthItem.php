<?php

namespace center\modules\auth\models;

use yii;
use yii\helpers\Json;
use center\modules\log\models\LogWriter;

/**
 * This is the model class for table "{{%auth_item}}".
 *
 * @property string $name
 * @property integer $type
 * @property string $description
 * @property string $rule_name
 * @property string $data
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $by_id
 * @property integer $path
 * @property integer $p_id
 * @property AuthAssignment[] $authAssignments
 * @property AuthRule $ruleName
 * @property AuthItemChild[] $authItemChildren
 */
class AuthItem extends ArTransition
{
    //public $updatedAt; //修改时间.
    public $auth_item_type_1 = 1; // 角色
    public $auth_item_type_2 = 2; // 权限
    private $_old = [];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%auth_item}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'description'], 'required'],
            ['name', 'unique'],
            [['type', 'created_at', 'updated_at'], 'integer'],
            [['data'], 'string'],
            [['description'], 'string', 'max' => 10],
            [['name', 'rule_name'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'roles name'),
            'type' => Yii::t('app', 'action type'),
            'description' => Yii::t('app', 'roles description'),
            'path' => Yii::t('app', 'path'),
            'p_id' => Yii::t('app', 'pid'),
            'by_id' => Yii::t('app', '创建人ID'),
            'rule_name' => 'Rule Name',
            'data' => 'Data',
            'created_at' => Yii::t('app', 'created_at'),
            'updated_at' => Yii::t('app', 'updated_at'),
            'permission' => '权限',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthAssignments()
    {
        return $this->hasMany(AuthAssignment::className(), ['item_name' => 'name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRuleName()
    {
        return $this->hasOne(AuthRule::className(), ['name' => 'rule_name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthItemChildren()
    {
        return $this->hasMany(AuthItemChild::className(), ['child' => 'name']);
    }

    public static function getChildDes($child)
    {
        $model = self::findOne(['name' => $child]);

        if ($model) {
            return $model->description;
        } else {
            return '';
        }
    }

    // 获取父级数据.
    public function getParentData($uid, $field)
    {
        $source = AuthItem::getItemName($uid, $field);
        return $source;
    }

    /**
     * 以键值对的形式返回角色或权限数据.
     * @param $type 类型，根据此参数分辨是角色还是权限.
     * @return array
     */
    public static function getItemArray($type)
    {
        $source = static::find()->where(['type' => $type])->all();

        $array = [];
        foreach ($source as $val) {
            $array[$val['name']] = $val['name'];
        }

        return $array;
    }

    /**
     * 根据 id 返回制定字段的值.
     * @param $name
     * @param $field
     * @return mixed
     */
    public static function getItemName($id, $field)
    {
        if ($id == '' || $field == '') {
            return null;
        } else {
            $data = self::find()->where(['id' => $id])->all();
            if($data) {
                return $data[0]['attributes'][$field];
            }
            return null;
        }
    }

    /**
     * 获取只属于自己管辖的角色数据.
     */
    public static function getChildRolesData()
    {
        if (\common\models\User::isSuper()) {
            $childRolesData = AuthItem::find()->where(['type' => 1])->all();
        } else {
            // 获取当期登录用户所在的角色组.
            $array = AuthItem::findAll(['by_id' => Yii::$app->user->id]);
            $AuthAssignmentArr = AuthAssignment::findAll(['user_id' => Yii::$app->user->id]);

            if ($array) {
                $query = new yii\db\Query();
                $query->from(AuthItem::tableName());
                $query->select(['name']);
                $query->where('type = 1');

                foreach ($array as $key => $val) {
                    $newPath = $val['attributes']['path'] . '-' . $val['attributes']['id'];
                    if ($key == 0) {
                        $query->andWhere('path like "' . $newPath . '%"');
                    } else {
                        $query->orWhere('path like "' . $newPath . '%"');
                    }
                }

                $childRolesData = array_merge($query->all(), $array);
            } else {
                $data[0]['name'] = $AuthAssignmentArr[0]['attributes']['item_name'];
                return $data;
            }
        }

        return $childRolesData;
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->by_id = Yii::$app->user->id;
                $this->path = $this->getParentData(Yii::$app->user->id, 'path') . '-' . $this->getParentData(Yii::$app->user->id, 'id');
                $this->p_id = $this->getParentData(Yii::$app->user->id, 'id');
                $this->created_at = $this->updated_at = time();
            } else {
                $this->updated_at = time();
            }
            $this->type = $this->auth_item_type_1; // 默认为角色.
            return true;
        } else {
            return false;
        }
    }


    //日志操作

    /**
     * 写操作日志, 增改.
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        $dirtyArr = LogWriter::dirtyData($this->old($insert), static::getBuildAttributes($this->attributes));

        if (!empty($dirtyArr)) {
            $logData = [
                'operator' => Yii::$app->user->identity->username,
                'target' => $this->name,
                'action' => $insert ? 'add' : 'edit',
                'action_type' => 'Setting Roles',
                'content' => Json::encode($dirtyArr),
                'class' => __CLASS__,
                'type' => 0,
            ];
            LogWriter::write($logData);
        }
    }

    /**
     * 获取更新之前的数据.
     * @param $isNewRecord 检查操作行为 true 为新增 false 为编辑
     * @return array|null 如果新增数据 则返回 null 否则返回该数据编辑之前的原始数据.
     */
    public function old($isNewRecord)
    {
        if ($isNewRecord) {
            return null;
        } else {
            return $this->_old;
        }
    }

    public static function getBuildAttributes($attributes)
    {
        $attributes['p_id'] = $attributes['p_id'] ? ($attributes['p_id'] . ':' . self::getItemName($attributes['p_id'], 'name')) : ''; //上级ID
        $attributes['by_id'] = $attributes['by_id'] ? ($attributes['by_id'] . ':' . UserModel::getItemValue($attributes['by_id'], 'username')) : ''; //上级ID
        return $attributes;
    }

    /**
     * 获取一个用户信息
     * @param mixed $id condition|array
     * @return bool|null|static
     */
    public static function findOne($id)
    {
        //从数据库中查询记录
        $model = parent::findOne($id);
        //将当前记录保存在临时旧数据
        $model->_old = self::getBuildAttributes($model->attributes);
        return $model;
    }

    public function log($oldAttributes, $attributes, $insert)
    {
        $dirtyArr = LogWriter::dirtyData($oldAttributes, $attributes);

        if (!empty($dirtyArr)) {
            $logData = [
                'operator' => Yii::$app->user->identity->username,
                'target' =>  $this->name,
                'action' => $insert ? 'add' : 'edit',
                'action_type' => 'Setting Roles',
                'content' => Json::encode($dirtyArr),
                'class' => __CLASS__,
                'type' => 0,
            ];
            LogWriter::write($logData);
        }
    }

    public function deleteLog($oldAttributes, $attributes)
    {
        $dirtyArr = LogWriter::dirtyData($oldAttributes, $this->getBuildAttributes($attributes));
        if (!empty($dirtyArr)) {
            $logData = [
                'operator' => Yii::$app->user->identity->username,
                'target' =>  $this->name,
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
