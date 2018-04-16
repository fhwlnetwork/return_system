<?php

//配置服务器ip，测试环境连接185
if (YII_ENV_DEV) {
    $server_ips = ['127.0.0.1'];
} else {
    $array = parse_ini_file('/srun3/etc/distribute.conf'); //取得server_ip
    $server_ips = explode(',', $array["server_ip"]); //server_id有可能是多个
}

return [
    //8081中语言的定义
    'define8081' => [
        'zh-CN' => '/srun3/www/include/define.php',
        'en' => '/srun3/www/include/define_en.php',
    ],
    //帮助中心地址
    'help_url' => 'http://121.41.95.98:8000/index/message?code=',
    //分布式ip
    'distribute_ip' => $server_ips,
    //菜单和权限
    'menu' => [
        //系统概况
        'dashboard' => [
            'label' => 'Dashboard', //标签名
            'ico' => 'fa fa-dashboard', //图标
            'color' => 'bg-danger', //颜色
            'url' => 'report/dashboard/index', //链接
            'items' => [],
        ],
        //我的作品
        'product' => [
            'label' => 'Strategy Manage',
            'ico' => 'fa fa-calendar-check-o',
            'color' => 'bg-primary',
            'url' => 'product',
            'items' => [
                //产品策略
                'product/default/works' => [
                    'product/default/base',
                    'product/default/work-history' => [
                        'product/work/add',
                        'product/work/list',
                        'product/default/works-add'
                    ],
                ],
            ],

        ],
        //我的作品
        'student' => [
            'label' => 'Student',
            'ico' => 'fa fa-user',
            'color' => 'bg-warning',
            'url' => 'student',
            'items' => [
                //产品策略
                'student/default/redirect' => [
                    'student/default/level',
                    'student/default/index'
                ],
            ],

        ],
        //就业率统计
        'employ' => [
            'label' => 'employ rate',
            'ico' => 'fa fa-sitemap',
            'color' => 'bg-info',
            'url' => 'employ',
            'items' => [
                //就业率展示
                'employ/default/redirect' => [
                    'employ/default/position',
                    'employ/default/level',
                    'employ/default/index',
                    'employ/default/out'
                ],
            ],

        ],
        //日志管理
        'log' => [
            'label' => 'Log Manage',
            'ico' => 'fa fa-commenting-o',
            'color' => 'bg-primary',
            'url' => 'log',
            'items' => [
                //操作日志
                'log/operate/index' => [
                    'log/operate/export'
                ],
            ],
        ],
        //设置
        'setting' => [
            'label' => 'Setting',
            'ico' => 'fa fa-cogs',
            'color' => 'bg-violet',
            'url' => 'setting',
            'items' => [
                //权限管理
                'auth/show/index' => [
                    //组织结构
                    'auth/structure/index',
                    //用户组绑定产品
                    'user/group/bind-product',
                    //角色
                    'auth/roles/index' => [
                        //'auth/roles/view',
                        'auth/roles/create',
                        'auth/roles/update',
                        'auth/roles/delete',
                    ],
                    //管理员
                    'auth/assign/index' => [
                        //'auth/assign/view',
                        //添加
                        'auth/assign/signup',
                        //编辑
                        'auth/assign/update',
                        'auth/assign/delete',
                    ],
                ],
                //附加字段
            ],
        ],
        //管理员或者老师肺部中心
        'message' => [
            'label' => 'message',
            'ico' => 'fa fa-pie-chart',
            'color' => 'bg-dark',
            'url' => 'message',
            'items' => [
                'message/default/redirect' => [
                    'message/default/index' => [
                        'message/default/check',
                        'message/default/edit',
                        'message/default/delete'
                    ],
                ],
                'message/work/index' => [
                    'message/work/add',
                    'message/work/view',
                    'message/work/edit',
                    'message/work/delete',
                ],
                'message/news/index' => [
                    'message/news/add',
                    'message/news/edit',
                    'message/news/view',
                ],

            ],
        ],
    ],

];