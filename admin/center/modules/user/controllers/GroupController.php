<?php
/**
 * Created by PhpStorm.
 * User: qk
 * Date: 15-12-14
 * Time: 下午2:04
 */

namespace center\modules\user\controllers;


use center\controllers\ValidateController;
use center\controllers\LogicController;
use center\modules\auth\models\SrunJiegou;
use center\modules\financial\models\PayList;
use center\modules\selfservice\models\Setting;
use center\modules\strategy\models\Package;
use center\modules\strategy\models\Product;
use center\modules\user\models\Base;
use center\modules\user\models\Users;
use common\models\Redis;
use common\models\User;
use Yii;
use yii\base\Exception;
use yii\helpers\Url;

class GroupController extends ValidateController
{
    public function actionIndex()
    {
        $model = new SrunJiegou();
        //产品列表
        $productModel = new Product();
        $productList = $productModel->getNameOfList();
        $productList = [Yii::t('app', 'select product')] + $productList;
        //套餐列表
        $packageList = (new Package())->getList();
        return $this->render('index', [
            'model' => $model,
            'packageList' => $packageList,
            'productList' => $productList,
        ]);
    }

    public function actionDownLoad()
    {
        //下载文件
        if (Yii::$app->request->get('file')) {
            return Yii::$app->response->sendFile(Yii::$app->request->get('file'));

        }
        if (Yii::$app->session->get('batch_excel_download_file')) {
            return Yii::$app->response->sendFile(Yii::$app->session->get('batch_excel_download_file'));
        } else {
            Yii::$app->getSession()->setFlash('error', Yii::t('app', 'batch excel help31'));
        }
        return $this->redirect(['index']);
    }

    /**
     * 批量续费
     */
    public function actionBatchRenew()
    {
        set_time_limit(0);
        $res = [];
        $params = Yii::$app->request->post();
        $userModel = new Base();
        $rs = $this->getUsers($params, $userModel, false);
        $group_id = $rs['group_id'];
        $users = $rs['users'];

        if (empty($users)) {
            Yii::$app->getSession()->setFlash('error', Yii::t('app', 'No results found.'));
            return $this->redirect(['index']);
        }

        $payModel = new PayList();
        foreach ($group_id as $id) {
            $groups[] = SrunJiegou::getOwnParent($id);
        }
        $groups_msg = implode(',', $groups);
        $result = $payModel->batchPay($users, $params['product_id'], $params['renew_num'], $groups_msg);
        Yii::$app->getSession()->setFlash('success', $result . Yii::t('app', 'down info', ['download_url' => Url::to(['down-load'])]));
        if ($result) {
            $res = ['id' => 1, 'msg' => Yii::t('app', 'operate success.')];
        } else {
            $res = ['id' => 0, 'msg' => Yii::t('app', 'operate failed.')];
        }
        echo json_encode($res);
    }

    /**
     * 批量销户
     */
    public function actionBatchDelete()
    {
        $res = [];
        $params = Yii::$app->request->post();
        $userModel = new Base();
        $rs = $this->getUsers($params, $userModel);
        $group_id = $rs['group_id'];
        $users = $rs['users'];

        if (empty($users)) {
            Yii::$app->getSession()->setFlash('error', Yii::t('app', 'No results found.'));
            return $this->redirect(['index']);
        }
        $userModel->batchDelete($users);

        //写日志
        //用户组名称拼写
        foreach ($group_id as $id) {
            $groups[] = SrunJiegou::getOwnParent($id);
        }
        $groups_msg = implode(',', $groups);
        $logContent = Yii::t('app', 'group msg2', [
            'mgr' => Yii::$app->user->identity->username,
            'groups' => $groups_msg,
            'success_num' => count($users),
            'action' => Yii::t('app', 'batch delete'),
            'file' => Yii::t('app', 'down info', ['download_url' => Url::to(['/user/group/down-load?file=' . Yii::$app->session->get('batch_excel_download_file')])]),
        ]);
        $userModel->batchLog('', $logContent);
        //操作结果
        Yii::$app->getSession()->setFlash('success', $logContent);
        $res = ['id' => 1, 'msg' => Yii::t('app', 'operate success.')];
        echo json_encode($res);
    }

