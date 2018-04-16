<?php
namespace center\modules\log\models;

use yii;
use yii\data\ActiveDataProvider;

class OperateLog extends yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'operate_log';
    }

    public static $searchField = [
        'sysmgr_user_name','optlog_user_name','type','user_name','start_optlog_time','end_optlog_time'
    ];


    /**
     * @param array $add_array 操作日志，插入数据 post数组
     * @return bool
     */
    public function create($add_array = array())
    {
        if ($add_array) {
            foreach ($add_array as $var => $value) {
                if ($this->hasAttribute($var)) {
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
     * $param array $param 操作日志查询条件数组
     * @return list
     */
    public function get_search_list($param = array())
    {
        $query = OperateLog::find()->orderBy(['optlog_time'=>SORT_DESC]);
        if ($param) {
            foreach ($param as $val => $value) {
                if(in_array($val, OperateLog::$searchField) && !empty($value)) {
                    switch ($val) {
                        case 'start_optlog_time':
                            $query->andWhere(['>=', 'optlog_time', strtotime($value)]);
                            break;
                        case 'end_optlog_time':
                            $query->andWhere(['<=', 'optlog_time', strtotime($value)]);
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