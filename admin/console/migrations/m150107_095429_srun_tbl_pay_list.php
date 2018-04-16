<?php

use yii\db\Schema;
use yii\db\Migration;

class m150107_095429_srun_tbl_pay_list extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=MyISAM';
        }
        // 创建数据表
        $this->createTable('{{%pay_list}}', [
            'id' => Schema::TYPE_BIGPK,
            'user_name' => Schema::TYPE_STRING . '(64) NOT NULL',
            'pay_num' => Schema::TYPE_FLOAT . '(8,2) NOT NULL DEFAULT 0.00',
            'type' => Schema::TYPE_SMALLINT.' NOT NULL DEFAULT 0',
            'pay_type_id' => Schema::TYPE_SMALLINT.' NOT NULL DEFAULT 1',
            'product_id' => Schema::TYPE_SMALLINT.' NOT NULL DEFAULT 0',
            'extra_pay_id' => Schema::TYPE_SMALLINT.' NOT NULL DEFAULT 0',
            'create_at' => Schema::TYPE_INTEGER.' NOT NULL',
            'mgr_name' => Schema::TYPE_STRING.'(64) NOT NULL',
            'FOREIGN KEY (id) REFERENCES pay_list (id) ON DELETE SET NULL ON UPDATE CASCADE',
        ], $tableOptions);
        // 以下为普通索引
        $this->createIndex('user_name', '{{%pay_list}}', 'user_name');
        $this->createIndex('pay_num', '{{%pay_list}}', 'pay_num');
        $this->createIndex('create_at', '{{%pay_list}}', 'create_at');
    }

    public function down()
    {
        $this->dropTable('{{%pay_list}}');
    }
}
