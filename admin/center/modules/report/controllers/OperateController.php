<?php

namespace center\modules\report\controllers;

use center\modules\report\models\DetailDay;
use Yii;
use center\extend\Tool;
use common\extend\Excel;
use center\modules\strategy\models\Control;
use center\modules\strategy\models\Billing;
use center\modules\report\models\OnlineReportPoint;
use center\modules\report\models\SrunDetailDay;
use center\modules\report\models\UserReportProducts;
use center\modules\auth\models\SrunJiegou;
use center\modules\report\models\TerminalTypeReport;

/**
 * Class OperateController 运营报表
 * @package center\modules\report\controllers
 */
class OperateController extends \center\controllers\ValidateController
{
    /**
     * 流量统计
     * @return string
     */
    public function actionIndex()
    {
        $model = new SrunDetailDay();

        $showField = SrunJiegou::getAllIdNameVal();
        $delkey = array_search('/', $showField);
        unset($showField[$delkey]);

        $post = Yii::$app->request->post();
        $get = Yii::$app->request->queryParams;

        if (isset($get['export'])) {
            $data = Yii::$app->session->get('data');
            $unit = Yii::$app->session->get('unit');
            $excelData = [];
            $excelData[0] = $model->getTableHeader();

            if (!empty($data)) {
                $i = 1;
                foreach ($data as $time => $v) {
                    $detail = isset($v['detail']) ? $v['detail'] : [];
                    $msg = empty($detail) ? $showField[$v['group_id']] : $showField[$v['group_id']] . '总共';
                    $excelData[$i][] = $time;
                    $excelData[$i][] = $msg;
                    $excelData[$i][] = $v['total'] . $unit;
                    $i++;
                    if (!empty($detail)) {
                        foreach ($detail as $group => $val) {
                            $excelData[$i][] = $time;
                            $excelData[$i][] = $showField[$group];
                            $excelData[$i][] = $val . $unit;
                            $i++;
                        }
                    }
                }

                $file = Yii::t('app', 'report/operate/index') . '.xls';
                $title = Yii::t('app', 'batch export');
                //将内容写入excel文件
                Excel::header_file($excelData, $file, $title);
                exit;
            }
        }
        if ($post) {
            if ($model->load($post) && $model->validate()) {
                if (!empty($model->btn_chooses)) {
                    $model->getTime();
                }
                if ($model->validateField($post['SrunDetailDay'])) {
                    if (isset($post['SrunDetailDay']['group_id'])) {
                        $son = SrunJiegou::getAllChildDatas($post['SrunDetailDay']['group_id']);
                        $searchField = explode(',', $post['SrunDetailDay']['group_id']);
                        foreach ($searchField as $val) {
                            $fieldArray[$val] = $showField[$val];
                        }
                    } else {
                        $fieldArray = array_slice($showField, 0, 1, true);
                        $post['SrunDetailDay']['group_id'] = implode(',', array_keys($fieldArray));
                        //$fieldArray  = implode(',', $fieldArray);
                        $son = SrunJiegou::getAllChildDatas(array_keys($fieldArray)[0]);
                    }

                    $source = $model->flowReportLine($son, $fieldArray);
                }
            }
        } else {
            $fieldArray = array_slice($showField, 0, 1, true);
            $post['SrunDetailDay']['group_id'] = implode(',', array_keys($fieldArray));
            //$fieldArray  = implode(',', $fieldArray);
            $son = SrunJiegou::getAllChildDatas(array_keys($fieldArray)[0]);
            $model->getTime();
            $source = $model->flowReportLine($son, $fieldArray);
        }

        //var_dump($source);exit;
        Yii::$app->session->set('data', $source['table']);
        Yii::$app->session->set('unit', $model->unit);

        return $this->render('flow_report_line', [
            'model' => $model,
            'source' => $source,
            'showField' => $showField,
            'params' => $post
        ]);
    }

    //时长统计
    public function actionTimelong()
    {
        $model = new SrunDetailDay();
        $model->sql_type = 'time_long';
        $post = Yii::$app->request->post();
        $groupName = '';
        if ($post) {
            if ($model->load($post) && $model->validate()) {
                if (!empty($model->btn_chooses)) {
                    $model->getTime();
                }
                if ($model->validateField($post['SrunDetailDay'])) {
                    if (isset($post['SrunDetailDay']['group_id']) && !empty($post['SrunDetailDay']['group_id'])) {
                        $model->user_group_id = $post['SrunDetailDay']['group_id'];
                        $groupId = explode(',', $post['SrunDetailDay']['group_id']);
                        if (in_array(1, $groupId)) {
                            $flag = true;
                        } else {
                            $names = SrunJiegou::getAllIdNameVal();
                            foreach ($groupId as $id) {
                                $groupName .= $names[$id].',';
                            }
                            $fieldArray = SrunJiegou::getNodeId($groupId);
                            $flag = false;
                        }

                    } else {
                        $fieldArray = [];
                        $flag = true;
                    }

                    $source = $model->getTimelong($fieldArray, $flag);
                }
            }
        } else {
            $model->setDefault();
            $source = $model->getTimelong([], true);
        }
        $groupName = rtrim($groupName, ',');

        return $this->render('time_long-page', [
            'model' => $model,
            'source' => $source,
            'params' => $post,
            'groupName' => $groupName
        ]);
    }

