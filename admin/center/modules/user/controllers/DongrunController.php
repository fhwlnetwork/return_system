<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2016/9/29
 * Time: 9:01
 */

namespace center\modules\user\controllers;


use center\controllers\ValidateController;
use center\modules\user\models\Base;
use center\modules\user\models\DongrunModel;
use Yii;
use common\models\Redis;

/**
 * 完成处理dongrun数据转移
 * Class DongrunController
 * @package center\modules\user\controllers
 */
class DongrunController extends ValidateController
{
    /**
     * 设置dongrun相关操作参数
     * @return string
     */
    public function actionIndex()
    {
        begin:
        set_time_limit(0);
        //分别获取中间表字段和用户表字段
        $model = new DongrunModel();
        $ssoFields = $model->getTableField();
        $ssoFields = array_combine($ssoFields, $ssoFields);
        $userModel = new Base();
        $searchFields = $userModel->getSearchField();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {

            $batchType = $_POST['batchType'];
            if ($batchType == 1) {
                $model->scenario = 'add';
                if (!isset($_POST["DongrunModel"]['mustAddExecFields'])) {
                    Yii::$app->getSession()->setFlash('error', Yii::t('app', 'dongrun help6'));

                    goto start;
                }
            } else {
                $model->scenario = 'edit';
                if (!isset($_POST["DongrunModel"]['mustEditExecFields'] )) {
                    Yii::$app->getSession()->setFlash('error', Yii::t('app', 'dongrun help6'));

                    goto start;
                }
            }
            if ($model->load(Yii::$app->request->post()) && $model->validate()) {
                //以post方式上传
                $key = DongrunModel::HASH_DONGRUN_KEY;
                if ($batchType == 1) {
                    $mustAddFields = $_POST['DongrunModel']['mustAddExecFields'];
                    $mustAddFieldsJson = json_encode($mustAddFields);
                    $array = [
                        'mustAddExecFields' => $mustAddFieldsJson,
                        'state' => $model->state,
                        'user_type' => $model->user_type,
                        'password_type' => $model->password_type,
                        'allow_delete' => $model->allow_delete,
                        'sso_add_fields' => $model->sso_add_fields,
                        'user_add_fields' => $model->user_add_fields
                    ];
                } else {
                    $mustEditFields = $_POST['DongrunModel']['mustEditExecFields'];
                    $mustEditFieldsJson = json_encode($mustEditFields);
                    $array = [
                        'mustEditExecFields' => $mustEditFieldsJson,
                        'state' => $model->state,
                        'password_type' => $model->password_type,
                        'allow_delete' => $model->allow_delete,
                        'user_type' => $model->user_type,
                        'sso_edit_fields' => $model->sso_edit_fields,
                        'user_edit_fields' => $model->user_edit_fields
                    ];
                }
                $hash = Redis::arrayToHash($array);
                //将对应信息写入redis
                $rs = Redis::executeCommand('hmset', $key, $hash);
                if ($rs) {
                    Yii::$app->getSession()->setFlash('success', Yii::t('app', 'operate success.'));
                } else {
                    Yii::$app->getSession()->setFlash('error', Yii::t('app', 'operate error.'));
                }
            }
        }
        start:

        $model->allow_delete = 0;
        $key = DongrunModel::HASH_DONGRUN_KEY;
        $flag = Redis::executeCommand('exists', $key);
        if ($flag) {
            //已经设置过了
            $hash = Redis::executeCommand('hgetall', $key);
            $array = Redis::hashToArray($hash);
            foreach ($array as $k => $v) {
                if (in_array($k, ['mustAddExecFields', 'mustEditExecFields'])) {
                    $v = json_decode($v, true);
                }
                $model->$k = $v;
            }
        }

        return $this->render('index', [
            'ssoFields' => $ssoFields,
            'userFields' => $searchFields,
            'model' => $model,
        ]);
    }




}