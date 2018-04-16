<?php
namespace center\models;

use Yii;

/**
 * Signup form
 */
class SendMessageModel extends \yii\db\ActiveRecord
{
    public $phone;
    public $content;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['phone', 'filter', 'filter' => 'trim'],
            ['phone', 'required'],
            ['phone', 'integer'],

            ['content', 'filter', 'filter' => 'trim'],
            ['content', 'required'],
            ['password', 'string', 'min' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'phone' => '手机',
            'content' => '内容',
        ];
    }
}