    /**
     * 活跃度
     * @return string
     */
    public function actionActivity()
    {
        $model = new SrunDetailDay();
        $model->sql_type = 'login_count';
        $post = Yii::$app->request->post();
        $groupName = '';
        if ($post) {
            if ($model->load($post) && $model->validate()) {
                if (!empty($model->btn_chooses)) {
                    $model->getTime();
                }
                if ($model->validateField($post['SrunDetailDay'])) {
                    if (isset($post['SrunDetailDay']['group_id']) && !empty($post['SrunDetailDay']['group_id'])) {
                        $model->user_group_id = $post['SrunDetailDay']['group_id'];
                        $groupId = explode(',', $post['SrunDetailDay']['group_id']);
                        if (in_array(1, $groupId)) {
                            $flag = true;
                        } else {
                            $names = SrunJiegou::getAllIdNameVal();
                            foreach ($groupId as $id) {
                                $groupName .= $names[$id].',';
                            }
                            $fieldArray = SrunJiegou::getNodeId($groupId);
                            $flag = false;
                        }

                    } else {
                        $fieldArray = [];
                        $flag = true;
                    }

                    $source = $model->getTimelong($fieldArray, $flag);
                }
            }
        } else {
            $model->setDefault();
            $source = $model->getTimelong([], true);
        }
        $groupName = rtrim($groupName, ',');

        return $this->render('activity-page', [
            'model' => $model,
            'source' => $source,
            'params' => $post,
            'groupName' => $groupName
        ]);
    }