    public function actionBatchEnable()
    {
        $res = [];
        $params = Yii::$app->request->post();
        $userModel = new Base();
        $rs = $this->getUsers($params, $userModel);
        $group_id = $rs['group_id'];
        $users = $rs['users'];
        if (empty($users)) {
            Yii::$app->getSession()->setFlash('error', Yii::t('app', 'No results found.'));
            return $this->redirect(['index']);
        }
        $userModel->batchEnable($users, 'batch enable');

        //写日志
        //用户组名称拼写
        foreach ($group_id as $id) {
            $groups[] = SrunJiegou::getOwnParent($id);
        }
        $groups_msg = implode(',', $groups);
        $logContent = Yii::t('app', 'group msg2', [
            'mgr' => Yii::$app->user->identity->username,
            'groups' => $groups_msg,
            'success_num' => count($users),
            'action' => Yii::t('app', 'batch enable'),
            'file' => Yii::t('app', 'down info', ['download_url' => Url::to(['/user/group/down-load?file=' . Yii::$app->session->get('batch_excel_download_file')])]),
        ]);
        $userModel->batchLog('', $logContent);
        //操作结果
        Yii::$app->getSession()->setFlash('success', $logContent);
        $res = ['id' => 1, 'msg' => Yii::t('app', 'operate success.')];
        echo json_encode($res);
    }

    public function actionBatchDisable()
    {
        $res = [];
        $params = Yii::$app->request->post();
        $userModel = new Base();
        $rs = $this->getUsers($params, $userModel);
        $group_id = $rs['group_id'];
        $users = $rs['users'];
        if (empty($users)) {
            Yii::$app->getSession()->setFlash('error', Yii::t('app', 'No results found.'));
            return $this->redirect(['index']);
        }
        $userModel->batchEnable($users, 'batch disable');

        //写日志
        //用户组名称拼写
        foreach ($group_id as $id) {
            $groups[] = SrunJiegou::getOwnParent($id);
        }
        $groups_msg = implode(',', $groups);
        $logContent = Yii::t('app', 'group msg2', [
            'mgr' => Yii::$app->user->identity->username,
            'groups' => $groups_msg,
            'success_num' => count($users),
            'action' => Yii::t('app', 'batch disable'),
            'file' => Yii::t('app', 'down info', ['download_url' => Url::to(['/user/group/down-load?file=' . Yii::$app->session->get('batch_excel_download_file')])]),
        ]);
        $userModel->batchLog('', $logContent);
        //操作结果
        Yii::$app->getSession()->setFlash('success', $logContent);
        $res = ['id' => 1, 'msg' => Yii::t('app', 'operate success.')];
        echo json_encode($res);
    }

    public function actionBatchStop()
    {
        $res = [];
        $params = Yii::$app->request->post();
        $userModel = new Base();
        $rs = $this->getUsers($params, $userModel);
        $group_id = $rs['group_id'];
        $users = $rs['users'];
        if (empty($users)) {
            Yii::$app->getSession()->setFlash('error', Yii::t('app', 'No results found.'));
            return $this->redirect(['index']);
        }
        $userModel->batchEnable($users, 'batch stop');

        //写日志
        //用户组名称拼写
        foreach ($group_id as $id) {
            $groups[] = SrunJiegou::getOwnParent($id);
        }
        $groups_msg = implode(',', $groups);
        $logContent = Yii::t('app', 'group msg2', [
            'mgr' => Yii::$app->user->identity->username,
            'groups' => $groups_msg,
            'success_num' => count($users),
            'action' => Yii::t('app', 'batch stop'),
            'file' => Yii::t('app', 'down info', ['download_url' => Url::to(['/user/group/down-load?file=' . Yii::$app->session->get('batch_excel_download_file')])]),
        ]);
        $userModel->batchLog('', $logContent);
        //操作结果
        Yii::$app->getSession()->setFlash('success', $logContent);
        $res = ['id' => 1, 'msg' => Yii::t('app', 'operate success.')];
        echo json_encode($res);
    }

