<?php
/**
 * Created by PhpStorm.
 * User: DM
 * Date: 17/4/19
 * Time: 16:56
 */

namespace center\modules\log\models;

use common\extend\Export\CsvExport;
use yii\data\Pagination;
use yii\db\ActiveRecord;
use yii\db\Query;
use Yii;

class LogCdr extends ActiveRecord
{
    public function getTodayCDR($get){
        $svc_type = $get['svc_type'];
        $caller = $get['caller'];
        $callee = $get['callee'];
        $start_time = $get['start_time'];
        $end_time = $get['end_time'];
        $result = $get['result'];
        $user_name = $get['user_name'];

        $export = $get['export'];

        // 今天凌晨
        $today_begin = strtotime(date('Y-m-d'));
        $today_end = time();

        $query = self::find();
        if($svc_type) $query->where(['svc_type' => $svc_type]);

        if(!$user_name){
            if($caller) $query->andWhere(['caller' => $caller]);
            if($callee) $query->andWhere(['callee' => $callee]);
        }else{
            $query->andWhere(['or',"caller='$user_name'","callee='$user_name'"]);
        }

        if($start_time) $query->andWhere(['>=','start_time',strtotime($start_time)]);
        if($end_time) $query->andWhere(['<=','end_time',strtotime($end_time)]);
        if($result){
            if($result == 1){
                $query->andWhere(['result' => 'success']);
            }else{
                $query->andWhere(['result' => 'fail']);
            }
        }
        if($export == 'csv'){
            $rs = $query->orderBy('id desc')->asArray()->all();
            export_csv($rs);
        }
        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count()]);
        $models = $query->offset($pages->offset)
            ->limit($pages->limit)
            ->orderBy('id desc')
            ->all();

        $col = Yii::$app->db->createCommand("select column_name,column_comment from information_schema.columns where table_schema ='srun4k' and table_name = 'log_cdr'")->queryAll();

        $re['models'] = $models;
        $re['pages'] = $pages;
        $re['col'] = $col;
        $re['get'] = $get;

        return $re;
    }
    public function getTenCdr($user_name){
        $models = self::find()
            ->where(['or',"caller='$user_name'","callee='$user_name'"])
            ->limit(10)
            ->orderBy('id desc')
            ->asArray()
            ->all();

        $col = Yii::$app->db->createCommand("select column_name,column_comment from information_schema.columns where table_schema ='srun4k' and table_name = 'log_cdr'")->queryAll();

        $re['models'] = $models;
        $re['col'] = $col;

        return $re;
    }
}