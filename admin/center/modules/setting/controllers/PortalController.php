<?php
namespace center\modules\setting\controllers;

use yii;
use common\models\User;
use common\models\Redis;
use center\controllers\ValidateController;
use center\modules\setting\models\Portal;
use yii\filters\VerbFilter;

class PortalController extends ValidateController
{
    public function actions()
    {
        return [
            'upload' => [
                'class' => 'kucha\ueditor\UEditorAction',
                'config' => [
                    "imageUrlPrefix"  => Yii::$app->request->hostInfo,//图片访问路径前缀
                    "imagePathFormat" => "/uploads/image/{yyyy}{mm}{dd}/{time}{rand:6}" //上传保存路径
                ],
            ]
        ];
    }
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $get = Yii::$app->request->get();

        if ($get) {
            $get = array_values($get)[0];
            //判断用户使用下载过文件
            $session = Yii::$app->session;
            $ip = Yii::$app->request->userIP;
            if ($session[$ip]) {
                if ((time() - $session[$ip] < 5)) {
                    $session[$ip] = time();
                    Yii::$app->getSession()->setFlash('error', Yii::t('app', 'portal_help6'));
                    return $this->redirect('index');
                } else {
                    $session[$ip] = time();
                }
            } else {
                $session[$ip] = time();
            }

            $file_name = $get['name'] . '.tar.gz';
            // tar zcvf xxx.tar.gz portal/srun/xxx
            $cmd = 'tar zcvf ' . $file_name . ' portal/' . $get['p'] . '/' . $get['name'];
            exec($cmd);
            Yii::$app->response->sendFile($file_name);
            exec('rm -rf ' . $file_name);
        }

        //portal模型
        $model = new Portal();
        $isSuper = User::isSuper();

        //超管显示全部模板, 非超管只显示自己能管理的模板
        $portal_arr = \center\modules\auth\models\UserModel::getAttributesList()['mgr_portal']; //默认模板数据
        if ($isSuper) {
            $tmp_arr = $portal_arr;
        } else {
            $mgr_portal = Yii::$app->user->identity->mgr_portal;
            $tmp_array = explode(',', $mgr_portal);

            foreach ($tmp_array as $val) {
                $tmp_arr[$val] = $portal_arr[$val];
            }
        }

        $arr = [];
        if (!empty($tmp_arr)) {
            //查询 portal 数据
            $data = $model->find()->where(['in', 'portal_name', array_flip($tmp_arr)])->asArray()->all();
            //对数据进行组合 使用模板key 作为新数组的 key, 方便在页面中使用
            if (!empty($data)) {
                foreach ($tmp_arr as $key => $val) {
                    foreach ($data as $value) {
                        if ($key == $value['portal_name']) {
                            $arr[$key] = $value;
                        } else {
                            $arr[$key] = '';
                        }
                    }
                }
            }
        }

        return $this->render('index', [
            'tmp_arr' => $tmp_arr,
            'arr' => $arr
        ]);
    }

    //生成实例
    public function actionCreate()
    {
        //get 传参 portal_name
        $get_arr = Yii::$app->getRequest()->get();

        foreach ($get_arr as $val) {
            $get['name'] = $val['name'];
        }

        $model = new Portal();

        if (strstr($get['name'], 'pc') !== false) {
            $model->type = 1;
            $model->setScenario('pc');
        } else {
            $model->type = 2;
            $model->setScenario('mobile');
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->portal_name = $get['name'];
            $model->pid = Yii::$app->user->getId();
            $model->save();

            if ($model->type == 1) {
                $model->pc($model); // 将模板文件需要的数据写入到js文件中
            } else {
                $model->mobile($model); // 将模板文件需要的数据写入到js文件中
            }

            $model->log('add'); //操作日志
            Yii::$app->getSession()->setFlash('success', Yii::t('app', 'edit success.'));
            return $this->redirect('index');
        }

        return $this->render('_form', [
            'model' => $model,
        ]);
    }

    //编辑实例
    public function actionUpdate()
    {
        //get 传参 portal_name
        $get = Yii::$app->getRequest()->get();
        $model = new Portal();
        $model = $model->findOne(['portal_name' => $get['name'], 'examples_name' => $get['examples_name']]);

        //区分 pc 和 移动端数据
        if ($model->type === 1) {
            $model->setScenario('pc');
        } else {
            $model->setScenario('mobile');
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->pid = Yii::$app->user->getId();
            $model->save();

            if ($model->type == 1) {
                $model->pc($model); // 将模板文件需要的数据写入到js文件中
            } else {
                $model->mobile($model); // 将模板文件需要的数据写入到js文件中
            }

            $model->log('edit'); //操作日志
            Yii::$app->getSession()->setFlash('success', Yii::t('app', 'edit success.'));
            return $this->redirect('index');
        }

        return $this->render('_form', [
            'model' => $model,
        ]);
    }

    //删除实例
    public function actionDelete($name)
    {
        $model = new Portal();
        $model->examples_name = $name;
        $model->log('delete'); //操作日志
        $model->remove($name);

        return $this->redirect('index');
    }

    //分发实例
    public function actionGive()
    {
        //get 传参 portal_name
        $get = Yii::$app->getRequest()->get();
        $model = new Portal();
        $model->setScenario('give');

        /**
         * 端口：16384
         * 队列：list:do_command:client
         * 字段：
         * action  1-拷文件  2-拷目录
         * sourct_path  要发送的本地文件或目录的路径
         * dest_ip 目标机的IP地址
         * dest_path 目标路径
         * 拷文件时必须为全路径，如：/tmp/test.txt;
         * 拷目录时为上级路径 如要把本地的/srun3/www/login-web/admin 拷到远程机的/srun3/www/login-web下，则目录写/srun3/www/login-web，而不能写/srun3/www/login-web/admin。目录结尾处不能带”/”。
         * $arr = [
         * ‘action’=>1,
         * ‘source_path’=>’ /srun3/www/login-web/srun’,
         * ‘dest_ip’=>’127.0.0.1’,
         * ‘dest_path’=>’ /srun3/www/login-web’];
         * $json=json_encode($arr);
         * $redis_cache->rpush(‘list:do_command:client’, $json);
         */

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $arr = [
                'action' => $model->action,
                'source_path' => $model->source_path,
                'dest_ip' => $model->dest_ip,
                'dest_path' => $model->dest_path
            ];

            $json = json_encode($arr);
            Redis::executeCommand('RPUSH', 'list:do_command:client', [$json], 'redis_cache');
            Yii::$app->getSession()->setFlash('success', Yii::t('app', 'operate success.'));
            return $this->redirect('index');
        }

        $model->source_path = Yii::$aliases['@webroot'] . '/portal/' . $get['name'] . '/' . $get['examples_name'];
        $model->dest_path = '/srun3/www/login-web/';
        $model->action = 2;

        return $this->render('give', [
            'model' => $model
        ]);
    }
}