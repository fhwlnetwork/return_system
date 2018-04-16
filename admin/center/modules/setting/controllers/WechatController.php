<?php
/**
 * w微信设置
 * User: jbs
 * Date: 2017/5/2
 * Time: 15:50
 */

namespace center\modules\setting\controllers;

use yii;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;
use common\models\Redis;
use center\controllers\ValidateController;
use center\modules\setting\models\Wechat;

class WechatController extends ValidateController
{
    public function actionIndex()
    {
        $model = new Wechat();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {

            $values = $model->toArray();
            //数据做缓存
            $res = Redis::executeCommand('hMSet', 'hash:wechat_config', Redis::arrayToHash($values), 'redis_cache');
            $res = $this->putIniFile($values);
            if ($res) {
                Yii::$app->getSession()->setFlash('success', Yii::t('app', 'operate success.'));
            } else {
                Yii::$app->getSession()->setFlash('success', Yii::t('app', 'operate failed.'));
            }
        } else {

            if(YII_ENV_DEV){
                $tmp = "wechat.conf";
            }else{
                $tmp = "/srun3/etc/wechat.conf";
            }
            if (file_exists($tmp)) {
                $config = parse_ini_file($tmp);
                $model->setAttributes($config);
            }
        }

        return $this->render('index', [
            'model' => $model
        ]);
    }

    protected function putIniFile($data)
    {
        $str = '';
        foreach ($data as $key => $value) {
            $str .= $key . "=" . "\"" . $value . "\"\n";
        }

        if(YII_ENV_DEV){
            $file = "wechat.conf";
        }else{
            $file = "/srun3/etc/wechat.conf";
        }

        //线上目录结构不同，布置到线上时用下边的
        //$file = '/srun3/etc/wechat.conf';
        $res = file_put_contents($file, $str);
        return $res;
    }
}