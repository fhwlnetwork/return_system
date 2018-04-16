<?php

use yii\db\Schema;
use yii\db\Migration;

class m150107_095429_srun_tbl_extra_pay extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=MyISAM';
        }
        // 创建数据表
        $this->createTable('{{%extra_pay}}', [
            'id' => Schema::TYPE_PK,
            'pay_name' => Schema::TYPE_STRING . '(64) NOT NULL',
            'pay_num' => Schema::TYPE_FLOAT . '(8,2) NOT NULL DEFAULT 0.00',
            'description' => Schema::TYPE_STRING.'(255) NOT NULL',
            'create_at' => Schema::TYPE_INTEGER.' NOT NULL',
            'mgr_name' => Schema::TYPE_STRING.'(64) NOT NULL',
            'FOREIGN KEY (id) REFERENCES extra_pay (id) ON DELETE SET NULL ON UPDATE CASCADE',
        ], $tableOptions);
        // 以下为普通索引
        $this->createIndex('pay_name', '{{%extra_pay}}', 'pay_name');
    }

    public function down()
    {
        $this->dropTable('{{%extra_pay}}');
    }
}
