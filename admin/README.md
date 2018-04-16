##srun4k项目介绍


##srun4k开发说明

* 开发工具：建议phpstorm 8.0
* 代码书写格式：采用yii框架的书写格式，稍后补充

##项目安装说明

* 在电脑上安装运行环境Apache+Mysql+PHP ( 简单方式可以安装[wamp](http://www.wampserver.com/en/)，选择php5.5版本的下载 )


    >注意：需要开启Apache的rewrite模块 
    
* 安装git客户端，git使用可以参考<http://git-scm.com/book/zh>.
* 将整个项目clone到www根目录.
* 访问<http://localhost/web/requirements.php>查看环境是否支持yii框架。
* 修改数据库的本地配置，打开 common/config/main-local.php

    ```php
    'db' => [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=localhost;dbname=srun',//建议数据库名称保持为srun
        'username' => 'root',//数据库用户名
        'password' => '',//数据库密码，如果没有则留空
        'charset' => 'utf8',
    ],
    ```

* 在mysql中新建数据库srun.语句如下：


    >mysql>CREATE DATABASE srun /*!40100 DEFAULT CHARACTER SET utf8 */;
    
* 双击运行根目录下的yii.bat。

* 运行下方命令创建权限数据库数据库

    >yii migrate --migrationPath=@yii/rbac/migrations/


* 创建数据表：在命令行下运行>yii migrate ，按照提示输入yes即可完成创建。


    >以后所有的数据库变化都通过此命令进行，具体用法请查看yii的api文档migrate类。
    >请按照文档中标准写法完成，方便其他人升级数据库以及以后的数据库迁移
    >如果您不是第一次运行此命令，还请您依次执行如下命令

    ```php
        DELETE FROM `auth_item`
        DELETE FROM `auth_assignment`
        DELETE FROM `auth_item_child`
        DELETE FROM `migration` WHERE `version` = 'm141230_060236_auth_modules_date'
    ```
    
* 访问<http://localhost/web/api/domos>,看到xml或json格式的内容即代表安装成功！


    >以后web目录为系统的根目录，建议使用域名或者别名的方式直接指向到web目录，则url变为<http://xxx/api/demos>
    >保留了默认的frontend文件，可以通过<http://localhost/web/api/frontend.php>访问；只为演示，实际不再使用；
    >yii的入口文件为www/web/api/index.php。工作目录为www/center目录

合并测试2
###开启快乐的编程之旅吧
