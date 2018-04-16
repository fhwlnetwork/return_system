<?php

namespace center\modules\auth\controllers;

use center\modules\product\models\Major;
use center\modules\user\models\BatchAdd;
use common\extend\Excel;
use common\models\FileOperate;
use common\models\User;
use yii;
use yii\filters\VerbFilter;
use center\models\SignupForm;
use center\modules\auth\models\AuthItem;
use center\modules\auth\models\UserModel;
use center\modules\auth\models\UserModelSearch;
use center\modules\auth\models\AuthAssignment;
use center\controllers\ValidateController;

/**
 * 管理员
 * AssignController implements the CRUD actions for AuthAssignment model.
 */
class AssignController extends ValidateController
{
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

    /**
     * Lists all AuthAssignment models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new UserModelSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * 添加用户操作, 同时将管理员添加到用户组中 auth_assignment.
     * 日志已经添加完毕, 没有问题.
     * @return string|\yii\web\Response
     */
    public function actionSignup()
    {
        $model = new SignupForm();
        $AuthItem = new AuthItem();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            //验证通过开启事务处理
            $transaction = Yii::$app->db->beginTransaction();
            try {
                if ($user = $model->signup()) {
                    //管理员添加成功
                    $model->mgr_product = ($model->mgr_product) ? implode(',', $model->mgr_product) : ''; // 可管理的产品
                    $model->log(null, $model->attributes, $insert = true); // 写 manager 表操作日志

                    $AuthAssignment = new AuthAssignment(); //将数据添加到 auth_assignment 表
                    $AuthAssignment->item_name = $model->roles;
                    $AuthAssignment->user_id = $user->id;
                    $AuthAssignment->save();

                    Yii::$app->getSession()->setFlash('success', Yii::t('app', 'add success.'));
                    $transaction->commit();
                    return $this->redirect(['index']);
                } else {
                    //管理员添加失败
                    Yii::$app->getSession()->setFlash('error', Yii::t('app', 'add failure'));
                    $transaction->rollBack();
                }
            } catch (\Exception $e) {
                Yii::$app->getSession()->setFlash('error', Yii::t('app', 'add failure.', ['msg' => $e->getMessage()]));
                $transaction->rollBack();
            }
        }
        $major = Major::getMajor();