    // 批量停机保号
    public function actionBatchStop2()
    {
        $num = Yii::$app->request->post('num');
        $type = Yii::$app->request->post('type');
        $money = Yii::$app->request->post('money');
        $group_ids = Yii::$app->request->post('group_id');

        if ($group_ids) {
            $group_ids = explode(',', $group_ids);
            $rs_users = Users::find()->select('user_id')->asArray()->where(['in', 'group_id', $group_ids])->all();
            if ($rs_users) {
                $ids = array_column($rs_users, 'user_id');
                $rs = LogicController::batchStopByUserId($ids, $num, $type, $money);
                if ($rs) {
                    $re['msg'] = '操作成功' . Yii::t('app', 'down info', ['download_url' => Url::to(['down-load'])]);
                    $re['status'] = 1;

                    exit(json_encode($re));
                } else {
                    $re['msg'] = '操作失败';
                    $re['status'] = 0;

                    exit(json_encode($re));
                }
            } else {
                $re['msg'] = '用户组下没有用户';
                $re['status'] = 0;

                exit(json_encode($re));
            }
        } else {
            $re['msg'] = '用户组错误';
            $re['status'] = 0;

            exit(json_encode($re));
        }
    }

    public function actionBatchBuy()
    {
        $res = [];
        $params = Yii::$app->request->post();
        $userModel = new Base();
        $rs = $this->getUsers($params, $userModel, 'buy');
        $group_id = $rs['group_id'];
        $users = $rs['users'];
        $packageIds = explode(':', trim($params['package_id'], ':'));

        if (empty($users)) {
            Yii::$app->getSession()->setFlash('error', Yii::t('app', 'No results found.'));
            return $this->redirect(['index']);
        }

        $payModel = new PayList();
        //可以管理的用户组
        foreach ($group_id as $id) {
            $groups[] = SrunJiegou::getOwnParent($id);
        }
        $groups_msg = implode(',', $groups);
        $item = [
            $params['product_id'] => $packageIds
        ];
        $result = $payModel->batchBuy($users, $item, $groups_msg);
        Yii::$app->getSession()->setFlash('success', $result . Yii::t('app', 'down info', ['download_url' => Url::to(['down-load'])]));
        if ($result) {
            $res = ['id' => 1, 'msg' => Yii::t('app', 'operate success.')];
        } else {
            $res = ['id' => 0, 'msg' => Yii::t('app', 'operate failed.')];
        }
        echo json_encode($res);
    }

    /**
     * 批量开启mac认证
     * @return \yii\web\Response
     */
    public function actionBatchMacOpen()
    {
        $res = [];
        $params = Yii::$app->request->post();
        $userModel = new Base();
        $rs = $this->getUsers($params, $userModel);
        $group_id = $rs['group_id'];
        $users = $rs['users'];
        if (empty($users)) {
            Yii::$app->getSession()->setFlash('error', Yii::t('app', 'No results found.'));
            return $this->redirect(['index']);
        }

        $userModel->batchMacOpen($users, 'open');

        //写日志
        //用户组名称拼写
        foreach ($group_id as $id) {
            $groups[] = SrunJiegou::getOwnParent($id);
        }
        $groups_msg = implode(',', $groups);
        $logContent = Yii::t('app', 'group msg2', [
            'mgr' => Yii::$app->user->identity->username,
            'groups' => $groups_msg,
            'success_num' => count($users),
            'action' => Yii::t('app', 'batch mac auth open'),
            'file' => Yii::t('app', 'down info', ['download_url' => Url::to(['/user/group/down-load?file=' . Yii::$app->session->get('batch_excel_download_file')])]),
        ]);
        $userModel->batchLog('', $logContent);
        //操作结果
        Yii::$app->getSession()->setFlash('success', $logContent);
        $res = ['id' => 1, 'msg' => Yii::t('app', 'operate success.')];
        echo json_encode($res);
    }

