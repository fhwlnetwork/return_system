<?php
namespace center\modules\log\models;

use yii;
use yii\data\ActiveDataProvider;

class SrunLoginLog extends yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'srun_login_log';
    }

    public static $searchField = [
        'user_name','user_ip','user_mac','err_msg','start_login_time','end_login_time'
    ];
    /**
     * @param array $add_array 认证日志，插入数据 post数组
     * @return bool
     */
    public function create($add_array = array())
    {
        if($add_array)
        {
            foreach ($add_array as $var => $value)
            {
                if ($this->hasAttribute($var))
                {
                    $this->$var = $value;
                }
            }
            //insert
            if ($this->save()) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * $param array $param 认证日志查询条件数组
     * @return list
     */
    public function get_search_list($param = array())
    {
        $query = SrunLoginLog::find()->orderBy(['log_time'=>SORT_DESC]);
        if ($param) {
            foreach ($param as $val => $value) {
                if(in_array($val, SrunLoginLog::$searchField) && !empty($value)) {
                    switch ($val) {
                        case 'start_login_time':
                            $query->andWhere(['>=', 'log_time', strtotime($value)]);
                            break;
                        case 'end_login_time':
                            $query->andWhere(['<=', 'log_time', strtotime($value)]);
                            break;
                        default:
                            $query->andWhere(['=', $val, $value]);
                            break;
                    }
                }
            }
        }
        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => isset($param['perPage'])?$param['perPage']:20,// 每页数量
            ],
        ]);
    }
}