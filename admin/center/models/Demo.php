<?php
namespace center\models;

use yii;

class Demo extends yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'test';
    }
}