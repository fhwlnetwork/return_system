<?php

use yii\db\Schema;
use yii\db\Migration;

class m150206_023905_tbl_operate extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        if ($this->db->createCommand("SHOW TABLES LIKE 'log_operate'")->queryAll()) {
            $this->down();
        }

        $this->createTable('{{%log_operate}}', [
            'id' => Schema::TYPE_PK,
            'operator' => Schema::TYPE_STRING . '(128) NOT NULL COMMENT "操作人"',
            'target' => Schema::TYPE_STRING . '(128) NOT NULL COMMENT "操作目标"',
            'action' => Schema::TYPE_STRING . '(64) NOT NULL COMMENT "操作动作"',
            'action_type' => Schema::TYPE_STRING . '(64) NOT NULL COMMENT "操作类型"',
            'content' => Schema::TYPE_TEXT . '(128) NOT NULL COMMENT "操作内容"',
            'opt_ip' => Schema::TYPE_STRING . '(32) NOT NULL COMMENT "操作IP"',
            'opt_time' => Schema::TYPE_INTEGER . ' NOT NULL COMMENT "操作时间"',
            'class' => Schema::TYPE_STRING . '(255) NOT NULL COMMENT "类"',
        ], $tableOptions);

        $this->createIndex('operator', '{{%log_operate}}', 'operator');
        $this->createIndex('target', '{{%log_operate}}', 'target');
        $this->createIndex('action', '{{%log_operate}}', 'action');
        $this->createIndex('opt_ip', '{{%log_operate}}', 'opt_ip');
        $this->createIndex('opt_time', '{{%log_operate}}', 'opt_time');

    }

    public function down()
    {
        echo "m150206_023905_tbl_operate cannot be reverted.\n";

        return false;
    }
}
