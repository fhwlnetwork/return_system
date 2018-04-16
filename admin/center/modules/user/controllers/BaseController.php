<?php
/**
 * 用户基本控制器
 */

namespace center\modules\user\controllers;

use center\controllers\LogicController;
use center\models\CloundOnlineReport;
use center\modules\auth\models\SrunJiegou;
use center\controllers\ValidateController;
use center\modules\financial\models\Bills;
use center\modules\financial\models\CheckoutList;
use center\modules\financial\models\PayList;
use center\modules\financial\models\PayType;
use center\modules\financial\models\RefundList;
use center\modules\financial\models\WaitCheck;
use center\modules\log\models\LogWriter;
use center\modules\log\models\Operate;
use center\modules\message\models\Key;
use center\modules\report\models\SrunDetailDay;
use center\modules\setting\models\ExtendsField;
use center\modules\strategy\models\Condition;
use center\modules\strategy\models\Package;
use center\modules\strategy\models\ProductsChange;
use center\modules\user\models\Online;
use center\modules\user\models\Template;
use common\extend\Excel;
use common\models\KernelInterface;
use common\models\Redis;
use common\models\User;
use yii;
use center\modules\strategy\models\Product;
use center\modules\user\models\Base;
use center\models\Pagination;
use yii\web\NotFoundHttpException;
use yii\helpers\Url;
use common\models\FileOperate;
use center\modules\user\models\Operator;
use center\modules\financial\models\TransferBalance;
use common\extend\Tool;

class BaseController extends ValidateController
{

    //用户状态
    private $userStaus = [
        '0' => '正常',
        '1' => '禁用',
    ];

    /**
     * 用户列表
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionIndex()
    {
        //请求的参数
        $params = Yii::$app->getRequest()->queryParams;
        $post = Yii::$app->getRequest()->post();
        $params = (isset($params) && !empty($params)) ? $params : $post;

        //是否导出
        $model = new Base();
        // 从redis中获取用户默认的在线菜单
        $paramKey = 'key:user:base:search:params';
        $paramRedis = Redis::executeCommand('get', $paramKey, [], 'redis_manage');

        // 判断是否进行mac认证搜索
        if ($params['mac']) {
            $mac_auth_user = Redis::executeCommand('get', 'key:users:mac_auth:' . $params['mac']);
            if ($params['user_name'] && $mac_auth_user) {
                if ($params['user_name'] != $mac_auth_user)
                    $mac_auth_user = false;
            }
            if (!$mac_auth_user) {
                return $this->render('index', [
                    'model' => $model,
                    'params' => $params,
                    'list' => [],
                    'products' => [],
                    'packages' => [],
                    'pagination' => new Pagination([
                        'defaultPageSize' => 0,
                        'totalCount' => 0
                    ])
                ]);
            } else
                $params['user_name'] = $mac_auth_user;
        }

        //整理要查询数据库的字段
        if (empty($params['showField'])) {
            // 将记录保存在redis中
            $defaultField = $paramRedis ? yii\helpers\Json::decode($paramRedis) : false;
            $params['showField'] = is_array($defaultField) ? $defaultField : $model->defaultField;
        }

        $rs = $model->getData($params, $model);

        if ($rs['code'] != 200) {
            Yii::$app->getSession()->setFlash('danger', $rs['msg']);
            $url = isset($rs['url']) ? $rs['url'] : 'index';

            if ($rs['code'] == 403) {
                return $this->redirect($url);
            }
        }
        $list = isset($rs['list']) ? $rs['list'] : '';

        $products = isset($rs['products']) ? $rs['products'] : [];
        $packages = isset($rs['packages']) ? $rs['packages'] : [];
        $pay_types = isset($rs['pay_types']) ? $rs['pay_types'] : [];
        $default = isset($rs['default']) ? $rs['default'] : '';
        $extendFields = isset($rs['extendFields']) ? $rs['extendFields'] : [];
        $params = $rs['params'];
        $pagination = isset($rs['pagination']) ? $rs['pagination'] : new Pagination([
            'defaultPageSize' => 0,
            'totalCount' => 0
        ]);


        return $this->render('index', [
            'model' => $model,
            'list' => $list,
            'pagination' => $pagination,
            'params' => $params,
            'products' => $products,
            'packages' => $packages,
            'extendFields' => $extendFields,
            'pay_types' => $pay_types,
            'default_type' => $default,
        ]);
    }

    /**
     * 用户查看页面，可以接受两种搜索参数：user_id或user_name
     */
    public function actionView()
    {
        $params = Yii::$app->getRequest()->queryParams;
        //搜索user_id
        if (isset($params['user_id']) && intval($params['user_id']) > 0) {
            $model = Base::findOne(['user_id' => $params['user_id']]);
        } //搜索user_name
        else if (isset($params['user_name']) && !empty($params['user_name'])) {
            $model = Base::findOne(['user_name' => $params['user_name']]);
        } //其他搜索方式可以依次判断，无匹配则退出
        else {
            throw new NotFoundHttpException(Yii::t('app', 'No results found.'));

        }
        if (!$model) { //没找到用户跳到列表
            //throw new NotFoundHttpException(Yii::t('app', 'No results found.'));
            Yii::$app->getSession()->setFlash('error', Yii::t('app', 'No results found.'));
            return $this->redirect(['index']);
        }
        //判断此用户是否可以管理
        if (!array_key_exists($model->group_id, $model->can_group)) {
            throw new yii\web\ForbiddenHttpException(Yii::t('app', 'message 401 3'));
        }

        //操作日志
        $operateModel = new Operate();
        $operateQuery = Operate::find()
            ->where(['target' => $model->user_name]);

        //判断管理员
        if (!$model->flag) {
            $operateQuery->andWhere(['operator' => $model->can_mgr + ['SELF-SERVICE']]);
        }

        //操作日志
        $operateList = $operateQuery
            ->orderBy(['id' => SORT_DESC])
            ->limit(20)
            ->asArray()
            ->all();
        if ($operateFormat = $operateModel->listShow($operateList)) {
            $operateContent = $operateModel->showHtml($operateFormat);
        } else {
            $operateContent = '';
        }
        $orderedProductList = [];
        if ($model->products_id) {
            //可以管理的并且是已订购的产品列表
            $orderedProductList = $model->getOrderedProduct($model->products_id, $model->user_id);
        }


        //可以管理的并且没订购的产品列表
        $unorderedProductList = $model->getUnOrderedProduct();
        //绑定的第一个产品的包月费，不是包月产品则为0
        $fee = $model->fistProFee($orderedProductList, $model->user_name);
        $fir_pid = $model->getFirstProductId($orderedProductList);
        //半个周期费用不一定是整个周期费用的二分之一，因为有可能余额不足整个周期费用，那么回事产品余额，可能大于办个周期的费用，比如周期费用是100，产品余额为80，半个周期费用应该是50，而不是40
        $halffee = $fee ? (new WaitCheck())->checkoutParams($model->user_name, $fir_pid, 0.5)['checkout_amount'] : 0;
        $checkout_amount_byday = $this->actionGetCheckoutByday($model->user_id, $fir_pid, $fee);
        //绑定信息
        $bindList = $model->getBind();
        //CDR绑定信息
        $bindCDRList = $model->getCDRList($model->user_name);

        //在线信息
        $onlineList = Online::getOnlineByNameRedis($model->user_name);
        return $this->render('view', [
            'model' => $model,
            'operateContent' => $operateContent,//操作日志
            'orderedProductList' => $orderedProductList,//已订购产品
            'unorderedProductList' => $unorderedProductList,//未订购的产品
            'fee' => $fee,//第一个产品的周期费用
            'halffee' => $halffee,//第一个产品的半个周期费用
            'daysfee' => $checkout_amount_byday,//第一个产品的按天扣除费用
            'bindList' => $bindList,//绑定信息
            'bindCDRList' => $bindCDRList,//绑定信息
            'onlineList' => $onlineList,//在线信息
        ]);
    }

