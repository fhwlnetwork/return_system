<?php
/**
 * Created by PhpStorm.
 * User: cyc
 * Date: 15-7-29
 * Time: 下午4:30
 */

namespace center\modules\user\controllers;

use center\controllers\ValidateController;
use center\modules\auth\models\SrunJiegou;
use center\modules\strategy\models\Product;
use center\modules\user\models\Base;
use center\modules\user\models\Operator;
use common\extend\Excel;
use common\models\FileOperate;
use common\models\User;
use yii;

class OperatorController extends ValidateController
{
    public function actionIndex()
    {
        $model = new Operator();
        $params = Yii::$app->request->queryParams;
        $user_name = isset($params['user_name']) ? $params['user_name'] : '';
        $lists = [];
        if (!empty($user_name)) {
            $model->userModel = Base::findOne(['user_name' => $user_name]);
            if (empty($model->userModel)) {
                Yii::$app->getSession()->setFlash('danger', Yii::t('app', 'The user {username} does not exist.', ['username' => $params['user_name']]));
                $this->redirect(['index']);
            }
            if (isset($model->userModel->products_id) && !empty($model->userModel->products_id)) {
                $lists = $model->getProObjList($model->userModel->user_id, $model->userModel->products_id);
            }

        }
        //var_dump($lists);exit;
        return $this->render('index', [
            'model' => $model,
            'lists' => $lists,
            'userModel' => $model->userModel, //用户模型
        ]);
    }

    public function actionEdit()
    {
        $model = new Operator();
        $model->scenario = 'bind';
        $params = Yii::$app->request->queryParams;
        $user_name = isset($params['user_name']) ? $params['user_name'] : '';
        $uid = isset($params['uid']) ? $params['uid'] : '';
        $pid = isset($params['products_id']) ? $params['products_id'] : '';
        $action = isset($params['action']) ? $params['action'] : 'edit';
        if(empty($user_name) || empty($uid) || empty($pid)){
            Yii::$app->getSession()->setFlash('danger', Yii::t('app', 'Parameter error'));
            return $this->redirect(['index']);
        }
        $proObj = $model->getOneProObj($uid, $pid);
        $model->setAttributes($proObj);
        //操作
        if($action == 'relieve'){//解绑
            $res = $model->updateProObj($user_name, $pid, '', '', isset($proObj['user_available']) ? $proObj['user_available'] : $model::STATUS_S);
        }elseif($action == 'start'){//启用
            $res = $model->updateProObj($user_name, $pid, $proObj['mobile_phone'], $proObj['mobile_password'], $model::STATUS_S);
        }elseif($action == 'close'){//禁用
            $res = $model->updateProObj($user_name, $pid, $proObj['mobile_phone'], $proObj['mobile_password'], $model::STATUS_F);
        }
        if(isset($res)){
            if($res){
                Yii::$app->getSession()->setFlash('success', Yii::t('app', 'operate success.'));
            }else{
                Yii::$app->getSession()->setFlash('danger', Yii::t('app', 'operate failed.'));
            }
            return $this->redirect(['index?user_name='.$user_name]);
        }
        //修改提交
        if($model->load(Yii::$app->request->post()) && $model->validate()){
            $res = $model->saves(Yii::$app->request->post('Operator'));
            if($res){
                Yii::$app->getSession()->setFlash('success', Yii::t('app', 'operate success.'));
            } else {
                Yii::$app->getSession()->setFlash('error', Yii::t('app', 'operate failed.'));
            }
            return $this->redirect(['index?user_name='.$user_name]);
        }
        //var_dump($proObj);exit;
        return $this->render('edit',[
            'model' => $model,
            'proObj' => $proObj,
        ]);
    }

