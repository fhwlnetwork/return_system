<?php

namespace common\models;

use yii;
use center\modules\setting\models\ExtendsField;

/**
 * This is the model class for table "key".
 *
 * @property string $key_id
 * @property integer $module_id
 * @property integer $used_status
 * @property string $expire_time
 * @property string $mgr_create
 * @property string $key_create_time
 * @property string $used_user_name
 * @property string $key_batch_id
 */
class Key extends \yii\db\ActiveRecord
{
    public $searchFields = ['key_value', 'key_tmpl_id', 'key_status', 'key_expires', 'key_create_time', 'key_batch_id', 'used_account', 'used_time', 'mgr_create', 'intf_res'];
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'key';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['key_tmpl_id', 'used_time','key_status', 'key_expires', 'key_create_time'], 'integer'],
            [['mgr_create', 'used_account'], 'string', 'max' => 50],
            [['key_batch_id'], 'string', 'max' => 14]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'key_id' => 'Key ID',
            'key_value' => Yii::t('app', 'key_value'),
            'key_tmpl_id' => Yii::t('app', 'template name'),
            'key_status' => Yii::t('app', 'use state'),
            'key_expires' => Yii::t('app', 'user expire time'),
            'mgr_create' => Yii::t('app', 'create card operator'),
            'key_create_time' => Yii::t('app', 'create time'),
            'used_time' => Yii::t('app', 'used time'),
            'used_account' => Yii::t('app', 'used user'),
            'key_batch_id' => Yii::t('app', 'key id'),
            'intf_res' => Yii::t('app', 'result')
        ];
    }
     //搜索字段
    private $_searchField = null;

    public function getSearchField()
    {
        if(!is_null($this->_searchField)){
            return $this->_searchField;
        }
        //将扩展字段加入搜索项
        $exFields = [];
        foreach(ExtendsField::getAllData() as $one){
            $exFields[$one['field_name']] = $one['field_desc'];
        }

        $this->_searchField = yii\helpers\ArrayHelper::merge([
            'key_id' => 'Key ID',
            'key_value' => Yii::t('app', 'key_value'),
            'key_tmpl_id' => Yii::t('app', 'template name'),
            'key_status' => Yii::t('app', 'use state'),
            'key_expires' => Yii::t('app', 'user expire time'),
            'mgr_create' => Yii::t('app', 'create card operator'),
            'key_create_time' => Yii::t('app', 'create time'),
            'used_time' => Yii::t('app', 'used time'),
            'used_account' => Yii::t('app', 'used user'),
            'key_batch_id' => Yii::t('app', 'key id'),
            'intf_res' => Yii::t('app', 'used result')
        ], []);

        return $this->_searchField;
    }


    public function getAttributesList($key = null)
    {
        $array = [
            'action_type' => [
                'restfulintf' => Yii::t('app', 'mesage_booking_font10'),
                'script' => Yii::t('app', 'mesage_booking_font8'),
                'hproseintf' => Yii::t('app', 'mesage_booking_font9')
            ],
            'key_status' => [
                0 => Yii::t('app', 'key_status0'),
                1 => Yii::t('app', 'key_status1'),
                2 => Yii::t('app', 'key_status2'),
            ]
        ];
        return $key == null ? $array : $array[$key];
    }

    /**
     * 要搜索的字段
     * @return array
     */
    public function getSearchInput()
    {
        //扩展字段加入搜索
        $exField = [];
        $usedTypes = ['' => Yii::t('app', 'Please Select')] + [
                0 => Yii::t('app', 'key_status0'),
                1 => Yii::t('app', 'key_status1'),
                2 => Yii::t('app', 'key_status2'),
            ];


        return yii\helpers\ArrayHelper::merge([
            'key_value' => [
              'label' => Yii::t('app', 'key_value'),
            ],
            'mgr_create' => [
                'label' => Yii::t('app', 'create card operator')
            ],
            'used_account' => [
                'label' => Yii::t('app', 'used user'),
            ],
            'key_batch_id' => [
                'label' => Yii::t('app', 'key id'),
            ],
            'key_create_time' => [
                'label' => Yii::t('app', 'create time')
            ],
            'key_status' => [
                'label' => Yii::t('app', 'use state'),
                'list' => $usedTypes,
            ],

        ], $exField);
    }

    /**
     * 获取key
     * @param $params
     * @return array
     */
    public function getList($params)
    {
        $query = $this->find();
        if (!empty($params)) {
            $field = $this->attributeLabels();
            $fields = array_keys($field);
            $likes = ['mgr_create', 'used_account', 'key_value'];
            $lumps = ['key_create_time'];
            foreach ($params as $key => $val) {
                if (!empty($val) || preg_match('/^0$/', $val)) {
                    if (in_array($key, $fields)) {
                        $val = trim($val);
                        if (isset($params['exact_tag'])) {
                            $query->andWhere("$key = '{$val}'");
                        } else {
                            if (in_array($key, $likes)) {
                                $v = "%$val%";
                                $query->andWhere("$key LIKE '{$v}'");
                            }
                        }
                        if (in_array($key, $lumps)) {
                            $time = strtotime($val);
                            $query->andWhere("$key >= '{$time}'");
                        }
                        if (in_array($key, ['key_status', 'key_batch_id'])) {
                            $query->andWhere("$key = '{$val}'");

                        }

                    }
                }
            }
        }
        //排序
        if (isset($params['orderBy']) && array_key_exists($params['orderBy'], $this->searchField)) {
            $query->orderBy([$params['orderBy'] => $params['sort'] == 'desc' ? SORT_DESC : SORT_ASC]);
        } else {
            $query->orderBy(['key_create_time' => SORT_DESC]);
        }

        //分页
        //一页多少条
        $offset = isset($params['offset']) && $params['offset'] > 0 ? $params['offset'] : 10;
        $pagination = new yii\data\Pagination([
                'defaultPageSize' => $offset,
                'totalCount' => $query->count(),
            ]
        );
        $count = $query->count();
        $list = [];
    
        if ($count > 0) {
            $list = $query->offset($pagination->offset)
                ->limit($pagination->limit)
                ->asArray()
                ->all();;
        }
        $list = !empty($list) ? array_merge($list, ['count' => $count]) : [];

        return $list;
    }
}