    /**
     * 批量关闭mac认证
     * @return \yii\web\Response
     */
    public function actionBatchMacClose()
    {
        $res = [];
        $params = Yii::$app->request->post();
        $userModel = new Base();
        $rs = $this->getUsers($params, $userModel);
        $group_id = $rs['group_id'];
        $users = $rs['users'];
        if (empty($users)) {
            Yii::$app->getSession()->setFlash('error', Yii::t('app', 'No results found.'));
            return $this->redirect(['index']);
        }

        $userModel->batchMacOpen($users, 'close');

        //写日志
        //用户组名称拼写
        foreach ($group_id as $id) {
            $groups[] = SrunJiegou::getOwnParent($id);
        }
        $groups_msg = implode(',', $groups);
        $logContent = Yii::t('app', 'group msg2', [
            'mgr' => Yii::$app->user->identity->username,
            'groups' => $groups_msg,
            'success_num' => count($users),
            'action' => Yii::t('app', 'batch mac auth close'),
            'file' => Yii::t('app', 'down info', ['download_url' => Url::to(['/user/group/down-load?file=' . Yii::$app->session->get('batch_excel_download_file')])]),
        ]);
        $userModel->batchLog('', $logContent);
        //操作结果
        Yii::$app->getSession()->setFlash('success', $logContent);
        $res = ['id' => 1, 'msg' => Yii::t('app', 'operate success.')];
        echo json_encode($res);
    }

