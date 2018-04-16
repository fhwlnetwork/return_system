<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2018/3/9
 * Time: 20:40
 */

namespace center\modules\product\controllers;


use center\modules\student\models\StuPubCenter;
use center\modules\student\models\StuWorks;
use yii;
use common\models\User;
use yii\web\NotFoundHttpException;
use center\modules\student\models\Manager;
use center\modules\student\models\StuWorksNow;

class DefaultController extends \center\controllers\ValidateController
{
    /**
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * 学生基本信息
     * @return string
     */
    public function actionBase($id = null)
    {
        //  return $this->redirect('/student/default/view');
        $id = $id ? $id : Yii::$app->user->identity->getId();
        $model = $this->findModel($id);
        $works = $model->getWorkHistory($model->id);
        $pubs = $model->getStuPub($model->id);
        $status  = [
            '' => '全部',
            0 => '待审核',
            1 => '审核通过',
            2 => '未通过'
        ];

        return $this->render('base', [
            'model' => $model,
            'works' => $works['data'] ? $works['data'] : [],
            'pubs' => $pubs['data'] ? $pubs['data'] : [],
            'status' => $status
        ]);
    }

    /**
     * 工作经历
     * @return string
     */
    public function actionWorkHistory()
    {
        $flag = User::isStudent();
        if ($flag) {
            $model = new StuWorks();
            $param = Yii::$app->request->queryParams;
            $listRs = $model->getList($param);
        } else {
            $model = new StuWorksNow();
            $param = Yii::$app->request->queryParams;
            $listRs = $model->getList($param);
        }

        $list = isset($listRs['data']) ? $listRs['data'] : [];
        $pages = isset($listRs['pagination']) ? $listRs['pagination'] : [];

        return $this->render('history', [
            'list' => $list,
            'model' => $model,
            'pagination' => $pages,
            'params' => $param,
            'flag' => $flag
        ]);
    }

    /**
     * 作品发布中心
     * @return string
     */
    public function actionWorks()
    {
        $model = new StuPubCenter();
        $params = Yii::$app->request->queryParams;
        $listRs = $model->getList($params);
        $list = isset($listRs['data']) ? $listRs['data'] : [];
        $pagination = isset($listRs['pagination']) ? $listRs['pagination'] : [];

        return $this->render('works', [
            'model' => $model,
            'list' => $list,
            'pagination' => $pagination,
            'params' => $params
        ]);
    }

    /**
     * 发布作品
     * @return string
     */
    public function actionWorksAdd()
    {
        $model = new StuPubCenter();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            error_reporting(0);
            $uploadedFile = yii\web\UploadedFile::getInstance($model, 'pic');  //Artcile[thumb]存储的图片字段
            //var_dump($uploadedFile);exit;
            if ($uploadedFile === null || $uploadedFile->hasError) {
                Yii::$app->getSession()->setFlash('error', '文件不存在，缺少上传的文章图片');

                return $this->redirect('works');
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
            $model->pic = $save_url . $new_file_name;
            $rs = $model->save();
            if ($rs) {
                Yii::$app->getSession()->setFlash('success', '发布作品成功');
            } else {
                Yii::$app->getSession()->setFlash('success', '发布作品失败');
            }

            return $this->redirect('works');
        }

        return $this->render('works-add', [
            'model' => $model,
            'action' => 'add'
        ]);
    }

    /**
     * 导出工作
     * @return yii\web\Response
     */
    public function actionExport()
    {
        $model = new StuWorksNow();
        $param = Yii::$app->request->queryParams;
        $listRs = $model->export($param);

        return $this->redirect('work-history');
    }
    /**
     * Finds the MajorWorkRelation model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Manager the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Manager::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

}