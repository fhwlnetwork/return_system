<?php

use yii\db\Schema;
use yii\db\Migration;

class m150115_083634_create_table_srun_jiegou extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        if ($this->db->createCommand("SHOW TABLES LIKE 'srun_jiegou'")->queryAll()) {
            $this->down();
        }

        $this->createTable('{{%srun_jiegou}}', [
            'id' => Schema::TYPE_PK,
            'name' => Schema::TYPE_STRING . '(100) NOT NULL COMMENT "组织结构名称"',
            'path' => Schema::TYPE_STRING . '(255) NOT NULL COMMENT "结构路径"',
        ], $tableOptions);

        $this->addColumn('{{%srun_jiegou}}', 'pid', 'tinyint(3) not null default 1 COMMENT "父ID"');
        $this->addColumn('{{%srun_jiegou}}', 'level', 'tinyint(3) not null default 1 COMMENT "组织递进"');
        $this->addColumn('{{%srun_jiegou}}', 'status', 'tinyint(2) not null default 1 COMMENT "状态"');
    }

    public function down()
    {
        $this->dropTable('{{%srun_jiegou}}');
    }
}
