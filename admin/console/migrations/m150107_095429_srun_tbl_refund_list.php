<?php

use yii\db\Schema;
use yii\db\Migration;

class m150107_095429_srun_tbl_refund_list extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=MyISAM';
        }
        // 创建数据表
        $this->createTable('{{%refund_list}}', [
            'id' => Schema::TYPE_PK,
            'user_name' => Schema::TYPE_STRING . '(64) NOT NULL',
            'refund_num' => Schema::TYPE_FLOAT . '(8,2) NOT NULL DEFAULT 0.00',
            'create_at' => Schema::TYPE_INTEGER.' NOT NULL',
            'mgr_name' => Schema::TYPE_STRING.'(64) NOT NULL',
            'FOREIGN KEY (id) REFERENCES refund_list (id) ON DELETE SET NULL ON UPDATE CASCADE',
        ], $tableOptions);
        // 以下为普通索引
        $this->createIndex('user_name', '{{%refund_list}}', 'user_name');
        $this->createIndex('refund_num', '{{%refund_list}}', 'refund_num');
    }

    public function down()
    {
        $this->dropTable('{{%refund_list}}');
    }
}
