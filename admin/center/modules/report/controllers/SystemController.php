<?php

namespace center\modules\report\controllers;

use Yii;
use mPDF;
use yii\helpers\Json;
use common\models\Redis;
use center\modules\report\models\Efficiency;
use center\modules\report\models\ServerType;
use center\modules\report\models\DashboardReports;
use center\modules\report\models\detail\BaseModel;
use center\modules\report\models\detail\EfficiencyBase;


/**
 * 系统运维
 * @package center\modules\report\controllers
 */
class SystemController extends \center\controllers\ValidateController
{
    const SRUN_INTF_MAIN = 'srun4_http_setting';
    //系统数据
    public function actionIndex()
    {
        $params = Yii::$app->request->post();
        $model = new BaseModel();
        if (!empty($params)) {
            if ($model->load($params) && $model->validateField()) {
                $model->setChildName();
                $model->getRealModel();
                $data = $model->realModel->getSystemStatus(0);
            }
        } else {
            $model->getRealModel();
            $data = $model->realModel->getSystemStatus(0);
        }

        if (!empty($data) && $data['code'] != 200) {
            Yii::$app->getSession()->setFlash('error', $data['msg']);
        }

        return $this->render('index', [
            'model' => $model,
            'data' => $data
        ]);
    }

    /**
     * 性能监控
     * @return string
     */
    public function actionEfficiency()
    {
        $model = new EfficiencyBase();
        $params = Yii::$app->request->post();
        if (!empty($params)) {
            if ($model->load($params) && $model->validateField()) {
                $model->setChildName();
                $model->getRealModel();
                $data = $model->realModel->getEfficiencyStatus(1);
            }
        } else {
            $model->getRealModel();
            $data = $model->realModel->getEfficiencyStatus(1);
        }


        if ($data['code'] != 200) {
            Yii::$app->getSession()->setFlash('error', $data['msg']);
        }

        return $this->render('efficiency', [
            'model' => $model,
            'data' => $data
        ]);
    }

    /**
     * 生成pdf报告
     * @return string
     */
    public function actionCreatePdf()
    {
        $model = new BaseModel();
        $post = Yii::$app->request->post();
        $source = [];

        if (!empty($post)) {
            if ($model->load($post) && $model->validateField(true)) {
                //去获取数据
                if (!empty($model->timePoint)) {
                    $model->setTime();
                }
                $source = $model->getSourceByType();
            }
        } else {
            $model->start_time = $model->stop_time = date('Y-m-d');
        }
        Yii::$app->session->set('system_params', $post);

        return $this->render('create-pdf', [
            'model' => $model,
            'source' => $source,
            'unit' => ($model->sql_type == 'cpu') ? '%' : '',
        ]);
    }

    /**
     * 生成pdf
     * @return bool|\yii\web\Response
     */
    public function actionCreatePdfInner()
    {
        $post = Yii::$app->session->get('system_params');
        $model = new BaseModel();
        $params = Yii::$app->session->get('system_params');
        $source = [];
        if (empty($params)) {
            Yii::$app->getSession()->setFlash('error', Yii::t('app', 'monitor help4'));

            return $this->redirect('create-pdf');
        }
        if ($model->load($post) && $model->validateField(true)) {
            //去获取数据
            if (!empty($model->timePoint)) {
                $model->setTime();
            }
            $source = $model->getSourceByType();
        }
        if ($model->sql_type == 'efficiency') {
            $base = $source['base'];
        } else {
            $fileName = Yii::$app->session->get('filename');
        }

        $content = $this->renderPartial('create-pdf', ['model' => $model, 'source' => $source, 'show' => true, 'filename' => $fileName, 'base' => $base]);
        $pdf = new mPDF('+aCJK', 'A4', '', '', 32, 25, 27, 25, 16, 13);
        $pdf->useAdobeCJK = true;
        $pdf->autoScriptToLang = true;
        $pdf->SetDisplayMode('fullpage');
        $pdf->writeHTML($content);

        $fileName = iconv('utf-8', 'gbk', $model->device_ip.':'.$model->getAllSqlType()[$model->sql_type].'监控.pdf');
        $pdf->Output($fileName, 'D');
        return true;
    }

