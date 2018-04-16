<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/../../common/config/cloud_api.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php'),
    //引入 api 以及 app 接口地址
    require(__DIR__ . '/api_params.php'),
    require(__DIR__ . '/app_params.php')
);

return [
    'id' => 'app-center',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'language' => 'zh-CN',
    //'sourceLanguage' => 'No-Country-language',
    'timeZone' => 'Asia/Shanghai',
    'controllerNamespace' => 'center\controllers',
    'modules' => [
        //日志模块
        'log' => [
            'class' => 'center\modules\log\Module',
        ],
        //用户模块
        'user' => [
            'class' => 'center\modules\user\Module',
        ],
        //权限模块
        'auth' => [
            'class' => 'center\modules\auth\Module',
        ],
        //核心模块
        'product' => [
            'class' => 'center\modules\product\Module',
        ],
        //策略模块
        'strategy' => [
            'class' => 'center\modules\strategy\Module',
        ],
        //财务模块
        'financial' => [
            'class' => 'center\modules\financial\Module',
        ],
        //学生管理模块
        'student' => [
            'class' => 'center\modules\student\Module',
        ],
        //报表模块
        'report' => [
            'class' => 'center\modules\report\Module',
        ],
        //设置模块
        'setting' => [
            'class' => 'center\modules\setting\Module',
        ],
        //就业率模块
        'employ' => [
            'class' => 'center\modules\employ\Module',
        ],
        // 网络故障处理
        'network' => [
            'class' => 'center\modules\network\Module',
        ],
        // 自服务设置
        'message' => [
            'class' => 'center\modules\message\Module',
        ],
    ],
    'components' => [
        'user' => [
            'class' => 'center\models\CustomUser',
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'loginUrl' => ['site/index'],
            'authTimeout' => 3600 //服务器保存session 时间
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'center\extend\Log',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            //'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => [
                ['class' => 'yii\rest\UrlRule', 'controller' => ['demo']],
                '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
                '<modules:\w+>/<controller:\w+>/<action:\w+>' => '<modules>/<controller>/<action>',
            ],
        ],
        'request' => [
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
        'errorHandler' => [
            'errorAction' => '/report/welcome/error',
        ],
        'formatter' => [
            'class' => 'yii\i18n\Formatter',
            'dateFormat' => 'php:Y-m-d',
            'datetimeFormat' => 'php:Y-m-d H:i:s',
            'timeFormat' => 'php:H:i:s',
        ],
    ],
    'params' => $params,
];