    public function actionImport(){
        $model = new Operator();
        $model->scenario = 'import';

        //下载模板
        $params = Yii::$app->request->post();
        if (isset($params['download']) && $params['download'] == Yii::t('app', 'batch excel download')) {
            $res = $model->excelDemo();
            if ($res) {
                $file = FileOperate::dir('temp') . 'operator_excel_demo_' . date('YmdHis') . '.xls';
                $title = Yii::t('app', 'reference template');
                Excel::arrayToExcel($res, $file, $title);
                return Yii::$app->response->sendFile($file);
            }
        }else{
            if(!empty($params)){
                //保存excel文件
                $model->file = yii\web\UploadedFile::getInstance($model, 'file');
                if ($model->file) {
                    $sn = date('YmdHis');
                    $newFileName = FileOperate::dir('import') . 'operator' . '_' . $sn . '.' . $model->file->extension;
                    $model->file->saveAs($newFileName);
                } else {
                    Yii::$app->getSession()->setFlash('error', Yii::t('app', 'batch excel help7'));
                    return $this->refresh();
                }
                $excelData = Excel::set_file($newFileName);
                $model->excelData = $excelData[0]; //excel 数据
                unset($model->excelData[1]);
                //如果小于等于1行数据，那么是个空表格
                if (empty($model->excelData)) {
                    Yii::$app->getSession()->setFlash('error', Yii::t('app', 'batch excel help6'));
                    return $this->refresh();
                }
                //处理上传的数据并进行批量绑定
                $res = $model->batchImport($model->excelData, $params['Operator']['product_id']);
                if (!empty($res)) {
                    $file =  Yii::t('app','batch import result'). date( 'Y-m-d H:i:s' ).'.xls';
                    $title = Yii::t('app', 'batch excel help11');

//                    Yii::$app->getSession()->setFlash('success', Yii::t('app', 'batch import help2', [
//                        'success_num'=>$res['success_num'],
//                        'failed_num'=>$res['failed_num'],
//                    ]));

                    Excel::header_file($res['data'], $file, $title);exit;
                    //return $this->redirect(['import']);
                } else {
                    Yii::$app->getSession()->setFlash('error', Yii::t('app', 'batch excel help6'));
                    return $this->refresh();
                }
            }
        }

        //获取产品列表
        $productModel = new Product();
        $model->products = $productModel->getNameOfList();

        //var_dump($model->products);exit;
        return $this->render('import',[
            'model' => $model,
        ]);
    }

    public function actionExport(){
        $model = new Operator();
        $model->scenario = 'export';

        //下载模板
        $params = Yii::$app->request->post();
        if($params){
            $query = Operator::find()->where(['products_id'=>$params['Operator']['product_id']]);
            //如果非超级管理员，则需要去判断
            if(!User::isSuper()){
                //判断组
                //所有可以管理的组
                $canMgrOrg = SrunJiegou::getAllNode();
                $query->leftJoin('users', 'users.user_name=user_products.user_name');
                $query->andWhere(['users.group_id'=>$canMgrOrg]);
            }

            $data = $query->asArray()->all();
            if(!empty($data)){
                $res = $model->batchExport($data);
                $file =  FileOperate::dir('account') . '/batch_export_res'. '_' . date( 'YmdHis' ).'.xls';
                $title = Yii::t('app', 'batch import help4');
                Excel::header_file($res, $file, $title);exit;
            }else{
                Yii::$app->getSession()->setFlash('error', Yii::t('app', 'batch import help5'));
                return $this->refresh();
            }

        }
        //获取产品列表
        $productModel = new Product();
        $model->products = $productModel->getNameOfList();
        return $this->render('export',[
            'model' => $model,
        ]);
    }

