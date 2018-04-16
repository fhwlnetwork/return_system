<?php

namespace center\modules\product\models;

use Yii;

/**
 * This is the model class for table "work_history".
 *
 * @property string $id
 * @property string $m_id
 * @property string $company_name
 * @property string $company_belong
 * @property string $start_time
 */
class WorkHistory extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'work_history';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['m_id', 'company_belong', 'start_time'], 'integer'],
            [['company_name'], 'string', 'max' => 64],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'm_id' => '管理员id',
            'company_name' => '公司名称',
            'company_belong' => '行业属性',
            'start_time' => '开始时间',
        ];
    }
}
