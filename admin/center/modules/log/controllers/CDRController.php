<?php
/**
 * Created by PhpStorm.
 * User: DM
 * Date: 17/4/19
 * Time: 16:22
 */

namespace center\modules\log\controllers;

use center\controllers\ValidateController;
use center\modules\log\models\LogCdr;
use common\extend\Export\CsvExport;
use common\models\Redis;
use Yii;

class CDRController extends ValidateController
{
    // 首页展示
    public function actionIndex(){
        // 接收 get参数
        $get = Yii::$app->request->get();
        // 默认显示今日 CDR 数据
        return $this->render('index',(new LogCdr())->getTodayCDR($get));
    }

    // 获取前10条日志
    public function actionGetTenCdr(){
        $lang = $this->module->module->language;
        $user_name = Yii::$app->request->post('user_name');
        if(!$user_name) $user_name = Yii::$app->request->get('user_name');
        if($user_name){
            $rs = (new LogCdr())->getTenCdr($user_name);
            if($rs){
                if($rs['col']){
                    if($lang == 'en'){
                        foreach ($rs['col'] as $v){
                            $rr[0][] = ucwords(str_replace('_',' ',$v['column_name']));
                        }
                    }else{
                        foreach ($rs['col'] as $v){
                            $rr[0][] = $v['column_comment'];
                        }
                    }
                }
                if($rs['models']){
                    $i = 1;
                    foreach ($rs['models'] as $vv){
                        $rr[$i][] = $vv['id'];
                        $rr[$i][] = $vv['svc_type'];
                        $rr[$i][] = $vv['session_id'];
                        $rr[$i][] = $vv['caller'];
                        $rr[$i][] = $vv['callee'];
                        $rr[$i][] = date('Y-m-d H:i:s',$vv['start_time']);
                        $rr[$i][] = date('Y-m-d H:i:s',$vv['end_time']);
                        $rr[$i][] = $vv['result'];
                        $rr[$i][] = $vv['cause'];
                        $i++;
                    }
                }
                $re['status'] = 1;
                $re['msg'] = Yii::t('app','got');
                $re['data'] = $rs;
                exit(json_encode($rr));
            }else{
                $re['status'] = 0;
                $re['msg'] = Yii::t('app','no_more_data');
                exit(json_encode($re));
            }
        }else{
            $re['status'] = 0;
            $re['msg'] = Yii::t('app','bad_user_name');
            exit(json_encode($re));
        }
    }

}