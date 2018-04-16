<?php

namespace center\modules\product\models;

use center\modules\log\models\LogWriter;
use Yii;
use yii\helpers\Json;

/**
 * This is the model class for table "major".
 *
 * @property string $id
 * @property string $major_name
 * @property string $ctime
 */
class Major extends \yii\db\ActiveRecord
{
    public $_temOldAttr = [];
    public $_mgrName;
    public static function getMajor()
    {
        $majores = Major::find()->indexBy('id')->asArray()->all();
        $m_list = ['' => '请选择'];
        foreach ($majores as $id => $majore) {
            $m_list[$id] = $majore['major_name'];
        }

        return $m_list;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'major';
    }

    public function beforeSave($insert)
    {
        if ($insert) {
            $this->ctime = time();
        }

        return parent::beforeSave($insert); // TODO: Change the autogenerated stub
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ctime'], 'integer'],
            [['major_name'], 'required'],
            [['major_name'], 'string', 'max' => 64],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'major_name' => '专业名称',
            'ctime' => '创建时间',
        ];
    }

    /**
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        //记录日志
        parent::afterSave($insert, $changedAttributes); // TODO: Change the autogenerated stub
        //写日志开始 获取脏数据
        $dirtyArr = LogWriter::dirtyData($this->_temOldAttr, $this->getCurrentData());
        if (!empty($dirtyArr)) {
            $logData = [
                'operator' => $this->getMgrName(),
                'target' => $this->major_name,
                'action' => $insert ? 'add' : 'edit',
                'action_type' => 'Major ' . ($insert ? 'add' : 'edit'),
                'content' => Json::encode($dirtyArr),
                'class' => __CLASS__,
                'type' => 0,
            ];
            LogWriter::write($logData);
        }
    }

    /**
     * 获取操作的管理员
     * @return mixed
     */
    public function getMgrName()
    {
        if ($this->_mgrName == '') {
            $this->setMgrName();
        }
        return $this->_mgrName;
    }

    /**
     * 设置管理员姓名
     * @param $name null|string
     * @return string
     */
    public function setMgrName($name = null)
    {
        if (is_null($name)) {
            $this->_mgrName = Yii::$app->user->identity->username;
        } else {
            $this->_mgrName = $name;
        }
    }

    /**
     * 获取当前的日志需要记录的值
     * @return array
     */
    public function getCurrentData()
    {
        //获取扩展字段
        /**
         * 要记录日志的普通字段，数据表字段以及扩展字段都在监控字段内。
         * 并非所有字段都需要记录，比如更新时间、创建人不需要记录，操作日志的作用是便于管理员排错，记录必要的信息即可。
         */
        $normalField = yii\helpers\ArrayHelper::merge([
            'major_name', 'ctime'
        ], []);
        //var_dump($this->hasAttribute('user_available'));
        $list = [];
        //给普通字段赋值
        foreach ($normalField as $field) {
            if ($this->hasAttribute($field)) {
                $list[$field] = $this->$field;
            }
        }
        //返回所有需要记录的字段值
        return yii\helpers\ArrayHelper::merge($list, []);
    }

    public function getAttributesList()
    {
        return yii\helpers\ArrayHelper::merge([], [
            'ctime' => '时间',
            'major_name' => '专业名称',
        ]);
    }
}