    /**
     * 用户组绑定产品
     * @return string
     */
    public function actionBindProduct()
    {
        $rs = Redis::executeCommand('get', 'add_user_depend_bind_relation', [], 'redis_cache');
//        dd($rs);
        $view = Yii::$app->getView();
        $view->params['add_user_depend_bind_relation'] = $rs;

        //绑定产品后，还要覆盖掉 可管理该用户组的非超管 的可管理产品。
        $model = Setting::model();
        $params = Yii::$app->request->post();
        if ($model->load($params) && $model->validate()) {
            //用户组数组
            $array = explode('.', rtrim($params['Setting']['key'], '.'));

            //管理员操作数组 和 要取消绑定的产品id
            $mgr_arr = $cancel_pids = [];

            $transaction = Yii::$app->db->beginTransaction();
            try {

                //全选
                if (isset($params['select_all']) && $params['select_all'] == 1) {
                    $group_all = json_decode(SrunJiegou::ajax(), true);
                    if ($group_all) {
                        $select_all = true;
                        $array = [];
                        $Product = new Product();
                        $products = implode(',', array_keys($Product->getNameOfList()));
                        foreach ($group_all as $one) {
                            if ($one['id'] == 1) {
                                continue;
                            }
                            $array[] = $one['id'];
                        }
                    }
                }
                foreach ($array as $val) {

                    //如果是全选
                    if ($select_all == true) {
                        //查询 mysql 中用户组的值
                        $data = $model->find()->where(['key' => 'group_bind_product_' . $val])->one();

                        //编辑
                        if ($data) {
                            $data->value = $products;
                            $data->update();
                        } else { //创建数据并保存在 mysql中
                            $model->oldAttributes = null;
                            $model->key = 'group_bind_product_' . $val;
                            $model->value = $products;
                            $model->save();
                        }
                    } else {
                        //查询该用户组下的产品
                        $gids = SrunJiegou::getNodeId([$val]);
                        foreach ($gids as $gid) {
                            if ($val != $gid && in_array($gid, $array)) {
                                unset($gids[array_search($gid, $gids)]);
                            }
                        }
                        if (isset($params['Setting']['group_bind_product'][$val])) {
                            foreach ($gids as $gid) {
                                //查询 mysql 中用户组的值
                                $data = $model->find()->where(['key' => 'group_bind_product_' . $gid])->one();

                                //编辑
                                if ($data) {
                                    $cancel_pids = array_diff(explode(',', $data->value), $params['Setting']['group_bind_product'][$val]);
                                    $data->value = implode(',', $params['Setting']['group_bind_product'][$val]);

                                    $data->update();
                                } else { //创建数据并保存在 mysql中
                                    $model->oldAttributes = null;
                                    $model->key = 'group_bind_product_' . $gid;
                                    $model->value = implode(',', $params['Setting']['group_bind_product'][$val]);
                                    $model->save();
                                }
                            }

                        } else {

                            //如果提交的 用户组中没选产品值 那么直接将 mysql 中 关于产品的值删除
                            foreach ($gids as $gid) {
                                $data = $model->find()->where(['key' => 'group_bind_product_' . $gid])->one();
                                if ($data) {
                                    $cancel_pids = explode(',', $data->value);
                                    $data->delete();
                                }
                            }
                        }
                    }

                    //覆盖 可管理该用户组的非超管的 可管理的产品
                    //先查出管理员
                    $userModel = new User();
                    if (!User::isSuper()) {
                        $canMgrope = $userModel->getChildIdAll();
                        $canMgrmgrId = array_keys($canMgrope);
                        //排除自己
                        $key = array_search(Yii::$app->user->identity['attributes']['id'], $canMgrmgrId);
                        if ($key !== false) {
                            array_splice($canMgrmgrId, $key, 1);
                        }
                    }
                    $query = User::find();
                    $pids = SrunJiegou::getOwnParentIds($val);
                    foreach ($pids as $id) {
                        $query->orWhere("find_in_set('" . $id . "', mgr_org)");
                    }

                    if (isset($canMgrmgrId)) {
                        $query->andWhere(['id' => $canMgrmgrId]);
                    }
                    $mgrs = $query->all();
                    if ($mgrs) {
                        $post_data = $params['Setting']['group_bind_product'][$val] ? $params['Setting']['group_bind_product'][$val] : [];
                        foreach ($mgrs as $one) {
                            if (isset($mgr_arr[$one->id])) {
                                $mgr_arr[$one->id] = array_merge($mgr_arr[$one->id], $post_data);
                            } else {
                                $mgr_arr[$one->id] = $post_data;
                            }
                        }
                    }
                }

                //合并每个管理员可管理的产品,然后保存
                if ($mgr_arr) {
                    foreach ($mgr_arr as $id => $one) {
                        $mgr_info = User::find(['mgr_product'])->where(['id' => $id])->one();
                        $products = explode(',', $mgr_info->mgr_product);
                        $new_pids = array_diff(array_unique(array_merge($products, $one)), $cancel_pids);
                        $new_mgr_product = [];
                        foreach ($new_pids as $pid_one) {
                            if ($pid_one) {
                                $new_mgr_product[] = $pid_one;
                            }
                        }
                        User::updateAll(['mgr_product' => implode(',', $new_mgr_product)], ['id' => $id]);
                    }
                }
                //日志数组
                $data = [
                    'target' => Yii::t('app', 'user/group/bind-product'),
                    'action' => 'edit',
                    'action_type' => 'Group Bind',
                    'content' => Yii::t('app', 'group_bind_product_msg2', ['mgr' => Yii::$app->user->identity->username]),
                ];
                $model->log($data);

                $transaction->commit();
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->getSession()->setFlash('error', Yii::t('app', 'operate failed.'));
                return $this->redirect(['bind-product']);
            }

            Yii::$app->getSession()->setFlash('success', Yii::t('app', 'operate success.'));
            return $this->redirect(['bind-product']);
        }
        return $this->render('bind_product', []);
    }

    /**
     * 获取当前用户组设置的绑定产品
     * @return array
     */

    public function actionAjaxGetProductsByGroup()
    {
        $group_id = Yii::$app->request->post('group_id');
        $data = [];
        $productModel = new Product();
        $products = $productModel->getNameOfList();
        $productIds = !empty($products) ? array_keys($products) : [];
        if ($group_id) {
            $data = Setting::getSetting('group_bind_product_' . $group_id);
        }

        //利用response，发送json格式数据
        $response = Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_JSON;

        return $response->data = [
            'groups' => $data,
            'products' => $productIds,
        ];

    }

