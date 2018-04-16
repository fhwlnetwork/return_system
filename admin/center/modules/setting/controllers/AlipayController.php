<?php
/**
 * 支付宝设置
 * User: Wjw
 * Date: 2015/8/18
 * Time: 9:50
 */

namespace center\modules\setting\controllers;

use yii;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;
use common\models\Redis;
use center\controllers\ValidateController;
use center\modules\setting\models\AlipayForm;

class AlipayController extends ValidateController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'test' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $model = new AlipayForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->alipay_key = UploadedFile::getInstance($model, 'alipay_key');
            $model->uploadFile();

            $values = $model->toArray();
            //数据做缓存
            Redis::executeCommand('hMSet', 'hash:alipay_config', Redis::arrayToHash($values), 'redis_cache');
            $res = $this->putIniFile($values);
            if ($res) {
                Yii::$app->getSession()->setFlash('success', Yii::t('app', 'operate success.'));
            } else {
                Yii::$app->getSession()->setFlash('success', Yii::t('app', 'operate failed.'));
            }
        } else {
            if (file_exists("/srun3/etc/alipay.conf")) {
                $config = parse_ini_file("/srun3/etc/alipay.conf");
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

        $file = '/srun3/etc/alipay.conf';
        $res = file_put_contents($file, $str);
        return $res;
    }
}