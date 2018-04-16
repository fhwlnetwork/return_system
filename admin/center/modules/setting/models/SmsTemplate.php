<?php

namespace center\modules\setting\models;

use Yii;
use yii\bootstrap\Html;
use center\modules\log\models\LogWriter;

/**
 * This is the model class for table "sms_template".
 *
 * @property integer $id
 * @property string $name
 * @property string $join_ali
 * @property integer $created_at
 * @property string $status
 * @property string $content
 * @property string $instructions
 * @property string $is_delete
 */
class SmsTemplate extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sms_template';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'content', 'instructions'], 'required'],
            [['created_at'], 'integer'],
            [['status', 'is_delete'], 'string'],
            [['name'], 'string', 'max' => 32],
            [['join_ali'], 'string', 'max' => 16],
            [['content'], 'string', 'max' => 140],
            [['instructions'], 'string', 'max' => 255],
            [['name'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'join_ali' => Yii::t('app', 'Join Ali'),
            'created_at' => Yii::t('app', 'created at'),
            'status' => Yii::t('app', 'Status'),
            'content' => Yii::t('app', 'operate content'),
            'instructions' => Yii::t('app', 'Instructions'),
            'is_delete' => Yii::t('app', 'is delete')
        ];
    }

    /**
     * @param string $params
     * @return array
     */
    public static function getAttributesLabel($params = '')
    {
        $attribute = [
            'status' => [
                0 => yii::t('app', 'sms help10'),
                1 => yii::t('app', 'sms help11')
            ],
            'is_delete' => [
                0 => yii::t('app', 'sms help8'),
                1 => yii::t('app', 'sms help9')
            ]
        ];

        if (isset($attribute[$params])) {
            return $attribute[$params];
        } else {
            return $attribute;
        }
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->created_at = time();
            }
            return true;
        } else {
            return false;
        }
    }

    private $_tmpOldData;

    public function afterFind()
    {
        parent::afterFind();
        $this->_tmpOldData = $this->getAttributes();
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        $this->writeLog($this->_tmpOldData, $this->getAttributes(), $insert ? 'add' : 'edit');
    }

    public function afterDelete()
    {
        parent::afterDelete();
        $this->writeLog(null, $this->_tmpOldData, 'delete');
    }

    /**
     * 写日志
     * @param $oldData
     * @param $newData
     * @param string $type
     * @return bool
     */
    public function writeLog($oldData, $newData, $type = 'delete')
    {
        //差异数据
        $differences = LogWriter::dirtyData($oldData, $newData);
        if ($differences) {
            $logData = [
                'operator' => Yii::$app->user->identity->username,
                'target' => $type == 'edit' ? $oldData['name'] : $newData['name'],
                'action' => $type,
                'action_type' => 'Setting Template',
                'content' => \yii\helpers\Json::encode($differences),
                'class' => get_class($this),
            ];
            LogWriter::write($logData);
        }
        //写日志结束
        return true;
    }

    /**
     * 验证数据是否可以被删除 页面需要
     * @param $params
     * @return string
     */
    public static function isDelete($params)
    {
        switch ($params) {
            case 0:
                $msg = Html::button(yii::t('app', 'sms help8'), ['class' => 'btn btn-success btn-xs']);
                break;
            case 1:
                $msg = Html::button(yii::t('app', 'sms help9'), ['class' => 'btn btn-primary btn-xs']);
                break;
            default:
                break;
        }

        return $msg;
    }

    /**
     * 数据审核是否通过 页面需要
     * @param $params
     * @return string
     */
    public static function isAudit($params)
    {
        switch ($params) {
            case 0:
                $msg = Html::button(yii::t('app', 'sms help10'), ['class' => 'btn btn-danger btn-xs']);
                break;
            case 1:
                $msg = Html::button(yii::t('app', 'sms help11'), ['class' => 'btn btn-success btn-xs']);
                break;
            default:
                break;
        }

        return $msg;
    }
}