    /**
     * ajax获取html类型的可管理的用户组绑定的产品
     */
    public function actionGetProductByBindGroup()
    {
        $group = new SrunJiegou();
        $Product = new Product();

        $group_id = Yii::$app->request->post('group_id');
        if ($group_id == 1) {
            echo '';
            exit;
        }
        $val = $group::canMgrGroupNameList()[$group_id];

        $name = 'Setting[group_bind_product][' . $group_id . '][]';
        $str = '';
        $str .= '<div style="margin:0px;padding:0px;" id="groupBindProduct_' . $group_id . '">
                <table  class="table">
                    <tr>
                        <td class="active col-lg-3" align="center" style="vertical-align:middle";>
                            <div>
                                ' . $val . '[ID:' . $group_id . ']' . '<br />
                            </div>
                        </td>
                        <td class="col-lg-9">
                            <div>';
        foreach ($Product->getNameOfList() as $keys => $value) {
            $str .= '<div class="col-lg-4">
                                        <input type="checkbox" name="' . $name . '" ' . Setting::autoChecked("group_bind_product_" . $group_id, $keys, 'value') . ' value=' . $keys . ' > ' . $value . '
                                </div>';
        }
        $str .= '               </div>
                        </td>
                    </tr>
                </table>
            </div>
        <hr/>';
        echo $str;
    }

    // 用户添加页不依赖绑定关系
    public function actionDepend()
    {
        $is_depend = Yii::$app->request->post('is_depend');

        $rs = '';
        if ($is_depend == 'yes') {    // 依赖
            $rs = Redis::executeCommand('set', 'add_user_depend_bind_relation', ['yes'], 'redis_cache');
            $re['msg'] = Yii::t('app', 'is_depend_yes');
        } elseif ($is_depend == 'no') {   // 不依赖
            $rs = Redis::executeCommand('set', 'add_user_depend_bind_relation', ['no'], 'redis_cache');
            $re['msg'] = Yii::t('app', 'is_depend_no');
        }

//        $rss = Redis::executeCommand('get','add_user_depend_bind_relation',[],'redis_cache');
//        $re['rs'] = $rs;
//        $re['rss'] = $rss;

        if ($rs) {
            $re['status'] = 1;

            exit(json_encode($re));
        } else {
            $re['status'] = 0;
            $re['msg'] = Yii::t('app', 'failed');

            exit(json_encode($re));
        }
    }

    /**
     * 批量处理获取用户
     * @param $params
     * @param $userModel
     * @param $flag
     * @return array
     */
    protected function getUsers($params, $userModel, $flag = true)
    {
        $rs = [];
        if (!is_bool($flag)) {
            if ($flag == 'buy') {
                if(!$params['group_id'] || !$params['product_id'] || !$params['package_id']){
                    $res = ['id' => 0, 'msg' => Yii::t('app', 'message Invalid Param')];
                    echo json_encode($res);exit;
                }
            }
        }

        if (!$flag) {
            if (!$params['group_id'] || !$params['product_id'] || !$params['renew_num'] || $params['renew_num'] <= 0) {
                $rs = ['id' => 0, 'msg' => Yii::t('app', 'message Invalid Param')];
                echo json_encode($rs);exit;
            }
        }
        if (!$params['group_id']) {
            $rs = ['id' => 0, 'msg' => Yii::t('app', 'message Invalid Param')];
            echo json_encode($rs);exit;
        } else {

            $group_id = explode(',', $params['group_id']);
            $diff = array_diff($group_id, array_keys($userModel->can_group));
            if ($diff) {
                //恶意攻击或者有不能获取部分
                $rs = ['id' => 0, 'msg' => Yii::t('app', 'Some products are not managed')];
                echo json_encode($rs);exit;
            } else {
                $ids = SrunJiegou::getNodeId($group_id);

                if (!empty($userModel->flag) && !in_array(1, array_keys($userModel->can_group))) {
                    $groupIds = array_intersect($ids, array_keys($userModel->can_group));
                } else {
                    $groupIds = $ids;
                }
                $users = $userModel->find()->andWhere(['group_id' => $groupIds])->select(['user_id', 'user_name'])->asArray()->all();
                $rs = ['code' => 200, 'users' => $users, 'group_id' => $group_id];
            }
        }

        return $rs;

    }
}