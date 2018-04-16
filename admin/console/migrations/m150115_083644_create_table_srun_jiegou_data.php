<?php

use yii\db\Schema;
use yii\db\Migration;

class m150115_083644_create_table_srun_jiegou_data extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        if ($this->db->createCommand("SHOW TABLES LIKE 'srun_jiegou_data'")->queryAll()) {
            $this->down();
        }

        $this->createTable('{{%srun_jiegou_data}}', [
            'jiegou_id' => Schema::TYPE_SMALLINT . ' NOT NULL COMMENT "结构ID"',
            'group_id' => Schema::TYPE_SMALLINT . ' NOT NULL COMMENT "用户组ID"',
            'PRIMARY KEY (jiegou_id, group_id)',
        ], $tableOptions);
    }

    public function down()
    {
        $this->dropTable('{{%srun_jiegou_data}}');
    }
}
