<?php

return [
    //菜单和权限
    'api_action' => [
        //用户管理
        'user' => [
            'label' => 'User Manage',
            'ico' => 'fa fa-user',
            'color' => 'bg-orange', //颜色
            'url' => 'user',
            'items' => [
                //用户相关
                'crud' => [
                    '/api/v1/users',
                    '/api/v1/user/delete',
                    '/api/v1/user/update',
                    '/api/v1/user/view',
                    '/api/v1/user/search',
                    '/api/v1/user/super-search',
                ],
                'Users extends' => [
                    '/api/v1/user/send-code,/api/v1/user/binding-phone',
                    '/api/v1/user/user-status-control',
                    '/api/v1/user/user-status-control-batch',
                    '/api/v1/user/balance',
                    '/api/v1/user/max-online-num',
                ],
                //修改密码
                'password' => [
                    '/api/v1/user/reset-password',
                    '/api/v1/user/super-reset-password',
                    '/api/v1/user/code,/api/v1/user/forget-reset-password',
                    '/api/v1/user/get-password',
                ],
                'Visitors' => [
                    '/api/v1/user/visitors',
                ],
                //在线用户
                'online' => [
                    //在线设备
                    '/api/v1/base/online-equipment',
                    //下线
                    '/api/v1/base/online-drop',
                    '/api/v1/user/off-line',
                ],
                //用户组
                'User Groups' => [
                    '/api/v1/groups',
                    '/api/v1/group/index,/api/v1/group',
                    '/api/v1/group/subscribe'
                ],
                //设备相关
                'Equipment' => [
                    '/api/v1/base/macs',
                    '/api/v1/base/create-mac',
                    '/api/v1/base/delete-mac',
                    '/api/v1/base/update-mac',
                    '/api/v1/base/create-mac-auth',
                    '/api/v1/base/update-mac-auth',
                    '/api/v1/base/list-mac-auth',
                    '/api/v1/base/update-vlan',
                ]
            ],
        ],
        //财务管理
        'financial' => [
            'label' => 'Financial Manage',
            'ico' => 'fa fa-usd',
            'color' => 'bg-success',
            'url' => 'financial',
            'items' => [
                //充值
                'pay' => [
                    '/api/v1/financial/recharge-wallet', //电子钱包缴费
                    '/api/v1/financial/transfer', //余额转账
                    '/api/v1/financial/recharge-cards', //查询充值卡数据
                    '/api/v1/financial/extra-pay', //附加费用
                    '/api/v1/financial/payment-data-sync', //第三方缴费数据同步接口
                    '/api/v1/alipay/pay,/api/v1/alipay/write-log', //支付宝缴费
                ],
                //缴费清单
                'Financial list' => [
                    '/api/v1/financial/payment-records',
                    '/api/v1/financial/refund',
                    '/api/v1/checkoutlist/detail',
                    '/api/v1/financial/bill',
                    '/api/v1/financial/pay-type',
                ],
            ],
        ],
        //设置
        'setting' => [
            'label' => 'Setting',
            'ico' => 'glyphicon glyphicon-user',
            'color' => 'bg-violet',
            'url' => 'auth',
            'items' => [
                '/api/v1/setting/language',
            ],
        ],
        //认证授权
        'auth' => [
            'label' => 'auth',
            'ico' => 'fa fa-cogs',
            'color' => 'bg-violet',
            'url' => 'setting',
            'items' => [
                '/api/v1/user/validate-users',
                '/api/v1/user/validate-manager',
                '/api/v1/auth',
                '/api/v1/auth/get-access-token'
            ],
        ],
        //日志管理
        'log' => [
            'label' => 'Log Manage',
            'ico' => 'fa fa-pencil-square-o',
            'color' => 'bg-info',
            'url' => 'log',
            'items' => [
                //上网明细
                '/api/v1/details'
            ],
        ],
        //策略管理
        'strategy' => [
            'label' => 'Strategy Manage',
            'ico' => 'fa fa-sitemap',
            'color' => 'bg-warning',
            'url' => 'strategy',
            'items' => [
                //产品策略
                'strategy/product/index' => [
                    '/api/v1/product/create',
                    '/api/v1/product/delete',
                    '/api/v1/product/update',
                    '/api/v1/product/index',
                    '/api/v1/product/view',
                    '/api/v1/product/can-subscribe-products',
                    '/api/v1/product/use-number',
                    '/api/v1/product/product-recharge',
                    '/api/v1/product/recharge',
                    '/api/v1/product/subscribe',
                    '/api/v1/product/transfer-product',
                    '/api/v1/product/next-billing-cycle',
                    '/api/v1/product/reservation-transfer-products',
                    '/api/v1/product/cancel-reservation-transfer-products',
                    '/api/v1/product/cancel',
                    '/api/v1/product/disable-product',
                    '/api/v1/product/enable-product',
                    '/api/v1/product/operators',
                    '/api/v1/product/expire',
                    '/api/v1/product/refund',
                ],
                //套餐策略
                'strategy/package/index' => [
                    '/api/v1/package/users-packages',
                    '/api/v1/packages',
                    '/api/v1/package/buy',
                    '/api/v1/package/buy-super',
                    '/api/v1/package/buys',
                    '/api/v1/package/batch',
                ],
                //消息策略
                'Message Manage' => [
                    '/api/v1/message/notice',
                    '/api/v1/message',
                    '/api/v1/message/new-message',
                ],
                //计划任务
                'task' => [
                    '/api/v1/task/key-event',
                    '/api/v1/task/key-third',
                    '/api/v1/task/key-view',
                ]
            ],

        ],
        //中间表
        'user/dongrun' => [
            'label' => 'user/dongrun',
            'ico' => 'glyphicon glyphicon-user',
            'color' => 'bg-violet',
            'url' => 'auth',
            'items' => [
                '/api/v1/dongruan/exec',
            ],
        ],
    ],
    'api_prefix' => YII_ENV_DEV ? 'http://' : 'https://',
    'api_host' => isset($mysql_config['interface_ip']) ? $mysql_config['interface_ip'] : '127.0.0.1',
    'api_port' => isset($mysql_config['api_port']) ? $mysql_config['api_port'] : '8001',
    'mgr_access_token' => isset($mysql_config['api_token']) && $mysql_config['api_token'] ? $mysql_config['api_token'] : '',
];