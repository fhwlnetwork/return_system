<?php
namespace center\modules\log\controllers;

use center\modules\log\models\System;
use common\models\Redis;
use yii;
use center\controllers\ValidateController;
use yii\data\Pagination;

class SystemController extends ValidateController
{
    public function actionIndex()
    {
        //请求的参数
        $params = Yii::$app->getRequest()->queryParams;
        //如果不输入任何条件，就查询当天的明细
        if (empty($params)) {
            $params["start_log_time"] = date("Y-m-d 00:00:00");
        }

        if(isset($params['offset'])) {
            $offset = $params["offset"];
        } else {
            $offset = 20;
        }

        $model = new System();

        $query = System::find();

        // 从redis中获取用户默认的在线菜单
        $paramKey = 'key:log:system:search:params';
        $paramRedis = Redis::executeCommand('get', $paramKey, [], 'redis_manage');

        //整理要查询数据库的字段
        if( empty($params['showField']) ){
            // 从redis中获取此管理员之前勾选过的字段
            $defaultField = $paramRedis ? yii\helpers\Json::decode($paramRedis) : false;
            $params['showField'] = is_array($defaultField) ? $defaultField : $model->defaultField;
        }
        $sortField = [];
        //无论如何要搜索user_id字段
        //$query->addSelect('id');
        foreach($params['showField'] as $val){
            if(array_key_exists($val, $model->searchField)){
                $query->addSelect($val);
                //将搜索字段压入新数组
                $sortField[$val] = $model->searchField[$val];
            }
        }

        //将记录保存在redis中
        Redis::executeCommand('set', $paramKey, [yii\helpers\Json::encode($params['showField'])], 'redis_manage');

        //重新排序searchField
        $model->searchField = $sortField + $model->searchField;

        //过滤查询条件字段
        foreach($params as $field=>$value){
            if( $value!='' ){
                switch ($field) {
                    case 'start_log_time':
                        $query->andWhere(['>=', 'log_time', strtotime($value)]);
                        break;
                    case 'end_log_time':
                        $query->andWhere(['<', 'log_time', strtotime('+1 days '.$value)]);
                        break;
                    case 'err_msg':
                        $query->andWhere(['like', 'err_msg', $value]);
                        break;
                    default:
                        if( array_key_exists($field, $model->searchField) ){
                            $query->andWhere(['=', $field, $value]);
                        }
                        break;
                }
            }
        }

        //排序
        if( isset($params['orderBy']) && array_key_exists($params['orderBy'], $model->searchField) ){
            $query->orderBy([ $params['orderBy'] => $params['sort'] == 'desc' ? SORT_DESC : SORT_ASC ]);
        } else {
            $query->orderBy([ 'id' => SORT_DESC ]);
        }

        //分页
        $pagination = new Pagination([
            'defaultPageSize' => $offset,
            'totalCount' => $query->count(),
        ]);

        //列表
        $list = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->asArray()
            ->all();
        $list = $model->msgReplace($list);

        return $this->render('index', [
            'model' => $model,
            'list' => $list,
            'pagination' => $pagination,
            'params' => $params,
        ]);
    }
}