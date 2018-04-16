<?php
/**
 * 扩展字段控制器
 */
namespace center\modules\setting\controllers;

use yii;
use center\controllers\ValidateController;
use center\modules\setting\models\ExtendsField;
use center\modules\setting\models\ExtendsFieldSearch;

class ExtendsFieldController extends ValidateController
{
    /**
     * 列表展示，扩展字段功能的入口
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new ExtendsFieldSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionAdd()
    {
        $model = new ExtendsField();
        $model->loadDefaultValues();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            //判断是否有字段
            $usersColumn = Yii::$app->db->getSchema()->getTableSchema($model->table_name)->columnNames;
            if(in_array($model->field_name, $usersColumn)){
                Yii::$app->getSession()->setFlash('error', Yii::t('app', 'field help8 {field_name}', ['field_name'=>$model->field_name]));
                return $this->refresh();
            }
            $res = $model->save();
            if ($res) {
                //如果有默认值
                $filed_type = 'string not null comment "'.$model->field_desc.'" default "' . $model->default_value . '"';
                //添加字段
                Yii::$app->db->createCommand()->addColumn($model->table_name, $model->field_name, $filed_type)->execute();

                Yii::$app->getSession()->setFlash('success', Yii::t('app', 'operate success.'));
                return $this->redirect(['index']);
            } else {
                Yii::$app->getSession()->setFlash('error', Yii::t('app', 'operate failed.'));
            }

            return $this->redirect('index');
        }

        return $this->render('add', [
            'model' => $model,
        ]);
    }

    public function actionUpdate($id)
    {
        $id = intval($id);
        $model = ExtendsField::findOne($id);
        if ( !$model ) {
            throw new yii\web\NotFoundHttpException(Yii::t('app', 'No results found.'));
        }
        if ( $model->load(Yii::$app->request->post()) && $model->validate() ) {
            $old = $model->getOldAttributes();
            //如果字段名做了更改
            if($model->field_name != $old['field_name']){
                //判断新字段是否存在
                $usersColumn = Yii::$app->db->getSchema()->getTableSchema($model->table_name)->columnNames;
                if(in_array($model->field_name, $usersColumn)){
                    Yii::$app->getSession()->setFlash('error', Yii::t('app', 'field help8 {field_name}', ['field_name'=>$model->field_name]));
                    return $this->refresh();
                }
            }

            $res = $model->save();
            if ($res) {
                //字段改名
                if($model->field_name != $old['field_name']){
                    Yii::$app->db->createCommand()->renameColumn($model->table_name, $old['field_name'], $model->field_name)->execute();
                }
                //字段更改描述 或者 字段更改默认值
                if($model->field_desc != $old['field_desc'] || $model->default_value != $old['default_value']){
                    Yii::$app->db->createCommand()->alterColumn($model->table_name, $model->field_name, 'string not null
                        comment "'.$model->field_desc.'" default "'.$model->default_value.'" ')->execute();
                }

                Yii::$app->getSession()->setFlash('success', Yii::t('app', 'operate success.'));
            } else {
                Yii::$app->getSession()->setFlash('error', Yii::t('app', 'operate failed.'));
            }
            return $this->redirect('index');
        }
        return $this->render('edit', [
            'model' => $model,
        ]);
    }

    public function actionDelete($id)
    {
        $id = intval($id);
        $model = ExtendsField::findOne($id);
        if ( !$model ) {
            throw new yii\web\NotFoundHttpException(Yii::t('app', 'No results found.'));
        }
        $res = $model->delete();
        if ($res) {
            Yii::$app->db->createCommand()->dropColumn($model->table_name, $model->field_name)->execute();
            Yii::$app->getSession()->setFlash('success', Yii::t('app', 'operate success.'));
        } else {
            Yii::$app->getSession()->setFlash('error', Yii::t('app', 'operate failed.'));
        }
        return $this->redirect(['index']);
    }

}
