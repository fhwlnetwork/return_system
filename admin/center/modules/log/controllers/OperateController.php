<?php
namespace center\modules\log\controllers;

use center\modules\auth\models\SrunJiegou;
use center\modules\log\models\Operate;
use center\modules\user\models\Base;
use common\extend\Excel;
use common\models\FileOperate;
use common\models\User;
use Yii;
use yii\data\Pagination;
use yii\db\Connection;
use yii\helpers\Json;
use yii\helpers\VarDumper;
use yii\i18n\MessageSource;
use center\controllers\ValidateController;

class OperateController extends ValidateController
{
    const LOG_EXPORT_LIMIT = 10000; //允许最大导出条数
    public function actionIndex()
    {
        //请求的参数
        $params = Yii::$app->getRequest()->queryParams;
        $post = Yii::$app->request->post();
        $params = (!empty($params)) ? $params : $post;
        $export = (!empty($params) && isset($params['export'])) ? true : false;

        $model = new Operate();

        $query = Operate::find();
        //操作表
        $tableName = Operate::tableName();
        //用户表
        $tableUser = Base::tableName();

        $query->addSelect($tableName.'.*');

        //过滤查询条件字段
        foreach($params as $field=>$value){
            if( $value!='' ){
                switch ($field) {
                    case 'start_opt_time':
                        $query->andWhere(['>=', $tableName.'.opt_time', strtotime($value)]);
                        break;
                    case 'end_opt_time':
                        $query->andWhere(['<', $tableName.'.opt_time', strtotime($value)]);
                        break;
                    default:
                        if( array_key_exists($field, $model->searchField) ){
                            $query->andWhere(['=', $tableName.'.'.$field, $value]);
                        }
                        break;
                }
            }
        }

        //last记录
        if(isset($params['last_id'])){
            $query->andWhere(['<', $tableName.'.id', intval($params['last_id'])]);
        }

        //非超级管理员
        if(!User::isSuper()){
            // 只能查看可管理的管理员操作的数据

            // SELECT * FROM `log_operate`
            // left join `users` on log_operate.target=users.user_name
            // WHERE log_operate.target='user1'
            // and (
            //   (log_operate.action_type='User Base' and `users`.group_id in (1, 2, 3) )
            //   or (log_operate.action_type!='User Base')
            // )
            // and log_operate.operator in ('admin1', 'admin2')

            $query->leftJoin($tableUser, $tableUser.'.user_name'.'='.$tableName.'.target');
            //可以管理的组织结构
            $canMgrOrg = SrunJiegou::getAllNode();
            $query->andWhere([
                'or',
                ['and', [$tableName.'.action_type'=>['User Base', 'User Self'], $tableUser.'.group_id'=>$canMgrOrg] ],
                ['and', ['!=', $tableName.'.action_type', 'User Base'], ['!=', $tableName.'.action_type', 'User Self']]
            ]);

            //可以管理的管理员
            $canMgrAdmin = (new User())->getChildIdAll();
            $query->andWhere([$tableName.'.operator' => $canMgrAdmin + ['SELF-SERVICE']]);
            $query->orWhere(['and', ['type' => 1], [$tableName.'.operator' => $canMgrAdmin + ['SELF-SERVICE']]]);
        }

        //排序
        $query->orderBy([ $tableName.'.id' => SORT_DESC ]);

        //一个随机字符串用户固定session
        $model->sessionKey = isset($params['key']) ? $params['key'] : md5(time());

        $count = $query->count();



        if ($export) {
            if ($count < 1) {

                Yii::$app->getSession()->setFlash('error', Yii::t('app', 'batch export help2'));
                echo 1;exit;
            } else if ($count > self::LOG_EXPORT_LIMIT) {
                Yii::$app->getSession()->setFlash('error', Yii::t('app', 'batch export help1', ['num'=>self::LOG_EXPORT_LIMIT]));
                echo 1;exit;
            } else {

                //获取导出数据
                $list = $query->asArray()->all();
                $list = $model->listShow($list);
                $excelData = [];
                $header = [];
                $content = [];
                $notExport = ['id', 'showContent', 'class', 'type'];
                $fields = $model->getSearchField();
                foreach ($list[0] as $k => $v) {
                    if (!in_array($k, $notExport)) {
                        $header[0][] = isset($fields[$k]) ? $fields[$k] : $k;
                    }

                }
                foreach ($list as $k => $v) {
                    $v['content'] =  ($v['showContent'] != $v['content']) ? $v['showContent']. $v['content'] : $v['content'];
                    $v['opt_time'] = date('Y-m-d H:i:s', $v['opt_time']);
                    unset($v['id']);
                    unset($v['showContent']);
                    unset($v['class']);
                    unset($v['type']);
                    $content[] = array_values($v);
                }
                $excelData = array_merge($header, $content);
                $file = FileOperate::dir('account') . 'batch_export_' . '_' . date('YmdHis') . '.xls';
                $title = Yii::t('app', 'batch export');
                //将内容写入excel文件
                Excel::arrayToExcel($excelData, $file, $title);
                //设置下载文件session
                Yii::$app->session->set('batch_excel_download_file', $file);

                return $this->redirect('/user/group/down-load');
            }
        }

        //列表
        try {
            $list = $query->limit($model->perPage)
                ->asArray()
                ->all();
            $list = $model->listShow($list);
            //var_dump($list);exit;
            $listContent = '';
            if(!empty($list)){
                $listContent = $model->showHtml($list);
            }

            //ajax获取更多内容
            if(isset($params['go']) && $params['go']=='more'){
                return Json::encode([
                    'last_id'=> $model->lastId,
                    'listContent'=>$listContent
                ]);
            }
        }catch (\Exception $e) {
            $listContent = [];
           /* var_dump($e->getMessage(), $e->getFile(), $e->getLine());
            VarDumper::dump($e->getMessage(), $e->getFile(), $e->getLine());exit;*/
        }


        return $this->render('index', [
            'model' => $model,
            'listContent' => $listContent,
            'params' => $params,
        ]);
    }

}