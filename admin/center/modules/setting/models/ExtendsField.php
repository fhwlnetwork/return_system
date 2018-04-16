<?php
/**
 * 扩展字段模型类
 */
namespace center\modules\setting\models;

use center\modules\auth\models\RegionGroup;
use center\modules\financial\models\PayList;
use center\modules\log\models\LogWriter;
use center\modules\selfservice\models\Register;
use center\modules\user\models\Base;
use center\modules\financial\models\RefundList;
use yii;
use yii\db\ActiveRecord;

class ExtendsField extends ActiveRecord
{
    //临时数据记录
    private $_tmpOldData = null;

    public static function tableName()
    {
        return 'extends_field';
    }

    public function attributeLabels()
    {
        $labels['table_name'] = Yii::t('app', 'table name');
        $labels['field_name'] = Yii::t('app', 'field name');
        $labels['field_desc'] = Yii::t('app', 'field desc');
        $labels['is_must'] = Yii::t('app', 'is must');
        $labels['can_search'] = Yii::t('app', 'can search');
        $labels['type'] = Yii::t('app', 'field type');
        $labels['value'] = Yii::t('app', 'field value');
        $labels['show_type'] = Yii::t('app', 'show type');
        //$labels['rule'] = Yii::t('app', 'field rule');
        $labels['sort'] = Yii::t('app', 'field sort');
        $labels['default_value'] = Yii::t('app', 'default value');
        $labels['field_hint'] = Yii::t('app', 'field hint');
        return $labels;
    }

    public function rules()
    {
        return [
            [['table_name', 'field_desc', 'field_name', 'is_must'], 'required'],
            ['field_desc', 'validateFieldDesc'],
            ['field_name', 'validateFieldName'],
            [['field_name'], 'match', 'pattern' => '/^[a-zA-Z][a-zA-Z0-9_]{0,49}$/'],
            [['table_name', 'field_name', 'field_desc', 'value', 'default_value', 'field_hint'], 'string'],
            [['is_must', 'can_search', 'sort', 'type', 'show_type'], 'integer'],
        ];
    }

    public function scenarios()
    {
        return [
            'default' => ['table_name', 'field_name', 'field_desc', 'is_must', 'can_search', 'type', 'value', 'show_type', 'sort', 'default_value', 'field_hint'],
        ];
    }

    /**
     * 验证单个表中field_name的唯一性
     * @param $attributes
     * @param $params
     */
    public function validateFieldName($attributes, $params)
    {
        $field = [];
        if ($this->isNewRecord) {
            $field = self::findOne(['table_name'=>$this->table_name, 'field_name'=>$this->field_name]);
        } else {
            if ($this->oldAttributes->field_name != $this->attributes->field_name) {
                //修改了字段名
                $field = self::findOne(['table_name'=>$this->table_name, 'field_name'=>$this->field_name]);
            }
        }

        if (!empty($field)) {
            $this->addError($attributes, Yii::t('app', 'extend add error', ['table'=>$this->table_name, 'field'=>$this->field_name]));
        }
    }
    /**
     * 验证单个表中field_desc的唯一性
     * @param $attributes
     * @param $params
     */
    public function validateFieldDesc($attributes, $params)
    {
        $field = [];
        if ($this->isNewRecord) {
            $field = self::findOne(['table_name'=>$this->table_name, 'field_desc'=>$this->field_desc]);
        } else {
            if ($this->oldAttributes->field_desc != $this->attributes->field_desc) {
                //修改了字段名
                $field = self::findOne(['table_name'=>$this->table_name, 'field_desc'=>$this->field_desc]);
            }
        }
        if (!empty($field)) {
            $this->addError($attributes, Yii::t('app', 'extend add error1', ['table'=>$this->table_name, 'field'=>$this->field_desc]));
        }
    }

    public static function getAttributesList()
    {
        return [
            'table_name' => [
                'users' => Yii::t('app', 'users table'),
                'region_group' => Yii::t('app', 'region_group table'),
                'pay_list' => Yii::t('app', 'pay_list table'),
                'register' => Yii::t('app', 'register'),
                'refund_list' => Yii::t('app', 'refund_list')
            ],
            //是否必须
            'is_must' => [
                '0' => Yii::t('app', 'no'),
                '1' => Yii::t('app', 'yes'),
            ],
            //是否作为搜索项
            'can_search' => [
                '0' => Yii::t('app', 'no'),
                '1' => Yii::t('app', 'yes'),
            ],
            //字段类型
            'type' => [
                '0' => Yii::t('app', 'field type1'),
                '1' => Yii::t('app', 'field type2'),
            ],
            //字段展示方式，只对列举类型起作用
            'show_type' => [
                '0' => Yii::t('app', 'field show type1'),
                '1' => Yii::t('app', 'field show type2'),
            ],
            //系统预设
            'system_save' => [
                'user_email' => Yii::t('app', 'network_user_email'), //邮箱
                'user_address' => Yii::t('app', 'address'), //地址
                'user_cert_type' => Yii::t('app', 'user cert type'), //卡类型
                'user_cert_num' => Yii::t('app', 'cert_num'), //卡号
                'phone' => Yii::t('app', 'phone'), //电话
                //'user_mobile_phone' => Yii::t('app', 'phone'), //电话

                'user_zip_code' => Yii::t('app', 'user_zip_code'), //邮政区号
                'user_student_card' => Yii::t('app', 'user_student_card'), //一卡通号
                'user_bank' => Yii::t('app', 'user_bank'), //用户开户银行
            ],
        ];
    }

