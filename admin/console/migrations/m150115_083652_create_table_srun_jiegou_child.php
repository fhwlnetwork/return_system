<?php

use yii\db\Schema;
use yii\db\Migration;

class m150115_083652_create_table_srun_jiegou_child extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        if ($this->db->createCommand("SHOW TABLES LIKE 'srun_jiegou_child'")->queryAll()) {
            $this->down();
        }

        $this->createTable('{{%srun_jiegou_child}}', [
            'parent' => Schema::TYPE_SMALLINT . ' NOT NULL COMMENT "组织节点ID"',
            'child' => Schema::TYPE_SMALLINT . ' NOT NULL COMMENT "管理员ID"',
            'PRIMARY KEY (parent, child)',
        ], $tableOptions);
        $this->addColumn('{{%srun_jiegou_child}}', 'status', 'tinyint(2) not null default 1 COMMENT "状态"');
    }

    public function down()
    {
        $this->dropTable('{{%srun_jiegou_child}}');
    }
}
