<?php
/**
 * 短信平台设置类
 * User: ligang
 * Date: 2015/5/11
 * Time: 10:42
 */

namespace center\modules\setting\controllers;

use common\models\Redis;
use yii;
use yii\filters\VerbFilter;
use center\controllers\ValidateController;
use center\modules\setting\models\EmailForm;

class EmailController extends ValidateController
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
        $model = new EmailForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->password = base64_encode($model->password);
            $values = $model->toArray();
            $str = $this->putIniFile($values);

            //将配置写入到 redis:16382 key:config:srun_weixin
            Redis::executeCommand('set', 'key:config:emailsender', [json_encode($values)]);

            if ($str) {
                Yii::$app->getSession()->setFlash('success', Yii::t('app', 'operate success.'));
            } else {
                Yii::$app->getSession()->setFlash('success', Yii::t('app', 'operate failed.'));
            }
        } else {
            if(YII_ENV_DEV){
                $dataDir = \Yii::$aliases['@common'];

                $dir = $dataDir . '/config';

                if (!is_dir($dir)) {
                    mkdir($dir);
                }
                $file = $dir.'/email.conf';
            }else{
                $file = '/srun3/etc/email.conf';
            }
            if (file_exists($file)) {

                $config = parse_ini_file($file);
                $model->setAttributes($config);
            }
        }
        $model->password = base64_decode($model->password);
        return $this->render('index', [
            'model' => $model,
        ]);
    }

    public function actionTest() {
        try{
			Yii::$app->mailer->compose()
            ->setFrom([Yii::$app->params['adminEmail'] => Yii::$app->params['nickName']])
            ->setTo(Yii::$app->params['adminEmail'])
            ->setSubject(Yii::t('app', 'T40009'))
            ->setTextBody(Yii::t('app', 'T40010'))
            ->send();

			Yii::$app->getSession()->setFlash('success', Yii::t('app', 'T40008'));
		}catch(\Exception $e){
			Yii::$app->getSession()->setFlash('error', $e->getMessage());
		}
        return $this->redirect(['index']);
    }

    protected function putIniFile($data) {
        $str = '';
        foreach ($data as $key => $value) {
            $str .= $key."="."\"".$value."\"\n";
        }

        if(YII_ENV_DEV){
            $dataDir = \Yii::$aliases['@common'];

            $dir = $dataDir . '/config';

            if (!is_dir($dir)) {
                mkdir($dir);
            }

            $file = $dir.'/email.conf';
            if(!file_exists($file)){
                fopen($file, 'w');
            }
        }else{
            $file = '/srun3/etc/email.conf';
        }

        $res = file_put_contents($file, $str);
        return $res;
    }
}