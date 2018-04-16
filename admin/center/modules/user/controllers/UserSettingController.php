<?php

namespace center\modules\user\controllers;

use center\modules\user\models\Setting;
use Yii;
use yii\web\Controller;

class UserSettingController extends Controller
{
    // 密码强度页面
    public function actionPwd()
    {
        // 获取当前密码强度
        $rs = Setting::findOne(['key' => 'pwd_strong']);

        return $this->render('pwd', ['rs' => $rs]);
    }

    // 密码修改策略页面
    public function actionPwdChange()
    {
        // 获取当前密码强度
        $rs = Setting::findOne(['key' => 'pwd_change_first']);

        // 获取修改密码策略
        $rs2 = Setting::findOne(['key' => 'pwd_change_way']);

        return $this->render('pwd-change', ['rs' => $rs, 'rs2' => json_decode($rs2->value, true)]);
    }

    // ajax设置密码强度
    public function actionSetPwdStrong()
    {
        $value = Yii::$app->request->post('value');

        if ($value) {
            $rs = Setting::findOne(['key' => 'pwd_strong']);
            $rs->value = $value;
            if (FALSE !== $rs->save()) {
                $re['msg'] = Yii::t('app','set_success');
                $re['status'] = 1;
                exit(json_encode($re));
            } else {
                $re['msg'] = Yii::t('app','set_failed');
                $re['status'] = 0;
                exit(json_encode($re, JSON_UNESCAPED_UNICODE));
            }
        } else {
            $re['msg'] = Yii::t('app','strong_error');
            $re['status'] = 0;
            exit(json_encode($re, JSON_UNESCAPED_UNICODE));
        }
    }

    // ajax设置第一次密码修改
    public function actionSetPwdChangeFirst()
    {
        $rs = Setting::findOne(['key' => 'pwd_change_first']);

        $first = Yii::$app->request->post('first');

        if ($first === 'true') {
            $rs->value = "1";
        } else {
            $rs->value = "2";
        }

        if (FALSE !== $rs->save()) {
            $re['msg'] = Yii::t('app','set_success');
            $re['status'] = 1;
            exit(json_encode($re));
        } else {
            $re['msg'] = Yii::t('app','set_failed');
            $re['status'] = 0;
            exit(json_encode($re));
        }
    }

    // ajax设置密码修改策略
    public function actionSetPwdChangeWay(){
        $way = Yii::$app->request->post('way');
        $num = Yii::$app->request->post('num');

        if($way and $num and is_numeric($num)){
            $value = json_encode(['way' => $way,'num' => $num]);
            $rs = Setting::findOne(['key'=>'pwd_change_way']);
            $rs->value = $value;
            if (FALSE !== $rs->save()) {
                $re['msg'] = Yii::t('app','set_success');
                $re['status'] = 1;
                exit(json_encode($re));
            } else {
                $re['msg'] = Yii::t('app','set_failed');
                $re['status'] = 0;
                exit(json_encode($re));
            }
        }else{
            $re['msg'] = Yii::t('app','params_error');
            $re['status'] = 0;
            exit(json_encode($re));
        }
    }
}
