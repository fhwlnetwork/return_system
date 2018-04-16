<?php
/**
 * Created by PhpStorm.
 * User: ligang
 * Date: 2015/3/17
 * Time: 10:46
 */

namespace center\modules\user\controllers;

use center\controllers\ValidateController;
use center\modules\auth\models\SrunJiegou;
use center\modules\financial\models\PayList;
use center\modules\financial\models\PayType;
use center\modules\financial\models\TransferBalance;
use center\modules\financial\models\WaitCheck;
use center\modules\setting\models\ExtendsField;
use center\modules\strategy\models\IpPool;
use center\modules\strategy\models\IpPartRelation;
use center\modules\strategy\models\IpPart;
use center\modules\strategy\models\Package;
use center\modules\strategy\models\Product;
use center\modules\user\models\Base;
use center\modules\user\models\BatchExcel;
use center\modules\user\models\Users;
use common\extend\Excel;
use common\models\Redis;
use common\models\User;
use common\models\FileOperate;
use yii;
use center\modules\user\models\BatchAdd;
use center\modules\log\models\LogWriter;
use yii\helpers\Url;

class BatchController extends ValidateController
{
    const SETTLE_ACCOUNT_LIMIT = 10000;  //最大结算数，因excel导出有限制
    const INSERT_UPDATE_USER_LIMIT = 5000; //最大开户或者编辑用户数
    const DELETE_USER_LIMIT = 5000; //销户数限制
    const EXPORT_USER_LIMIT = 30000; //导出用户限制
    const REFUND_USER_LIMIT = 5000; //退费限制
    const PAY_USER_LIMIT = 5000; //批量缴费限制
    const BUY_USER_LIMIT = 5000; //批量购买限制