    public function actionBatchEdit(){
        $model = new Operator();
        $model->scenario = 'batch-edit';
        $mgrName = Yii::$app->user->identity->username;

        $get = Yii::$app->request->queryParams;
        //下载文件
        if(Yii::$app->request->get('action') && Yii::$app->request->get('action')=='download'){
            if(Yii::$app->session->get('batch_add_download_file')){
                return Yii::$app->response->sendFile(Yii::$app->session->get('batch_add_download_file'));
            }else{
                Yii::$app->getSession()->setFlash('error', Yii::t('app', 'batch excel help31'));
            }
        }

        $params = Yii::$app->request->post();

        if(!empty($params)){
            if (isset($params['download']) && $params['download'] == Yii::t('app', 'batch excel download')) {
                if (count($params['selectFields']) == 1) {
                    Yii::$app->getSession()->setFlash('error', Yii::t('app', 'batch edit help6'));

                    return $this->refresh();
                }
                $res = $model->excelEditDemo($params['selectFields']);
                if ($res) {
                    $file = FileOperate::dir('temp') . 'operator_excel_demo_' . date('YmdHis') . '.xls';
                    $title = Yii::t('app', 'reference template');
                    Excel::arrayToExcel($res, $file, $title);
                    return Yii::$app->response->sendFile($file);
                }
            }else if (isset($params['preview'])) {
                //选择的字段
                $selectFields = $params['selectFields'];
                if(!in_array($model->conditionField, $params['selectFields'])){
                    Yii::$app->getSession()->setFlash('error', Yii::t('app', 'batch edit help1'));
                    return $this->refresh();
                }
                if(count($selectFields) == count($model->conditionField)){
                    Yii::$app->getSession()->setFlash('error', Yii::t('app', 'batch edit help6'));
                    return $this->refresh();
                }
                $model->selectFields = $selectFields;
                //保存excel文件
                $model->file = yii\web\UploadedFile::getInstance($model, 'file');
                if ($model->file) {
                    $sn = date('YmdHis');
                    $newFileName = FileOperate::dir('import') . 'operator' . '_' . $sn . '.' . $model->file->extension;
                    $model->file->saveAs($newFileName);
                } else {
                    Yii::$app->getSession()->setFlash('error', Yii::t('app', 'batch excel help7'));
                    return $this->refresh();
                }
                $excelData = Excel::set_file($newFileName);
                $model->excelData = $excelData[0]; //excel 数据

                unset($model->excelData[1]);
                //如果小于等于1行数据，那么是个空表格
                if (empty($model->excelData)) {
                    Yii::$app->getSession()->setFlash('error', Yii::t('app', 'batch excel help6'));
                    return $this->refresh();
                }

                $model->selectFields = $params['selectFields'];
                Yii::$app->session->set($mgrName.'selectFields', $params['selectFields']);
                Yii::$app->session->set($mgrName.'batch_edit_file', $model->excelData);


                return $this->render('excel_preview', [
                    'model' => $model
                ]);
            }else{
                $model->selectFields = Yii::$app->session->get($mgrName.'selectFields');
                $model->excelData = Yii::$app->session->get($mgrName.'batch_edit_file');
                //处理上传的数据并进行批量绑定
                $res = $model->batchEdit($model->excelData);
                if (!empty($res)) {
                    $file =  FileOperate::dir('account') . '/user_add_'. date( 'YmdHis' ).'.xls';
                    $title = Yii::t('app', 'batch excel help11');
                    Excel::arrayToExcel($res['data'], $file, $title);
                    //把文件名写入session
                    Yii::$app->session->set('batch_add_download_file', $file);
                    //写日志开始
                    $logContent = Yii::t('app', 'user batch edit result', [
                        'mgr' => $mgrName,
                        'file' => Yii::t('app', 'down info', ['download_url' => yii\helpers\Url::to((['/user/group/down-load?file='.$file]))])
                    ] );
                    (new Base())->batchLog('', $logContent);
                    //写日志结束
                    Yii::$app->getSession()->setFlash('success', Yii::t('app', 'user batch edit result', [
                        'mgr'=>$mgrName,
                        'file' => Yii::t('app', 'down info', ['download_url' => yii\helpers\Url::to((['/user/group/down-load?file='.$file]))])
                    ]));
                } else {
                    Yii::$app->getSession()->setFlash('error', Yii::t('app', 'batch excel help6'));
                    return $this->refresh();
                }
            }
        }
        return $this->render('batch_edit',[
            'model' => $model,
        ]);
    }
}