<?php
/**
 * Created by PhpStorm.
 * User: qk
 * Date: 15-9-2
 * Time: 下午2:56
 */

namespace center\modules\report\controllers;


use yii;
use common\extend\Excel;
use center\controllers\ValidateController;
use center\modules\report\models\error\ErrorBase;

class ErrorController extends ValidateController
{
    public function actionLogin()
    {
        $post = Yii::$app->request->post();
        $model = new ErrorBase();
        $source = [];
        $get = Yii::$app->request->queryParams;
        $lang = Yii::$app->language;

        if (isset($get['export'])) {
            $data = Yii::$app->session->get('data');
            $detail = Yii::$app->session->get('detail');
            $error = Yii::$app->session->get('error');
            $dates = Yii::$app->session->get('date');
            $excelData = [];
            $excelData[0] = ['合计|用户名','日期', '认证失败次数|信息'];

            if (!empty($data)) {
                foreach ($dates as $time) {
                    $date = date('Y-m-d', $time);
                    if (isset($detail[$date])) {
                        foreach ($detail[$date] as $name => $val) {
                            $excelData[] = [$val['user_name'], $date, $error[explode(':', $val['err_msg'])[0]]];
                        }
                    }

                    $byte = isset($data[$date]) ? $data[$date] : 0;
                    $excelData[] = ['合计', $date, $byte];
                }
                $excelData = array_merge($excelData, $data);
                $file = '认证错误统计' . '.xls';
                $title = Yii::t('app', 'batch export');
                //将内容写入excel文件
                Excel::header_file($excelData, $file, $title);
                exit;
            }
        } else {
            if (!empty($post)) {
                if($model->load($post) && $model->validate()){
                    //    $source = $model->getData();
                    $model->getRealModel();
                    if ($model->validateField()) {
                        $source = $model->realModel->getData();
                    }
                }
            } else {
                //  $source = $model->getData();
                $model->getRealModel();
                $source = $model->realModel->getData();
            }


            Yii::$app->session->set('data', $source['table']);
            Yii::$app->session->set('detail', $source['detail']);
            Yii::$app->session->set('date', $source['date']);
            Yii::$app->session->set('error', $source['error']);
            //var_dump($source);exit;

            return $this->render('login',[
                'model' => $model,
                'source' => $source,
                'lang' => $lang
            ]);
        }

    }

    /**
     * 按照错误信息统计用户个数
     */
    public function actionUserLogin(){
        $params = Yii::$app->request->queryParams;
        $model = new \center\modules\report\models\Login();
        $data = [];
        $model->start_At = $params['start_At'];
        $model->stop_At = $params['stop_At'];
        if($model->validate() && $model->validateField()){
            $data = $model->getUsersByErr($params);
        }
        //var_dump($data);exit;
        //var_dump($model->load(Yii::$app->request->post()) && $model->validate() && $model->validateField());exit;
        return $this->render('user_login',[
            'model' => $model,
            'params' => $params,
            'list' => $data,
        ]);
    }
} 