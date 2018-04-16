<?php
namespace center\controllers;

use common\models\ManagerLoginLog;
use Yii;
use common\models\LoginForm;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use center\models\SignupForm;
use center\models\ContactForm;
use common\models\Redis;
use common\models\User;

/**
 * Site controller
 */
class SiteController extends Controller
{ 
    public $layout = 'signin';

    /**
     * @inheritdoc
     */
    public function behaviors()
    {//test
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'login', 'index'],
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['index', 'logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
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
                'class' => 'common\extend\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
                'width' => 90,
                'height' => 46,
                'transparent' => true,
                'maxLength' => 4,
                'minLength' => 4,
            ],
        ];
    }

    public function actionIndex()
    {
        //判断是否已经登录
        if (!\Yii::$app->user->isGuest) {

            $url = 'returnUrl';
            $returnUrl = Yii::$app->session[$url];

            if ($returnUrl != '' && $returnUrl != '/') {
                return $this->redirect([Yii::$app->session[$url]]);
            } else {
                return $this->redirect(['/report/welcome/index']);
            }
        }

        $model = new LoginForm();
        $userIP = Yii::$app->request->userIP;
        $model->ip_area = $userIP;
        $data = Yii::$app->request->post();
        if($data){
            $data['LoginForm']['password'] = base64_decode($data['LoginForm']['password']);
        }
        //var_dump($model->load($data) && $model->login(), $model->username, $data, $_POST);exit;
        if ($model->load($data) && $model->login()) {
            $managerLoginModel = new ManagerLoginLog();
            $id = Yii::$app->user->getId();
            $managerLoginModel->ip = $userIP;
            $managerLoginModel->user_id = $id;
            $managerLoginModel->manager_name = $model->username;
            $managerLoginModel->login_time = time();
            $rs = $managerLoginModel->save(false);
            if ($rs) {
                return $this->goBack(Yii::$app->request->getReferrer());
            } else {
                Yii::$app->getSession()->setFlash('error', Yii::t('app', 'login error'));
                return $this->render('index', [
                    'model' => $model,
                ]);
            }
        } else {
            return $this->render('index', [
                'model' => $model,
            ]);
        }
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->goHome();
    }

    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail(Yii::$app->params['adminEmail'])) {
                Yii::$app->session->setFlash('success', 'Thank you for contacting us. We will respond to you as soon as possible.');
            } else {
                Yii::$app->session->setFlash('error', 'There was an error sending email.');
            }

            return $this->refresh();
        } else {
            return $this->render('contact', [
                'model' => $model,
            ]);
        }
    }

    public function actionAbout()
    {
        return $this->render('about');
    }

    public function actionSignup()
    {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post())) {
            if ($user = $model->signup()) {
                if (Yii::$app->getUser()->login($user)) {
                    return $this->goHome();
                }
            }
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    /*public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->getSession()->setFlash('success', 'Check your email for further instructions.');

                return $this->goHome();
            } else {
                Yii::$app->getSession()->setFlash('error', 'Sorry, we are unable to reset password for email provided.');
            }
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->getSession()->setFlash('success', 'New password was saved.');

            return $this->goHome();
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }*/

    public function actionLanguage($l)
    {
        //判断语言包文件是否存在，存在则切换
        if (is_dir(Yii::getAlias('@center/messages/' . $l . '/'))) {
            Yii::$app->session->set('language', $l);
        }
        return $this->goBack(Yii::$app->request->getReferrer());
    }
}
