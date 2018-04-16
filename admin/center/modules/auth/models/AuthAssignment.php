<?php

namespace center\modules\auth\models;

use Yii;
use common\models\UserModel as UserModels;
use yii\helpers\Json;
use center\modules\log\models\LogWriter;

/**
 * This is the model class for table "{{%auth_assignment}}".
 *
 * @property string $item_name
 * @property string $user_id
 * @property integer $created_at
 *
 * @property AuthItem $itemName
 */
class AuthAssignment extends ArTransition
{
    private $_old = [];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%auth_assignment}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['item_name', 'user_id'], 'required'],
            [['created_at'], 'integer'],
            [['item_name', 'user_id'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'item_name' => Yii::t('app', 'roles name'),
            'user_id' => Yii::t('app', 'user id'),
            'created_at' => Yii::t('app', 'created_at'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemName()
    {
        return $this->hasOne(AuthItem::className(), ['name' => 'item_name']);
    }

    public function validate($attributeNames = NULL, $clearErrors = true)
    {
        $model = self::find()->where(['item_name' => $this->item_name, 'user_id' => $this->user_id])->all();

        if ($model):
            $this->addError('user_id', Yii::t('app', 'account {name} already exists', ['name' => UserModels::getUserName($this->user_id)]));
            return false;
        else:
            return true;
        endif;
    }

    /**
     * 返回所有管理员名称, 根据参数.
     * @param $UserId 管理员ID.
     * @return string
     */
    public static function getItemNameData($UserId)
    {
        $array = self::find()->where(['user_id' => $UserId])->all();
        $ItemName = '';

        if (!empty($array)) {
            foreach ($array as $val) {
                $ItemName .= $val['item_name'];
            }
        } else {
            $ItemName = '无';
        }

        return $ItemName;
    }

    public function beforeSave($insert)
    {
        if ($insert) {
            $this->created_at = time();
            return true;
        } else {
            return true;
        }
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        $dirtyArr = LogWriter::dirtyData($this->old($insert), self::getBuildAttributes($this->attributes));

        if (!empty($dirtyArr)) {
            $logData = [
                'operator' => Yii::$app->user->identity->username,
                'target' => $this->item_name,
                'action' => $insert ? 'add' : 'edit',
                'action_type' => 'Setting Assign',
                'content' => Json::encode($dirtyArr),
                'class' => __CLASS__,
                'type' => 0,
            ];
            LogWriter::write($logData);
        }
    }

    public function afterDelete()
    {
        parent::afterDelete();
        $dirtyArr = LogWriter::dirtyData(null, $this->getBuildAttributes($this->attributes));
        if (!empty($dirtyArr)) {
            $logData = [
                'operator' => Yii::$app->user->identity->username,
                'target' => $this->item_name,
                'action' => 'delete',
                'action_type' => 'Setting Assign',
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

        if ($model) {
            //将当前记录保存在临时数组中.
            $model->_old = self::getBuildAttributes($model->attributes);
            return $model;
        }
    }
}
