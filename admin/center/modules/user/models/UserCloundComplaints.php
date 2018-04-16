<?php

namespace center\modules\user\models;

use yii;
use center\modules\setting\models\ExtendsField;

/**
 * This is the model class for table "user_clound_complaints".
 *
 * @property string $id
 * @property string $user_id
 * @property string $bug_type
 * @property string $bug_content
 * @property string $bug_pub_at
 * @property string $bug_target
 * @property string $bug_attach
 * @property string $hope_process_time
 * @property string $products_key
 * @property string $bug_state
 */
class UserCloundComplaints extends \yii\db\ActiveRecord
{
    //默认搜索显示的字段
    public $defaultField = [ 'bug_type', 'bug_title', 'bug_pub_at', 'bug_target', 'hope_process_time', 'bug_state' , 'bug_solution_time','products_key'];
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_clound_complaints';
    }
    public static function model()
    {

    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['bug_title', 'bug_content', 'bug_type', 'hope_process_time'], 'required'],
            [['bug_type', 'bug_content'], 'string'],
            [['bug_target'], 'string', 'max' => 32],
            [['products_key'], 'string', 'max' => 30],
            [['bug_state'], 'integer',],
            [['bug_solution'], 'string',],
            [['bug_solution_time'], 'integer',],
            [['bug_attach'], 'file', 'extensions' => 'jpg, png', 'mimeTypes' => 'image/jpeg, image/png',],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'bug_type' => Yii::t('app', 'bug type'),
            'bug_content' => Yii::t('app', 'bug content'),
            'bug_pub_at' => Yii::t('app', 'bug publish time'),
            'bug_target' => Yii::t('app', 'bug target'),
            'bug_attach' => Yii::t('app', 'bug attach'),
            'hope_process_time' => Yii::t('app', 'hope process time'),
            'products_key' => Yii::t('app', 'operate type User Base'),
            'bug_title' => Yii::t('app', 'bug title'),
            'bug_state' => Yii::t('app', 'bug state'),
            'bug_solution' => Yii::t('app', 'bug solution'),
            'bug_solution_time' => Yii::t('app', 'bug solution time'),
        ];
    }
    public function getAttributesList()
    {
        //获取扩展字段的列表字段
        $exField = ExtendsField::getList();

        return yii\helpers\ArrayHelper::merge($exField, [

            //用户状态
            'bugTypes' => [
                '1' => Yii::t('app', 'bug type1'),
                '2' => Yii::t('app', 'bug type2'),
                '3' => Yii::t('app', 'bug type3'),
                '4' => Yii::t('app', 'bug type4'),
                '5' => Yii::t('app', 'bug type5'),
            ],

            'bugStates' => [
                '0' => Yii::t('app', 'not resolved'),
                '1' => Yii::t('app', 'resolved'),
            ],
        ]);
    }
    /**
     * 要搜索的字段
     * @return array
     */
    public function getSearchInput()
    {
        //扩展字段加入搜索
        $exField = [];
        $bugTypes = [Yii::t('app','bug type')]+[
                '1' => Yii::t('app', 'bug type1'),
                '2' => Yii::t('app', 'bug type2'),
                '3' => Yii::t('app', 'bug type3'),
                '4' => Yii::t('app', 'bug type4'),
                '5' => Yii::t('app', 'bug type5'),
            ];
        $bugStates = ['' => Yii::t('app', 'bug state')] + [
                '0' => Yii::t('app', 'not resolved'),
                '1' => Yii::t('app', 'resolved'),
            ];
        return yii\helpers\ArrayHelper::merge([
            'products_key' => [
                'label' => Yii::t('app', 'operate type User Base')
            ],
            'bug_pub_time' => [
                'label' => Yii::t('app', 'bug publish time')
            ],
            'bug_type' => [
                'label' => Yii::t('app', 'bug type'),
                'list' => $bugTypes,
            ],
            'bug_state' => [
                'label' => Yii::t('app', 'bug state'),
                'list' => $bugStates,
            ],
        ], $exField);
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
            'id' => 'ID',
            'bug_type' => Yii::t('app', 'bug type'),
            'bug_content' => Yii::t('app', 'bug content'),
            'bug_pub_at' => Yii::t('app', 'bug publish time'),
            'bug_target' => Yii::t('app', 'bug target'),
            'bug_attach' => Yii::t('app', 'bug attach'),
            'hope_process_time' => Yii::t('app', 'hope process time'),
            'products_key' => Yii::t('app', 'operate type User Base'),
            'bug_title' => Yii::t('app', 'bug title'),
            'bug_state' => Yii::t('app', 'bug state'),
            'bug_solution_time' => Yii::t('app', 'bug solution time'),
        ], []);

        return $this->_searchField;
    }

    public function setSearchField($data){
        $this->_searchField = $data;
    }
}