    /**
     * 添加用户
     * @return string
     * @throws yii\web\ForbiddenHttpException
     */
    public function actionAdd()
    {
        //用来记录（用户添加不依赖绑定关系（默认为空或yes，如果不依赖   no ）
        $rs = Redis::executeCommand('get', 'add_user_depend_bind_relation', [], 'redis_cache');
        $model = new Base();
        $model->loadDefaultValues();
        // 保存密码到redis 事件 180秒
        $model->on(Base::EVENT_AFTER_INSERT, function ($event) {
            $key = $event->sender->user_id . '_' . $event->sender->user_name;

            Redis::executeCommand('set', $key, [$event->sender['user_password']]);
            Redis::executeCommand('expire', $key, [180]);
        });
        $params = Yii::$app->request->post();
        if ($params['Base']) {
            $params['Base']['mobile_password'] = $params['Base']['mobile_password_hidden'];
        }

        $isSuper = $model->flag;
        $open_num = $max_open_num = 0;
        if (!$isSuper) {
            //当前管理员开户数
            $open_num = $model->getOpenUserNum();
            $max_open_num = Yii::$app->user->identity->max_open_num;
            if (empty($params)) {
                Yii::$app->getSession()->setFlash('info', Yii::t('app', 'current_open_num', ['open_num' => $open_num, 'still_num' => $max_open_num - $open_num < 0 ? 0 : $max_open_num - $open_num]));
            }
        }
        if (isset($params['Base']['pwd_type'])) {
            //显示对应的密码类型(表单提交后如果有验证错误)
            if ($params['Base']['pwd_type'] != 1) {
                $model->pwd_type = $params['Base']['pwd_type'];
                //user_password值为123456只是为了避免验证时报错，提交后程序会再次生成val值
                $model->user_password = '123456';
                $model->user_confirm_password = '123456';
            }
        }

        $model->scenario = 'add';
        $model->loadDefaultValues(); //加载默认项
        //获取提交值
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if (!($model->checkIsOpen($open_num, $max_open_num) && $model->getCanOrg() && $model->getCanProduct())) {

                return $this->refresh();
            }

            //判断密码的类型（手动录入，随机生成）
            if (isset($params['Base']['pwd_type'])) {
                if ($params['Base']['pwd_type'] == 2) {
                    //密码与用户名相同
                    $model->user_password = $params['Base']['user_name'];
                    $model->user_confirm_password = $params['Base']['user_name'];
                } elseif ($params['Base']['pwd_type'] == 3) {
                    //生成6位随机密码
                    $model->user_password = Tool::randpw(6);
                    $model->user_confirm_password = Tool::randpw(6);
                }
            }
            $model->balance = intval(yii::$app->request->post()['Base']['balance']);
            //保存
            $res = $model->saveUser();

            if ($res) {
                $ex_msg = '';
                if ($model->payModel) {
                    $ex_msg = '<br/>' . $model->payModel->getPayMessage('<br />');
                }
                $info = $model->message ? '<br/><span style="color: blue">' . implode(',', $model->message) . '</span>' : '';
                Yii::$app->getSession()->setFlash('success', Yii::t('app', 'operate success.') . $ex_msg . $info);

                return $this->redirect(['index']);
            } else {
                Yii::$app->getSession()->setFlash('error', Yii::t('app', 'operate failed.'));
            }
        }
        //获取get参数
        $params = Yii::$app->getRequest()->queryParams;

        //加载开户模板
        $temModel = new Template();
        //获取可使用的开户模板列表
        $allTemList = $temModel->getValidList();//print_r($temList);exit;

        //如果删除模板
        if (isset($params['action']) && $params['action'] == 'delTem') {
            if (isset($params['tem']) && array_key_exists($params['tem'], $allTemList['self'])) {
                $temModel->deleteOne($params['tem']);
                Yii::$app->getSession()->setFlash('success', Yii::t('app', 'user base help7'));
                return $this->refresh();
            }
        }

        //从redis中获取上次选中的模板id
        $temKey = 'key:user:' . Yii::$app->user->identity->username . ':addTemp';
        $redisTemId = Redis::executeCommand('get', $temKey, [], 'redis_manage');

        //如果没有模板
        if (!isset($params['tem'])) {
            $params['tem'] = $redisTemId > 0 ? $redisTemId : 0;
        } else {
            //判断选择的模板ID是否是可以操作的模板
            if (array_key_exists($params['tem'], $allTemList['all'])) {
                $tem = $temModel->getOne($params['tem']);
                if (isset($tem['content'])) {
                    $useTemp = true;
                    $model->setAttributes($tem['content']);
                    $model->selectTemplate = $tem['id'];
                }
            }
        }

        //将值保存在redis中
        Redis::executeCommand('set', $temKey, [$model->selectTemplate], 'redis_manage');

        //获取产品列表
        if (isset($useTemp)) {
            //如果使用开户模板，那么产品顺序按照开户模板的顺序来
            $productList = $temModel->productsByTempSort($params['tem'], $model->can_product);
        } else {
            $productList = $model->can_product;
        }
        //开户模板列表
        $temNameList = $temModel->getNameOfList($allTemList);
        $temNameList = ['0' => Yii::t('app', 'not use template')] + $temNameList;

        //默认过期时间
        $model->user_expire_time == 0 && $model->user_expire_time = '';
        //密码类型
        $pwdTypeList = array(
            '1' => Yii::t('app', 'pwd_type_1'),
            '2' => Yii::t('app', 'pwd_type_2'),
            //'3' => Yii::t('app', 'pwd_type_3'),
        );

