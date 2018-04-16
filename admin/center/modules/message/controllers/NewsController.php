<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2018/3/10
 * Time: 8:59
 */

namespace center\modules\message\controllers;

use center\modules\message\models\News;
use center\modules\product\models\MajorWorkRelation;
use yii;
use center\controllers\ValidateController;
use yii\web\NotFoundHttpException;

/**
 * 招聘控制器
 * Class DefaultController
 * @package center\modules\message\controllers
 */
class NewsController extends ValidateController
{
    public function actionRedirect()
    {
        if (Yii::$app->user->can('message/news/index')) {
            return $this->redirect('index');
        }
    }

    /**
     * 发布新闻工作中心
     * @return string
     */
    public function actionIndex()
    {
        $model = new News();
        $attributes = $model->getAttributesList();
        $types = isset($attributes['types']) ? $attributes['types'] : [];
        $params = Yii::$app->request->queryParams;
        $listRs = $model->getList($params);
        $list = isset($listRs['data']) ? $listRs['data'] : [];
        $pagination = isset($listRs['pagination']) ? $listRs['pagination'] : [];

        return $this->render('index', [
            'model' => $model,
            'list' => $list,
            'types' => $types,
            'pagination' => $pagination,
            'params' => $params
        ]);
    }

    /**
     * 发布新闻信息
     * @return yii\web\Response
     */
    public function actionAdd()
    {
        $model = new News();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $uploadedFile = yii\web\UploadedFile::getInstance($model, 'pic');  //Artcile[thumb]存储的图片字段
            if ($uploadedFile === null || $uploadedFile->hasError) {
                Yii::$app->getSession()->setFlash('error', '文件不存在，缺少上传的文章图片');

                return $this->redirect('index');
            }
            //创建时间
            $ymd = date("Ymd");
            //存储到本地的路径
            $save_path = \Yii::getAlias('@uploads') . '/' . $ymd . '/';
            //存储到数据库的地址
            $save_url = 'uploads' . '/' . $ymd . '/';
            if (!is_dir($save_path)) {
                mkdir($save_path);
            }
            //图片格式
            $file_ext = $uploadedFile->getExtension();
            //新文件名
            $new_file_name = date("YmdHis") . '_' . rand(10000, 99999) . '.' . $file_ext;
            //图片信息
            $uploadedFile->saveAs($save_path . $new_file_name);
            $model->pic =  $save_url . $new_file_name;
            $rs = $model->save();
            if ($rs) {
                Yii::$app->getSession()->setFlash('success', '添加文章成功');
            } else {
                Yii::$app->getSession()->setFlash('success', '添加文章失败');
            }
        } else {
            var_dump($model->getErrors());
            exit;
        }

        return $this->redirect('index');
    }

    /**
     * 编辑招聘信息
     * @param $id
     * @return string|yii\web\Response
     */
    public function actionEdit($id)
    {
        $model = $this->findModel($id);
        $attributes = $model->getAttributesList();
        $types = isset($attributes['types']) ? $attributes['types'] : [];
        if ($model->load(Yii::$app->request->post())) {
            $uploadedFile = yii\web\UploadedFile::getInstance($model, 'pic');  //Artcile[thumb]存储的图片字段
            if (!($uploadedFile === null || $uploadedFile->hasError)) {
                //创建时间
                $ymd = date("Ymd");
                //存储到本地的路径
                $save_path = \Yii::getAlias('@uploads') . '/' . $ymd . '/';
                //存储到数据库的地址
                $save_url = 'uploads' . '/' . $ymd . '/';
                if (!is_dir($save_path)) {
                    mkdir($save_path);
                }
                //图片格式
                $file_ext = $uploadedFile->getExtension();
                //新文件名
                $new_file_name = date("YmdHis") . '_' . rand(10000, 99999) . '.' . $file_ext;
                //图片信息
                $uploadedFile->saveAs($save_path . $new_file_name);
                unlink($model->pic);
                $model->pic =  $save_url . $new_file_name;
            }
            $model->save();
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('_form', [
                'model' => $model,
                'action' => 'edit',
                'types' => $types,
            ]);
        }
    }

    /**
     * 查看文章
     * @param $id
     * @return string|yii\web\Response
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $attributes = $model->getAttributesList();
        $types = isset($attributes['types']) ? $attributes['types'] : [];
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('_form', [
                'model' => $model,
                'action' => 'view',
                'types' => $types
            ]);
        }
    }

    /**
     * 删除新闻
     * @param $id
     * @return yii\web\Response
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * @param $major_id
     * @return string
     */
    public function actionWorks($major_id)
    {
        try {
            $data = MajorWorkRelation::find()
                ->where(['major_id' => $major_id])
                ->indexBy('id')
                ->asArray()
                ->all();
            if ($data) {
                $rs = ['code' => 1, 'msg' => 'ok', 'data' => $data];
            } else {
                $rs = ['code' => 404, 'msg' => '没有工作相关，请添加'];
            }
        } catch (\Exception $e) {
            $rs = ['code' => 500, 'msg' => '获取招聘信息异常'];
        }

        return json_encode($rs);
    }

    /**
     * Finds the MajorWorkRelation model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return News the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected
    function findModel($id)
    {
        if (($model = News::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

}