    //取出系统预设字段不存在数据库中的字段.
    public static function getFieldDiff($table_name = 'users')
    {
        $system = static::getAttributesList()['system_save']; // 系统预设字段数组.
        $usersColumn = Yii::$app->db->getSchema()->getTableSchema($table_name)->columnNames; //取出 users 表所有字段
        $extends = static::find()->select('field_name')->asArray()->all(); // 取出存储在 扩展字段表里面的字段值

        //将二维数组转换成一位数组.
        if (!empty($extends)) {
            foreach ($extends as $val) {
                $arr[] = $val['field_name'];
            }
        } else {
            $arr = [];
        }

        $haveArr = array_merge($usersColumn, $arr);  // 将 users 表 字段 和 扩展字段表 数据合并
        $res = array_diff(array_keys($system), $haveArr); // 取差集

        return $res;
    }

    //扩展字段数组
    private static $_allData = null;

    public static function getAllData($tableName = 'users')
    {
        self::$_allData = self::find()
            ->andWhere(['table_name' => $tableName])
            ->andWhere(['!=', 'field_type', 2])
            ->orderBy(['sort' => SORT_DESC, 'id' => SORT_ASC])
            ->asArray()
            ->all();

        return self::$_allData;
    }


    public static function getFieldsData($tableName = 'users')
    {
        $data = self::find()
            ->andWhere(['table_name' => $tableName])
            ->andWhere(['!=', 'field_type', 2])
            ->orderBy(['sort' => SORT_DESC, 'id' => SORT_ASC])
            ->asArray()
            ->all();
        $model = self::getModel($tableName);
        if (!empty($model)) {
            $res = [];
            if (!empty($data)) {
                foreach ($data as $k => $one) {
                    if($model->hasAttribute($one['field_name'])) {
                        $res[] = $one;
                    }
                }
            }
        }
        return $res;
    }



    /**
     * 获取列表类的数据
     * @param string $tableName
     * @return array
     */
    public static function getList($tableName = 'users')
    {
        $list = [];
        if (self::getAllData($tableName)) {
            foreach (self::getAllData($tableName) as $one) {
                //如果是数组形式
                $arr1 = [];
                if ($one['type'] == 1) {
                    if (!empty($one['value'])) {
                        $arr1 = explode("\n", $one['value']);
                        if (is_array($arr1)) {
                            foreach ($arr1 as $str) {
                                $arr2 = [];
                                $arr2 = explode(':', $str);
                                if (is_array($arr2) && count($arr2) == 2) {
                                    $list[$one['field_name']][$arr2[0]] = $arr2[1];
                                }
                            }
                        }
                    }
                }
            }
        }
        return $list;
    }


    public function afterFind()
    {
        parent::afterFind();

        $this->_tmpOldData = $this->getAttributes();
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        //写日志
        $this->writeLog($this->_tmpOldData, $this->getAttributes(), $insert ? 'add' : 'edit');
    }

    public function afterDelete()
    {
        parent::afterDelete();

        //写日志
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
        //写日志开始
        $dirtyArr = LogWriter::dirtyData($oldData, $newData);
        if ($dirtyArr) {
            $logData = [
                'operator' => Yii::$app->user->identity->username,
                'target' => $type == 'edit' ? $oldData['field_desc'] : $newData['field_desc'],
                'action' => $type,
                'action_type' => 'Setting ExtendsField',
                'content' => yii\helpers\Json::encode($dirtyArr),
                'class' => get_class($this),
            ];
            LogWriter::write($logData);
        }
        //写日志结束
        return true;
    }

    public static function getFieldNames($tableName){
        $res = [];
        $data = self::getFieldsData($tableName);
        if($data){
            foreach($data as $one){
                $res[] = $one['field_name'];
            }
        }
        return $res;
    }

    /**
     * 扩展字段表以及该操作模型
     * @var array
     */
    private static $tableModel = [
        'users' => ['tableName' => 'users', 'modelName' => 'center\modules\user\models\Base'],
        'pay_list' => ['tableName' => 'users', 'modelName' => 'center\modules\financial\models\PayList'],
        'region_group' => ['tableName' => 'users', 'modelName' => 'center\modules\auth\models\RegionGroup'],
        'register' => ['tableName' => 'users', 'modelName' => 'center\modules\selfservice\models\Register'],
        'refund_list' => ['tableName' => 'refund_list', 'modelName' => 'center\modules\financial\models\RefundList'],
    ];

    /**
     * 获取操作模型
     * @param string $tableName
     * @return string
     */
    private static function getModel($tableName = 'users')
    {
        $model = '';
        if (isset(self::$tableModel[$tableName])) {
             $model = new self::$tableModel[$tableName]['modelName'];
        }

        return $model;
    }
}