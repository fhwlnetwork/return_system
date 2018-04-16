<?php

use yii\db\Schema;
use yii\db\Migration;

class m141230_060236_auth_modules_date extends Migration
{
    public function up()
    {
        $this->batchInsert('{{%auth_item}}', ['name', 'type', 'description', 'rule_name', 'data', 'created_at', 'updated_at'], [
            ['auth/assign/create', '2', '创建角色组用户信息', NULL, NULL, 1413791383, 1413791383],
            ['auth/assign/index', '2', '添加角色组用户主界面', NULL, NULL, 1413791383, 1413791383],
            ['auth/assign/view', '2', '展示角色组用户信息', NULL, NULL, 1413791383, 1413791383],
            ['auth/empower/ajax', '2', 'ajax传值，赋权必选项', NULL, NULL, 1413791383, 1413791383],
            ['auth/empower/create', '2', '创建角色赋权操作', NULL, NULL, 1413791383, 1413791383],
            ['auth/empower/delete', '2', '删除一条角色权限数据', NULL, NULL, 1413791383, 1413791383],
            ['auth/empower/index', '2', '角色赋权主界面', NULL, NULL, 1413791383, 1413791383],
            ['auth/empower/view', '2', '展示角色权限详细信息', NULL, NULL, 1413791383, 1413791383],
            ['auth/permission/create', '2', '创建权限操作', NULL, NULL, 1413791383, 1413791383],
            ['auth/permission/delete', '2', '删除一条权限数据', NULL, NULL, 1413791383, 1413791383],
            ['auth/permission/index', '2', '权限管理主界面', NULL, NULL, 1413791383, 1413791383],
            ['auth/permission/show', '2', '权限使用说明', NULL, NULL, 1413791383, 1413791383],
            ['auth/permission/update', '2', '修改一条权限操作', NULL, NULL, 1413791383, 1413791383],
            ['auth/permission/view', '2', '展示权限详细信息', NULL, NULL, 1413791383, 1413791383],
            ['auth/roles/create', '2', '创建新角色操作', NULL, NULL, 1413791383, 1413791383],
            ['auth/roles/delete', '2', '删除一条角色数据', NULL, NULL, 1413791383, 1413791383],
            ['auth/roles/index', '2', '角色管理主界面', NULL, NULL, 1413791383, 1413791383],
            ['auth/roles/update', '2', '修改一条角色操作', NULL, NULL, 1413791383, 1413791383],
            ['auth/roles/view', '2', '展示角色详细信息', NULL, NULL, 1413791383, 1413791383],
            ['financial/default/index', '2', '财务模块', NULL, NULL, 1413791383, 1413791383],
            ['log/default/index', '2', '日志模块', NULL, NULL, 1413791383, 1413791383],
            ['message/default/index', '2', '信息模块', NULL, NULL, 1413791383, 1413791383],
            ['report/default/index', '2', '报告模块', NULL, NULL, 1413791383, 1413791383],
            ['strategy/default/index', '2', '策略模块', NULL, NULL, 1413791383, 1413791383],
            ['user/default/index', '2', '用户模块', NULL, NULL, 1413791383, 1413791383],
            ['root', '1', '超级管理员', NULL, NULL, 1413791150, 1413791150],
            ['test', '1', '测试用户', NULL, NULL, 1413791999, 1413791999],
        ]);

        $this->batchInsert('{{%auth_assignment}}', ['item_name', 'user_id', 'created_at'], [
            ['root', '1', 1413966431],
            ['test', '2', 1413966439],
        ]);

        $this->batchInsert('{{%auth_item_child}}', ['parent', 'child'], [
            ['root', 'auth/empower/ajax'],
            ['root', 'auth/empower/create'],
            ['root', 'auth/empower/index'],
            ['root', 'auth/empower/delete'],
            ['root', 'auth/empower/view'],
            ['root', 'auth/permission/index'],
            ['root', 'auth/permission/create'],
            ['root', 'auth/permission/show'],
        ]);
    }

    public function down()
    {
        echo "m141230_060236_auth_modules_date cannot be reverted.\n";

        return false;
    }
}
