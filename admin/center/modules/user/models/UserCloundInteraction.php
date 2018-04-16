<?php

namespace center\modules\user\models;

use yii;
use center\modules\setting\models\ExtendsField;

/**
 * This is the model class for table "user_clound_interaction".
 *
 * @property string $id
 * @property string $question_type
 * @property string $question_title
 * @property string $question_description
 * @property string $question_answer
 * @property string $question_pub_at
 * @property string $products_key
 */
class UserCloundInteraction extends \yii\db\ActiveRecord
{
    //默认搜索显示的字段
    public $defaultField = [ 'question_type', 'question_title', 'question_pub_at', 'question_state' , 'question_solution_time','products_key'];
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_clound_interaction';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['question_title','question_answer','question_type'], 'required'],
            [['question_type', 'question_description', 'question_answer'], 'string'],
            [['question_title'], 'string', 'max' => 32],
            [['products_key'], 'string', 'max' => 30],
            [['question_state'], 'integer',],
            [['question_solution_time'], 'integer',],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'question_state' => Yii::t('app', 'question state'),
            'question_type' => Yii::t('app', 'question type'),
            'question_title' => Yii::t('app', 'question title'),
            'question_content' => Yii::t('app', 'question content'),
            'question_description' => Yii::t('app', 'question description'),
            'question_answer' => Yii::t('app', 'question answer'),
            'question_pub_at' => Yii::t('app', 'question publish time'),
            'products_key' => Yii::t('app', 'operate type User Base'),
        ];
    }

    public function getAttributesList()
    {

        return yii\helpers\ArrayHelper::merge([], [
            //用户状态
            'questionTypes' => [
                '1' => Yii::t('app', 'question type1'),
                '2' => Yii::t('app', 'question type2'),
                '3' => Yii::t('app', 'question type3'),
                '4' => Yii::t('app', 'question type4'),
            ],
            //用户状态
            'questionStates' => [
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
        $questionTypes = [Yii::t('app','question type')]+[
                '1' => Yii::t('app', 'question type1'),
                '2' => Yii::t('app', 'question type2'),
                '3' => Yii::t('app', 'question type3'),
                '4' => Yii::t('app', 'question type4'),
            ];
        $questionStates = ['' => Yii::t('app', 'question state')] + [
                '0' => Yii::t('app', 'not resolved'),
                '1' => Yii::t('app', 'resolved'),
            ];
        return yii\helpers\ArrayHelper::merge([
            'products_key' => [
                'label' => Yii::t('app', 'operate type User Base')
            ],
            'question_pub_time' => [
                'label' => Yii::t('app', 'question publish time')
            ],
            'question_type' => [
                'label' => Yii::t('app', 'question type'),
                'list' => $questionTypes,
            ],
            'question_state' => [
                'label' => Yii::t('app', 'question state'),
                'list' => $questionStates,
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
            'question_type' => Yii::t('app', 'question type'),
            'question_content' => Yii::t('app', 'question content'),
            'question_pub_at' => Yii::t('app', 'question publish time'),
            'question_target' => Yii::t('app', 'question target'),
            'question_attach' => Yii::t('app', 'question attach'),
            'hope_process_time' => Yii::t('app', 'hope process time'),
            'products_key' => Yii::t('app', 'operate type User Base'),
            'question_title' => Yii::t('app', 'question title'),
            'question_state' => Yii::t('app', 'question state'),
            'question_solution_time' => Yii::t('app', 'question solution time'),
        ], []);

        return $this->_searchField;
    }

    public function setSearchField($data){
        $this->_searchField = $data;
    }

}
