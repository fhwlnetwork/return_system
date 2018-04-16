<?php

namespace center\modules\setting\controllers;

use yii;
use common\models\Redis;
use common\models\SrunSms;
use center\controllers\ValidateController;
use center\modules\setting\models\Sms;

/**
 * Class SmsController
 * @package center\modules\setting\controllers
 */
class SmsController extends ValidateController
{

    public function actionIndex()
    {
        $model = Sms::findOne(['key' => Sms::$keyName]);

        if (!$model) {
            $model = new Sms();
            $model->set();
        } else {
            $array = json_decode($model->value, true);

            if (isset($array['sms_type'])) {
                $model->sms_type = $array['sms_type'];
            }
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {

            //处理 第三方对接 参数
            if ($model->sms_type == 1) {
                $res = $model->save();
                if ($res) {
                    Yii::$app->getSession()->setFlash('success', Yii::t('app', 'operate success.'));
                } else {
                    Yii::$app->getSession()->setFlash('error', Yii::t('app', 'operate failed.'));
                }
            } else {
                //处理 对接深澜短信网关
                $className = $model->class;

                //如果签名填了 说明执行的 深澜大鱼 短信接口程序
                if ($model->sign) {
                    $res = $model->save();
                    if ($res) {
                        Yii::$app->getSession()->setFlash('success', Yii::t('app', 'operate success.'));
                    } else {
                        Yii::$app->getSession()->setFlash('error', Yii::t('app', 'operate failed.'));
                    }
                } else {
                    //处理 深澜老版本短信对接的问题
                    if (!class_exists($className)) {
                        Yii::$app->getSession()->setFlash('error', Yii::t('app', 'sms help1', ['className' => $className]));
                    } else if (!method_exists((new $className()), 'send_msg')) {
                        Yii::$app->getSession()->setFlash('error', Yii::t('app', 'sms help2'));
                    } else {
                        $res = $model->save();
                        if ($res) {
                            Yii::$app->getSession()->setFlash('success', Yii::t('app', 'operate success.'));
                        } else {
                            Yii::$app->getSession()->setFlash('error', Yii::t('app', 'operate failed.'));
                        }
                    }
                }
            }

            return $this->redirect('index');
        }

        return $this->render('index', [
            'model' => $model,
        ]);
    }

    public function actionSend()
    {
        $arr = [
            'phone' => '18618413509',
            'msg' => '您的验证码是：123456',
        ];
        $json = json_encode($arr);
        $res = Redis::executeCommand('RPUSH', "list:sms", [$json]);
        return $res;
    }

    public function actionMsg()
    {
        $setting = Sms::getSetting();
        $data = $setting['setting'];

        if (!isset($data['name']) || !isset($data['token']) || !isset($data['phone']) || !isset($data['content'])) {
            Yii::$app->getSession()->setFlash('error', Yii::t('app', 'message Invalid Param.'));
            return $this->redirect('index');
        }

        $phone = trim($data['phone'], '&');
        $msg = trim($data['content'], '&');
        $res = (new SrunSms())->send_msg($phone, $msg);

        if ($res == 1) {
            Yii::$app->getSession()->setFlash('success', Yii::t('app', 'success'));
        } else {
            Yii::$app->getSession()->setFlash('error', $res);
        }

        return $this->redirect('index');
    }
}