        return $this->render('_form', ['model' => $model, 'AuthItem' => $AuthItem, 'major' => $major]);
    }

    /**
     * 更新管理员.
     * 日志已经添加完毕, 没有问题.
     * @param $id 管理员ID.
     * @return string|\yii\web\Response
     */
    public function actionUpdate($id)
    {
        $major = Major::getMajor();
        $canRunIndex = Yii::$app->user->can('auth/assign/index');
        $direct_url = $canRunIndex ? 'index' : 'update?id='.$id;
        $model = UserModel::findOne($id); // 获取要修改的管理员数据.
        $AuthItem = new AuthItem(); //实例化一个 角色表模型 对象.
        $model->scenario = 'update';
        $model->expire_time = $model->expire_time ? date('Y-m-d', $model->expire_time) : '';
        $trans = Yii::$app->db->beginTransaction();
        try {
            $params = Yii::$app->request->post();
            //var_dump($params);exit;
            if (!empty($params)) {
                if ($model->load($params) && $model->validate()) {
                    $post = $params['UserModel'];
                    $model->begin_time = $model->begin_time ? strtotime($model->begin_time) : '';
                    $model->stop_time = $model->stop_time ? strtotime($model->stop_time) : '';
                    $major = Major::find()->where(['id' => $model->major_id])->one();
                    $model->major_name = $major ? $major->major_name : '';
                    $model->ip_area = isset($post['ip_area']) ? $post['ip_area'] : '';

                    if (isset($post['mgr_org'])) {
                        $model->mgr_org = !empty($model->mgr_org) ? $model->mgr_org : ''; // 用户组
                    }
                    if (isset($post['mgr_portal'])) {
                        $model->mgr_portal = !empty($post['mgr_portal']) ? implode(',', $post['mgr_portal']) : ''; // portal
                    }

                    // 如果密码项填写了责重置密码.
                    if ($model->password) {
                        $model->password_hash = Yii::$app->security->generatePasswordHash(trim($model->password));
                        $model->auth_key = Yii::$app->security->generateRandomString();
                    }

                    // 如果管理员所在的用户组发生变化，则更新用户组的对应值.
                    $AuthAssignment = new AuthAssignment;
                    $AuthAssignmentData = $AuthAssignment->findOne(['user_id' => $id]); //查询值是否存在.

                    //如果 $AuthAssignmentData 不存在则重新创建, 否则就更新原有数据.
                    if (!empty($AuthAssignmentData)) {
                        $AuthAssignmentData->item_name = $model->roles;
                        $rs = $AuthAssignmentData->save(false); // 将数据保存在 auth_assignment 表 (角色 => 管理员ID)
                        if (!$rs) {

                            $trans->rollBack();
                            Yii::$app->getSession()->setFlash('error', Yii::t('app', 'update Administrators error2'));

                            return $this->redirect([$direct_url]);
                        }
                    } else {
                        $AuthAssignment->item_name = $model->roles;
                        $AuthAssignment->user_id = $id;
                        $rs = $AuthAssignment->save(false); // 将数据保存在 auth_assignment 表 (角色 => 管理员ID)
                        if (!$rs) {
                            $trans->rollBack();
                            Yii::$app->getSession()->setFlash('error', Yii::t('app', 'update Administrators error2'));

                            return $this->redirect([$direct_url]);
                        }
                    }

                    $modelRs = $model->save(false); //更新管理员表的
                    //var_dump($modelRs, Yii::$app->db->createCommand()->getRawSql());exit;
                    if (!$modelRs) {
                        $trans->rollBack();
                        Yii::$app->getSession()->setFlash('error', Yii::t('app', 'update Administrators error3'));


                        return $this->redirect([$direct_url]);
                    }
                    $trans->commit();
                    Yii::$app->getSession()->setFlash('success', Yii::t('app', 'edit success.'));
                    return $this->redirect([$direct_url]);
                } else {
                    $trans->rollBack();
                    //验证失败
                    foreach ($model->getErrors() as $key => $error) {
                        //失败返回值
                        Yii::$app->getSession()->setFlash('error', Yii::t('app', 'update Administrators validate error', ['param' => $key, 'error' => $error[0]]));

                        return $this->redirect([$direct_url]);
                    }
                }
            }
        } catch (\Exception $e) {
            $trans->rollBack(); //回滚
            //var_dump($e->getFile(), $e->getLine());exit;
            Yii::$app->getSession()->setFlash('error', Yii::t('app', 'update Administrators error', ['msg' => $e->getMessage()]));

            return $this->redirect([$direct_url]);
        }
        $model->begin_time = date('Y-m-d', $model->begin_time);
        $model->stop_time = date('Y-m-d', $model->stop_time);
        $isRoot = AuthAssignment::findOne(['user_id' => $model->id])->item_name == SUPER_ROLE;
        return $this->render('_form', ['model' => $model, 'AuthItem' => $AuthItem, 'isRoot' => $isRoot, 'major' => $major]);
    }

    /**
     * 删除管理员，同时将管理员在 auth_assignment 表中删除.
     * @param $id 管理员id.
     * @return \yii\web\Response
     */
    public function actionDelete($id)
    {
        /**
         * 如果角色管理员表 auth_assignment 表中有数据,则将 auth_assignment 和 manager 表同时删除
         * 否则只将 manager 表数据删除即可.
         */
        $trans = Yii::$app->db->beginTransaction();
        try {
            $delUserModel = UserModel::findOne($id); //要删除的角色
            $source = $this->findModel($id);
            $path = "%{$delUserModel->id}%";
            //查出子孙级元素
            $users = User::find()->select('id,username,pid,path')->where('path like' . "'{$path}'")->asArray()->all();
            if (!empty($users)) {
                $trans->rollBack();
                Yii::$app->getSession()->setFlash('error', Yii::t('app', 'del Administrator help1'));

                return $this->redirect(['index']);
            }
            $rs = $delUserModel->delete();

            if ($rs) {
                if (!empty($source)) {
                    $res = $this->findModel($id)->delete();
                    if (!$res) {
                        $trans->rollBack();
                        Yii::$app->getSession()->setFlash('error', Yii::t('app', 'del Administrator error3', ['username' => $delUserModel->username]));

                        return $this->redirect(['index']);
                    }
                    $trans->commit();
                    Yii::$app->getSession()->setFlash('success', Yii::t('app', 'delete success.'));
                }
            } else {
                $trans->rollBack();
                Yii::$app->getSession()->setFlash('error', Yii::t('app', 'del Administrator error2', ['username' => $delUserModel->username]));

                return $this->redirect(['index']);
            }
        } catch (\Exception $e) {
            $trans->rollBack();
            Yii::$app->getSession()->setFlash('error', Yii::t('app', 'delete error.', ['msg' => $e->getMessage()]));
        }

        return $this->redirect(['index']);
    }

    /**
     * 根据type获取不同的可管理的管理员
     *
     * @return array
     */
    public function actionGetType()
    {
        $model = new User();
        $data = [];
        if (!empty($_POST)) {
            $data = $model->getChildIdAllTwo($_POST['id'], $_POST['type'], $_POST['action']);
        }

        //利用response，发送json格式数据
        $response = Yii::$app->response;
        $response->format = yii\web\Response::FORMAT_JSON;
        return $response->data = $data;

    }

    /**
     * 验证密码是否正确
     * @throws yii\base\Exception
     */
    public function actionVerifyPassword()
    {
        $id = Yii::$app->request->post()['id'];
        $password = Yii::$app->request->post()['password'];
        if (!empty($password)) {
            $passwordHash = Yii::$app->security->generatePasswordHash($password);
            $user = User::findOne($id);
            if (!$user || !$user->validatePassword($password)) {
                echo json_encode(['error' => 1, 'msg' => Yii::t('app', 'Old password error')]);
            } else {
                echo json_encode(['error' => 0]);
            }
        } else {
            echo json_encode(['error' => 0]);
        }
    }

    protected function findModel($id)
    {
        if (($model = AuthAssignment::findOne(['user_id' => $id])) !== null) {
            return $model;
        }
    }

    /*
     * ajax获取指定管理员可管理的产品
     */
    public function actionAjaxMgrProductById()
    {
        $id = Yii::$app->request->post('id');
        $mgr_product = [];
        if ($id) {
            $products = UserModel::findOne($id)->mgr_product;
            $mgr_product = $products ? explode(',', $products) : [];
        }
        echo json_encode($mgr_product);
    }

    /**
     * 设置默认密码123456
     * @return string
     */
    public function actionSetDefaultPass()
    {
        $id = Yii::$app->request->post('id');
        try {
            $user = User::findOne($id);
            if ($user) {
                $user->password_hash = Yii::$app->security->generatePasswordHash('123456');
                $rs = $user->save(false);
                if ($rs) {
                    $res = ['error' => 0, 'msg' => Yii::t('app', 'auth help2')];
                    //记录日志
                    $mgrName = Yii::$app->user->identity->username;
                    $user->setLog($mgrName, $user->username);
                    Yii::$app->getSession()->setFlash('success', Yii::t('app', 'auth help2'));
                } else {
                    $res = ['error' => 1, Yii::t('app', 'auth help3')];
                    Yii::$app->getSession()->setFlash('error', Yii::t('app', 'auth help3'));
                }
            } else {
                $res = ['error' => 2, 'msg' => Yii::t('app', 'The user does not exist.')];
                Yii::$app->getSession()->setFlash('error', Yii::t('app', 'The user does not exist.'));
            }
        } catch (\Exception $e) {
            $res = ['error' => 3, 'msg' => Yii::t('app', 'auth help4')];
            Yii::$app->getSession()->setFlash('error', Yii::t('app', 'auth help4'));
        }
        return json_encode($res, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 批量添加用户
     * @return string
     */
    public function actionBatch()
    {
        $model = new BatchAdd();
        //下载文件
        if (Yii::$app->request->get('action') && Yii::$app->request->get('action') == 'download') {
            if (Yii::$app->session->get('batch_add_download_file')) {
                return Yii::$app->response->sendFile(Yii::$app->session->get('batch_add_download_file'));
            } else {
                Yii::$app->getSession()->setFlash('error', Yii::t('app', 'batch excel help31'));
            }
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $res = $model->batch_add_user();
            if ($res) {
                $file = FileOperate::dir('account') . '/user_add_' . date('YmdHis') . '.xls';
                $title = Yii::t('app', 'batch add help7');
                Excel::arrayToExcel($res['list'], $file, $title);
                //把文件名写入session
                Yii::$app->session->set('batch_add_download_file', $file);
                //将结果写入日志
                // 写日志
                //'srun 批量开户 完成,成功记录：'.count($excel_ok).'条，失败记录：'.count($excel_err).'条,详情：{file};' ;
                $logString = Yii::t('app', 'group msg11', [
                    'mgr' => Yii::$app->user->identity->username,
                    'ok_num' => $res['ok'],
                    'error_num' => $res['err'],
                    'target' => 'users',
                    'action' => 'add',
                    'action_type' => 'User Batch',
                    'file' => Yii::t('app', 'down info', ['download_url' => yii\helpers\Url::to(['/user/group/down-load?file=' . $file])]),
                ]);
               // $rs = $model->batchLog('', $logString);
                Yii::$app->getSession()->setFlash('success', Yii::t('app', 'batch add help1', [
                    'ok_num' => $res['ok'],
                    'err_num' => $res['err'],
                    'download_url' => yii\helpers\Url::to(['action' => 'download'])
                ]));

            } else {
                Yii::$app->getSession()->setFlash('error', Yii::t('app', 'operate failed.'));
            }
        }
        $major = Major::getMajor();
        return $this->render('batch', [
            'model' => $model,
            'major' => $major
        ]);
    }
}
