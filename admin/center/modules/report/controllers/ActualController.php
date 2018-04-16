<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2017/6/21
 * Time: 11:03
 */
namespace center\modules\report\controllers;

use Yii;
use center\modules\report\models\detail\Actual;
/**
 * Class OperateController 实际在网人数
 * @package center\modules\report\controllers
 */
class ActualController extends \center\controllers\ValidateController
{

    /**
     * 实际在网人数
     * @return string
     */
    public function actionIndex()
    {
        $model = new Actual();
        $params = Yii::$app->request->post();
        $source = $model->getData($params);
        Yii::$app->session->set('actual_number', $params);

        return $this->render('index', [
            'model' => $model,
            'params' => $params,
            'source' => $source
        ]);
    }

    /**
     * 下载明细
     * @param $date
     * @return \yii\web\Response
     */
    public function actionDownload($date)
    {
        //下载某天的实际上网人数, 超过5W csv导出10W封顶
        $params = Yii::$app->session->get('actual_number');
        $model = new Actual();
        $rs = $model->exportData($date, $params);
        if ($rs['code'] != 200) {
            Yii::$app->getSession()->setFlash('error', $rs['msg']);

            return $this->redirect('index');
        }
        exit;
    }
}