    //在线数
    public function actionOnline()
    {
        $model = new OnlineReportPoint();

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->validateField()) {
            $source = $model->getOnline($model);

            return $this->render('online-page', [
                'model' => $model,
                'source' => $source
            ]);
        } else {
            return $this->render('online-page', [
                'model' => $model
            ]);
        }
    }

    //终端类型
    public function actionTerminal()
    {
        $model = new TerminalTypeReport();

        $post = Yii::$app->request->post();
        if ($model->load($post) && $model->validate() && $model->validateField()) {
            $source = $model->terminal($model);

            return $this->render('terminal-page', [
                'model' => $model,
                'source' => $source,
            ]);
        } else {
            return $this->render('terminal-page', [
                'model' => $model
            ]);
        }
    }

    //产品分析
    public function actionUserproduct()
    {

        $model = new UserReportProducts();

        $showField = SrunJiegou::getAllIdNameVal();
        $delkey = array_search('/', $showField);
        unset($showField[$delkey]);

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->validateField()) {

            if (isset(Yii::$app->request->post()['UserReportProducts']['user_group_id'])) {

                $searchField = Yii::$app->request->post()['UserReportProducts']['user_group_id'];
                foreach ($searchField as $val) {
                    $fieldArray[$val] = $showField[$val];
                }

            } else {
                $fieldArray = $showField;
            }
            if ($_POST['search'] == 'all') {
                $source = $model->getData($model, $fieldArray, false);
            } else {
                $source = $model->getData($model, $fieldArray, true);
            }
        } else {
            $fieldArray = array_slice($showField, 0, 5, true);
        }
        Yii::$app->session->set('product_usergroup', $fieldArray);

        return $this->render('user-product-page', [
            'model' => $model,
            'source' => $source,
            'showField' => $showField
        ]);

    }

    //用户状态统计
    public function actionUserstatus()
    {

        $model = new UserReportProducts();

        $showField = SrunJiegou::getAllIdNameVal();
        $delkey = array_search('/', $showField);
        unset($showField[$delkey]);

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->validateField()) {

            if (isset(Yii::$app->request->post()['UserReportProducts']['user_group_id'])) {

                $searchField = Yii::$app->request->post()['UserReportProducts']['user_group_id'];
                foreach ($searchField as $val) {
                    $fieldArray[$val] = $showField[$val];
                }

            } else {
                $fieldArray = $showField;
            }

            Yii::$app->session->set('status_usergroup', $fieldArray);

            if ($_POST['search'] == 'all') {
                $source = $model->getStatusData($model, $fieldArray, false);
            } else {
                $source = $model->getStatusData($model, $fieldArray, true);
            }
            return $this->render('user-status-page', [
                'model' => $model,
                'source' => $source,
                'showField' => $showField
            ]);
        } else {
            $source = $model->getStatusData("", $showField, true);
            return $this->render('user-status-page', [
                'source' => $source,
                'model' => $model,
                'showField' => $showField
            ]);
        }
    }

    //用户组分组产品收费
    public function actionUsergroup()
    {
        $showField = SrunJiegou::getAllIdNameVal();
        $delkey = array_search('/', $showField);
        unset($showField[$delkey]);
        $post = Yii::$app->request->post();

        $model = new UserReportProducts();
        $get = Yii::$app->request->queryParams;

        if (isset($get['export'])) {
            $data = Yii::$app->session->get('data');
            $names = Yii::$app->session->get('products');
            $excelData = [];
            $excelData[0] = [Yii::t('app', 'user_group'), Yii::t('app', 'network default font1') . '|' . Yii::t('app', 'products name'), Yii::t('app', 'amount')];

            if (!empty($data)) {
                $i = 1;
                foreach ($data as $time => $v) {
                    $detail = isset($v['detail']) ? $v['detail'] : [];
                    $msg = empty($detail) ? Yii::t('app', 'no pay record') : Yii::t('app', 'network default font1');
                    $excelData[$i][] = $time;
                    $excelData[$i][] = $msg;
                    $excelData[$i][] = $v['data'] . Yii::t('app', '$');
                    $i++;
                    if (!empty($detail)) {
                        foreach ($detail as $group => $val) {
                            $excelData[$i][] = $time;
                            $excelData[$i][] = $val['product_id'] . ":" . $names[$val['product_id']];
                            $excelData[$i][] = $val['num'] . Yii::t('app', '$');
                            $i++;
                        }
                    }
                }

                $file = Yii::t('app', 'report/operate/usergroup') . '.xls';
                $title = Yii::t('app', 'batch export');
                //将内容写入excel文件
                Excel::header_file($excelData, $file, $title);
                exit;
            }
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->validateField()) {

            //如果搜索的产品没有勾选, 则查询所有产品.
            if (isset($post['group_id'])) {
                $show = explode(',', $post['group_id']);
                $son = SrunJiegou::getAllChildDatas($post['group_id']);
                foreach ($show as $val) {
                    $fieldArray[$val] = $showField[$val];
                }
            } else {
                $fieldArray = array_slice($showField, 0, 1, true);
                $post['SrunDetailDay']['group_id'] = implode(',', array_keys($fieldArray));
                //$fieldArray  = implode(',', $fieldArray);
                $son = SrunJiegou::getAllChildDatas(array_keys($fieldArray)[0]);
            }

            //将查询的数据保存在session中
            Yii::$app->session->set('usergroup', $fieldArray);
        } else {
            $model->start_At = date('Y-m-1');
            $model->stop_At = date('Y-m-d');
            $fieldArray = array_slice($showField, 0, 1, true);
            $post['SrunDetailDay']['group_id'] = implode(',', array_keys($fieldArray));
            //$fieldArray  = implode(',', $fieldArray);
            $son = SrunJiegou::getAllChildDatas(array_keys($fieldArray)[0]);
            $post['group_id'] = implode(',', array_keys($fieldArray));
        }

        $source = $model->getGroupData($son, $fieldArray, true);
        Yii::$app->session->set('usergroup', $fieldArray);
        Yii::$app->session->set('data', $source['table']);
        Yii::$app->session->set('products', $source['products']);

        return $this->render('user-group-page', [
            'model' => $model,
            'source' => $source,
            'showField' => $showField,
            'params' => $post
        ]);
    }


    /**
     * 统计流量最大值， 平均值
     * @return string
     */
    public function actionBytes()
    {
        $model = new SrunDetailDay();
        $model->sql_type = 'time_long';
        $post = Yii::$app->request->post();
        $get = Yii::$app->request->queryParams;

        if (isset($get['export'])) {
            $data = Yii::$app->session->get('data');
            $dates = json_decode(Yii::$app->session->get('date'), true);
            $flag = Yii::$app->session->get('flag');
            $excelData = [];
            if (!$flag) {
                $excelData[0] = [Yii::t('app', 'date'), Yii::t('app', 'sum_bytes'), Yii::t('app', 'max_bytes'), Yii::t('app', 'aver_bytes'), Yii::t('app', 'user_number')];
            } else {
                if ($flag == 2) {
                    $excelData[0] = [Yii::t('app', 'date'), Yii::t('app', 'user_name'), Yii::t('app', 'flux'), Yii::t('app', 'time_long')];
                } else {
                    $excelData[0] = [Yii::t('app', 'date'), Yii::t('app', 'user_name'), Yii::t('app', 'flux')];
                }

            }

            if (!empty($data)) {
                if ($flag) {
                    foreach ($data as $name => $v) {
                        if ($flag == 2) {
                            $time = date('Y-m-d', $v['record_day']);
                            $byte = Tool::bytes_format($v['total_bytes']);
                            $time_long = Tool::seconds_format($v['time_long']);
                            $excelData[] = [$time, $v['user_name'], $byte, $time_long];
                        } else {
                            $time = date('Y-m-d', $v['record_day']);
                            $byte = Tool::bytes_format($v['total']);
                            $excelData[] = [$time, $name, $byte];
                        }
                    }
                } else {
                    foreach ($dates as $time) {
                        $time = strtotime($time);
                        $date = date('Y-m-d', $time);
                        if (isset($data[$time])) {
                            $byte = Tool::bytes_format($data[$time]['total']);
                            $max = Tool::bytes_format($data[$time]['max_bytes']);
                            $average = Tool::bytes_format($data[$time]['total'] / $data[$time]['user_number']);
                            $excelData[] = [$date, $byte, $max, $average, $data[$time]['user_number']];
                        } else {
                            $excelData[] = [$date, 0, 0, 0, 0];
                        }
                    }
                }

                $file = Yii::t('app', 'Detail Log') . '.xls';
                $title = Yii::t('app', 'batch export');
                //将内容写入excel文件
                Excel::header_file($excelData, $file, $title);
                exit;
            }
        }
        if (!empty($post)) {
            if ($model->load($post) && $model->validate() && $model->validateField($post['SrunDetailDay'])) {
                if (!empty($model->btn_chooses)) {
                    $model->getTime();
                }
                if ($model->validateField($post['SrunDetailDay'])) {
                    $source = $model->getBytesDetails();
                }
            }
        } else {
            $model->setDefault();
            $source = $model->getBytesDetails();
        }


        Yii::$app->session->set('data', $source['table']);
        Yii::$app->session->set('detail', $source['detail']);
        Yii::$app->session->set('date', $source['dates']);
        Yii::$app->session->set('flag', $model->flag);
        //var_dump($model->flag);exit;

        return $this->render('bytes', [
            'model' => $model,
            'source' => $source,
        ]);
    }

    /**
     * 用户流量段统计
     * @return string
     */
    public function actionBytesDetail()
    {
        $model = new SrunDetailDay();
        $model->sql_type = 'bytes';
        $post = Yii::$app->request->post();
        $groupName = '';
        if ($post) {
            if ($model->load($post) && $model->validate()) {
                if (!empty($model->btn_chooses)) {
                    $model->getTime();
                }
                if ($model->validateField($post['SrunDetailDay'])) {
                    if (isset($post['SrunDetailDay']['group_id']) && !empty($post['SrunDetailDay']['group_id'])) {
                        $groupId = explode(',', $post['SrunDetailDay']['group_id']);
                        $model->user_group_id = $post['SrunDetailDay']['group_id'];
                        if (in_array(1, $groupId)) {
                            $flag = true;
                        } else {
                            $names = SrunJiegou::getAllIdNameVal();
                            foreach ($groupId as $id) {
                                $groupName .= $names[$id].',';
                            }
                            $fieldArray = SrunJiegou::getNodeId($groupId);
                            $flag = false;
                        }

                    } else {
                        $fieldArray = [];
                        $flag = true;
                    }

                    $source = $model->getTimelong($fieldArray, $flag);
                }
            }
        } else {
            //$fieldArray  = implode(',', $fieldArray);
            $model->setDefault();
            $source = $model->getTimelong([], true);
        }
        $groupName = rtrim($groupName, ',');

        return $this->render('user-bytes', [
            'model' => $model,
            'source' => $source,
            'params' => $post,
            'groupName' => $groupName
        ]);
    }

    /**
     * 导出明细
     * @return \yii\web\Response
     */
    public function actionDetail()
    {
        $params = Yii::$app->request->queryParams;
        $model = new SrunDetailDay();
        foreach ($params as $key => $val) {
            $model->$key = $val;
        }
        $rs = $model->exportData();
        if ($rs['code'] != 200) {
            Yii::$app->getSession()->setFlash('error', $rs['msg']);

            return $this->redirect(Yii::$app->request->referrer);

        }
    }
}