    /**
     * 批量开户
     * @return string
     * @throws yii\web\ForbiddenHttpException
     */
    public function actionAdd()
    {
        $baseModel = new Users();
        $model = new BatchAdd();
        $productList = $model->can_product;
        $params = Yii::$app->request->post();
        //判断产品
        $products = array_keys($model->can_product);
        if (!$model->flag) {
            //当前管理员开户数
            $open_num = $model->getOpenUserNum();
            $max_open_num = Yii::$app->user->identity->max_open_num;
            if (empty($params)) {
                Yii::$app->getSession()->setFlash('info', Yii::t('app', 'current_open_num', ['open_num' => $open_num, 'still_num' => $max_open_num - $open_num < 0 ? 0 : $max_open_num - $open_num]));
            }
        }
        //下载文件
        if (Yii::$app->request->get('action') && Yii::$app->request->get('action') == 'download') {
            if (Yii::$app->session->get('batch_add_download_file')) {
                return Yii::$app->response->sendFile(Yii::$app->session->get('batch_add_download_file'));
            } else {
                Yii::$app->getSession()->setFlash('error', Yii::t('app', 'batch excel help31'));
            }
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {

            //判断是否还有开户的权限
            if (!$model->flag) {
                if ($max_open_num - $open_num - $model->gen_num < 0) {
                    Yii::$app->getSession()->setFlash('error', Yii::t('app', 'open_num_error'));
                    return $this->refresh();
                }
            }
            //判断组织结构
            if (!in_array($model->group_id, array_keys($model->can_group))) {
                throw new yii\web\ForbiddenHttpException(Yii::t('app', 'message 401 1'));
            }

            foreach ($model->products_id as $pid => $v) {
                if (!in_array($pid, $products)) {
                    throw new yii\web\ForbiddenHttpException(Yii::t('app', 'message 401 2'));
                }
            }

            $res = $model->batch_add_user();

            if ($res) {
                $file = FileOperate::dir('account') . '/user_add_' . date('YmdHis') . '.xls';
                $title = Yii::t('app', 'batch add help7');
                Excel::arrayToExcel($res['list'], $file, $title);

                //把文件名写入session
                Yii::$app->session->set('batch_add_download_file', $file);
                //将结果写入日志
                // 写日志
                //'srun 批量开户 完成,成功记录：'.count($excel_ok).'条，失败记录：'.count($excel_err).'条,详情：{file};' ;
                $logString = Yii::t('app', 'group msg11', [
                    'mgr' => Yii::$app->user->identity->username,
                    'ok_num' => $res['ok'],
                    'error_num' => $res['err'],
                    'target' => 'users',
                    'action' => 'add',
                    'action_type' => 'User Batch',
                    'file' => Yii::t('app', 'down info', ['download_url' => Url::to(['/user/group/down-load?file=' . $file])]),
                ]);
                $rs = $baseModel->batchLog('', $logString);


                Yii::$app->getSession()->setFlash('success', Yii::t('app', 'batch add help1', [
                    'ok_num' => $res['ok'],
                    'err_num' => $res['err'],
                    'download_url' => yii\helpers\Url::to(['action' => 'download'])
                ]));

            } else {
                Yii::$app->getSession()->setFlash('error', Yii::t('app', 'operate failed.'));
            }
        }


        //缴费方式默认值
        $model->payType = PayType::getDefaultType();

        return $this->render('add', [
            'model' => $model,
            'productList' => $productList,
        ]);
    }

    /*jin
     * ipNumsAjax
     * 1批量开户的时候ajax获取可用ip总数
     * */
    public function actionIpNumsAjax()
    {
        $IpPart = new IpPart();
        //如果ippart表没有数据就不需要分配ip
        $count = $IpPart->find()
            ->select(['count(id) as ids'])
            ->asArray()
            ->one();
        if ($count['ids'] <= 0) {
            return 'no_num';
            die;
        }
        //根据groupid和product_id获取ip总数
        $group_id = Yii::$app->request->post('group_id');
        $product_id = Yii::$app->request->post('product_id');
        $ip = (new IpPool())->getIpNums($group_id, $product_id);
        return !empty($ip) ? $ip : 'no';
    }

    /**
     * excel 批量处理
     * @return string|yii\web\Response|static
     * @throws yii\web\ForbiddenHttpException
     */
    public function actionExcel()
    {
        //下载文件
        if (Yii::$app->request->get('action') && Yii::$app->request->get('action') == 'download') {
            if (Yii::$app->session->get('batch_excel_download_file')) {
                return Yii::$app->response->sendFile(Yii::$app->session->get('batch_excel_download_file'));
            } else {
                Yii::$app->getSession()->setFlash('error', Yii::t('app', 'batch excel help31'));
            }
        }
        //下载文件
        if (Yii::$app->request->get('file')) {
            return Yii::$app->response->sendFile(Yii::$app->request->get('file'));
        }

        $model = new BatchExcel();
        $msg = $this->actionAjaxGetLimit();
        $params = Yii::$app->request->post();
        if (!$model->flag) {
            //当前管理员开户数
            $open_num = $model->getOpenUserNum();
            $max_open_num = Yii::$app->user->identity->max_open_num;
            if (empty($params)) {
                Yii::$app->getSession()->setFlash('info', Yii::t('app', 'current_open_num', ['open_num' => $open_num, 'still_num' => $max_open_num - $open_num < 0 ? 0 : $max_open_num - $open_num]));
            }
        }

        $session = Yii::$app->session->get('batch_excel');
        if ($session && $session['selectField']) {
            $model->selectField = $session['selectField'];
        }

//var_dump($model);exit;
        return $this->render('excel', [
            'model' => $model,
            'msg' => $msg,
        ]);
    }

    /**
     * 批量操作预览
     * @return string|yii\web\Response
     * @throws yii\web\ForbiddenHttpException
     */
    public function actionPreview()
    {

        set_time_limit(0);
        //提交的数据
        //var_dump($_POST);exit;
        $post = Yii::$app->request->post();
        //var_dump($post, $post['batchType'] == 7 && !empty($post['export_group_id']));exit;
        if (isset($post['download'])) {
            header('location:download', true, 307);
            exit;
        }
        if ($post['batchType'] == 4) {
            //导出
            header('location:export', true, 307);
            exit;
        }
        if ($post['batchType'] == 5 && isset($post['refund']) && $post['refund'] == 2 && !isset($post['preview'])) {
            //按用户组退费
            header('location:refund', true, 307);
            exit;
        }
        if ($post['batchType'] == 7 && !empty($post['export_group_id'])) {
            //按用户组退费
            header('location:checkout', true, 307);
            exit;
        }
        // var_dump($post, $_GET);exit;
        if ($post['batchType'] == 8) { //购买
            $userNames = isset($post['username']) ? $post['username'] : '';

            return $this->redirect('buy?username=' . $userNames);
        }
        $model = new BatchExcel();
        //获取session
        foreach ($post as $key => $val) {
            if ($model->hasProperty($key)) {
                $model->$key = $val;
            }
        }
        if (!in_array($model->batchType, [1, 2, 3, 4, 5, 6, 7, 8])) {
            Yii::$app->getSession()->setFlash('error', Yii::t('app', 'batch excel help2'));
            return $this->refresh();
        }

        //已选择的字段
        if ($model->batchType == 1) {
            $model->selectField = isset($post['addSelectField']) ? $post['addSelectField'] : [];
            $addSelectField = $model->selectField;
        } elseif ($model->batchType == 2) {
            $model->selectField = isset($post['editSelectField']) ? $post['editSelectField'] : [];
            $editSelectField = $model->selectField;
        } elseif ($model->batchType != 8) {
            $model->selectField = isset($post['selectField']) ? $post['selectField'] : [];
        }

        //已选择的导出字段
        $model->selectExportField = isset($post['selectExportField']) ? $post['selectExportField'] : [];
        //已选择的组
        $model->export_group_id = (isset($post['export_group_id']) && !empty($post['export_group_id'])) ? explode(',', $post['export_group_id']) : [];

        //下载模板 或 预览 需要对勾选的数据进行验证

        //导入模式， 用户名和密码 必需, 2015/7/14改为密码可以输入MD5
        if ($model->batchType == 1) {
            if (!in_array('user_name', $model->selectField) || (!in_array('user_password', $model->selectField) && !in_array('user_password_md5', $model->selectField))) {
                Yii::$app->getSession()->setFlash('error', Yii::t('app', 'batch excel help3'));
                return $this->refresh();
            }
            if ((in_array('carrier_mobile_phone', $model->selectField) || in_array('carrier_mobile_password', $model->selectField) || in_array('carrier_status', $model->selectField)) && !in_array('products_id', $model->selectField)) {
                Yii::$app->getSession()->setFlash('error', Yii::t('app', 'batch excel help38'));
                return $this->refresh();
            }
        } //修改模式， 用户名 必需，而且 只要有一个其他字段
        else if ($model->batchType == 2) {
            if (!in_array('user_name', $model->selectField)) {
                Yii::$app->getSession()->setFlash('error', Yii::t('app', 'batch excel help4'));
                return $this->refresh();
            } else if (count($model->selectField) == 1) {
                Yii::$app->getSession()->setFlash('error', Yii::t('app', 'batch excel help5'));
                return $this->refresh();
            }
            if ((in_array('carrier_mobile_phone', $model->selectField) || in_array('carrier_mobile_password', $model->selectField) || in_array('carrier_status', $model->selectField)) && !in_array('products_id', $model->selectField)) {
                Yii::$app->getSession()->setFlash('error', Yii::t('app', 'batch excel help38'));
                return $this->refresh();
            }
        } //销户模式、退费模式
        else if ($model->batchType == 3 || $model->batchType == 5) {
            $model->selectField = ['user_name'];
        } //购买套餐模式 //必须勾选所有字段
        else if ($model->batchType == 6 && $model->buyObject == 'package') {
            $is_error = array_diff(array_keys($model->buyField), $model->selectField);
            if (!empty($is_error)) {
                Yii::$app->getSession()->setFlash('error', Yii::t('app', 'batch excel help44'));
                return $this->refresh();
            }
        } //用户结算
        else if ($model->batchType == 7) {
            $model->selectField = ['user_name'];
        } else if ($model->batchType == 8) {
            $userNames = isset($post['username']) ? $post['username'] : '';

            return $this->redirect('buy?username=' . $userNames);
        }

        if ($model->deleteType == 2) {
            //这里按用户组生成文件
            //如果有选择组
            if ($model->export_group_id) {
                foreach ($model->export_group_id as $group_id) {
                    if (!array_key_exists($group_id, $model->can_group)) {
                        throw new yii\web\ForbiddenHttpException(Yii::t('app', 'message 401 1'));
                    }
                }
            }
            $model->selectExportField = ['user_name'];
            $groupId = SrunJiegou::getNodeId($model->export_group_id);
            $res = Users::find()->select('user_name')->where(['group_id' => $groupId])->asArray()->all();
            $model->excelData = $res;
            $newFileName = "";
            if (empty($res)) {
                Yii::$app->getSession()->setFlash('error', Yii::t('app', 'batch excel help58'));
                return $this->refresh();
            }
            if ($res) {
                $users = $excel = [];
                foreach ($res as $k => $one) {

                    if ($k == 0) {
                        $users[1][0] = $excel[0][0] = $one[0];
                    } else {
                        $users[][0] = $excel[][0] = $one['user_name'];
                    }
                }
                $model->excelData = $users;
                $file = FileOperate::dir('export') . '/user_excel_' . $model->batchType . '_' . date('YmdHis') . '.xls';
                $title = Yii::t('app', 'batch excel help11');
                Excel::arrayToExcel($excel, $file, $title);
                $newFileName = $file;
            } else {
                Yii::$app->getSession()->setFlash('error', Yii::t('app', 'batch excel help7'));

                return $this->redirect('excel');
            }
        } else {
            $model->file = yii\web\UploadedFile::getInstance($model, 'file');

            if ($model->file && $model->validate()) {
                $newFileName = FileOperate::dir('import') . '/batch' . '_' . date('YmdHis') . rand(100, 999) . '.' . $model->file->extension;
                $model->file->saveAs($newFileName);
            } else {
                Yii::$app->getSession()->setFlash('error', Yii::t('app', 'batch excel help7'));

                return $this->redirect('excel');
            }

            $excelData = Excel::set_file($newFileName);
            $count = count($excelData[0]) - 1;
            //var_dump($count, $excelData);exit;
            $limit = Yii::$app->session->get('limit');
            if ($count > $limit) {
                $msg = Yii::t('app', 'a1', [
                    'mgr' => $model->_mgrName,
                    'count' => $count,
                    'limit' => $limit
                ]);
                Yii::$app->getSession()->setFlash('danger', $msg);

                return $this->redirect('excel');
            }
            $model->excelData = $excelData[0];
        }

        if ($model->batchType == 1) {
            //判断是否还有开户的权限
            if (!$model->flag) {
                $open_num = $model->getOpenUserNum();
                $max_open_num = Yii::$app->user->identity->max_open_num;

                if ($max_open_num - $open_num - count($model->excelData) < 1) {
                    Yii::$app->getSession()->setFlash('error', Yii::t('app', 'open_num_error'));

                    return $this->redirect('excel');
                }
            }
        }
        //如果小于等于1行数据，那么是个空表格
        if (count($model->excelData) <= 1) {
            Yii::$app->getSession()->setFlash('error', Yii::t('app', 'batch excel help6'));

            return $this->redirect('excel');
        }
        $get_group_id = '';
        $get_product_id = '';
        foreach ($model->excelData as $k => $v) {
            if ($k == 1) {
                continue;
            }
            $get_group_id .= $v[2] . ',';
            $get_product_id .= $v[3] . ',';
        }
        //保存在session中
        Yii::$app->session->set('batch_excel', [
            'addSelectField' => $addSelectField, //选择的字段
            'editSelectField' => $editSelectField, //选择的字段
            'exportSelectField' => $model->selectExportField,//导出选择的字段
            'selectField' => $model->selectField, //选择的字段
            'fileName' => $newFileName, //文件名
            'batchType' => $model->batchType, //类型
            'setting' => $model->setting,//设置
            'buyObject' => $model->buyObject,//
        ]);


        return $this->render('excel_preview', [
            'model' => $model,
        ]);
    }

    /**
     * 批量excel处理
     * @return yii\web\Response
     */
    public function actionOperate()
    {
        $model = new BatchExcel();
        $userModel = new Users;
        $session = Yii::$app->session->get('batch_excel');
        //session 不存在了
        if (empty($session['selectField']) || empty($session['batchType']) || empty($session['fileName']) || empty($session['setting'])) {
            Yii::$app->getSession()->setFlash('error', Yii::t('app', 'batch excel help8'));

            return $this->redirect('excel');
        }
        //excel文件已过期
        if (!is_file($session['fileName'])) {
            Yii::$app->getSession()->setFlash('error', Yii::t('app', 'batch excel help9'));

            return $this->redirect('excel');
        }
        $excelData = Excel::set_file($session['fileName']);
        $model->excelData = $excelData[0]; //excel 数据
        $model->batchType = $session['batchType']; //批量处理的 类型
        $model->selectField = $session['selectField']; //选择的字段
        $model->setting = $session['setting']; //设置项
        $model->buyObject = $session['buyObject']; //购买对象
        $res = $model->batch_data();

        if ($res) {
            //Yii::$app->session->set('batch_excel', '');//删除session
            Yii::$app->session->set('batch_excel', ['fileName' => '']);
            $file = FileOperate::dir('account') . '/user_excel_' . $model->batchType . '_' . date('YmdHis') . '.xls';
            $title = Yii::t('app', 'batch excel help11');
            //将内容写入excel文件
            Excel::arrayToExcel($res['list'], $file, $title);
            //设置下载文件session
            Yii::$app->session->set('batch_excel_download_file', $file);
            // 写日志
            if ($model->batchType == 7) {
                //'srun 批量结算 完成,成功记录：'.count($excel_ok).'条，失败记录：'.count($excel_err).'条; 详情:file'
                $logContent = Yii::t('app', 'batch excel help57', [
                    'mgr' => Yii::$app->user->identity->username,
                    'file' => Yii::t('app', 'down info', ['download_url' => Url::to(['/user/group/down-load?file=' . $file])]),
                    'ok_num' => $res['ok'],
                    'err_num' => $res['err']
                ]);
                $logData = [
                    'operator' => Yii::$app->user->identity->username,
                    'target' => 'users',
                    'action' => 'settleAccount',
                    'action_type' => 'User Batch',
                    'content' => $logContent,
                    'class' => get_class($this),
                    'type' => 1
                ];
                LogWriter::write($logData);
                //日志结束
            } else if ($model->batchType != 4) {
                $type = '';
                switch ($model->batchType) {
                    case  '1':
                        $type = 'batch excel help25';
                        break;
                    case  '2':
                        $type = 'batch excel help26';
                        break;
                    case  '3':
                        $type = 'batch excel help27';
                        break;
                    case  '5':
                        $type = 'batch excel help35';
                        break;
                    case  '6':
                        if ($model->buyObject == 1) {
                            $type = 'batch excel help52';
                        } else {
                            $type = 'batch excel help53';
                        }
                        break;
                }
                // 写日志
                //'srun 批量导入用户 完成,成功记录：'.count($excel_ok).'条，失败记录：'.count($excel_err).'条,操作结果:{file};'
                $logContent = Yii::t('app', $type, [
                    'mgr' => Yii::$app->user->identity->username,
                    'ok_num' => $res['ok'],
                    'err_num' => $res['err'],
                    'file' => Yii::t('app', 'down info', ['download_url' => Url::to(['/user/group/down-load?file=' . $file])]),
                ]);
                $userModel->batchLog('', $logContent);
                //日志结束
            }
            Yii::$app->getSession()->setFlash('success', Yii::t('app', 'batch excel help10', [
                'ok_num' => $res['ok'],
                'err_num' => $res['err'],
                'download_url' => yii\helpers\Url::to('/user/batch/excel?action=download')
            ]));

            return $this->redirect('excel');
        } else

            return $this->redirect('excel');
    }

    /**
     * 按用户组退费
     * @return yii\web\Response
     */
    public function actionRefund()
    {
        set_time_limit(0);
        $post = Yii::$app->request->post();
        $model = new BatchExcel();
        if (!empty($post['export_group_id'])) {
            $groupIds = explode(',', $post['export_group_id']);
            $diff = array_diff($groupIds, array_keys($model->can_group));
            if ($diff) {
                //有部分用户组不可管理
                Yii::$app->getSession()->setFlash('danager', Yii::t('app', 'message 401 1'));

                return $this->redirect('excel');
            }
        }
        foreach ($post as $k => $v) {
            if ($model->hasProperty($k)) {
                if ($k == 'export_group_id' && !empty($v)) {
                    $v = explode(',', $v);
                }
                $model->$k = $v;
            }
        }


        $res = $model->batch_data();
        if ($res) {
            $file = FileOperate::dir('account') . '/user_excel_' . $model->batchType . '_' . date('YmdHis') . '.xls';
            $title = Yii::t('app', 'batch excel help11');
            //将内容写入excel文件
            Excel::arrayToExcel($res['list'], $file, $title);
            //设置下载文件session
            Yii::$app->session->set('batch_excel_download_file', $file);
            Yii::$app->getSession()->setFlash('success', Yii::t('app', 'batch excel help10', [
                'ok_num' => $res['ok'],
                'err_num' => $res['err'],
                'download_url' => yii\helpers\Url::to('/user/batch/excel?action=download')
            ]));

            //写入日志
            //'srun 批量结算 完成,成功记录：'.count($excel_ok).'条，失败记录：'.count($excel_err).'条; 详情:file'
            $logContent = Yii::t('app', 'batch excel help57', [
                'mgr' => Yii::$app->user->identity->username,
                'file' => Yii::t('app', 'down info', ['download_url' => Url::to('/user/batch/excel?file=' . $file)]),
                'ok_num' => $res['ok'],
                'err_num' => $res['err']
            ]);
            //var_dump($file,$logContent, Yii::t('app', 'down info', ['download_url' => Url::to(['action'=>'download'])]));exit;
            $logData = [
                'operator' => Yii::$app->user->identity->username,
                'target' => 'users',
                'action' => 'settleAccount',
                'action_type' => 'User Batch',
                'content' => $logContent,
                'class' => get_class($this),
                'type' => 1
            ];
            LogWriter::write($logData);
        }

        return $this->redirect('excel');
    }

    /**
     * 用户组方式结算
     * @return yii\web\Response
     */
    public function actionCheckout()
    {
        $post = Yii::$app->request->post();
        $model = new BatchExcel();
        if (!empty($post['export_group_id'])) {
            $groupIds = explode(',', $post['export_group_id']);
            $diff = array_diff($groupIds, array_keys($model->can_group));
            if ($diff) {
                //有部分用户组不可管理
                Yii::$app->getSession()->setFlash('danager', Yii::t('app', 'message 401 1'));

                return $this->redirect('excel');
            }
        }
        foreach ($post as $k => $v) {
            if ($model->hasProperty($k)) {
                if ($k == 'export_group_id') {
                    $v = explode(',', $v);
                }
                $model->$k = $v;
            }
        }
        $res = $model->batch_data();
        if ($res) {
            $file = FileOperate::dir('account') . '/user_excel_' . $model->batchType . '_' . date('YmdHis') . '.xls';
            $title = Yii::t('app', 'batch excel help11');
            //将内容写入excel文件
            Excel::arrayToExcel($res['list'], $file, $title);
            //设置下载文件session
            Yii::$app->session->set('batch_excel_download_file', $file);
            Yii::$app->getSession()->setFlash('success', Yii::t('app', 'batch excel help10', [
                'ok_num' => $res['ok'],
                'err_num' => $res['err'],
                'download_url' => yii\helpers\Url::to('/user/batch/excel?action=download')
            ]));

            //写入日志
            //'srun 批量结算 完成,成功记录：'.count($excel_ok).'条，失败记录：'.count($excel_err).'条; 详情:file'
            $logContent = Yii::t('app', 'batch excel help57', [
                'mgr' => Yii::$app->user->identity->username,
                'file' => Yii::t('app', 'down info', ['download_url' => Url::to('/user/batch/excel?file=' . $file)]),
                'ok_num' => $res['ok'],
                'err_num' => $res['err']
            ]);
            //var_dump($file,$logContent, Yii::t('app', 'down info', ['download_url' => Url::to(['action'=>'download'])]));exit;
            $logData = [
                'operator' => Yii::$app->user->identity->username,
                'target' => 'users',
                'action' => 'settleAccount',
                'action_type' => 'User Batch',
                'content' => $logContent,
                'class' => get_class($this),
                'type' => 1
            ];
            LogWriter::write($logData);
        }

        return $this->redirect('excel');
    }


    /**
     * 批量导出
     * @return $this|yii\web\Response
     */
    public function actionExport()
    {
        $post = Yii::$app->request->post();
        $model = new BatchExcel();
        if (!empty($post['export_group_id'])) {
            $groupIds = explode(',', $post['export_group_id']);
            $diff = array_diff($groupIds, array_keys($model->can_group));
            if ($diff) {
                //有部分用户组不可管理
                Yii::$app->getSession()->setFlash('danger', Yii::t('app', 'message 401 1'));

                return $this->redirect('excel');
            }
        }

        foreach ($post as $k => $v) {
            if ($model->hasProperty($k)) {
                if ($k == 'export_group_id' && !empty($v)) {
                    $v = explode(',', $v);
                }
                $model->$k = $v;
            }
        }
        $res = $model->batch_data();
        if ($res) {
            $file = FileOperate::dir('export') . '/user_excel_' . $model->batchType . '_' . date('YmdHis') . '.xls';
            $title = Yii::t('app', 'batch excel help11');
            Excel::arrayToExcel($res, $file, $title, 'Excel5');

            return Yii::$app->response->sendFile($file);
        } else {
            return $this->redirect('excel');
        }
    }


    /**
     * 批量给用户的产品缴费 ，调试时期使用
     *
     */
    public function _actionTransferMoney()
    {
        //查询所有用户
        $query = Base::find()->where('user_id>210');
        foreach ($query->each(10) as $one) {
            $data = [
                'productPay' => [
                    4 => 1000,
                ],
            ];
            (new PayList())->payByUser($one->user_name, $data, 4);
            echo $one->user_name . '<br />';
        }
    }

    public function actionExportPackages($product_id)
    {
        $array = [
            '0' => [
                '用户名',
                '未用完的套餐',
            ]
        ];
        if ($product_id) {
            $packageModel = new Package();
            $uids = Redis::executeCommand('LRANGE', 'list:products:' . $product_id, [0, -1]);
            $users = Base::find()->andWhere(['user_id' => $uids])->all();
            if ($users) {
                $packages = $packageModel->getNameOfList();
                foreach ($users as $one) {
                    $packInfo = $packageModel->getOneByUidAndPid($one['user_id'], $product_id);
                    $arr = [];
                    $arr[0] = $one['user_name'];
                    if ($packInfo) {
                        $data = [];
                        foreach ($packInfo['detail'] as $packOne) {
                            if ($packOne['usage_rate'] > 0) {
                                if (isset($data[$packOne['package_id']])) {
                                    $data[$packOne['package_id']] += 1;
                                } else {
                                    $data[$packOne['package_id']] = 1;
                                }
                            }
                        }
                        $packs = [];
                        foreach ($data as $packid => $num) {
                            $packs[] = $packages[$packid] . '(' . $num . '个)';
                        }
                        $arr[1] = implode(', ', $packs);
                    }
                    $array[] = $arr;
                }
            }
        }
        $title = '用户的未用完的套餐';
        $file = $title . '.xls';
        Excel::header_file($array, $file, $title);
        exit;
    }

    public function actionBatchCancelPackages($product_id)
    {
        $array = [
            '0' => [
                '用户名',
                '产品',
                '未用完的套餐',
                '退套餐金额'
            ]
        ];
        $pacids = [2, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14];
        if ($product_id) {
            $packageModel = new Package();
            $uids = Redis::executeCommand('LRANGE', 'list:products:' . $product_id, [0, -1]);
            $users = Base::find()->andWhere(['user_id' => $uids])->all();
            if ($users) {
                $products = (new Product())->getAllNameArr();

                foreach ($users as $one) {
                    $packInfo = $packageModel->getOneByUidAndPid($one['user_id'], $product_id);
                    $arr = [];
                    $arr[0] = $one['user_name'];
                    if ($packInfo) {
                        foreach ($packInfo['detail'] as $packOne) {
                            if (in_array($packOne['package_id'], $pacids)) {
                                $packageInfo = $packageModel->getOne($packOne['package_id']);
                                $amount = 0;
                                if (round($packOne['usage_rate'], 2) > 0 && $packageInfo['amount'] > 0) {//没用完的才能退钱
                                    //取消套餐成功后，必须把套餐金额退回电子钱包
                                    //添加电子钱包
                                    $amount = $packageInfo['amount'];
                                    $model = Base::findOne($one['user_id']);
                                    $model->balance += $amount;
                                    if ($model->save()) {
                                        //写转账记录
                                        $transferModel = new TransferBalance();
                                        $transferData = [
                                            'transfer_num' => $amount,
                                            'user_name_from' => $one['user_name'],
                                            'user_name_to' => $one['user_name'],
                                            'type' => 1,
                                            'product_id' => $product_id,
                                        ];
                                        $trans_res = $transferModel->insertData($transferData);
                                        if ($trans_res) {
                                            //操作记录
                                            $product_name = $products[$product_id];
                                            $log = Yii::t('app', 'user base help39', ['mgr' => Yii::$app->user->identity->username, 'user_name' => $one['user_name'], 'product_name' => $product_name, 'package_name' => $packOne['package_name'], 'amount' => $amount]);
                                            $packageModel->cancelPackageLog($one['user_name'], $log);
                                        }
                                    }
                                } else {
                                    //操作记录
                                    $product_name = $products[$product_id];
                                    $log = Yii::t('app', 'user base help39', ['mgr' => Yii::$app->user->identity->username, 'user_name' => $one['user_name'], 'product_name' => $product_name, 'package_name' => $packOne['package_name'], 'amount' => '0(已用光)']);
                                    $packageModel->cancelPackageLog($one['user_name'], $log);
                                }
                                $packageModel->delPackageObj($one['user_name'], $product_id, $packOne['package_id'], $packOne['user_package_id']);
                                $array[] = [$one['user_name'], $products[$product_id], 'ID:' . $packOne['package_id'] . '[' . $packageInfo['package_name'] . ']', $amount];
                            }
                        }
                    }
                }
            }
        }
        $title = '用户的未用完的套餐';
        $file = $title . '.xls';
        Excel::header_file($array, $file, $title);
        exit;
    }

    /**
     * 批量缴费
     *
     * @return string|yii\web\Response
     */
    public function actionBuy()
    {
        $get = Yii::$app->request->queryParams;
        $post = Yii::$app->request->post();
        $model = new BatchExcel();
        $userModel = new Users();
        if (!empty($post)) {
            if (isset($post['users'])) {
                $userIds = array_keys($post['users']);
                $query = $userModel->find()->select(['user_id', 'user_name']);
                $query->andWhere(['user_id' => $userIds]);
                //非超级管理员可以管理的组织结构
                if (!User::isSuper()) {
                    $canMgrOrg = SrunJiegou::getAllNode();
                    $query->andWhere(['group_id' => $canMgrOrg]);
                }
                $users = $query->asArray()->all();
                $userModel->payModel = new PayList();
                $rs = $userModel->payModel->batchPayListSecond($users, $post['users'], $post['PayList']);
                Yii::$app->getSession()->setFlash('success', $rs);

                return $this->refresh();
            }
        }
        $userNames = isset($get['username']) ? explode(',', $get['username']) : '';
        // var_dump($get,$userNames);exit;
        $users = [];
        if (!empty($userNames)) {
            $users = $userModel->find()
                ->select('user_id,user_name,user_real_name,balance')
                ->where(['user_name' => $userNames])
                ->asArray()
                ->all();
        }
        if (!empty($users)) {
            $userData = [];
            foreach ($users as $k => $v) {
                //获取该用户产品使用情况
                $userData[] = $v;
                $v = Users::findOne(['user_id' => $v['user_id']]);
                $products = $model->getProductByName($v['user_id']);
                if ($products) {
                    $userData[$k]['products_id'] = array_keys($products);
                    //可以管理的并且是已订购的产品列表
                    $ids = array_keys($products);
                    $orderedProductList = $model->getOrderedProduct($ids, $v['user_id']);

                    foreach ($products as $key => $val) {
                        $waitCheckModel = WaitCheck::findOne(['user_id' => $v->user_id, 'products_id' => $key]);
                        $userData[$k]['checkout_date'][] = !empty($waitCheckModel) ? date('Y-m-d', $waitCheckModel->checkout_date) : '--';
                        $balance = isset($orderedProductList[$key]['used']['user_balance']) ? number_format($orderedProductList[$key]['used']['user_balance'], 2) : '0';
                        $productName = $products[$key];
                        $userData[$k]['product_name'][] = $productName;
                        $userData[$k]['product_balance'][] = $balance;
                    }
                }
            }
            $payModel = new PayList();
            $extendFields = ExtendsField::getFieldsData('pay_list');
            $lists = $payModel->getPayElementList();
            //var_dump($lists['payTypeList']);exit;

            return $this->render('batch-buy', [
                'params' => $get,
                'model' => $payModel,
                'list' => $lists,
                'userModel' => $userData,
                'extendFields' => $extendFields, //参数
            ]);
        } else {
            Yii::$app->getSession()->setFlash('error', Yii::t('app', 'No results found.'));

            return $this->redirect('excel');
        }
    }

    /**
     * 下载模板
     * @return $this|yii\web\Response
     */
    public function actionDownload()
    {
        $model = new BatchExcel();
        $post = Yii::$app->request->post();
        foreach ($post as $k => $v) {
            if ($model->hasProperty($k)) {
                if ($k == 'export_group_id' && !empty($v)) {
                    $v = explode(',', $v);
                }
                $model->$k = $v;
            }
        }
        //已选择的字段
        if($model->batchType == 1){
            $model->selectField = isset($post['addSelectField']) ? $post['addSelectField'] : [];
        }elseif($model->batchType == 2){
            $model->selectField = isset($post['editSelectField']) ? $post['editSelectField'] : [];
        }elseif($model->batchType !=8){
            $model->selectField = isset($post['selectField']) ? $post['selectField'] : [];
        }
        //导入模式， 用户名和密码 必需, 2015/7/14改为密码可以输入MD5
        //已选择的导出字段
        $model->selectExportField = isset($post['selectExportField']) ? $post['selectExportField'] : [];
        //已选择的组
        $model->export_group_id = (isset($post['export_group_id']) && !empty($post['export_group_id'])) ? explode(',', $post['export_group_id']) : [];

        if ($model->batchType == 1) {
            if (!in_array('user_name', $model->selectField) || (!in_array('user_password', $model->selectField) && !in_array('user_password_md5', $model->selectField))) {
                Yii::$app->getSession()->setFlash('error', Yii::t('app', 'batch excel help3'));
                return $this->redirect('excel');
            }
            if ((in_array('carrier_mobile_phone', $model->selectField) || in_array('carrier_mobile_password', $model->selectField) || in_array('carrier_status', $model->selectField)) && !in_array('products_id', $model->selectField)) {
                Yii::$app->getSession()->setFlash('error', Yii::t('app', 'batch excel help38'));
                return $this->redirect('excel');
            }
        } //修改模式， 用户名 必需，而且 只要有一个其他字段
        else if ($model->batchType == 2) {
            if (!in_array('user_name', $model->selectField)) {
                Yii::$app->getSession()->setFlash('error', Yii::t('app', 'batch excel help4'));
                return $this->redirect('excel');
            } else if (count($model->selectField) == 1) {
                Yii::$app->getSession()->setFlash('error', Yii::t('app', 'batch excel help5'));
                return $this->redirect('excel');
            }
            if ((in_array('carrier_mobile_phone', $model->selectField) || in_array('carrier_mobile_password', $model->selectField) || in_array('carrier_status', $model->selectField)) && !in_array('products_id', $model->selectField)) {
                Yii::$app->getSession()->setFlash('error', Yii::t('app', 'batch excel help38'));
                return $this->redirect('excel');
            }
        } //销户模式、退费模式
        else if ($model->batchType == 3 || $model->batchType == 5) {
            $model->selectField = ['user_name'];
        } //购买套餐模式 //必须勾选所有字段
        else if ($model->batchType == 6 && $model->buyObject == 'package') {
            $is_error = array_diff(array_keys($model->buyField), $model->selectField);
            if (!empty($is_error)) {
                Yii::$app->getSession()->setFlash('error', Yii::t('app', 'batch excel help44'));
                return $this->redirect('excel');
            }
        } //用户结算
        else if ($model->batchType == 7) {
            $model->selectField = ['user_name'];
        }
        //批量缴费


        $res = $model->template();
        if ($res) {
            $file = FileOperate::dir('temp') . '/user_template_' . date('YmdHis') . '.xls';
            $title = Yii::t('app', 'batch excel help1');
            Excel::arrayToExcel($res, $file, $title);

            return Yii::$app->response->sendFile($file);
        }

        return $this->redirect('excel');

    }


    /**
     * ajax获取限制用户数
     * @param int $type
     * @return string
     */
    public function actionAjaxGetLimit($type = 1)
    {
        $limit = self::INSERT_UPDATE_USER_LIMIT;
        if (!in_array($type, [1, 2, 3, 5, 6, 8])) {
            if ($type == 4) {
                //导出
                $limit = self::EXPORT_USER_LIMIT;
            } else if ($type == 7) {
                //结算
                $limit = self::SETTLE_ACCOUNT_LIMIT;
            }
        }
        Yii::$app->session->set('limit', $limit);
        $msg = Yii::t('app', "批量操作, 可操作总数: $limit, 超出请分批处理");
        $isAjax = Yii::$app->request->isAjax;
        if ($isAjax) {
            return json_encode(['error' => 0, 'msg' => $msg]);
        }

        return $msg;
    }
}