<?php
namespace center\modules\setting\controllers;

use center\controllers\ValidateController;
use yii;
use yii\data\Pagination;
use center\modules\setting\models\Server;

class ServerController extends ValidateController
{
    public function actionIndex()
    {
        $model = new Server();
		
		$query = $model::find();
        $pagination = new yii\data\Pagination([
            'defaultPageSize' => 10,
            'totalCount' => $query->count(),
        ]);

        $list = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->orderBy([$model->primaryKey()[0] => SORT_DESC])
            ->asArray()
            ->all();		
	
        return $this->render('index', [
            'model'=>$model,
            'list'=>$list,
            'pagination'=>$pagination
        ]);
    }

    public function actionAdd()
    {
        $model = new Server();
        if ( $model->load(Yii::$app->request->post()) && $model->validate() ) {
			$model->type = implode(',',$model->type);
			$model->addtime = time();
			$model->log([],Yii::$app->request->post(), $action = 'add');	
            $res = $model->save();
            if ($res) {
                Yii::$app->getSession()->setFlash('success', Yii::t('app', 'operate success.'));
            } else {
                Yii::$app->getSession()->setFlash('error', Yii::t('app', 'operate failed.'));
            }
            return $this->redirect(['index']);
        }
    }

    public function actionEdit($id)
    {
        $model = new Server();
        $id = intval($id);
        $model = $model->getOne($id);
        if ( !$model ) {
            throw new yii\web\NotFoundHttpException(Yii::t('app', 'No results found.'));
        }

        $model->setAttributes($one);

        if ( $model->load(Yii::$app->request->post()) && $model->validate() ) {
			$model->ip = $model->ip;
			$model->devicename = $model->devicename;
			$model->type = implode(',',$model->type);
			$model->admin = $model->admin;
			$model->region = $model->region;
			$model->fault = $model->fault;
			$model->configure = $model->configure;
			$res = $model->save();
            if ($res) {
                Yii::$app->getSession()->setFlash('success', Yii::t('app', 'operate success.'));
            } else {
                Yii::$app->getSession()->setFlash('error', Yii::t('app', 'operate failed.'));
            }
            return $this->redirect('index');
        }
		$model->type = explode(',',$model->type);
        return $this->render('index', ['model'=>$model ]) ;
    }

    public function actionDelete($id)
    {
        $model = new Server();
        $id = intval($id);
        $one = $model->getOne(intval($id));
        if ( !$one ) {
            throw new yii\web\NotFoundHttpException(Yii::t('app', 'No results found.'));
        }
        $res = $model->deleteOne($id);
        if ($res) {
            Yii::$app->getSession()->setFlash('success', Yii::t('app', 'operate success.'));
        } else {
            Yii::$app->getSession()->setFlash('error', Yii::t('app', 'operate failed.'));
        }
        return $this->goBack(Yii::$app->request->getReferrer());
    }

}