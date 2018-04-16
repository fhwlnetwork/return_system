<?php
namespace center\controllers;

use common\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use common\models\Redis;

/**
 * 访问控制总控制器，所有的控制器的父类
 * @todo 忽略列表需要补充完全
 */
class ValidateController extends Controller
{

    // 忽略列表，列表中不做权限验证
    private $ignoreList = [
        'strategy/package/check',
        'user/group/down-load', //下载用户批量操作结果
        'auth/structure/ajax', //获取组织结构
        'auth/structure/node',
        'report/welcome/index', //欢迎页面
        'strategy/product/getbillnum', //获取产品勾选的计费策略所需的金额
        'financial/refund/getprobal', //查询产品可退余额
        'user/base/getprofee', //查询用户产品的包月费
        'financial/pay/printnumadd', //缴费清单打印次数累加
        'message/mesgploy/lists', //ajax 消息策略.
        'strategy/product/ajaxcheckmode',//获取产品结算模式的选项
        'financial/manualcheckout/ajaxedit',//ajax修改手动结算页面结算日期
        'user/base/checkusername',//添加用户时检测用户名是否重复
        'financial/pay/ajax-get-paytypes',//缴费时对缴费方式的切换
        'strategy/condition/get-key',//用户详情查看动态条件
        'auth/assign/get-type', //勾选管理员
        'auth/assign/verify-password', //验证密码
        'user/group/ajax-get-products-by-group',//根据用户组选择绑定的产品
        'strategy/product/get-used-num',//获取产品使用人数
        'strategy/condition/get-value-by-user',//用户详情页查看动态条件值
        'log/prochange/ajax-del-next-product',//用户详情页删除下个产品日志
        'log/prochange/ajax-get-next-product',//用户详情页获取下个产品
        'user/group/get-product-by-bind-group',//用户组查看产品绑定
        'financial/refund/print',//用户打印退费票据
        'auth/assign/ajax-mgr-product-by-id',//ajax获取指定管理员可管理的产品
        'message/booking/search',//创建预约任务单独用户搜索
        'report/system/get-one-detail', //实时监控获取某台机器监控详情
        'report/system/ajax-get-one-type-status', //实时监控获取某台机器某个类型监控详情
        'report/system/ajax-get-all-data', //实时监控页面ajax加载所有机器运行状态
        'report/system/image-save', //保存echarts图片到机器
        'report/actual/download', //导出在网人数
        'user/batch/ip-nums-ajax',//开户查询ip可绑定个数
        //ip段绑定产品和用户组
        'strategy/ip/product-list',
        'strategy/ip/no-bind-list',
        'strategy/ip/yes-bind-list',
        'strategy/ip/bind-product',
        'strategy/ip/cancel-bind-product',
        'strategy/ip/batch-delete',
        'strategy/ip/get-more',
        'strategy/ip/bind-ip',
        'strategy/ip/cancel-bind-ip',
        'user/base/operate',//用户详情ajax操作
        'user/batch/ajax-get-limit', //ajax获取操作用户总数
        'user/batch/download', //下载模板,
        'user/batch/preview',
        'user/batch/export',
        'product/work/list',
        'student/default/view',
        'student/default/index',
        'product/default/works-add',
        'product/work/add',
        'report/dashboard/index',
        'product/work/edit',
        'product/default/export',
        'product/work/pub-view',
        'message/work/index',
        'auth/work/index'
    ];

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    // allow authenticated users
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    // everything else is denied by default
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'test' : null,
                'width' => 120,
                'height' => 46,
                'transparent' => true,
                'maxLength' => 6,
                'minLength' => 6,
            ],
        ];
    }


    /**
     * 在程序执行之前，对访问的方法进行权限验证.
     * @param \yii\base\Action $action
     * @return bool
     * @throws ForbiddenHttpException
     */
    public function beforeAction($action)
    {
        //如果没有配置 两个参数，则直接略过云端验证
        if(!empty(Yii::$app->params['dbConfig']['products_key']) && !empty(Yii::$app->params['dbConfig']['products_password'])){
            $this->actionAbsoluteTimeout();
        }

        $this->actionTimeOut();

        //如果未登录，则直接返回
        if (Yii::$app->user->isGuest) {
            $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
            header("Location:".$http_type.$_SERVER['HTTP_HOST']);
        }

        //获取路径
        $path = Yii::$app->request->pathInfo;

        //忽略列表
        if (in_array($path, $this->ignoreList)) {
            return true;
        }

        if (Yii::$app->user->can($path)) {
            return true;
        } else {
            throw new ForbiddenHttpException(Yii::t('app', 'message 401'));
        }
    }

    public function actionTimeOut()
    {
        if (!Yii::$app->user->getId()) {
            $session = Yii::$app->session;
            $url = 'returnUrl';
            if (Yii::$app->request->url == '/site/logout') {
                $session[$url] = '';
            } else {
                $session[$url] = Yii::$app->request->url;
            }
            Yii::$app->getSession()->setFlash('error', Yii::t('app', 'login timeout'));
            Yii::$app->user->logout();
            $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
            header("Location:".$http_type.$_SERVER['HTTP_HOST']);
        }

        return true;
    }

    /**
     * 如果验证token不通过，时间(5分钟)到期就直接退出
     * 如果有token就验证token过期时间，快要过期就重新获取一下token
     * @return bool|\yii\web\Response
     */
    public function actionAbsoluteTimeout(){
        $absoluteTimeout = Yii::$app->session['noAuthLicenseTime'];
        if ($absoluteTimeout) {
            if(time()-$absoluteTimeout>300){
                $session = Yii::$app->session;
                $url = 'returnUrl';
                if (Yii::$app->request->url == '/site/logout') {
                    $session[$url] = '';
                } else {
                    $session[$url] = Yii::$app->request->url;
                }
                Yii::$app->user->logout();
                $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
                header("Location:".$http_type.$_SERVER['HTTP_HOST']);
            }else{
                $token = Yii::$app->session['access_token'];
                if(empty($token)){
                    Yii::$app->getSession()->setFlash('error', Yii::t('app', 'login timeout by reason', ['message'=>Yii::$app->session['cloud_err']]));
                }
            }
        }
        return true;
    }
}
