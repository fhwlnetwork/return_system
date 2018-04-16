<?php

namespace center\modules\auth\models;

use yii;
use yii\db\ActiveRecord;

/**
 * This is the ActiveRecord Transition.
 */
class ArTransition extends ActiveRecord
{
    public function getAttributesList()
    {
        return [
            'type' => [
                1 => Yii::t('app', 'operate type Setting Roles'),
                2 => Yii::t('app', 'auth_power'),
            ],
        ];
    }

    /**
     * 构建拼装一个供日志完美展示的数据,
     *
     * For example,
     *
     * ~~~
     * $array = ['1' => 'user1', '2' => 'user2'];
     * 日志展示成 1:user1 形式.
     * ~~~
     */
    public static function setBuildLogValue($array)
    {
        $source = '';
        if (!empty($array)) {
            foreach ($array as $key => $val) {
                $source .= $key . ':' . $val . ',';
            }

            return rtrim($source, ',');
        }
    }
}
