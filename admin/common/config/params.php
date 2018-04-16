<?php
return [
    'adminEmail' => isset($mailer['username']) ? $mailer['username'] : '',
    'supportEmail' => isset($mailer['username']) ? $mailer['username'] : '',
    'nickName' => isset($mailer['nickname']) ? $mailer['nickname'] : '',
    'user.passwordResetTokenExpire' => 3600,
    'dbConfig' => $mysql_config,
    //AAA上线地址
    'online_ip' => '127.0.0.1',
    //飞塔接口配置项
    'feita_config' => [//218.249.230.165 //Drpeng
        'token_url' => 'http://218.249.230.165/logincheck',
        'username' => 'admin',
        'secretKey' => 'Drpeng',
        'add_object_url' => 'http://218.249.230.165/api/v2/cmdb/firewall/address',
        'open_url' => 'http://218.249.230.165/api/v2/cmdb/firewall/policy?vdom=root',
        'close_url' => 'http://218.249.230.165/api/v2/cmdb/firewall/policy/',
    ],
];