    /**
     * @return bool
     */
    public function actionImageSave()
    {
        $post = Yii::$app->request->post();
        $picInfo = $post['baseimg'];
        $savingDir = 'uploads/monitor/';
        if (!is_dir($savingDir)) {
            mkdir($savingDir);
        }

        $streamFileRand = $savingDir.$post['sql_type'].$post['proc'].'.png'; //图片名
        Yii::$app->session->set('filename', $streamFileRand);
        preg_match('/(?<=base64,)[\S|\s]+/',$picInfo,$picInfoW);//处理base64文本
        file_put_contents($streamFileRand,base64_decode($picInfoW[0]));//文件写入

        return true;
    }

    /**
     * 获取某个ip状态
     * @param $ip
     * @param $type
     * @return string
     */
    public function actionAjaxGetOneStatus($ip, $type)
    {
        $model = new Efficiency();
        $data = $model->Checktype($type, $ip);

        return Json::encode($data);
    }

    /**
     * @return string
     */
    public function actionAjaxGetTypes()
    {
        $Server = new ServerType();
        $types = $Server->findServerType();
        $data = [];
        try {
            if (!empty($types)) {
                foreach ($types as $key => $val) {
                    if (is_array($val) && !empty($val)) {
                        foreach ($val as $one) {
                            foreach ($one as $detail) {
                                $data[] = $detail;
                            }
                        }
                    }
                }
                $rs = ['code' => 200, 'rows' => $data];
            } else {
                $rs = ['code' => 404, 'msg' => '未注册相关管理服务器'];
            }
        } catch (\Exception $e) {
            $rs = ['code' => 404, 'msg' => '发生异常'];
        }


        return $rs;
    }

    /**
     * 获取某台机器最近三小时详情
     * @param $ip
     * @param $type
     * @return string
     */
    public function  actionGetOneDetail($ip, $type)
    {
        $model = new ServerType();
        if ($type == 'Portal') {
            $type = 'Portal Server';
        }
        $attributes = $model->attributeLabels()[$type];
        $data = $source = [];
        $i = 0;
        foreach ($attributes as $key => $val) {
            $data[$key] = DashboardReports::dataStatus($key, $ip);
            if ($i == 0) {
                $source = $model->getSource($key, $ip);
                $status = $key;
            }

            $i++;
        }

        $num = count($attributes) == 3 ? 4 : 3;


        return $this->renderAjax('chart', [
            'rows' => $data,
            'attributes' => $attributes,
            'num' => $num,
            'source' => $source,
            'status' => $status,
            'ip' => $ip
        ]);

    }

    /**
     * 获取某类型的监控状态
     * @param $ip
     * @param $type
     * @return string
     */
    public function actionAjaxGetOneTypeStatus($ip, $type)
    {
        $model = new ServerType();
        $source = $model->getSource($type, $ip);

        return Json::encode($source);
    }

    /**
     * 获取所有机器状态
     * @return string
     */
    public function actionAjaxGetAllData()
    {
        $rs = $this->actionAjaxGetTypes();
        $model = new Efficiency();

        if ($rs['code'] == 200) {
            try {
                foreach ($rs['rows'] as $v) {
                    $id = str_replace('.', '', $v['ip']) . $v['devicename'] . str_replace(' ', '', $v['type']);
                    $data = $model->Checktype($v['type'], $v['ip']);
                    $rs['data'][$id] = $data;
                    $rs['data'][$id]['ip'] = $v['ip'];
                }
            } catch (\Exception $e) {
                $rs = ['code' => 500, 'msg' => '获取机器状态发生异常'];
            }
        }

        return Json::encode($rs);
    }

    /**
     * 设置4k
     * @return string
     */
    public function actionSetting()
    {
        $key = self::SRUN_INTF_MAIN;
        $isExist = Redis::executeCommand('exists', $key);
        $detail = [];
        if ($_POST) {
            $hash = Redis::arrayToHash($_POST);
            $rs = Redis::executeCommand('hmset', $key, $hash);
            if ($rs) {
                Yii::$app->getSession()->setFlash('success', Yii::t('app', 'operate success.'));
            } else {
                Yii::$app->getSession()->setFlash('danger', Yii::t('app', 'operate failed.'));
            }
            $detail = $_POST;
        }
        if ($isExist) {
            $data = Redis::executeCommand('hgetall', $key);
            $detail = Redis::hashToArray($data);
        }

        return $this->render('setting', [
            'detail' => $detail
        ]);
    }
}