        return $this->render('add', [
            'model' => $model,
            'productList' => $productList,
            'packageList' => $model->package_list,
            'allTemList' => $allTemList,
            'temNameList' => $temNameList,
            'pwdTypeList' => $pwdTypeList,
            'extendField' => $model->user_extends,//扩展字段
            'yes_no' => $rs,
        ]);
    }

    /**
     * 编辑用户 @todo 已经订购的产品只能拖拽排序，不能取消产品，可以订购新产品并缴费
     * @param $id
     * @return string|yii\web\Response
     * @throws NotFoundHttpException
     * @throws yii\web\ForbiddenHttpException
     */
    public function actionEdit($id)
    {
        set_time_limit(0);
        //用来记录（用户添加不依赖绑定关系（默认为空或yes，如果不依赖   no ）
        $rs = Redis::executeCommand('get', 'add_user_depend_bind_relation', [], 'redis_cache');
        $id = intval($id);
        $model = Base::findOne($id);

        if (!$model) {
            throw new NotFoundHttpException(Yii::t('app', 'No results found.'));
        }

        //判断组织结构和产品是否可用
        //判断组织结构
        if (!array_key_exists($model->group_id, $model->can_group)) {
            throw new yii\web\ForbiddenHttpException(Yii::t('app', 'message 401 1'));
        }

        $model->scenario = 'edit';
        $model_products = $model->products_id;
        $params = Yii::$app->request->post();

        if ($params['Base']) {
            $params['Base']['mobile_password'] = $params['Base']['mobile_password_hidden'];
        }
        if ($model->load($params) && $model->validate()) {
            if (!empty($model->user_new_password)) {
                $model->user_password = $model->user_new_password;
            }
            $get_money = intval(yii::$app->request->post()['Base']['balance']);
            $res = $model->saveUser(true, null, $get_money);
            if ($res) {
                //通知用户
                if (!empty($model->user_new_password)) {
                    $model->updatePassNotice($model->user_name, $model->user_new_password);
                }
                Yii::$app->getSession()->setFlash('success', Yii::t('app', 'operate success.'));
            } else {
                Yii::$app->getSession()->setFlash('error', Yii::t('app', 'operate failed.'));
            }
            return $this->redirect('view?user_name=' . $model->user_name);
        } else {
            $model->products_id = $model_products;
        }

        if ($model->mobile_phone[0] != '{')//没有加密头的是明文
            $model->mobile_is_text = 1;


        //排序
        if (!empty($model->can_product) && !empty($model->products_id)) {
            $productList = $model->keySort($model->products_id);
        }
        return $this->render('edit', [
            'model' => $model,
            'productList' => $productList,
            'extendField' => ExtendsField::getAllData(),//扩展字段
            'yes_no' => $rs,
        ]);
    }

    /**
     * 删除用户
     * @param $id
     * @return yii\web\Response
     * @throws NotFoundHttpException
     * @throws yii\web\ForbiddenHttpException
     */
    public function actionDelete($id)
    {
        $id = intval($id);
        $model = Base::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException(Yii::t('app', 'No results found.'));
        }

        //判断组织结构和产品是否可用
        //判断组织结构
        if (!array_key_exists($model->group_id, $model->can_group)) {
            throw new yii\web\ForbiddenHttpException(Yii::t('app', 'message 401 6'));
        }

        //判断产品
        if ($model->products_id) {
            foreach ($model->products_id as $pid) {
                if (!array_key_exists($pid, $model->can_product)) {
                    throw new yii\web\ForbiddenHttpException(Yii::t('app', 'message 401 4'));
                }
            }
        }
        $balance = $model->balance;
        if ($balance < 0.1) {
            //如果0<余额<0.1,那么就结算掉
            if ($balance > 0) {
                $checkoutModel = new CheckoutList();
                $checkoutModel->user_name = $model->user_name;
                $checkoutModel->spend_num = $balance;
                $checkoutModel->type = CheckoutList::CHECKOUT_OTH; //结算类型为其他(系统结算掉)
                $checkoutModel->create_at = time();
                $checkoutModel->save(false);
            }
            $orderedProductList = $model->getOrderedProductDetail($model->products_id, $model->user_id);

            $whether = $model->getWhetherDelete($orderedProductList, $model->user_name);
            if (is_bool($whether)) {
                //销户
                if ($whether) {
                    $res = LogicController::transferUserById($id);
                    if ($res) {
                        Yii::$app->getSession()->setFlash('success', Yii::t('app', 'operate success.'));
                    } else {
                        Yii::$app->getSession()->setFlash('error', Yii::t('app', 'operate failed.'));
                    }
                } else {
                    Yii::$app->getSession()->setFlash('error', Yii::t('app', 'disable user error3'));
                }

            } else {
                Yii::$app->getSession()->setFlash('error', Yii::t('app', 'disable user error2', [
                    'user_name' => $model->user_name,
                    'proName' => $whether,
                ]));
            }
        } else {
            Yii::$app->getSession()->setFlash('error', Yii::t('app', 'disable user error1', [
                'user_name' => $model->user_name,
            ]));
        }

        return $this->redirect('index');
    }

    /**
     * 修改密码
     */
    public function actionPassword()
    {
        $model = new Base();

        if (Yii::$app->request->post()) {
            $post = Yii::$app->request->post()['Base'];
            $one = Base::findOne(['user_name' => $post['user_name']]);
            if (!$one) {
                Yii::$app->getSession()->setFlash('error', Yii::t('app', 'No results found.'));
            } else {
                $model = $one;
            }

            //判断组织结构
            if (!User::canManage('org', $model->group_id)) {
                Yii::$app->getSession()->setFlash('error', Yii::t('app', 'message 401 3'));
                return $this->redirect('password');
            }

            //搜索
            if ($post['type'] == 'search') {

            } //提交编辑
            else if ($post['type'] == 'edit') {
                $model->user_password = $post['user_password'];
                $model->save();
                //写消息给用户
                $model->updatePassNotice($model->user_name, $post['user_password']);
                Yii::$app->getSession()->setFlash('success', Yii::t('app', 'operate success.'));
                return $this->refresh();
            }
        }

        $model->scenario = 'chgPassword';

        return $this->render('password', [
            'model' => $model,
        ]);
    }

    /**
     * 取消产品
     * @param $user_name string 用户名
     * @param $id int 产品id
     * @return yii\web\Response
     * @throws NotFoundHttpException
     * @throws yii\web\BadRequestHttpException
     * @throws yii\web\ForbiddenHttpException
     */
    public function actionCancelProduct($user_name, $id)
    {
        $model = Base::findOne(['user_name' => $user_name]);
        if (!$model) {
            throw new NotFoundHttpException(Yii::t('app', 'No results found.'));
        }

        //判断组织结构
        if (!array_key_exists($model->group_id, $model->can_group)) {
            throw new yii\web\ForbiddenHttpException(Yii::t('app', 'message 401 3'));
        }

        //判断此产品是否可以管理
        if (!array_key_exists($id, $model->can_product)) {
            throw new yii\web\ForbiddenHttpException(Yii::t('app', 'message 401 4'));
        }

        //用户产品中没有此产品
        if (!in_array($id, $model->products_id)) {
            throw new NotFoundHttpException(Yii::t('app', 'No results found.'));
        }

        //如果产品数量小于等于1，则不能取消
        if (count($model->products_id) <= 1) {
            //无效的请求
            throw new yii\web\BadRequestHttpException(Yii::t('app', 'message 400'));
        }

        $res = $model->cancelProduct($id);

        if ($res) {
            Yii::$app->getSession()->setFlash('success', Yii::t('app', 'operate success.'));
        } else {
            Yii::$app->getSession()->setFlash('error', Yii::t('app', 'operate failed.'));
        }
        return $this->goBack(Yii::$app->request->referrer);
    }

    /**
     * 禁用产品
     * @param $user_name string 用户名
     * @param $id int 产品id
     * @return yii\web\Response
     * @throws NotFoundHttpException
     * @throws yii\web\BadRequestHttpException
     * @throws yii\web\ForbiddenHttpException
     */
    public function actionDisableProduct($user_name, $id)
    {
        $model = Base::findOne(['user_name' => $user_name]);
        if (!$model) {
            throw new NotFoundHttpException(Yii::t('app', 'No results found.'));
        }

        //判断组织结构
        if (!array_key_exists($model->group_id, $model->can_group)) {
            throw new yii\web\ForbiddenHttpException(Yii::t('app', 'message 401 3'));
        }

        //判断此产品是否可以管理
        if (!array_key_exists($id, $model->can_product)) {
            throw new yii\web\ForbiddenHttpException(Yii::t('app', 'message 401 4'));
        }

        //用户产品中没有此产品
        if (!in_array($id, $model->products_id)) {
            throw new NotFoundHttpException(Yii::t('app', 'No results found.'));
        }


        $res = $model->enableOrDisableProduct($id, 1);

        if ($res) {
            Yii::$app->getSession()->setFlash('success', Yii::t('app', 'operate success.'));
        } else {
            Yii::$app->getSession()->setFlash('error', Yii::t('app', 'operate failed.'));
        }
        return $this->goBack(Yii::$app->request->referrer);
    }

    /**
     * 启用产品
     * @param $user_name string 用户名
     * @param $id int 产品id
     * @return yii\web\Response
     * @throws NotFoundHttpException
     * @throws yii\web\BadRequestHttpException
     * @throws yii\web\ForbiddenHttpException
     */
    public function actionEnableProduct($user_name, $id)
    {
        $model = Base::findOne(['user_name' => $user_name]);
        if (!$model) {
            throw new NotFoundHttpException(Yii::t('app', 'No results found.'));
        }

        //判断组织结构
        if (!array_key_exists($model->group_id, $model->can_group)) {
            throw new yii\web\ForbiddenHttpException(Yii::t('app', 'message 401 3'));
        }

        //判断此产品是否可以管理
        if (!array_key_exists($id, $model->can_product)) {
            throw new yii\web\ForbiddenHttpException(Yii::t('app', 'message 401 4'));
        }

        //用户产品中没有此产品
        if (!in_array($id, $model->products_id)) {
            throw new NotFoundHttpException(Yii::t('app', 'No results found.'));
        }

        $res = $model->enableOrDisableProduct($id);

        if ($res) {
            Yii::$app->getSession()->setFlash('success', Yii::t('app', 'operate success.'));
        } else {
            Yii::$app->getSession()->setFlash('error', Yii::t('app', 'operate failed.'));
        }
        return $this->goBack(Yii::$app->request->referrer);
    }

    /**
     * 其他操作
     */
    public function actionOperate()
    {
        $post = Yii::$app->request->post();
        $get = Yii::$app->request->get();
        if (!isset($post['action']) && !isset($get['action'])) {
            //无效的请求
            throw new yii\web\BadRequestHttpException(Yii::t('app', 'message 400'));
        }
        //有效的get方式，其他都为post
        $getValid = ['drop', 'delBind', 'delCDRBind'];
        if (isset($get['action'])) {
            if (!in_array($get['action'], $getValid)) {
                throw new yii\web\BadRequestHttpException(Yii::t('app', 'message 400'));
            }
        }
        $action = isset($post['action']) ? $post['action'] : $get['action'];
        $request = isset($post['action']) ? $post : $get;
        if (!isset($request['user_name'])) {
            throw new yii\base\InvalidParamException(Yii::t('app', 'message Invalid Param'));
        }
        $model = Base::findOne(['user_name' => $request['user_name']]);
        if (!$model) {
            throw new NotFoundHttpException(Yii::t('app', 'No results found.'));
        }

        //判断组织结构
        if (!array_key_exists($model->group_id, $model->can_group)) {
            throw new yii\web\ForbiddenHttpException(Yii::t('app', 'message 401 3'));
        }

        $res = false;

        //下线
        if ($action == 'drop') {
            if (!isset($request['type']) || !in_array($request['type'], ['radius', 'proxy']) || !isset($request['id'])) {
                throw new yii\base\InvalidParamException(Yii::t('app', 'message Invalid Param'));
            }
            $res = Online::dropOnlineById($request['type'], $request['id']);
        } //用户状态
        else if ($action == 'available') {
            $request['type'] = $get['type'];
            if (!isset($request['type']) || !in_array($request['type'], [0, 1, 3, 4]) || !isset($request['user_name'])) {
                throw new yii\base\InvalidParamException(Yii::t('app', 'message Invalid Param'));
            }
            $model->user_available = $request['type'];
            //同步更新下redis数据
            $user = Redis::executeCommand('HGETALL', 'hash:users:' . $model->user_id);
            if (!empty($user)) {
                $data = Redis::arrayToHash(['user_available' => $request['type']]);
                Redis::executeCommand('hMset', 'hash:users:' . $model->user_id, $data);
            }
            $res = $model->save(false);
        } //过期日期
        else if ($action == 'expire') {
            $model->user_expire_time = $request['user_expire_time'];
            $res = $model->save(false);
        } //最大连接数
        else if ($action == 'max_online_num') {
            if ((!is_numeric($request['max_online_num']) || $request['max_online_num'] < 0) && $request['max_online_num'] != '') {
                throw new yii\base\InvalidParamException(Yii::t('app', 'message Invalid Param'));
            }
            $data = Redis::arrayToHash(['max_online_num' => $request['max_online_num']]);
            Redis::executeCommand('hMset', 'hash:users:' . $model->user_id, $data);
            $res = $model->save(false);
        } //是否mac认证
        else if ($action == 'no_mac') {
            if (!isset($request['type']) || !in_array($request['type'], [0, 1])) {
                throw new yii\base\InvalidParamException(Yii::t('app', 'message Invalid Param'));
            }
            $res = KernelInterface::setMacAuth(['user_name' => $model->user_name, 'value' => $request['type']]);
        } //绑定数据
        else if ($action == 'addBind') {
            if (!isset($request['type']) || !isset($request['bindVal'])) {
                throw new yii\base\InvalidParamException(Yii::t('app', 'message Invalid Param'));
            }
            if ($request['type'] == 1) {
                $check_res = KernelInterface::checkMacAuth($request['bindVal']);
                if ($check_res) {
                    Yii::$app->getSession()->setFlash('error', 'Mac Invalid');
                    return $this->goBack(Yii::$app->request->referrer);
                }
            }
            $data = [
                'operation' => 1,
                'user_name' => $model->user_name,
                'value' => $request['bindVal'],
                'type' => $request['type']
            ];
            $res = KernelInterface::userBind($data);
            //绑定记录操作日志
            $model->bindLog($data);
        } //绑定CDR数据
        else if ($action == 'addCDRBind') {
            if (!isset($request['bindCDRVal'])) {
                throw new yii\base\InvalidParamException(Yii::t('app', 'message Invalid Param'));
            }
            $res = KernelInterface::CDRBind($model->user_name, $request['bindCDRVal']);
        } //删除绑定数据
        else if ($action == 'delBind') {
            if (!isset($request['type']) || !array_key_exists($request['type'], $model->bindType) || !isset($request['bindVal'])) {
                throw new yii\base\InvalidParamException(Yii::t('app', 'message Invalid Param'));
            }
            //删除mac的时候把 mac后边连接的系统名称去掉 如c0:cb:38:83:77:ba(Windows 7)
            $value = str_replace(strstr($request['bindVal'], '('), '', $request['bindVal']);
            $data = [
                'operation' => 2,
                'user_name' => $model->user_name,
                'value' => $value,
                'type' => $model->bindType[$request['type']]
            ];
            $res = KernelInterface::userBind($data);
            $model->bindLog($data);
        } //删除CDR绑定数据
        else if ($action == 'delCDRBind') {
            if (!isset($request['bindCDRVal'])) {
                throw new yii\base\InvalidParamException(Yii::t('app', 'message Invalid Param'));
            }
            $res = KernelInterface::delCDRBind($model->user_name, $request['bindCDRVal']);
        } //停机保号,type:类型，num:数量，money:钱数
        else if ($action == 'stopToProtect') {
            if (!isset($request['type']) || !in_array($request['type'], ['days', 'months', 'years']) ||
                !isset($request['num']) || !preg_match('/^[1-9][\d]*$/', $request['num']) || intval($request['num']) <= 0 ||
                !isset($request['money']) || !preg_match('/^\d+(\.\d+)?$/', $request['money']) || floatval($request['money']) < 0
            ) {
                //throw new yii\base\InvalidParamException(Yii::t('app', 'message Invalid Param'));
                echo Yii::t('app', 'message Invalid Param');
                exit;
            }
            //判断用户余额是否足够支付停机金额
            if ($model->balance < $request['money']) {
                echo Yii::t('app', 'money shortage');
                exit;
                //throw new yii\base\InvalidParamException(Yii::t('app', 'money shortage'));
                //Yii::$app->getSession()->setFlash('error', Yii::t('app', 'money shortage'));
                //return $this->redirect(['/financial/pay/index?user_name='.$model->user_name]);
            }
            //停机时间
            $res = $model->stopToProtect($request['type'], intval($request['num']), $request['money']);
            echo $res ? Yii::t('app', 'operate success.') : Yii::t('app', 'operate failed.');
            exit;
        }//Key充值
        elseif ($action == 'rechargeKey') {
            $keyModel = new Key();
            if (!isset($request['key']) || empty($request['key'])) {
                echo Yii::t('app', 'key_msg6');
                exit;
            }
            $key = $request['key'];
            $res = $keyModel->recharge($model->user_name, $request['key']);

            if ($res === true) {
                $data = array(
                    'data' =>
                        array(
                            'msg_detail' => Yii::t('app', 'key used result'),
                            'type' => 'success',
                            'link' => $link = array(
                                array(
                                    'text' => Yii::t('app', 'view result'),
                                    'href' => Url::to(['/message/key/index?exact_tag=true&update=true&key_value=' . $key . '&user_name=' . $model->user_name])
                                ),
                                array(
                                    'text' => Yii::t('app', 'key index'),
                                    'href' => Url::to(['/message/key/index']),
                                )
                            ),
                        ));

                return $this->redirect(array('/message/message/message', 'data' => $data));
            }
            echo $res;
            exit;
        }//绑定动态条件
        elseif ($action == 'addCondition') {
            if (empty($request['condition_key']) || $request['value'] == '') {
                throw new yii\base\InvalidParamException(Yii::t('app', 'message Invalid Param'));
            }
            $conditon_value = $request['value'];
            $conditon_name = $model->getAttributesList()['condition'][$request['condition_key']];
            $conditon = new Condition();
            $params = [
                $conditon::USER_NAME => $model->user_name,
                $conditon::GROUP_ID => $request['condition_group_id'],
                $conditon::PRODUCTS_ID => $request['condition_products_id'],
            ];
            $new_key = $conditon->replaceKey($request['condition_key'], $params);
            $res = $conditon->setConditon($new_key, $conditon_value);
            $conditon->writeLog(Yii::$app->user->identity->username, $model->user_name, $conditon_name, $new_key, $conditon_value);
        } //立即转移产品
        elseif ($action == 'changeProductNow') {
            if (!isset($request['user_id']) || !isset($request['pid_from']) || !isset($request['pid_to']) || !isset($request['discount']) || !isset($request['fee']) || $request['amount'] == '') {
                echo Yii::t('app', 'message Invalid Param');
                exit;
            }
            $pro_temp = (new Product())->getProOne($request['pid_from']);
            if ($pro_temp) {
                $ladder_amount = Redis::executeCommand('HGET', 'hash:users:products:' . $request['user_id'] . ':' . $request['pid_from'], ['checkout_amount']);
                $ladder_amount = !is_null($ladder_amount) && $ladder_amount > 0 ? $ladder_amount : 0;
                $checkout_amount = $pro_temp['checkout_amount'] + $ladder_amount;
                $discount = $request['fee'] && $checkout_amount ? $request['amount'] / $checkout_amount : 1;
                $model = new ProductsChange();
                $res = $model->changeProductNow($request['user_name'], $request['user_id'], $request['pid_from'], $request['pid_to'], $discount, $request['fee'], $request['amount']);
            } else {
                $res = false;
            }
            echo $res ? Yii::t('app', 'operate success.') : Yii::t('app', 'operate failed.');
            exit;
        } //转移产品到下个周期
        elseif ($action == 'changeProductNext') {
            if (!isset($request['user_id']) || !isset($request['pid_from']) || !isset($request['pid_to'])) {
                echo Yii::t('app', 'message Invalid Param');
                exit;
            }

            //是否已经存在转移记录
            $is_exists = (new ProductsChange)->isExistNextProduct($request['user_id'], $request['pid_to']);
            if ($is_exists) {
                echo Yii::t('app', 'change product msg1');
                exit;
            }
            $changeModel = new ProductsChange();
            $data = [
                'user_id' => $request['user_id'],
                'products_id_from' => $request['pid_from'],
                'products_id_to' => $request['pid_to'],
            ];
            $res = $changeModel->changeProductNext($data);
            $changeModel->addLogNext($request['user_name'], $request['pid_from'], $request['pid_to']);
            echo $res ? Yii::t('app', 'operate success.') : Yii::t('app', 'operate failed.');
            exit;
        } //预约转移产品
        elseif ($action == 'changeProductAppoint') {
            if (empty($request['user_id']) || empty($request['pid_from']) || empty($request['pid_to']) || empty($request['change_date']) || strtotime($request['change_date']) < time()) {
                echo Yii::t('app', 'message Invalid Param');
                exit;
            }

            //是否已经存在转移记录
            $is_exists = (new ProductsChange)->isExistNextProduct($request['user_id'], $request['pid_to']);
            if ($is_exists) {
                echo Yii::t('app', 'change product msg1');
                exit;
            }

            /*//预约日期不能小于产品的结算日期
            $wait = WaitCheck::find()->where(['user_id' => $request['user_id'], 'products_id' => $request['pid_from']])->andWhere(['>','checkout_date', time()])->one();
            if($wait){
                if(strtotime($request['change_date']) <= $wait->checkout_date){
                    echo '预约日期必须在产品结算日期后';exit;
                }
            }*/

            $model = new ProductsChange();
            $data = [
                'user_id' => $request['user_id'],
                'products_id_from' => $request['pid_from'],
                'products_id_to' => $request['pid_to'],
                'change_date' => strtotime($request['change_date']),
            ];
            $res = $model->changeProductAppoint($data);
            $model->addLogAppoint($request['user_name'], $request['pid_from'], $request['pid_to'], $request['change_date']);
            if ($res) {
                $res = Yii::t('app', 'operate success.');
            } else {
                $res = Yii::t('app', 'operate failed.');
            }
            echo $res;
            exit;
        }
        if ($res) {
            Yii::$app->getSession()->setFlash('success', Yii::t('app', 'operate success.'));
        } else {
            Yii::$app->getSession()->setFlash('error', Yii::t('app', 'operate failed.'));
        }
        return $this->goBack(Yii::$app->request->referrer);
    }

    /**
     * 获取周期性产品的一个周期内的费用（包月费）
     */
    public function actionGetprofee()
    {
        $pid = Yii::$app->request->post('product_id');
        $user_name = Yii::$app->request->post('user_name');
        $user_id = Redis::executeCommand('get', 'key:users:user_name:' . $user_name);
        $discount = 1;
        $checkout_amount = 0;
        if ($pid && $user_name && $user_id) {
            $checkout = new WaitCheck();
            $parses = $checkout->checkoutParams($user_name, $pid, $discount);
            $checkout_amount = $parses['checkout_mode'] ? $parses['checkout_amount'] : 0;

            $checkout_amount_byhalf = $checkout_amount ? $checkout->checkoutParams($user_name, $pid, 0.5)['checkout_amount'] : 0;

            //该产品按天扣除费用，那么算剩余多少天，然后算出总天数，如果没有结算日期，说明还没开始,那么扣为0。按天扣除的钱：结算金额*(1-剩余天数/总天数)
            $checkout_amount_byday = $this->actionGetCheckoutByday($user_id, $pid, $checkout_amount);
        }
        echo json_encode(['checkout_amount_byday' => $checkout_amount_byday, 'checkout_amount_byhalf' => round($checkout_amount_byhalf, 2), 'checkout_amount' => round($checkout_amount, 2)]);
    }

    /**
     * 根据当前时间 获取按天扣除改周期结算金额的费用
     * @param $user_id
     * @param $pid
     * @param $checkout_amount
     * @return int
     */
    private function actionGetCheckoutByday($user_id, $pid, $checkout_amount)
    {
        if ($checkout_amount <= 0) {
            return 0;
        }
        $checkout = WaitCheck::find()->where(['user_id' => $user_id, 'products_id' => $pid])->andWhere(['>', 'checkout_date', strtotime(date('Y-m-d 23:59:59'))])->orderBy(['checkout_id' => SORT_DESC])->one();
        if ($checkout) {
            $pro_info = Redis::executeCommand('hgetall', 'hash:products:' . $pid);
            $pro_info = Redis::hashToArray($pro_info);
            if ($pro_info['checkout_cycle'] == 'day') {
                $all_days = $pro_info['cycle_num'];
            } elseif ($pro_info['checkout_cycle'] == 'year') {
                $all_days = 365 * $pro_info['cycle_num'];
            } else {
                $all_days = 30 * $pro_info['cycle_num'];
            }
            //剩余天数
            $remain_days = ceil(($checkout->checkout_date - time()) / 86400);
            $discount = 1 - $remain_days / $all_days;
            $user_name = Redis::executeCommand('hget', 'hash:users:' . $user_id, ['user_name']);
            $checkout = (new WaitCheck())->checkoutParams($user_name, $pid, $discount)['checkout_amount'];
            $checkout_amount_byday = round($checkout, 2);
        } else {
            $checkout_amount_byday = 0;
        }
        return $checkout_amount_byday;
    }

    public function actionOnlineProduct($user_name, $id)
    {
        $model = Base::findOne(['user_name' => $user_name]);
        if (!$model) {
            throw new NotFoundHttpException(Yii::t('app', 'No results found.'));
        }

        //判断组织结构
        if (!array_key_exists($model->group_id, $model->can_group)) {
            throw new yii\web\ForbiddenHttpException(Yii::t('app', 'message 401 3'));
        }

        //判断此产品是否可以管理
        if (!array_key_exists($id, $model->can_product)) {
            throw new yii\web\ForbiddenHttpException(Yii::t('app', 'message 401 4'));
        }

        //用户产品中没有此产品
        if (!in_array($id, $model->products_id)) {
            throw new NotFoundHttpException(Yii::t('app', 'No results found.'));
        }

        $res = $model->onlineProduct($user_name, $id);

        if (isset($res['code']) && $res['code'] == 'success') {
            Yii::$app->getSession()->setFlash('success', Yii::t('app', 'operate success.'));
        } else {
            Yii::$app->getSession()->setFlash('error', Yii::t('app', 'operate failed.'));
        }
        $this->redirect('view?user_name=' . $user_name);
    }

    public function actionOfflineProduct($user_name, $id)
    {
        $model = Base::findOne(['user_name' => $user_name]);
        if (!$model) {
            throw new NotFoundHttpException(Yii::t('app', 'No results found.'));
        }
        //判断组织结构
        if (!array_key_exists($model->group_id, $model->can_group)) {
            throw new yii\web\ForbiddenHttpException(Yii::t('app', 'message 401 3'));
        }

        //判断此产品是否可以管理
        if (!array_key_exists($id, $model->can_product)) {
            throw new yii\web\ForbiddenHttpException(Yii::t('app', 'message 401 4'));
        }

        //用户产品中没有此产品
        if (!in_array($id, $model->products_id)) {
            throw new NotFoundHttpException(Yii::t('app', 'No results found.'));
        }

        $res = $model->offlineProduct($user_name, $id);

        if (isset($res['code']) && $res['code'] == 'success') {
            Yii::$app->getSession()->setFlash('success', Yii::t('app', 'operate success.'));
        } else {
            Yii::$app->getSession()->setFlash('error', Yii::t('app', 'operate failed.'));
        }
        $this->redirect('view?user_name=' . $user_name);
    }

    /**
     * 用户管理界面批量操作
     * @return string
     */
    public function actionBatchOperate()
    {
        $params = Yii::$app->getRequest()->post();
        $model = new Base();


        $response = $model->batchOperate($params);

        //操作结果
        if (preg_match('/buy/i', $params['action']) || preg_match('/renew/i', $params['action'])) {
            Yii::$app->getSession()->setFlash('success', $response);
        }
        if ($response) {
            $res = ['id' => 1, 'msg' => Yii::t('app', 'operate success.')];
        } else {
            $res = ['id' => 0, 'msg' => Yii::t('app', 'operate failed.')];
        }
        echo json_encode($res);

    }

    /*
     * 检测添加用户是否重复
     * @param res Array/false
     */

    public function actionCheckusername()
    {
        $model = new Base();
        $res = $model->getUserInRedis(yii::$app->request->post('username'));
        if (!$res) {
            $res = false;
        }
        echo json_encode($res);
    }

    /**
     * 潜水用户即 不上网的用户
     * @return string
     */
    public function actionCorpseUsers()
    {
        $model = new Base();
        $params = Yii::$app->getRequest()->getQueryParams();

        //查询srun_detail_day表名
        $res = SrunDetailDay::resetPartitionIndex($params);
        if (!empty($res)) {
            Yii::$app->getSession()->setFlash('danger', $res);
        }

        $list = [];
        $pages = '';
        $attributes = $model->getAttributesList();
        if ((isset($params['start_date']) && $params['start_date']) || (isset($params['end_date']) && $params['end_date'])) {
            $detailUsers = $model->getOnlineUsers($params);
            $query = Base::find()->select(['user_id', 'user_name', 'user_real_name', 'group_id', 'balance']);
            if ($detailUsers) {
                $query->where(['not in', 'user_name', $detailUsers]);
            }
            if (!$model->flag) {
                //所有可以管理的组
                $query->andWhere(['group_id' => array_keys($model->can_group)]);
            }

            //导出excel
            $is_excel = Yii::$app->request->get('action') == 'excel' ? true : false;
            if ($is_excel) {
                $list = $query->asArray()->all();
                if (empty($list)) {
                    Yii::$app->getSession()->setFlash('error', Yii::t('app', 'no record'));
                    return $this->redirect('corpse-users');
                }
                $dataList = $model->corpse_excel($list);
                //生成excel
                $title = Yii::t('app', 'user/base/corpse-users');
                $file = $title . '.xls';
                Excel::header_file($dataList, $file, $title);
                exit;
            } else {
                $pagesSize = 20; // 每页条数
                $pages = new Pagination([
                    'totalCount' => $query->count(),
                    'pageSize' => $pagesSize
                ]);

                $list = $query->offset($pages->offset)
                    ->limit($pages->limit)
                    ->asArray()
                    ->all();
            }


            if ($list) {
                foreach ($list as $key => $value) {
                    //显示产品
                    $products = $model->getProductByName($value['user_name']);
                    if ($products) {
                        $list[$key]['product_name'] = array_values($products);
                        foreach ($products as $pid => $product_name) {
                            $list[$key]['user_balance'][] = Redis::executeCommand('hget', 'hash:users:products:' . $value['user_id'] . ':' . $pid, ['user_balance']);
                        }
                    }
                    //显示状态
                    $userRedis = $model->getUserInRedis($value['user_name']);
                    if ($userRedis) {
                        $list[$key]['user_available'] = $userRedis['user_available'];
                    }
                }
            }
        }

        return $this->render('corpse', [
            'corpseUsers' => $list,
            'params' => $params,
            'pages' => $pages,
            'attributes' => $attributes,
        ]);
    }

    /**
     * 取消套餐 未过期且未使用的才能取消
     * @param $user_name
     * @param $product_id
     * @param $package_id 套餐id 包括':'
     * @param $amount 套餐金额
     * @return yii\web\Response
     * @throws \yii\web\NotFoundHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionCancelPackage($user_name, $product_id, $package_id, $amount)
    {
        $model = Base::findOne(['user_name' => $user_name]);
        if (!$model) {
            throw new NotFoundHttpException(Yii::t('app', 'No results found.'));
        }

        //判断组织结构
        if (!User::canManage('org', $model->group_id)) {
            throw new yii\web\ForbiddenHttpException(Yii::t('app', 'message 401 3'));
        }

        //判断此产品是否可以管理
        if (!User::canManage('product', $product_id)) {
            throw new yii\web\ForbiddenHttpException(Yii::t('app', 'message 401 4'));
        }

        //用户产品中没有此产品
        if (!in_array($product_id, $model->products_id)) {
            throw new NotFoundHttpException(Yii::t('app', 'No results found.'));
        }

        $packageModel = new Package();
        //确保传的套餐金额和套餐的实际金额一致
        $pack_id = explode(':', $package_id)[0];
        $packageInfo = $packageModel->getOne($pack_id);
        if ($amount != $packageInfo['amount']) {
            throw new yii\web\ForbiddenHttpException(Yii::t('app', 'user base help38'));
        }

        $res = $packageModel->cancelPackage($user_name, $product_id, $package_id);
        if ($res) {
            if ($amount > 0) {
                //取消套餐成功后，必须把套餐金额退回电子钱包
                //添加电子钱包
                $user_balance = $model->balance;
                $model->balance += $amount;
                if ($model->save()) {
                    //写转账记录
                    $payModel = new PayList();
                    $payModel->userModel = $model;
                    $data = [
                        'transfer_num' => $amount,
                        'type' => 1,
                        'product_id' => $product_id,
                        'package_id' => $pack_id,
                    ];
                    $trans_res = $payModel->transfer($data);
                    if ($trans_res) {
                        //退费记录
                        $refund_model = new RefundList();
                        $refundData = [
                            'user_name' => $user_name,
                            'refund_num' => $amount,
                            'type' => $refund_model::REFUND_PACKAGE,
                            'product_id' => $product_id,
                            'is_refund_fee' => 0,
                            'package_id' => $pack_id,
                            'remarks' => Yii::t('app', 'user/base/cancel-package')
                        ];
                        $refund_model->insertData($refundData);

                        //操作记录
                        $product_name = (new Product())->getProOne($product_id)['products_name'];
                        $log = Yii::t('app', 'user base help39', ['mgr' => Yii::$app->user->identity->username, 'user_name' => $user_name, 'product_name' => $product_name, 'package_name' => $packageInfo['package_name'], 'amount' => $amount]);
                        $packageModel->cancelPackageLog($user_name, $log);

                        //写取消套餐余额流水
                        $billsData = [
                            'user_name' => $user_name,
                            'target_id' => $pack_id,
                            'change_amount' => $amount,
                            'before_amount' => 0,
                            'before_balance' => $user_balance,
                        ];
                        $this->on(Bills::PACKAGE_CANCEL, ['center\modules\financial\models\Bills', 'billsRecord'], $billsData);
                        $this->trigger(Bills::PACKAGE_CANCEL);
                        $this->off(Bills::PACKAGE_CANCEL);

                        //写电子钱包余额流水
                        $billsData = [
                            'user_name' => $user_name,
                            'target_id' => $model->user_id,
                            'change_amount' => $amount,
                            'before_amount' => 0,
                            'before_balance' => $user_balance,
                            'remark' => Bills::PACKAGE_CANCEL
                        ];
                        $this->on(Bills::WALLET_RECHARGE, ['center\modules\financial\models\Bills', 'billsRecord'], $billsData);
                        $this->trigger(Bills::WALLET_RECHARGE);
                        $this->off(Bills::WALLET_RECHARGE);
                        Yii::$app->getSession()->setFlash('success', Yii::t('app', 'operate success.'));

                        Yii::$app->getSession()->setFlash('success', Yii::t('app', 'operate success.'));
                    }
                }
            } else {
                //操作记录
                $product_name = (new Product())->getProOne($product_id)['products_name'];
                $log = Yii::t('app', 'user base help39', ['mgr' => Yii::$app->user->identity->username, 'user_name' => $user_name, 'product_name' => $product_name, 'package_name' => $packageInfo['package_name'], 'amount' => $amount]);
                $packageModel->cancelPackageLog($user_name, $log);
                Yii::$app->getSession()->setFlash('success', Yii::t('app', 'operate success.'));
            }
        } else {
            Yii::$app->getSession()->setFlash('error', Yii::t('app', 'operate failed.'));
        }

        return $this->goBack(Yii::$app->request->referrer);
    }

    /**
     * 用户转账
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     * @throws yii\web\ForbiddenHttpException
     */
    public function actionTransfer($id)
    {
        $id = intval($id);
        $model = Base::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException(Yii::t('app', 'No results found.'));
        }
        $transferModel = new TransferBalance();
        $transferModel->user_name_from = $model->user_name;
        $transferModel->balance = $model->balance;
        $transferModel->type = 2;
        $transferModel->group_id_from = $model->group_id;

        //判断组织结构和产品是否可用
        //判断组织结构
        if (!User::canManage('org', $model->group_id)) {
            throw new yii\web\ForbiddenHttpException(Yii::t('app', 'message 401 1'));
        }
        $transferModel->scenario = 'transfer';
        if ($transferModel->load(Yii::$app->request->post()) && $transferModel->validate()) {
            //开启事物处理
            $db = Yii::$app->db;
            $transaction = $db->beginTransaction();
            try {
                $transFerNum = $transferModel->transfer_num;
                $toUser = Base::findOne(['user_name' => $transferModel->user_name_to]);
                $balance_before_from = $model->balance;//付款人转账前余额
                $balance_before_to = $toUser->balance;//收款人入账前余额
                //当前用户余额减少
                $model->balance = $model->balance - $transFerNum;
                $model->save();
                //收账用户添加
                $toUser->balance = $toUser->balance + $transFerNum;
                $toUser->save();


                $transaction->commit();
            } catch (\Exception $e) {
                $transaction->rollback(); // 如果操作失败, 数据回滚
                Yii::$app->getSession()->setFlash('error', 'transfer error', ['msg' => $e->getMessage()]);

                return $this->redirect('view?user_name=' . $model->user_name);
            }

            //生成转账记录
            $transferModel->group_id = $toUser->group_id;
            $res = $transferModel->save();

            if ($res) {
                //写入日志
                $base = new Base();
                $logContent = Yii::t('app', 'group msg10', [
                    'mgr' => Yii::$app->user->identity->username,
                    'user_name_from' => $transferModel->user_name_from,
                    'user_name_to' => $transferModel->user_name_to,
                    'transfer_num' => $transFerNum,
                    'time' => date('Y-m-d H:i:s', $transferModel->create_at),
                ]);
                $base->batchLog('', $logContent);

                //付款人写流水
                $billsData = [
                    'user_name' => $transferModel->user_name_from,
                    'target_id' => $model->user_id,
                    'change_amount' => $transFerNum,
                    'before_amount' => 0,
                    'before_balance' => $balance_before_from,
                ];
                $this->on(Bills::PAYMENT, ['center\modules\financial\models\Bills', 'billsRecord'], $billsData);
                $this->trigger(Bills::PAYMENT);
                $this->off(Bills::PAYMENT);

                //收款人写流水
                $billsData = [
                    'user_name' => $transferModel->user_name_to,
                    'target_id' => $toUser->user_id,
                    'change_amount' => $transFerNum,
                    'before_amount' => 0,
                    'before_balance' => $balance_before_to,
                ];
                $this->on(Bills::RECEIPT, ['center\modules\financial\models\Bills', 'billsRecord'], $billsData);
                $this->trigger(Bills::RECEIPT);
                $this->off(Bills::RECEIPT);

                Yii::$app->getSession()->setFlash('success', Yii::t('app', 'operate success.'));
            } else {
                Yii::$app->getSession()->setFlash('error', Yii::t('app', 'operate failed.'));
            }
            return $this->redirect('view?user_name=' . $model->user_name);
        }


        return $this->render('transfer', [
            'model' => $model,
            'transferModel' => $transferModel,
            'extendField' => ExtendsField::getAllData(),//扩展字段
        ]);
    }
}
