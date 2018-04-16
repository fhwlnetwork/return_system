<?php
namespace center\modules\auth\controllers;

use Yii;
use common\models\User;
use yii\web\NotFoundHttpException;
use center\modules\auth\models\Rbac;
use center\modules\auth\models\AuthItem;
use center\controllers\ValidateController;
use center\modules\auth\models\AuthItemChild;
use center\modules\auth\models\AuthItemSearch;
use center\modules\auth\models\AuthAssignment;


class RolesController extends ValidateController
{
    /**
     * 角色列表
     * @return string
     */
    public function actionIndex()
    {
        $model = new AuthItem();
        $searchModel = new AuthItemSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $model->auth_item_type_1);

        return $this->render('index', [
            'model' => $model,
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel
        ]);
    }

    /**
     * 创建角色.
     * @return string
     */
    public function actionCreate()
    {
        $model = new AuthItem();

        //获取当前管理员拥有的权限
        $userPermission = AuthItemChild::getItemsByUser();
        //获取当前管理员是否超管
        $userIsSuper = User::isSuper();


        if ($model->load(Yii::$app->request->post())) {
            //开启事物
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $model->save();
                // 将角色对应的权限添加到数据库中
                $AuthItemChild = new AuthItemChild();
                $AuthItemChild->deleteAll(['parent' => $model->name]);
                if (isset(Yii::$app->request->post()['AuthItemChild'])) {
                    $permissionData = Yii::$app->request->post()['AuthItemChild'];

                    $data = [];
                    foreach ($permissionData as $val) {
                        $data = $val;
                    }

                    for ($i = 0; $i < count($data); $i++) {
                        $AuthItemChildS = new AuthItemChild();
                        $AuthItemChildS->parent = $model->name;
                        $AuthItemChildS->child = $data[$i];
                        $AuthItemChildS->save();
                    }
                }
                $transaction->commit();

                Yii::$app->getSession()->setFlash('success', Yii::t('app', 'add success.'));
            } catch(\Exception $e) {
                $transaction->rollBack();
                Yii::$app->getSession()->setFlash('error', Yii::t('app', 'add failure.', ['msg' => $e->getMessage()]));
            }

            return $this->redirect(['index']);
        }

        return $this->render('create', [
            'model' => $model,
            'userPermission' => $userPermission,
            'userIsSuper' => $userIsSuper,
        ]);
    }

    /**
     * 更新角色和权限.
     * @param $id 角色名称.
     * @return string|\yii\web\Response
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $beforeName = $model->name; // 修改之前的 角色名称.

        //获取当前管理员拥有的权限
        $userPermission = AuthItemChild::getItemsByUser();
        //获取当前管理员是否超管
        $userIsSuper = User::isSuper();
        //获取此角色组的所有权限
        $items = AuthItemChild::getItemsByRole($model->name);

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {

            //增加事物处理
            $transaction = Yii::$app->db->beginTransaction();
            try {
                Yii::$app->db->createCommand()->update($model::tableName(), ['name' => $model->name, 'description' => $model->description, 'updated_at' => time()], ['id' => $id])->execute(); //更新角色表数据.
                Yii::$app->db->createCommand()->update(AuthItemChild::tableName(), ['parent' => $model->name], ['parent' => $beforeName])->execute(); //更新角色赋权数据.
                Yii::$app->db->createCommand()->update(AuthAssignment::tableName(), ['item_name' => $model->name], ['item_name' => $beforeName])->execute(); //更新角色管理员表数据.

                $AuthItemChild = new AuthItemChild();
                $AuthItemChild->deleteAll(['parent' => $model->name]);

                if (isset(Yii::$app->request->post()['AuthItemChild'])) {
                    $permissionData = Yii::$app->request->post()['AuthItemChild'];

                    $data = [];
                    foreach ($permissionData as $val) {
                        $data = $val;
                    }


                    //过滤重复值
                    $data = array_unique($data);

                    for ($i = 0; $i < count($data); $i++) {
                        $AuthItemChild = new AuthItemChild();
                        $AuthItemChild->parent = $model->name;
                        $AuthItemChild->child = $data[$i];
                        $AuthItemChild->save();
                    }
                }
                $transaction->commit();
            } catch (\Exception $e) {
                Yii::$app->getSession()->setFlash('error', Yii::t('app', '更新权限失败'),['msg' =>$e->getMessage()]);
                $transaction->rollBack();
                return $this->redirect(['index']);
            }

            /**
             * 为日志做准备.
             */
            $old = [];
            $old['name'] = $beforeName;
            $old['permission'] = $items;

            $des = [];
            $des['name'] = $model->name;
            $des['name'] = $model->name;
            $des['permission'] = $data;
            $model->log($old, $des, $insert = false);// 写添加角色日志.

            Yii::$app->getSession()->setFlash('success', Yii::t('app', 'edit success.'));
            return $this->redirect(['index']);
        }

        return $this->render('create', [
            'model' => $model,
            'userPermission' => $userPermission,
            'userIsSuper' => $userIsSuper,
            'items' => $items,
        ]);
    }

    /**
     * 删除角色操作。
     * 同时会删除角色的对应权限以及权限下面的管理员
     * 会影响到 `auth_item`, `auth_assignment`, `auth_child` 三张表的数据.
     */
    public function actionDelete($id)
    {
      if($id == 1)//不允许删除ID为1的角色
        {
        	Yii::$app->getSession()->setFlash('success', Yii::t('app', 'operate failed.'));
        	return $this->redirect(['index']);
        }
        /**
         * 日志.
         */
        $AuthItem = AuthItem::findOne($id);
        $permission = AuthItemChild::findAll(['parent' => $AuthItem->name]);

        $child = '';
        foreach ($permission as $val) {
            $child .= $val['attributes']['child'] . '<br />';
        }

        $data['parent'] = $AuthItem->name;
        $data['child'] = $child;
        $AuthItem->deleteLog(null, $AuthItem);
        AuthItemChild::deleteLog(null, $data);

        /**
         * 删除数据操作.
         */
        $model = new Rbac();
        $model->deleteItem($id, 'roles');
        Yii::$app->getSession()->setFlash('success', Yii::t('app', 'delete success.'));
        return $this->redirect(['index']);
    }

    /**
     * Finds the AuthItem model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return AuthItem the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = AuthItem::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
