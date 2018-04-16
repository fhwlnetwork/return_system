<?php

use yii\db\Schema;
use yii\db\Migration;

class m150126_033733_auth_modules_data extends Migration
{
    public function up()
    {
        $this->batchInsert('{{%auth_item}}', ['name', 'type', 'description', 'rule_name', 'data', 'created_at', 'updated_at'], [
            ['auth/permission/createPermissionButton', '2', '创建权限按钮', NULL, NULL, 1413791383, 1413791383],
            ['auth/roles/createRolesButton', '2', '创建角色按钮', NULL, NULL, 1413791383, 1413791383],
            ['auth/structure/createButton', '2', '创建根节点按钮', NULL, NULL, 1413791383, 1413791383],
            ['auth/structure/createJiedianButton', '2', '添加结构', NULL, NULL, 1413791383, 1413791383],
            ['auth/structure/deleteJiegou', '2', '删除结构', NULL, NULL, 1413791383, 1413791383],
            ['auth/structure/updateJiedianManager', '2', '修改节点管理员', NULL, NULL, 1413791383, 1413791383],
            ['auth/structure/updateJiedianName', '2', '修改节点名称', NULL, NULL, 1413791383, 1413791383],
            ['auth/structure/signup', '2', '添加管理员', NULL, NULL, 1413791383, 1413791383],
            ['auth/show/index', '2', '展示角色详细信息', NULL, NULL, 1413791383, 1413791383],
            ['setting/default/index', '2', '设置模块', NULL, NULL, 1413791383, 1413791383],
        ]);

        $this->batchInsert('{{%auth_item_child}}', ['parent', 'child'], [
            ['root', 'auth/permission/createPermissionButton'],
            ['root', 'auth/roles/createRolesButton'],
            ['root', 'auth/structure/createButton'],
            ['root', 'auth/structure/createJiedianButton'],
            ['root', 'auth/structure/deleteJiegou'],
            ['root', 'auth/structure/updateJiedianManager'],
            ['root', 'auth/structure/updateJiedianName'],
            ['root', 'auth/structure/signup'],
            ['root', 'auth/show/index'],
            ['root', 'setting/default/index'],
        ]);
    }

    public function down()
    {
        echo "m150126_033733_auth_modules_data cannot be reverted.\n";

        return false;
    }
}
